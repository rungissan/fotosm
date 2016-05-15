<?php defined('SYSPATH') or die('No direct script access.');

class Model_Order extends Model_Db_Crud {

    protected $_table = 'order';
    protected $_relations = array(
        'image' => array(
            'model' => 'Order_Image',
            'attrs' => array(
                'except' => 'album'
            ),
        ),         
    );

    protected $_fields = array(
        
        'album' => array(
            'name' => 'Album',
            'external' => 'Album',
            'rules' => array(
                'not_empty'  => NULL,
                'numeric'  => NULL,
             ),
        ),
        
        'comment' => array(
            'name' => 'Comment',
            'rules' => array(
                'max_length' => array(':value', 500),
             ),
        ),
        
        
        'user' => array(
            'name' => 'Client',
            'external' => 'User',
            'default' => array(
                'Model_Db_Default_User::data' => array('id')
            ),
            'rules' => array(
                'not_empty'  => NULL,
                'numeric'  => NULL,
             ),
        ),
        
            'date' => array(
            'name' => 'Registration date',
            'rules' => array(
                'date'  => NULL,
             ),
        ),
    );
    
       

    public function load(array $param = array(), $pagination = FALSE, Closure $mixin = NULL, $restriction = FALSE) {
        
        /* include to response metadata */
        $meta = false; 
        if(isset($param['bind'])){
            if(!is_array($param['bind'])){
                $param['bind'] = explode(',', $param['bind']);
            }
            
            if(FALSE !== ($position = array_search('meta', $param['bind']))){
                $meta = true;
                unset($param['bind'][$position]);
            } 
        }
        
        $data = parent::load($param, $pagination, function($query, &$param) use($mixin){
            
            if(null != $mixin){
                $mixin($query, $param);
            }
           
        }, $restriction);

        if($pagination){
            $orders = &$data['data'];
        } else {
            $orders = &$data;
        }
        
        if(!empty($orders) and $meta){
            $albumImage = Model::factory('Album_Image');
            $orderImage = Model::factory('Order_Image');
            foreach ($orders as &$item){
                $item['meta'] = array();
                $item['meta']['inorder'] = $orderImage->count(array(
                    'order' => $item['id']
                ));

                if(isset($item['album'])){

                    $item['meta']['total'] = $albumImage->count(array(
                        'album' => (is_array($item['album']))? $item['album']['id']:$item['album']
                    ));

                }

                $item['sum'] = 0;                
            }
        }

        if(!empty($orders) and isset($orders[0]['album']) and is_array($orders[0]['album'])){
             
            $user = Model::factory('User');
            
            foreach ($orders as &$item){
                if(isset($item['album']['autor'])){
                    $item['album']['autor'] = $user->find($item['album']['autor'], 'firstname,lastname');
                }
                
                
            }
        }
        
        return $data;
    }
    
   
   
    
  
    
} 