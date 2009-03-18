<?php
/*
copywrite to go here
*/

class Tinycimm_model extends Model {
	
	function Tinycimm_model(){
		parent::Model();
	}
	
	/**
	* get an asset from the database
	*
	* @param integer|$image_id The id of the image to retrieve
	* @return Object| an object containing full database row for the image
	**/
	function get_asset($asset_id){
		return $this->db->where('id', (int) $asset_id)->get('asset', 1)->row();
	}
	
	/**
	* Deletes an asset's data from the database
	*
	* @param integer|$image_id The id of the image to delete
	**/
	function delete_asset($asset_id){
		$this->db->where('id', (int)$asset_id)->delete('asset');	
	}
	
	/**
	* Get all image folders
	*
	* @param String|$orderby the method to sort the results by
	* @return Array| the full query array
	**/
	function get_asset_folders($orderby='caption'){
		return $this->db->orderby($orderby, 'asc')->get('asset_folder')->row_array();
	}
}
?>
