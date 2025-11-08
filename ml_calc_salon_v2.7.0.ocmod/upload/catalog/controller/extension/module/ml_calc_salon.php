<?php
class ControllerExtensionModuleMLCalcSalon extends Controller {

    public function index() {
        $this->load->language('extension/module/ml_calc_salon');

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_no_products'] = $this->language->get('text_no_products');
        $data['text_add_product'] = $this->language->get('text_add_product');
        $data['text_product_name'] = $this->language->get('text_product_name');
        $data['text_product_price'] = $this->language->get('text_product_price');
        $data['text_clients_per_day'] = $this->language->get('text_clients_per_day');
        $data['text_procedure_cost'] = $this->language->get('text_procedure_cost');
        $data['text_master_percent'] = $this->language->get('text_master_percent');
        $data['text_actions'] = $this->language->get('text_actions');
        $data['text_calculate'] = $this->language->get('text_calculate');
        $data['text_save_calculation'] = $this->language->get('text_save_calculation');
        $data['button_remove'] = $this->language->get('button_remove');

        // Общие параметры
        $data['entry_working_days'] = $this->language->get('entry_working_days');
        $data['entry_rent'] = $this->language->get('entry_rent');
        $data['entry_utilities'] = $this->language->get('entry_utilities');

        // Результаты
        $data['text_results'] = $this->language->get('text_results');
        $data['text_total_investment'] = $this->language->get('text_total_investment');
        $data['text_total_payback'] = $this->language->get('text_total_payback');
        $data['text_total_profit'] = $this->language->get('text_total_profit');
        $data['text_product_results'] = $this->language->get('text_product_results');
        $data['text_months'] = $this->language->get('text_months');

        // Значения по умолчанию
        $data['default_working_days'] = $this->config->get('module_ml_calc_salon_default_working_days') ?: 30;
        $data['default_rent'] = $this->config->get('module_ml_calc_salon_default_rent') ?: 8000;
        $data['default_utilities'] = $this->config->get('module_ml_calc_salon_default_utilities') ?: 2000;
        $data['default_clients_per_day'] = $this->config->get('module_ml_calc_salon_default_clients_per_day') ?: 7;
        $data['default_procedure_cost'] = $this->config->get('module_ml_calc_salon_default_procedure_cost') ?: 1000;
        $data['default_master_percent'] = $this->config->get('module_ml_calc_salon_default_master_percent') ?: 15;

        // Загрузка сохраненного расчета, если передан токен
        $share_token = isset($this->request->get['share']) ? $this->request->get['share'] : '';
        $data['share_token'] = $share_token;
        $data['saved_calculation'] = null;

        if ($share_token) {
            $saved_data = $this->loadCalculation($share_token);
            if ($saved_data) {
                $data['saved_calculation'] = $saved_data;
            }
        }

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');

        $this->response->setOutput($this->load->view('extension/module/ml_calc_salon', $data));
    }

