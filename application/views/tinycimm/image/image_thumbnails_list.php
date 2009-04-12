<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr><td width="178" valign="top">
<div id="addfolder" style="display:none;">
<div class="heading">&raquo; Add Folder</div>
<input type="text" id="add_folder_caption" class="input" style="width:136px;float:left;margin-right:5px"> 
<img src="img/save.gif" onClick="ImageDialog.addFolder();" style="cursor:pointer;float:left;opacity:0.65" onMouseOver="this.style.opacity='1';" onMouseOut="this.style.opacity='0.65';"" alt="save folder" title="save folder" />
&nbsp;<img src="img/cancel.png" onclick="o('addfolder').style.display='none';o('add_folder_caption').value='';" style="cursor:pointer;opacity:0.65" onMouseOver="this.style.opacity='1';" onMouseOut="this.style.opacity='0.65';" title="cancel" />
<div style="clear:left"></div>
</div>

<div class="heading">
<span style="float:right;padding-right:2px;font-weight:normal">
[<a href="#" onclick="o('addfolder').style.display='block';o('add_folder_caption').focus()">add</a>]
</span>&raquo; Folders</div>


<div id="folderlist">
<ul class="folderlist">
<?foreach($folders AS $folder):?>
<li>
<span style="display:block" id="folder-<?=$folder['id'];?>" onMouseOver="this.style.color='#000066';this.style.background='#EEEEEE';" onMouseOut="this.style.color='#000000';this.style.background='#FFFFFF';">
<span class="editimg">
<a href="javascript:;"><img height="13" onclick="editFolder('53');" title="edit" src="img/pencil_sm.png"/></a>
<a href="javascript:;"><img onclick="ImageDialog.deleteFolder('<?=$folder['id'];?>');" title="remove" src="img/delete.gif"/></a>
</span>
<span style="cursor:pointer;" onClick="ImageDialog.fileBrowser('<?=$folder['id'];?>');"><img class="folderimg" id="img-<?=$folder['id'];?>" src="img/folder.gif" /> <?=$folder['caption'];?>/</span>
</span>
<br class="clear" /></li>
<?endforeach;?>
</ul>
</div>
<br/>
<div class="heading">&raquo; Folder Info</div>
<table border="0" cellpadding="2" cellspacing="1">
<tr><td>Images:</td><td><?=$folderinfo['num_files'];?></td></tr>
<tr><td>Size:</td><td><?=$folderinfo['tot_file_size'];?></td></tr>
<tr><td>Owner:</td><td><?=$folderinfo['username'];?></td></tr>
<tr><td>View:</td><td>
<select style="border:1px solid #AAA" onchange="ImageDialog.changeView(this.options[this.selectedIndex].value)">
<optgroup label="Views">
<option value="listing">File Listing</option>
<option selected>Thumbnails</option>
</optgroup>
</select></td></tr>
</table></td>
<td width="5">&nbsp;</td>
<td valign="top">
<div class="heading">&raquo; <?=$folderinfo['caption'];?></div>

<div id="filelist">
<div id="filelist-contextmenu"></div>
<?if (sizeof($images) == 0) {?>
(folder is empty)
<?} else {?>
<?foreach($images AS $image):?>

<span class="thumb_wrapper" title="insert image" onclick="ImageDialog.insertPreviewImage('<?=$image['filename'];?>', '<?=$image['alttext'];?>');" onMouseOver="this.className='thumb_wrapper_over';" onMouseOut="this.className='thumb_wrapper';">
<span id="image-<?=$image['id'];?>"></span>
<img id="img-<?=$image['id'];?>" class="thumb_preview" src="/images/uploaded/thumbs/<?=$image['filename'];?>" />
</span>

<?endforeach;?>
<?}?>
<br class="clear" />
</div>
</td></tr>
</table>
