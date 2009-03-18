<?php
/*
Developer:  Richard Willis
Copyright:	Richard Willis
TinyCIMM media manager
*/

class Assetmanager extends Controller
{
	
	var $user_id;
  
	public function __construct(){
		parent::Controller();

		$this->load->library('image_lib');
		$this->load->library('session');
		$this->load->library('tinycimm');
		$this->load->model('tinycimm_model');
		$this->load->config('tinycimm');
		TinyCIMM::check_paths();

		// eg: $this->user_id = $this->auth->user_id OR die('Acess denied.');
		$this->user_id = 1;
		
		// set view type in user session
		if (!$this->session->userdata('cimm_view')) {
			$this->session->set_userdata('cimm_view', 'thumbnails');
		}
  	}

	function _remap($method){
		$this->load->library('tinycimm_'.$method);
		$array = array_slice(explode("/", $this->uri->uri_string()),4);
		$count = 0;
                foreach ($array as $element) {
			$array[$count] = "'" . $element . "'";$count++;
		}
                eval("TinyCIMM_{$method}::" . $this->uri->segment(3) . "(".join(",", $array).");");
	}
	
}
