<?php defined('SYSPATH') or die('No direct script access.');


class Kohana_Controller_Api_User extends Controller_Rest_Template
{ 
    protected $_model = 'User';
    
    function action_item($identifier = null, array $args = array()) 
    {
        $args['except'] = 'password,token';
        return parent::action_item($identifier, $args);
    }
    
    function action_read(array $args = array()) 
    {
        $args['except'] = 'password,token';
        return parent::action_read($args);
    }

}