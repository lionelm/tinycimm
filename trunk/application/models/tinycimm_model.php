<?php
/*
copywrite to go here
*/

class Tinycimm_model extends Model {
	
	function Tinycimm_model(){
		parent::Model();
	}
	
	/**
	* get an image from the database
	*
	* @param integer|$image_id The id of the image to retrieve
	* @return Object| an object containing full database row for the image
	**/
	function get_image($image_id){
		return $this->db->where('id', (int) $image_id)->get('images', 1)->row();
	}
	function get_asset($asset_id){
		return $this->db->where('id', (int) $asset_id)->get('asset', 1)->row();
	}
	
	/**
	* Deletes an images data from the database
	*
	* @param integer|$image_id The id of the image to delete
	**/
	function delete_image($image_id){
		$this->db->where('id', (int)$image_id)->delete('images');	
	}
	function delete_asset($asset_id){
		$this->db->where('id', (int)$asset_id)->delete('asset');	
	}
	
	/**
	* Get all image folders
	*
	* @param String|$orderby the method to sort the results by
	* @return Array| the full query array
	**/
	function get_image_folders($orderby='caption'){
		$sql = 'SELECT *
			FROM image_folders
			ORDER BY ? ASC';
			
		return $this->db->query($sql, array($orderby))->result_array();
	}
}
?>
