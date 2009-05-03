<?php  
/*
 *
 * tinycimm_image.php
 * Copyright (c) 2009 Richard Willis & Liam Gooding
 * MIT license  : http://www.opensource.org/licenses/mit-license.php
 * Project      : http://tinycimm.googlecode.com/
 * Contact      : willis.rh@gmail.com
 *
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');
 
class TinyCIMM_image extends TinyCIMM {

	var $view_path = '';

	public function __construct(){
		parent::__construct();
	}

	public function get($asset_id, $width=200, $height=200){
		$this->get_asset((int) $asset_id, $width, $height);
	}

	// returns an asset database object
	public function get_image($image_id=0){
		$ci = &get_instance();
		if ($image = $ci->tinycimm_model->get_asset($image_id)) {
			// get image dimenions
			$dimensions = getimagesize($ci->config->item('tinycimm_asset_path').$image->filename);
			$image->width = $dimensions[0];
			$image->height = $dimensions[1];
			$image->outcome = 'success';
			$this->response_encode($image);
		} else {
			die('image not found');
		}
	}
	
	/**
	* uploads an asset and insert info into db
	**/
	public function upload(){
		$ci = &get_instance();

		$asset = $this->upload_asset();
		
		// resize image
		$max_x = (int) $ci->input->post('max_x');
		$max_y = (int) $ci->input->post('max_y');
		$adjust_size = (int) $ci->input->post('adjust_size') === 1 and ($asset->width > $max_x or $asset->height > $max_y);
		if ($adjust_size and ($asset->width > $max_x or $asset->height > $max_y)) {
			$this->resize_asset($asset, $max_x, $max_y, 85, false);
		}

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
	public function get_browser($folder=0, $offset=0) {
		$ci = &get_instance();
		$ci->load->library('pagination');
		$ci->load->helper('url');

		$total_assets = count($ci->tinycimm_model->get_assets($folder));

		$pagination_config['base_url'] = base_url($ci->config->item('tinycimm_controller').'image/get_browser/'.$folder);
		$pagination_config['total_rows'] = $total_assets;
		$pagination_config['full_tag_open'] = '<div class="heading pagination">';
		$pagination_config['full_tag_close'] = '</div>';
		$pagination_config['per_page'] = $ci->config->item('tinycimm_pagination_per_page'); 
		$pagination_config['uri_segment'] = 5;
		$ci->pagination->initialize($pagination_config);
	
		// store an 'uncategorized' root folder (aka smart folder)
		$data['folders'][] = array( 'id'=>'0', 'name' => 'All images', 'total_assets' => $total_assets);

		// get a list of folders, and store the total amount of assets
		foreach($folders = $ci->tinycimm_model->get_folders() as $folderinfo) {
			$folderinfo['total_assets'] = count($ci->tinycimm_model->get_assets($folderinfo['id']));
			$data['folders'][] = $folderinfo;
			// selected folder info
			if ($folderinfo['id'] == $folder) {
				$data['selected_folder_info'] = $folderinfo;
		  	}
		}
		if (!isset($data['selected_folder_info'])) {
			$data['selected_folder_info'] = $data['folders'][0];
		}
		
		$data['images'] = array();
		$totimagesize = 0;
		foreach($assets = $ci->tinycimm_model->get_assets((int) $folder, $offset, $ci->config->item('tinycimm_pagination_per_page')) as $image) {
			$image_path = $this->config->item('tinycimm_asset_path').$image['id'].$image['extension'];
			$image_size = ($imgsize = @getimagesize($image_path)) ? $imgsize : array(0,0);
			$image['width'] = $image_size[0];
			$image['height'] = $image_size[1];
			$image['dimensions'] = $image_size[0].'x'.$image_size[1];
			$image['filesize'] = round(@filesize($image_path)/1024, 0);
			// format image name
			if (strlen($image['name']) > 34) {
				$image['name'] = substr($image['name'], 0, 34);
			}
			$data['images'][] = $image;	 
			$totimagesize += $image['filesize'];
		}
		// prepare total image size
		$data['selected_folder_info']['total_file_size'] = ($totimagesize > 1024) ? round($totimagesize/1024, 2).'mb' : $totimagesize.'kb';

		$ci->load->view($this->view_path.'image_'.$ci->session->userdata('cimm_view').'_list', $data);
	}
  
	/**
	* this geneates a javascript array of images that is
	* used in the adv image dialog window as drop down list
	**/
	public function image_list() {
		header("text/javascript");

		$output = 'var tinyMCEImageList = new Array('."\n";
		// get images in folder
		$sql = 'SELECT img.*, fld.name AS foldername
			FROM asset
			LEFT JOIN asset_folder
				ON asset.folder = asset_folder.id
			ORDER by asset.folder ASC, asset.name ASC';
		$query = $this->db->query($sql);
		
		foreach($query->result_array() AS $image) {
			$image['foldername'] = 'General/'.($image['foldername']!=''?$image['foldername'].'/':'');
			
			/**
			* @TODO Assumes uploaded folder is /images/uploaded/
			* @Liam
			**/
			$output .= '["'.$image['foldername'].$image['name'].'", "'.base_url('images/uploaded/'.$image['filename']).'", "desc"],'."\n";
			/**/
			
		}
		
		die(rtrim($output, ",\n").');');
  	}
  	
  	/**
  	* set view type for asset listing (list or thumbnails) in user session
  	**/
	public function setview($view) {
		$this->session->set_userdata('cimm_view', $view);
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
			$response['outcome'] = 'error';
			$response['message'] = 'Image not found.';
			$this->response_encode($response);
			exit;
		}
		$response['outcome'] = 'success';
		$response['message'] = 'Image successfully deleted.';
		$this->response_encode($response);
		exit;
	}

  	/**
  	* delete an image from database and file system
  	**/
	public function delete_image($image_id=0) {
		$ci = &get_instance();
		if (!$this->delete_asset((int) $image_id)) {
			$response['outcome'] = 'error';
			$response['message'] = 'Image not found.';
			$this->response_encode($response);
			exit;
		}
		$response['outcome'] = 'success';
		$response['message'] = 'Image successfully deleted.';
		$this->response_encode($response);
		exit;
	}
	
  	/**
  	* @TODO would become obsolete if we switched away from a multi folder system and went with categories @Liam
  	**/
	public function delete_folder($folder_id=0) {
		$ci = &get_instance();
		if (!parent::delete_folder((int) $folder_id)) {
			$response['outcome'] = 'error';
			$response['message'] = 'Image not found.';
			$this->response_encode($response);
			exit;
		}
		$response['outcome'] = 'success';
		$response['images_affected'] = $this->images_affected;
		$this->response_encode($response);
		exit;
 	}
  	
  	/**
  	* @TODO would become obsolete if we switched away from a multi folder system and went with categories @Liam
  	**/
	public function add_folder($name=''){ 
		if (is_array($response = parent::add_folder($name))) {
                        $this->response_encode($response);
                        exit;
                }
		$this->get_folders_html();
  	}
  	
  	/**
  	* @TODO would become obsolete if we switched away from a multi folder system and went with categories @Liam
  	**/
	public function get_folders_select($folder_id=0){
		parent::get_folders_select((int) $folder_id);
	}

	public function get_folders_html(){
		parent::get_folders_html();
	}
	
	/**
	* resizes an image
	**/
	public function save_image_size($image_id, $width, $height, $quality=90){
		$ci = &get_instance();
		if (!(int) $width or !(int) $height) {
			TinyCIMM::response_encode(array('outcome'=>'error','message'=>'Incorrect dimensions supplied. (Cant have value of 0)'));
		}
		$image = $ci->tinycimm_model->get_asset($image_id);
		$this->resize_asset($image, $width, $height, $quality, false);
		
		$response['outcome'] = 'success';
		$response['message'] = 'Image size successfully saved.';
		$this->response_encode($response);
	}

  
	/**
	*
	**/
	public function change_view($view='thumb', $folder_id=0){
		$ci = &get_instance();
		$ci->session->set_userdata('cimm_view', $view);
		$this->get_browser((int) $folder_id);
	}
	
	/**
	* get extension of filename
	**/
	public static function get_extension($filename) {
		return end(explode('.', $filename));
	}
  	
} // class TinyCIMM_image
