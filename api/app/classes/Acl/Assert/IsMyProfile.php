<?php defined('SYSPATH') OR die('No direct script access.');

 
class Acl_Assert_IsMyProfile implements Acl_Assert_Interface {
	 
	public function assert(Acl $acl, $role = null, $resource = null, $privilege = null, $context = null)
	{

        $auth = Auth::instance();
        return $context['id'] == $auth->get_user('id');

        
    }
}