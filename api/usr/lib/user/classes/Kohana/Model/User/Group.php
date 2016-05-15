<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Model_User_Group extends Model_Db_Crud {

    protected $_table = 'user/group'; 

    protected $_fields = array(
        'active' => array(
            'name' => 'Active',
            'rules' => array(
                'in_array'  => array(':value', array(1, 0)),
             ),
        ), 
        'system' => array(
            'name' => 'System group',
            'rules' => array(
                'in_array'  => array(':value', array(1, 0)),
             ),
        ),
        'name' => array(
            'name' => 'Group name',
            'filters' => array(
                //'strtolower'  => NULL,
            ),            
            'rules' => array(
                'not_empty'     => NULL, 
            ),
        ), 
        'date' => array(
            'name' => 'Date',
            'rules' => array(
                'date'     => NULL, 
            ),            
        ),
    );

    public function remove($identifier, Closure $access = NULL, Closure $beforeRemove = NULL, Closure $afterRemove = NULL) {
        
        if(empty($identifier)){
            return FALSE;
        }
        
        if(!is_array($identifier)){
            $identifier = array('id' => $identifier);
        }
        
        $identifier['system'] = false;
     
        if(parent::remove($identifier)){
            
            Model::factory('User_Privilege')->remove(array(
                'group' => $identifier['id']
            ));
            
            return TRUE;
        }
        
        return FALSE;
    }
    
    public function load(array $param = array(), $pagination = FALSE, Closure $mixin = NULL, $restriction = FALSE) {
        
        /* include to response privileges */
        $privilege = false;
        if(isset($param['bind'])){
            if(!is_array($param['bind'])){
                $param['bind'] = explode(',', $param['bind']);
            }
            
            if(FALSE !== ($position = array_search('privilege', $param['bind']))){
                $privilege = true;
                unset($param['bind'][$position]);
            }
        }
        
        $data = parent::load($param, $pagination, $mixin, $restriction);
         
        if($pagination){
            $groups = &$data['data'];
        } else {
            $groups = &$data;
        }
         
        if(!empty($groups) and $privilege){ 
            
            foreach ($groups as &$item){
                $item['privilege'] = User_Acl::get_privileges($item['id']);
            }
        }
        
        return $data;
    }    
} 