<?php defined('SYSPATH') or die('No direct script access.');

class Model_Album_Users extends Model_Db {

    protected $_table = 'album/users'; 

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
                'numeric'  => NULL,
             ),
        ),
        
        'group' => array(
            'external' => 'Album_Group',
            'name' => 'Group',
            'rules' => array(
                'numeric'  => NULL,
             ),
        ),

        'rate' => array(
            'name' => 'Rate',
            'rules' => array(
                'numeric'   => NULL,
                'range'  => array(':value', 1, 99),
            ),
        ),

        'level' => array(
            'name' => 'Level',
            'rules' => array(
                'numeric'   => NULL,
                'in_array'  => array(':value', array('album', 'group')),
             ),
        ),
        
        'access' => array(
            'name' => 'access',
            'rules' => array(
                'numeric'   => NULL,
                'in_array'  => array(':value', array('read', 'manager', 'owner')),
             ),
        ),
         
        'date' => array(
            'name' => 'Registration date',
            'rules' => array(
                'date'     => NULL, 
            ),            
        ),
    );
    
    public function load(array $param = array(), $pagination = FALSE, Closure $mixin = NULL, $restriction = FALSE){

        $data = parent::load($param, $pagination , function($query, &$param) use($mixin){


            if(null != $mixin){
                $mixin($query, $param);
            }
            
            if(isset($param['or:group']) && isset($param['or:album'])){
                $query->where_open();
                    $query->where('album', (is_array($param['or:album']))? 'in':'=', $param['or:album']);
                    $query->or_where_open();
                        $query->where('root.album', 'is', null);
                        $query->where('root.group', (is_array($param['or:group']))? 'in':'=', $param['or:group']);
                    $query->where_close();
                $query->where_close();
                unset($param['or:group']);
                unset($param['or:album']);
            }
           
            
        }, $restriction);

        if($pagination){
            $access = &$data['data'];
        } else {
            $access = &$data;
        }
        
        $album = null;
        if(isset($param['album'])){
            $album = $param['album'];
        }  
        if(isset($param['or:album'])){
            $album = $param['or:album'];
        }  
        
        $order = Model::factory('Order');
        foreach($access as &$item){
            if(!empty($album) && !empty($item['user'])){
                $item['orders'] = $order->count(array(
                    'album' => $album,
                    'user' => (is_array($item['user']))? $item['user']['id']:$item['user']
                ));
            }
        }
        
        return $data;
    }
    
    
    public function remove($identifier, Closure $access = NULL, Closure $beforeRemove = NULL, Closure $afterRemove = NULL)
    {
        if(empty($identifier) || !is_string($identifier)){
            return FALSE;
        }

        $model = substr($identifier, 0, 1);
        $identifier = substr($identifier, 1);
        
        if('a' == $model){
            Model::factory('Album_Access')->remove($identifier);
        } else if('g' == $model){
            Model::factory('Album_Group_Access')->remove($identifier);
        } else {
            // ????
            return false;
        }


        if(!is_null($beforeRemove)){
            $beforeRemove();
        }



        if(!is_null($afterRemove)){
            $afterRemove();
        }


        return TRUE;
    } 
    

    /**
     * @param $record - array
     * @return errors array or id new record
     */
    public function create(array $record)
    { 
        if(isset($record['user']) and is_array($record['user'])){
            
            $user = Model::factory('User');
            
            if(!isset($record['user']['login']) || FALSE === ($profile = $user->find(array('login' => $record['user']['login'])))){
                
                $record['user']['group'] = Model_User::$CLIENT;
                $record['user']['password'] = Text::random();
                
                $userid = $errors = $user->create($record['user']);

                if (!is_numeric($userid)) {
                    return array(
                        'user' => $errors
                    );
                }
                
                $record['user'] = $userid;
                
            } else if(isset ($profile) and !empty ($profile)){
                $record['user'] = $profile['id'];
            }
                
            
        }
        
        if(isset($record['group'])){
            $prefix = 'g';
            $accessid = $errors = Model::factory('Album_Group_Access')->create($record);
            
        } else {
            $prefix = 'a';
            $accessid = $errors = Model::factory('Album_Access')->create($record);
            
        }
        
        
        if(!is_numeric($accessid)){
            return $errors;
        }
        
        
        return $prefix.$accessid;
        
    }    

}