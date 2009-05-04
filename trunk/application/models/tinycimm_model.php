<?php
/*
copywrite to go here
*/

class Tinycimm_model extends Model {
	
	function Tinycimm_model(){
		parent::Model();
	}
	
	/**
	* Get an asset from the database
	*
	* @param integer|$asset_id The id of the image to retrieve
	* @return Object| an object containing full database row for the image
	**/
	function get_asset($asset_id=0){
		return $this->db->where('id', (int) $asset_id)->get('asset', 1)->row();
	}

	
	/**
	* Get a list of assets by folder from the database
	*
	* @param integer|$folder_id The id of the folder the assets are related to
	* @return Object| a result object containing rows for the assets
	**/
	function get_assets($folder_id=0, $offset=NULL, $limit=NULL){
		return $this->db->where('folder_id', (int) $folder_id)->order_by('dateadded', 'desc')->get('asset', $limit, $offset)->result_array();
	}
	
	/**
	* Deletes an asset's data from the database
	*
	* @param integer|$asset_id The id of the image to delete
	**/
	function delete_asset($asset_id=0){
		return $this->db->where('id', (int) $asset_id)->delete('asset');	
	}

	/** 
	* Inserts an asset into the database
	*
	* @returns integer|insert_id the last insert id from the id sequence colmn
	**/
	function insert_asset($folder_id=0, $name='', $filename='', $description='', $extension='', $mimetype=''){
		$fields = array(
			'folder_id' => $folder_id, 
			'name' => $name, 
			'filename' => $filename, 
			'description' => $description, 
			'extension' => $extension, 
			'mimetype' => $mimetype
			);
		$this->db->set($fields)->insert('asset');
		return $this->db->insert_id();
	}
	
	/** 
	* Updates an asset details in the database
	*
	* @returns integer|insert_id the last insert id from the id sequence colmn
	**/
	function update_asset($column='', $id=0, $folder_id=0, $name='', $description='', $filename=''){
		$fields = array();
		if ($folder_id !== 0) {
			$fields['folder_id'] = $folder_id;
		}
		if ($name !== '') {
			$fields['name'] = $name;
		}
		if ($description !== '') {
			$fields['description'] = $description;
		}
		if ($filename !== '') {
			$fields['filename'] = $filename;
		}
		return $this->db->where($column, $id)->update('asset', $fields); 
	}


	/** 
	* Inserts a folder into the database
	*
	* @returns integer|insert_id the last insert id from the id sequence colmn
	**/
	function insert_folder($folder_name=''){
		$fields = array('name' => $folder_name);
		$this->db->set($fields)->insert('asset_folder');
		return $this->db->insert_id();
	}
	
	/**
	* Get all image folders, or folders owned by $user_id
	*
	* @param String|$orderby the method to sort the results by
	* @param Integer|$user_id 
	* @return Object| a result object of the list of folder from the database
	**/
	function get_folders($order_by='name', $user_id=FALSE){
		if ($user_id === FALSE) {
			return $this->db->order_by($order_by, 'asc')->get('asset_folder')->result_array();
		} else {
			return $this->db->where('user_id', (int) $user_id)->order_by($order_by, 'asc')->get('asset_folder')->result_array();
		}
	}

	/**
	* Deletes a folder from the database
	**/
	function delete_folder($folder_id=0){
		return $this->db->where('id', (int) $folder_id)->delete('asset_folder');	
	}
	
	/**
	* Returns number of affected rows
	**/
	function affected_rows(){
		return $this->db->affected_rows();
	}
}
?>
