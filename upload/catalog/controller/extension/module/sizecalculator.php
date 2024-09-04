<?php
class ControllerExtensionModuleSizeCalculator extends Controller {
    public function index($setting) {

        static $module = 0;


        // Load necessary styles and scripts
        $this->document->addScript('https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js');
        $this->document->addStyle('catalog/view/theme/default/stylesheet/sizecalculator.css');
        $this->document->addScript('catalog/view/javascript/sizecalculator.js');

        // Load language
        $this->load->language('extension/module/sizecalculator');

        // Load image model
        $this->load->model('tool/image');



        // Load module settings
        $data['title'] = $setting['title'];
        $data['subtitle'] = $setting['subtitle'];


        // Load selected papers and cardboards from settings
        $data['papers'] = array();
        $data['products'] = array();
        $data['cardboards'] = array();

        if (!empty($setting['product_id'])) {
            $this->load->model('catalog/product');
            foreach ($setting['product_id'] as $product_id) {
                $product_info = $this->model_catalog_product->getProduct($product_id);
                if ($product_info) {
                    $data['products'][] = $product_info;
                }
            }
        }


        if (!empty($setting['paper_id'])) {
            $this->load->model('catalog/product');
            foreach ($setting['paper_id'] as $paper_id) {
                $paper_info = $this->model_catalog_product->getProduct($paper_id);
                if ($paper_info) {
                    $data['papers'][] = $paper_info;
                }
            }
        }

        if (!empty($setting['cardboard_id'])) {
            foreach ($setting['cardboard_id'] as $cardboard_id) {
                $cardboard_info = $this->model_catalog_product->getProduct($cardboard_id);
                if ($cardboard_info) {
                    $data['cardboards'][] = $cardboard_info;
                }
            }
        }

        // Load images
        $data['images'] = array();
        for ($i = 1; $i <= 3; $i++) {
            $image_field = 'image_' . $i;
            if (!empty($setting[$image_field]) && is_file(DIR_IMAGE . $setting[$image_field])) {
                $data['images'][] = $this->model_tool_image->resize($setting[$image_field], 300, 300);
            }
        }

        $data['module'] = $module++;

        // Render the view
        return $this->load->view('extension/module/sizecalculator', $data);
    }


    public function sendMail() {
        $json = array();

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $mail = new Mail($this->config->get('config_mail_engine'));
            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
            $mail->smtp_username = $this->config->get('config_mail_smtp_username');
            $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
            $mail->smtp_port = $this->config->get('config_mail_smtp_port');
            $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

            $mail->setTo($this->config->get('config_email'));
            $mail->setFrom($this->config->get('config_email'));
            $mail->setSender(html_entity_decode($this->request->post['name'], ENT_QUOTES, 'UTF-8'));
            $mail->setSubject(html_entity_decode(sprintf('%s - Новый заказ', $this->config->get('config_name')), ENT_QUOTES, 'UTF-8'));

            $message = "Имя: " . $this->request->post['name'] . "\n";
            $message .= "Телефон: " . $this->request->post['phone'] . "\n\n";
            $message .= "Детали заказа:\n";
            $message .= "Вид продукции: " . $this->request->post['product_type'] . "\n";
            $message .= "Тип бумаги: " . $this->request->post['paper_type'] . "\n";
            $message .= "Марка картона: " . $this->request->post['cardboard_grade'] . "\n";
            $message .= "Размеры (ШxДxВ): " . $this->request->post['width'] . "x" . $this->request->post['length'] . "x" . $this->request->post['height'] . " мм\n";
            $message .= "Рассчитанная стоимость: " . $this->request->post['calculated_cost'] . " руб.";

            $mail->setText($message);
            $mail->send();

            $json['success'] = true;
        } else {
            $json['success'] = false;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function validate() {
        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 32)) {
            return false;
        }

        if (!preg_match('/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/', $this->request->post['phone'])) {
            return false;
        }

        return true;
    }
}