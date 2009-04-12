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
		<br class="clear" />
	</li>
	<?endforeach;?>
</ul>
