//--------------------------------------------------
// Used by TinyCIMM Image Manager 
//--------------------------------------------------
var max_w, max_h, new_w, new_h, preImage, ajax_img = 'img/ajax-loader.gif';
//---------------------------------------------------


var TinyCIMMImage = {

	init : function(ed) {
		var f = document.forms[0], nl = f.elements, ed = tinyMCEPopup.editor, dom = ed.dom, n = ed.selection.getNode();

		tinyMCEPopup.resizeToInnerSize();

	},

	ciController : 'assetmanager',
	assetPath : '/assets/',
	
	/* these following methods handle the different tab panels */
	showGeneral : function() {
		mcTabs.displayTab('general_tab','general_panel');
	},
	showAdvanced : function() {
		mcTabs.displayTab('advanced_tab','advanced_panel');
	},
	showUploader : function() {
		mcTabs.displayTab('upload_tab','upload_panel');
		TinyCIMMImage.loaduploader();
	},
	showAppearance : function() {
		mcTabs.displayTab('appearance_tab','appearance_panel');
	},
	showManager : function() {
		TinyCIMMImage.loadManager();
	},
	showBrowser : function(folder) {
		mcTabs.displayTab('browser_tab','browser_panel');
		TinyCIMMImage.fileBrowser(folder)
	},

	// clear all image data fields
	resetTinyCIMMImage : function() {
		tinyMCEPopup.dom.get('src').value = tinyMCEPopup.dom.get('alt').value = tinyMCEPopup.dom.get('title').value = tinyMCEPopup.dom.get('width').value = tinyMCEPopup.dom.get('height').value = tinyMCEPopup.dom.get('style').value = '';
	},

	 // load list of folders and images via json request
	fileBrowser : function(folder) {
		folder = folder || 0;
		if (tinyMCEPopup.dom.get('img-'+folder) == null) {
			tinyMCEPopup.dom.setHTML('filebrowser', '<span id="loading">loading</span>');
		}
		else {
			tinyMCEPopup.dom.get('img-'+folder).src = ajax_img;
		}

		tinymce.util.XHR.send({
			url : TinyCIMMImage.baseURL('assetmanager/image/get_browser/'+folder),
			error : function(text) {
				tinyMCEPopup.editor.windowManager.alert('There was an error retrieving the images.');
			},
			success : function(text) {
				tinyMCEPopup.dom.setHTML('filebrowser', text);
			}
		});
	},

	changeView : function(view) {
		// show loading image
		tinyMCEPopup.dom.setHTML('filebrowser', '<span id="loading">loading</span>');
		tinymce.util.XHR.send({
			url : TinyCIMMImage.baseURL('assetmanager/image/change_view/'+view),
			error : function(text) {
				tinyMCEPopup.editor.windowManager.alert('There was an error processing the request.');
			},
			success : function(text) {
				tinyMCEPopup.dom.setHTML('filebrowser', text);
			}
		});
	},
	
	// prepare uploading form
	loaduploader : function() {
		// load the uploader form
		if (tinyMCEPopup.dom.get('upload_target_ajax').src == '') {
			tinyMCEPopup.dom.get('upload_target_ajax').src = 'uploadform.htm';
		} 
		this.loadselect();
		/*// show loading msg
		tinyMCEPopup.dom.setHTML('fileuploader_info', '<span id="loading">loading</span>');
		// send a request for user info
		tinymce.util.XHR.send({
			url : TinyCIMMImage.baseURL('assetmanager/image/get_user_info/'),
			error : function(text) {
				tinyMCEPopup.editor.windowManager.alert('There was an error retrieving your user info.');
			},
			success : function(text) {
				 tinyMCEPopup.dom.setHTML('fileuploader_info', text);
			}
		});
		*/
		tinyMCEPopup.resizeToInnerSize();
	},
	
	// prepare image manager panel
	loadManager : function() {
		if (tinyMCEPopup.dom.get('image_alttext')) {
			tinyMCEPopup.dom.setHTML('alttext_container', '<textarea id="image_alttext" style="color:#aaa;width: 160px; height: 36px;">loading</textarea>');
		}
		if (tinyMCEPopup.dom.get('src').value == '') {
			tinyMCEPopup.editor.windowManager.alert('You need to select an image first.', 
			function(s) {
				// if not already viewing the browser
				if (tinyMCEPopup.dom.get('browser_tab').className != "current") {
					TinyCIMMImage.showBrowser();
				}
			});
			return;
		}
		// show loading img
		tinyMCEPopup.dom.setHTML('folder_select_list', '<select><option>loading..</option></select>');
		// prep thumb path
		var imgsrc_arr = tinyMCEPopup.editor.documentBaseURI.toRelative(tinyMCEPopup.dom.get('src').value).split('/');
		var imgsrc = imgsrc_arr[imgsrc_arr.length-1];
		var imgid = imgsrc.replace(/(.*\/)?([0-9]+)\.([a-zA-Z]+)/, "$2");
		// set thumb	
		tinyMCEPopup.dom.get('manage_thumb_img').style.background = 'url(img/progress.gif) no-repeat center center';
		//tinyMCEPopup.dom.get('manage_thumb_img').width = 95;
		//tinyMCEPopup.dom.get('manage_thumb_img').height = 95;
	
		// display panel
		mcTabs.displayTab('manager_tab','manager_panel');
		// send a request for image info
		tinymce.util.XHR.send({
			url : TinyCIMMImage.baseURL('assetmanager/image/get_image/'+imgid),
			error : function(response) {
				tinyMCEPopup.editor.windowManager.alert('There was an error retrieving the image info.');
			},
			success : function(response) {
				var obj = tinymce.util.JSON.parse(response);
				if (obj.outcome == 'error') {
					tinyMCEPopup.editor.windowManager.alert(obj.message);
				}
				else {
					tinyMCEPopup.dom.get('del_image').rel = obj.id;
					tinyMCEPopup.dom.get('manage_thumb_img').style.background = 'url('+TinyCIMMImage.baseURL('/assetmanager/image/get/'+obj.id+'/95/95')+') no-repeat center center';
					TinyCIMMImage.loadSelectManager(obj.folder);
					TinyCIMMImage.loadAltTextManager(obj.alttext);
				}
			}
		});
		
		return;
		//tinyMCEPopup.resizeToInnerSize();
	},

	// updates image form fields after successfull upload
	updateImage : function(imgsrc, alttext) {
		var imgsrc = tinyMCEPopup.dom.get('src').value = TinyCIMMImage.baseURL(this.assetPath+imgsrc);
		tinyMCEPopup.dom.get('alt').value = alttext;
		this.showPreviewImage(imgsrc);
		this.loadManager();
		tinyMCEPopup.editor.windowManager.alert('Image uploaded successfully, please update the image description.');
	},
	
	
	// get select list of folders in html select & option format (var folder would give option selected attr)
	loadSelectManager : function(folder) {
		folder = folder==undefined?'':folder;
		tinymce.util.XHR.send({
			url : TinyCIMMImage.baseURL('assetmanager/image/get_folders_select/'+folder),
			error : function(response) {
				tinyMCEPopup.editor.windowManager.alert('There was an error retrieving the select list.');
			},
			success : function(response) {
					tinyMCEPopup.dom.setHTML('folder_select_list', response);
			}
		});
	},

 	// get select list of folders in html select & option format (var folder would give option selected attr)
	loadAltTextManager : function(alttext) {
		tinymce.util.XHR.send({
			url : TinyCIMMImage.baseURL('assetmanager/image/get_alttext_textbox/alttext/'+alttext),
			error : function(response) {
				tinyMCEPopup.editor.windowManager.alert('There was an error retrieving the image description.');
			},
			success : function(response) {
					tinyMCEPopup.dom.setHTML('alttext_container', response);
			}
		});
	},
	
	// prepare image attributes
	loadresizer : function() {
		if (tinyMCEPopup.dom.get('src').value == '') {
			tinyMCEPopup.editor.windowManager.alert('You need to select an image first.', 
			function(s) {
					// if not already viewing the browser
					if (tinyMCEPopup.dom.get('browser_tab').className != "current") {
						TinyCIMMImage.showBrowser();
					}
			});
			return;
		}
		// ensure image is cached before loading the resizer
		this.loadImage(TinyCIMMImage.baseURL(tinyMCEPopup.dom.get('src').value));
	},

	// pre-cache an image
	loadImage : function(img) { 
		preImage = new Image();
		preImage.src = img;
		//console.debug(preImage);
		setTimeout("TinyCIMMImage.checkImgLoad()",10);	// ie
	},

	// show loading text if image not already cached
	checkImgLoad : function() {
		if (!preImage.complete) {
			mcTabs.displayTab('resize_tab','resize_panel');
			tinyMCEPopup.dom.setHTML('image-info-dimensions', '<img style="float:left;margin-right:4px" src="'+ajax_img+'"/> caching image');
		}
		this.checkLoad();
	},	

	checkLoad : function() {
		if (preImage.complete) { 
			//console.debug(preImage.complete);
			preImage = null;
			this.showResizeImage();
			return;
		}
 		setTimeout("TinyCIMMImage.checkLoad()", 10);
	},
	
	// show resizer image
	showResizeImage : function() {
		// load image 
		tinyMCEPopup.dom.get('slider_img').src = TinyCIMMImage.baseURL(tinyMCEPopup.dom.get('src').value);
		tinyMCEPopup.dom.get('slider_img').width = max_w = tinyMCEPopup.dom.get('width').value;
		tinyMCEPopup.dom.get('slider_img').height = max_h = tinyMCEPopup.dom.get('height').value;
		// display panel
		mcTabs.displayTab('resize_tab','resize_panel');
		// image dimensions overlay layer
		tinyMCEPopup.dom.setHTML('image-info-dimensions', '<span id="slider_width_val"></span> x <span id="slider_height_val"></span>');
		// image scroller
		new ScrollSlider(tinyMCEPopup.dom.get('image-slider'), {
			min : 0,
			max : max_w,
			value : max_w,
			size : 380,
			scroll : function(new_w) {
				// onscroll => update image dimensions
				tinyMCEPopup.dom.get('slider_width_val').innerHTML = (tinyMCEPopup.dom.get('slider_img').width=new_w);
				tinyMCEPopup.dom.get('slider_height_val').innerHTML = (tinyMCEPopup.dom.get('slider_img').height=Math.round((parseInt(new_w)/parseInt(max_w))*max_h))+'px';
				}
		});
	},
	
	// load list of folders via request
	loadselect : function(folder) {
		folder = folder==undefined?'':folder;
		tinymce.util.XHR.send({
			url : TinyCIMMImage.baseURL('assetmanager/image/get_folders_select/'+folder),
			error : function(text) {
				tinyMCEPopup.editor.windowManager.alert('There was an error retrieving the select list.');
			},
			success : function(text) {
				try {
					if (typeof window.upload_target_ajax == 'object') {
						// this ensures iframe src file has loaded correctly
						setTimeout(function(){
							var d = window.upload_target_ajax.document.getElementById('folder_select_list');
							if (d) {
								d.innerHTML = text;
							} else {tinyMCEPopup.dom.setHTML('folder_select_list', text);}
						}, 500);
					}
				}
				catch(e) {alert(e);}
			}
		});
	},
	
	
	// populates the image src and description form fields, 
	// and shows the preview image in the dialog window
	insertPreviewImage : function(imgsrc, alttext) {
		//imgsrc = imgsrc.replace(/\@/, "");
		imgsrc = tinyMCEPopup.dom.get('src').value = TinyCIMMImage.baseURL('assets/'+imgsrc);
		tinyMCEPopup.dom.get('alt').value = alttext;
		tinyMCEPopup.dom.get('title').value = '';
		this.showPreviewImage(imgsrc);
		this.showGeneral();
	},
	
	saveImgDetails : function() {
		tinyMCEPopup.editor.windowManager.alert('Image details changed.');
	},
	
	saveImgSize : function() {
		// show loading animation
		tinyMCEPopup.dom.get('saveimg').src = tinyMCEPopup.dom.get('saveimg').src.replace('save.gif', 'ajax-loader.gif');
		
		// prepare request url
		var replace = tinyMCEPopup.dom.get('replace').checked == true ? '1' : '0';
		var imgsrc_arr = tinyMCEPopup.editor.documentBaseURI.toRelative(tinyMCEPopup.dom.get('slider_img').src).split('/');
		var requesturl = TinyCIMMImage.baseURL('assetmanager/image/save_image_size/'+imgsrc_arr[imgsrc_arr.length-1]+'/'+tinyMCEPopup.dom.get('slider_img').width+'/'+tinyMCEPopup.dom.get('slider_img').height+'/90/'+replace);
		// send request
		tinymce.util.XHR.send({
			url : requesturl,
			error : function(response) {
				tinyMCEPopup.editor.windowManager.alert('There was an error processing the request: '+response+"\nPlease try again.");
				tinyMCEPopup.dom.get('saveimg').src = tinyMCEPopup.dom.get('saveimg').src.replace('ajax-loader.gif', 'save.gif');
			},
			success : function(response) {
				tinyMCEPopup.dom.get('saveimg').src = tinyMCEPopup.dom.get('saveimg').src.replace('ajax-loader.gif', 'save.gif');
				var obj = tinymce.util.JSON.parse(response);
				if (obj.outcome == 'error') {
					tinyMCEPopup.editor.windowManager.alert(obj.message); 
				}
				else if (obj.outcome == 'success') {
					tinyMCEPopup.editor.windowManager.alert('Image size successfully saved.', 
					function(s) {
						var imgsrc = TinyCIMMImage.baseURL(tinyMCEPopup.dom.get('slider_img').src);
						tinyMCEPopup.dom.get('src').value = imgsrc;
						tinyMCEPopup.dom.get('width').value = tinyMCEPopup.dom.get('slider_img').width;
						tinyMCEPopup.dom.get('height').value = tinyMCEPopup.dom.get('slider_img').height;
						TinyCIMMImage.updateStyle();
						TinyCIMMImage.showPreviewImage(imgsrc, 1);
						TinyCIMMImage.showGeneral();
					});
				}
			}
		});
	},
	
	// add image folder
	addFolder : function() {
		var captionID = encodeURIComponent(tinyMCEPopup.dom.get('add_folder_caption').value.replace(/^\s+|\s+$/g, ''));
		var requesturl = TinyCIMMImage.baseURL('assetmanager/image/add_folder')+'/'+captionID;
		tinymce.util.XHR.send({
			url : requesturl,
			error : function(response) {
				tinyMCEPopup.editor.windowManager.alert('There was an error processing the request.');
			},
			success : function(response) {
				var obj = tinymce.util.JSON.parse(response);
				if (obj && obj.outcome == 'error') {
						tinyMCEPopup.editor.windowManager.alert('Error: '+obj.message);
				}
				else {
					//success
					tinyMCEPopup.dom.setHTML('folderlist', response)
					tinyMCEPopup.dom.get('addfolder').style.display = 'none';
					tinyMCEPopup.dom.get('add_folder_caption').value = '';
				}
			}
		});
	},
	
	// delete image folder
	deleteFolder : function(folderID) {
		tinyMCEPopup.editor.windowManager.confirm('Are you sure you want to delete this folder?', function(s) {
			if (!s) {
				return false;
			}
 			var requesturl = TinyCIMMImage.baseURL('assetmanager/image/delete_folder')+'/'+folderID;
			tinymce.util.XHR.send({
				url : requesturl,
				error : function(response) {
		 			tinyMCEPopup.editor.windowManager.alert('There was an error processing the request.');
				},
				success : function(response) {
		 			var obj = tinymce.util.JSON.parse(response);
					if (obj && obj.outcome == 'error') {
						tinyMCEPopup.editor.windowManager.alert('Error: '+obj['message']);
		 			} else {
						TinyCIMMImage.getFoldersHTML(function(folderHTML){
							tinyMCEPopup.dom.setHTML('folderlist', folderHTML)
						});
						if (obj.images_affected > 0) {
							tinyMCEPopup.editor.windowManager.alert(obj.images_affected+" images were moved to the root directory.");
						}
		 			}
				}
			});
		});
	},			

	// get folders as html string
	getFoldersHTML : function(callback) {
 		var requesturl = TinyCIMMImage.baseURL('assetmanager/image/get_folders_html');
		tinymce.util.XHR.send({
			url : requesturl,
			error : function(response) {
		 		tinyMCEPopup.editor.windowManager.alert('There was an error processing the request.');
			},
			success : function(response) {
				callback(response.toString());	
			}
		});
	},
	
	// delete image 
	deleteImage : function(imageID) {
		tinyMCEPopup.editor.windowManager.confirm('Are you sure you want to delete this image?', function(s) {
			if (!s) {return false;}

			// loading img
			var img_delete_src = tinyMCEPopup.dom.get('img_delete').src, folder = '';
			tinyMCEPopup.dom.get('img_delete').src = ajax_img;
			// send request
			var requesturl = TinyCIMMImage.baseURL('assetmanager/image/delete_image/')+'/'+imageID;
			tinymce.util.XHR.send({
				url : requesturl,
				error : function(response) {
					tinyMCEPopup.dom.get('img_delete').src = img_delete_src;
					tinyMCEPopup.editor.windowManager.alert('There was an error processing the request.');
				},
				success : function(response) {
					tinyMCEPopup.dom.get('img_delete').src = img_delete_src;
					var obj = tinymce.util.JSON.parse(response);
					if (obj.outcome == 'error') {
						tinyMCEPopup.editor.windowManager.alert('Error: '+obj.message);
					}
					else {
						tinyMCEPopup.editor.windowManager.alert(obj.message);
						folder = obj.folder
					}
 					// reset inputs, loadbrowser
 					TinyCIMMImage.resetTinyCIMMImage();
				 	TinyCIMMImage.showPreviewImage();
				 	TinyCIMMImage.showBrowser(folder);
				}
			});
		});
	},
	
	resizeInputs : function() {
		var wHeight=0, wWidth=0, owHeight=0, owWidth=0;
		try {
			if (!tinymce.isIE) {
				 wHeight = self.innerHeight - 65;
				 wWidth = self.innerWidth;
			} else {
				 wHeight = document.body.clientHeight - 54;
				 wWidth = document.body.clientWidth;
			}
			wHeight -= 130;
			
			tinyMCEPopup.dom.get('filebrowser').style.height = Math.abs(wHeight+94) + 'px';
			//if (tinyMCEPopup.dom.get('filelist') != null) {
			// tinyMCEPopup.dom.get('filelist').style.height = Math.abs(wHeight+78) + 'px';
			//}
			if (tinyMCEPopup.dom.get('resizer') != null) {
			 tinyMCEPopup.dom.get('resizer').style.height = Math.abs(wHeight+90) + 'px';
			}
			if (tinyMCEPopup.dom.get('image-info') != null) {
			 tinyMCEPopup.dom.get('image-info').style.height = Math.abs(wHeight+44) + 'px';
			}
			tinyMCEPopup.dom.get('image-slider').size = Math.abs(wWidth-190);
			//ScrollSlider.change();
			
		}
		catch(e) {
			// do nothing
			 alert(e);
			 return;
		}
	},

	baseURL : function(url) {
		return tinyMCEPopup.editor.documentBaseURI.toAbsolute(url);
	},
	
	// reload dialog window to initial state
	reload : function() {
		tinyMCEPopup.dom.get('info_tab_link').className = 'rightclick';
		setTimeout(function() {
			location.reload();
			tinyMCEPopup.resizeToInnerSize();
		}, 300);
	}
	
	
	//--------------------------------------
	// end Image Manager
	// ------------------------------------------
};

tinyMCEPopup.onInit.add(TinyCIMMImage.init, TinyCIMMImage);
