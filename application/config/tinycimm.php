<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

// image resize
$config['resize_config']['image_library'] = 'GD2';
$config['resize_config']['maintain_ratio'] = TRUE;
$config['resize_config']['create_thumb'] = FALSE;
$config['resize_config']['width'] = 1024;
$config['resize_config']['height'] = 768;
$config['resize_config']['quality'] = 90;

$config['tinycimm_views_root'] = 'media/ajaxfilemanager/';
$config['tinycimm_image_upload_path'] = $_SERVER['DOCUMENT_ROOT'].'/images/uploaded/';
$config['tinycimm_image_thumb_upload_path'] = $config['tinycimm_image_upload_path'].'thumbs/';

// set to either 0777 or 0755 depending on your server setup
$config['tinycimm_asset_upload_chmod'] = 0777;

// upload configuration variables
$config['upload_config']['field_name'] = 'fileupload';
$config['upload_config']['upload_path'] = $config['tinycimm_image_upload_path'];
$config['upload_config']['allowed_types'] = 'gif|jpg|png';
$config['upload_config']['max_size'] = '6800';
$config['upload_config']['max_width']  = '5000';
$config['upload_config']['max_height']  = '5000';

?>
