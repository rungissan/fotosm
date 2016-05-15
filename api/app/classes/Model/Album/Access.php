<?php defined('SYSPATH') or die('No direct script access.');

class Model_Album_Access extends Model_Db_Crud {

    protected $_table = 'album/access'; 

    protected $_fields = array(
        
        'user' => array(
            'external' => 'User',
            'name' => 'User',
            'rules' => array(
                'not_empty'  => NULL,
                'numeric'  => NULL,
                'Model_Db_Callback_Unique::execute'  => array(array('user', 'album'), ':value', ':validation', ':context'),
             ),
        ),
        
        'album' => array(
            'external' => 'Album',
            'name' => 'Album',
            'rules' => array(
                'not_empty'  => NULL,
                'numeric'  => NULL,
             ),
        ),
         
        'date' => array(
            'name' => 'Registration date',
            'rules' => array(
                'date'     => NULL, 
            ),            
        ),
    );
    

}