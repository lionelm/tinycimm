<script type="text/javascript" src="<?=base_url().'js/tiny_mce/tiny_mce.js';?>"></script>
<script type="text/javascript">
tinyMCE.init(
{
	document_base_url : "<?=base_url();?>",
	mode : "exact",
	elements : "demo_textarea",
	convert_urls : true,
	relative_urls : false,
	theme : "advanced",
	plugins : "tinycimm,advimage,advlink,media,paste,pagebreak,inlinepopups,contextmenu",
	theme_advanced_buttons1 : "bold,italic,|,undo,redo,|,tinycimm-image,media,|,link,unlink,|,code,help",
	//theme_advanced_buttons1 : "bold,italic,|,undo,redo,|,image,tinycimm-image,media,|,link,unlink,|,code,help",
	theme_advanced_buttons2 : "",
	theme_advanced_buttons3 : "",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_path_location : "bottom",
	theme_advanced_resizing : true,
	theme_advanced_resize_horizontal : false,
	paste_use_dialog : false,
	paste_convert_headers_to_strong : true,
	cleanup : true,
	apply_source_formatting : true,
	force_hex_style_colors : true,
	button_tile_map : true,
	file_browser_callback : 'tinycimm',
	tinycimm_controller : '<?=$this->config->item('tinycimm_controller');?>',
	tinycimm_assets_path : '<?=$this->config->item('tinycimm_asset_path');?>',
	tinycimm_resize_default_intial_width : '<?=$this->config->item('default_initial_width', 'tinycimm_image_resize_config');?>',
	tinycimm_thumb_width : '<?=$this->config->item('tinycimm_image_thumbnail_default_width');?>',
	tinycimm_thumb_height : '<?=$this->config->item('tinycimm_image_thumbnail_default_height');?>',
	tinycimm_thumb_lightbox_class : '<?=$this->config->item('tinycimm_image_thumbnail_default_lightbox_class');?>',
	tinycimm_thumb_lightbox_gallery : '<?=$this->config->item('tinycimm_image_thumbnail_default_lightbox_gallery');?>'
});

function tinycimm(field_name, url, type, win) {

	var url = win.tinyMCE.baseURI.relative+'/plugins/tinycimm/'+type+'.htm';

	tinyMCE.activeEditor.windowManager.open({
		file : url,
		width : 574,
		height : 462,
		resizable : "yes",
		inline : "yes",  
		close_previous : "no"
	}, {
		window : win,
		tinyMCEPopup : win.tinyMCEPopup,
		input : field_name
	});
	return false;
}
</script>
