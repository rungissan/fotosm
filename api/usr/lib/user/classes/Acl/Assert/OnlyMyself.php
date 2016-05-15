<?php defined('SYSPATH') OR die('No direct script access.');

 
class Acl_Assert_OnlyMyself implements Acl_Assert_Interface {
	 
	public function assert(Acl $acl, $role = null, $resource = null, $privilege = null, $context = null)
	{ 
            return isset($context['id']) and $context['id'] == Auth::instance()->get_user('id');
	}
}