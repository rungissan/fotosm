<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Controller_Api_Image extends Controller_Rest_Template
{ 

    public function action_update() {
        
        $this->attach_plain_image();
        
        return parent::action_update();
    }
    
     function action_create(){
         
        $this->attach_plain_image(); 
        
        $image = $body = parent::action_create();
        
        if(!isset($image['id'])){
            return $body;
        }
        
        return $this->_model->find($image['id']);
        
    }
    

    public function attach_plain_image(){
        
        // pre attached file from default file system
        if(isset($this->_query['file']) and is_array($this->_query['file'])){
            return;
        }
        
        // if file received from POST request 
        
        // default mode
        if(!empty($_FILES)){

            if(NULL == ($tmpfile = Arr::path($_FILES, key($_FILES).'.tmp_name')) or !file_exists($tmpfile)){
                throw new HTTP_Exception_406('File not found.');
                return;
            }
            
            if(!is_readable($tmpfile)){
                throw new HTTP_Exception_403('Access denied to read tmpfile');
                return;
            }

            $stream = file_get_contents($tmpfile);

            if(FALSE === ($tmpfile = Model::factory('Filesystem')->create_tmpfile_stream($stream))){
                throw new Exception('Cannot create tmpfile');
            }
            
            
            $this->_query['file'] = array(array(
                'file' => $tmpfile['file'],
                'name' => $tmpfile['file'],
                'main' => 0
            ));
            
        // from base64 code    
            
        } else if(isset($this->_query['file']) and is_string($this->_query['file'])){
            
            $base64code = base64_decode($this->_query['file']);
            
            if(FALSE === ($tmpfile = Model::factory('Filesystem')->create_tmpfile_stream($base64code))){
                throw new Exception('Cannot create tmpfile');
            }
            
            $this->_query['file'] = $tmpfile['file'];
            
        }

        
    }    
}