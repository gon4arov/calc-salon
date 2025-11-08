<?php
class ControllerExtensionModuleMLCalc extends Controller {

    public function index($setting) {
        if (!$this->config->get('module_ml_calc_status')) {
            return '';
        }

        $this->load->language('extension/module/ml_calc');

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_calculate'] = $this->language->get('text_calculate');
        $data['text_payback'] = $this->language->get('text_payback');
        $data['text_payback_regular'] = $this->language->get('text_payback_regular');
        $data['text_profit'] = $this->language->get('text_profit');
        $data['text_months'] = $this->language->get('text_months');
        $data['text_not_applicable'] = $this->language->get('text_not_applicable');
        $data['text_warning_low_profit'] = $this->language->get('text_warning_low_profit');
        $data['text_monthly_breakdown'] = $this->language->get('text_monthly_breakdown');
        $data['text_monthly_profit'] = $this->language->get('text_monthly_profit');
        $data['text_monthly_expenses'] = $this->language->get('text_monthly_expenses');
        $data['text_monthly_rent'] = $this->language->get('text_monthly_rent');
        $data['text_monthly_master'] = $this->language->get('text_monthly_master');
        $data['text_monthly_utilities'] = $this->language->get('text_monthly_utilities');

        $data['entry_clients_per_day'] = $this->language->get('entry_clients_per_day');
        $data['entry_procedure_cost'] = $this->language->get('entry_procedure_cost');
        $data['entry_working_days'] = $this->language->get('entry_working_days');
        $data['entry_rent'] = $this->language->get('entry_rent');
        $data['entry_utilities'] = $this->language->get('entry_utilities');
        $data['entry_master_percent'] = $this->language->get('entry_master_percent');

        // Языковые строки для формул тултипов
        $data['formula_daily_income'] = $this->language->get('formula_daily_income');
        $data['formula_monthly_income'] = $this->language->get('formula_monthly_income');
        $data['formula_master_expenses'] = $this->language->get('formula_master_expenses');
        $data['formula_total_expenses'] = $this->language->get('formula_total_expenses');
        $data['formula_net_profit'] = $this->language->get('formula_net_profit');
        $data['formula_payback'] = $this->language->get('formula_payback');
        $data['formula_days'] = $this->language->get('formula_days');
        $data['formula_months_short'] = $this->language->get('formula_months_short');
        $data['formula_monthly_profit'] = $this->language->get('formula_monthly_profit');
        $data['formula_annual_profit'] = $this->language->get('formula_annual_profit');
        $data['formula_regular_price'] = $this->language->get('formula_regular_price');
        $data['formula_per_month'] = $this->language->get('formula_per_month');

        // Значения по умолчанию из настроек
        $data['default_clients_per_day'] = $this->config->get('module_ml_calc_default_clients_per_day') ? $this->config->get('module_ml_calc_default_clients_per_day') : 7;
        $data['default_procedure_cost'] = $this->config->get('module_ml_calc_default_procedure_cost') ? $this->config->get('module_ml_calc_default_procedure_cost') : 1000;
        $data['default_working_days'] = $this->config->get('module_ml_calc_default_working_days') ? $this->config->get('module_ml_calc_default_working_days') : 30;
        $data['default_rent'] = $this->config->get('module_ml_calc_default_rent') ? $this->config->get('module_ml_calc_default_rent') : 8000;
        $data['default_utilities'] = $this->config->get('module_ml_calc_default_utilities') ? $this->config->get('module_ml_calc_default_utilities') : 2000;
        $data['default_master_percent'] = $this->config->get('module_ml_calc_default_master_percent') ? $this->config->get('module_ml_calc_default_master_percent') : 15;

        $show_product_summary = false;
        $product_id = 0;
        $ignore_jan_check = false;

        if (is_array($setting)) {
            if (isset($setting['product_id'])) {
                $product_id = (int)$setting['product_id'];
            }

            if (!empty($setting['show_product_summary'])) {
                $show_product_summary = true;
            }

            if (!empty($setting['ignore_jan_check'])) {
                $ignore_jan_check = true;
            }
        }

        if (!$product_id && isset($this->request->get['product_id'])) {
            $product_id = (int)$this->request->get['product_id'];
        }

        if (!$product_id) {
            return '';
        }

        $allowed_groups = $this->config->get('module_ml_calc_customer_groups');
        if (is_array($allowed_groups) && !empty($allowed_groups)) {
            $allowed_groups = array_values(array_filter(array_map('intval', $allowed_groups), function($group_id) {
                return $group_id > 0;
            }));

            if (!empty($allowed_groups)) {
                $customer_group_id = $this->customer->isLogged() ? (int)$this->customer->getGroupId() : (int)$this->config->get('config_customer_group_id');

                if (!in_array($customer_group_id, $allowed_groups, true)) {
                    return '';
                }
            }
        }

        $this->load->model('catalog/product');
        $product_info = $this->model_catalog_product->getProduct($product_id);

        if (!$product_info) {
            return '';
        }

        // Проверка JAN, если не установлен флаг игнорирования
        if (!$ignore_jan_check && (string)$product_info['jan'] !== '1') {
            return '';
        }

        // Определяем цену: сначала special (акционная), если нет - обычная
        $base_price = $product_info['price'];
        if ((float)$product_info['special']) {
            $base_price = $product_info['special'];
        }

        // Получаем цену с налогами
        $price_with_tax = $this->tax->calculate($base_price, $product_info['tax_class_id'], $this->config->get('config_tax'));

        // Конвертируем в текущую валюту пользователя (обычно гривны)
        $this->load->model('localisation/currency');

        // Принудительная конвертация из USD в текущую валюту
        // Цены товаров хранятся в USD, независимо от базовой валюты магазина
        $current_currency = isset($this->session->data['currency']) ? $this->session->data['currency'] : $this->config->get('config_currency');
        $converted_product_price = $this->currency->convert($price_with_tax, 'USD', $current_currency);

        $data['product_price'] = $converted_product_price;
        $data['product_price_base'] = (float)$price_with_tax;
        $data['currency_code'] = $current_currency;
        $data['product_price_regular'] = 0.0;
        $data['product_price_regular_base'] = 0.0;
        $data['product_price_special_ratio'] = 1.0;

        if ((float)$price_with_tax > 0.0) {
            $data['currency_rate'] = $converted_product_price / (float)$price_with_tax;
        } else {
            $data['currency_rate'] = $this->currency->convert(1, 'USD', $current_currency);
        }

        // Передаем ID товара для обработки опций в JavaScript
        $data['product_id'] = $product_id;
        $data['product_summary'] = array();
        $data['product_options'] = array();

        // Налаштування стилів
        $data['primary_color'] = $this->config->get('module_ml_calc_primary_color') ? $this->config->get('module_ml_calc_primary_color') : '#007bff';
        $data['button_color'] = $this->config->get('module_ml_calc_button_color') ? $this->config->get('module_ml_calc_button_color') : '#28a745';
        $data['text_color'] = $this->config->get('module_ml_calc_text_color') ? $this->config->get('module_ml_calc_text_color') : '#333333';
        $data['background_color'] = $this->config->get('module_ml_calc_background_color') ? $this->config->get('module_ml_calc_background_color') : '#f8f9fa';
        $data['result_border_color'] = $this->config->get('module_ml_calc_result_border_color') ? $this->config->get('module_ml_calc_result_border_color') : '#28a745';
        $data['income_color'] = $this->config->get('module_ml_calc_income_color') ? $this->config->get('module_ml_calc_income_color') : '#28a745';
        $data['expense_color'] = $this->config->get('module_ml_calc_expense_color') ? $this->config->get('module_ml_calc_expense_color') : '#dc3545';
        $data['title_font_size'] = $this->config->get('module_ml_calc_title_font_size') ? $this->config->get('module_ml_calc_title_font_size') : 24;
        $data['label_font_size'] = $this->config->get('module_ml_calc_label_font_size') ? $this->config->get('module_ml_calc_label_font_size') : 14;
        $data['result_font_size'] = $this->config->get('module_ml_calc_result_font_size') ? $this->config->get('module_ml_calc_result_font_size') : 18;
        $data['button_font_size'] = $this->config->get('module_ml_calc_button_font_size') ? $this->config->get('module_ml_calc_button_font_size') : 16;
        $data['breakdown_font_size'] = $this->config->get('module_ml_calc_breakdown_font_size') ? $this->config->get('module_ml_calc_breakdown_font_size') : 14;

        // Мобільні розміри шрифтів
        $data['mobile_title_font_size'] = $this->config->get('module_ml_calc_mobile_title_font_size') ? $this->config->get('module_ml_calc_mobile_title_font_size') : 20;
        $data['mobile_label_font_size'] = $this->config->get('module_ml_calc_mobile_label_font_size') ? $this->config->get('module_ml_calc_mobile_label_font_size') : 12;
        $data['mobile_result_font_size'] = $this->config->get('module_ml_calc_mobile_result_font_size') ? $this->config->get('module_ml_calc_mobile_result_font_size') : 16;
        $data['mobile_button_font_size'] = $this->config->get('module_ml_calc_mobile_button_font_size') ? $this->config->get('module_ml_calc_mobile_button_font_size') : 14;
        $data['mobile_breakdown_font_size'] = $this->config->get('module_ml_calc_mobile_breakdown_font_size') ? $this->config->get('module_ml_calc_mobile_breakdown_font_size') : 12;

        $original_price_with_tax = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'));
        $data['product_price_regular_base'] = (float)$original_price_with_tax;
        $data['product_price_regular'] = $this->currency->convert($original_price_with_tax, 'USD', $current_currency);
        $formatted_original_price = $this->currency->format($original_price_with_tax, $current_currency);

        $formatted_special_price = '';
        if ((float)$product_info['special']) {
            $special_price_with_tax = $this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'));
            $formatted_special_price = $this->currency->format($special_price_with_tax, $current_currency);
            if ((float)$original_price_with_tax > 0.0) {
                $data['product_price_special_ratio'] = $special_price_with_tax / (float)$original_price_with_tax;
            }
        }

        if ($show_product_summary) {
            $this->load->model('tool/image');

            $image_path = !empty($product_info['image']) ? $product_info['image'] : 'no_image.png';
            $thumb_width = 240;
            $thumb_height = 240;

            $data['product_summary'] = array(
                'name' => $product_info['name'],
                'image' => $this->model_tool_image->resize($image_path, $thumb_width, $thumb_height),
                'href' => $ignore_jan_check ? '' : $this->url->link('product/product', 'product_id=' . $product_id),
                'price_regular' => $formatted_original_price,
                'price_special' => $formatted_special_price,
                'price_regular_value' => $data['product_price_regular'],
                'price_special_value' => $converted_product_price,
                'price_discount_ratio' => $data['product_price_special_ratio']
            );

            $option_types_supported = array('select', 'radio');

            $product_options_raw = $this->model_catalog_product->getProductOptions($product_id);

            foreach ($product_options_raw as $product_option) {
                if (!in_array($product_option['type'], $option_types_supported, true)) {
                    continue;
                }

                $option_values_data = array();
                $is_first_value = true;

                foreach ($product_option['product_option_value'] as $product_option_value) {
                    if ($product_option_value['subtract'] && (int)$product_option_value['quantity'] <= 0) {
                        continue;
                    }

                    $option_price_value = (float)$product_option_value['price'];
                    $option_price_with_tax = $this->tax->calculate($option_price_value, $product_info['tax_class_id'], $this->config->get('config_tax'));
                    $option_price_display = $option_price_with_tax ? $this->currency->format($option_price_with_tax, $current_currency) : '';

                    $option_selected = false;
                    if (isset($product_option['value']) && $product_option['value'] !== '') {
                        $option_selected = ((string)$product_option_value['product_option_value_id'] === (string)$product_option['value']) ||
                                           ((string)$product_option_value['option_value_id'] === (string)$product_option['value']);
                    }
                    if (!$option_selected && $is_first_value) {
                        $option_selected = true;
                    }

                    $option_values_data[] = array(
                        'product_option_value_id' => (int)$product_option_value['product_option_value_id'],
                        'option_value_id' => (int)$product_option_value['option_value_id'],
                        'name' => $product_option_value['name'],
                        'price_raw' => $option_price_value,
                        'price_prefix' => $product_option_value['price_prefix'],
                        'price_display' => $option_price_display,
                        'selected' => $option_selected
                    );

                    $is_first_value = false;
                }

                if (!$option_values_data) {
                    continue;
                }

                $data['product_options'][] = array(
                    'product_option_id' => (int)$product_option['product_option_id'],
                    'option_id' => (int)$product_option['option_id'],
                    'name' => $product_option['name'],
                    'type' => $product_option['type'],
                    'required' => !empty($product_option['required']),
                    'values' => $option_values_data
                );
            }
        }

        $data['show_regular_payback'] = $this->config->get('module_ml_calc_show_regular_payback');

        // Tooltips - статус
        $data['tooltip_payback_status'] = $this->config->get('module_ml_calc_tooltip_payback_status');
        $data['tooltip_payback_regular_status'] = $this->config->get('module_ml_calc_tooltip_payback_regular_status');
        $data['tooltip_annual_profit_status'] = $this->config->get('module_ml_calc_tooltip_annual_profit_status');
        $data['tooltip_monthly_profit_status'] = $this->config->get('module_ml_calc_tooltip_monthly_profit_status');
        $data['tooltip_monthly_expenses_status'] = $this->config->get('module_ml_calc_tooltip_monthly_expenses_status');

        // Tooltips - текст (для текущего языка)
        $language_id = $this->config->get('config_language_id');

        $tooltip_payback_data = $this->config->get('module_ml_calc_tooltip_payback');
        $data['tooltip_payback'] = (is_array($tooltip_payback_data) && isset($tooltip_payback_data[$language_id])) ? $tooltip_payback_data[$language_id] : '';

        $tooltip_payback_regular_data = $this->config->get('module_ml_calc_tooltip_payback_regular');
        $data['tooltip_payback_regular'] = (is_array($tooltip_payback_regular_data) && isset($tooltip_payback_regular_data[$language_id])) ? $tooltip_payback_regular_data[$language_id] : '';

        $tooltip_annual_profit_data = $this->config->get('module_ml_calc_tooltip_annual_profit');
        $data['tooltip_annual_profit'] = (is_array($tooltip_annual_profit_data) && isset($tooltip_annual_profit_data[$language_id])) ? $tooltip_annual_profit_data[$language_id] : '';

        $tooltip_monthly_profit_data = $this->config->get('module_ml_calc_tooltip_monthly_profit');
        $data['tooltip_monthly_profit'] = (is_array($tooltip_monthly_profit_data) && isset($tooltip_monthly_profit_data[$language_id])) ? $tooltip_monthly_profit_data[$language_id] : '';

        $tooltip_monthly_expenses_data = $this->config->get('module_ml_calc_tooltip_monthly_expenses');
        $data['tooltip_monthly_expenses'] = (is_array($tooltip_monthly_expenses_data) && isset($tooltip_monthly_expenses_data[$language_id])) ? $tooltip_monthly_expenses_data[$language_id] : '';

        // Result calculation tooltips
        $data['show_result_tooltips'] = $this->config->get('module_ml_calc_show_result_tooltips');

        return $this->load->view('extension/module/ml_calc', $data);
    }

