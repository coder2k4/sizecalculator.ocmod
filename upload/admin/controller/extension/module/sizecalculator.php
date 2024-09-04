<?php
class ControllerExtensionModuleSizeCalculator extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/module/sizecalculator');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/module');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if (!isset($this->request->get['module_id'])) {
                $this->model_setting_module->addModule('sizecalculator', $this->request->post);
            } else {
                $this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        if (!isset($this->request->get['module_id'])) {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/sizecalculator', 'user_token=' . $this->session->data['user_token'], true)
            );
        } else {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/sizecalculator', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
            );
        }

        if (!isset($this->request->get['module_id'])) {
            $data['action'] = $this->url->link('extension/module/sizecalculator', 'user_token=' . $this->session->data['user_token'], true);
        } else {
            $data['action'] = $this->url->link('extension/module/sizecalculator', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
        }

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
        }

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } elseif (!empty($module_info)) {
            $data['name'] = $module_info['name'];
        } else {
            $data['name'] = '';
        }

        if (isset($this->request->post['title'])) {
            $data['title'] = $this->request->post['title'];
        } elseif (!empty($module_info)) {
            $data['title'] = $module_info['title'];
        } else {
            $data['title'] = '';
        }

        if (isset($this->request->post['subtitle'])) {
            $data['subtitle'] = $this->request->post['subtitle'];
        } elseif (!empty($module_info)) {
            $data['subtitle'] = $module_info['subtitle'];
        } else {
            $data['subtitle'] = '';
        }




        $this->load->model('catalog/product');

        $data['selected_papers'] = array();
        $data['selected_products'] = array();
        $data['selected_cardboards'] = array();


        // Products
        if (isset($this->request->post['product'])) {
            $products = $this->request->post['product'];
        } elseif (!empty($module_info['product'])) {
            $products = $module_info['product'];
        } else {
            $products = array();
        }

        foreach ($products as $product_id) {
            $product_info = $this->model_catalog_product->getProduct($product_id);
            if ($product_info) {
                $data['selected_products'][] = $product_info;
            }
        }


        // Papers
        if (isset($this->request->post['paper'])) {
            $papers = $this->request->post['paper'];
        } elseif (!empty($module_info['paper'])) {
            $papers = $module_info['paper'];
        } else {
            $papers = array();
        }

        foreach ($papers as $paper_id) {
            $paper_info = $this->model_catalog_product->getProduct($paper_id);
            if ($paper_info) {
                $data['selected_papers'][] = $paper_info;
            }
        }



        // Cardboards
        if (isset($this->request->post['cardboard'])) {
            $cardboards = $this->request->post['cardboard'];
        } elseif (!empty($module_info['cardboard'])) {
            $cardboards = $module_info['cardboard'];
        } else {
            $cardboards = array();
        }

        foreach ($cardboards as $cardboard_id) {
            $cardboard_info = $this->model_catalog_product->getProduct($cardboard_id);
            if ($cardboard_info) {
                $data['selected_cardboards'][] = $cardboard_info;
            }
        }


        $data['products'] = $data['papers'] =  $data['cardboards'] =  $this->model_catalog_product->getProducts();



        $this->load->model('tool/image');

        for ($i = 1; $i <= 3; $i++) {
            $image_field = 'image_' . $i;
            if (isset($this->request->post[$image_field]) && is_file(DIR_IMAGE . $this->request->post[$image_field])) {
                $data[$image_field] = $this->request->post[$image_field];
                $data[$image_field . '_thumb'] = $this->model_tool_image->resize($this->request->post[$image_field], 100, 100);
            } elseif (!empty($module_info[$image_field]) && is_file(DIR_IMAGE . $module_info[$image_field])) {
                $data[$image_field] = $module_info[$image_field];
                $data[$image_field . '_thumb'] = $this->model_tool_image->resize($module_info[$image_field], 100, 100);
            } else {
                $data[$image_field] = '';
                $data[$image_field . '_thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
            }
        }

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($module_info)) {
            $data['status'] = $module_info['status'];
        } else {
            $data['status'] = '';
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/sizecalculator', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/sizecalculator')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!isset($this->request->post['name']) || (utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
            $this->error['name'] = $this->language->get('error_name');
        }

        return !$this->error;
    }
}
