// resizer vars
var max_w, max_h, new_w, new_h;
// return object
function o(el) {return document.getElementById(el);}

var ImageDialog = {
	preInit : function() {
		var url;
		tinyMCEPopup.requireLangPack();
		if (url = tinyMCEPopup.getParam("external_image_list_url"))
			document.write('<script language="javascript" type="text/javascript" src="' + tinyMCEPopup.editor.documentBaseURI.toAbsolute(url) + '"></script>');
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
		}

    
		// Setup browse button
		//
    //o('srcbrowsercontainer').innerHTML = getBrowserHTML('srcbrowser','src','image','theme_advanced_image');
		//if (isVisible('srcbrowser')) {
		//	o('src').style.width = '260px';
    //}
    
		// Setup browse button for mouse over img
		o('onmouseoversrccontainer').innerHTML = getBrowserHTML('overbrowser','onmouseoversrc','image','theme_advanced_image');
		if (isVisible('overbrowser'))
			o('onmouseoversrc').style.width = '260px';
      
		// Setup browse button for mouseout img
		o('onmouseoutsrccontainer').innerHTML = getBrowserHTML('outbrowser','onmouseoutsrc','image','theme_advanced_image');
		if (isVisible('outbrowser'))
			o('onmouseoutsrc').style.width = '260px';
    
		// If option enabled default contrain proportions to checked
		if (ed.getParam("advimage_constrain_proportions", true))
			f.constrain.checked = true;

		// Check swap image if valid data
		if (nl.onmouseoversrc.value || nl.onmouseoutsrc.value)
			this.setSwapImage(true);
		else
			this.setSwapImage(false);

		this.changeAppearance();
		this.updateStyle();
		this.showPreviewImage(nl.src.value, 1);
    
    
	},

	insert : function(file, title) {
		var t = this;

		if (tinyMCEPopup.getParam("accessibility_warnings", 1)) {
			if (!document.forms[0].alt.value) {
				tinyMCEPopup.editor.windowManager.alert(tinyMCEPopup.getLang('advimage_dlg.missing_alt'), function(s) {
					document.forms[0].alt.focus();
				});

				return;
			}
		}

		t.insertAndClose();
	},

	insertAndClose : function() {
		var ed = tinyMCEPopup.editor, f = document.forms[0], nl = f.elements, v, args = {}, el;

		// Fixes crash in Safari
		if (tinymce.isWebKit)
			ed.getWin().focus();

		if (!ed.settings.inline_styles) {
			args = tinymce.extend(args, {
				vspace : nl.vspace.value,
				hspace : nl.hspace.value,
				border : nl.border.value,
				align : getSelectValue(f, 'align')
			});
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

		if (nl.onmouseoversrc.value)
			args.onmouseover = "this.src='" + nl.onmouseoversrc.value + "';";

		if (nl.onmouseoutsrc.value)
			args.onmouseout = "this.src='" + nl.onmouseoutsrc.value + "';";

		el = ed.selection.getNode();

		if (el && el.nodeName == 'IMG') {
			ed.dom.setAttribs(el, args);
		} else {
			ed.execCommand('mceInsertContent', false, '<img id="__mce_tmp" src="javascript:;" />');
			ed.dom.setAttribs('__mce_tmp', args);
			ed.dom.setAttrib('__mce_tmp', 'id', '');
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
		f.onmouseoutsrc.disabled  = !st;
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
  
  // clear all image data fields
  resetImageDialog : function() {
    o('src').value = o('alt').value = o('title').value = o('width').value = o('height').value = o('style').value = '';
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
		var ed = tinyMCEPopup.editor, f = document.forms[0], img = o('alignSampleImg');

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
			t.updateStyle();
			return;
		}

		if (f.width.value == "" || f.height.value == "")
			return;

		tp = (parseInt(f.width.value) / parseInt(t.preloadImg.width)) * t.preloadImg.height;
		f.height.value = tp.toFixed(0);
		t.updateStyle();
    t.showPreviewImage(f.src.value, 1);
	},

	changeWidth : function() {
  
		var f = document.forms[0], tp, t = this;

		if (!f.constrain.checked || !t.preloadImg) {
			t.updateStyle();
			return;
		}

		if (f.width.value == "" || f.height.value == "")
			return;

		tp = (parseInt(f.height.value) / parseInt(t.preloadImg.height)) * t.preloadImg.width;
		f.width.value = tp.toFixed(0);
		t.updateStyle();
    t.showPreviewImage(f.src.value, 1);
	},

	updateStyle : function() {
    
		var dom = tinyMCEPopup.dom, st, v, f = document.forms[0];

		if (tinyMCEPopup.editor.settings.inline_styles) {
			st = tinyMCEPopup.dom.parseStyle(dom.get('style').value);

			// Handle align
			v = getSelectValue(f, 'align');
			if (v) {
				if (v == 'left' || v == 'right') {
					st['float'] = v;
					delete st['vertical-align'];
				} else {
					st['vertical-align'] = v;
					delete st['float'];
				}
			} else {
				delete st['float'];
				delete st['vertical-align'];
			}

			// Handle border
			v = f.border.value;
			if (v || v == '0') {
				if (v == '0')
					st['border'] = '0';
				else
					st['border'] = v + 'px solid black';
			} else
				delete st['border'];

			// Handle hspace
			v = f.hspace.value;
			if (v) {
				delete st['margin'];
				st['margin-left'] = v + 'px';
				st['margin-right'] = v + 'px';
			} else {
				delete st['margin-left'];
				delete st['margin-right'];
			}

			// Handle vspace
			v = f.vspace.value;
			if (v) {
				delete st['margin'];
				st['margin-top'] = v + 'px';
				st['margin-bottom'] = v + 'px';
			} else {
				delete st['margin-top'];
				delete st['margin-bottom'];
			}

			// Merge
			st = tinyMCEPopup.dom.parseStyle(dom.serializeStyle(st));
			dom.get('style').value = dom.serializeStyle(st);
		}
	},

	changeMouseMove : function() {
	},

	showPreviewImage : function(u, st) {

    var f = document.forms[0], dim;  
    
    if (!u) {
			tinyMCEPopup.dom.setHTML('prev', '');
      f.alt.value = '';
			return;
		}

    if (!st && tinyMCEPopup.getParam("advimage_update_dimensions_onchange", true))
			this.resetImageData();
    
		u = tinyMCEPopup.editor.documentBaseURI.toAbsolute(u);
    
    
    dim = (f.width.value != '' && f.height.value != '') ? 'width="' + f.width.value +'" height="' + f.height.value +'" ' : '';
    
		if (!st)
			tinyMCEPopup.dom.setHTML('prev', '<img id="previewImg" '+dim+'src="' + u + '" border="0" onload="ImageDialog.updateImageData(this);" onerror="ImageDialog.resetImageData();" />');
		else
			tinyMCEPopup.dom.setHTML('prev', '<img id="previewImg" '+dim+'src="' + u + '" border="0" onload="ImageDialog.updateImageData(this, 1);" />');
	},
  
  // load list of folders and images via json request
  loadfiles : function(folder) {
  alert(tinyMCEPopup.editor.documentBaseURI.toAbsolute('media/ajaxfilemanager/type/image/get_file_folder_list_ajax/folder/'));
    if (o('img-'+folder) == null) {
      tinyMCEPopup.dom.setHTML('filebrowser', '<span id="loading">loading</span>');
    }
    else {
      o('img-'+folder).src = 'images/ajax-loader.gif';
    }

    tinymce.util.XHR.send({
      url : tinyMCEPopup.editor.documentBaseURI.toAbsolute('media/ajaxfilemanager/type/image/get_file_folder_list_ajax/folder/'+folder),
      error : function(text) {
        alert('There was an error retrieving the images.');
        ImageDialog.resizeInputs();
      },
      success : function(text) {
        tinyMCEPopup.dom.setHTML('filebrowser', text);
        ImageDialog.resizeInputs();
      }
    });
  },
  
  changeView : function(view) {
    // show loading image
    o('filebrowser').innerHTML = '<span id="loading">loading</span>';
    tinymce.util.XHR.send({
      url : tinyMCEPopup.editor.documentBaseURI.toAbsolute('media/ajaxfilemanager/type/image/change_view_adv_ajax/view/'+view),
      error : function(text) {
        alert('There was an error processing the request.');
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
      url : tinyMCEPopup.editor.documentBaseURI.toAbsolute('media/ajaxfilemanager/type/image/get_user_info/'),
      error : function(text) {
        alert('There was an error retrieving your user info.');
      },
      success : function(text) {
        tinyMCEPopup.dom.setHTML('fileuploader_info', text);
      }
    });
    tinyMCEPopup.resizeToInnerSize();
  },
  
  // prepare image manager panel
  loadmanager : function() {
    if (o('src').value == '') {
      tinyMCEPopup.editor.windowManager.alert('You need to select an image first.', 
      function(s) {
          ImageDialog.showBrowser();
      });
      return;
    }
    // show loading img
    o('folder_select_list').innerHTML = '<select><option>loading..</option></select>';
    // prep thumb path
    var imgsrc_arr = tinyMCEPopup.editor.documentBaseURI.toRelative(o('src').value).split('/');
    var imgsrc = imgsrc_arr[imgsrc_arr.length-1];
    // set thumb  
    o('manage_thumb_img').src = 'images/progress.gif';
    o('manage_thumb_img').width = 95;
    o('manage_thumb_img').height = 95;
    o('image_alttext').innerHTML = '';
    // display panel
    mcTabs.displayTab('manager_tab','manager_panel');
    // send a request for image info
    tinymce.util.XHR.send({
      url : tinyMCEPopup.editor.documentBaseURI.toAbsolute('media/ajaxfilemanager/type/image/get_image_info_ajax/image/'+imgsrc),
      error : function(response) {
        alert('There was an error retrieving the image info.');
      },
      success : function(response) {
        var obj = tinymce.util.JSON.parse(response);
        if (obj.outcome == 'error') {
          alert(obj.message);
        }
        else {
          o('image_alttext').innerHTML = obj.alttext;
          o('del_image').rel = obj.id;
          o('manage_thumb_img').src = tinyMCEPopup.editor.documentBaseURI.toAbsolute('images/uploaded/thumbs/'+obj.filename);
          ImageDialog.loadselectmanager(obj.folder);
        }
      }
    });
    
    return;
    //tinyMCEPopup.resizeToInnerSize();
  },
  
  // get list of folders in html select format (var folder would give option selected attr)
  loadselectmanager : function(folder) {
    folder = folder==undefined?'':folder;
    tinymce.util.XHR.send({
      url : tinyMCEPopup.editor.documentBaseURI.toAbsolute('media/ajaxfilemanager/type/image/get_folder_select_ajax/folder/'+folder),
      error : function(response) {
        alert('There was an error retrieving the select list.');
      },
      success : function(response) {
          o('folder_select_list').innerHTML = response;
      }
    });
  },
  
  // prepare image attributes
  loadresizer : function() {
    if (o('src').value == '') {
      tinyMCEPopup.editor.windowManager.alert('You need to select an image first.', 
      function(s) {
        ImageDialog.showBrowser();
      });
      return;
    }
    // initial image 
    o('slider_img').src = tinyMCEPopup.editor.documentBaseURI.toAbsolute(o('src').value);
    o('slider_img').width = max_w = o('width').value;
    o('slider_img').height = max_h = o('height').value;
    // display panel
    mcTabs.displayTab('resize_tab','resize_panel');
    // image dimensions overlay layer
    var info = document.createElement('div');
    if (o('image-info-dimensions') == null) {
      info.id = 'image-info-dimensions'; 
      info.innerHTML = '<span id="slider_width_val"></span> x <span id="slider_height_val"></span>';
      o('image-info').appendChild(info);
    }
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
      url : tinyMCEPopup.editor.documentBaseURI.toAbsolute('media/ajaxfilemanager/type/image/get_folder_select_ajax/folder/'+folder),
      error : function(text) {
        alert('There was an error retrieving the select list.');
      },
      success : function(text) {
        try {
          if (typeof window.upload_target_ajax == 'object') {
            // this ensures iframe src file has loaded correctly
            setTimeout(function(){
              var d = window.upload_target_ajax.document.getElementById('folder_select_list');
              if (d) {
                d.innerHTML = text;
              } else {o('folder_select_list').innerHTML = text;}
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
    imgsrc = o('src').value = tinyMCEPopup.editor.documentBaseURI.toAbsolute('images/uploaded/'+imgsrc);
    o('alt').value = alttext;
    o('title').value = '';
    this.showPreviewImage(imgsrc);
    this.showGeneral();
  },
  
  // updates image form fields after successfull upload
  updateimage : function(imgsrc, alttext) {
    var imgsrc = o('src').value=tinyMCEPopup.editor.documentBaseURI.toAbsolute(imgsrc.replace('thumbs/',''));
    o('alt').value = alttext;
    this.showPreviewImage(imgsrc);
    this.loadmanager();
  },
  
  saveImgDetails : function() {
    alert('Image details changed.');
  },
  
  saveImgSize : function() {
    // show loading animation
    o('saveimg').src = o('saveimg').src.replace('save.gif', 'ajax-loader.gif');
    
    // prepare request url
    var replace = o('replace').checked == true ? '1' : '0';
    var imgsrc_arr = tinyMCEPopup.editor.documentBaseURI.toRelative(o('slider_img').src).split('/');
    var requesturl = tinyMCEPopup.editor.documentBaseURI.toAbsolute('media/ajaxfilemanager/type/image/save_image_size_ajax/img/'+imgsrc_arr[imgsrc_arr.length-1]+'/width/'+o('slider_img').width+'/height/'+o('slider_img').height+'/replace/'+replace);
    // send request
    tinymce.util.XHR.send({
      url : requesturl,
      error : function(response) {
        alert('There was an error processing the request: '+response+"\nPlease try again.");
        o('saveimg').src = o('saveimg').src.replace('ajax-loader.gif', 'save.gif');
      },
      success : function(response) {
        o('saveimg').src = o('saveimg').src.replace('ajax-loader.gif', 'save.gif');
        var outcome = response.split('|');
        if (outcome[0] == 'success') {
          tinyMCEPopup.editor.windowManager.alert('Image size successfully saved.', 
          function(s) {
            var imgsrc = tinyMCEPopup.editor.documentBaseURI.toAbsolute(o('slider_img').src);
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
    var requesturl = tinyMCEPopup.editor.documentBaseURI.toAbsolute('media/ajaxfilemanager/type/image/add_folder_ajax/caption')+'/'+captionID;
    tinymce.util.XHR.send({
      url : requesturl,
      error : function(response) {
        alert('There was an error processing the request.');
      },
      success : function(response) {
        var obj = tinymce.util.JSON.parse(response);
        if (obj['outcome'] == 'error') {
          alert('Error: '+obj['message']);
        }
        else {
          alert(obj['message']);
          ImageDialog.loadfiles();
        }
      }
    });
  },
  
  // delete image folder
  deleteFolder : function(folderID) {
    // send request
    var requesturl = tinyMCEPopup.editor.documentBaseURI.toAbsolute('media/ajaxfilemanager/type/image/delete_folder_ajax/folder')+'/'+folderID;
    tinymce.util.XHR.send({
      url : requesturl,
      error : function(response) {
        alert('There was an error processing the request.');
      },
      success : function(response) {
        var obj = tinymce.util.JSON.parse(response);
        if (obj['outcome'] == 'error') {
          alert('Error: '+obj['message']);
        }
        else {
          alert(obj['message']);
          ImageDialog.loadfiles();
        }
      }
    });
  },
  
  // delete image from db & filesystem
  deleteImage : function(imageID) {
    if (!confirm('Are you sure you want to delete this image?')) {return;}
    // loading img
    var img_delete_src = o('img_delete').src, folder = '';
    o('img_delete').src = 'images/ajax-loader.gif';
    // send request
    var requesturl = tinyMCEPopup.editor.documentBaseURI.toAbsolute('media/ajaxfilemanager/type/image/delete_image_ajax/image')+'/'+imageID;
    tinymce.util.XHR.send({
      url : requesturl,
      error : function(response) {
        o('img_delete').src = img_delete_src;
        alert('There was an error processing the request.');
      },
      success : function(response) {
        o('img_delete').src = img_delete_src;
        var obj = tinymce.util.JSON.parse(response);
        if (obj.outcome == 'error') {
          alert('Error: '+obj.message);
        }
        else {
          //alert(obj.message);
          folder = obj.folder
        }
        // reset inputs, loadbrowser
        ImageDialog.resetImageDialog();
        ImageDialog.showPreviewImage();
        ImageDialog.showBrowser(folder);
      }
    });
  },
  
  resizeInputs : function() {
    var wHeight=0, wWidth=0, owHeight=0, owWidth=0;
    try {
      var el = o('prev');
      var wr = o('image_wrapper_panel');
      var bp = o('filebrowser');
      var fl = o('filelist');
      var rz = o('resizer');
      var ii = o('image-info');
      var is = o('image-slider');
      
      
      if (!tinymce.isIE) {
         wHeight = self.innerHeight - 65;
         wWidth = self.innerWidth;
      } else {
         wHeight = document.body.clientHeight - 54;
         wWidth = document.body.clientWidth;
      }
      wHeight -= 130;
      //alert(document.getElementById('image_wrapper_panel').style.height);
      
      //document.getElementById('image_wrapper_panel').style.height = Math.abs(wHeight+100) + 'px';
      //return;
      
      
      //el.style.height = Math.abs(wHeight)-10 + 'px';
      //wr.style.height = Math.abs(wHeight+112) + 'px';
      
      bp.style.height = Math.abs(wHeight+84) + 'px';
      if (fl != null) {
       fl.style.height = Math.abs(wHeight+52) + 'px';
      }
      if (rz != null) {
       rz.style.height = Math.abs(wHeight+90) + 'px';
      }
      if (ii != null) {
       ii.style.height = Math.abs(wHeight+44) + 'px';
      }
      is.size = Math.abs(wWidth-190);
      //ScrollSlider.change();
      
    }
    catch(e) {
      // do nothing
       alert(e);
       return;
    }
  },
  
  // reload dialog window to initial state
  reload : function() {
    o('info_tab_link').className = 'rightclick';
    setTimeout(function() {
      location.reload();
      tinyMCEPopup.resizeToInnerSize();
    }, 300);
  },
  
 
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
    ImageDialog.loadmanager();
  },
  showBrowser : function(folder) {
    mcTabs.displayTab('browser_tab','browser_panel');
    ImageDialog.loadfiles(folder)
  }

  
};

ImageDialog.preInit();
tinyMCEPopup.onInit.add(ImageDialog.init, ImageDialog);