<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Filesystem extends Controller {

    public function action_index() {

        $identifier = $this->request->param('file');
        $identifier = explode('-', $identifier);

        $ext = $this->request->param('ext');

        $filesystem = Model::factory('Filesystem');

        if(2 != count($identifier) or FALSE === ($file = $filesystem->find($identifier[0])) or !file_exists($file['realpath']) or $ext != $file['ext'] or $identifier[1] != $file['hash']){
            throw new HTTP_Exception_404('File not found');
        }

        if(!is_readable($file['realpath'])){
            throw new HTTP_Exception_403('Access denied');
        }
        
        $this->response->headers('content-type',  File::mime_by_ext($file['ext']));
        $this->response->headers('cache-control',  'private, max-age = 2592000');
        
//        if(in_array($file['ext'], array('jpg', 'png')) && FALSE !== ($image = Model::factory('Album_Image')->find(array(
//                'or:file' => $file['id'],
//                'or:thumb' => $file['id'],
//                'extra' => 'album-watermark,album-name,album-modified'
//        )))){
//        
//        $this->response->headers('last-modified', date('r', strtotime($image['album']['modified'])));
//        }
        
        $this->response->headers('last-modified', date('r', filemtime($file['realpath'])));
        $this->response->body(file_get_contents($file['realpath']));
        
    }
}