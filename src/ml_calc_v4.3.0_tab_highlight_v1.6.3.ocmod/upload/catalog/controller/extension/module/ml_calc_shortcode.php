<?php
class ControllerExtensionModuleMlCalcShortcode extends Controller {
    public function index($setting = array()) {
        if (!$this->config->get('module_ml_calc_status')) {
            return '<!-- DEBUG: Module disabled -->';
        }

        $this->load->language('extension/module/ml_calc');

        $data['text_select_product'] = $this->language->get('text_select_product');
        $data['text_no_products'] = $this->language->get('text_no_products');
        $data['text_loading_error'] = $this->language->get('text_loading_error');

        $products = $this->getAvailableProducts();
        $data['products'] = $products;

        // ОТЛАДКА: выводим количество найденных товаров

        if (isset($setting['show_selector'])) {
            $show_selector = (bool)$setting['show_selector'];
        } else {
            $show_selector = isset($this->request->get['show_selector']) ? (bool)$this->request->get['show_selector'] : true;
        }

        if (isset($setting['show_title'])) {
            $show_title = (bool)$setting['show_title'];
        } else {
            $show_title = isset($this->request->get['show_title']) ? (bool)$this->request->get['show_title'] : false;
        }

        // Підтримка багатомовних заголовків
        // Можна вказати або текст, або мовний ключ у форматі "lang:key"
        $title_param = isset($setting['title']) ? $setting['title'] : null;

        if ($title_param) {
            // Якщо починається з "lang:", це мовний ключ
            if (strpos($title_param, 'lang:') === 0) {
                $lang_key = substr($title_param, 5); // Видаляємо "lang:" з початку
                $title = $this->language->get($lang_key);

                // Якщо ключ не знайдено, повертається сам ключ - використовуємо за замовчуванням
                if ($title === $lang_key) {
                    $title = $this->language->get('text_shortcode_title');
                }
            } else {
                // Звичайний текст
                $title = $title_param;
            }
        } else {
            // За замовчуванням
            $title = $this->language->get('text_shortcode_title');
        }

        $data['show_selector'] = $show_selector && !empty($products);
        $data['show_title'] = $show_title;
        $data['title'] = $title;

        $product_id = 0;

        if (isset($setting['product_id'])) {
            $product_id = (int)$setting['product_id'];
        } elseif (isset($this->request->get['product_id'])) {
            $product_id = (int)$this->request->get['product_id'];
        }

        if (!$product_id && !empty($products)) {
            $product_id = (int)$products[0]['product_id'];
        }

        $data['selected_product_id'] = $product_id;
        $data['module_uid'] = uniqid('ml-calc-shortcode-');

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

        // Проверяем, нужно ли игнорировать JAN check
        $ignore_jan = isset($setting['ignore_jan_check']) && $setting['ignore_jan_check'];

        if ($product_id && ($ignore_jan || $this->isProductAllowed($product_id))) {
            $module_setting = array(
                'product_id' => $product_id,
                'show_product_summary' => true
            );

            // Передаем флаг игнорирования JAN в основной контроллер
            if ($ignore_jan) {
                $module_setting['ignore_jan_check'] = true;
            }

            $data['module_html'] = $this->load->controller('extension/module/ml_calc', $module_setting);

            if (empty($data['module_html'])) {
                $data['module_html'] = '<!-- DEBUG: ml_calc returned empty! Product ID: ' . $product_id . ' -->';
            }
        } else {
            $data['module_html'] = '<!-- DEBUG: Product not allowed or no product_id. ID: ' . $product_id . ', Allowed: ' . ($product_id ? ($this->isProductAllowed($product_id) ? 'YES' : 'NO') : 'N/A') . ' -->';
        }

        if (!$data['module_html'] && empty($products)) {
            return '';
        }

        return $this->load->view('extension/module/ml_calc_shortcode', $data);
    }

    public function render() {
        if (!$this->config->get('module_ml_calc_status')) {
            $this->response->setOutput('');
            return;
        }

        $product_id = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;

        if (!$product_id || !$this->isProductAllowed($product_id)) {
            $this->response->setOutput('');
            return;
        }

        $module_setting = array(
            'product_id' => $product_id,
            'show_product_summary' => true
        );

        $this->response->setOutput($this->load->controller('extension/module/ml_calc', $module_setting));
    }

    public function shortcode() {
        if (!$this->config->get('module_ml_calc_status')) {
            $this->response->setOutput('');
            return;
        }

        $setting = array();

        if (isset($this->request->get['product_id'])) {
            $setting['product_id'] = (int)$this->request->get['product_id'];
        }

        if (isset($this->request->get['show_selector'])) {
            $setting['show_selector'] = $this->request->get['show_selector'] === '1' || $this->request->get['show_selector'] === 'true';
        }

        if (isset($this->request->get['show_title'])) {
            $setting['show_title'] = $this->request->get['show_title'] === '1' || $this->request->get['show_title'] === 'true';
        }

        if (isset($this->request->get['title'])) {
            // Защита от XSS: strip_tags + htmlspecialchars + ограничение длины
            $setting['title'] = htmlspecialchars(substr(strip_tags($this->request->get['title']), 0, 200), ENT_QUOTES, 'UTF-8');
        }

        // Флаг для игнорирования проверки JAN, если передан явный product_id
        if (isset($this->request->get['current_product']) && $this->request->get['current_product'] === '1') {
            $setting['ignore_jan_check'] = true;
            $setting['show_selector'] = false;  // Не показываем селектор для текущего товара
        }

        $this->response->setOutput($this->index($setting));
    }

    private function getAvailableProducts() {
        $products = array();

        // Используем escape для безопасности SQL запроса
        $language_id = (int)$this->config->get('config_language_id');

        $query = $this->db->query("SELECT p.product_id, pd.name, p.price, p.tax_class_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.status = '1' AND p.jan = '1' AND pd.language_id = '" . $this->db->escape($language_id) . "' ORDER BY pd.name ASC");

        foreach ($query->rows as $row) {
            $products[] = array(
                'product_id' => (int)$row['product_id'],
                'name'       => $row['name'],
                'price'      => $this->currency->format($this->tax->calculate($row['price'], $row['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
            );
        }

        return $products;
    }

    private function isProductAllowed($product_id) {
        // Приведение к int и escape для безопасности
        $product_id = (int)$product_id;

        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE product_id = '" . $this->db->escape($product_id) . "' AND status = '1' AND jan = '1'");

        return (bool)$query->row['total'];
    }
}