    public function calculate() {
        $this->load->language('extension/module/ml_calc');

        $json = array();

        // Базовая CSRF защита: проверка HTTP Referer
        $referer_valid = false;
        if (isset($this->request->server['HTTP_REFERER'])) {
            $referer_host = parse_url($this->request->server['HTTP_REFERER'], PHP_URL_HOST);
            $current_host = $this->request->server['HTTP_HOST'];
            $referer_valid = ($referer_host === $current_host);
        }

        // Проверяем что это POST запрос с правильного домена
        if ($this->request->server['REQUEST_METHOD'] !== 'POST' || !$referer_valid) {
            $json['success'] = false;
            $json['error'] = 'Invalid request';
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        if (isset($this->request->post['product_price']) &&
            isset($this->request->post['clients_per_day']) &&
            isset($this->request->post['procedure_cost']) &&
            isset($this->request->post['working_days']) &&
            isset($this->request->post['rent']) &&
            isset($this->request->post['master_percent'])) {

            $product_price = (float)$this->request->post['product_price'];
            $product_price_regular = isset($this->request->post['product_price_regular']) ? (float)$this->request->post['product_price_regular'] : 0;
            $clients_per_day = (int)$this->request->post['clients_per_day'];
            $procedure_cost = (float)$this->request->post['procedure_cost'];
            $working_days = (int)$this->request->post['working_days'];
            $rent = (float)$this->request->post['rent'];
            $utilities = isset($this->request->post['utilities']) ? (float)$this->request->post['utilities'] : 0.0;
            $master_percent = (float)$this->request->post['master_percent'];

            // Валидация входных данных
            if ($clients_per_day < 1 || $clients_per_day > 20) {
                $json['success'] = false;
                $json['error'] = $this->language->get('error_clients_per_day_range');
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }

        if ($procedure_cost < 100 || $procedure_cost > 6000) {
                $json['success'] = false;
                $json['error'] = $this->language->get('error_procedure_cost_range');
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }

            if ($working_days < 1 || $working_days > 31) {
                $json['success'] = false;
                $json['error'] = $this->language->get('error_working_days_range');
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }

            if ($rent < 0 || $rent > 50000) {
                $json['success'] = false;
                $json['error'] = $this->language->get('error_rent_range');
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }

            if ($utilities < 0 || $utilities > 10000) {
                $json['success'] = false;
                $json['error'] = $this->language->get('error_utilities_range');
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }

            if ($master_percent < 0 || $master_percent > 50) {
                $json['success'] = false;
                $json['error'] = $this->language->get('error_master_percent_range');
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }

            if ($product_price <= 0) {
                $json['success'] = false;
                $json['error'] = $this->language->get('error_product_price');
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }

            // Расчет
            $daily_income = $clients_per_day * $procedure_cost;
            $monthly_income = $daily_income * $working_days;
            $master_expenses = $monthly_income * ($master_percent / 100);
            $net_profit = $monthly_income - $rent - $utilities - $master_expenses;

            $monthly_profit_raw = max($net_profit, 0);
            $annual_profit_raw = $monthly_profit_raw * 12;
            $monthly_expenses_total_raw = $rent + $utilities + $master_expenses;

            if ($net_profit > 0) {
                $payback_days = $product_price / $net_profit * 30; // Окупаемость в днях
                $payback_months = ceil($product_price / $net_profit);
                $month_word = $this->getMonthWord($payback_months);

                $json['success'] = true;
                $json['payback_text'] = $payback_months . ' ' . $month_word;
                $json['payback_days_raw'] = (float)$payback_days; // Добавлено для формул тултипов
                $json['annual_profit'] = number_format($annual_profit_raw, 0, '', ' ');
                $json['net_profit'] = number_format($monthly_profit_raw, 0, '', ' ');
                $json['monthly_income'] = number_format($monthly_income, 0, '', ' ');
                $json['warning'] = '';
                $json['monthly_profit'] = number_format($monthly_profit_raw, 0, '', ' ');
                $json['monthly_expenses_total'] = number_format($monthly_expenses_total_raw, 0, '', ' ');

                // Расчет окупаемости для обычной цены (если есть акция)
                if ($product_price_regular > 0 && $product_price_regular > $product_price) {
                    $payback_days_regular = $product_price_regular / $net_profit * 30; // Окупаемость в днях
                    $payback_months_regular = ceil($product_price_regular / $net_profit);
                    // Показываем только если срок окупаемости отличается
                    if ($payback_months_regular != $payback_months) {
                        $month_word_regular = $this->getMonthWord($payback_months_regular);
                        $json['payback_text_regular'] = $payback_months_regular . ' ' . $month_word_regular;
                        $json['payback_days_regular_raw'] = (float)$payback_days_regular; // Добавлено для формул тултипов
                        $json['price_regular_raw'] = (float)$product_price_regular; // Добавлено для формул тултипов
                        $json['has_regular_price'] = true;
                    } else {
                        $json['has_regular_price'] = false;
                    }
                } else {
                    $json['has_regular_price'] = false;
                }
            } else {
                $json['success'] = true;
                $json['payback_text'] = $this->language->get('text_not_applicable');
                $json['annual_profit'] = number_format($annual_profit_raw, 0, '', ' ');
                $json['net_profit'] = number_format($monthly_profit_raw, 0, '', ' ');
                $json['monthly_income'] = number_format(max($monthly_income, 0), 0, '', ' ');
                $json['warning'] = $this->language->get('text_warning_low_profit');
                $json['monthly_profit'] = number_format($monthly_profit_raw, 0, '', ' ');
                $json['monthly_expenses_total'] = number_format($monthly_expenses_total_raw, 0, '', ' ');
            }

            $json['monthly_expense_rent'] = number_format($rent, 0, '', ' ');
            $json['monthly_expense_rent_raw'] = (float)$rent;
            $json['monthly_expense_utilities'] = number_format($utilities, 0, '', ' ');
            $json['monthly_expense_utilities_raw'] = (float)$utilities;
            $json['monthly_expense_master'] = number_format($master_expenses, 0, '', ' ');
            $json['monthly_expense_master_raw'] = (float)$master_expenses;
            $json['monthly_profit_raw'] = (float)$monthly_profit_raw;
            $json['monthly_expenses_total_raw'] = (float)$monthly_expenses_total_raw;

            // Добавляем промежуточные данные для формул тултипов
            $json['daily_income_raw'] = (float)$daily_income;
            $json['monthly_income_raw'] = (float)$monthly_income;
            $json['annual_profit_raw'] = (float)$annual_profit_raw;
        } else {
            $json['success'] = false;
            $json['error'] = $this->language->get('error_missing_data');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
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
