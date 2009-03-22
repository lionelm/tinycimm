<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
 
class TinyCIMM {

	/**
	* upload asset to directory and insert info into DB
	**/
	public function upload_asset() {
		$upload_config = $this->config->item('upload_config');
		// if file has been uploaded
		if (isset($_FILES[$upload_config['field_name']]['name']) AND $_FILES[$upload_config['field_name']]['name'] != '') {

			// load upload library
			$this->load->library('upload', $this->config->item('upload_config'));

			// move file into specified upload directory	
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
	* Deletes a file from the database and from the fileserver
	* Goes on to also delete any new files that were created as a result of resizing the image
	**/
	public function delete_asset($asset_id){
		// lets drop it from the database
		Tinycimm_model::delete_asset($asset_id) or die(TinyCIMM::tinymce_alert('asset not found'));

		// delete images from filesystem, including original and thumbnails
		// @TODO this assumes the asset is an image @Liam
		if (file_exists($this->image_path.$image->filename)) {
			@unlink($this->image_path.$image->filename);
		}
		if (file_exists($this->image_path.$this->orig_path.$image->filename)) {
			@unlink($this->image_path.$this->orig_path.$image->filename);
		}
		if (file_exists($this->image_path.$this->thumb_path.$image->filename)) {
			@unlink($this->image_path.$this->thumb_path.$image->filename);
		}

		// delete the new size specific files				
		if ($handle = @opendir($this->image_path)) {
			while (FALSE !== ($file = readdir($handle))) {
				if (strpos($file, $image->filename) !== FALSE) {
					@unlink($this->image_path.$file);
				}
			}	
			@closedir($handle);
		}
	}

	/**
	* Takes a PHP array and outputs it as a JOSN array to screen using PHP's die function
	*
	* @param $response an array in PHP
	**/
	public function response_encode($response=array()) {
		header("Pragma: no-cache");
		header("Cache-Control: no-store, no-cache, max-age=0, must-revalidate");
		header('Content-Type: text/x-json');
		if (function_exists("json_encode")) {
			die(json_encode($response));
		} else {
			$response_txt = '{';
			foreach($response AS $key => $value) {
				$response_txt .= '"'.$key.'":"'.$value.'",';
			}
			$response_txt = rtrim($response_txt, ',').'}';
			die($response_txt);
		}
	}

	
	/** 
	* check if image directories exist, if not then try to create them with 0777/0755 permissions
	* Added config variable to allow user to choose between 0777 and 0755, as different server setups require different settings
	**/    
	public function check_paths() {
		// what CHMOD permissions should we use for the upload folders?
		$chmod = $this->config->item('tinycimm_asset_upload_chmod');
		
		// upload dir
		if (!file_exists($this->config->item('tinycimm_image_upload_path'))) {
			@mkdir($this->config->item('tinycimm_image_upload_path'), $chmod) or die('Error: Unable to create upload folder '.$this->config->item('tinycimm_image_upload_path').'<br/><strong>Please adjust permissions</strong>');
		}
		// cache dir
		if (!file_exists($this->config->item('tinycimm_image_upload_cache_path'))) {
			@mkdir($this->config->item('tinycimm_image_upload_cache_path'), $chmod) or die('Error: Unable to create cache folder '.$this->config->item('tinycimm_image_upload_cache_path').'<br/><strong>Please adjust permissions</strong>');
		}
	}
	
	/**
	* Throw up an alert message using TinyMCE's alert method (only used in upload function at this time)
	**/
	public static function tinymce_alert($message){
		echo "<script type=\"text/javascript\">
		parent.parent.tinyMCEPopup.editor.windowManager.alert('".$message."');
		</script";
	}
	
} // class TinyCIMM
?>
