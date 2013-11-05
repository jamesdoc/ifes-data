<?php

class Resource_model extends CI_Model
{

	
	function select_search($lang_code = 'en', $query, $filter = null, $limit = 10)
	{
		$query = "
			SELECT resource.resource_id, type, title, IF(vanity_url IS NOT NULL, vanity_url, resource.resource_id) as vanity_url, published_dt
			FROM resource
			JOIN resource_translation on resource.resource_id = resource_translation.resource_id
			WHERE title LIKE '%" . mysql_real_escape_string($query) . "%' and lang_code = '" . mysql_real_escape_string($lang_code) ."' AND resource.status = 1
			ORDER BY FIELD(type, 'profile','blog','event','video','audio'), published_dt DESC
			LIMIT $limit
		";


		$query = $this->db->query($query);

		if ($query->num_rows() > 0)
		{
			
			foreach ($query->result() as $row)
			{
				$data[] = $row;
			}

			return $data;
		}
	}
	
}
