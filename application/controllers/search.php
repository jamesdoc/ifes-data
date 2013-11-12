<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Example
 *
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array.
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 * @author		Phil Sturgeon
 * @link		http://philsturgeon.co.uk/code/
*/

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class Search extends REST_Controller
{
	public function _remap($method, $params = array())
    {

        if (method_exists($this, $method))
        {
            return call_user_func_array(array($this, $method), $params);
        }
        else
        {
            $this->for_get($method);
        }

    }

    function for_get()
    {

        if (!$this->get('query')) { $this->response(NULL, 400); }
        
        $search_config = (object) array(
            'query'     => $this->get('query'),
            'limit'     => 10
            );

        if($this->get('lang') != null)
        {
            $search_config->language = $this->get('lang');
        }

        if($this->get('filter') != null)
        {
            $search_config->filter = $this->get('filter');
        }

        $this->load->model('resource_model');
        
        $search = $this->resource_model->select_search($search_config);
    	
        if($search)
        {
            $this->response($search, 200); // 200 being the HTTP response code
        }

        else
        {
            $this->response(array('error' => 'No results'), 404);
        }
    }
}