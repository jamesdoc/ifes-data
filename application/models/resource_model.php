<?php

class Resource_model extends CI_Model
{

	
	function select_search($settings)
	{
		
		/***
		 *
		 *	$settings = object array
		 *	query = what are we searching for (string)
		 *	filter = resource type to return (string)
		 *	language = return a specific language, defaults to English (string)
		 *	limit = how many results? Default = 10 (int)
		 *
		 */

		if (!isset($settings->query))
		{
			return null;
		}

		if (!isset($settings->language))
		{
			$settings->language = 'en';
		}

		if (!isset($settings->limit))
		{
			$settings->limit = 10;
		}


		$query = "
			SELECT resource.resource_id, type, title, IF(vanity_url IS NOT NULL, vanity_url, resource.resource_id) as vanity_url, published_dt
			FROM resource
			JOIN resource_translation on resource.resource_id = resource_translation.resource_id
			JOIN tag_link ON tag_link.page_id = resource.resource_id
		
			LEFT JOIN locale ON locale.locale_code = tag_link.tag_id
			LEFT JOIN tag_translation ON tag_translation.tag_id = tag_link.tag_id
			
			WHERE lang_code = '" . mysql_real_escape_string($settings->language) ."' AND (locale_en LIKE '%" . mysql_real_escape_string($settings->query) . "%' OR tag_translation.tag_title LIKE '%" . mysql_real_escape_string($settings->query) . "%' OR title LIKE '%" . mysql_real_escape_string($settings->query) . "%') AND resource.status = 1
		";

		if (isset($settings->filter) && $settings->filter != null)
		{
			$query .= "AND type = '" . $settings->filter . "'";
		}

		$query .= "
			AND type != 'prayer'
			GROUP BY resource_id
			ORDER BY FIELD(type, 'profile','blog','event','video','audio') ASC, published_dt DESC
			LIMIT $settings->limit
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
