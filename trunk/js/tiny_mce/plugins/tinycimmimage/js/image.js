/*
 *
 * image.js
 * Copyright (c) 2009 Richard Willis & Liam Gooding
 * MIT license  : http://www.opensource.org/licenses/mit-license.php
 * Project      : http://tinycimm.googlecode.com/
 * Contact      : willis.rh@gmail.com
 *
 */

var TinyCIMMImage = {

	init : function(ed) {
		var f = document.forms[0], nl = f.elements, ed = tinyMCEPopup.editor, dom = ed.dom, n = ed.selection.getNode();
		tinyMCEPopup.resizeToInnerSize();
		this.showBrowser(0);
	},

	getImage : function(imageid, callback) {
		tinymce.util.XHR.send({
			url : TinyCIMMImage.baseURL(tinyMCEPopup.editor.settings.tinycimm_controller+'image/get_image/'+imageid),
			error : function(response) {
				tinyMCEPopup.editor.windowManager.alert('There was an error retrieving the image info.');
				return false;
			},
			success : function(response) {
				var obj = tinymce.util.JSON.parse(response);
				if (obj.outcome == 'error') {
					tinyMCEPopup.editor.windowManager.alert(obj.message);
				} else {
					(callback) && callback(obj);
				}
			}
		});
	},

	insert : function(imageid) {
		var t = this;
		this.getImage(imageid, function(image){
			t.insertAndClose(image);
		});
	},

	insertAndClose : function(image) {
		var ed = tinyMCEPopup.editor, f = document.forms[0], nl = f.elements, v, args = {}, el;

		tinyMCEPopup.restoreSelection();

		// Fixes crash in Safari
		if (tinymce.isWebKit) {
			ed.getWin().focus();
		}

		args = {
			src : TinyCIMMImage.baseURL(tinyMCEPopup.editor.settings.tinycimm_assets_path+image.filename),
			width : '',
			height : '',
			alt : image.description,
			title : image.description
		};

		el = ed.selection.getNode();

		if (el && el.nodeName == 'IMG') {
			ed.dom.setAttribs(el, args);
		} else {
			ed.execCommand('mceInsertContent', false, '<img id="__mce_tmp" />', {skip_undo : 1});
			ed.dom.setAttribs('__mce_tmp', args);
			ed.dom.setAttrib('__mce_tmp', 'id', '');
			ed.undoManager.add();
		}

		tinyMCEPopup.close();
	},

	showUploader : function() {
		mcTabs.displayTab('upload_tab','upload_panel');
		tinyMCEPopup.dom.get('resize_tab').style.display = 'none';
		TinyCIMMImage.loaduploader();
	},
	showManager : function() {
		TinyCIMMImage.loadManager();
	},
	showBrowser : function(folder) {
		mcTabs.displayTab('browser_tab','browser_panel');
		tinyMCEPopup.dom.get('resize_tab').style.display = 'none';
		TinyCIMMImage.fileBrowser(folder);
	},

	// clear all image data fields
	resetTinyCIMMImage : function() {
		tinyMCEPopup.dom.get('src').value = tinyMCEPopup.dom.get('alt').value = tinyMCEPopup.dom.get('title').value = tinyMCEPopup.dom.get('width').value = tinyMCEPopup.dom.get('height').value = tinyMCEPopup.dom.get('style').value = '';
	},

	 // load list of folders and images via json request
	fileBrowser : function(folder, offset) {
		folder = folder || 0;
		offset = offset || 0;
		if (tinyMCEPopup.dom.get('img-'+folder) == null) {
			tinyMCEPopup.dom.setHTML('filebrowser', '<span id="loading">loading</span>');
		}

		tinymce.util.XHR.send({
			url : TinyCIMMImage.baseURL(tinyMCEPopup.editor.settings.tinycimm_controller+'image/get_browser/'+folder+'/'+offset),
			error : function(reponse) {
				tinyMCEPopup.editor.windowManager.alert('There was an error retrieving the images.');
			},
			success : function(response) {
				tinyMCEPopup.dom.setHTML('filebrowser', response);
				// bind click event to pagination links
				var pagination_anchors = tinyMCEPopup.dom.select('div.pagination a');
				for(var anchor in pagination_anchors) {
					pagination_anchors[anchor].onclick = function(e){
						e.preventDefault();
						TinyCIMMImage.fileBrowser(folder, this.href.replace(/.*\/([0-9]+)$/, '$1'));
					};
				}
				// bind hover event to thumbnail
				var thumb_images = tinyMCEPopup.dom.select('.thumb_wrapper');
				for(var image in thumb_images) {
					thumb_images[image].onmouseover = function(e){
						tinyMCE.activeEditor.dom.addClass(this, 'show')
					};
					thumb_images[image].onmouseout = function(e){
						tinyMCE.activeEditor.dom.removeClass(this, 'show')
					};
				}
			}
		});
	},

	changeView : function(view) {
		// show loading image
		tinyMCEPopup.dom.setHTML('filebrowser', '<span id="loading">loading</span>');
		tinymce.util.XHR.send({
			url : TinyCIMMImage.baseURL(tinyMCEPopup.editor.settings.tinycimm_controller+'image/change_view/'+view),
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
	
		// display panel
		mcTabs.displayTab('manager_tab','manager_panel');
		// send a request for image info
		tinymce.util.XHR.send({
			url : TinyCIMMImage.baseURL(tinyMCEPopup.editor.settings.tinycimm_controller+'image/get_image/'+imgid),
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
					tinyMCEPopup.dom.get('manage_thumb_img').style.background = 'url('+TinyCIMMImage.baseURL(tinyMCEPopup.editor.settings.tinycimm_controller+'image/get/'+obj.id+'/95/95')+') no-repeat center center';
					TinyCIMMImage.loadSelectManager(obj.folder);
					TinyCIMMImage.loadAltTextManager(obj.alttext);
				}
			}
		});
		
		return;
		//tinyMCEPopup.resizeToInnerSize();
	},

	// file upload callback function
	imageUploaded : function(folder) {
		tinyMCEPopup.editor.windowManager.alert('Image successfully uploaded!');
		TinyCIMMImage.showBrowser(folder);
	},
	
	
	// get select list of folders in html select & option format (var folder would give option selected attr)
	loadSelectManager : function(folder) {
		folder = folder==undefined?'':folder;
		tinymce.util.XHR.send({
			url : TinyCIMMImage.baseURL(tinyMCEPopup.editor.settings.tinycimm_controller+'image/get_folders_select/'+folder),
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
			url : TinyCIMMImage.baseURL(tinyMCEPopup.editor.settings.tinycimm_controller+'image/get_alttext_textbox/alttext/'+alttext),
			error : function(response) {
				tinyMCEPopup.editor.windowManager.alert('There was an error retrieving the image description.');
			},
			success : function(response) {
					tinyMCEPopup.dom.setHTML('alttext_container', response);
			}
		});
	},
	
	// prepare the resizer panel
	loadresizer : function(imagesrc) {
		// ensure image is cached before loading the resizer
		this.loadImage(TinyCIMMImage.baseURL(tinyMCEPopup.editor.settings.tinycimm_assets_path+imagesrc));
	},

	// pre-cache an image
	loadImage : function(img) { 
		var preImage = new Image();
		preImage.src = img;
		//console.debug(preImage);
		setTimeout(function(){
			TinyCIMMImage.checkImgLoad(preImage);
		},10);	// ie
	},

	// show loading text if image not already cached
	checkImgLoad : function(preImage) {
		if (!preImage.complete) {
			mcTabs.displayTab('resize_tab','resize_panel');
			tinyMCEPopup.dom.setHTML('image-info-dimensions', '<img style="float:left;margin-right:4px" src=""/> caching image');
		}
		this.checkLoad(preImage);
	},	

	checkLoad : function(preImage) {
		if (preImage.complete) { 
			//console.debug(preImage.complete);
			this.showResizeImage(preImage);
			return;
		}
 		setTimeout(function(){
			TinyCIMMImage.checkLoad(preImage)
		}, 10);
	},
	
	// show resizer image
	showResizeImage : function(preImage) {
		this.getImage(preImage.src.replace(/.*\/([0-9]+)_?.*$/, '$1'), function(image){
			// load image 
			tinyMCEPopup.dom.get('slider_img').src = preImage.src;
			tinyMCEPopup.dom.get('slider_img').width = max_w = image.width; 
			tinyMCEPopup.dom.get('slider_img').height = max_h = image.height;
			// display panel
			mcTabs.displayTab('resize_tab','resize_panel');
			tinyMCEPopup.dom.get('resize_tab').style.display = 'block';
			// image dimensions overlay layer
			tinyMCEPopup.dom.setHTML('image-info-dimensions', '<span id="slider_width_val"></span> x <span id="slider_height_val"></span>');
			
			new ScrollSlider(tinyMCEPopup.dom.get('image-slider'), {
				min : 0,
				max : max_w,
				value : max_w,
				size : 380,
				scroll : function(new_w) {
					tinyMCEPopup.dom.get('slider_width_val').innerHTML = (tinyMCEPopup.dom.get('slider_img').width=new_w);
					tinyMCEPopup.dom.get('slider_height_val').innerHTML = (tinyMCEPopup.dom.get('slider_img').height=Math.round((parseInt(new_w)/parseInt(max_w))*max_h))+'px';
				}
			});

		});
	},
	
	// load list of folders via request
	loadselect : function(folder) {
		folder = folder==undefined?'':folder;
		tinymce.util.XHR.send({
			url : TinyCIMMImage.baseURL(tinyMCEPopup.editor.settings.tinycimm_controller+'image/get_folders_select/'+folder),
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
	
	
	// populates the image src and description form fields of the ImageDialog window
	insertPreviewImage : function(imgsrc, alttext) {
		// use tinmce setting for this!
		var win = tinyMCEPopup.getWindowArg("window");
		var URL = TinyCIMMImage.baseURL(tinyMCEPopup.editor.settings.tinycimm_assets_path+imgsrc);

	
		if (win != undefined) {
			win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = URL;
			if (typeof(win.ImageDialog) != "undefined") {
				if (win.ImageDialog.getImageData) {
					win.ImageDialog.getImageData();
				}
				if (win.ImageDialog.showPreviewImage) {
					win.ImageDialog.showPreviewImage(URL);
				}
				win.document.getElementById('alt').value = alttext;
				//tinyMCEPopup.dom.get('title').value = '';
			}
 			tinyMCEPopup.close();
		} else {
			this.insert(imgsrc.replace(/.*\/([0-9]+)_?.*$/, '$1'));
		}
		return;
	},
	
	// @TODO
	saveImgDetails : function() {
		tinyMCEPopup.editor.windowManager.alert('Image details changed.');
	},
	
	saveImgSize : function() {
		// show loading animation
		tinyMCEPopup.dom.get('saveimg').src = tinyMCEPopup.dom.get('saveimg').src.replace('save.gif', 'ajax-loader.gif');
		
		// prepare request url
		var imgsrc_arr = tinyMCEPopup.editor.documentBaseURI.toRelative(tinyMCEPopup.dom.get('slider_img').src).split('/');
		var requesturl = TinyCIMMImage.baseURL(tinyMCEPopup.editor.settings.tinycimm_controller+'image/save_image_size/'+imgsrc_arr[imgsrc_arr.length-1].replace(/^([0-9]+)_?.*$/, '$1')+'/'+tinyMCEPopup.dom.get('slider_img').width+'/'+tinyMCEPopup.dom.get('slider_img').height+'/90');
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
				} else if (obj.outcome == 'success') {
					tinyMCEPopup.editor.windowManager.confirm('Image size successfully saved.\n\nClick OK to insert image or cancel to return.', function(s) {
						if (!s) {
							TinyCIMMImage.showBrowser();
							return false;
						}
						TinyCIMMImage.insertPreviewImage(obj.filename, obj.description);
					});
				}
			}
		});
	},
	
	// add image folder
	addFolder : function() {
		var captionID = encodeURIComponent(tinyMCEPopup.dom.get('add_folder_caption').value.replace(/^\s+|\s+$/g, ''));
		var requesturl = TinyCIMMImage.baseURL(tinyMCEPopup.editor.settings.tinycimm_controller+'image/add_folder/'+captionID);
		tinymce.util.XHR.send({
			url : requesturl,
			error : function(response) {
				tinyMCEPopup.editor.windowManager.alert('There was an error processing the request.');
			},
			success : function(response) {
				var obj = tinymce.util.JSON.parse(response);
				if (obj && obj.outcome == 'error') {
						tinyMCEPopup.editor.windowManager.alert('Error: '+obj.message);
				} else {
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
 			var requesturl = TinyCIMMImage.baseURL(tinyMCEPopup.editor.settings.tinycimm_controller+'image/delete_folder/'+folderID);
			tinymce.util.XHR.send({
				url : requesturl,
				error : function(response) {
		 			tinyMCEPopup.editor.windowManager.alert('There was an error processing the request.');
				},
				success : function(response) {
		 			var obj = tinymce.util.JSON.parse(response);
					if (obj && obj.outcome == 'error') {
						tinyMCEPopup.editor.windowManager.alert('Error: '+obj.message);
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
 		var requesturl = TinyCIMMImage.baseURL(tinyMCEPopup.editor.settings.tinycimm_controller+'image/get_folders_html');
		tinymce.util.XHR.send({
			url : requesturl,
			error : function(response) {
		 		tinyMCEPopup.editor.windowManager.alert('There was an error processing the request.');
			},
			success : function(response) {
				(callback) && callback(response.toString());	
			}
		});
	},
	
	// delete image 
	deleteImage : function(imageID) {
		tinyMCEPopup.editor.windowManager.confirm('Are you sure you want to delete this image?', function(s) {
			if (!s) {return false;}
			tinymce.util.XHR.send({
				url : TinyCIMMImage.baseURL(tinyMCEPopup.editor.settings.tinycimm_controller+'image/delete_image/'+imageID),
				error : function(response) {
					tinyMCEPopup.editor.windowManager.alert('There was an error processing the request.');
				},
				success : function(response) {
					var obj = tinymce.util.JSON.parse(response);
					if (obj.outcome == 'error') {
						tinyMCEPopup.editor.windowManager.alert('Error: '+obj.message);
					} else {
						tinyMCEPopup.editor.windowManager.alert(obj.message);
						folder = obj.folder
					}
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
		catch(e) { return; }
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
	
};

tinyMCEPopup.onInit.add(TinyCIMMImage.init, TinyCIMMImage);
