/*
 *
 * tinycimm.js
 * Copyright (c) 2009 Richard Willis & Liam Gooding
 * MIT license  : http://www.opensource.org/licenses/mit-license.php
 * Project      : http://tinycimm.googlecode.com/
 * Contact      : willis.rh@gmail.com
 *
 */

String.prototype.toId = function(){
        return /\//.test(this) ? this.replace(/.*\/([0-9]+).*$/, '$1') : this.replace(/([0-9]+).*$/, '$1')
}
String.prototype.ucfirst = function(){
	return this.substr(0, 1).toUpperCase()+this.substr(1, this.length-1).toLowerCase();
}


function TinyCIMM(type){
	this.type = type || null;
	this.settings = tinyMCEPopup.editor.settings;
}

TinyCIMM.prototype.init = function(ed){
	var n = ed.selection.getNode();
	if (tinyMCEPopup.params.resize) {
		this.loadresizer(n.src);
	} else {
		this.showBrowser(0);
	}
}

TinyCIMM.prototype.baseURL = function(url) {
	return tinyMCEPopup.editor.documentBaseURI.toAbsolute(url);
}

TinyCIMM.prototype.get = function(assetid, callback){
	tinymce.util.XHR.send({
		url : this.baseURL(this.settings.tinycimm_controller+this.type+'/get_'+this.type+'/'+assetid),
		error : function(response) {
			tinyMCEPopup.editor.windowManager.alert('There was an error retrieving the image info.');
			return false;
		},
		success : function(response) {
			var obj = tinymce.util.JSON.parse(response);
			if (!obj.outcome) {
				tinyMCEPopup.editor.windowManager.alert(obj.message);
			} else {
				(callback) && callback(obj);
			}
		}
	});
}

TinyCIMM.prototype.showBrowser = function(folder) {
	mcTabs.displayTab('browser_tab','browser_panel');
	tinyMCEPopup.dom.get('resize_tab').style.display = 'none';
	(this.fileBrowser) && this.fileBrowser(folder);
}

TinyCIMM.prototype.showUploader = function() {
	mcTabs.displayTab('upload_tab','upload_panel');
	tinyMCEPopup.dom.get('resize_tab').style.display = 'none';
	(this.loadUploader) && this.loadUploader();
}

// load list of folders and files via json request
TinyCIMM.prototype.getBrowser = function(folder, offset, callback) {
	var _this = this;
	folder = folder || 0;
	offset = offset || 0;
	if (tinyMCEPopup.dom.get('img-'+folder) == null) {
		tinyMCEPopup.dom.setHTML('filebrowser', '<span id="loading">loading</span>');
	}
	(this.type) && tinymce.util.XHR.send({
		url : this.baseURL(this.settings.tinycimm_controller+this.type+'/get_browser/'+folder+'/'+offset),
		error : function(reponse) {
			tinyMCEPopup.editor.windowManager.alert('Sorry, there was an error retrieving the assets.');
		},
		success : function(response) {
			// insert the html
			tinyMCEPopup.dom.setHTML('filebrowser', response);
			// bind click event to pagination links
			var pagination_anchors = tinyMCEPopup.dom.select('div.pagination a');
			for(var anchor in pagination_anchors) {
				pagination_anchors[anchor].onclick = function(e){
					e.preventDefault();
					_this.getBrowser(folder, this.href.toId().toString());
				};
			}
			(callback) && callback();
		}
	});
}

TinyCIMM.prototype.insert = function(assetid) {
	var _this = this;
	this.get(assetid, function(asset){
		_this.insertAndClose(asset);
	});
}
	
TinyCIMM.prototype.loadSelect = function(folder) {
	folder = folder || 0;
	tinymce.util.XHR.send({
		url : this.baseURL(this.settings.tinycimm_controller+'image/get_folders_select/'+folder),
		error : function(text) {
			tinyMCEPopup.editor.windowManager.alert('There was an error retrieving the select list.');
		},
		success : function(data) {
			try {
				if (typeof window.upload_target_ajax == 'object') {
					// this ensures iframe src file has loaded correctly
					setTimeout(function(){
						var d = window.upload_target_ajax.document.getElementById('folder_select_list');
						if (d) {
							d.innerHTML = data;
						} else {
							tinyMCEPopup.dom.setHTML('folder_select_list', data);
						}
					}, 500);
				}
			}
			catch(e) {}
		}
	});
}