    public function getProducts() {
        $this->load->language('extension/module/ml_calc_salon');
        $this->load->model('catalog/product');
        $this->load->model('tool/image');

        $json = array();

        // Получаем список товаров с JAN='1'
        $filter_data = array(
            'filter_jan' => '1',
            'start' => 0,
            'limit' => 100
        );

        $results = $this->model_catalog_product->getProducts($filter_data);

        $products = array();

        foreach ($results as $result) {
            $price = $result['price'];
            $special = $result['special'];

            if ($special) {
                $price = $special;
            }

            // Получаем цену с налогами
            $price_with_tax = $this->tax->calculate($price, $result['tax_class_id'], $this->config->get('config_tax'));

            // Конвертируем в текущую валюту
            $current_currency = isset($this->session->data['currency']) ? $this->session->data['currency'] : $this->config->get('config_currency');
            $converted_price = $this->currency->convert($price_with_tax, 'USD', $current_currency);

            $products[] = array(
                'product_id' => $result['product_id'],
                'name' => $result['name'],
                'price' => $converted_price,
                'price_formatted' => $this->currency->format($price_with_tax, $current_currency),
                'image' => $this->model_tool_image->resize($result['image'] ?: 'no_image.png', 100, 100),
                'href' => $this->url->link('product/product', 'product_id=' . $result['product_id'])
            );
        }

        $json['products'] = $products;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function calculateMultiple() {
        $this->load->language('extension/module/ml_calc_salon');

        $json = array();

        // CSRF защита
        $referer_valid = false;
        if (isset($this->request->server['HTTP_REFERER'])) {
            $referer_host = parse_url($this->request->server['HTTP_REFERER'], PHP_URL_HOST);
            $current_host = $this->request->server['HTTP_HOST'];
            $referer_valid = ($referer_host === $current_host);
        }

        if ($this->request->server['REQUEST_METHOD'] !== 'POST' || !$referer_valid) {
            $json['success'] = false;
            $json['error'] = 'Invalid request';
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        // Получаем данные
        $products = isset($this->request->post['products']) ? $this->request->post['products'] : array();
        $working_days = isset($this->request->post['working_days']) ? (int)$this->request->post['working_days'] : 30;
        $rent = isset($this->request->post['rent']) ? (float)$this->request->post['rent'] : 0;
        $utilities = isset($this->request->post['utilities']) ? (float)$this->request->post['utilities'] : 0;

        // Валидация общих параметров
        if ($working_days < 1 || $working_days > 31) {
            $json['success'] = false;
            $json['error'] = $this->language->get('error_working_days_range');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        if ($rent < 0 || $rent > 100000) {
            $json['success'] = false;
            $json['error'] = $this->language->get('error_rent_range');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        if ($utilities < 0 || $utilities > 20000) {
            $json['success'] = false;
            $json['error'] = $this->language->get('error_utilities_range');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        if (empty($products) || !is_array($products)) {
            $json['success'] = false;
            $json['error'] = $this->language->get('error_no_products');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        // Рассчитываем общую выручку и выручку по каждому товару
        $total_revenue = 0;
        $product_revenues = array();

        foreach ($products as $index => $product) {
            $clients = isset($product['clients_per_day']) ? (int)$product['clients_per_day'] : 0;
            $cost = isset($product['procedure_cost']) ? (float)$product['procedure_cost'] : 0;
            $master_percent = isset($product['master_percent']) ? (float)$product['master_percent'] : 0;

            // Валидация
            if ($clients < 1 || $clients > 50) {
                $json['success'] = false;
                $json['error'] = $this->language->get('error_clients_per_day_range');
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }

            if ($cost < 100 || $cost > 10000) {
                $json['success'] = false;
                $json['error'] = $this->language->get('error_procedure_cost_range');
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }

            if ($master_percent < 0 || $master_percent > 100) {
                $json['success'] = false;
                $json['error'] = $this->language->get('error_master_percent_range');
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }

            $daily_income = $clients * $cost;
            $monthly_income = $daily_income * $working_days;
            $master_expenses = $monthly_income * ($master_percent / 100);
            $net_revenue = $monthly_income - $master_expenses;

            $product_revenues[$index] = $net_revenue;
            $total_revenue += $net_revenue;
        }

        // Общие расходы (аренда + коммунальные)
        $common_expenses = $rent + $utilities;

        // Рассчитываем результаты для каждого товара
        $product_results = array();
        $total_investment = 0;
        $total_profit = 0;

        foreach ($products as $index => $product) {
            $price = isset($product['price']) ? (float)$product['price'] : 0;
            $clients = (int)$product['clients_per_day'];
            $cost = (float)$product['procedure_cost'];
            $master_percent = (float)$product['master_percent'];

            if ($price <= 0) {
                continue;
            }

            $daily_income = $clients * $cost;
            $monthly_income = $daily_income * $working_days;
            $master_expenses = $monthly_income * ($master_percent / 100);
            $net_revenue = $monthly_income - $master_expenses;

            // Распределяем общие расходы пропорционально выручке
            if ($total_revenue > 0) {
                $share = $net_revenue / $total_revenue;
                $product_expenses = $common_expenses * $share;
            } else {
                $product_expenses = 0;
            }

            $product_profit = $net_revenue - $product_expenses;

            // Окупаемость
            if ($product_profit > 0) {
                $payback_months = ceil($price / $product_profit);
                $payback_text = $payback_months . ' ' . $this->getMonthWord($payback_months);
            } else {
                $payback_months = 0;
                $payback_text = $this->language->get('text_not_applicable');
            }

            $product_results[] = array(
                'product_id' => isset($product['product_id']) ? $product['product_id'] : 0,
                'name' => isset($product['name']) ? $product['name'] : '',
                'price' => $price,
                'revenue' => number_format($net_revenue, 0, '', ' '),
                'expenses' => number_format($product_expenses, 0, '', ' '),
                'profit' => number_format($product_profit, 0, '', ' '),
                'payback_months' => $payback_months,
                'payback_text' => $payback_text,
                'profit_raw' => $product_profit
            );

            $total_investment += $price;
            $total_profit += $product_profit;
        }

        // Общая окупаемость
        if ($total_profit > 0) {
            $total_payback_months = ceil($total_investment / $total_profit);
            $total_payback_text = $total_payback_months . ' ' . $this->getMonthWord($total_payback_months);
        } else {
            $total_payback_months = 0;
            $total_payback_text = $this->language->get('text_not_applicable');
        }

        $json['success'] = true;
        $json['total_investment'] = number_format($total_investment, 0, '', ' ');
        $json['total_profit'] = number_format($total_profit, 0, '', ' ');
        $json['total_annual_profit'] = number_format($total_profit * 12, 0, '', ' ');
        $json['total_payback_text'] = $total_payback_text;
        $json['total_payback_months'] = $total_payback_months;
        $json['product_results'] = $product_results;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function saveCalculation() {
        $this->load->language('extension/module/ml_calc_salon');

        $json = array();

        // CSRF защита
        $referer_valid = false;
        if (isset($this->request->server['HTTP_REFERER'])) {
            $referer_host = parse_url($this->request->server['HTTP_REFERER'], PHP_URL_HOST);
            $current_host = $this->request->server['HTTP_HOST'];
            $referer_valid = ($referer_host === $current_host);
        }

        if ($this->request->server['REQUEST_METHOD'] !== 'POST' || !$referer_valid) {
            $json['success'] = false;
            $json['error'] = 'Invalid request';
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $calculation_data = isset($this->request->post['calculation_data']) ? $this->request->post['calculation_data'] : '';

        if (empty($calculation_data)) {
            $json['success'] = false;
            $json['error'] = $this->language->get('error_no_data');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        // Генерируем уникальный токен
        $share_token = substr(md5(uniqid(rand(), true)), 0, 32);

        // Сохраняем в БД
        $this->db->query("INSERT INTO `" . DB_PREFIX . "ml_calc_salon_calculations`
            SET `share_token` = '" . $this->db->escape($share_token) . "',
                `calculation_data` = '" . $this->db->escape($calculation_data) . "',
                `created_at` = NOW()");

        $url = $this->url->link('extension/module/ml_calc_salon', 'share=' . $share_token);

        $json['success'] = true;
        $json['share_token'] = $share_token;
        $json['url'] = $url;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function loadCalculation($share_token) {
        if (strlen($share_token) !== 32 || !ctype_alnum($share_token)) {
            return null;
        }

        $query = $this->db->query("SELECT `calculation_data` FROM `" . DB_PREFIX . "ml_calc_salon_calculations`
            WHERE `share_token` = '" . $this->db->escape($share_token) . "' LIMIT 1");

        if ($query->num_rows) {
            return $query->row['calculation_data'];
        }

        return null;
    }

    private function getMonthWord($count) {
        $count = abs((int)$count);

        $language_code = '';

        if (!empty($this->session->data['language'])) {
            $language_code = $this->session->data['language'];
        } else {
            $language_code = $this->config->get('config_language');
        }

        $language_prefix = substr($language_code, 0, 2);

        $one = $this->language->get('text_month_one');
        $few = $this->language->get('text_month_two');
        $many = $this->language->get('text_month_five');

        if (($language_prefix === 'ru') || ($language_prefix === 'uk')) {
            $mod10 = $count % 10;
            $mod100 = $count % 100;

            if ($mod10 === 1 && $mod100 !== 11) {
                return $one;
            }

            if ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 12 || $mod100 > 14)) {
                return $few;
            }

            return $many;
        }

        return ($count === 1) ? $one : $many;
    }
}
