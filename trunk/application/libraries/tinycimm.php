<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
 
class TinyCIMM {
	
	/**
	* Resizes an image using CI's image library
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
	public function delete_asset($image){
		Tinycimm_model::delete_asset($image->id);

		// delete images from filesystem, including original and thumbnails
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
		$response_txt = '{';
		foreach($response AS $key => $value) {
			$response_txt .= '"'.$key.'":"'.$value.'",';
		}
		$response_txt = rtrim($response_txt, ',').'}';
		die($response_txt);
	}
	
}
