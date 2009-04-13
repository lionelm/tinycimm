<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr>
		<td width="178" valign="top">
			<div id="addfolder" style="display:none;">
				<div class="heading">&raquo; Add Folder</div>
				<input type="text" id="add_folder_caption" class="input" style="width:156px;float:left;margin-right:5px">
				<img src="img/save.gif" onClick="ImageDialog.addFolder();" style="cursor:pointer;float:left" alt="save folder" title="save folder" />
				<div style="clear:left"></div>
			</div>
			<div class="heading">
				<span style="float:right;padding-right:2px;font-weight:normal">
					[<a href="#" onclick="o('addfolder').style.display='block';o('add_folder_caption').focus()">add</a>]
				</span>&raquo; Folders
			</div>
			<div id="folderlist">
				<ul class="folderlist">
					<?foreach($folders AS $folder):?>
						<li>
							<span style="display:block" id="folder-<?=$folder['id'];?>" onMouseOver="this.style.color='#000066';this.style.background='#EEEEEE';" onMouseOut="this.style.color='#000000';this.style.background='#FFFFFF';">
								<span class="editimg">
									<a href="javascript:;"><img height="13" onclick="editFolder('53');" title="edit" src="img/pencil_sm.png"/></a>
									<a href="javascript:;"><img onclick="ImageDialog.deleteFolder('<?=$folder['id'];?>');" title="remove" src="img/delete.gif"/></a>
								</span>
								<span style="cursor:pointer;" onClick="ImageDialog.fileBrowser('<?=$folder['id'];?>');"><img class="folderimg" id="img-<?=$folder['id'];?>" src="img/folder.gif" /> <?=$folder['name'];?>/</span>
							</span>
							<br class="clear" />
						</li>
					<?endforeach;?>
				</ul>
			</div>
			<br/>
			<div class="heading">&raquo; Folder Info</div>
			<table border="0" cellpadding="2" cellspacing="1">
				<tr><td>Images:</td><td><?=$folderinfo['num_files'];?></td></tr>
				<tr><td>Size:</td><td><?=$folderinfo['tot_file_size'];?></td></tr>
				<tr><td>Owner:</td><td><?=$folderinfo['username'];?></td></tr>
				<tr>
					<td>View:</td>
					<td>
						<select style="border:1px solid #AAA" onchange="ImageDialog.changeView(this.options[this.selectedIndex].value)">
							<optgroup label="Views">
								<option value="listing" selected>File Listing</option>
								<option value="thumbnails">Thumbnails</option>
							</optgroup>
						</select>
					</td>
				</tr>
			</table>
		</td>
		<td width="5">&nbsp;</td>
		<td valign="top">
			<div class="heading">&raquo; <?=$folderinfo['name'];?></div>
			<div id="filelist">
				<ul class="folderlist">
				<?if (sizeof($images) == 0) {?>
					<li>(folder is empty)</li>
				<?} else {?>
					<?foreach($images AS $image):?>
					<li>
						<span id="image-<?=$image['id'];?>" onclick="ImageDialog.insertPreviewImage('<?=$image['filename'];?>', '<?=$image['alttext'];?>');" style="cursor:pointer;display:block" title="insert image" onMouseOver="this.style.color='#000066';this.style.background='#EEEEEE';" onMouseOut="this.style.color='#000000';this.style.background='#FFFFFF';">
						<span class="image_dimensions" style="display:inline;"><?=$image['dimensions'];?></span>
						<img id="img-<?=$image['id'];?>" class="image_preview" src="img/icons/<?=$image['extension'];?>.gif" />
						<?=$image['name'];?>
						<br class="clear" /></span>
					</li>
					<?endforeach;?>
				<?}?>
				</ul>
				<br class="clear" />
			</div>
		</td>
	</tr>
</table>
