<?php
class ControllerExtensionModuleMlSalonCalc extends Controller {
    const VERSION = '1.0.3';

    public function index() {
        $this->load->language('extension/module/ml_salon_calc');

        $data['heading_title']       = $this->language->get('heading_title');
        $data['text_intro']          = $this->language->get('text_intro');
        $data['text_presets']        = $this->language->get('text_presets');
        $data['text_procedures']     = $this->language->get('text_procedures');
        $data['text_devices']        = $this->language->get('text_devices');
        $data['text_totals']         = $this->language->get('text_totals');
        $data['text_email']          = $this->language->get('text_email');
        $data['text_procedure_hint'] = $this->language->get('text_procedure_hint');
        $data['text_device_hint']    = $this->language->get('text_device_hint');
        $data['text_global_hint']    = $this->language->get('text_global_hint');
        $data['text_months']         = $this->language->get('text_months');
        $data['text_currency']       = $this->language->get('text_currency');

        $data['label_working_days']  = $this->language->get('label_working_days');
        $data['label_rent']          = $this->language->get('label_rent');
        $data['label_utilities']     = $this->language->get('label_utilities');
        $data['label_clients']       = $this->language->get('label_clients');
        $data['label_price']         = $this->language->get('label_price');
        $data['label_cost']          = $this->language->get('label_cost');
        $data['label_revenue']       = $this->language->get('label_revenue');
        $data['label_payback']       = $this->language->get('label_payback');
        $data['label_email']         = $this->language->get('label_email');
        $data['label_add_device']    = $this->language->get('label_add_device');

        $data['button_apply_preset'] = $this->language->get('button_apply_preset');
        $data['button_add_device']   = $this->language->get('button_add_device');
        $data['button_send']         = $this->language->get('button_send');

        $data['column_device']       = $this->language->get('column_device');
        $data['column_clients']      = $this->language->get('column_clients');
        $data['column_price']        = $this->language->get('column_price');
        $data['column_cost']         = $this->language->get('column_cost');
        $data['column_revenue']      = $this->language->get('column_revenue');
        $data['column_actions']      = $this->language->get('column_actions');

        $data['text_total_capex']    = $this->language->get('text_total_capex');
        $data['text_total_revenue']  = $this->language->get('text_total_revenue');
        $data['text_total_profit']   = $this->language->get('text_total_profit');
        $data['text_total_payback']  = $this->language->get('text_total_payback');

        $data['text_email_success']  = $this->language->get('text_email_success');
        $data['text_email_error']    = $this->language->get('text_email_error');
        $data['error_email_required'] = $this->language->get('error_email_required');
        $data['error_email_invalid'] = $this->language->get('error_email_invalid');

        $data['action_send'] = $this->url->link('extension/module/ml_salon_calc/sendEmail', '', true);

        // Справочник процедур
        $procedures = array(
            array('id' => 'hair_removal', 'name' => 'Удаление волос'),
            array('id' => 'rejuvenation', 'name' => 'Омоложение/лифтинг'),
            array('id' => 'vascular', 'name' => 'Сосудистые/пигмент'),
            array('id' => 'acne', 'name' => 'Акне/подростковая кожа'),
            array('id' => 'body', 'name' => 'Коррекция фигуры/липосакция'),
            array('id' => 'peeling', 'name' => 'Пилинги/фракционная шлифовка')
        );

        // Каталог аппаратов (примерный список)
        $devices = array(
            array(
                'id' => 'diode_basic',
                'name' => 'Диодный лазер 808 нм',
                'cost' => 350000,
                'price' => 900,
                'clients' => 6,
                'tags' => array('hair_removal')
            ),
            array(
                'id' => 'el_light',
                'name' => 'E-light (IPL+RF)',
                'cost' => 280000,
                'price' => 800,
                'clients' => 5,
                'tags' => array('hair_removal', 'vascular', 'acne')
            ),
            array(
                'id' => 'rf_bipolar',
                'name' => 'RF лифтинг (би-/монополярный)',
                'cost' => 240000,
                'price' => 1200,
                'clients' => 4,
                'tags' => array('rejuvenation')
            ),
            array(
                'id' => 'hifu',
                'name' => 'HIFU SMAS-лифтинг',
                'cost' => 450000,
                'price' => 2500,
                'clients' => 2,
                'tags' => array('rejuvenation')
            ),
            array(
                'id' => 'co2_fractional',
                'name' => 'CO2 фракционный лазер',
                'cost' => 520000,
                'price' => 3000,
                'clients' => 1.5,
                'tags' => array('peeling', 'rejuvenation', 'acne')
            ),
            array(
                'id' => 'coolsculpt',
                'name' => 'Криолиполиз/липолазер',
                'cost' => 390000,
                'price' => 3200,
                'clients' => 1.2,
                'tags' => array('body')
            ),
            array(
                'id' => 'pressotherapy',
                'name' => 'Прессотерапия/лимфодренаж',
                'cost' => 120000,
                'price' => 600,
                'clients' => 4,
                'tags' => array('body')
            )
        );

        // Пресеты
        $presets = array(
            array(
                'id' => 'solo',
                'name' => 'Одиночный мастер (1 аппарат)',
                'devices' => array('diode_basic'),
                'working_days' => 22,
                'rent' => 12000,
                'utilities' => 3000
            ),
            array(
                'id' => 'starter',
                'name' => 'Салон старт (2-3 аппарата)',
                'devices' => array('diode_basic', 'rf_bipolar', 'el_light'),
                'working_days' => 24,
                'rent' => 20000,
                'utilities' => 6000
            ),
            array(
                'id' => 'pro',
                'name' => 'Серьезный салон (5+ аппаратов)',
                'devices' => array('diode_basic', 'rf_bipolar', 'el_light', 'co2_fractional', 'coolsculpt'),
                'working_days' => 26,
                'rent' => 35000,
                'utilities' => 9000
            )
        );

        $data['ml_salon_calc'] = array(
            'version' => self::VERSION,
            'presets' => $presets,
            'procedures' => $procedures,
            'devices' => $devices,
            'defaults' => array(
                'working_days' => 24,
                'rent' => 18000,
                'utilities' => 5000
            )
        );

        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->addStyle('catalog/view/theme/default/template/extension/module/css/ml_salon_calc.css');
        $this->document->addScript('catalog/view/javascript/ml_salon_calc.js');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/ml_salon_calc', $data));
    }

