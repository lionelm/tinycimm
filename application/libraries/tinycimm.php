<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
 
class TinyCIMM {
	
	/**
	* Resizes an image using CI's image library class (GD2)
	**/
	public function save_image_size($image_filename=0, $width=0, $height=0){
		$ci =&get_instance();
		$image_path = $ci->config->item('tinycimm_image_upload_path').$image_filename;
		$config['image_library'] = 'gd2';
		$config['source_image'] = $image_path;
		$config['new_image'] = $image_path;
		$config['maintain_ratio'] = FALSE;
		$config['height'] = $height;
		$config['width'] = $width;
		$ci->image_lib->initialize($config);
		$ci->image_lib->resize();
		$ci->image_lib->clear();
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
    *
    * Added config variable to allow user to choose between 0777 and 0755, as different server setups require different settings
    **/    
	public function check_paths() {
		// what CHMOD permissions should we use for the upload folders?
		$chmod = $this->config->item('tinycimm_asset_upload_chmod');
		
		// image dir
		if (!file_exists($this->config->item('tinycimm_image_upload_path'))) {
			@mkdir($this->config->item('tinycimm_image_upload_path'), $chmod) OR show_error('Unable to create image folder '.$this->config->item('tinycimm_image_upload_path').'<br/><strong>Please adjust permissions</strong>');
		}
		// thumb dir
		if (!file_exists($this->_full_thumb_path = $this->config->item('tinycimm_image_thumb_upload_path'))) {
			@mkdir($this->_full_thumb_path, $chmod) OR show_error('Unable to create thumbnails folder '.$this->_full_thumb_path.'<br/><strong>Please adjust permissions</stro
ng>');
		}
		// orig dir
		if (!file_exists($this->_full_orig_path = $this->config->item('tinycimm_image_upload_path'))) {
			@mkdir($this->_full_orig_path, $chmod) OR show_error('Unable to create image folder '.$this->_full_orig_path.'<br/><strong>Please adjust permissions</strong>');
		}
	}
	
	/**
	* Throw up an alert message
	**/
	public function tinymce_alert($message){
		echo "<script type=\"text/javascript\">
		parent.parent.tinyMCEPopup.editor.windowManager.alert('".$message."');
		</script";
	}
	
} // class TinyCIMM
?>