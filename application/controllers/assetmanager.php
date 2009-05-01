<?php
/*
	TinyCIMM media manager
*/

class Assetmanager extends Controller {
	
	public function __construct(){
		parent::Controller();

		$this->load->library('image_lib');
		$this->load->library('session');
		$this->load->library('tinycimm');
		$this->load->library('tinycimm_image');
		$this->load->model('tinycimm_model');
		$this->load->config('tinycimm');
		TinyCIMM::check_paths();

		// set default view type in user session
		!$this->session->userdata('cimm_view') and $this->session->set_userdata('cimm_view', 'thumbnails');

		// add your user auth check here to secure tinycimm
		// eg $this->auth->is_logged_in() or die('Access denied.');
  	}

	function image() {
		$param = array_slice(explode("/", $this->uri->uri_string()),4);
		$method = trim($this->uri->segment(3));
		$count = 0;
                foreach ($param as $element) {
			$param[$count] = "'".$element."'";
			$count++;
		}
		$this->tinycimm_image->view_path = $this->view_path = $this->config->item('tinycimm_views_root').$this->config->item('tinycimm_views_root_image');
		// eval should just never be used, until i find a better way this will have to do
		method_exists($this->tinycimm_image, $method) and eval('$this->tinycimm_image->' . $method . "(".join(",", $param).");");
	}

}
