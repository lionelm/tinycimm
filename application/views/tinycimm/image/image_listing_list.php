<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr>
		<td width="178" valign="top">
			<div id="addfolder" style="display:none;">
				<div class="heading">&raquo; Add Folder</div>
				<input type="text" id="add_folder_caption" class="input" style="width:156px;float:left;margin-right:5px">
				<img src="img/save.gif" onClick="TinyCIMMImage.addFolder();" style="cursor:pointer;float:left" alt="save folder" title="save folder" />
				<div style="clear:left"></div>
			</div>
			<div class="heading">
				<span style="float:right;padding-right:2px;font-weight:normal">
					[<a href="#" onclick="tinyMCEPopup.dom.get('addfolder').style.display='block';tinyMCEPopup.dom.get('add_folder_caption').focus()">add</a>]
				</span>&raquo; Folders
			</div>
			<div id="folderlist">
				<?= $this->load->view($this->view_path.'image_folder_list');?>
			</div>
			<br/>
			<div class="heading">&raquo; Folder Info</div>
			<table border="0" cellpadding="2" cellspacing="1">
				<tr><td>Images:</td><td><?=$selected_folder_info['total_assets'];?></td></tr>
				<tr><td>Size:</td><td><?=$selected_folder_info['total_file_size'];?></td></tr>
				<!--<tr><td>Owner:</td><td><?=$selected_folder_info['username'];?></td></tr>-->
				<tr>
					<td>View:</td>
					<td>
						<select style="border:1px solid #AAA" onchange="TinyCIMMImage.changeView(this.options[this.selectedIndex].value)">
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
			<div class="heading">&raquo; <?=$selected_folder_info['name'];?></div>
			<div id="filelist">
				<ul class="folderlist">
				<?if (sizeof($images) == 0) {?>
					<li>(folder is empty)</li>
				<?} else {?>
					<?foreach($images as $image):?>
					<li>
						<span id="image-<?=$image['id'];?>" onclick="TinyCIMMImage.insertPreviewImage('<?=$image['id'];?>', '<?=$image['description'];?>');" style="cursor:pointer;display:block" title="insert image" onMouseOver="this.style.color='#000066';this.style.background='#EEEEEE';" onMouseOut="this.style.color='#000000';this.style.background='#FFFFFF';">
						<span class="image_dimensions" style="display:inline;"><?=$image['dimensions'];?></span>
						<img id="img-<?=$image['id'];?>" class="image_preview" src="img/icons/<?=str_replace('.', '', $image['extension']);?>.gif" />
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
