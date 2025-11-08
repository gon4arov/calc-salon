<?php
class ControllerExtensionModuleMLCalcSalon extends Controller {
    private $error = array();

    const VERSION = '2.7.0';

    public function index() {
        $this->load->language('extension/module/ml_calc_salon');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_ml_calc_salon', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true));
        }

        // Заголовки
        $data['heading_title'] = $this->language->get('heading_title');
        $data['module_version'] = self::VERSION;
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_default_clients_per_day'] = $this->language->get('entry_default_clients_per_day');
        $data['entry_default_procedure_cost'] = $this->language->get('entry_default_procedure_cost');
        $data['entry_default_working_days'] = $this->language->get('entry_default_working_days');
        $data['entry_default_rent'] = $this->language->get('entry_default_rent');
        $data['entry_default_utilities'] = $this->language->get('entry_default_utilities');
        $data['entry_default_master_percent'] = $this->language->get('entry_default_master_percent');

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
            'href' => $this->url->link('extension/module/ml_calc_salon', 'token=' . $this->session->data['token'], true)
        );

        // Действия
        $data['action'] = $this->url->link('extension/module/ml_calc_salon', 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true);

        // Значения по умолчанию
        if (isset($this->request->post['module_ml_calc_salon_status'])) {
            $data['module_ml_calc_salon_status'] = $this->request->post['module_ml_calc_salon_status'];
        } else {
            $data['module_ml_calc_salon_status'] = $this->config->get('module_ml_calc_salon_status');
        }

        if (isset($this->request->post['module_ml_calc_salon_default_clients_per_day'])) {
            $data['module_ml_calc_salon_default_clients_per_day'] = $this->request->post['module_ml_calc_salon_default_clients_per_day'];
        } else {
            $data['module_ml_calc_salon_default_clients_per_day'] = $this->config->get('module_ml_calc_salon_default_clients_per_day') ?: 7;
        }

        if (isset($this->request->post['module_ml_calc_salon_default_procedure_cost'])) {
            $data['module_ml_calc_salon_default_procedure_cost'] = $this->request->post['module_ml_calc_salon_default_procedure_cost'];
        } else {
            $data['module_ml_calc_salon_default_procedure_cost'] = $this->config->get('module_ml_calc_salon_default_procedure_cost') ?: 1000;
        }

        if (isset($this->request->post['module_ml_calc_salon_default_working_days'])) {
            $data['module_ml_calc_salon_default_working_days'] = $this->request->post['module_ml_calc_salon_default_working_days'];
        } else {
            $data['module_ml_calc_salon_default_working_days'] = $this->config->get('module_ml_calc_salon_default_working_days') ?: 30;
        }

        if (isset($this->request->post['module_ml_calc_salon_default_rent'])) {
            $data['module_ml_calc_salon_default_rent'] = $this->request->post['module_ml_calc_salon_default_rent'];
        } else {
            $data['module_ml_calc_salon_default_rent'] = $this->config->get('module_ml_calc_salon_default_rent') ?: 8000;
        }

        if (isset($this->request->post['module_ml_calc_salon_default_utilities'])) {
            $data['module_ml_calc_salon_default_utilities'] = $this->request->post['module_ml_calc_salon_default_utilities'];
        } else {
            $data['module_ml_calc_salon_default_utilities'] = $this->config->get('module_ml_calc_salon_default_utilities') ?: 2000;
        }

        if (isset($this->request->post['module_ml_calc_salon_default_master_percent'])) {
            $data['module_ml_calc_salon_default_master_percent'] = $this->request->post['module_ml_calc_salon_default_master_percent'];
        } else {
            $data['module_ml_calc_salon_default_master_percent'] = $this->config->get('module_ml_calc_salon_default_master_percent') ?: 15;
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/ml_calc_salon', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/ml_calc_salon')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function install() {
        // Создаем таблицу для сохранения расчетов
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ml_calc_salon_calculations` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `share_token` varchar(32) NOT NULL,
            `calculation_data` TEXT NOT NULL,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `share_token` (`share_token`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");

        // Автоматическая установка прав для группы Administrator
        $this->load->model('user/user_group');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/ml_calc_salon');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/ml_calc_salon');
    }

    public function uninstall() {
        // Удаление данных при деинсталляции
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_ml_calc_salon');

        // Удаление таблицы (опционально - можно оставить данные)
        // $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ml_calc_salon_calculations`");
    }
}
