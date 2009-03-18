<?php
class Tinycimm_model extends Model {
	
	function Tinycimm_model() {
		parent::Model();
	}
	
	/**
	* get an image from the database
	*
	* @param integer|$image_id The id of the image to retrieve
	* @return Array| an array containing full database row for the image
	**/
	function get_image($image_id) {
		$sql = 'SELECT *
			FROM images
			WHERE id = ?
			LIMIT 1';
		$query = $this->db->query($sql, array($image_id));
		
		return $query->row_array();
	}
	
	/**
	* Deletes an images data from the database
	*
	* @param integer|$image_id The id of the image to delete
	**/
	function delete_image($image_id){
		$sql = 'DELETE
			FROM images
			WHERE id = ?
			LIMIT 1';
			
		return $this->db->query($sql, array($image_id));
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