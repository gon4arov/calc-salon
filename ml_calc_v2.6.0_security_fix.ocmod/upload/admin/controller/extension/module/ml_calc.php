<?php
class ControllerExtensionModuleMlCalc extends Controller {
    private $error = array();

    const VERSION = '2.6.0';

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

        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_show_regular_payback'] = $this->language->get('entry_show_regular_payback');
        $data['help_show_regular_payback'] = $this->language->get('help_show_regular_payback');
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
        // Автоматическая установка прав для группы Administrator
        $this->load->model('user/user_group');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/ml_calc');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/ml_calc');

        // JavaScript подключается автоматически через OCMOD модификацию в install.xml
    }

    public function uninstall() {
        // Удаление данных при деинсталляции
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_ml_calc');
    }
}
