<?php defined('SYSPATH') or die('No direct script access.');

class Model_Album_Image extends Kohana_Model_Image{
    protected $_context = 'album';
    protected $_table = 'album/image';
    protected $_thumbs = array(900 => 'original', 300 => 'crop');
    
    public function __construct() {
        parent::__construct();
        
     
        $this->_fields['location'] = array(
            'name' => 'Local FS',
            'rules' => array(
             ),
        );        
    }

    public function remove($identifier, \Closure $access = NULL, \Closure $beforeRemove = NULL, \Closure $afterRemove = NULL) {
        return parent::remove($identifier, $access, function($image){

            if(Model::factory('Order_Image')->count(array('file' => $image['id'])) > 0){
                throw new LogicException("You cannot delete this image, because image is included in one of the orders.");
            }   

        }, $afterRemove);
    }

    public function load(array $param = array(), $pagination = FALSE, $mixin = NULL, $restriction = FALSE) {
        return parent::load($param, $pagination, function($query, &$param) use($mixin){

            if(null != $mixin){
                $mixin($query, $param);
            }


            if(isset($param['userlevel']) and is_numeric($param['userlevel'])){

                 // uav 
                $query
                     ->join(array('album', 'ai'))
                        ->on('ai.id', '=', 'root.album')    
                    ->join(array('album/view', 'uav'), 'left')
                        ->on('ai.id', '=', DB::expr('uav.album and uav.user = '.$param['userlevel']))
                    ->where_open()    
                            ->where('uav.id', 'IS NOT', NULL)
                            ->or_where('ai.autor', '=', $param['userlevel']) 
                            ->or_where('ai.partner', '=', $param['userlevel']) 
                        ->where_close();                  

                    unset($param['userlevel']);
            }



        }, $restriction);

    }
    
    public function create(array $record)
    {

       if(TRUE != ($errors = $this->check($record))){
           return $errors;
       }
       
       unset($record['_relation']);

        $filesystem = Model::factory('Filesystem');
        require Kohana::find_file('vendors/PHPImageWorkshop', 'ImageWorkshop');
        
        try{
             
             $album = Model::factory('Album')->find(array(
                 'id' => $record['album'],
                 'extra' => 'autor-firstname,autor-lastname'
             ));
             
             $watermark_text = !empty($album['watermark'])? 
                    $album['watermark']:
                    $album['autor']['lastname'].' '.$album['autor']['firstname'];

             $tmpfile = $filesystem->tmppath($record['file']);   
             $original = PHPImageWorkshop\ImageWorkshop::initFromPath($tmpfile);

             $image = $filesystem->register(array($record['file'], $record['name']), FALSE);

             $record['file'] = $image['id'];

             $record['width'] = $original->getWidth();
             $record['direction'] = (($original->getWidth() > $original->getHeight())? 'horizontal':'vertical');

             // original image without watermark
             $identifier = DB::insert($this->getTable(), array_keys($record))->
                values(array_values($record))->
                execute(); 
             
             $record['thumb'] = $identifier[0];

             
             foreach ($this->_thumbs as $width => $format){

                $thumbnail = $record['file'].'-'.$width.'.'.$image['ext'];
                
                if('vertical' == $record['direction']){
                    $width = ceil($width / 1.5);
                }
                
                $original->resizeInPixel($width, null, true);
                $thumb = clone($original);

                if('crop' == $format){
                    $thumb->cropMaximum('pixel', 0, 0, 'MM');
                    
                } else {
                    $watermark = PHPImageWorkshop\ImageWorkshop::initTextLayer($watermark_text, DOCROOT.'../public/fonts/arial.ttf', 25, 'ffffff', 0);
                    $watermark->opacity(40);
                    $thumb->addLayer(1, $watermark, 0, 0, 'MM');
                    
                }

                
                $thumb->save($filesystem->tmppath(''), $thumbnail, true, null, 80);
                
                $image = $filesystem->register(array($thumbnail, $record['name']), FALSE);

                $record['file'] = $image['id'];
                $record['width'] = $thumb->getWidth();

               
                DB::insert($this->getTable(), array_keys($record))->
                values(array_values($record))->
                execute();

             }


             @unlink($tmpfile);

         } catch (Exception $ex) {
             Log::instance()->add(Log::ERROR, 'Cannot attach image: '.$ex->getMessage());
             return FALSE;
         }

        return  $identifier[0];
      
    }    
}