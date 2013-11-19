<?php

class Resource_model extends CI_Model
{

	
	function select_search($settings)
	{
		
		/***
		 *
		 *	$settings = object array
		 *	- query = what are we searching for (string)
		 *	- ilter = resource type to return (string)
		 *	- language = return a specific language, defaults to English (string)
		 *	- limit = how many results? Default = 10 (int)
		 *
		 */

		if (!isset($settings->query)) return null;
		if (!isset($settings->language)) $settings->language = 'en';
		if (!isset($settings->limit)) $settings->limit = 10;

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

	function select_resource($settings)
	{

		/***
		 *
		 *	$settings = object array
		 *	- id = resource id (int)
		 *	- url = vanity_url of resource (string)
		 *	- language = return a specific language, defaults to English (string)
		 *	- limit = how many results? Default = 10 (int)
		 *
		 */

		if($settings->url != null)
		{
			$return = $this->select_resource_id($settings->url);
			$settings->id = $return->resource_id;
			$settings->language = $return->lang_code;
		}
		else if ($settings->id != null)
		{
			if (!isset($settings->language) || $settings->language == null) $settings->language = 'en';
		}
		else
		{
			return null;
		}

		if (!isset($settings->limit) || $settings->limit == null) $settings->limit = 10;
		
		$this->db->select('resource.resource_id, type, published_dt, knownas');
		$this->db->select("IFNULL(MAX(IF (lang_code = '" . mysql_real_escape_string($settings->language) . "' AND resource_translation.status = '1', title, NULL)), title) AS title", FALSE);
		$this->db->select("IFNULL(MAX(IF (lang_code = '" . mysql_real_escape_string($settings->language) . "' AND resource_translation.status = '1', body, NULL)), body) AS body", FALSE);
		$this->db->select("IFNULL(MAX(IF (lang_code = '" . mysql_real_escape_string($settings->language) . "' AND resource_translation.status = '1', vanity_url, NULL)), vanity_url) AS vanity_url", FALSE);
		
		$this->db->select("(SELECT GROUP_CONCAT(lang_code) translations FROM resource_translation WHERE resource_id = $settings->id) AS translations", false);

		$this->db->from('resource');
		$this->db->join('resource_translation', 'resource.resource_id = resource_translation.resource_id');
		$this->db->join('individual', 'resource.member_id = individual.member_id');

		$this->db->where('resource.resource_id', $settings->id);

		$query = $this->db->get();
		//echo $this->db->last_query(); exit;

		if ($query->num_rows() > 0 && $query->row()->resource_id != null)
		{
			
			foreach ($query->result() as $row)
			{
				$data[] = $row;
			}

			return $data;
		}

	}


	function select_resource_id($vanity_url)
	{
		
		/***
		 *
		 *	Takes a vanity url and returns a resource_id and lang_code
		 *
		 */

		$this->db->select('resource.resource_id, lang_code');
		$this->db->from('resource');
		$this->db->join('resource_translation', 'resource.resource_id = resource_translation.resource_id');
		$this->db->where('vanity_url', $vanity_url);

		$query = $this->db->get();

		if ($query->num_rows() > 0) return $query->row();
	}
	
}
