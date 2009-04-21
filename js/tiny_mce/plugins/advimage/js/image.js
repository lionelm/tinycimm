//--------------------------------------------------
// Used by TinyCIMM Image Manager 
//--------------------------------------------------
var max_w, max_h, new_w, new_h, preImage, ajax_img = 'img/ajax-loader.gif';
function o(el) {return document.getElementById(el);}
//---------------------------------------------------



var ImageDialog = {
	preInit : function() {
		var url;

		tinyMCEPopup.requireLangPack();

		if (url = tinyMCEPopup.getParam("external_image_list_url"))
			document.write('<script language="javascript" type="text/javascript" src="' + ImageDialog.baseURL(url) + '"></script>');
	},

	init : function(ed) {
		var f = document.forms[0], nl = f.elements, ed = tinyMCEPopup.editor, dom = ed.dom, n = ed.selection.getNode();

		tinyMCEPopup.resizeToInnerSize();
		this.fillClassList('class_list');
		this.fillFileList('src_list', 'tinyMCEImageList');
		this.fillFileList('over_list', 'tinyMCEImageList');
		this.fillFileList('out_list', 'tinyMCEImageList');

		if (n.nodeName == 'IMG') {
			nl.src.value = dom.getAttrib(n, 'src');
			nl.width.value = dom.getAttrib(n, 'width');
			nl.height.value = dom.getAttrib(n, 'height');
			nl.alt.value = dom.getAttrib(n, 'alt');
			nl.title.value = dom.getAttrib(n, 'title');
			nl.vspace.value = this.getAttrib(n, 'vspace');
			nl.hspace.value = this.getAttrib(n, 'hspace');
			nl.border.value = this.getAttrib(n, 'border');
			selectByValue(f, 'align', this.getAttrib(n, 'align'));
			selectByValue(f, 'class_list', dom.getAttrib(n, 'class'));
			nl.style.value = dom.getAttrib(n, 'style');
			nl.id.value = dom.getAttrib(n, 'id');
			nl.dir.value = dom.getAttrib(n, 'dir');
			nl.lang.value = dom.getAttrib(n, 'lang');
			nl.usemap.value = dom.getAttrib(n, 'usemap');
			nl.longdesc.value = dom.getAttrib(n, 'longdesc');
			nl.insert.value = ed.getLang('update');

			if (/^\s*this.src\s*=\s*\'([^\']+)\';?\s*$/.test(dom.getAttrib(n, 'onmouseover')))
				nl.onmouseoversrc.value = dom.getAttrib(n, 'onmouseover').replace(/^\s*this.src\s*=\s*\'([^\']+)\';?\s*$/, '$1');

			if (/^\s*this.src\s*=\s*\'([^\']+)\';?\s*$/.test(dom.getAttrib(n, 'onmouseout')))
				nl.onmouseoutsrc.value = dom.getAttrib(n, 'onmouseout').replace(/^\s*this.src\s*=\s*\'([^\']+)\';?\s*$/, '$1');

			if (ed.settings.inline_styles) {
				// Move attribs to styles
				if (dom.getAttrib(n, 'align'))
					this.updateStyle('align');

				if (dom.getAttrib(n, 'hspace'))
					this.updateStyle('hspace');

				if (dom.getAttrib(n, 'border'))
					this.updateStyle('border');

				if (dom.getAttrib(n, 'vspace'))
					this.updateStyle('vspace');
			}
		}

		// Setup browse button
		//document.getElementById('srcbrowsercontainer').innerHTML = getBrowserHTML('srcbrowser','src','image','theme_advanced_image');
		//if (isVisible('srcbrowser'))
		//	document.getElementById('src').style.width = '260px';

		// Setup browse button
		document.getElementById('onmouseoversrccontainer').innerHTML = getBrowserHTML('overbrowser','onmouseoversrc','image','theme_advanced_image');
		if (isVisible('overbrowser'))
			document.getElementById('onmouseoversrc').style.width = '260px';

		// Setup browse button
		document.getElementById('onmouseoutsrccontainer').innerHTML = getBrowserHTML('outbrowser','onmouseoutsrc','image','theme_advanced_image');
		if (isVisible('outbrowser'))
			document.getElementById('onmouseoutsrc').style.width = '260px';

		// If option enabled default contrain proportions to checked
		if (ed.getParam("advimage_constrain_proportions", true))
			f.constrain.checked = true;

		// Check swap image if valid data
		if (nl.onmouseoversrc.value || nl.onmouseoutsrc.value)
			this.setSwapImage(true);
		else
			this.setSwapImage(false);

		this.changeAppearance();
		this.showPreviewImage(nl.src.value, 1);
	},

	insert : function(file, title) {
		var ed = tinyMCEPopup.editor, t = this, f = document.forms[0];

		if (f.src.value === '') {
			if (ed.selection.getNode().nodeName == 'IMG') {
				ed.dom.remove(ed.selection.getNode());
				ed.execCommand('mceRepaint');
			}

			tinyMCEPopup.close();
			return;
		}

		if (tinyMCEPopup.getParam("accessibility_warnings", 1)) {
			if (!f.alt.value) {
				tinyMCEPopup.editor.windowManager.confirm(tinyMCEPopup.getLang('advimage_dlg.missing_alt'), function(s) {
					if (s)
						t.insertAndClose();
				});

				return;
			}
		}

		t.insertAndClose();
	},

	insertAndClose : function() {
		var ed = tinyMCEPopup.editor, f = document.forms[0], nl = f.elements, v, args = {}, el;

		tinyMCEPopup.restoreSelection();

		// Fixes crash in Safari
		if (tinymce.isWebKit)
			ed.getWin().focus();

		if (!ed.settings.inline_styles) {
			args = {
				vspace : nl.vspace.value,
				hspace : nl.hspace.value,
				border : nl.border.value,
				align : getSelectValue(f, 'align')
			};
		} else {
			// Remove deprecated values
			args = {
				vspace : '',
				hspace : '',
				border : '',
				align : ''
			};
		}

		tinymce.extend(args, {
			src : nl.src.value,
			width : nl.width.value,
			height : nl.height.value,
			alt : nl.alt.value,
			title : nl.title.value,
			'class' : getSelectValue(f, 'class_list'),
			style : nl.style.value,
			id : nl.id.value,
			dir : nl.dir.value,
			lang : nl.lang.value,
			usemap : nl.usemap.value,
			longdesc : nl.longdesc.value
		});

		args.onmouseover = args.onmouseout = '';

		if (f.onmousemovecheck.checked) {
			if (nl.onmouseoversrc.value)
				args.onmouseover = "this.src='" + nl.onmouseoversrc.value + "';";

			if (nl.onmouseoutsrc.value)
				args.onmouseout = "this.src='" + nl.onmouseoutsrc.value + "';";
		}

		el = ed.selection.getNode();

		if (el && el.nodeName == 'IMG') {
			ed.dom.setAttribs(el, args);
		} else {
			ed.execCommand('mceInsertContent', false, '<img id="__mce_tmp" src="javascript:;" />', {skip_undo : 1});
			ed.dom.setAttribs('__mce_tmp', args);
			ed.dom.setAttrib('__mce_tmp', 'id', '');
			ed.undoManager.add();
		}

		tinyMCEPopup.close();
	},

	getAttrib : function(e, at) {
		var ed = tinyMCEPopup.editor, dom = ed.dom, v, v2;

		if (ed.settings.inline_styles) {
			switch (at) {
				case 'align':
					if (v = dom.getStyle(e, 'float'))
						return v;

					if (v = dom.getStyle(e, 'vertical-align'))
						return v;

					break;

				case 'hspace':
					v = dom.getStyle(e, 'margin-left')
					v2 = dom.getStyle(e, 'margin-right');

					if (v && v == v2)
						return parseInt(v.replace(/[^0-9]/g, ''));

					break;

				case 'vspace':
					v = dom.getStyle(e, 'margin-top')
					v2 = dom.getStyle(e, 'margin-bottom');
					if (v && v == v2)
						return parseInt(v.replace(/[^0-9]/g, ''));

					break;

				case 'border':
					v = 0;

					tinymce.each(['top', 'right', 'bottom', 'left'], function(sv) {
						sv = dom.getStyle(e, 'border-' + sv + '-width');

						// False or not the same as prev
						if (!sv || (sv != v && v !== 0)) {
							v = 0;
							return false;
						}

						if (sv)
							v = sv;
					});

					if (v)
						return parseInt(v.replace(/[^0-9]/g, ''));

					break;
			}
		}

		if (v = dom.getAttrib(e, at))
			return v;

		return '';
	},

	setSwapImage : function(st) {
		var f = document.forms[0];

		f.onmousemovecheck.checked = st;
		setBrowserDisabled('overbrowser', !st);
		setBrowserDisabled('outbrowser', !st);

		if (f.over_list)
			f.over_list.disabled = !st;

		if (f.out_list)
			f.out_list.disabled = !st;

		f.onmouseoversrc.disabled = !st;
		f.onmouseoutsrc.disabled	= !st;
	},

	fillClassList : function(id) {
		var dom = tinyMCEPopup.dom, lst = dom.get(id), v, cl;

		if (v = tinyMCEPopup.getParam('theme_advanced_styles')) {
			cl = [];

			tinymce.each(v.split(';'), function(v) {
				var p = v.split('=');

				cl.push({'title' : p[0], 'class' : p[1]});
			});
		} else
			cl = tinyMCEPopup.editor.dom.getClasses();

		if (cl.length > 0) {
			lst.options[lst.options.length] = new Option(tinyMCEPopup.getLang('not_set'), '');

			tinymce.each(cl, function(o) {
				lst.options[lst.options.length] = new Option(o.title || o['class'], o['class']);
			});
		} else
			dom.remove(dom.getParent(id, 'tr'));
	},

	fillFileList : function(id, l) {
		var dom = tinyMCEPopup.dom, lst = dom.get(id), v, cl;

		l = window[l];

		if (l && l.length > 0) {
			lst.options[lst.options.length] = new Option('', '');

			tinymce.each(l, function(o) {
				lst.options[lst.options.length] = new Option(o[0], o[1]);
			});
		} else
			dom.remove(dom.getParent(id, 'tr'));
	},

	resetImageData : function() {
		var f = document.forms[0];

		f.elements.width.value = f.elements.height.value = '';
	},

	updateImageData : function(img, st) {
		var f = document.forms[0];

		if (!st) {
			f.elements.width.value = img.width;
			f.elements.height.value = img.height;
		}

		this.preloadImg = img;
	},

	changeAppearance : function() {
		var ed = tinyMCEPopup.editor, f = document.forms[0], img = document.getElementById('alignSampleImg');

		if (img) {
			if (ed.getParam('inline_styles')) {
				ed.dom.setAttrib(img, 'style', f.style.value);
			} else {
				img.align = f.align.value;
				img.border = f.border.value;
				img.hspace = f.hspace.value;
				img.vspace = f.vspace.value;
			}
		}
	},

	changeHeight : function() {
		var f = document.forms[0], tp, t = this;

		if (!f.constrain.checked || !t.preloadImg) {
			return;
		}

		if (f.width.value == "" || f.height.value == "")
			return;

		tp = (parseInt(f.width.value) / parseInt(t.preloadImg.width)) * t.preloadImg.height;
		f.height.value = tp.toFixed(0);
	},

	changeWidth : function() {
		var f = document.forms[0], tp, t = this;

		if (!f.constrain.checked || !t.preloadImg) {
			return;
		}

		if (f.width.value == "" || f.height.value == "")
			return;

		tp = (parseInt(f.height.value) / parseInt(t.preloadImg.height)) * t.preloadImg.width;
		f.width.value = tp.toFixed(0);
	},

	updateStyle : function(ty) {
		var dom = tinyMCEPopup.dom, st, v, f = document.forms[0], img = dom.create('img', {style : dom.get('style').value});

		if (tinyMCEPopup.editor.settings.inline_styles) {
			// Handle align
			if (ty == 'align') {
				dom.setStyle(img, 'float', '');
				dom.setStyle(img, 'vertical-align', '');

				v = getSelectValue(f, 'align');
				if (v) {
					if (v == 'left' || v == 'right')
						dom.setStyle(img, 'float', v);
					else
						img.style.verticalAlign = v;
				}
			}

			// Handle border
			if (ty == 'border') {
				dom.setStyle(img, 'border', '');

				v = f.border.value;
				if (v || v == '0') {
					if (v == '0')
						img.style.border = '';
					else
						img.style.border = v + 'px solid black';
				}
			}

			// Handle hspace
			if (ty == 'hspace') {
				dom.setStyle(img, 'marginLeft', '');
				dom.setStyle(img, 'marginRight', '');

				v = f.hspace.value;
				if (v) {
					img.style.marginLeft = v + 'px';
					img.style.marginRight = v + 'px';
				}
			}

			// Handle vspace
			if (ty == 'vspace') {
				dom.setStyle(img, 'marginTop', '');
				dom.setStyle(img, 'marginBottom', '');

				v = f.vspace.value;
				if (v) {
					img.style.marginTop = v + 'px';
					img.style.marginBottom = v + 'px';
				}
			}

			// Merge
			dom.get('style').value = dom.serializeStyle(dom.parseStyle(img.style.cssText));
		}
	},

	changeMouseMove : function() {
	},

	showPreviewImage : function(u, st) {
		if (!u) {
			tinyMCEPopup.dom.setHTML('prev', '');
			return;
		}

		if (!st && tinyMCEPopup.getParam("advimage_update_dimensions_onchange", true))
			this.resetImageData();

		u = ImageDialog.baseURL(u);

		if (!st)
			tinyMCEPopup.dom.setHTML('prev', '<img id="previewImg" src="' + u + '" border="0" onload="ImageDialog.updateImageData(this);" onerror="ImageDialog.resetImageData();" />');
		else
			tinyMCEPopup.dom.setHTML('prev', '<img id="previewImg" src="' + u + '" border="0" onload="ImageDialog.updateImageData(this, 1);" />');
	},
	

	
	//-----------------------------------------------------------
	// TinyCIMM image manager
	// Developer: Richard Willis
	//----------------------------------------------------------

	ci_controller : 'assetmanager',
	
	/* these following methods handle the different tab panels */
	showGeneral : function() {
		mcTabs.displayTab('general_tab','general_panel');
	},
	showAdvanced : function() {
		mcTabs.displayTab('advanced_tab','advanced_panel');
	},
	showUploader : function() {
		mcTabs.displayTab('upload_tab','upload_panel');
		ImageDialog.loaduploader();
	},
	showAppearance : function() {
		mcTabs.displayTab('appearance_tab','appearance_panel');
	},
	showManager : function() {
		ImageDialog.loadManager();
	},
	showBrowser : function(folder) {
		mcTabs.displayTab('browser_tab','browser_panel');
		ImageDialog.fileBrowser(folder)
	},

	// clear all image data fields
	resetImageDialog : function() {
		o('src').value = o('alt').value = o('title').value = o('width').value = o('height').value = o('style').value = '';
	},

	 // load list of folders and images via json request
	fileBrowser : function(folder) {
		folder = folder || 0;
		if (o('img-'+folder) == null) {
			tinyMCEPopup.dom.setHTML('filebrowser', '<span id="loading">loading</span>');
		}
		else {
			o('img-'+folder).src = ajax_img;
		}

		tinymce.util.XHR.send({
			url : ImageDialog.baseURL('assetmanager/image/get_file_folder_list/'+folder),
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
			url : ImageDialog.baseURL('assetmanager/image/change_view_adv/view/'+view),
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
		if (o('upload_target_ajax').src == '') {
			o('upload_target_ajax').src = 'uploadform.htm';
		} 
		this.loadselect();
		// show loading msg
		tinyMCEPopup.dom.setHTML('fileuploader_info', '<span id="loading">loading</span>');
		// send a request for user info
		tinymce.util.XHR.send({
			url : ImageDialog.baseURL('assetmanager/image/get_user_info/'),
			error : function(text) {
				tinyMCEPopup.editor.windowManager.alert('There was an error retrieving your user info.');
			},
			success : function(text) {
				 tinyMCEPopup.dom.setHTML('fileuploader_info', text);
			}
		});
		tinyMCEPopup.resizeToInnerSize();
	},
	
	// prepare image manager panel
	loadManager : function() {
		if (o('image_alttext')) {
			tinyMCEPopup.dom.setHTML('alttext_container', '<textarea id="image_alttext" style="color:#aaa;width: 160px; height: 36px;">loading</textarea>');
		}
		if (o('src').value == '') {
			tinyMCEPopup.editor.windowManager.alert('You need to select an image first.', 
			function(s) {
				// if not already viewing the browser
				if (o('browser_tab').className != "current") {
					ImageDialog.showBrowser();
				}
			});
			return;
		}
		// show loading img
		tinyMCEPopup.dom.setHTML('folder_select_list', '<select><option>loading..</option></select>');
		// prep thumb path
		var imgsrc_arr = tinyMCEPopup.editor.documentBaseURI.toRelative(o('src').value).split('/');
		var imgsrc = imgsrc_arr[imgsrc_arr.length-1];
		var imgid = imgsrc.replace(/(.*\/)?([0-9]+)\.([a-zA-Z]+)/, "$2");
		// set thumb	
		o('manage_thumb_img').src = 'img/progress.gif';
		o('manage_thumb_img').width = 95;
		o('manage_thumb_img').height = 95;
	
		// display panel
		mcTabs.displayTab('manager_tab','manager_panel');
		// send a request for image info
		tinymce.util.XHR.send({
			url : ImageDialog.baseURL('assetmanager/image/get_image/'+imgid),
			error : function(response) {
				tinyMCEPopup.editor.windowManager.alert('There was an error retrieving the image info.');
			},
			success : function(response) {
				var obj = tinymce.util.JSON.parse(response);
				if (obj.outcome == 'error') {
					tinyMCEPopup.editor.windowManager.alert(obj.message);
				}
				else {
					o('del_image').rel = obj.id;
					o('manage_thumb_img').src = ImageDialog.baseURL('/assetmanager/image/get/'+obj.id+'/95/95');
					ImageDialog.loadSelectManager(obj.folder);
					ImageDialog.loadAltTextManager(obj.alttext);
				}
			}
		});
		
		return;
		//tinyMCEPopup.resizeToInnerSize();
	},

	// updates image form fields after successfull upload
	updateImage : function(imgsrc, alttext) {
		var imgsrc = o('src').value=ImageDialog.baseURL(imgsrc.replace('thumbs/',''));
		o('alt').value = alttext;
		this.showPreviewImage(imgsrc);
		this.loadManager();
		tinyMCEPopup.editor.windowManager.alert('Image uploaded successfully, please update the image description.');
	},
	
	
	// get select list of folders in html select & option format (var folder would give option selected attr)
	loadSelectManager : function(folder) {
		folder = folder==undefined?'':folder;
		tinymce.util.XHR.send({
			url : ImageDialog.baseURL('assetmanager/image/get_folder_select/folder/'+folder),
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
			url : ImageDialog.baseURL('assetmanager/image/get_alttext_textbox/alttext/'+alttext),
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
		if (o('src').value == '') {
			tinyMCEPopup.editor.windowManager.alert('You need to select an image first.', 
			function(s) {
					// if not already viewing the browser
					if (o('browser_tab').className != "current") {
						ImageDialog.showBrowser();
					}
			});
			return;
		}
		// ensure image is cached before loading the resizer
		this.loadImage(ImageDialog.baseURL(o('src').value));
	},

	// pre-cache an image
	loadImage : function(img) { 
		preImage = new Image();
		preImage.src = img;
		//console.debug(preImage);
		setTimeout("ImageDialog.checkImgLoad()",10);	// ie
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
 		setTimeout("ImageDialog.checkLoad()", 10);
	},
	
	// show resizer image
	showResizeImage : function() {
		// load image 
		o('slider_img').src = ImageDialog.baseURL(o('src').value);
		o('slider_img').width = max_w = o('width').value;
		o('slider_img').height = max_h = o('height').value;
		// display panel
		mcTabs.displayTab('resize_tab','resize_panel');
		// image dimensions overlay layer
		tinyMCEPopup.dom.setHTML('image-info-dimensions', '<span id="slider_width_val"></span> x <span id="slider_height_val"></span>');
		// image scroller
		new ScrollSlider(o('image-slider'), {
			min : 0,
			max : max_w,
			value : max_w,
			size : 380,
			scroll : function(new_w) {
				// onscroll => update image dimensions
				o('slider_width_val').innerHTML = (o('slider_img').width=new_w);
				o('slider_height_val').innerHTML = (o('slider_img').height=Math.round((parseInt(new_w)/parseInt(max_w))*max_h))+'px';
				}
		});
	},
	
	// load list of folders via request
	loadselect : function(folder) {
		folder = folder==undefined?'':folder;
		tinymce.util.XHR.send({
			url : ImageDialog.baseURL('assetmanager/image/get_folder_select/folder/'+folder),
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
		imgsrc = o('src').value = ImageDialog.baseURL('assets/'+imgsrc);
		o('alt').value = alttext;
		o('title').value = '';
		this.showPreviewImage(imgsrc);
		this.showGeneral();
	},
	
	saveImgDetails : function() {
		tinyMCEPopup.editor.windowManager.alert('Image details changed.');
	},
	
	saveImgSize : function() {
		// show loading animation
		o('saveimg').src = o('saveimg').src.replace('save.gif', 'ajax-loader.gif');
		
		// prepare request url
		var replace = o('replace').checked == true ? '1' : '0';
		var imgsrc_arr = tinyMCEPopup.editor.documentBaseURI.toRelative(o('slider_img').src).split('/');
		var requesturl = ImageDialog.baseURL('assetmanager/image/save_image_size/'+imgsrc_arr[imgsrc_arr.length-1]+'/'+o('slider_img').width+'/'+o('slider_img').height+'/90/'+replace);
		// send request
		tinymce.util.XHR.send({
			url : requesturl,
			error : function(response) {
				tinyMCEPopup.editor.windowManager.alert('There was an error processing the request: '+response+"\nPlease try again.");
				o('saveimg').src = o('saveimg').src.replace('ajax-loader.gif', 'save.gif');
			},
			success : function(response) {
				o('saveimg').src = o('saveimg').src.replace('ajax-loader.gif', 'save.gif');
				var obj = tinymce.util.JSON.parse(response);
				if (obj.outcome == 'error') {
					tinyMCEPopup.editor.windowManager.alert(obj.message); 
				}
				else if (obj.outcome == 'success') {
					tinyMCEPopup.editor.windowManager.alert('Image size successfully saved.', 
					function(s) {
						var imgsrc = ImageDialog.baseURL(o('slider_img').src);
						o('src').value = imgsrc;
						o('width').value = o('slider_img').width;
						o('height').value = o('slider_img').height;
						ImageDialog.updateStyle();
						ImageDialog.showPreviewImage(imgsrc, 1);
						ImageDialog.showGeneral();
					});
				}
			}
		});
	},
	
	// add image folder
	addFolder : function() {
		var captionID = o('add_folder_caption').value.replace(/^\s+|\s+$/g, '');
		// send request
		var requesturl = ImageDialog.baseURL('assetmanager/image/add_folder')+'/'+captionID;
		tinymce.util.XHR.send({
			url : requesturl,
			error : function(response) {
				tinyMCEPopup.editor.windowManager.alert('There was an error processing the request.');
			},
			success : function(response) {
				var obj = tinymce.util.JSON.parse(response);
				if (obj != undefined && obj.outcome == 'error') {
						tinyMCEPopup.editor.windowManager.alert('Error: '+obj.message);
				}
				else {
					//success
					tinyMCEPopup.dom.setHTML('folderlist', response)
					o('addfolder').style.display = 'none';
					o('add_folder_caption').value = '';
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
 			var requesturl = ImageDialog.baseURL('assetmanager/image/delete_folder')+'/'+folderID;
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
						ImageDialog.getFoldersHTML(function(folderHTML){
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
 		var requesturl = ImageDialog.baseURL('assetmanager/image/get_folders_html');
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
			var img_delete_src = o('img_delete').src, folder = '';
			o('img_delete').src = ajax_img;
			// send request
			var requesturl = ImageDialog.baseURL('assetmanager/image/delete_image/')+'/'+imageID;
			tinymce.util.XHR.send({
				url : requesturl,
				error : function(response) {
					o('img_delete').src = img_delete_src;
					tinyMCEPopup.editor.windowManager.alert('There was an error processing the request.');
				},
				success : function(response) {
					o('img_delete').src = img_delete_src;
					var obj = tinymce.util.JSON.parse(response);
					if (obj.outcome == 'error') {
						tinyMCEPopup.editor.windowManager.alert('Error: '+obj.message);
					}
					else {
						tinyMCEPopup.editor.windowManager.alert(obj.message);
						folder = obj.folder
					}
 					// reset inputs, loadbrowser
 					ImageDialog.resetImageDialog();
				 	ImageDialog.showPreviewImage();
				 	ImageDialog.showBrowser(folder);
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
			
			o('filebrowser').style.height = Math.abs(wHeight+94) + 'px';
			//if (o('filelist') != null) {
			// o('filelist').style.height = Math.abs(wHeight+78) + 'px';
			//}
			if (o('resizer') != null) {
			 o('resizer').style.height = Math.abs(wHeight+90) + 'px';
			}
			if (o('image-info') != null) {
			 o('image-info').style.height = Math.abs(wHeight+44) + 'px';
			}
			o('image-slider').size = Math.abs(wWidth-190);
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
		o('info_tab_link').className = 'rightclick';
		setTimeout(function() {
			location.reload();
			tinyMCEPopup.resizeToInnerSize();
		}, 300);
	}
	
	
	//--------------------------------------
	// end Image Manager
	// ------------------------------------------
};

ImageDialog.preInit();
tinyMCEPopup.onInit.add(ImageDialog.init, ImageDialog);
