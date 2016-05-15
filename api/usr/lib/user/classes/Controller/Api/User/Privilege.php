<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Api_User_Privilege extends Controller_Rest
{ 
    
    public function action_read(){
        return User_Acl::get_privileges($this->request->param('id'));
    }
    
    public function action_create(){
        
        $identifier = $errors = Model::factory('User_Privilege')->create($this->query());
        
         if (is_numeric($identifier)) {

            return array(
                'id' => $identifier
            );
        } 
        
        if(is_array($errors)){

            return array(
                '_status' => array(
                  'code' => 406,
                   'msg' => 'Request is invalid' 
                ),
                'errors' => $errors
            );

        } else {
              return array(
                '_status' => array(
                  'code' => 400,
                  'msg' => 'No data, the request was ignored'
                )
            );            
        }
    }
}
