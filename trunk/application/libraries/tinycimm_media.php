<?php  
/*
 *
 * tinycimm_image.php
 * Copyright (c) 2009 Richard Willis
 * MIT license  : http://www.opensource.org/licenses/mit-license.php
 * Project      : http://tinycimm.googlecode.com/
 * Contact      : willis.rh@gmail.com : http://badsyntax.co.uk
 *
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');
 
class TinyCIMM_media extends TinyCIMM {

	var $view_path = '';

	public function __construct(){
		parent::__construct();
	}

	public function get($asset_id, $width=200, $height=200){
		$this->get_asset((int) $asset_id, $width, $height);
	}

	public function get_asset($image_id=0){
		$ci = &get_instance();
		if ($asset = $ci->tinycimm_model->get_asset($image_id)) {
			$asset->outcome = true;
			$this->response_encode($asset);
		} else {
			$this->response_encode(array('outcome' => false, 'message' => 'Image not found.'));
		}
	}
	
	/**
	* uploads an asset and insert info into db
	**/
	public function upload(){
		$ci = &get_instance();

		$asset = $this->upload_asset($this->config->item('tinycimm_media_upload_config'));
		
		echo
		"<script type=\"text/javascript\">
		parent.removedim();
		parent.updateimg('".$asset->folder."');
		</script>";
		exit;
	}

	/**
	* get browser 
	**/
	public function get_browser($folder=0, $offset=0, $search='') {
		$ci = &get_instance();
		$ci->load->library('pagination');
		$ci->load->helper('url');

		$per_page = $ci->config->item('tinycimm_pagination_per_page_'.$ci->session->userdata('cimm_view'));
		$total_assets = count($ci->tinycimm_model->get_assets($folder, $offset, NULL, $search));

		$pagination_config['base_url'] = base_url($ci->config->item('tinycimm_controller').'image/get_browser/'.$folder);
		$pagination_config['total_rows'] = $total_assets;
		$pagination_config['full_tag_open'] = '<div class="heading pagination">';
		$pagination_config['full_tag_close'] = '</div>';
		$pagination_config['per_page'] = $per_page;
		$pagination_config['uri_segment'] = 5;
		$ci->pagination->initialize($pagination_config);

	
		// store an 'uncategorized' root folder (aka smart folder)
		$data['folders'][] = array( 'id'=>'0', 'name' => 'All files', 'total_assets' => $total_assets);

		// get a list of folders, and store the total amount of assets
		foreach($folders = $ci->tinycimm_model->get_folders('media') as $folderinfo) {
			$folderinfo['total_assets'] = count($ci->tinycimm_model->get_assets($folderinfo['id'], $offset, $per_page, $search));
			$data['folders'][] = $folderinfo;
			// selected folder info
			if ($folderinfo['id'] == $folder) {
				$data['selected_folder_info'] = $folderinfo;
		  	}
		}
		if (!isset($data['selected_folder_info'])) {
			if ($search != '') {
				$data['selected_folder_info'] = array( 'id'=>'0', 'name' => 'Search results', 'total_assets' => $total_assets);
			} else {
				$data['selected_folder_info'] = $data['folders'][0];
			}
		}

		$totimagesize = (int) $ci->tinycimm_model->get_filesize_assets($folder) / 1024;
		$data['selected_folder_info']['total_file_size'] = ($totimagesize > 1024) ? round($totimagesize/1024, 2).'mb' : round($totimagesize, 2).'kb';

		$data['assets'] = array();
		foreach($assets = $ci->tinycimm_model->get_assets((int) $folder, $offset, $per_page, $search) as $asset) {
			$asset_path = $this->config->item('tinycimm_asset_path').$asset['id'].$asset['extension'];
			$asset['filesize'] = round(@filesize($asset_path)/1024, 0);
			// format image name
			if (strlen($asset['name']) > 34) {
				$asset['name'] = substr($asset['name'], 0, 34);
			}
			$data['assets'][] = $asset;	 
		}

     		$ci->load->view($this->view_path.$ci->session->userdata('cimm_view').'_list', $data);
	}
  
	/**
	* update asset row
	**/
	public function update_asset($image_id=0) {
		if (!count($_POST)) {
			exit;
		}
		$ci = &get_instance();
		if (!$ci->tinycimm_model->update_asset((int) $image_id, $_POST['folder_id'], $_POST['name'], $_POST['description'])) {
			$response['outcome'] = false;
			$response['message'] = 'Image not found.';
			$this->response_encode($response);
			exit;
		}
		$response['outcome'] = true;
		$response['message'] = 'Image successfully deleted.';
		$this->response_encode($response);
		exit;
	}

  	/**
  	* delete an image from database and file system
  	**/
	public function delete_asset($image_id=0) {
		$ci = &get_instance();
		$image = $ci->tinycimm_model->get_asset($image_id);
		$this->delete_asset((int) $image_id);
		
		$response['outcome'] = true;
		$response['message'] = 'Image successfully deleted.';
		$response['folder'] = $image->folder_id; 
		$this->response_encode($response);
		exit;
	}
	
  	/**
  	* @TODO would become obsolete if we switched away from a multi folder system and went with categories @Liam
  	**/
	public function delete_folder($folder_id=0) {
		$ci = &get_instance();
		if (!parent::delete_folder((int) $folder_id)) {
			$response['outcome'] = false;
			$response['message'] = 'Image not found.';
			$this->response_encode($response);
			exit;
		}
		$response['outcome'] = true;
		$response['images_affected'] = $this->images_affected;
		$this->response_encode($response);
		exit;
 	}
  	
  	/**
  	* @TODO would become obsolete if we switched away from a multi folder system and went with categories @Liam
  	**/
	public function add_folder($name='', $type=''){ 
		if (is_array($response = parent::add_folder($name, $type))) {
                        $this->response_encode($response);
                        exit;
                }
		$this->get_folders_html('media');
  	}
  	
        public function get_folders_select($folder_id=0){
                parent::get_folders_select((int) $folder_id);
        }

	public function get_folders_html(){
		parent::get_folders_html('media');
	}
	
	/**
	* change browser template in user session
	**/
	public function change_view($view='thumbnails'){
		$ci = &get_instance();
		$ci->session->set_userdata('cimm_view', $view);
		exit;
	}

	/**
	* displays the image upload form
	*/
	public function get_uploader_form(){
		$ci = &get_instance();
		$data['upload_config'] = $ci->config->item('tinycimm_media_upload_config');
		$ci->load->view($this->view_path.'upload_form', $data);
	}
	
	/**
	* get extension of filename
	**/
	public static function get_extension($filename) {
		return end(explode('.', $filename));
	}
  	
} // class TinyCIMM_media
