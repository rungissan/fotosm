<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Controller_Api_Filesystem extends Controller_Rest
{ 
    protected $_action_map = array
    (
        HTTP_Request::GET       => 'read',
        HTTP_Request::POST      => 'create'
    );
     
    function before() 
    {
        parent::before();

        $this->_filesystem = Model::factory('Filesystem');
        
    }

    public function action_read(){
        
        $identifier = $this->request->param('id');

        if (FALSE === ($record = $this->_filesystem->find($identifier))) {
            throw new HTTP_Exception_404('File not found');
        }

        return $record;
    }
    
    public function action_create(){

        if(NULL === ($stream = $this->query('stream'))){

            if(NULL == ($tmpfile = Arr::path($_FILES, key($_FILES).'.tmp_name')) or !file_exists($tmpfile)){
                throw new HTTP_Exception_404('File not found.');
                return;
            }

            if(!is_readable($tmpfile)){
                throw new HTTP_Exception_403('Access denied to read tmpfile');
                return;
            }

            $stream = file_get_contents($tmpfile);

        } else {

            $stream = base64_decode($stream);
        }

         
        if(FALSE === ($tmpfile = $this->_filesystem->create_tmpfile_stream($stream))){
            throw new Exception('Cannot create tmpfile');
        }
        
        return Arr::merge($tmpfile, array('isUploaded' => true));
    }
   
}