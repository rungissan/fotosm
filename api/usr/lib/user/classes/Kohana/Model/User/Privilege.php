<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Model_User_Privilege extends Model_Db_Crud {

    protected $_table = 'user/privilege';

    protected $_fields = array(
        'allow' => array(
            'name' => 'Доступ',
            'rules' => array(
                'not_empty'  => NULL,
                'in_array'  => array(':value', array(1, 0)),
             ),
        ), 
        'group' => array(
            'name' => 'Группа',
            'external' => 'User_Group',
            'rules' => array(
                'not_empty'  => NULL,
                'numeric'  => NULL,
             ),
        ),  
        'resource' => array(
            'name' => 'Ресурс',
            'rules' => array(
                'not_empty'  => NULL,
             ),
        ),  
        'privilege' => array(
            'name' => 'Привилегия',
        ),  
        'assert' => array(
            'name' => 'Утверждение',
        ), 
    );
    
    public function create(array $record) {
        if(TRUE !== ($errors = $this->check($record))){
            return $errors;
        }
        
        $this->remove(Arr::extract($record, array('group','resource','privilege', 'assert')));
        
        return parent::create($record);
    }
    
    public function update(array $record, $identifier)
    {
        throw new LogicException('Method not allowed');
    }

} 