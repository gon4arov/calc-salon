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
        $data['entry_email'] = $this->language->get('entry_email');
        $data['text_email_lead'] = $this->language->get('text_email_lead');
        $data['button_send_email'] = $this->language->get('button_send_email');
        $data['text_email_sending'] = $this->language->get('text_email_sending');
        $data['text_email_success'] = $this->language->get('text_email_success');
        $data['error_email_required'] = $this->language->get('error_email_required');
        $data['error_email_invalid'] = $this->language->get('error_email_invalid');
        $data['error_email_calculation'] = $this->language->get('error_email_calculation');
        $data['error_email_send'] = $this->language->get('error_email_send');

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

        $json = $this->buildCalculationResult($this->request->post);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function sendEmail() {
        $this->load->language('extension/module/ml_calc');

        $json = array('success' => false);

        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $json['error'] = 'Invalid request';
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        // CSRF защита: проверка HTTP Referer
        $referer_valid = false;
        if (isset($this->request->server['HTTP_REFERER'])) {
            $referer_host = parse_url($this->request->server['HTTP_REFERER'], PHP_URL_HOST);
            $current_host = $this->request->server['HTTP_HOST'];
            $referer_valid = ($referer_host === $current_host);
        }

        if (!$referer_valid) {
            $json['error'] = 'Invalid request';
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $email = isset($this->request->post['email']) ? trim($this->request->post['email']) : '';
        $product_id = isset($this->request->post['product_id']) ? (int)$this->request->post['product_id'] : 0;

        if ($email === '') {
            $json['error'] = $this->language->get('error_email_required');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $json['error'] = $this->language->get('error_email_invalid');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        if (!$product_id) {
            $json['error'] = $this->language->get('error_email_product');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $calculation = $this->buildCalculationResult($this->request->post);
        if (empty($calculation['success'])) {
            $json['error'] = isset($calculation['error']) ? $calculation['error'] : $this->language->get('error_email_calculation');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        // Получаем информацию о товаре для темы письма
        $this->load->model('catalog/product');
        $product_info = $this->model_catalog_product->getProduct($product_id);
        $product_name = $product_info ? $product_info['name'] : '';
        $product_image = '';
        $this->load->model('tool/image');
        if (!empty($product_info['image'])) {
            $product_image = $this->model_tool_image->resize($product_info['image'], 320, 320);
        }

        $current_currency = isset($this->session->data['currency']) ? $this->session->data['currency'] : $this->config->get('config_currency');

        $formatCurrency = function($value) use ($current_currency) {
            return $this->currency->format($value, $current_currency, 1, true);
        };
        $has_discount = !empty($calculation['product_price_regular']) && $calculation['product_price_regular'] > $calculation['product_price'];
        $currency_rate = (!empty($calculation['currency_rate']) && $calculation['currency_rate'] > 0) ? (float)$calculation['currency_rate'] : null;
        $currency_code = !empty($calculation['currency_code']) ? $calculation['currency_code'] : $current_currency;
        $formatCurrencyCode = function($value, $code) {
            return $this->currency->format($value, $code, 1, true);
        };

        $subject = sprintf($this->language->get('text_email_subject'), $product_name ? $product_name : $this->language->get('text_email_subject_generic'));

        $lines = array();
        $lines[] = sprintf($this->language->get('text_email_intro'), $product_name ? $product_name : $this->language->get('text_email_subject_generic'));
        $lines[] = $this->language->get('text_email_product') . ': ' . ($product_name ? $product_name : $this->language->get('text_email_subject_generic'));
        $lines[] = $this->language->get('text_email_product_link') . ': ' . $this->url->link('product/product', 'product_id=' . $product_id);
        $price_main = $formatCurrency($calculation['product_price']);
        $price_usd = null;
        if ($currency_rate) {
            $price_usd = $formatCurrencyCode($calculation['product_price'] / $currency_rate, 'USD');
        }
        $price_regular_usd = null;
        if ($currency_rate && !empty($calculation['product_price_regular'])) {
            $price_regular_usd = $formatCurrencyCode($calculation['product_price_regular'] / $currency_rate, 'USD');
        }

        if ($has_discount) {
            $lines[] = $this->language->get('text_email_product_price_special') . ': ' . $price_main . ($price_usd ? (' (' . $price_usd . ')') : '');
            $lines[] = $this->language->get('text_email_product_price_regular') . ': ' . $formatCurrency($calculation['product_price_regular']) . ($price_regular_usd ? (' (' . $price_regular_usd . ')') : '');
        } else {
            $lines[] = $this->language->get('text_email_product_price') . ': ' . $price_main . ($price_usd ? (' (' . $price_usd . ')') : '');
        }
        $lines[] = '';
        $lines[] = $this->language->get('text_payback') . ': ' . $calculation['payback_text'];
        if (!empty($calculation['payback_text_regular']) && !empty($calculation['has_regular_price'])) {
            $lines[] = $this->language->get('text_payback_regular') . ': ' . $calculation['payback_text_regular'];
        }
        $lines[] = $this->language->get('text_profit') . ': ' . $formatCurrency($calculation['annual_profit_raw']);
        $lines[] = $this->language->get('text_monthly_profit') . ': ' . $formatCurrency($calculation['monthly_profit_raw']);
        $lines[] = $this->language->get('text_monthly_expenses') . ': ' . $formatCurrency($calculation['monthly_expenses_total_raw']);
        $lines[] = $this->language->get('text_monthly_rent') . ': ' . $formatCurrency($calculation['monthly_expense_rent_raw']);
        $lines[] = $this->language->get('text_monthly_utilities') . ': ' . $formatCurrency($calculation['monthly_expense_utilities_raw']);
        $lines[] = $this->language->get('text_monthly_master') . ': ' . $formatCurrency($calculation['monthly_expense_master_raw']);
        $lines[] = '';
        $lines[] = $this->language->get('text_email_inputs');
        $lines[] = $this->language->get('entry_clients_per_day') . ': ' . $calculation['clients_per_day'];
        $lines[] = $this->language->get('entry_procedure_cost') . ': ' . $formatCurrency($calculation['procedure_cost']);
        $lines[] = $this->language->get('entry_working_days') . ': ' . $calculation['working_days'];
        $lines[] = $this->language->get('entry_rent') . ': ' . $formatCurrency($calculation['rent']);
        $lines[] = $this->language->get('entry_utilities') . ': ' . $formatCurrency($calculation['utilities']);
        $lines[] = $this->language->get('entry_master_percent') . ': ' . $calculation['master_percent'] . '%';
        $lines[] = '';
        $lines[] = sprintf($this->language->get('text_email_footer'), $this->config->get('config_name'));

        $message = implode("\n", $lines);

        $product_name_safe = htmlspecialchars($product_name ? $product_name : $this->language->get('text_email_subject_generic'), ENT_QUOTES, 'UTF-8');
        $company_name_safe = htmlspecialchars($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
        $product_link = $this->url->link('product/product', 'product_id=' . $product_id);
        $product_link_safe = htmlspecialchars($product_link, ENT_QUOTES, 'UTF-8');

        $html = '<div style="font-family: Arial, sans-serif; color: #222; max-width: 640px;">';
        $html .= '<h2 style="margin: 0 0 12px; font-size: 20px;">' . sprintf($this->language->get('text_email_intro'), $product_name_safe) . '</h2>';

        $html .= '<div style="margin-bottom:12px; padding:12px 14px; border:1px solid #e9ecef; border-radius:8px; display:flex; gap:12px; align-items:center;">';
        if ($product_image) {
            $html .= '<div style="flex:0 0 120px; margin-right:10px;"><img src="' . htmlspecialchars($product_image, ENT_QUOTES, 'UTF-8') . '" alt="' . $product_name_safe . '" style="max-width:120px; border-radius:8px; border:1px solid #e9ecef;"></div>';
        }
        $html .= '<div style="flex:1;">';
        $html .= '<div style="font-size:16px; font-weight:600; margin-bottom:6px;">' . $product_name_safe . '</div>';
        $html .= '<div style="margin-bottom:6px;"><a href="' . $product_link_safe . '" style="color:#0d6efd; text-decoration:none;">' . $product_link_safe . '</a></div>';
        if ($has_discount) {
            $html .= '<div style="font-size:14px; color:#111;">' . $this->language->get('text_email_product_price_special') . ': <strong>' . htmlspecialchars($price_main, ENT_QUOTES, 'UTF-8') . '</strong>' . ($price_usd ? ' <span style="color:#555;">(' . htmlspecialchars($price_usd, ENT_QUOTES, 'UTF-8') . ')</span>' : '') . '</div>';
            $html .= '<div style="font-size:13px; color:#555;">' . $this->language->get('text_email_product_price_regular') . ': ' . htmlspecialchars($formatCurrency($calculation['product_price_regular']), ENT_QUOTES, 'UTF-8') . ($price_regular_usd ? ' <span>(' . htmlspecialchars($price_regular_usd, ENT_QUOTES, 'UTF-8') . ')</span>' : '') . '</div>';
        } else {
            $html .= '<div style="font-size:14px; color:#111;">' . $this->language->get('text_email_product_price') . ': <strong>' . htmlspecialchars($price_main, ENT_QUOTES, 'UTF-8') . '</strong>' . ($price_usd ? ' <span style="color:#555;">(' . htmlspecialchars($price_usd, ENT_QUOTES, 'UTF-8') . ')</span>' : '') . '</div>';
        }
        $html .= '</div>';
        $html .= '</div>';

        $html .= '<div style="background:#f8f9fa; border:1px solid #e9ecef; border-radius:8px; padding:0; margin-bottom:16px; overflow:hidden;">';
        $html .= '<div style="padding:12px 16px; font-size:16px; color:#111; font-weight:600;">' . $this->language->get('text_payback') . '</div>';
        $html .= '<table style="width:100%; border-collapse:collapse; font-size:14px;">';
        $paybackRows = array(
            array($this->language->get('text_payback'), $calculation['payback_text']),
            (!empty($calculation['payback_text_regular']) && !empty($calculation['has_regular_price'])) ? array($this->language->get('text_payback_regular'), $calculation['payback_text_regular']) : null,
            array($this->language->get('text_profit'), $formatCurrency($calculation['annual_profit_raw']))
        );
        foreach ($paybackRows as $row) {
            if (!$row) {
                continue;
            }
            $html .= '<tr>';
            $html .= '<td style="padding:10px 14px; border-top:1px solid #e9ecef;">' . htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td style="padding:10px 14px; border-top:1px solid #e9ecef; text-align:right; font-weight:600;">' . htmlspecialchars((string)$row[1], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
        $html .= '</div>';

        $html .= '<div style="border:1px solid #e9ecef; border-radius:8px; overflow:hidden; margin-bottom:16px;">';
        $html .= '<div style="background:#f1f3f5; padding:10px 14px; font-size:14px; font-weight:bold;">' . $this->language->get('text_monthly_breakdown') . '</div>';
        $html .= '<table style="width:100%; border-collapse:collapse; font-size:13px;">';
        $rows = array(
            array($this->language->get('text_monthly_profit'), $formatCurrency($calculation['monthly_profit_raw']), '#28a745'),
            array($this->language->get('text_monthly_expenses'), $formatCurrency($calculation['monthly_expenses_total_raw']), '#dc3545'),
            array($this->language->get('text_monthly_rent'), $formatCurrency($calculation['monthly_expense_rent_raw'])),
            array($this->language->get('text_monthly_utilities'), $formatCurrency($calculation['monthly_expense_utilities_raw'])),
            array($this->language->get('text_monthly_master'), $formatCurrency($calculation['monthly_expense_master_raw']))
        );
        foreach ($rows as $row) {
            $html .= '<tr>';
            $color = isset($row[2]) ? $row[2] : '#222';
            $paddingLeft = ($row[0] === $this->language->get('text_monthly_rent') ||
                            $row[0] === $this->language->get('text_monthly_utilities') ||
                            $row[0] === $this->language->get('text_monthly_master')) ? ' padding-left:28px;' : '';
            $html .= '<td style="padding:8px 12px; border-top:1px solid #e9ecef;' . $paddingLeft . '">' . htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td style="padding:8px 12px; border-top:1px solid #e9ecef; text-align:right; font-weight:600; color:' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . ';">' . htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
        $html .= '</div>';

        $html .= '<div style="border:1px solid #e9ecef; border-radius:8px; overflow:hidden;">';
        $html .= '<div style="background:#f1f3f5; padding:10px 14px; font-size:14px; font-weight:bold;">' . $this->language->get('text_email_inputs') . '</div>';
        $html .= '<table style="width:100%; border-collapse:collapse; font-size:13px;">';
        $inputRows = array(
            array($this->language->get('entry_clients_per_day'), $calculation['clients_per_day']),
            array($this->language->get('entry_procedure_cost'), $formatCurrency($calculation['procedure_cost'])),
            array($this->language->get('entry_working_days'), $calculation['working_days']),
            array($this->language->get('entry_rent'), $formatCurrency($calculation['rent'])),
            array($this->language->get('entry_utilities'), $formatCurrency($calculation['utilities'])),
            array($this->language->get('entry_master_percent'), $calculation['master_percent'] . '%')
        );
        foreach ($inputRows as $row) {
            $html .= '<tr>';
            $html .= '<td style="padding:8px 12px; border-top:1px solid #e9ecef;">' . htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td style="padding:8px 12px; border-top:1px solid #e9ecef; text-align:right;">' . htmlspecialchars((string)$row[1], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
        $html .= '</div>';

        $html .= '<p style="margin:12px 0 0; font-size:12px; color:#6c757d;">' . sprintf($this->language->get('text_email_footer'), $company_name_safe) . '</p>';
        $html .= '</div>';

        try {
            if (method_exists('Mail', '__construct') && version_compare(VERSION, '3.0.0.0', '>=')) {
                $mail = new Mail($this->config->get('config_mail_engine'));
            } else {
                $mail = new Mail();
            }

            if (version_compare(VERSION, '3.0.0.0', '>=')) {
                $mail->parameter = $this->config->get('config_mail_parameter');
                $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
            } else {
                $mail->protocol = $this->config->get('config_mail_protocol');
                $mail->parameter = $this->config->get('config_mail_parameter');
                $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                $mail->smtp_password = $this->config->get('config_mail_smtp_password');
                $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
            }

            $mail->setTo($email);
            $mail->setFrom($this->config->get('config_email'));
            $mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
            $mail->setSubject($subject);
            $mail->setText($message);
            if (method_exists($mail, 'setHtml')) {
                $mail->setHtml($html);
            }
            $mail->send();

            $json['success'] = true;
            $json['message'] = sprintf($this->language->get('text_email_success'), $email);
        } catch (\Exception $e) {
            $json['error'] = $this->language->get('error_email_send');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function saveStatistics() {
        $json = array('success' => false);

        // Проверка что это POST запрос
        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        // CSRF защита: проверка HTTP Referer
        $referer_valid = false;
        if (isset($this->request->server['HTTP_REFERER'])) {
            $referer_host = parse_url($this->request->server['HTTP_REFERER'], PHP_URL_HOST);
            $current_host = $this->request->server['HTTP_HOST'];
            $referer_valid = ($referer_host === $current_host);
        }

        if (!$referer_valid) {
            $json['error'] = 'Invalid request';
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        // Получаем данные
        $product_id = isset($this->request->post['product_id']) ? (int)$this->request->post['product_id'] : 0;

        if (!$product_id) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        // Получаем информацию о товаре
        $this->load->model('catalog/product');
        $product_info = $this->model_catalog_product->getProduct($product_id);

        if (!$product_info) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $this->ensureStatisticsSchema();

        // Получаем IP адрес
        $ip_address = '';
        if (isset($this->request->server['HTTP_X_FORWARDED_FOR'])) {
            $ip_address = $this->request->server['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($this->request->server['HTTP_CLIENT_IP'])) {
            $ip_address = $this->request->server['HTTP_CLIENT_IP'];
        } elseif (isset($this->request->server['REMOTE_ADDR'])) {
            $ip_address = $this->request->server['REMOTE_ADDR'];
        }

        // Получаем данные расчета из POST запроса
        $changed_parameter = null;
        if (isset($this->request->post['changed_parameter'])) {
            $changed_parameter_raw = trim($this->request->post['changed_parameter']);
            if ($changed_parameter_raw !== '') {
                $changed_parameter = $this->db->escape($changed_parameter_raw);
            }
        }
        $product_price = isset($this->request->post['product_price']) ? (float)$this->request->post['product_price'] : null;
        $product_price_regular = isset($this->request->post['product_price_regular']) ? (float)$this->request->post['product_price_regular'] : 0.0;
        $clients_per_day = isset($this->request->post['clients_per_day']) ? (int)$this->request->post['clients_per_day'] : null;
        $procedure_cost = isset($this->request->post['procedure_cost']) ? (float)$this->request->post['procedure_cost'] : null;
        $working_days = isset($this->request->post['working_days']) ? (int)$this->request->post['working_days'] : null;
        $rent = isset($this->request->post['rent']) ? (float)$this->request->post['rent'] : null;
        $utilities = isset($this->request->post['utilities']) ? (float)$this->request->post['utilities'] : null;
        $master_percent = isset($this->request->post['master_percent']) ? (float)$this->request->post['master_percent'] : null;
        $old_value = (isset($this->request->post['old_value']) && $this->request->post['old_value'] !== '') ? (float)$this->request->post['old_value'] : null;
        $new_value = (isset($this->request->post['new_value']) && $this->request->post['new_value'] !== '') ? (float)$this->request->post['new_value'] : null;

        // Расчет окупаемости для сохранения в статистике
        $payback_months = null;
        $payback_months_regular = null;
        if ($product_price !== null && $clients_per_day !== null && $procedure_cost !== null && $working_days !== null && $rent !== null && $master_percent !== null) {
            $utilities_calc = ($utilities !== null) ? $utilities : 0;
            $daily_income = $clients_per_day * $procedure_cost;
            $monthly_income = $daily_income * $working_days;
            $master_expenses = $monthly_income * ($master_percent / 100);
            $net_profit = $monthly_income - $rent - $utilities_calc - $master_expenses;
            if ($net_profit > 0) {
                $payback_months = round($product_price / $net_profit, 1);
                if ($product_price_regular > 0) {
                    $payback_months_regular = round($product_price_regular / $net_profit, 1);
                }
            }
        }

        // Сохраняем статистику
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "ml_calc_statistics`
            SET `product_id` = '" . (int)$product_id . "',
                `product_name` = '" . $this->db->escape($product_info['name']) . "',
                `ip_address` = '" . $this->db->escape($ip_address) . "',
                `changed_parameter` = " . ($changed_parameter !== null ? "'" . $changed_parameter . "'" : "NULL") . ",
                `product_price` = " . ($product_price !== null ? "'" . (float)$product_price . "'" : "NULL") . ",
                `clients_per_day` = " . ($clients_per_day !== null ? "'" . (int)$clients_per_day . "'" : "NULL") . ",
                `procedure_cost` = " . ($procedure_cost !== null ? "'" . (float)$procedure_cost . "'" : "NULL") . ",
                `working_days` = " . ($working_days !== null ? "'" . (int)$working_days . "'" : "NULL") . ",
                `rent` = " . ($rent !== null ? "'" . (float)$rent . "'" : "NULL") . ",
                `utilities` = " . ($utilities !== null ? "'" . (float)$utilities . "'" : "NULL") . ",
                `master_percent` = " . ($master_percent !== null ? "'" . (float)$master_percent . "'" : "NULL") . ",
                `payback_months` = " . ($payback_months !== null ? "'" . (float)$payback_months . "'" : "NULL") . ",
                `payback_months_regular` = " . ($payback_months_regular !== null ? "'" . (float)$payback_months_regular . "'" : "NULL") . ",
                `value_old` = " . ($old_value !== null ? "'" . (float)$old_value . "'" : "NULL") . ",
                `value_new` = " . ($new_value !== null ? "'" . (float)$new_value . "'" : "NULL") . ",
                `date_added` = NOW()
        ");

        $json['success'] = true;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function buildCalculationResult($input) {
        $json = array();

        if (!isset($input['product_price']) ||
            !isset($input['clients_per_day']) ||
            !isset($input['procedure_cost']) ||
            !isset($input['working_days']) ||
            !isset($input['rent']) ||
            !isset($input['master_percent'])) {
            $json['success'] = false;
            $json['error'] = $this->language->get('error_missing_data');
            return $json;
        }

        $product_price = (float)$input['product_price'];
        $product_price_regular = isset($input['product_price_regular']) ? (float)$input['product_price_regular'] : 0;
        $clients_per_day = (int)$input['clients_per_day'];
        $procedure_cost = (float)$input['procedure_cost'];
        $working_days = (int)$input['working_days'];
        $rent = (float)$input['rent'];
        $utilities = isset($input['utilities']) ? (float)$input['utilities'] : 0.0;
        $master_percent = (float)$input['master_percent'];

        // Валидация входных данных
        if ($clients_per_day < 1 || $clients_per_day > 20) {
            $json['success'] = false;
            $json['error'] = $this->language->get('error_clients_per_day_range');
            return $json;
        }

        if ($procedure_cost < 100 || $procedure_cost > 6000) {
            $json['success'] = false;
            $json['error'] = $this->language->get('error_procedure_cost_range');
            return $json;
        }

        if ($working_days < 1 || $working_days > 31) {
            $json['success'] = false;
            $json['error'] = $this->language->get('error_working_days_range');
            return $json;
        }

        if ($rent < 0 || $rent > 50000) {
            $json['success'] = false;
            $json['error'] = $this->language->get('error_rent_range');
            return $json;
        }

        if ($utilities < 0 || $utilities > 10000) {
            $json['success'] = false;
            $json['error'] = $this->language->get('error_utilities_range');
            return $json;
        }

        if ($master_percent < 0 || $master_percent > 50) {
            $json['success'] = false;
            $json['error'] = $this->language->get('error_master_percent_range');
            return $json;
        }

        if ($product_price <= 0) {
            $json['success'] = false;
            $json['error'] = $this->language->get('error_product_price');
            return $json;
        }

        // Расчет
        $daily_income = $clients_per_day * $procedure_cost;
        $monthly_income = $daily_income * $working_days;
        $master_expenses = $monthly_income * ($master_percent / 100);
        $net_profit = $monthly_income - $rent - $utilities - $master_expenses;

        $monthly_profit_raw = max($net_profit, 0);
        $annual_profit_raw = $monthly_profit_raw * 12;
        $monthly_expenses_total_raw = $rent + $utilities + $master_expenses;

        $json['clients_per_day'] = $clients_per_day;
        $json['procedure_cost'] = (float)$procedure_cost;
        $json['working_days'] = $working_days;
        $json['rent'] = (float)$rent;
        $json['utilities'] = (float)$utilities;
        $json['master_percent'] = (float)$master_percent;
        $json['product_price'] = (float)$product_price;
        $json['product_price_regular'] = (float)$product_price_regular;
        $json['currency_rate'] = isset($input['currency_rate']) ? (float)$input['currency_rate'] : null;
        $json['currency_code'] = isset($input['currency_code']) ? $this->db->escape($input['currency_code']) : '';

        if ($net_profit > 0) {
            $payback_days = $product_price / $net_profit * 30; // Окупаемость в днях
            $payback_months = round($product_price / $net_profit, 1);
            $month_word = $this->getMonthWord(ceil($payback_months));

            $json['success'] = true;
            $json['payback_text'] = number_format($payback_months, 1, '.', '') . ' ' . $month_word;
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
                $payback_months_regular = round($product_price_regular / $net_profit, 1);
                // Показываем только если срок окупаемости отличается
                if (abs($payback_months_regular - $payback_months) >= 0.1) {
                    $month_word_regular = $this->getMonthWord(ceil($payback_months_regular));
                    $json['payback_text_regular'] = number_format($payback_months_regular, 1, '.', '') . ' ' . $month_word_regular;
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

        return $json;
    }

    private function ensureStatisticsSchema() {
        static $schemaChecked = false;

        if ($schemaChecked) {
            return;
        }

        $schemaChecked = true;

        // Создаем таблицу, если она отсутствует (обновление без повторной установки модуля)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ml_calc_statistics` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `product_name` varchar(255) NOT NULL,
                `ip_address` varchar(45) NOT NULL,
                `changed_parameter` varchar(50) DEFAULT NULL,
                `product_price` decimal(15,4) DEFAULT NULL,
                `clients_per_day` int(11) DEFAULT NULL,
                `procedure_cost` decimal(15,4) DEFAULT NULL,
                `working_days` int(11) DEFAULT NULL,
                `rent` decimal(15,4) DEFAULT NULL,
                `utilities` decimal(15,4) DEFAULT NULL,
                `master_percent` decimal(5,2) DEFAULT NULL,
                `payback_months` decimal(10,2) DEFAULT NULL,
                `payback_months_regular` decimal(10,2) DEFAULT NULL,
                `value_old` decimal(15,4) DEFAULT NULL,
                `value_new` decimal(15,4) DEFAULT NULL,
                `date_added` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `product_id` (`product_id`),
                KEY `date_added` (`date_added`),
                KEY `changed_parameter` (`changed_parameter`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");

        $columnsToAdd = array(
            'payback_months' => 'ADD COLUMN `payback_months` decimal(10,2) DEFAULT NULL AFTER `master_percent`',
            'payback_months_regular' => 'ADD COLUMN `payback_months_regular` decimal(10,2) DEFAULT NULL AFTER `payback_months`',
            'value_old' => 'ADD COLUMN `value_old` decimal(15,4) DEFAULT NULL AFTER `payback_months_regular`',
            'value_new' => 'ADD COLUMN `value_new` decimal(15,4) DEFAULT NULL AFTER `value_old`'
        );

        foreach ($columnsToAdd as $column => $alterSql) {
            $checkQuery = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "ml_calc_statistics` LIKE '" . $column . "'");
            if (!$checkQuery->num_rows) {
                $this->db->query("ALTER TABLE `" . DB_PREFIX . "ml_calc_statistics` " . $alterSql);
            }
        }
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
