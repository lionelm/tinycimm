/**
 */

(function() {
	//tinymce.PluginManager.requireLangPack('tinycimmimage');

	tinymce.create('tinymce.plugins.TinyCIMMImagePlugin', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			ed.addCommand('mceTinyCIMMImage', function() {
				ed.windowManager.open({
					file : url + '/image.htm',
					width : 570,
					height : 416,
					inline : 1
				}, {
					plugin_url : url
				});
			});

			// register button
			ed.addButton('tinycimmimage', {
				title : 'Image Manager',
				cmd : 'mceTinyCIMMImage',
				image : url + '/img/insertimage.gif'
			});

			// Add a node change handler, selects the button in the UI when a image is selected
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('example', n.nodeName == 'IMG');
			});
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'TinyCIMM Image Plugin',
				author : 'Richard Willis & Liam Gooding',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/example',
				version : "0.1"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('tinycimmimage', tinymce.plugins.TinyCIMMImagePlugin);
})();
