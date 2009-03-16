(function(){tinymce.create('tinymce.plugins.AdvancedImagePlugin',
{
  init:function(ed,url){
  ed.addCommand('mceAdvImage',
  function(){
    var e=ed.selection.getNode();
    //alert(ed.dom.getAttrib(e,'class'));
    if(ed.dom.getAttrib(e,'class').indexOf('mceItem')!=-1)return;
    ed.windowManager.open({file:url+'/image.htm',width:556+ed.getLang('advimage.delta_width',0),height:420+ed.getLang('advimage.delta_height',0),inline:1,maximizable:1},{plugin_url:url});});
    ed.addButton('image',{title:'advimage.image_desc',cmd:'mceAdvImage'});
    // Add a node change handler, selects the button in the UI when a image is selected
    ed.onNodeChange.add(function(ed, cm, n) {
       //alert(cm.title);
                  
      cm.setActive('myimgsplitbutton', n.nodeName == 'IMG');
      //cm.remove();
    });
  }
  ,createControl: function(n, cm) {
          switch (n) {
              case 'myimgsplitbutton':
                  var c = cm.createSplitButton('myimgsplitbutton', {
                      title : 'Insert/Edit Image',
                      image : 'http://127.0.0.1/tinycimm/clientscript/tiny_mce/themes/advanced/images/image.gif',
                      onclick : function() {tinyMCE.activeEditor.execCommand('mceAdvImage', false);}                
                  });
                  c.onRenderMenu.add(function(c, m) {
                      m.add({title : 'Image Options', 'class' : 'mceMenuItemTitle'}).setDisabled(1);

                      m.add({title : 'Insert/Edit Image', 'class' : 'mceMenuItem', onclick : function() {
                        tinyMCE.activeEditor.execCommand('mceAdvImage', false);
                      }});

                      m.add({title : 'Upload Image/s', 'class' : 'mceMenuItem', onclick : function() {
                          tinyMCE.activeEditor.execCommand('mceAdvImage', false);
                      }});
                      
                      m.add({title : 'Manage Images', 'class' : 'mceMenuItem', onclick : function() {
                          ajaxFileBrowser('src', '', 'image');
                      }});
                  });

                  // Return the new splitbutton instance
                  return c;
          }
                return null;
  }
  ,getInfo:function(){
    return{longname:'Advanced Image',author:'Richard Willis',authorurl:'http://tinymce.moxiecode.com',infourl:'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/advimage',version:'0.1b'};}}
  );
  tinymce.PluginManager.add('advimage',tinymce.plugins.AdvancedImagePlugin);
})();