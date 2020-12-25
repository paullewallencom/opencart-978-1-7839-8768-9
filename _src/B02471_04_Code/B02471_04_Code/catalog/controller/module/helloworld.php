<?php
class ControllerModuleHelloWorld extends Controller {
	public function index($setting) {
		
		if (isset($setting['module_description'][$this->config->get('config_language_id')])) {
			$data['heading_title'] = html_entity_decode($setting['module_description'][$this->config->get('config_language_id')]['title'], ENT_QUOTES, 'UTF-8');
			$data['helloworld'] = html_entity_decode($setting['module_description'][$this->config->get('config_language_id')]['description'], ENT_QUOTES, 'UTF-8');
		
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/helloworld.tpl')) {
				return $this->load->view($this->config->get('config_template') . '/template/module/helloworld.tpl', $data);
			} else {
				return $this->load->view('default/template/module/helloworld.tpl', $data);
			}
		}
	}
}
