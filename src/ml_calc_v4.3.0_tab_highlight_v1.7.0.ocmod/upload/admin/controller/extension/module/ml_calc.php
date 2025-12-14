<?php
class ControllerExtensionModuleMlCalc extends Controller {
    private $error = array();

    const VERSION = '1.7.3';

    public function index() {
        $this->load->language('extension/module/ml_calc');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        $this->load->model('localisation/language');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if (isset($this->request->post['module_ml_calc_customer_groups'])) {
                $this->request->post['module_ml_calc_customer_groups'] = array_values(array_filter(array_map('intval', (array)$this->request->post['module_ml_calc_customer_groups']), function($group_id) {
                    return $group_id > 0;
                }));
            }

            // Санитизация tooltip полей для защиты от XSS
            $tooltip_fields = array(
                'module_ml_calc_tooltip_payback',
                'module_ml_calc_tooltip_payback_regular',
                'module_ml_calc_tooltip_annual_profit',
                'module_ml_calc_tooltip_monthly_profit',
                'module_ml_calc_tooltip_monthly_expenses'
            );

            foreach ($tooltip_fields as $field) {
                if (isset($this->request->post[$field]) && is_array($this->request->post[$field])) {
                    foreach ($this->request->post[$field] as $language_id => $text) {
                        // Удаляем HTML теги и обрезаем до 500 символов
                        $this->request->post[$field][$language_id] = substr(strip_tags($text), 0, 500);
                    }
                }
            }

            $this->model_setting_setting->editSetting('module_ml_calc', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true));
        }

        // Заголовки и хлебные крошки
        $data['heading_title'] = $this->language->get('heading_title');
        $data['module_version'] = self::VERSION;
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_statistics'] = $this->language->get('text_statistics');

        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_show_regular_payback'] = $this->language->get('entry_show_regular_payback');
        $data['help_show_regular_payback'] = $this->language->get('help_show_regular_payback');
        $data['entry_show_calc_button'] = $this->language->get('entry_show_calc_button');
        $data['help_show_calc_button'] = $this->language->get('help_show_calc_button');
        $data['entry_calc_button_bg_color'] = $this->language->get('entry_calc_button_bg_color');
        $data['help_calc_button_bg_color'] = $this->language->get('help_calc_button_bg_color');
        $data['entry_calc_button_text_color'] = $this->language->get('entry_calc_button_text_color');
        $data['help_calc_button_text_color'] = $this->language->get('help_calc_button_text_color');
        $data['entry_default_clients_per_day'] = $this->language->get('entry_default_clients_per_day');
        $data['entry_default_procedure_cost'] = $this->language->get('entry_default_procedure_cost');
        $data['entry_default_working_days'] = $this->language->get('entry_default_working_days');
        $data['entry_default_rent'] = $this->language->get('entry_default_rent');
        $data['entry_default_utilities'] = $this->language->get('entry_default_utilities');
        $data['entry_default_master_percent'] = $this->language->get('entry_default_master_percent');

        // Налаштування стилів
        $data['text_style_settings'] = $this->language->get('text_style_settings');
        $data['text_font_settings'] = $this->language->get('text_font_settings');
        $data['text_mobile_font_settings'] = $this->language->get('text_mobile_font_settings');
        $data['entry_primary_color'] = $this->language->get('entry_primary_color');
        $data['entry_button_color'] = $this->language->get('entry_button_color');
        $data['entry_text_color'] = $this->language->get('entry_text_color');
        $data['entry_background_color'] = $this->language->get('entry_background_color');
        $data['entry_result_border_color'] = $this->language->get('entry_result_border_color');
        $data['entry_income_color'] = $this->language->get('entry_income_color');
        $data['entry_expense_color'] = $this->language->get('entry_expense_color');
        $data['entry_title_font_size'] = $this->language->get('entry_title_font_size');
        $data['entry_label_font_size'] = $this->language->get('entry_label_font_size');
        $data['entry_result_font_size'] = $this->language->get('entry_result_font_size');
        $data['entry_button_font_size'] = $this->language->get('entry_button_font_size');
        $data['entry_breakdown_font_size'] = $this->language->get('entry_breakdown_font_size');
        $data['entry_mobile_title_font_size'] = $this->language->get('entry_mobile_title_font_size');
        $data['entry_mobile_label_font_size'] = $this->language->get('entry_mobile_label_font_size');
        $data['entry_mobile_result_font_size'] = $this->language->get('entry_mobile_result_font_size');
        $data['entry_mobile_button_font_size'] = $this->language->get('entry_mobile_button_font_size');
        $data['entry_mobile_breakdown_font_size'] = $this->language->get('entry_mobile_breakdown_font_size');
        $data['help_primary_color'] = $this->language->get('help_primary_color');
        $data['help_button_color'] = $this->language->get('help_button_color');
        $data['help_text_color'] = $this->language->get('help_text_color');
        $data['help_background_color'] = $this->language->get('help_background_color');
        $data['help_result_border_color'] = $this->language->get('help_result_border_color');
        $data['help_income_color'] = $this->language->get('help_income_color');
        $data['help_expense_color'] = $this->language->get('help_expense_color');
        $data['help_title_font_size'] = $this->language->get('help_title_font_size');
        $data['help_label_font_size'] = $this->language->get('help_label_font_size');
        $data['help_result_font_size'] = $this->language->get('help_result_font_size');
        $data['help_button_font_size'] = $this->language->get('help_button_font_size');
        $data['help_breakdown_font_size'] = $this->language->get('help_breakdown_font_size');
        $data['help_desktop_fonts'] = $this->language->get('help_desktop_fonts');
        $data['help_mobile_fonts'] = $this->language->get('help_mobile_fonts');
        $data['help_mobile_title_font_size'] = $this->language->get('help_mobile_title_font_size');
        $data['help_mobile_label_font_size'] = $this->language->get('help_mobile_label_font_size');
        $data['help_mobile_result_font_size'] = $this->language->get('help_mobile_result_font_size');
        $data['help_mobile_button_font_size'] = $this->language->get('help_mobile_button_font_size');
        $data['help_mobile_breakdown_font_size'] = $this->language->get('help_mobile_breakdown_font_size');

        $data['entry_tab_highlight'] = $this->language->get('entry_tab_highlight');
        $data['help_tab_highlight'] = $this->language->get('help_tab_highlight');
        $data['text_tab_highlight_none'] = $this->language->get('text_tab_highlight_none');
        $data['text_tab_highlight_red'] = $this->language->get('text_tab_highlight_red');
        $data['text_tab_highlight_yellow'] = $this->language->get('text_tab_highlight_yellow');
        $data['text_tab_highlight_new_badge'] = $this->language->get('text_tab_highlight_new_badge');

        $data['entry_customer_groups'] = $this->language->get('entry_customer_groups');
        $data['help_customer_groups'] = $this->language->get('help_customer_groups');

        // Tooltips
        $data['text_tooltip_settings'] = $this->language->get('text_tooltip_settings');
        $data['entry_tooltip_payback'] = $this->language->get('entry_tooltip_payback');
        $data['entry_tooltip_payback_regular'] = $this->language->get('entry_tooltip_payback_regular');
        $data['entry_tooltip_annual_profit'] = $this->language->get('entry_tooltip_annual_profit');
        $data['entry_tooltip_monthly_profit'] = $this->language->get('entry_tooltip_monthly_profit');
        $data['entry_tooltip_monthly_expenses'] = $this->language->get('entry_tooltip_monthly_expenses');
        $data['help_tooltips'] = $this->language->get('help_tooltips');

        // Result calculation tooltips
        $data['text_result_tooltip_settings'] = $this->language->get('text_result_tooltip_settings');
        $data['entry_show_result_tooltips'] = $this->language->get('entry_show_result_tooltips');
        $data['help_result_tooltips'] = $this->language->get('help_result_tooltips');

        $data['text_none'] = $this->language->get('text_none');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        // Ошибки
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        // Хлебные крошки
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/ml_calc', 'token=' . $this->session->data['token'], true)
        );

        // Действия
        $data['action'] = $this->url->link('extension/module/ml_calc', 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true);
        $data['statistics'] = $this->url->link('extension/module/ml_calc/statistics', 'token=' . $this->session->data['token'], true);

        $this->load->model('customer/customer_group');
        $data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

        // Загрузка языков для многоязычных полей
        $data['languages'] = $this->model_localisation_language->getLanguages();

        // Значения по умолчанию
        if (isset($this->request->post['module_ml_calc_status'])) {
            $data['module_ml_calc_status'] = $this->request->post['module_ml_calc_status'];
        } else {
            $data['module_ml_calc_status'] = $this->config->get('module_ml_calc_status');
        }

        if (isset($this->request->post['module_ml_calc_show_regular_payback'])) {
            $data['module_ml_calc_show_regular_payback'] = $this->request->post['module_ml_calc_show_regular_payback'];
        } else {
            $data['module_ml_calc_show_regular_payback'] = $this->config->get('module_ml_calc_show_regular_payback');
        }

        if (isset($this->request->post['module_ml_calc_show_calc_button'])) {
            $data['module_ml_calc_show_calc_button'] = $this->request->post['module_ml_calc_show_calc_button'];
        } else {
            $data['module_ml_calc_show_calc_button'] = $this->config->get('module_ml_calc_show_calc_button');
        }

        if (isset($this->request->post['module_ml_calc_tab_highlight'])) {
            $data['module_ml_calc_tab_highlight'] = $this->request->post['module_ml_calc_tab_highlight'];
        } else {
            $data['module_ml_calc_tab_highlight'] = $this->config->get('module_ml_calc_tab_highlight');
        }

        if (isset($this->request->post['module_ml_calc_customer_groups'])) {
            $data['module_ml_calc_customer_groups'] = (array)$this->request->post['module_ml_calc_customer_groups'];
        } elseif ($this->config->has('module_ml_calc_customer_groups')) {
            $stored_groups = $this->config->get('module_ml_calc_customer_groups');
            $data['module_ml_calc_customer_groups'] = is_array($stored_groups) ? $stored_groups : array();
        } else {
            $data['module_ml_calc_customer_groups'] = array();
        }

        $data['module_ml_calc_customer_groups'] = array_values(array_filter(array_map('intval', $data['module_ml_calc_customer_groups']), function($group_id) {
            return $group_id > 0;
        }));

        if (isset($this->request->post['module_ml_calc_default_clients_per_day'])) {
            $data['module_ml_calc_default_clients_per_day'] = $this->request->post['module_ml_calc_default_clients_per_day'];
        } else {
            $data['module_ml_calc_default_clients_per_day'] = $this->config->get('module_ml_calc_default_clients_per_day') ? $this->config->get('module_ml_calc_default_clients_per_day') : 7;
        }

        if (isset($this->request->post['module_ml_calc_default_procedure_cost'])) {
            $data['module_ml_calc_default_procedure_cost'] = $this->request->post['module_ml_calc_default_procedure_cost'];
        } else {
            $data['module_ml_calc_default_procedure_cost'] = $this->config->get('module_ml_calc_default_procedure_cost') ? $this->config->get('module_ml_calc_default_procedure_cost') : 1000;
        }

        if (isset($this->request->post['module_ml_calc_default_working_days'])) {
            $data['module_ml_calc_default_working_days'] = $this->request->post['module_ml_calc_default_working_days'];
        } else {
            $data['module_ml_calc_default_working_days'] = $this->config->get('module_ml_calc_default_working_days') ? $this->config->get('module_ml_calc_default_working_days') : 30;
        }

        if (isset($this->request->post['module_ml_calc_default_rent'])) {
            $data['module_ml_calc_default_rent'] = $this->request->post['module_ml_calc_default_rent'];
        } else {
            $data['module_ml_calc_default_rent'] = $this->config->get('module_ml_calc_default_rent') ? $this->config->get('module_ml_calc_default_rent') : 8000;
        }

        if (isset($this->request->post['module_ml_calc_default_utilities'])) {
            $data['module_ml_calc_default_utilities'] = $this->request->post['module_ml_calc_default_utilities'];
        } else {
            $data['module_ml_calc_default_utilities'] = $this->config->get('module_ml_calc_default_utilities') ? $this->config->get('module_ml_calc_default_utilities') : 2000;
        }

        if (isset($this->request->post['module_ml_calc_default_master_percent'])) {
            $data['module_ml_calc_default_master_percent'] = $this->request->post['module_ml_calc_default_master_percent'];
        } else {
            $data['module_ml_calc_default_master_percent'] = $this->config->get('module_ml_calc_default_master_percent') ? $this->config->get('module_ml_calc_default_master_percent') : 15;
        }

        // Налаштування кольорів
        if (isset($this->request->post['module_ml_calc_primary_color'])) {
            $data['module_ml_calc_primary_color'] = $this->request->post['module_ml_calc_primary_color'];
        } else {
            $data['module_ml_calc_primary_color'] = $this->config->get('module_ml_calc_primary_color') ? $this->config->get('module_ml_calc_primary_color') : '#007bff';
        }

        if (isset($this->request->post['module_ml_calc_button_color'])) {
            $data['module_ml_calc_button_color'] = $this->request->post['module_ml_calc_button_color'];
        } else {
            $data['module_ml_calc_button_color'] = $this->config->get('module_ml_calc_button_color') ? $this->config->get('module_ml_calc_button_color') : '#28a745';
        }

        if (isset($this->request->post['module_ml_calc_text_color'])) {
            $data['module_ml_calc_text_color'] = $this->request->post['module_ml_calc_text_color'];
        } else {
            $data['module_ml_calc_text_color'] = $this->config->get('module_ml_calc_text_color') ? $this->config->get('module_ml_calc_text_color') : '#333333';
        }

        if (isset($this->request->post['module_ml_calc_background_color'])) {
            $data['module_ml_calc_background_color'] = $this->request->post['module_ml_calc_background_color'];
        } else {
            $data['module_ml_calc_background_color'] = $this->config->get('module_ml_calc_background_color') ? $this->config->get('module_ml_calc_background_color') : '#f8f9fa';
        }

        if (isset($this->request->post['module_ml_calc_result_border_color'])) {
            $data['module_ml_calc_result_border_color'] = $this->request->post['module_ml_calc_result_border_color'];
        } else {
            $data['module_ml_calc_result_border_color'] = $this->config->get('module_ml_calc_result_border_color') ? $this->config->get('module_ml_calc_result_border_color') : '#28a745';
        }

        if (isset($this->request->post['module_ml_calc_income_color'])) {
            $data['module_ml_calc_income_color'] = $this->request->post['module_ml_calc_income_color'];
        } else {
            $data['module_ml_calc_income_color'] = $this->config->get('module_ml_calc_income_color') ? $this->config->get('module_ml_calc_income_color') : '#28a745';
        }

        if (isset($this->request->post['module_ml_calc_expense_color'])) {
            $data['module_ml_calc_expense_color'] = $this->request->post['module_ml_calc_expense_color'];
        } else {
            $data['module_ml_calc_expense_color'] = $this->config->get('module_ml_calc_expense_color') ? $this->config->get('module_ml_calc_expense_color') : '#dc3545';
        }

        if (isset($this->request->post['module_ml_calc_button_bg_color'])) {
            $data['module_ml_calc_button_bg_color'] = $this->request->post['module_ml_calc_button_bg_color'];
        } else {
            $data['module_ml_calc_button_bg_color'] = $this->config->get('module_ml_calc_button_bg_color') ? $this->config->get('module_ml_calc_button_bg_color') : '#4169E1';
        }

        if (isset($this->request->post['module_ml_calc_button_text_color'])) {
            $data['module_ml_calc_button_text_color'] = $this->request->post['module_ml_calc_button_text_color'];
        } else {
            $data['module_ml_calc_button_text_color'] = $this->config->get('module_ml_calc_button_text_color') ? $this->config->get('module_ml_calc_button_text_color') : '#ffffff';
        }

        // Налаштування шрифтів
        if (isset($this->request->post['module_ml_calc_title_font_size'])) {
            $data['module_ml_calc_title_font_size'] = $this->request->post['module_ml_calc_title_font_size'];
        } else {
            $data['module_ml_calc_title_font_size'] = $this->config->get('module_ml_calc_title_font_size') ? $this->config->get('module_ml_calc_title_font_size') : 24;
        }

        if (isset($this->request->post['module_ml_calc_label_font_size'])) {
            $data['module_ml_calc_label_font_size'] = $this->request->post['module_ml_calc_label_font_size'];
        } else {
            $data['module_ml_calc_label_font_size'] = $this->config->get('module_ml_calc_label_font_size') ? $this->config->get('module_ml_calc_label_font_size') : 14;
        }

        if (isset($this->request->post['module_ml_calc_result_font_size'])) {
            $data['module_ml_calc_result_font_size'] = $this->request->post['module_ml_calc_result_font_size'];
        } else {
            $data['module_ml_calc_result_font_size'] = $this->config->get('module_ml_calc_result_font_size') ? $this->config->get('module_ml_calc_result_font_size') : 18;
        }

        if (isset($this->request->post['module_ml_calc_button_font_size'])) {
            $data['module_ml_calc_button_font_size'] = $this->request->post['module_ml_calc_button_font_size'];
        } else {
            $data['module_ml_calc_button_font_size'] = $this->config->get('module_ml_calc_button_font_size') ? $this->config->get('module_ml_calc_button_font_size') : 16;
        }

        if (isset($this->request->post['module_ml_calc_breakdown_font_size'])) {
            $data['module_ml_calc_breakdown_font_size'] = $this->request->post['module_ml_calc_breakdown_font_size'];
        } else {
            $data['module_ml_calc_breakdown_font_size'] = $this->config->get('module_ml_calc_breakdown_font_size') ? $this->config->get('module_ml_calc_breakdown_font_size') : 14;
        }

        // Мобільні розміри шрифтів
        if (isset($this->request->post['module_ml_calc_mobile_title_font_size'])) {
            $data['module_ml_calc_mobile_title_font_size'] = $this->request->post['module_ml_calc_mobile_title_font_size'];
        } else {
            $data['module_ml_calc_mobile_title_font_size'] = $this->config->get('module_ml_calc_mobile_title_font_size') ? $this->config->get('module_ml_calc_mobile_title_font_size') : 20;
        }

        if (isset($this->request->post['module_ml_calc_mobile_label_font_size'])) {
            $data['module_ml_calc_mobile_label_font_size'] = $this->request->post['module_ml_calc_mobile_label_font_size'];
        } else {
            $data['module_ml_calc_mobile_label_font_size'] = $this->config->get('module_ml_calc_mobile_label_font_size') ? $this->config->get('module_ml_calc_mobile_label_font_size') : 12;
        }

        if (isset($this->request->post['module_ml_calc_mobile_result_font_size'])) {
            $data['module_ml_calc_mobile_result_font_size'] = $this->request->post['module_ml_calc_mobile_result_font_size'];
        } else {
            $data['module_ml_calc_mobile_result_font_size'] = $this->config->get('module_ml_calc_mobile_result_font_size') ? $this->config->get('module_ml_calc_mobile_result_font_size') : 16;
        }

        if (isset($this->request->post['module_ml_calc_mobile_button_font_size'])) {
            $data['module_ml_calc_mobile_button_font_size'] = $this->request->post['module_ml_calc_mobile_button_font_size'];
        } else {
            $data['module_ml_calc_mobile_button_font_size'] = $this->config->get('module_ml_calc_mobile_button_font_size') ? $this->config->get('module_ml_calc_mobile_button_font_size') : 14;
        }

        if (isset($this->request->post['module_ml_calc_mobile_breakdown_font_size'])) {
            $data['module_ml_calc_mobile_breakdown_font_size'] = $this->request->post['module_ml_calc_mobile_breakdown_font_size'];
        } else {
            $data['module_ml_calc_mobile_breakdown_font_size'] = $this->config->get('module_ml_calc_mobile_breakdown_font_size') ? $this->config->get('module_ml_calc_mobile_breakdown_font_size') : 12;
        }

        // Tooltips - статус
        if (isset($this->request->post['module_ml_calc_tooltip_payback_status'])) {
            $data['module_ml_calc_tooltip_payback_status'] = $this->request->post['module_ml_calc_tooltip_payback_status'];
        } else {
            $data['module_ml_calc_tooltip_payback_status'] = $this->config->get('module_ml_calc_tooltip_payback_status');
        }

        if (isset($this->request->post['module_ml_calc_tooltip_payback_regular_status'])) {
            $data['module_ml_calc_tooltip_payback_regular_status'] = $this->request->post['module_ml_calc_tooltip_payback_regular_status'];
        } else {
            $data['module_ml_calc_tooltip_payback_regular_status'] = $this->config->get('module_ml_calc_tooltip_payback_regular_status');
        }

        if (isset($this->request->post['module_ml_calc_tooltip_annual_profit_status'])) {
            $data['module_ml_calc_tooltip_annual_profit_status'] = $this->request->post['module_ml_calc_tooltip_annual_profit_status'];
        } else {
            $data['module_ml_calc_tooltip_annual_profit_status'] = $this->config->get('module_ml_calc_tooltip_annual_profit_status');
        }

        if (isset($this->request->post['module_ml_calc_tooltip_monthly_profit_status'])) {
            $data['module_ml_calc_tooltip_monthly_profit_status'] = $this->request->post['module_ml_calc_tooltip_monthly_profit_status'];
        } else {
            $data['module_ml_calc_tooltip_monthly_profit_status'] = $this->config->get('module_ml_calc_tooltip_monthly_profit_status');
        }

        if (isset($this->request->post['module_ml_calc_tooltip_monthly_expenses_status'])) {
            $data['module_ml_calc_tooltip_monthly_expenses_status'] = $this->request->post['module_ml_calc_tooltip_monthly_expenses_status'];
        } else {
            $data['module_ml_calc_tooltip_monthly_expenses_status'] = $this->config->get('module_ml_calc_tooltip_monthly_expenses_status');
        }

        // Tooltips - текст (многоязычные)
        if (isset($this->request->post['module_ml_calc_tooltip_payback'])) {
            $data['module_ml_calc_tooltip_payback'] = $this->request->post['module_ml_calc_tooltip_payback'];
        } else {
            $tooltip_data = $this->config->get('module_ml_calc_tooltip_payback');
            if (is_array($tooltip_data)) {
                $data['module_ml_calc_tooltip_payback'] = $tooltip_data;
            } else {
                $data['module_ml_calc_tooltip_payback'] = array();
            }
        }

        if (isset($this->request->post['module_ml_calc_tooltip_payback_regular'])) {
            $data['module_ml_calc_tooltip_payback_regular'] = $this->request->post['module_ml_calc_tooltip_payback_regular'];
        } else {
            $tooltip_data = $this->config->get('module_ml_calc_tooltip_payback_regular');
            if (is_array($tooltip_data)) {
                $data['module_ml_calc_tooltip_payback_regular'] = $tooltip_data;
            } else {
                $data['module_ml_calc_tooltip_payback_regular'] = array();
            }
        }

        if (isset($this->request->post['module_ml_calc_tooltip_annual_profit'])) {
            $data['module_ml_calc_tooltip_annual_profit'] = $this->request->post['module_ml_calc_tooltip_annual_profit'];
        } else {
            $tooltip_data = $this->config->get('module_ml_calc_tooltip_annual_profit');
            if (is_array($tooltip_data)) {
                $data['module_ml_calc_tooltip_annual_profit'] = $tooltip_data;
            } else {
                $data['module_ml_calc_tooltip_annual_profit'] = array();
            }
        }

        if (isset($this->request->post['module_ml_calc_tooltip_monthly_profit'])) {
            $data['module_ml_calc_tooltip_monthly_profit'] = $this->request->post['module_ml_calc_tooltip_monthly_profit'];
        } else {
            $tooltip_data = $this->config->get('module_ml_calc_tooltip_monthly_profit');
            if (is_array($tooltip_data)) {
                $data['module_ml_calc_tooltip_monthly_profit'] = $tooltip_data;
            } else {
                $data['module_ml_calc_tooltip_monthly_profit'] = array();
            }
        }

        if (isset($this->request->post['module_ml_calc_tooltip_monthly_expenses'])) {
            $data['module_ml_calc_tooltip_monthly_expenses'] = $this->request->post['module_ml_calc_tooltip_monthly_expenses'];
        } else {
            $tooltip_data = $this->config->get('module_ml_calc_tooltip_monthly_expenses');
            if (is_array($tooltip_data)) {
                $data['module_ml_calc_tooltip_monthly_expenses'] = $tooltip_data;
            } else {
                $data['module_ml_calc_tooltip_monthly_expenses'] = array();
            }
        }

        // Result calculation tooltips - статус
        if (isset($this->request->post['module_ml_calc_show_result_tooltips'])) {
            $data['module_ml_calc_show_result_tooltips'] = $this->request->post['module_ml_calc_show_result_tooltips'];
        } else {
            $data['module_ml_calc_show_result_tooltips'] = $this->config->get('module_ml_calc_show_result_tooltips');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/ml_calc', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/ml_calc')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function install() {
        // Создаем таблицу статистики
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

        // Добавляем новые колонки, если таблица уже существует (для обновления с предыдущих версий)
        $columns_to_add = array(
            'changed_parameter' => 'ADD COLUMN `changed_parameter` varchar(50) DEFAULT NULL AFTER `ip_address`',
            'product_price' => 'ADD COLUMN `product_price` decimal(15,4) DEFAULT NULL AFTER `changed_parameter`',
            'clients_per_day' => 'ADD COLUMN `clients_per_day` int(11) DEFAULT NULL AFTER `product_price`',
            'procedure_cost' => 'ADD COLUMN `procedure_cost` decimal(15,4) DEFAULT NULL AFTER `clients_per_day`',
            'working_days' => 'ADD COLUMN `working_days` int(11) DEFAULT NULL AFTER `procedure_cost`',
            'rent' => 'ADD COLUMN `rent` decimal(15,4) DEFAULT NULL AFTER `working_days`',
            'utilities' => 'ADD COLUMN `utilities` decimal(15,4) DEFAULT NULL AFTER `rent`',
            'master_percent' => 'ADD COLUMN `master_percent` decimal(5,2) DEFAULT NULL AFTER `utilities`',
            'payback_months' => 'ADD COLUMN `payback_months` decimal(10,2) DEFAULT NULL AFTER `master_percent`',
            'payback_months_regular' => 'ADD COLUMN `payback_months_regular` decimal(10,2) DEFAULT NULL AFTER `payback_months`',
            'value_old' => 'ADD COLUMN `value_old` decimal(15,4) DEFAULT NULL AFTER `payback_months_regular`',
            'value_new' => 'ADD COLUMN `value_new` decimal(15,4) DEFAULT NULL AFTER `value_old`'
        );

        foreach ($columns_to_add as $column => $alter_sql) {
            $check_query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "ml_calc_statistics` LIKE '" . $column . "'");
            if (!$check_query->num_rows) {
                $this->db->query("ALTER TABLE `" . DB_PREFIX . "ml_calc_statistics` " . $alter_sql);
            }
        }

        // Автоматическая установка прав для группы Administrator
        $this->load->model('user/user_group');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/ml_calc');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/ml_calc');

        // JavaScript подключается автоматически через OCMOD модификацию в install.xml
    }

    public function statistics() {
        $this->load->language('extension/module/ml_calc');

        $this->ensureStatisticsSchema();

        $this->document->setTitle($this->language->get('heading_title') . ' - ' . $this->language->get('text_statistics'));

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_statistics'] = $this->language->get('text_statistics');
        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['column_calc_number'] = $this->language->get('column_calc_number');
        $data['column_product'] = $this->language->get('column_product');
        $data['column_changed_parameter'] = $this->language->get('column_changed_parameter');
        $data['column_value_old'] = $this->language->get('column_value_old');
        $data['column_value_new'] = $this->language->get('column_value_new');
        $data['column_payback_special'] = $this->language->get('column_payback_special');
        $data['column_payback_regular'] = $this->language->get('column_payback_regular');
        $data['column_clients_per_day'] = $this->language->get('column_clients_per_day');
        $data['column_procedure_cost'] = $this->language->get('column_procedure_cost');
        $data['column_working_days'] = $this->language->get('column_working_days');
        $data['column_rent'] = $this->language->get('column_rent');
        $data['column_utilities'] = $this->language->get('column_utilities');
        $data['column_master_percent'] = $this->language->get('column_master_percent');
        $data['column_ip'] = $this->language->get('column_ip');
        $data['column_date'] = $this->language->get('column_date');
        $data['button_clear'] = $this->language->get('button_clear');
        $data['button_back'] = $this->language->get('button_back');
        $data['button_export_xls'] = $this->language->get('button_export_xls');
        $data['button_delete_selected'] = $this->language->get('button_delete_selected');
        $data['text_confirm_clear'] = $this->language->get('text_confirm_clear');
        $data['text_confirm_delete_selected'] = $this->language->get('text_confirm_delete_selected');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/ml_calc', 'token=' . $this->session->data['token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_statistics'),
            'href' => $this->url->link('extension/module/ml_calc/statistics', 'token=' . $this->session->data['token'], true)
        );

        $data['back'] = $this->url->link('extension/module/ml_calc', 'token=' . $this->session->data['token'], true);
        $data['clear'] = $this->url->link('extension/module/ml_calc/clearStatistics', 'token=' . $this->session->data['token'], true);
        $data['export_xls'] = $this->url->link('extension/module/ml_calc/exportXls', 'token=' . $this->session->data['token'], true);
        $data['delete_selected_action'] = $this->url->link('extension/module/ml_calc/deleteSelectedStatistics', 'token=' . $this->session->data['token'], true);

        // Пагинация
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $limit = 50;
        $offset = ($page - 1) * $limit;
        $catalog_base = '';
        if (defined('HTTP_CATALOG')) {
            $catalog_base = rtrim(HTTP_CATALOG, '/');
        } else {
            $catalog_base = rtrim($this->config->get('config_url'), '/');
        }

        // Получаем общее количество записей
        $count_query = $this->db->query("
            SELECT COUNT(*) as total
            FROM `" . DB_PREFIX . "ml_calc_statistics`
        ");

        $total_records = (int)$count_query->row['total'];

        // Получаем статистику из БД с пагинацией
        $query = $this->db->query("
            SELECT *
            FROM `" . DB_PREFIX . "ml_calc_statistics`
            ORDER BY `date_added` DESC
            LIMIT " . (int)$limit . " OFFSET " . (int)$offset . "
        ");

        // Нумерация по всем расчетам (группы product_id + ip), порядок от старых к новым
        $group_number_map = array();
        $group_query = $this->db->query("
            SELECT `product_id`, `ip_address`, MIN(`date_added`) AS first_date
            FROM `" . DB_PREFIX . "ml_calc_statistics`
            GROUP BY `product_id`, `ip_address`
            ORDER BY first_date ASC
        ");
        $counter = 1;
        foreach ($group_query->rows as $g_row) {
            $key = (int)$g_row['product_id'] . '_' . trim($g_row['ip_address']);
            $group_number_map[$key] = $counter++;
        }
        $next_number = $counter;

        // Словарь для перевода названий параметров
        $parameter_names = array(
            'product_price' => $this->language->get('column_product_price'),
            'clients_per_day' => $this->language->get('column_clients_per_day'),
            'procedure_cost' => $this->language->get('column_procedure_cost'),
            'working_days' => $this->language->get('column_working_days'),
            'rent' => $this->language->get('column_rent'),
            'utilities' => $this->language->get('column_utilities'),
            'master_percent' => $this->language->get('column_master_percent')
        );

        $formatValue = function($param, $value) {
            if ($value === null) {
                return '-';
            }

            if ($param === 'master_percent') {
                return rtrim(rtrim(number_format((float)$value, 2, '.', ' '), '0'), '.') . '%';
            }

            if (in_array($param, array('clients_per_day', 'working_days'), true)) {
                return (int)$value;
            }

            return number_format((float)$value, 0, '.', ' ');
        };
        $formatPayback = function($value) {
            if ($value === null) {
                return '-';
            }
            return number_format((float)$value, 1, '.', ' ') . ' мес';
        };

        $data['statistics'] = array();
        $group_index_map = array();
        foreach ($query->rows as $row) {
            // Получаем название измененного параметра
            $changed_param = $row['changed_parameter'];
            $changed_param_display = isset($parameter_names[$changed_param]) ? $parameter_names[$changed_param] : '-';
            $row_value_old = array_key_exists('value_old', $row) ? $row['value_old'] : null;
            $row_value_new = array_key_exists('value_new', $row) ? $row['value_new'] : null;
            $row_payback_special = array_key_exists('payback_months', $row) ? $row['payback_months'] : null;
            $row_payback_regular = array_key_exists('payback_months_regular', $row) ? $row['payback_months_regular'] : null;

            $current_group_key = (int)$row['product_id'] . '_' . trim($row['ip_address']);
            if (isset($group_number_map[$current_group_key])) {
                $calc_number = $group_number_map[$current_group_key];
            } else {
                $calc_number = $next_number++;
                $group_number_map[$current_group_key] = $calc_number;
            }
            if (!isset($group_index_map[$current_group_key])) {
                $group_index_map[$current_group_key] = ($calc_number !== null) ? ($calc_number % 2) : (count($group_index_map) % 2);
            }
            $group_class = 'stat-group-' . ($group_index_map[$current_group_key] % 2);

            $data['statistics'][] = array(
                'calc_group_key'    => $current_group_key,
                'calc_number'       => $calc_number,
                'id'                => isset($row['id']) ? (int)$row['id'] : 0,
                'product_url'       => $catalog_base . '/index.php?route=product/product&product_id=' . (int)$row['product_id'],
                'product_name'       => $row['product_name'],
                'changed_parameter'  => $changed_param,
                'changed_parameter_display' => $changed_param_display,
                'product_price'      => $row['product_price'] !== null ? number_format((float)$row['product_price'], 0, '.', ' ') : '-',
                'clients_per_day'    => $row['clients_per_day'] !== null ? $row['clients_per_day'] : '-',
                'procedure_cost'     => $row['procedure_cost'] !== null ? number_format((float)$row['procedure_cost'], 0, '.', ' ') : '-',
                'working_days'       => $row['working_days'] !== null ? $row['working_days'] : '-',
                'rent'               => $row['rent'] !== null ? number_format((float)$row['rent'], 0, '.', ' ') : '-',
                'utilities'          => $row['utilities'] !== null ? number_format((float)$row['utilities'], 0, '.', ' ') : '-',
                'master_percent'     => $row['master_percent'] !== null ? $formatValue('master_percent', $row['master_percent']) : '-',
                'payback_special'    => $formatPayback($row_payback_special),
                'payback_regular'    => $formatPayback($row_payback_regular),
                'value_old'          => $formatValue($changed_param, $row_value_old),
                'value_new'          => $formatValue($changed_param, $row_value_new),
                'group_class'        => $group_class,
                'ip_address'         => $row['ip_address'],
                'date_added'         => date('d.m.Y H:i', strtotime($row['date_added']))
            );
        }

        // calc_number уже определен заранее

        $data['total'] = $total_records;

        // Пагинация
        $pagination = new Pagination();
        $pagination->total = $total_records;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('extension/module/ml_calc/statistics', 'token=' . $this->session->data['token'] . '&page={page}', true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($total_records) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($total_records - $limit)) ? $total_records : ((($page - 1) * $limit) + $limit), $total_records, ceil($total_records / $limit));

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/ml_calc_statistics', $data));
    }

    public function deleteSelectedStatistics() {
        $this->load->language('extension/module/ml_calc');
        $this->ensureStatisticsSchema();

        if (!isset($this->request->get['token']) || $this->request->get['token'] != $this->session->data['token']) {
            $this->response->redirect($this->url->link('extension/module/ml_calc/statistics', 'token=' . $this->session->data['token'], true));
            return;
        }

        if ($this->request->server['REQUEST_METHOD'] === 'POST' && !empty($this->request->post['selected']) && is_array($this->request->post['selected'])) {
            $ids = array();
            foreach ($this->request->post['selected'] as $id) {
                $id = (int)$id;
                if ($id > 0) {
                    $ids[] = $id;
                }
            }

            if ($ids) {
                $this->db->query("DELETE FROM `" . DB_PREFIX . "ml_calc_statistics` WHERE `id` IN (" . implode(',', $ids) . ")");
                $this->session->data['success'] = $this->language->get('text_success');
            }
        }

        $this->response->redirect($this->url->link('extension/module/ml_calc/statistics', 'token=' . $this->session->data['token'], true));
    }

    public function clearStatistics() {
        $this->load->language('extension/module/ml_calc');

        $this->ensureStatisticsSchema();

        if (isset($this->request->get['token']) && $this->request->get['token'] == $this->session->data['token']) {
            $this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "ml_calc_statistics`");

            $this->session->data['success'] = $this->language->get('text_statistics_cleared');
        }

        $this->response->redirect($this->url->link('extension/module/ml_calc/statistics', 'token=' . $this->session->data['token'], true));
    }

    public function exportXls() {
        $this->load->language('extension/module/ml_calc');

        $this->ensureStatisticsSchema();

        if (!isset($this->request->get['token']) || $this->request->get['token'] != $this->session->data['token']) {
            $this->response->redirect($this->url->link('extension/module/ml_calc/statistics', 'token=' . $this->session->data['token'], true));
            return;
        }

        // Получаем все записи из БД
        $query = $this->db->query("
            SELECT *
            FROM `" . DB_PREFIX . "ml_calc_statistics`
            ORDER BY `date_added` DESC
        ");

        // Нумерация по всем расчетам (группы product_id + ip), порядок от старых к новым
        $groupNumberMap = array();
        $groupQuery = $this->db->query("
            SELECT `product_id`, `ip_address`, MIN(`date_added`) AS first_date
            FROM `" . DB_PREFIX . "ml_calc_statistics`
            GROUP BY `product_id`, `ip_address`
            ORDER BY first_date ASC
        ");
        $counter = 1;
        foreach ($groupQuery->rows as $gRow) {
            $key = (int)$gRow['product_id'] . '_' . trim($gRow['ip_address']);
            $groupNumberMap[$key] = $counter++;
        }
        $nextNumber = $counter;

        // Формируем XLS (HTML таблица с заголовком Excel)
        $output = '<!DOCTYPE html>';
        $output .= '<html>';
        $output .= '<head>';
        $output .= '<meta charset="UTF-8">';
        $output .= '<style>table { border-collapse: collapse; } th, td { border: 1px solid #000; padding: 5px; text-align: left; } td.number { text-align: right; }</style>';
        $output .= '</head>';
        $output .= '<body>';
        $output .= '<table>';
        $output .= '<thead>';
        $output .= '<tr>';
        $output .= '<th>' . $this->language->get('column_calc_number') . '</th>';
        $output .= '<th>' . $this->language->get('column_product') . '</th>';
        $output .= '<th>' . $this->language->get('column_changed_parameter') . '</th>';
        $output .= '<th>' . $this->language->get('column_value_old') . '</th>';
        $output .= '<th>' . $this->language->get('column_value_new') . '</th>';
        $output .= '<th>' . $this->language->get('column_payback_special') . '</th>';
        $output .= '<th>' . $this->language->get('column_payback_regular') . '</th>';
        $output .= '<th>' . $this->language->get('column_clients_per_day') . '</th>';
        $output .= '<th>' . $this->language->get('column_procedure_cost') . '</th>';
        $output .= '<th>' . $this->language->get('column_working_days') . '</th>';
        $output .= '<th>' . $this->language->get('column_rent') . '</th>';
        $output .= '<th>' . $this->language->get('column_utilities') . '</th>';
        $output .= '<th>' . $this->language->get('column_master_percent') . '</th>';
        $output .= '<th>' . $this->language->get('column_ip') . '</th>';
        $output .= '<th>' . $this->language->get('column_date') . '</th>';
        $output .= '</tr>';
        $output .= '</thead>';
        $output .= '<tbody>';

        // Словарь для перевода названий параметров
        $parameter_names = array(
            'product_price' => $this->language->get('column_product_price'),
            'clients_per_day' => $this->language->get('column_clients_per_day'),
            'procedure_cost' => $this->language->get('column_procedure_cost'),
            'working_days' => $this->language->get('column_working_days'),
            'rent' => $this->language->get('column_rent'),
            'utilities' => $this->language->get('column_utilities'),
            'master_percent' => $this->language->get('column_master_percent')
        );

        $formatValue = function($param, $value) {
            if ($value === null) {
                return '-';
            }

            if ($param === 'master_percent') {
                return rtrim(rtrim(number_format((float)$value, 2, '.', ' '), '0'), '.') . '%';
            }

            if (in_array($param, array('clients_per_day', 'working_days'), true)) {
                return (int)$value;
            }

            return number_format((float)$value, 0, '.', ' ');
        };
        $formatPayback = function($value) {
            if ($value === null) {
                return '-';
            }

            return number_format((float)$value, 1, '.', ' ') . ' мес';
        };

        $groupIndexMap = array();
        $groupIndexCounter = 0;
        $rowsBuffer = array();

        foreach ($query->rows as $row) {
            $changed_param = $row['changed_parameter'];
            $changed_param_display = isset($parameter_names[$changed_param]) ? $parameter_names[$changed_param] : '-';
            $row_value_old = array_key_exists('value_old', $row) ? $row['value_old'] : null;
            $row_value_new = array_key_exists('value_new', $row) ? $row['value_new'] : null;
            $row_payback_special = array_key_exists('payback_months', $row) ? $row['payback_months'] : null;
            $row_payback_regular = array_key_exists('payback_months_regular', $row) ? $row['payback_months_regular'] : null;

            $currentGroupKey = (int)$row['product_id'] . '_' . trim($row['ip_address']);
            if (isset($groupNumberMap[$currentGroupKey])) {
                $calcNumber = $groupNumberMap[$currentGroupKey];
            } else {
                $calcNumber = $nextNumber++;
                $groupNumberMap[$currentGroupKey] = $calcNumber;
            }
            if (!isset($groupIndexMap[$currentGroupKey])) {
                $groupIndexMap[$currentGroupKey] = $groupIndexCounter++;
            }

            $rowsBuffer[] = array(
                'calc_number' => $calcNumber,
                'product_name' => $row['product_name'],
                'changed_param_display' => $changed_param_display,
                'changed_param' => $changed_param,
                'row_value_old' => $row_value_old,
                'row_value_new' => $row_value_new,
                'payback_special' => $row_payback_special,
                'payback_regular' => $row_payback_regular,
                'clients_per_day' => $row['clients_per_day'],
                'procedure_cost' => $row['procedure_cost'],
                'working_days' => $row['working_days'],
                'rent' => $row['rent'],
                'utilities' => $row['utilities'],
                'master_percent' => $row['master_percent'],
                'ip_address' => $row['ip_address'],
                'date_added' => $row['date_added']
            );
        }

        foreach ($rowsBuffer as $row) {
            $output .= '<tr>';
            $output .= '<td class="number">' . $row['calc_number'] . '</td>';
            $output .= '<td>' . htmlspecialchars($row['product_name'], ENT_QUOTES, 'UTF-8') . '</td>';
            $output .= '<td>' . htmlspecialchars($row['changed_param_display'], ENT_QUOTES, 'UTF-8') . '</td>';
            $output .= '<td class="number">' . $formatValue($row['changed_param'], $row['row_value_old']) . '</td>';
            $output .= '<td class="number">' . $formatValue($row['changed_param'], $row['row_value_new']) . '</td>';
            $output .= '<td class="number">' . $formatPayback($row['payback_special']) . '</td>';
            $output .= '<td class="number">' . $formatPayback($row['payback_regular']) . '</td>';
            $output .= '<td class="number">' . ($row['clients_per_day'] !== null ? $row['clients_per_day'] : '-') . '</td>';
            $output .= '<td class="number">' . ($row['procedure_cost'] !== null ? number_format((float)$row['procedure_cost'], 0, '.', ' ') : '-') . '</td>';
            $output .= '<td class="number">' . ($row['working_days'] !== null ? $row['working_days'] : '-') . '</td>';
            $output .= '<td class="number">' . ($row['rent'] !== null ? number_format((float)$row['rent'], 0, '.', ' ') : '-') . '</td>';
            $output .= '<td class="number">' . ($row['utilities'] !== null ? number_format((float)$row['utilities'], 0, '.', ' ') : '-') . '</td>';
            $output .= '<td class="number">' . ($row['master_percent'] !== null ? $formatValue('master_percent', $row['master_percent']) : '-') . '</td>';
            $output .= '<td>' . htmlspecialchars($row['ip_address'], ENT_QUOTES, 'UTF-8') . '</td>';
            $output .= '<td>' . date('d.m.Y H:i', strtotime($row['date_added'])) . '</td>';
            $output .= '</tr>';
        }

        $output .= '</tbody>';
        $output .= '</table>';
        $output .= '</body>';
        $output .= '</html>';

        // Отправляем заголовки для скачивания как XLS
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="ml_calc_statistics_' . date('Y-m-d_H-i-s') . '.xls"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');

        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo $output;
        exit;
    }

    private function ensureStatisticsSchema() {
        static $schemaChecked = false;

        if ($schemaChecked) {
            return;
        }

        $schemaChecked = true;

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
            'changed_parameter' => 'ADD COLUMN `changed_parameter` varchar(50) DEFAULT NULL AFTER `ip_address`',
            'product_price' => 'ADD COLUMN `product_price` decimal(15,4) DEFAULT NULL AFTER `changed_parameter`',
            'clients_per_day' => 'ADD COLUMN `clients_per_day` int(11) DEFAULT NULL AFTER `product_price`',
            'procedure_cost' => 'ADD COLUMN `procedure_cost` decimal(15,4) DEFAULT NULL AFTER `clients_per_day`',
            'working_days' => 'ADD COLUMN `working_days` int(11) DEFAULT NULL AFTER `procedure_cost`',
            'rent' => 'ADD COLUMN `rent` decimal(15,4) DEFAULT NULL AFTER `working_days`',
            'utilities' => 'ADD COLUMN `utilities` decimal(15,4) DEFAULT NULL AFTER `rent`',
            'master_percent' => 'ADD COLUMN `master_percent` decimal(5,2) DEFAULT NULL AFTER `utilities`',
            'payback_months' => 'ADD COLUMN `payback_months` decimal(10,2) DEFAULT NULL AFTER `master_percent`',
            'payback_months_regular' => 'ADD COLUMN `payback_months_regular` decimal(10,2) DEFAULT NULL AFTER `payback_months`',
            'value_old' => 'ADD COLUMN `value_old` decimal(15,4) DEFAULT NULL AFTER `master_percent`',
            'value_new' => 'ADD COLUMN `value_new` decimal(15,4) DEFAULT NULL AFTER `value_old`'
        );

        foreach ($columnsToAdd as $column => $alterSql) {
            $checkQuery = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "ml_calc_statistics` LIKE '" . $column . "'");
            if (!$checkQuery->num_rows) {
                $this->db->query("ALTER TABLE `" . DB_PREFIX . "ml_calc_statistics` " . $alterSql);
            }
        }
    }

    public function uninstall() {
        // Удаление данных при деинсталляции
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_ml_calc');

        // Удаление таблицы статистики
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ml_calc_statistics`");
    }
}
