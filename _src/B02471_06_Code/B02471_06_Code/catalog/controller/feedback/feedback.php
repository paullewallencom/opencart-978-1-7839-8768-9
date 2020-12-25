<?php
class ControllerFeedbackFeedback extends Controller {
	public function index() {
		
                $this->load->language('feedback/feedback');
                $this->document->setTitle($this->language->get('heading_title'));
                $this->document->setDescription($this->language->get('feedback_description'));
                $this->document->setKeywords($this->language->get('feedback_keywords'));
               
                $data['heading_title'] = $this->language->get('heading_title');
              
                $data['text_empty'] = $this->language->get('text_empty');
                $data['button_continue'] = $this->language->get('button_continue');
                
                if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
                
                $data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
		
                // Set the last feedback breadcrumb
                $data['breadcrumbs'][] = array(
                        'text' => $this->language->get('heading_title'),
                        'href' => $this->url->link('feedback/feedback')
                );

                $data['feedbacks'] = array();
                $limit = $this->config->get('config_product_limit');
                $filter_data = array(
                        'start'              => ($page - 1) * $limit,
                        'limit'              => $limit
                );


                $this->load->model('feedback/feedback');
                $feedback_total = $this->model_feedback_feedback->getTotalFeedbacks($filter_data);
                $results = $this->model_feedback_feedback->getfeedbacks($filter_data);

                foreach ($results as $result) {
                        $data['feedbacks'][] = array(
                                'feedback_id'  => $result['feedback_id'],
                                'author'        => $result['author'],
                                'description' => strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))
                        );
                }

                $url = '';

                if (isset($this->request->get['limit'])) {
                        $url .= '&limit=' . $this->request->get['limit'];
                }





                $pagination = new Pagination();
                $pagination->total = $feedback_total;
                $pagination->page = $page;
                $pagination->limit = $limit;
                $pagination->url = $this->url->link('feedback/feedback', 'path' . $url . '&page={page}');

                $data['pagination'] = $pagination->render();

                $data['results'] = sprintf($this->language->get('text_pagination'), ($feedback_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($feedback_total - $limit)) ? $feedback_total : ((($page - 1) * $limit) + $limit), $feedback_total, ceil($feedback_total / $limit));

                
                $data['limit'] = $limit;

                $data['continue'] = $this->url->link('common/home');

                $data['column_left'] = $this->load->controller('common/column_left');
                $data['column_right'] = $this->load->controller('common/column_right');
                $data['content_top'] = $this->load->controller('common/content_top');
                $data['content_bottom'] = $this->load->controller('common/content_bottom');
                $data['footer'] = $this->load->controller('common/footer');
                $data['header'] = $this->load->controller('common/header');

                if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/feedback/feedback.tpl')) {
                        $this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/feedback/feedback.tpl', $data));
                } else {
                        $this->response->setOutput($this->load->view('default/template/feedback/feedback.tpl', $data));
                }
	}
}