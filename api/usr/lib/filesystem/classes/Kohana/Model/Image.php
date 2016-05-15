<?php defined('SYSPATH') or die('No direct script access.');

abstract class Kohana_Model_Image extends Model_Db_Crud {

    protected $_context;
    
    protected $_thumbs = array(500);
    
    protected $_fields = array(
        
        'file' => array(
            'name' => 'File',
            'rules' => array(
                'not_empty'  => NULL,
                'Kohana_Model_Image::check_file'  => array(':value', ':validation'),
             ),
        ),
        'name' => array(
            'name' => 'Filename',
            'rules' => array(
                'not_empty'  => NULL,
            ),
        ),
        'width' => array(
            'name' => 'Width',
            'rules' => array(
                'numeric'  => NULL,
             ),
        ),  
        'direction' => array(
            'name' => 'Type',
            'rules' => array(
                'in_array'  => array(':value', array('horizontal', 'vertical')),
             ),
        ),
        'main' => array(
            'name' => 'Main',
            'default' => 0,
            'rules' => array(
                'numeric'   => NULL,
                'in_array'  => array(':value', array(1, 0)),
            ),
        ), 
        'thumb' => array(
            'name' => 'Thumb',
            'rules' => array(
                'numeric'   => NULL,
            ),
        )        
    );
    
   static function check_file($file, Validation $validation){
        
        $filesystem = Model::factory('Filesystem');
        $file = str_replace(array('.','/','\\'), '', $file);
        
        $tmpfile = $filesystem->tmppath($file);
        
        if(!file_exists($tmpfile)){
             $validation->error('file', 'image_not_found', array($file));
             return;
        }
        
        try{
        $original = Image::factory($tmpfile);
        $original->resize($original->width - 1);
        } catch (Exception $ex){
            $validation->error('file', 'image_is_incorrect', array($file));
        }
    }

   public function __construct() {
        parent::__construct();
        
        $this->_fields[$this->_context] = array(
            'external' => ucfirst($this->_context),
            'name' => 'Relation entity',
            'rules' => array(
                'not_empty'  => NULL,
                'numeric'  => NULL,
             ),
        );
    }
    
   public function count(array $param = array(), $restriction = FALSE) {
        $param['only'] = 'id';
        
        if(!isset($param['thumb']) && !isset($param['or:thumb'])){
            $param['thumb'] = NULL;
        }
        
        $record = parent::load($param, false, function($query){
            $query->select(DB::expr('COUNT(*) as count'));
        }, $restriction);

        return $record[0]['count'];
   }
    
   public function load(array $param = array(), $pagination = FALSE, Closure $mixin = NULL, $restriction = FALSE) {
        
        if(!isset($param['thumb']) && !isset($param['or:thumb'])){
            $param['thumb'] = NULL;
        }

        unset($param['turn']);
        
        $joinFS = function($query, &$param) use ($mixin){
           
            if(null != $mixin){
                $mixin($query, $param);
            }
            
           $query->
                join(array('filesystem', 'fs'))->
                on('root.file', '=', 'fs.id');
            
           $filesystem = Model::factory('Filesystem');
           
           $query->select(
                    DB::expr("CONCAT(fs.id, '-', fs.hash ,'.',fs.ext) as filename"),
                    DB::expr("CONCAT('".URL::site(Route::url('filesystem', array()), 'http')."/', fs.id, '-', fs.hash ,'.',fs.ext) as filelink"),
                    DB::expr("CONCAT('".$filesystem->config('basepath')."/',fs.folder, '/', fs.id ,'.',fs.ext) as realpath"),
                   'fs.folder', 
                   'fs.realname', 
                   'fs.ext'
                   );

        };

       $data = parent::load($param, $pagination, $joinFS, $restriction);
       // print Database::instance()->last_query; exit;
        if($pagination){
            $images = &$data['data'];
        } else {
            $images = &$data;
        }

        foreach ($images as &$image){
             $image['thumb'] = parent::load(array('thumb' => $image['id'], 'turn' => 'width'), FALSE, $joinFS);
        }

        return $data;
    }
    
   public function remove($identifier, Closure $access = NULL, Closure $beforeRemove = NULL, Closure $afterRemove = NULL){
        
        if(empty($identifier)){
            return FALSE;
        }
        
        if(is_numeric($identifier)){
            $identifier = array('id' => $identifier);
        }
        
        $identifier['thumb'] = NULL;
        
        $records = $this->load($identifier);
        
        if(empty($records)){
            return FALSE;
        } 
        $filesystem = Model::factory('Filesystem');

        
        foreach ($records as $file){
            
            if(!is_null($beforeRemove)){
                $beforeRemove($file);
            }

            $filesystem->remove($file['file']);

            foreach (DB::select('file')->from($this->_table)->where('thumb','=',$file['id'])->execute()->as_array() as $item){
                $filesystem->remove($item['file']);
            }
            
            DB::delete($this->getTable())->where('thumb', '=', $file['id'])->execute();  
            
            if(!is_null($afterRemove)){
                $afterRemove($file);
            }
            
        }
        
        DB::delete($this->getTable())->where('id', 'in', Arr::path($records, '*.id'))->execute();

        
        return TRUE;
    }

   public function update(array $record, $identifier) {
       return parent::update(Arr::extract($record, array('alt', 'thumb')), $identifier);
   }
   
   public function create(array $record)
   {       
       if(TRUE != ($errors = $this->check($record))){
           return $errors;
       }
       
       unset($record['_relation']);

        $filesystem = Model::factory('Filesystem');
        
        try{

             $tmpfile = $filesystem->tmppath($record['file']);   
             $original = Image::factory($tmpfile);

             $image = $filesystem->register(array($record['file'], $record['name']), FALSE);

             $record['file'] = $image['id'];

             $record['width'] = $original->width;
             $record['direction'] = (($original->width > $original->height)? 'horizontal':'vertical');
             
             $identifier = DB::insert($this->getTable(), array_keys($record))->
                values(array_values($record))->
                execute(); 
             
             $record['thumb'] = $identifier[0];

             
             foreach ($this->_thumbs as $width){

                $thumbnail = Text::random('hexdec', 10);

                 if('x' == $width){
                     $width = $original->width;
                 }
                 if('x/2' == $width){
                     $width = ceil($original->width/2);
                 }

                 $original
                  ->resize($width, $width, Image::AUTO)
                  ->save($filesystem->tmppath($thumbnail), 80);

                $image = $filesystem->register(array($thumbnail, $record['name']), FALSE);

                $record['file'] = $image['id'];
                $record['width'] = $original->width;

               
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