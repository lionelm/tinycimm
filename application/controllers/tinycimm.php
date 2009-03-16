<?php
/*
Filename:   tinycimm.php
Developer:  Richard Willis
Copyright:	Richard Willis
TinyCIMM media manager
*/

class Tinycimm extends Controller
{
	
	var $image_url;
	var $image_path;
	var $thumb_path;
	var $orig_path;
	var $upload_path;
	var $view;          // Options: thumbnails (default); list;
	var $user_id;
	var $upload_config;
	var $resize_config;
  
	function Tinycimm()
	{
		parent::Controller();

		// SECURE the media manager here
		// eg: $this->auth->check('admin');
		// USERID
		// eg: $this->user_id = $this->auth->user_id OR die('You are not logged in.');
		$this->user_id = 1;
		
		// private vars
		$this->_view_base = 'media/ajaxfilemanager/';
		
		// class vars
		$this->image_url = '/images/uploaded/';
		$this->image_path = $_SERVER['DOCUMENT_ROOT'].$this->image_url;
		$this->thumb_path = 'thumbs/';
		$this->orig_path = 'originals/';
		
		// perhaps the following config can be stored in a separate config file
		
		// upload config
		$this->upload_config['field_name'] = 'fileupload';
		$this->upload_config['upload_path'] = './images/uploaded/originals';
		$this->upload_config['allowed_types'] = 'gif|jpg|png';
		$this->upload_config['max_size'] = '6800';
		$this->upload_config['max_width']  = '5000';
		$this->upload_config['max_height']  = '5000';
    
		// image resize config
		$this->resize_config['image_library'] = 'GD2';
		$this->resize_config['maintain_ratio'] = TRUE;
		$this->resize_config['create_thumb'] = FALSE;
		$this->resize_config['width'] = 1024;
		$this->resize_config['height'] = 768;
		$this->resize_config['quality'] = 90;

		// check image directories exist
		$this->_check_paths();

		// set view type in user session
		if (!$this->db_session->userdata('cimm_view')) {
			$this->db_session->set_userdata('cimm_view', 'thumbnails');
			$this->view = 'thumbnails';
		}
		else {
			$this->view = $this->db_session->userdata('cimm_view');
		}
    
  	}
	
	function index(){	
	}
 

	// determines what manager to use: image or media
	function image() {
		$this->image_manager();
  	}
  
  	function image_manager() { 
		$method = $this->uri->segment(3);
		$args = $this->uri->uri_to_assoc(4);

		if (method_exists($this, $method)) {
			$this->$method($args);
		} else {
			die('bad url');
		}
  	}

	// get folder listing from db
  function get_folder_list($arg) 
  {
    header("Cache-Control: no-cache, must-revalidate");
    header("Cache-Control: no-store"); 

    // get all folders
    $sql = 'SELECT * 
            FROM image_folders 
            ORDER by caption ASC';
    $query = $this->db->query($sql);
    $data['folders'] = array();
    foreach($query->result_array() AS $folderinfo) {
     $data['folders'][] = $folderinfo;     
    }

    $this->load->view($this->_view_base.'image_folder_list', $data);
  }
  
