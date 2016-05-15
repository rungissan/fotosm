<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Model_User extends Model_Db_Crud {

    protected $_table = 'user';


    protected $_fields = array(
        
        'firstname' => array(
            'name' => 'Firstname',
            'filters' => array(
                'UTF8::ucfirst'  => NULL,
            ),            
            'rules' => array(
                'not_empty'  => NULL
             ),
        ),
        'lastname' => array(
            'filters' => array(
                'UTF8::ucfirst'  => NULL,
            ),
            'name' => 'Lastname',
        ),
        'login' => array(
            'name' => 'Login',
            'rules' => array(
                'email'     => NULL,
                'not_empty'     => NULL,
                'Model_Db_Callback_Unique::execute'  => array('login', ':value', ':validation', ':context'),
            ),
        ),
        'token'  => array(
            'name' => 'Token',
            'rules' => array(
                //'not_empty'  => NULL,
                'max_length' => array(':value', 100),
            ),
        ),
        'password' => array(
            'name' => 'Password',
            'filters' => array(
                'Model_User::hash_password'  => NULL,
            ),
            'rules' => array(
                'not_empty'  => NULL,
                'min_length' => array(':value', 4),
            ),
        ),

        'group' => array(
            'external' => 'User_Group',
            'name' => 'User group',
            'rules' => array(
                'not_empty' => NULL,
                'numeric'   => NULL,
             ),
        ),

        'active' => array(
            'name' => 'Active',
            'rules' => array(
                'numeric'   => NULL,
                'in_array'  => array(':value', array(1, 0)),
             ),
        ),
        'date' => array(
            'name' => 'Registration date',
            'rules' => array(
                'date'     => NULL, 
            ),            
        ),
        
        'phone' => array(
            'name' => 'Phone',
            'rules' => array( 
            ),            
        ),
        
        'skype' => array(
            'name' => 'Skype',
            'rules' => array( 
            ),            
        ),

        'activity' => array(
            'name' => 'Last activity',
            'rules' => array(
                'date'     => NULL, 
            ),            
        ),
    );


    public static function hash_password($password)
    {
        if(empty($password)){
            return;
        }
        
        return Auth::instance()->hash_password($password);
    }
    
  public function load(array $param = array(), $pagination = FALSE, \Closure $mixin = NULL, $restriction = FALSE) {
        
        if(isset($param['name']) and is_string($param['name'])){
            $param['or:firstname'] = 'like:'.$param['name'];
            $param['or:lastname'] = 'like:'.$param['name'];
            unset($param['name']);
        }
        return parent::load($param, $pagination, $mixin, $restriction);
    }    
} 