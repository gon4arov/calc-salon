<?php
class ControllerExtensionModuleMlSalonCalc extends Controller {
    const VERSION = '1.0.28';

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

        // Каталог аппаратов из БД (jan = 1)
        $this->load->model('catalog/product');
        $language_id = (int)$this->config->get('config_language_id');
        $products_query = $this->db->query("
            SELECT p.product_id, p.price, p.tax_class_id, pd.name
            FROM `" . DB_PREFIX . "product` p
            LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id)
            WHERE p.status = '1' AND p.jan = '1' AND pd.language_id = '" . $language_id . "'
            ORDER BY pd.name ASC
        ");

        $devices = array();
        $current_currency = isset($this->session->data['currency']) ? $this->session->data['currency'] : $this->config->get('config_currency');
        $customer_group_id = (int)$this->config->get('config_customer_group_id');
        $now_date = date('Y-m-d');
        $product_ids = array();

        foreach ($products_query->rows as $row) {
            $price = isset($row['price']) ? (float)$row['price'] : 0;
            $special = 0.0;
            $product_ids[] = (int)$row['product_id'];

            // Получаем активную спеццену
            $special_query = $this->db->query("
                SELECT price FROM `" . DB_PREFIX . "product_special`
                WHERE product_id = '" . (int)$row['product_id'] . "'
                  AND customer_group_id = '" . $customer_group_id . "'
                  AND (date_start = '0000-00-00' OR date_start <= '" . $this->db->escape($now_date) . "')
                  AND (date_end = '0000-00-00' OR date_end >= '" . $this->db->escape($now_date) . "')
                ORDER BY priority ASC, price ASC
                LIMIT 1
            ");
            if ($special_query->num_rows) {
                $special = (float)$special_query->row['price'];
            }
            $cost_base = $special > 0 ? $special : $price;
            $cost_taxed = $this->tax->calculate($cost_base, $row['tax_class_id'], $this->config->get('config_tax'));
            $cost_raw = $this->currency->format($cost_taxed, $current_currency, '', false); // число в текущей валюте
            $cost_formatted = $this->currency->format($cost_taxed, $current_currency);      // строка с символом

            $devices[] = array(
                'id' => (string)$row['product_id'],
                'name' => $row['name'],
                'cost' => $cost_formatted,
                'cost_raw' => $cost_raw,
                'price' => 0, // пользователь задает цену услуги вручную
                'clients' => 0,
                'tags' => array()
            );
        }

        // Подтягиваем процедуры только из OCFilter (ocfilter_* таблицы)
        $procedures = array();
        if (!empty($product_ids)) {
            $product_ids_sql = implode(',', array_map('intval', $product_ids));

            $ocfilterExists = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "ocfilter_filter_value_to_product'");
            if ($ocfilterExists->num_rows) {
                // 1. Ищем группу (пары filter_id + source)
                $filter_cond_parts = array();
                $filterNames = $this->db->query("
                    SELECT DISTINCT fd.filter_id, fd.source
                    FROM `" . DB_PREFIX . "ocfilter_filter_description` fd
                    WHERE (LOWER(fd.name) LIKE '%види процедур%' OR LOWER(fd.name) LIKE '%виды процедур%')
                ");
                
                foreach ($filterNames->rows as $row) {
                    $filter_cond_parts[] = "(filter_id = '" . (int)$row['filter_id'] . "' AND source = '" . (int)$row['source'] . "')";
                }

                // Fallback (ID 2), если ничего не нашли
                if (empty($filter_cond_parts)) {
                     // Пытаемся найти source для ID 2
                     $check2 = $this->db->query("SELECT source FROM `" . DB_PREFIX . "ocfilter_filter_description` WHERE filter_id = '2' AND (name LIKE '%процедур%' OR name LIKE '%rocedur%') LIMIT 1");
                     if ($check2->num_rows) {
                         $filter_cond_parts[] = "(filter_id = '2' AND source = '" . (int)$check2->row['source'] . "')";
                     }
                }

                if (!empty($filter_cond_parts)) {
                    $filter_where = " AND (" . implode(" OR ", $filter_cond_parts) . ")";
                    
                    // 2. Значения (фильтруем по id+source)
                    $procedure_values = $this->db->query("
                        SELECT DISTINCT fvd.value_id, fvd.name, fvd.language_id
                        FROM `" . DB_PREFIX . "ocfilter_filter_value_description` fvd
                        WHERE 1 " . $filter_where . "
                        ORDER BY (fvd.language_id = '" . (int)$language_id . "') DESC, fvd.name ASC
                    ");

                    // Убираем дубли языков (берем первый, т.к. отсортировано по language_id)
                    $temp_procedures = array();
                    $value_ids = array();
                    foreach ($procedure_values->rows as $row) {
                        $vid = (int)$row['value_id'];
                        if (!isset($temp_procedures[$vid])) {
                            $temp_procedures[$vid] = array(
                                'id' => 'ocf_' . $vid,
                                'name' => $row['name']
                            );
                            $value_ids[] = $vid;
                        }
                    }
                    $procedures = array_values($temp_procedures);

                    // 3. Привязки к товарам (фильтруем по product_id и (filter_id+source))
                    if (!empty($value_ids)) {
                        $filter_where_fvp = str_replace(array('filter_id', 'source'), array('fvp.filter_id', 'fvp.source'), $filter_where);
                        // Дополнительно фильтруем по value_id, чтобы не зацепить лишнее (хотя filter+source уже должны ограничить)
                        $value_ids_sql = implode(',', $value_ids);
                        
                        $ocfBindings = $this->db->query("
                            SELECT fvp.product_id, fvp.value_id
                            FROM `" . DB_PREFIX . "ocfilter_filter_value_to_product` fvp
                            WHERE fvp.product_id IN (" . $product_ids_sql . ")
                              " . $filter_where_fvp . "
                              AND fvp.value_id IN (" . $value_ids_sql . ")
                        ");

                        $device_tags = array();
                        foreach ($ocfBindings->rows as $row) {
                            $pid = (int)$row['product_id'];
                            if (!isset($device_tags[$pid])) {
                                $device_tags[$pid] = array();
                            }
                            $device_tags[$pid][] = 'ocf_' . (string)$row['value_id'];
                        }

                        foreach ($devices as &$dev) {
                            $pid = (int)$dev['id'];
                            if (isset($device_tags[$pid])) {
                                $dev['tags'] = $device_tags[$pid];
                            }
                        }
                        unset($dev);
                    }
                }

                // Если в OCFilter ничего не нашли, пробуем классический OcFilter-опции (ocfilter_option_* таблицы)
                if (empty($procedures)) {
                    $optExists = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "ocfilter_option_value_to_product'");
                    if ($optExists->num_rows) {
                        $option_ids = array();
                        $optionRows = $this->db->query("
                            SELECT DISTINCT option_id
                            FROM `" . DB_PREFIX . "ocfilter_option_description`
                            WHERE LOWER(name) LIKE '%процедур%'
                        ");
                        foreach ($optionRows->rows as $row) {
                            $option_ids[] = (int)$row['option_id'];
                        }
                        if (empty($option_ids)) {
                            $option_ids[] = 2;
                        }
                        $option_ids_sql = implode(',', $option_ids);

                        $valueRows = $this->db->query("
                            SELECT DISTINCT ovd.option_id, ovd.value_id, ovd.name, ovd.language_id
                            FROM `" . DB_PREFIX . "ocfilter_option_value_description` ovd
                            WHERE ovd.option_id IN (" . $option_ids_sql . ")
                            ORDER BY (ovd.language_id = '" . (int)$language_id . "') DESC, ovd.name ASC
                        ");

                        $value_filter_sql2 = '';
                        if ($valueRows->num_rows) {
                            $value_ids = array();
                            foreach ($valueRows->rows as $row) {
                                $value_ids[] = (int)$row['value_id'];
                            }
                            $value_filter_sql2 = " AND ovp.value_id IN (" . implode(',', $value_ids) . ")";
                        }

                        $optBindings = $this->db->query("
                            SELECT ovp.product_id, ovp.value_id
                            FROM `" . DB_PREFIX . "ocfilter_option_value_to_product` ovp
                            WHERE ovp.product_id IN (" . $product_ids_sql . ")
                              AND ovp.option_id IN (" . $option_ids_sql . ")
                              " . $value_filter_sql2 . "
                        ");

                        $device_tags = array();
                        foreach ($optBindings->rows as $row) {
                            $pid = (int)$row['product_id'];
                            if (!isset($device_tags[$pid])) {
                                $device_tags[$pid] = array();
                            }
                            $device_tags[$pid][] = 'ocf_' . (string)$row['value_id'];
                        }
                        foreach ($devices as &$dev) {
                            $pid = (int)$dev['id'];
                            if (isset($device_tags[$pid])) {
                                $dev['tags'] = $device_tags[$pid];
                            }
                        }
                        unset($dev);

                        foreach ($valueRows->rows as $row) {
                            $procedures[] = array(
                                'id' => 'ocf_' . (string)$row['value_id'],
                                'name' => $row['name']
                            );
                        }
                    }
                }
            }
        }

        // Пресеты формируем из доступных устройств (jan=1), чтобы ID совпадали
        $device_ids = array();
        foreach ($devices as $d) {
            $device_ids[] = $d['id'];
        }
        $presets = array();
        if (!empty($device_ids)) {
            $presets[] = array(
                'id' => 'solo',
                'name' => 'Одиночный мастер (1 аппарат)',
                'devices' => array_slice($device_ids, 0, 1),
                'working_days' => 22,
                'rent' => 12000,
                'utilities' => 3000
            );
            $presets[] = array(
                'id' => 'starter',
                'name' => 'Салон старт (до 3 аппаратов)',
                'devices' => array_slice($device_ids, 0, min(3, count($device_ids))),
                'working_days' => 24,
                'rent' => 20000,
                'utilities' => 6000
            );
            $presets[] = array(
                'id' => 'pro',
                'name' => 'Серьезный салон (до 5 аппаратов)',
                'devices' => array_slice($device_ids, 0, min(5, count($device_ids))),
                'working_days' => 26,
                'rent' => 35000,
                'utilities' => 9000
            );
        }

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

        // В отладочном режиме можно увидеть собранные процедуры/связки
        if (isset($this->request->get['debug'])) {
            header('Content-Type: text/plain; charset=utf-8');
            echo "ml_salon_calc version: " . self::VERSION . "\n\n";
            echo "Devices (" . count($devices) . "):\n";
            print_r($devices);
            echo "\nProcedures (" . count($procedures) . "):\n";
            print_r($procedures);
            if (isset($filter_ids)) {
                echo "\nFilter IDs used: " . implode(',', $filter_ids) . "\n";
            }
            if (isset($option_ids)) {
                echo "\nOption IDs used: " . implode(',', $option_ids) . "\n";
            }
            exit;
        }

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
