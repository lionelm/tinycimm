<?php
/*
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
		$this->load->library('tinycimm_image');
		$this->load->model('tinycimm_model');
		$this->load->config('tinycimm');
		TinyCIMM::check_paths();

		// eg: $this->user_id = $this->auth->user_id OR die('Acess denied.');
		// Why are we checking for users? Authorisation should be handled externally? @author Liam
		$this->user_id = 1;
		
		// set default view type in user session
		if (!$this->session->userdata('cimm_view')) {
			$this->session->set_userdata('cimm_view', 'thumbnails');
		}
  	}

	function _remap($lib){
		$param = array_slice(explode("/", $this->uri->uri_string()),4);
		$method = $this->uri->segment(3);
		$count = 0;
                foreach ($param as $element) {
			$param[$count] = "'".$element."'";
			$count++;
		}
                eval("
			class_exists('TinyCIMM_{$lib}') and 
			method_exists('TinyCIMM_{$lib}', '{$method}') and 
			TinyCIMM_{$lib}::{$method}(".join(",", $param).");
		");
	}
	
} // class Assetmanager
