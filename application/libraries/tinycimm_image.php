<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
 
class TinyCIMM_image extends TinyCIMM {

	/**
	* get folder listing from db
	*
	* @TODO if we move to a category system, this will become obsolete. @Liam
	**/
	public function get_folder_list($arg) {
		header("Cache-Control: no-cache, must-revalidate");
		header("Cache-Control: no-store");

		// get all folders
		$sql = 'SELECT * FROM asset_folder
			ORDER by caption ASC';
		$query = $this->db->query($sql);
		$data['folders'] = array();
		foreach($query->result_array() AS $folderinfo) {
			$data['folders'][] = $folderinfo;
		}
		$this->load->view($this->config->item('tinycimm_views_root').'image_folder_list', $data);
  	}
  
	/**
	* get folder listing from db
	**/
	public function get_file_folder_list($arg) {
	
		// first we get info on uncategorized ROOT folder
		$sql = 'SELECT id FROM asset
			WHERE folder = \'\'';
		$query = $this->db->query($sql);
	
		// DEFAULT current folder info, default root folder info
		$data['folderinfo'] = array('id'=>'0','user_id' => '0','username' => 'demo','caption' => 'General', 'num_files' => $query->num_rows());
		// create [all folders] list
		$data['folders'][] = $data['folderinfo'];

		foreach($this->tinycimm_model->get_asset_folders() as $folderinfo) {
	 		// number of images in folder
		  	$sql = 'SELECT id FROM asset
				WHERE folder = ?
				ORDER BY filename';
	
			$file_query = $this->db->query($sql, array($folderinfo['id']));
			$folderinfo['num_files'] = $file_query->num_rows();
			$data['folders'][] = $folderinfo;
	  
			// current folder info
			if ($folderinfo['id'] == $arg['folder']) {
				// DEMO PURPOSES
				$folderinfo['username'] = 'demo';
				// DEMO PURPOSES
				$data['folderinfo'] = $folderinfo;
		  	}
		}
	
		// get all files in cur folder
		$sql = 'SELECT * 
				FROM asset
				WHERE folder = ?
				ORDER by filename ASC';
		$query = $this->db->query($sql, array($arg['folder']));
		$data['images'] = array();
		$totimagesize = 0;
		
		foreach($query->result_array() AS $image) {
	  		TinyCIMM_image::gen_thumb($this->config->item('tinycimm_image_thumb_upload_path').$image['filename']);
	  		
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
		$data['folderinfo']['tot_file_size'] = ($totimagesize > 1024) ? round($totimagesize/1024, 2).'mb' : $totimagesize.'kb';

		header("Pragma: no-cache");
		header("Cache-Control: no-store, no-cache, max-age=0, must-revalidate");
		header('Content-Type: text/x-json'); 
		$this->load->view($this->config->item('tinycimm_views_root').'image_'.$this->session->userdata('cimm_view').'_list', $data);
	}
  
	/**
	* this geneates a javascript array of images that is
	* used in the adv image dialog window as drop down list
	**/
	public function image_list() {
		header("text/javascript");

		$output = 'var tinyMCEImageList = new Array('."\n";
		// get images in folder
		$sql = 'SELECT img.*, fld.caption AS foldername
			FROM asset
			LEFT JOIN asset_folder
				ON asset.folder = asset_folder.id
			ORDER by asset.folder ASC, asset.caption ASC';
		$query = $this->db->query($sql);
		
		foreach($query->result_array() AS $image) {
			$image['foldername'] = 'General/'.($image['foldername']!=''?$image['foldername'].'/':'');
			
			/**
			* @TODO Assumes uploaded folder is /images/uploaded/
			* @Liam
			**/
			$output .= '["'.$image['foldername'].$image['caption'].'", "'.base_url('images/uploaded/'.$image['filename']).'", "desc"],'."\n";
			/**/
			
		}
		
		die(rtrim($output, ",\n").');');
  	}
  	
  	/**
  	* 
  	**/
	public function setview($args) {
		$this->session->set_userdata('cimm_view', $args['view']);
	}
	
	/**
	*
	**/
	public function update_details($arg) {
		$sql = 'UPDATE asset
			SET caption = ?, alttext = ?, folder = ?
			WHERE id = ?
			LIMIT 1';
		$this->db->query($sql, array($arg['caption'], $arg['alttext'], $arg['folder'], $arg['imageid']));
	}

	/**
	* upload image to dir and insert info into DB
	**/
	public function upload_image() {
		$upload_config = $this->config->item('upload_config');
		// if file has been selected
		if (isset($_FILES[$upload_config['field_name']]['name']) AND $_FILES[$upload_config['field_name']]['name'] != '') {

			// load upload library
			$this->load->library('upload', $this->config->item('upload_config'));
	
			if (!$this->upload->do_upload($upload_config['field_name']))  {
			 	/* upload failed */  
				$this->tinymce_alert('There was an error processing the request: '.$this->upload->display_errors());
				exit;
	  		}
			$image_data = $this->upload->data();
			$max_x = (int)$this->input->post('max_x');
			$max_y = (int)$this->input->post('max_y');
			$adjust_size = (int)$this->input->post('adjust_size');
			
			// resize image
			if ($adjust_size == 1 AND ($image_data['image_width'] > $max_x OR $image_data['image_height'] > $max_y)) {
				/*	
				$resize_config = $this->config->item('resize_config');		
				$resize_config['source_image'] = $image_data['full_path'];
				$resize_config['width'] = $max_x;
				$resize_config['height'] = $max_y;
				$this->load->library('image_lib', $resize_config);
				
				//  resize image
				if (!$this->image_lib->resize()) {
					$this->tinymce_alert($this->image_lib->display_errors());
					exit;
				}
	
				// store new dimensions
				$image_data['image_width'] = $this->image_lib->width;
				$image_data['image_height'] = $this->image_lib->height;
				*/
			}
	
			$alttext = str_replace($image_data['file_ext'], '', strtolower($image_data['orig_name']));
			$folder = (int) $this->input->post('uploadfolder');
			  
			// update the fileid for the photo in db
			$sql = 'INSERT INTO asset (caption, filename, alttext, folder, dateadded)
				VALUES (?, ?, ?, ?, ?)';
			$query = $this->db->query($sql, array(basename($image_data['orig_name']), basename($image_data['full_path']), $alttext, $folder, time()));
			$lastid = $this->db->insert_id();
			
			/**
			* @TODO Assumes uploaded folder is /images/uploaded/
			* @Liam
			**/
			die("<script type=\"text/javascript\">
			parent.removedim();
			parent.updateimg('".base_url('images/uploaded/'.basename($image_data['full_path']))."', '".$alttext."');
			</script>");
			/**
			* @TODO Assumes uploaded folder is /images/uploaded/
			* @Liam
			**/
			  
		}
		// no file specified to upload
		else {
			echo "<script type=\"text/javascript\">
			parent.removedim();
			parent.parent.tinyMCEPopup.editor.windowManager.alert('Please select an image to upload.');
			</script>";
			exit;
		}
  	}
  	
  	/**
  	*
  	**/
	public function delete_image($arg) {
		$image_id = isset($arg['image']) ? (int) $this->input->xss_clean($arg['image']) : 0;

		if (!$image = TinyCIMM_model::get_asset($image_id)) {
			$response['outcome'] = 'error';
			$response['message'] = 'Image not found.';
			TinyCIMM::response_encode($response);
		}
		TinyCIMM::delete_asset($image_id);
		$response['outcome'] = 'success';
		$response['message'] = 'Image successfully deleted.';
		$response['folder'] = $image->folder;
		TinyCIMM::response_encode($response);
	}
	
	/**
	*
	**/
	public function get_image($image_id){
		die(print_r(TinyCIMM_model::get_asset($image_id)));
	}
  	
  	/**
  	* @TODO would become obsolete if we switched away from a multi folder system and went with categories @Liam
  	**/
	public function delete_folder($arg) {
		$folder = isset($arg['folder']) ? (int) $this->input->xss_clean($arg['folder']) : 0;
		
		if ($folder > 0 ) {
			// move images from folder to root folder
			$this->db->query('UPDATE asset SET folder = \'\' WHERE folder = ?', array($folder));
			$images_affected = $this->db->affected_rows();
		
			// remove folder
			$this->db->query('DELETE FROM asset_folder WHERE id = ?', array($folder));
	  
			// get new list of folders
			$sql = 'SELECT *
				FROM asset_folder
				WHERE user_id = ?
				ORDER by caption ASC';
			$query = $this->db->query($sql, array($this->user_id));
			$data['folders'][0] = array('id'=>0,'caption'=>'General');
			foreach($query->result_array() AS $folderinfo) {
		 		$data['folders'][] = $folderinfo;
			}
			die($this->load->view($this->config->item('tinycimm_views_root').'image_folder_list', $data, true).'<div style="display:none" id="message">'.(($images_affected>0?$images_affected.' image'.($images_affected==1?'':'s').' moved to General folder.':'').'</div>'));
		} else {
			$response['outcome'] = 'error';
			$response['message'] = 'You can\'t delete this folder.';
		}
	
		TinyCIMM::response_encode($response);
 	}
  	
  	/**
  	* @TODO would become obsolete if we switched away from a multi folder system and went with categories @Liam
  	**/
	public function add_folder($arg){ 
		$caption = isset($arg['caption']) ? $this->input->xss_clean($arg['caption']) : '';
	
		if ($caption == '') {
			$response['outcome'] = 'error';
			$response['message'] = 'Please specify a valid folder name.';
		} else if (strlen($caption) == 1) {
			$response['outcome'] = 'error';
			$response['message'] = 'The folder name must be at least 2 characters in length.';
		} else if (strlen($caption) > 24) {
			$response['outcome'] = 'error';
			$response['message'] = "The folder name must be less than 24 characters.\n(The supplied folder name is "+captionID.length+" characters).";
		}
	
		if (isset($response)) {
			TinyCIMM::response_encode($response);
		}
	
		$sql = 'INSERT INTO asset_folder (caption)
			VALUES (?)';
		$query = $this->db->query($sql, array($caption));
		$lastid = $this->db->insert_id();

		// get new list of folders
		$sql = 'SELECT *
			FROM asset_folder
			WHERE user_id = ?
			ORDER by caption ASC';
		$query = $this->db->query($sql, array($this->user_id));
		$data['folders'][0] = array('id'=>0,'caption'=>'General');
		foreach($query->result_array() AS $folderinfo) {
			$data['folders'][] = $folderinfo;
		}
	
		die($this->load->view($this->config->item('tinycimm_views_root').'image_folder_list', $data, true));
  	}
  	
  	/**
  	* @TODO would become obsolete if we switched away from a multi folder system and went with categories @Liam
  	**/
	public function edit_folder($arg) {
		header("Cache-Control: no-cache, must-revalidate");
		header("Cache-Control: no-store");
		
		$folder = isset($arg['folder']) ? (int) $this->input->xss_clean($arg['folder']) : '';

		if ($folder == '' OR $folder == '0') {
			echo '';
		} else {
			$sql = 'SELECT caption
				FROM asset_folder
				WHERE id = ?
				LIMIT 1';
			$query = $this->db->query($sql, array($folder));
			$folderinfo = $query->row_array();
			$html = '<input type="text" value="'.$folderinfo['caption'].'" class="input" style="font-family: tahoma, verdana, sans-serif;font-size:11px;float:left;padding:0 1px" />';
		  	$html .= '<img style="float:left;margin-left:3px" title="save" src="/images/save.gif" />';
			$html .= '<img style="float:left;margin-left:3px" title="cancel/undo" onclick="getFolderList()" src="/images/admin/undo.gif" />';
			$html .= '<br class="clear" />';
			echo $html;
		}
		exit;
	}
  	
  	/**
  	* @TODO would become obsolete if we switched away from a multi folder system and went with categories @Liam
  	**/
	public function get_folder_select($args){
		$data['folderid'] = isset($args['folder']) ? (int) $args['folder'] : 0;

		// get all folders
		$sql = 'SELECT * 
			FROM asset_folder
			WHERE user_id = ?
			ORDER by caption ASC';
		$query = $this->db->query($sql, array($this->user_id));
	
		$data['folders'] = array();
		foreach($query->result_array() AS $folderinfo) {
			$data['folders'][] = $folderinfo;
		}
		die($this->load->view($this->config->item('tinycimm_views_root').'image_folder_select', $data, true));
	}
	
	/**
	*
	**/
	public function get_alttext_textbox($args){
		$data['alttext'] = isset($args['alttext']) ? $this->input->xss_clean($args['alttext']) : '';
		die($this->load->view($this->config->item('tinycimm_views_root').'image_alttext_textbox', $data, true));
	}
	
	/**
	*
	**/
	public function save_image_size($args){
		if (!ctype_digit($args['width']) or !ctype_digit($args['height'])) {
			TinyCIMM::response_encode(array('outcome'=>'error','message'=>'Incorrect dimensions supplied. (Cant have value of 0)'));
		}

		TinyCIMM::save_image_size($args['img'], (int)$args['width'], (int)$args['height'], 90); 
		$response['outcome'] = 'success';
		$response['message'] = 'Image size successfully saved.';
		TinyCIMM::response_encode($response);
	}

  
	/**
	* get image details from db
	**/
	public function get_image_info($arg = array('image' => '')) {
		$image = isset($arg['image']) ? basename($this->input->xss_clean($arg['image'])) : '';

		// get info image
		$sql = 'SELECT * 
			FROM asset 
			WHERE filename = ?
			LIMIT 1';
		$query = $this->db->query($sql, array($image));
		if ($query->num_rows() == 0) {
			$response['outcome'] = 'error';
			$response['message'] = 'Image not found in database.';
		}
		else {
			$response = $query->row_array();
			$this->gen_thumb($this->config->item('tinycimm_image_thumb_upload_path').$image);
			$response['outcome'] = 'success';
		}
	
		TinyCIMM::response_encode($response);
  	}
	
	/**
	* @TODO not sure if its worth assuming a multi-user system yet.
	**/
	public function get_user_info() {
		// get user info: total images uploaded, privelages, max upload sizes etc etc
	
		$sql = 'SELECT COUNT(id) AS tot_images
			FROM asset 
			WHERE user_id = ?';
		$query = $this->db->query($sql, array($this->user_id));
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
		
		$this->load->view($this->config->item('tinycimm_views_root').'image_user_info', $data);
	}
	
	/**
	*
	**/
	private function change_view($arg = array('view' => '')){
		$this->session->set_userdata('cimm_view', $arg['view']);
		$this->get_thumbs(array('folder' => ''));
	}
	
	/**
	*
	**/
	public function change_view_adv($args = array('view' => '')) {
		$this->session->set_userdata('cimm_view', $args['view']);
		$this->get_file_folder_list(array('folder' => ''));
	}

	/**
	* get extension of filename
	**/
	public static function get_extension($filename) {
		$ext = array_reverse(explode('.', $filename));
		return strtolower($ext[0]);
	}
  	
  	/**
  	* generate a thumbnail of an image using CI's image library class
  	**/
	public function gen_thumb($thumb_file='', $width=95, $height=95) {
		if(file_exists($thumb_file) == FALSE) {
			$config['image_library'] = 'gd2';
			$config['source_image'] = $this->config->item('tinycimm_image_upload_path').basename($thumb_file);
			$config['new_image'] = $thumb_file;
			$config['maintain_ratio'] = FALSE;
			$config['height'] = $height;
			$config['width'] = $width;
			$this->image_lib->initialize($config);
			$this->image_lib->resize();
			$this->image_lib->clear();
		}
	}
 
} // class TinyCIMM_image