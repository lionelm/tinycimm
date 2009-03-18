<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
 
class TinyCIMM {

	public function save_image_size($image_id=0, $width=0, $height=0){
		$ci =&get_instance();

		// get image info, to ensure image src exists
		$sql = 'SELECT *
			FROM images
			WHERE id = ?
			LIMIT 1';
		$query = $ci->db->query($sql, array($image_id));
		if(!$query->num_rows()) {
			die('not found');
		}
		$imageinfo = $query->row_array();
		
		$img_path = $ci->image_path.$ci->orig_path.$imageinfo['filename'];
		
		$config['image_library'] = 'gd2';
		$config['source_image'] = $image_path;
		$config['new_image'] = $image_path;
		$config['maintain_ratio'] = FALSE;
		$config['height'] = $height;
		$config['width'] = $width;
		$ci->image_lib->initialize($config);
		$ci->image_lib->resize();
		$ci->image_lib->clear();

		die('hello');
	}
}
