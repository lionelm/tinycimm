<?php
class Tinycimm_model extends Model {

	function Tinycimm_model() {
		parent::Model();
	}

	function get_image($image_id) {
		$sql = 'SELECT *
                        FROM images
                        WHERE id = ?
                        LIMIT 1';
                $query = $this->db->query($sql, array($image_id));
		return $query->row_array();
	}

	function delete_image($image_id){
		$sql = 'DELETE
			FROM images
			WHERE id = ?
			LIMIT 1';
        	return $this->db->query($sql, array($image_id));
	}

	function get_image_folders($orderby='caption'){
		$sql = 'SELECT *
			FROM image_folders
			ORDER BY ? ASC';
		return $this->db->query($sql, array($orderby))->result_array();
	}
}
?>
