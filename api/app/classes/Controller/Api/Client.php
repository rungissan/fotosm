<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Api_Client extends Controller_Rest_Template {

    protected $_action_map = array
    (
        HTTP_Request::GET       => 'read',
    );

    protected $_model = 'Client';


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