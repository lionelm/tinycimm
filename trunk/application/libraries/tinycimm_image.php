<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
 
class TinyCIMM_image extends TinyCIMM {

	var $view_path = '';

	public function __construct(){
		parent::__construct();
	}

	public function get($asset_id, $width=200, $height=200){
		$this->get_asset((int) $asset_id, $width, $height);
	}
	
	/**
	* debug function @richw
	**/
	public function get_image($image_id=0){
		$ci = &get_instance();
		$image = $ci->tinycimm_model->get_asset($image_id);
		$image->outcome = 'success';
		$this->response_encode($image);
	}
  	
	/**
	* uploads an asset and insert info into db
	**/
	public function upload_image(){
		$this->upload_asset();
	}

	/**
	* get folder listing from db
	**/
	public function get_file_folder_list($folder=0) {
		$ci = &get_instance();
	
		$assets = $ci->tinycimm_model->get_assets();
		// 'uncategorized' folder
		$data['folders'][] = array('id'=>'0','user_id' => '0','username' => 'demo','name' => 'General', 'total_assets' => count($assets));
		// get a list of folders, and store the total amount of assets
		foreach($folders = $ci->tinycimm_model->get_folders() as $folderinfo) {
			$folderinfo['total_assets'] = count($ci->tinycimm_model->get_assets($folderinfo['id']));
			$data['folders'][] = $folderinfo;
			// selected folder info
			if ($folderinfo['id'] == $folder) {
				$folderinfo['username'] = 'demo';
				$data['selected_folder_info'] = $folderinfo;
		  	}
		}
		if (!isset($data['selected_folder_info'])) {
			$data['selected_folder_info'] = $data['folders'][0];
		}
		
		$assets = $ci->tinycimm_model->get_assets($folder);
		$data['images'] = array();
		$totimagesize = 0;
		
		foreach($assets AS $image) {
	  		
	  		/**
			* @TODO Assumes uploaded folder is /images/uploaded/
			* @Liam
			**/
			$imgsize = ($imgsize = @getimagesize('./images/uploaded/'.$image['filename'])) ? $imgsize : array(0,0);
			/**/
			
			$image['width'] = $imgsize[0];
			$image['height'] = $imgsize[1];
			$image['dimensions'] = $imgsize[0].'x'.$imgsize[1];
			$image['extension'] = TinyCIMM_image::get_extension($image['filename']);
			
			/**
			* @TODO Assumes uploaded folder is /images/uploaded/
			* @Liam
			**/
			$image['filesize'] = round(@filesize('./images/uploaded/'.$image['filename'])/1024, 0);
			/**/
			
			$data['images'][] = $image;	 
			$totimagesize = $totimagesize + $image['filesize'];
		}
		
		// prepare total image size
		$data['selected_folder_info']['total_file_size'] = ($totimagesize > 1024) ? round($totimagesize/1024, 2).'mb' : $totimagesize.'kb';

		header("Pragma: no-cache");
		header("Cache-Control: no-store, no-cache, max-age=0, must-revalidate");
		header('Content-Type: text/x-json'); 
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
		$ci = &get_instance();
		$name = urldecode(trim($name));
	
		if ($name == '') {
			$response['outcome'] = 'error';
			$response['message'] = 'Please specify a valid folder name.';
		} else if (strlen($name) == 1) {
			$response['outcome'] = 'error';
			$response['message'] = 'The folder name must be at least 2 characters in length.';
		} else if (strlen($name) > 24) {
			$response['outcome'] = 'error';
			$response['message'] = "The folder name must be less than 24 characters.\n(The supplied folder name is "+captionID.length+" characters).";
		}
	
		if (isset($response)) {
			$this->response_encode($response);
			exit;
		}

		$ci->tinycimm_model->insert_folder($name);

		$this->get_folders_html();
  	}
  	