// file upload callback function
TinyCIMM.prototype.assetUploaded = function(folder) {
	tinyMCEPopup.editor.windowManager.alert(this.type.ucfirst()+' successfully uploaded!');
	this.showBrowser(folder);
}
	
TinyCIMM.prototype.addFolder = function() {
	var captionID = encodeURIComponent(tinyMCEPopup.dom.get('add_folder_caption').value.replace(/^\s+|\s+$/g, ''));
	var requesturl = this.baseURL(this.settings.tinycimm_controller+this.type+'/add_folder/'+captionID);
	tinymce.util.XHR.send({
		url : requesturl,
		error : function(response) {
			tinyMCEPopup.editor.windowManager.alert('There was an error processing the request.');
		},
		success : function(response) {
			var obj = tinymce.util.JSON.parse(response);
			if (!obj.outcome) {
					tinyMCEPopup.editor.windowManager.alert('Error: '+obj.message);
			} else {
				tinyMCEPopup.dom.setHTML('folderlist', response)
				tinyMCEPopup.dom.get('addfolder').style.display = 'none';
				tinyMCEPopup.dom.get('add_folder_caption').value = '';
			}
		}
	});
}

TinyCIMM.prototype.deleteFolder = function(folderid) {
	var _this = this;
	tinyMCEPopup.editor.windowManager.confirm('Are you sure you want to delete this folder?', function(s) {
		if (!s) { return false; }
		var requesturl = _this.baseURL(_this.settings.tinycimm_controller+_this.type+'/delete_folder/'+folderid);
		tinymce.util.XHR.send({
			url : requesturl,
			error : function(response) {
	 			tinyMCEPopup.editor.windowManager.alert('There was an error processing the request.');
			},
			success : function(response) {
	 			var obj = tinymce.util.JSON.parse(response);
				if (!obj.outcome) {
					tinyMCEPopup.editor.windowManager.alert('Error: '+obj.message);
	 			} else {
					_this.getFoldersHTML(function(folderHTML){
						tinyMCEPopup.dom.setHTML('folderlist', folderHTML)
					});
					if (obj.images_affected > 0) {
						tinyMCEPopup.editor.windowManager.alert(obj.images_affected+" images were moved to the root directory.");
						_this.showBrowser(0);
					}
	 			}
			}
		});
	});
}			
	
// get folders as html string
TinyCIMM.prototype.getFoldersHTML = function(callback) {
	tinymce.util.XHR.send({
		url : this.baseURL(this.settings.tinycimm_controller+'image/get_folders_html'),
		error : function(response) {
	 		tinyMCEPopup.editor.windowManager.alert('There was an error processing the request.');
		},
		success : function(response) {
			(callback) && callback(response.toString());	
		}
	});
}
	
TinyCIMM.prototype.deleteAsset = function(assetid) {
	var _this = this;
	tinyMCEPopup.editor.windowManager.confirm('Are you sure you want to delete this '+this.type+'?', function(s) {
		if (!s) {return false;}
		tinymce.util.XHR.send({
			url : _this.baseURL(_this.settings.tinycimm_controller+_this.type+'/delete_'+_this.type+'/'+assetid),
			error : function(response) {
				tinyMCEPopup.editor.windowManager.alert('There was an error processing the request.');
			},
			success : function(response) {
				var obj = tinymce.util.JSON.parse(response);
				if (!obj.outcome) {
					tinyMCEPopup.editor.windowManager.alert('Error: '+obj.message);
				} else {
					tinyMCEPopup.editor.windowManager.alert(obj.message);
			 		_this.showBrowser(obj.folder);
				}
			}
		});
	});
}
	
TinyCIMM.prototype.changeView = function(view) {
	// show loading image
	tinyMCEPopup.dom.setHTML('filebrowser', '<span id="loading">loading</span>');
	tinymce.util.XHR.send({
		url : this.baseURL(this.settings.tinycimm_controller+'image/change_view/'+view),
		error : function(text) {
			tinyMCEPopup.editor.windowManager.alert('There was an error processing the request.');
		},
		success : function(text) {
			tinyMCEPopup.dom.setHTML('filebrowser', text);
		}
	});
}
	
// reload dialog window to initial state
TinyCIMM.prototype.reload = function() {
	tinyMCEPopup.dom.get('info_tab_link').className = 'rightclick';
	setTimeout(function() {
		window.location.reload();
		tinyMCEPopup.resizeToInnerSize();
	}, 300);
}
