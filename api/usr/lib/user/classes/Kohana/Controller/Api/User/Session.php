<?php defined('SYSPATH') or die('No direct script access.');
 
class Kohana_Controller_Api_User_Session extends Controller_Rest
{ 
    protected $_model = 'User_Session';

    function action_read(array $args = array())
    {
        foreach($this->query() as $key => $value){
            if(false !== strpos($key, 'or:')){
                throw new HTTP_Exception_403('Method OR not allowed');
            }
        }
        
        return Model::factory('User_Session')->load($this->query(), TRUE);
    }
}