    public function sendEmail() {
        $this->load->language('extension/module/ml_salon_calc');
        $json = array('success' => false);

        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $json['error'] = 'Invalid request';
            return $this->respondJson($json);
        }

        $email = isset($this->request->post['email']) ? trim($this->request->post['email']) : '';
        $payload = isset($this->request->post['payload']) ? trim($this->request->post['payload']) : '';

        if ($email === '') {
            $json['error'] = $this->language->get('error_email_required');
            return $this->respondJson($json);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $json['error'] = $this->language->get('error_email_invalid');
            return $this->respondJson($json);
        }

        if ($payload === '') {
            $payload = 'No calculation payload supplied';
        }

        $store_email = $this->config->get('config_email');
        $subject = '[' . $this->config->get('config_name') . '] Salon payback calculation';
        $message = "Email: {$email}\n\n" . $payload;

        require_once(DIR_SYSTEM . 'library/mail.php');
        $mail = new Mail();
        $mail->protocol = $this->config->get('config_mail_protocol');
        $mail->parameter = $this->config->get('config_mail_parameter');
        $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
        $mail->smtp_username = $this->config->get('config_mail_smtp_username');
        $mail->smtp_password = $this->config->get('config_mail_smtp_password');
        $mail->smtp_port = $this->config->get('config_mail_smtp_port');
        $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

        $mail->setTo($store_email);
        $mail->setFrom($store_email);
        $mail->setSender($this->config->get('config_name'));
        $mail->setSubject($subject);
        $mail->setText($message);
        $mail->send();

        $json['success'] = true;
        return $this->respondJson($json);
    }

    private function respondJson($payload) {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($payload));
        return null;
    }
}