  // get folder listing from db
  function get_file_folder_list($arg) 
  {
    
    // first we get info on uncategorized ROOT folder
    $sql = 'SELECT id FROM images
            WHERE folder = \'\'';
    $query = $this->db->query($sql);
    
    // DEFAULT current folder info, default root folder info
    $data['folderinfo'] = array('id' => '0', 
                                'user_id' => '0', 
																'username' => 'demo',
                                'caption' => 'General', 
                                'num_files' => $query->num_rows());
    // create [all folders] list
    $data['folders'][] = $data['folderinfo'];

    // get all folders info (this exludes 'root' folder as it has no info in db)
    $sql = 'SELECT * 
            FROM image_folders 
            ORDER by caption ASC';
    $query = $this->db->query($sql);
    foreach($query->result_array() AS $folderinfo) {
      // number of images in folder
      $sql = 'SELECT id FROM images
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
            FROM images
            WHERE folder = ?
            ORDER by filename ASC';
    $query = $this->db->query($sql, array($arg['folder']));
    $data['images'] = array();
		$totimagesize = 0;
    foreach($query->result_array() AS $image) {
    	
      $this->_gen_thumb($this->image_path.$this->thumb_path.$image['filename']);
      
      $imgsize = ($imgsize = @getimagesize('./images/uploaded/'.$image['filename'])) ? $imgsize : array(0,0);
      $image['width'] = $imgsize[0];
      $image['height'] = $imgsize[1];
      $image['dimensions'] = $imgsize[0].'x'.$imgsize[1];
      $image['extension'] = $this->_get_extension($image['filename']);
      $image['filesize'] = round(@filesize('./images/uploaded/'.$image['filename'])/1024, 0);
      $data['images'][] = $image;     
			$totimagesize = $totimagesize + $image['filesize'];			
      
    }
		// prepare total image size
		$data['folderinfo']['tot_file_size'] = ($totimagesize > 1024) ? round($totimagesize/1024, 2).'mb' : $totimagesize.'kb';

		header("Pragma: no-cache");  
    header("Cache-Control: no-store, no-cache, max-age=0, must-revalidate");  
    header('Content-Type: text/x-json'); 
    
    $this->load->view($this->_view_base.'image_'.$this->view.'_list', $data);
  }
  
  // this geneates a javascript array of images that is 
  // used in the adv image dialog window as drop down list
  function image_list()
  {
    header("text/javascript");

    $output = 'var tinyMCEImageList = new Array('."\n";
    // get images in folder
    $sql = 'SELECT img.*, fld.caption AS foldername 
            FROM images AS img
            LEFT JOIN image_folders fld
              ON img.folder = fld.id
            ORDER by img.folder ASC, img.caption ASC';
    $query = $this->db->query($sql);
    foreach($query->result_array() AS $image) {
      $image['foldername'] = 'General/'.($image['foldername']!=''?$image['foldername'].'/':'');
      $output .= '["'.$image['foldername'].$image['caption'].'", "'.base_url('images/uploaded/'.$image['filename']).'", "desc"],'."\n";
    }
    die(rtrim($output, ",\n").');');
  }
  
  function setview($args) {
    $this->view = $args['view'];
    $this->db_session->set_userdata('cimm_view', $args['view']);
  }
  
  // upload image to dir, insert image info into db
  function upload_image()
  {
    // if file has been selected
    if (isset($_FILES[$this->upload_config['field_name']]['name']) AND $_FILES[$this->upload_config['field_name']]['name'] != '')
    {
			// DEMO PURPOSES
			die("<script type=\"text/javascript\">
      parent.removedim();
      parent.parent.tinyMCEPopup.editor.windowManager.alert('This is a demo, no files were uploaded.');
			parent.parent.ImageDialog.showBrowser();
      </script>");

      // load upload library
      $this->load->library('upload', $this->upload_config);
      
      // try upload the file
      if (!$this->upload->do_upload($this->upload_config['field_name']))  {
         /* UPLOAD FAILED */   
        die("<script type=\"text/javascript\">
        parent.parent.tinyMCEPopup.editor.windowManager.alert('There was an error processing the request.');
        </script>");
      }
      
      /* UPLOAD SUCCESS */      
      $imgdata = $this->upload->data();
      
      // constrain dimensions?
      $adjust_size = (int) $this->input->post('adjust_size');
      $max_x = (int) $this->input->post('max_x');
      $max_y = (int) $this->input->post('max_y');
      
      // yes, resize image
      if ($adjust_size == 1 AND ($imgdata['image_width'] > $max_x OR $imgdata['image_height'] > $max_y)) 
      {
        $this->resize_config['source_image'] = $imgdata['full_path'];
        $this->resize_config['width'] = $max_x;
        $this->resize_config['height'] = $max_y;
        
        // load image library
        $this->load->library('image_lib', $this->resize_config);
        
        //  resize image
        if (!$this->image_lib->resize()) {
          //show_error($this->image_lib->display_errors());
          //exit;
        }
        // save new dimensions
        $imgdata['image_width'] = $this->image_lib->width;
        $imgdata['image_height'] = $this->image_lib->height;
      }


      //@copy($imgdata['full_path'], $this->image_path.(basename($imgdata['full_path'])));
          
      $alttext = str_replace($imgdata['file_ext'], '', strtolower($imgdata['orig_name']));
          
      $uploadfolder = (int) $this->input->post('uploadfolder');
          
      // update the fileid for the photo in db
      $sql = 'INSERT INTO images (caption, filename, alttext, folder, dateadded) 
              VALUES (?, ?, ?, ?, ?)';
      $query = $this->db->query($sql, array(basename($imgdata['orig_name']), basename($imgdata['full_path']), $alttext, $uploadfolder, time()));
      $lastid = $this->db->insert_id();
          
      die("<script type=\"text/javascript\">
      //alert('".$imgdata['file_ext']."');
      parent.removedim();
      parent.updateimg('".base_url('images/uploaded/'.basename($imgdata['full_path']))."', '".$alttext."');
        </script>");
          
    }
    // no file specified to upload
    else {
      die("<script type=\"text/javascript\">
      parent.removedim();
      parent.parent.tinyMCEPopup.editor.windowManager.alert('Please select an image to upload.');
      </script>");
    }
      
  }
  
  function delete_image($arg)
  {
    $imageid = isset($arg['image']) ? (int) $this->input->xss_clean($arg['image']) : 0;
    
    // get image info, filepath etc from db
    $sql = 'SELECT * 
            FROM images 
            WHERE id = ?
            LIMIT 1';
    $query = $this->db->query($sql, array($imageid));
    // error
    if ($query->num_rows() == 0) {
      $response['outcome'] = 'error';
      $response['message'] = 'Image not found.';
      $this->_tinymce_serialize($response);
    }
    
    $imageinfo = $query->row_array();

    // delete image info from db
    $sql = 'DELETE 
            FROM images 
            WHERE id = ?
            LIMIT 1';
    $query = $this->db->query($sql, array($imageid));

    // now delete images from filesystem, including original and thumbnails
    if (file_exists($this->image_path.$imageinfo['filename'])) {
      @unlink($this->image_path.$imageinfo['filename']); 
    }
    if (file_exists($this->image_path.$this->orig_path.$imageinfo['filename'])) {
      @unlink($this->image_path.$this->orig_path.$imageinfo['filename']); 
    }
    if (file_exists($this->image_path.$this->thumb_path.$imageinfo['filename'])) { 
      @unlink($this->image_path.$this->thumb_path.$imageinfo['filename']); 
    }

    // delete the new size specific files
    if ($handle = @opendir($this->image_path)) {
      while (FALSE !== ($file = readdir($handle))) {
        if (strpos($file, $imageinfo['filename']) !== FALSE) {
          @unlink($this->image_path.$file);
        }
      }
      @closedir($handle);
    }
    
    $response['outcome'] = 'success';
    $response['message'] = 'Image successfully deleted.';
    $response['folder'] = $imageinfo['folder'];
    
    $this->_tinymce_serialize($response);
  }
  
	function delete_folder($arg)
	{
		$folder = isset($arg['folder']) ? (int) $this->input->xss_clean($arg['folder']) : 0;

		if ($folder > 0 ) {
			// move images from folder to root folder
			$this->db->query('UPDATE images SET folder = \'\' WHERE folder = ?', array($folder));
			$images_affected = $this->db->affected_rows();

			// remove folder
			$this->db->query('DELETE FROM image_folders WHERE id = ?', array($folder));
      
			// get new list of folders
			$sql = 'SELECT * 
							FROM image_folders
							WHERE user_id = ?            
							ORDER by caption ASC';	    $query = $this->db->query($sql, array($this->user_id));
	    $data['folders'][0] = array('id'=>0,'caption'=>'General');
	    foreach($query->result_array() AS $folderinfo) {
	      $data['folders'][] = $folderinfo;     
	    }
    
	    die($this->load->view($this->_view_base.'image_folder_list', $data, true).'<div style="display:none" id="message">'.(($images_affected>0?$images_affected.' image'.($images_affected==1?'':'s').' moved to General folder.':'').'</div>'));
    }
    else {
      $response['outcome'] = 'error';
      $response['message'] = 'You can\'t delete this folder.';
    }
    
    $this->_tinymce_serialize($response);
  }
  
  
  function add_folder($arg)
  {
    $caption = isset($arg['caption']) ? $this->input->xss_clean($arg['caption']) : '';
    
    if ($caption == '') {
      $response['outcome'] = 'error';
      $response['message'] = 'Please specify a valid folder name.';
    } 
    else if (strlen($caption) == 1) {
      $response['outcome'] = 'error';
      $response['message'] = 'The folder name must be at least 2 characters in length.';
    } 
    else if (strlen($caption) > 24) {
      $response['outcome'] = 'error';
      $response['message'] = "The folder name must be less than 24 characters.\n(The supplied folder name is "+captionID.length+" characters).";
    }
    if (isset($response)) {
      $this->_tinymce_serialize($response);
    }
    
    $sql = 'INSERT INTO image_folders (caption) 
            VALUES (?)';
    $query = $this->db->query($sql, array($caption));
    $lastid = $this->db->insert_id();

		// get new list of folders
		$sql = 'SELECT * 
						FROM image_folders
						WHERE user_id = ?            
						ORDER by caption ASC';    $query = $this->db->query($sql, array($this->user_id));
    $data['folders'][0] = array('id'=>0,'caption'=>'General');
    foreach($query->result_array() AS $folderinfo) {
      $data['folders'][] = $folderinfo;     
    }
    
    die($this->load->view($this->_view_base.'image_folder_list', $data, true));
  }
  
  function edit_folder($arg)
  {
    header("Cache-Control: no-cache, must-revalidate");
    header("Cache-Control: no-store"); 
    
    $folder = isset($arg['folder']) ? (int) $this->input->xss_clean($arg['folder']) : '';
    
    if ($folder == '' OR $folder == '0') {
      echo '';
    }
    else {
      $sql = 'SELECT caption
              FROM image_folders
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
  
  function get_folder_select($args)
  {
    $data['folderid'] = isset($args['folder']) ? (int) $args['folder'] : 0;

    // get all folders
    $sql = 'SELECT * 
            FROM image_folders
            WHERE user_id = ?            
            ORDER by caption ASC';
    $query = $this->db->query($sql, array($this->user_id));
    
    $data['folders'] = array();
    foreach($query->result_array() AS $folderinfo) {
      $data['folders'][] = $folderinfo;     
    }
    
    die($this->load->view($this->_view_base.'image_folder_select', $data, true));
  }

	function get_alttext_textbox($args)
	{
		$data['alttext'] = isset($args['alttext']) ? $this->input->xss_clean($args['alttext']) : '';
		die($this->load->view($this->_view_base.'image_alttext_textbox', $data, true));
	}
  
  function save_image_size($args) 
  {
    $quality = 90;

    $width = (int) $args['width'];
    $height = (int) $args['height'];
    if ($width <= 0 OR $height <= 0) {
      $this->_tinymce_serialize(array('outcome'=>'error','message'=>'Incorrect dimensions supplied. (Cant have value of 0)'));
    }

    // get image info, to ensure image src exists
    $sql = 'SELECT * 
            FROM images 
            WHERE filename = ?
            LIMIT 1';
    $query = $this->db->query($sql, array($args['img']));
    if ($query->num_rows() == 0) {
      die('fail|image not found');
    }
    $imageinfo = $query->row_array();
    $img_name = $this->image_path.$this->orig_path.$imageinfo['filename'];
	  $ext = $this->_get_extension($imageinfo['filename']);
  	$size = GetImageSize($img_name);
    
    switch($ext) {
      case 'jpg' : $src_img = ImageCreateFromJPEG($img_name); break;
      case 'gif' : $src_img = ImageCreateFromGIF($img_name); break;
      case 'png' : $src_img = ImageCreateFromPNG($img_name); break;
      default : $this->_tinymce_serialize(array('outcome'=>'error','message'=>'Incorrect file type.'));
    }

		// DEMO PURPOSES
		$this->_tinymce_serialize(array('outcome'=>'error','message'=>'This is a demo, the image was not resized.'));
		// DEMO PURPOSES		

    $thumb = ImageCreateTrueColor($width, $height);
    ImageCopyResampled($thumb, $src_img, 0, 0, 0, 0, ($width), ($height), $size[0], $size[1]);
    
    // dont replace over original
    if ($args['replace'] == 0) {	 
      switch($ext) {
        case 'jpg' : ImageJPEG($thumb, $this->image_path.$width."_".$height."_".$imageinfo['filename'], $quality); break;
        case 'gif' : ImageGIF($thumb, $this->image_path.$width."_".$height."_".$imageinfo['filename'], $quality); break;
        case 'png' : ImagePNG($thumb, $this->image_path.$width."_".$height."_".$imageinfo['filename']); break;
      }
    }
    // replace original image
    else {  
      switch($ext) {
        case 'jpg' : ImageJPEG($thumb, $this->image_path.$imageinfo['filename'], $quality); break;
        case 'gif' : ImageGIF($thumb, $this->image_path.$imageinfo['filename'], $quality); break;
        case 'png' : ImagePNG($thumb, $this->image_path.$imageinfo['filename']); break;
      }
    }      
    
    ImageDestroy($src_img);
    ImageDestroy($thumb);

		$response['outcome'] = 'success';
		$response['message'] = 'Image size successfully saved.';
    $this->_tinymce_serialize($response);
  }
    
  
  function restore_image($arg)
  {
    $imageid = isset($arg['image']) ? (int) $this->input->xss_clean($arg['image']) : 0;

    $sql = 'SELECT * 
            FROM images 
            WHERE id = ?
            LIMIT 1';
    $query = $this->db->query($sql, array($imageid));
    $imageinfo = $query->row_array();

    @copy($this->image_path.$this->orig_path.$imageinfo['filename'], $this->image_path.$imageinfo['filename']);
  }
  
  function update_details($arg)
  {
    $sql = 'UPDATE images 
            SET caption = ?, alttext = ?, folder = ?
            WHERE id = ?
            LIMIT 1';
    $this->db->query($sql, array($arg['caption'], $arg['alttext'], $arg['folder'], $arg['imageid']));
  }
  
  // get image details from db
  function get_image_info($arg = array('image' => ''))
  {
    $image = isset($arg['image']) ? basename($this->input->xss_clean($arg['image'])) : '';

    // get info image
    $sql = 'SELECT * 
            FROM images 
            WHERE filename = ?
            LIMIT 1';
    $query = $this->db->query($sql, array($image));
    if ($query->num_rows() == 0) {
      $response['outcome'] = 'error';
      $response['message'] = 'Image not found in database.';
    }
    else {
      $response = $query->row_array();
			$this->_gen_thumb($this->image_path.$this->thumb_path.$image);
      $response['outcome'] = 'success';
    }
    
    $this->_tinymce_serialize($response);
  }
  
    
	function get_user_info($args) 
	{
		// get user info: total images uploaded, privelages, max upload sizes etc etc
    
		$sql = 'SELECT COUNT(id) AS tot_images
						FROM images 
						WHERE user_id = ?';
		$query = $this->db->query($sql, array($this->user_id));
		$data['user'] = $query->row_array();
    
		// num gif
		$sql = 'SELECT id
						FROM images
						WHERE filename LIKE \'%.gif\'';
		$query = $this->db->query($sql);
		$data['user']['tot_gif'] = $query->num_rows();
		// num jpg
		$sql = 'SELECT id
						FROM images
						WHERE filename LIKE \'%.jpg\'';
		$query = $this->db->query($sql);
		$data['user']['tot_jpg'] = $query->num_rows();
		// num png
		$sql = 'SELECT id
						FROM images
						WHERE filename LIKE \'%.png\'';
		$query = $this->db->query($sql);
		$data['user']['tot_png'] = $query->num_rows();

		header("Pragma: no-cache");  
		header("Cache-Control: no-store, no-cache, max-age=0, must-revalidate");  
		header('Content-Type: text/x-json');  
		
		$this->load->view($this->_view_base.'image_user_info', $data);
  }
  
  function change_view($arg = array('view' => ''))
  {
    $this->view = strtolower($arg['view']);
    $this->get_thumbs(array('folder' => ''));
  }
  
  function change_view_adv($args = array('view' => ''))
  {
    $this->view = $args['view'];
    $this->db_session->set_userdata('cimm_view', $args['view']);
    $this->get_file_folder_list(array('folder' => ''));
  }  
  
  function _gen_thumb($thumb_file='')
	{
		if(file_exists($thumb_file) == FALSE) {
			$filename = basename($thumb_file);
			$img_name = $this->image_path.$this->orig_path.$filename;
			$ext = $this->_get_extension($filename);
			$the_width = 95; // maximum x aperture  in pixels
			$the_height = 95; // maximum y aperture in pixels
			
			$size=GetImageSize($img_name);
			if($size['0'] < $size['1']) {
				$ratio = $the_width/$size['0'];
				$semi_x = $the_width;
				$semi_y = $size['1']*$ratio;
			}
			else {
				$ratio = $the_height/$size['1'];
				$semi_x = $size['0']*$ratio;
				$semi_y = $the_height;
			}
			$new_width    = $the_width;
			$new_height   = $the_height;
			if ($ext == "jpg") {
				$src_img = ImageCreateFromJPEG($img_name);
			}
			else if ($ext == "gif") {
				$src_img = ImageCreateFromGIF($img_name);
			}
			else if ($ext == "png"){
				$src_img = ImageCreateFromPNG($img_name);
			}
			$thumb = ImageCreateTrueColor($new_width, $new_height);

			ImageCopyResized($thumb, $src_img, -($semi_x/2) + ($the_width/2), -($semi_y/2) + ($the_height/2), 0, 0, ($semi_x), ($semi_y), $size['0'], $size['1']);
			
			if ($ext == "jpg") {
			  ImageJPEG($thumb, $this->image_path.$this->thumb_path.$filename,90);
			}
			else if ($ext == "gif") {
			  ImageGIF($thumb, $this->image_path.$this->thumb_path.$filename,90);
			}
			else if ($ext == "png") {
			  ImagePNG($thumb, $this->image_path.$this->thumb_path.$filename);
			}   
			ImageDestroy($src_img);
			ImageDestroy($thumb);
		}
		return true;
  }
  
    // check if image directories exist, if not then try to create them
  function _check_paths()
  {
    // image dir
    if (!file_exists($this->image_path)) {
      @mkdir($this->image_path, 0777) OR show_error('Unable to create image folder '.$this->image_path.'<br/><strong>Please adjust permissions</strong>');
    }
    // thumb dir
    if (!file_exists($this->_full_thumb_path = $this->image_path.$this->thumb_path)) {
      @mkdir($this->_full_thumb_path, 0777) OR show_error('Unable to create thumbnails folder '.$this->_full_thumb_path.'<br/><strong>Please adjust permissions</strong>');
    }
    // orig dir
    if (!file_exists($this->_full_orig_path = $this->image_path.$this->orig_path)) {
      @mkdir($this->_full_orig_path, 0777) OR show_error('Unable to create image folder '.$this->_full_orig_path.'<br/><strong>Please adjust permissions</strong>');
    }
  }
  
  // get extension of filename
	function _get_extension($filename) {
		$ext = array_reverse(explode('.', $filename));
		return strtolower($ext[0]);
	}
  
  // serialize a simple associative array to JSON string 
	function _tinymce_serialize($response = array()) {
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
