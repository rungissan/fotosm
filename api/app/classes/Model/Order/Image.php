<?php defined('SYSPATH') or die('No direct script access.');

class Model_Order_Image extends Model_Db_Crud{

    protected $_table = 'order/image';

    protected $_fields = array(

        'order' => array(
            'name' => 'Order',
            'external' => 'Order',
            'rules' => array(
                'not_empty'  => NULL,
                'numeric'  => NULL,
            ),
        ),
        'album' => array(
            'name' => 'Album',
            'external' => 'Album',
            'rules' => array(
                'numeric'  => NULL,
            ),
        ),
        'file' => array(
            'name' => 'Файл',
            'rules' => array(
                'not_empty'  => NULL,
                'numeric'  => NULL,
                'Model_Db_Callback_Unique::execute'  => array(array('file', 'order'), ':value', ':validation', ':context'),
             ),
        ),        
        'size' => array(
            'name' => 'Image size',
            'rules' => array(
                'in_array'  => array(':value', array('10х15','13х18','15х20','20х30', '9х13','11.4х15','15х21','15х22','15х23', '15х45','18х24','21х30', '23х30','30х30','30х40','30х45', '30х60','30х90'))
            ),
        ),
        'type' => array(
            'name' => 'Image type',
            'rules' => array(
                'in_array'  => array(':value', array('matt','gloss', 'silk', 'satin', 'metallic')),
             ),
        ),
        'count' => array(
            'name' => 'Amount',
            'default' => 1,
            'rules' => array(
                'not_empty' => NULL,
                'numeric'  => NULL,
                'range' => array(':value', 1, 999)
             ),
        ), 
    );
    
    public function create(array $record) {


        $order = null;
        if(isset($record['order'])){

            $order = Model::factory('Order')->find($record['order']);

            if(FALSE === $order){
                throw new LogicException("Unknown order.");
            }

            if( 'completed' == $order['status']){
                return array('order' => 'You cannot add image, this order has been completed.');
            }

            $record['album'] = $order['album'];
        }

        $identifier = $errors = parent::create($record);
        
        if(!is_numeric($identifier)){
            return $errors;
        }
        
        // recalculate the price ...
        Model::factory('Order')->update(array('price' => NULL), $order['id']);
        
        return $identifier;
    }

    public function update(array $record, $identifier) {
        
        $image = $this->find(array(
            'id' => $identifier,
            'extra' => 'order-status'
        ));
        
        if(false !== $image && 'completed' == $image['order']['order']['status']){
            throw new LogicException("You cannot change image data, this order has been completed.");
        }

        unset($record['album']);

        $identifier = $errors = parent::update($record, $identifier);
        
        if(!is_numeric($identifier)){
            return $errors;
        }
        
        // recalculate the price ...
        Model::factory('Order')->update(array('price' => NULL), $image['order']['order']['id']);
        
        return $identifier;

    }
    
    public function load(array $param = array(), $pagination = FALSE, $mixin = NULL, $restriction = FALSE) {
        $data = parent::load($param, $pagination, function($query, &$param) use($mixin){
            
            if(null != $mixin){
                $mixin($query, $param);
            }
           
//            if(isset($param['userlevel']) and is_numeric($param['userlevel'])){
//                
//                 // uav
//                 $query
//                    ->join(array('order', 'oi'))
//                    ->on('oi.id', '=', 'root.order')
//                    ->join(array('album', 'ai'))
//                    ->on('ai.id', '=', 'oi.album')
//                    ->where_open()    
//                            ->where('oi.user', '=', $param['userlevel'])
//                            ->or_where('ai.autor', '=', $param['userlevel']) 
//                            ->or_where('ai.partner', '=', $param['userlevel']) 
//                        ->where_close(); 
//                    
//                    unset($param['userlevel']);
//            }
        }, $restriction);

        if($pagination){
            $images = &$data['data'];
        } else {
            $images = &$data;
        }

        if(!empty($images) and isset($images[0]['file'])){
            foreach(Model::factory('Album_Image')->load(array('file' => Arr::path($images, '*.file'))) as $image){

                $imageNotFound = true;
                foreach($images as &$meta){
                    if($meta['file'] == $image['file']){
                        $image['order'] = $meta;
                        $meta = $image;
                        $imageNotFound = false;
                        break;

                    }
                }

                if($imageNotFound){
                    $meta = array(
                        'order' => $meta
                    );
                }
            }
        }

        return $data;
    }
    
    public function remove($identifier, Closure $access = NULL, Closure $beforeRemove = NULL, Closure $afterRemove = NULL)
    {
        if(empty($identifier)){
            return FALSE;
        }

        if(is_numeric($identifier)){
            $args = array('id' => $identifier);
        } else {
            $args = $identifier;
        }

        $records = $this->load($args);

        if(empty($records)){
            return FALSE;
        }
        
        $order = Model::factory('Order');

        foreach ($records as $record){ 

            $fileid = (!is_array($record['order']))? $record['id']:$record['order']['id']; 
            $orderid = (!is_array($record['order']))? $record['order']:$record['order']['order']; 
            
            if($order->exists(array('id' => $orderid, 'status' => 'completed'))){
                 throw new LogicException("You cannot delete image, this order has been completed.");
            }
            

            DB::delete($this->getTable())->where('id', '=', $fileid)->execute();
            
            // recalculate the price ...
            $order->update(array('price' => NULL), $orderid);

        }

        return TRUE;
    }

}