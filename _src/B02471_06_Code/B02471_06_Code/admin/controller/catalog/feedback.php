<?php
class ControllerCatalogFeedback extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('catalog/feedback');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/feedback');

		$this->getList();
	}



	public function add() {
		$this->load->language('catalog/feedback');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('catalog/feedback');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_feedback->addfeedback($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			$this->response->redirect($this->url->link('catalog/feedback', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('catalog/feedback');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('catalog/feedback');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_feedback->editfeedback($this->request->get['feedback_id'], $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			$this->response->redirect($this->url->link('catalog/feedback', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
		$this->getForm();
	}

	public function delete() {
		$this->load->language('catalog/feedback');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('catalog/feedback');
		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $feedback_id) {
				$this->model_catalog_feedback->deletefeedback($feedback_id);
			}
			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			$this->response->redirect($this->url->link('catalog/feedback', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
		$this->getList();
	}

	protected function getList() {
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/feedback', 'token=' . $this->session->data['token'] . $url, 'SSL')
		);
		
		$data['add'] = $this->url->link('catalog/feedback/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$data['delete'] = $this->url->link('catalog/feedback/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$data['feedbacks'] = array();

		$filter_data = array(
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$feedback_total = $this->model_catalog_feedback->getTotalfeedbacks();

		$results = $this->model_catalog_feedback->getfeedbacks($filter_data);

		foreach ($results as $result) {
			$data['feedbacks'][] = array(
				'feedback_id' => $result['feedback_id'],
				'author'      => $result['author'],
				'description' => html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
				'edit'        => $this->url->link('catalog/feedback/edit', 'token=' . $this->session->data['token'] . '&feedback_id=' . $result['feedback_id'] . $url, 'SSL')
			);
		}

		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['text_list'] = $this->language->get('text_list');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');

		$data['column_feedback'] = $this->language->get('column_feedback');
		$data['column_author'] = $this->language->get('column_author');
		$data['column_action'] = $this->language->get('column_action');

		$data['button_add'] = $this->language->get('button_add');
		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_delete'] = $this->language->get('button_delete');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$pagination = new Pagination();
		$pagination->total = $feedback_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('catalog/feedback', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($feedback_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($feedback_total - $this->config->get('config_limit_admin'))) ? $feedback_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $feedback_total, ceil($feedback_total / $this->config->get('config_limit_admin')));

		

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/feedback_list.tpl', $data));
	}

	protected function getForm() {
		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['text_form'] = !isset($this->request->get['feedback_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_default'] = $this->language->get('text_default');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['entry_author'] = $this->language->get('entry_author');
		$data['entry_description'] = $this->language->get('entry_description');
		
		$data['entry_store'] = $this->language->get('entry_store');
		
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_layout'] = $this->language->get('entry_layout');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['tab_general'] = $this->language->get('tab_general');
		$data['tab_data'] = $this->language->get('tab_data');
		$data['tab_design'] = $this->language->get('tab_design');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['author'])) {
			$data['error_author'] = $this->error['author'];
		} else {
			$data['error_author'] = array();
		}

		if (isset($this->error['description'])) {
			$data['error_description'] = $this->error['description'];
		} else {
			$data['error_description'] = array();
		}

		
		
		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/feedback', 'token=' . $this->session->data['token'] . $url, 'SSL')
		);
		
		if (!isset($this->request->get['feedback_id'])) {
			$data['action'] = $this->url->link('catalog/feedback/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$data['action'] = $this->url->link('catalog/feedback/edit', 'token=' . $this->session->data['token'] . '&feedback_id=' . $this->request->get['feedback_id'] . $url, 'SSL');
		}

		$data['cancel'] = $this->url->link('catalog/feedback', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['feedback_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$feedback_info = $this->model_catalog_feedback->getfeedback($this->request->get['feedback_id']);
		}

		$data['token'] = $this->session->data['token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['feedback_description'])) {
			$data['feedback_description'] = $this->request->post['feedback_description'];
		} elseif (isset($this->request->get['feedback_id'])) {
			$data['feedback_description'] = $this->model_catalog_feedback->getfeedbackDescriptions($this->request->get['feedback_id']);
		} else {
			$data['feedback_description'] = array();
		}
               

		$this->load->model('setting/store');
		$data['stores'] = $this->model_setting_store->getStores();

		if (isset($this->request->post['feedback_store'])) {
			$data['feedback_store'] = $this->request->post['feedback_store'];
		} elseif (isset($this->request->get['feedback_id'])) {
			$data['feedback_store'] = $this->model_catalog_feedback->getfeedbackStores($this->request->get['feedback_id']);
		} else {
			$data['feedback_store'] = array(0);
		}

		

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($feedback_info)) {
			$data['status'] = $feedback_info['status'];
		} else {
			$data['status'] = true;
		}

		

		if (isset($this->request->post['feedback_layout'])) {
			$data['feedback_layout'] = $this->request->post['feedback_layout'];
		} elseif (isset($this->request->get['feedback_id'])) {
			$data['feedback_layout'] = $this->model_catalog_feedback->getfeedbackLayouts($this->request->get['feedback_id']);
		} else {
			$data['feedback_layout'] = array();
		}

		$this->load->model('design/layout');

		$data['layouts'] = $this->model_design_layout->getLayouts();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/feedback_form.tpl', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/feedback')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		foreach ($this->request->post['feedback_description'] as $language_id => $value) {
			if ((utf8_strlen($value['author']) < 3) || (utf8_strlen($value['author']) > 64)) {
				$this->error['author'][$language_id] = $this->language->get('error_author');
			}
                        if ((utf8_strlen($value['description']) < 3)) {
				$this->error['description'][$language_id] = $this->language->get('error_description');
			}
		}
		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}
		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/feedback')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}
}