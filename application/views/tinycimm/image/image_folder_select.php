<select name="uploadfolder" id="folderselect">
	<optgroup label="Image Folders">
		<option value="">General</option>
		<?foreach($folders AS $folderinfo):?>
		<option value="<?=$folderinfo['id'];?>"<?=($folderid==$folderinfo['id'])?' selected':'';?>><?=$folderinfo['name'];?></option>
		<?endforeach;?>
	</optgroup>
</select>
