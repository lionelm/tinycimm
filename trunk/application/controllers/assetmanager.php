<?php
/*
	TinyCIMM media manager
*/

class Assetmanager extends Controller {
	
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

	/*
	* prevent default controller method execution, use the default CI segment method to load in the corresponding
	* tinycimm library, and use the next segment to execute the corresponding libraries' method
	*/
	function _remap($lib){
		$param = array_slice(explode("/", $this->uri->uri_string()),4);
		$method = trim($this->uri->segment(3));
		$tinycimm_lib = "tinycimm_{$lib}";
		$count = 0;
                foreach ($param as $element) {
			$param[$count] = "'".$element."'";
			$count++;
		}
		$this->{$tinycimm_lib}->view_path = $this->view_path = $this->config->item('tinycimm_views_root').$this->config->item('tinycimm_views_root_'.$lib);
		if (empty($method)) { 
			$method = 'index';
		}
		eval('$this->'.$tinycimm_lib. "->" . $method . "(".join(",", $param).");");
	}
	
}
