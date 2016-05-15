<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Media extends Controller {

    public function action_index(){

        $file = $this->request->param('file');

        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $file = substr($file, 0, -(strlen($ext) + 1));

        if(!strstr($file, 'vendor')){
            $folder = (!in_array($ext, array('gif', 'jpg', 'png')))? $ext:'images';
        } else {
            $folder = '';
        }

        if ($file = Kohana::find_file('media/'.$folder, $file, $ext) and FALSE !== strpos(realpath($file), 'media')){
            
            //$this->response->check_cache(sha1($this->request->uri()).filemtime($file), $this->request);
            $this->response->body(file_get_contents($file));
            $this->response->headers('content-type',  File::mime_by_ext($ext));
            $this->response->headers('last-modified', date('r', filemtime($file)));

        } else {
            $this->response->status(404);
        }
    }
}





