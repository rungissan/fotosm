<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Api_Stream extends Controller
{
   
    
    public function action_index(){
       
        $filename = str_replace(array('.', '/'), '__wtf__', $this->request->query('id'));
     
        if(empty($filename)){
            throw new HTTP_Exception_404('Tmpfile not found');
        }
        
        $filesystem = Model::factory('Filesystem');
        
        $filename = $filesystem->config('tmpdir').'/'.$filename; 
        
        if(!file_exists($filename)){
            throw new HTTP_Exception_404('Tmpfile not found');
        }
        
        //$this->response->check_cache(sha1($this->request->uri()).filemtime($filename), $this->request);
        $this->response->body(file_get_contents($filename));
        $this->response->headers('content-type',  File::mime_by_ext($filename));
        $this->response->headers('last-modified', date('r', filemtime($filename)));
             
    }
}