  	/**
  	* @TODO would become obsolete if we switched away from a multi folder system and went with categories @Liam
  	**/
	public function get_folder_select($args){
		$data['folderid'] = isset($args['folder']) ? (int) $args['folder'] : 0;
		$ci = &get_instance();
		$data['folders'] = array();
		foreach($folders = $ci->tinycimm_model->get_folders('name', $ci->user_id) AS $folderinfo) {
			$data['folders'][] = $folderinfo;
		}
		die($ci->load->view($this->view_path.'image_folder_select', $data, true));
	}

	public function get_folders_html(){
		$ci = &get_instance();
		$data['folders'][0] = array('id'=>0,'name'=>'General');
		foreach($folders = $ci->tinycimm_model->get_folders('name', $ci->user_id) as $folderinfo) {
			$data['folders'][] = $folderinfo;
		}
		$ci->load->view($this->view_path.'image_folder_list', $data);
	}
	
	/**
	*
	**/
	public function get_alttext_textbox($args){
		$ci = &get_instance();
		$data['alttext'] = isset($args['alttext']) ? $this->input->xss_clean($args['alttext']) : '';
		die($ci->load->view($this->view_path.'image_alttext_textbox', $data, true));
	}
	
	/**
	* resizes an image
	**/
	public function save_image_size($filename, $width, $height, $quality=90, $replace_original=0){
		if (!(int)$width or !(int)$height) {
			TinyCIMM::response_encode(array('outcome'=>'error','message'=>'Incorrect dimensions supplied. (Cant have value of 0)'));
		}

		$image_path = $this->config->item('tinycimm_image_upload_path').$filename;
		$config['image_library'] = 'gd2';
		$config['source_image'] = $image_path;
		$config['new_image'] = $image_path;
		$config['maintain_ratio'] = TRUE;
		$config['height'] = (int)$height;
		$config['width'] = (int)$width;
		$this->image_lib->initialize($config);
		$this->image_lib->resize();
		$this->image_lib->clear();

		$response['outcome'] = 'success';
		$response['message'] = 'Image size successfully saved.';
		TinyCIMM::response_encode($response);
	}

  
	/**
	* @TODO not sure if its worth assuming a multi-user system yet.
	**/
	public function get_user_info(){
		$ci = &get_instance();
		// get user info: total images uploaded, privelages, max upload sizes etc etc

	
		$sql = 'SELECT COUNT(id) AS tot_images
			FROM asset 
			WHERE user_id = ?';
		$query = $this->db->query($sql, array($ci->user_id));
		$data['user'] = $query->row_array();
	
		// num gif
		$sql = 'SELECT id
			FROM asset
			WHERE filename LIKE \'%.gif\'';
		$query = $this->db->query($sql);
		$data['user']['tot_gif'] = $query->num_rows();
		// num jpg
		$sql = 'SELECT id
			FROM asset
			WHERE filename LIKE \'%.jpg\'';
		$query = $this->db->query($sql);
		$data['user']['tot_jpg'] = $query->num_rows();
		// num png
		$sql = 'SELECT id
			FROM asset
			WHERE filename LIKE \'%.png\'';
		$query = $this->db->query($sql);
		$data['user']['tot_png'] = $query->num_rows();

		header("Pragma: no-cache");
		header("Cache-Control: no-store, no-cache, max-age=0, must-revalidate");
		header('Content-Type: text/x-json');
		
		$ci->load->view($this->view_path.'image_user_info', $data);
	}
	
	/**
	*
	**/
	private function change_view($view){
		$ci = &get_instance();
		$ci->session->set_userdata('cimm_view', $view);
	}
	
	/**
	*
	**/
	public function change_view_adv($args = array('view' => '')) {
		$ci = &get_instance();
		$ci->session->set_userdata('cimm_view', $args['view']);
	}

	/**
	* get extension of filename
	**/
	public static function get_extension($filename) {
		return end(explode('.', $filename));
	}
  	
} // class TinyCIMM_image
