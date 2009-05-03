<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

$config['tinycimm_controller'] = '/assetmanager/';
$config['tinycimm_views_root'] = 'tinycimm/';
$config['tinycimm_views_root_image'] = 'image/';
$config['tinycimm_asset_path'] = $_SERVER['DOCUMENT_ROOT'].'/assets/';
$config['tinycimm_asset_cache_path'] = $config['tinycimm_asset_path'].'cache/';

$config['tinycimm_pagination_per_page'] = 10;

// set to either 0777 or 0755 depending on your server setup
$config['tinycimm_asset_path_chmod'] = 0777;

// upload configuration variables
$config['tinycimm_upload_config']['field_name'] = 'fileupload';
$config['tinycimm_upload_config']['upload_path'] = $config['tinycimm_asset_path'];
$config['tinycimm_upload_config']['allowed_types'] = 'gif|jpg|png';
$config['tinycimm_upload_config']['max_size'] = '6800';
$config['tinycimm_upload_config']['max_width']  = '5000';
$config['tinycimm_upload_config']['max_height']  = '5000';

// image resize
$config['tinycimm_image_resize_config']['image_library'] = 'GD2';
$config['tinycimm_image_resize_config']['maintain_ratio'] = TRUE;
$config['tinycimm_image_resize_config']['create_thumb'] = FALSE;
$config['tinycimm_image_resize_config']['width'] = 1024;
$config['tinycimm_image_resize_config']['height'] = 768;
$config['tinycimm_image_resize_config']['quality'] = 90;

?>
