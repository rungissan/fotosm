<?php defined('SYSPATH') OR die('No direct access allowed.');

class User_Acl{
    
    static function get_privileges($roleid = NULL){
        
        $resources = array();
        
        $privileges = (empty($roleid))? array():Model::factory('User_Privilege')->load(array(
            'group' => $roleid,
            'except' => 'group'
        ));
        
        
        $findPrivilege = function($resource, $privilege = NULL, $assert = NULL) use($privileges) {
            foreach ($privileges as $item){
                if(
                        $resource == $item['resource'] and 
                        $privilege == $item['privilege'] and 
                        $assert == $item['assert']
                        ){
                    return (bool)$item['allow'];
                }
            }
        };
        
        $createRule = function(array $config, &$item, $context) use($findPrivilege){
           
            $item = array();

            if(isset($config['predefined'])){
                $item['allow'] = $config['predefined'];
            } else {
               $item['allow'] = call_user_func_array($findPrivilege, $context);
                
                if(NULL === $item['allow']){
                    $item['allow'] = (isset($config['default']))? $config['default']:false;
                }
            }

            if(isset($config['assert']) and !empty($config['assert'])){
                $item['assert'] = array();
                foreach ($config['assert'] as $aName => $aConfig){
                  
                    if(isset($aConfig['predefined'])){
                        $item['assert'][$aName] = $aConfig['predefined'];
                    } else {
                       $item['assert'][$aName] = call_user_func_array($findPrivilege, Arr::merge($context, array($aName)));

                        if(NULL === $item['assert'][$aName]){
                            $item['assert'][$aName] = (isset($aConfig['default']))? $aConfig['default']:false;
                        }
                    }    
                     

                }
            } else {
               $item['assert'] = NULL;
            }
        };
        
        foreach (Kohana::$config->load('acl') as $name => $resource){
            
            $resources[$name] = array();
            $createRule($resource, $resources[$name], array($name, NULL));
            
            if(isset($resource['privilege']) and !empty($resource['privilege'])){
                $resources[$name]['privilege'] = array();
                foreach($resource['privilege'] as $privilege => $config){
                    $resources[$name]['privilege'][$privilege] = array();
                    $createRule($config, $resources[$name]['privilege'][$privilege], array($name, $privilege));
                }
                
                
            } else {
                $resources[$name]['privilege'] = null;
            }
            
        }
        return $resources;        
    }
}

