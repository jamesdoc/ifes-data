<?php defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class Resource extends REST_Controller
{
	public function _remap($method, $params = array())
    {

        if (method_exists($this, $method))
        {
            return call_user_func_array(array($this, $method), $params);
        }
        else
        {   
            $this->select_get($method);
        }

    }

    function select_get()
    {

        if (!$this->get('id') && !$this->get('url')) { $this->response(NULL, 400); }
        
        $search_config = (object) array(
            'id'        => $this->get('id'),
            'url'       => $this->get('url'),
            'language'  => $this->get('lang'),
            'limit'     => 10
            );

        $this->load->model('resource_model');
        
        $resource = $this->resource_model->select_resource($search_config);
    	
        if($resource)
        {
            $this->response($resource, 200); // 200 being the HTTP response code
        }

        else
        {
            $this->response(array('error' => 'No results'), 404);
        }
    }
}