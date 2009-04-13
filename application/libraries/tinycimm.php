<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
 
class TinyCIMM {

	public function __construct(){
		$ci = &get_instance();
                $this->db = &$ci->db;
                $this->config = &$ci->config;
		$this->input = &$ci->input;
	}

	public function get_asset($asset_id, $width=200, $height=200, $quality=85, $send_nocache=true){
		$ci = &get_instance();
		$asset = $ci->tinycimm_model->get_asset($asset_id) or die('asset not found');
		$asset->filepath = $this->config->item('tinycimm_asset_path').$asset_id.$asset->extension;
		if (!@file_exists($asset->filepath)) {
			die('asset not found');
		}
		
		$resize_asset = $this->resize_asset($asset, $width, $height, $quality);

		header('Content-Transfer-Encoding: binary');
		if ($send_nocache) {
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		}
		header('Content-type: '.$resize_asset->mimetype);
		header("Content-Length: ".filesize($resize_asset->filepath));
		//ob_clean();
		flush();
		readfile($resize_asset->filepath);
	}

	public function resize_asset($asset, $width=200, $height=200, $quality=85, $cache=true){
		$ci = &get_instance();
		if ($cache) {
			$asset->new_filepath = $this->config->item('tinycimm_asset_path').'cache/'.$asset->id.'_'.$width.'_'.$height.'_'.$quality.$asset->extension;
		} else {
			$asset->new_filepath = $asset->filepath;
		}
		$resize_config = $this->config->item('tinycimm_image_resize_config');		
		$resize_config['source_image'] = $asset->filepath;
		$resize_config['new_image'] = $asset->new_filepath;
		$resize_config['width'] = $width;
		$resize_config['height'] = $height;
		$ci->load->library('image_lib');
		$ci->image_lib->initialize($resize_config);
		if (!$ci->image_lib->resize()) {
			$this->tinymce_alert($ci->image_lib->display_errors());
			exit;
		}
		$asset->filepath = $asset->new_filepath;
		return $asset;
	}

	/**
	* upload asset to directory and insert info into DB
	**/
	public function upload_asset() {
		$ci = &get_instance();
		$upload_config = $this->config->item('tinycimm_upload_config');
		// if file has been uploaded
		if (isset($_FILES[$upload_config['field_name']]['name']) AND $_FILES[$upload_config['field_name']]['name'] != '') {

			// load upload library
			$ci->load->library('upload', $upload_config);

			// move file into specified upload directory	
			if (!$ci->upload->do_upload($upload_config['field_name']))  {
			 	/* upload failed */  
				$this->tinymce_alert('There was an error processing the request: '.$this->upload->display_errors());
				exit;
	  		}
			$image_data = $ci->upload->data();
			$alttext = str_replace($image_data['file_ext'], '', strtolower($image_data['orig_name']));
			$folder = (int) $ci->input->post('uploadfolder');

			$last_insert_id = $ci->tinycimm_model->get_last_id('asset');
			$last_insert_id++;
		 
			// insert the asset info into the db
			$ci->tinycimm_model->insert_asset($folder, basename($image_data['orig_name']), $last_insert_id.$image_data['file_ext'], $alttext, $image_data['file_ext'], $_FILES[$upload_config['field_name']]['type']);

			// rename the uploaded file, CI's Upload library does not handle custom file naming 	
			rename($image_data['full_path'], $image_data['file_path'].$last_insert_id.$image_data['file_ext']);
			
			$max_x = (int) $ci->input->post('max_x');
			$max_y = (int) $ci->input->post('max_y');
			$adjust_size = (int) $ci->input->post('adjust_size');
			
			// resize image
			if ($adjust_size === 1 AND ($image_data['image_width'] > $max_x OR $image_data['image_height'] > $max_y)) {
				$resize_config = $this->config->item('tinycimm_resize_config');		
				$resize_config['source_image'] = $image_data['file_path'].$last_insert_id.$image_data['file_ext'];
				$resize_config['width'] = $max_x;
				$resize_config['height'] = $max_y;
				$ci->load->library('image_lib');
				$ci->image_lib->initialize($resize_config);
				if (!$ci->image_lib->resize()) {
					$this->tinymce_alert($ci->image_lib->display_errors());
					exit;
				}
			}
			
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
			die("<script type=\"text/javascript\">
                        parent.removedim();
			parent.parent.tinyMCEPopup.editor.windowManager.alert('Please select an image to upload.');
                        </script>");
			//$this->tinymce_alert('Please select an image to upload');
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
	* Takes a PHP array and outputs it as a JSON array to screen using PHP's die function
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
		if (!file_exists($this->config->item('tinycimm_asset_path'))) {
			@mkdir($this->config->item('tinycimm_asset_path'), $chmod) or die('Error: Unable to create asset folder '.$this->config->item('tinycimm_asset_path').'<br/><strong>Please adjust permissions</strong>');
		}
		// cache dir
		if (!file_exists($this->config->item('tinycimm_asset_cache_path'))) {
			@mkdir($this->config->item('tinycimm_asset_cache_path'), $chmod) or die('Error: Unable to create asset cache folder '.$this->config->item('tinycimm_asset_cache_path').'<br/><strong>Please adjust permissions</strong>');
		}
	}
	
	/**
	* Throw up an alert message using TinyMCE's alert method (only used in upload function at this time)
	**/
	public static function tinymce_alert($message){
		echo "<script type=\"text/javascript\">
		parent.removedim();
		parent.parent.tinyMCEPopup.editor.windowManager.alert('".$message."');
		</script";
	}
	
} // class TinyCIMM
?>
