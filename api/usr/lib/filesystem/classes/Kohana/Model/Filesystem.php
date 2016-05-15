<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Model_Filesystem extends Model{

    public function __construct() {
        $this->_config = Kohana::$config->load('filesystem');

        $last = strtolower($this->_config['maxSize'][strlen($this->_config['maxSize']) - 1]);
        switch ($last) {
            case 'g': $this->_config['maxSize'] *= 1024;
            case 'm': $this->_config['maxSize'] *= 1024;
            case 'k': $this->_config['maxSize'] *= 1024;
        }
    }

    public function remove($identifier){

        if(!is_numeric($identifier) or FALSE === ($record = $this->find($identifier))){
            Log::instance()->add(Log::ERROR, 'Cannot remove file '.$identifier.'. File not found.');
            return FALSE;
        }

        if(@unlink($record['realpath'])){
            Log::instance()->add(Log::ERROR, 'Cannot remove file '.$record['realpath'].'. Permission denied.');
        }

        DB::delete($this->_table)->where('id', '=', $identifier)->execute();

        foreach (DB::select('id')->from($this->_table)->where('package', '=', $identifier)->execute() as $item){
            $this->remove($item['id']);
        }

        return TRUE;

    }

    public function find($identifier){

        $record = DB::select('id', 'ext', 'hash', 'size', 'folder')->from($this->_table)
            ->where('id', '=', (int)$identifier)
            ->execute()->current();

        if(empty($record)){
            return FALSE;
        }

        $record['realpath'] = $this->realpath($record);
        $record['filelink'] = $this->filelink($record);

        return $record;
    }

    public function realpath($file){
        return $this->config('filesystem').'/'.$file['folder'].'/'.$file['id'].'.'.$file['ext'];
    }

    public function tmppath($file){
        return DOCROOT.$this->config('tmpdir').'/'.$file;
    }

    public function filelink($file){
        return Route::url('filesystem', array()).'/'.$file['id'].'-'.$file['hash'].'.'.$file['ext'];
    }

    public function register($file, $tmpclean = TRUE){

        $realname = null;
        if(is_array($file)){
            $filename = current($file);
            $realname = end($file);

        } else {
            $filename = $file;
        }

        if(!is_string($filename) or empty($filename)){
            throw new Exception('File not found');
        }

        $filename = $this->tmppath($filename);

        if(!file_exists($filename)){
            throw new Exception('File not found');
        }

        if(FALSE === ($mime = File::mime($filename)) or FALSE == ($ext = File::exts_by_mime($mime)) or empty($ext)){
            throw new Exception('Unknown file format');
        }

        $ext = array_pop($ext);

        $folder = $this->_dir_generate(time());
        $directory = $this->config('filesystem').'/'.$folder;

        if(!file_exists($directory) and !$this->_dir_create($folder)){
            throw new Exception('Cannot create directory '.$directory);
        }

        if(!is_writeable($directory)){
            throw new Exception('Directory '.$directory.' must be writable');
        }

        $fileinfo = pathinfo($filename);

        $record = array(
            'autor'    => Auth::instance()->get_user('id'),
            'realname' => $realname,
            'mime'     => $mime,
            'folder'   => $folder,
            'ext'      => strtolower($ext),
            'hash'     => $this->hash_file($filename),
            'size'     => filesize($filename)
        );


        $identifier = DB::insert($this->_table, array_keys($record))->
            values(array_values($record))->
            execute();

        $record['id'] = $identifier = $identifier[0];

        if(!copy($filename, DOCROOT.$directory.'/'.$identifier.'.'.$record['ext'])){

            DB::delete($this->_table)
                ->where('id', '=', $identifier)
                ->execute();

            @unlink($filename);

            throw new Exception('Cannot move temporary file '.$filename);
        }


        if($tmpclean){
            @unlink($filename);
        }

        return $record;
    }

    public function hash_file($filename){

        if(!file_exists($filename)){
            return FALSE;
        }

        return md5_file($filename);
    }

    public function create_tmpfile_uri($uri){

        if(FALSE === ($stream = @file_get_contents($uri))){
            throw new Exception('URI '.$uri.' must be valid address');
            return FALSE;
        }
        return $this->create_tmpfile_stream($stream);
    }

    public function create_tmpfile_stream($stream){

        if (Request::initial()->headers('Content-Length') > $this->config('maxSize')) {
            throw new Exception('Exceeded the size of the file on the server');
        }

        $tmpdir = DOCROOT.$this->config('tmpdir');

        if(!is_writeable($tmpdir)){
            throw new Exception('Directory '.$tmpdir.' must be writable');
        }

        $filename = Text::random('hexdec', 10);

        if((FALSE === @file_put_contents($tmpdir.'/'.$filename, $stream))){
            throw new Exception('Error creating a temporary file');
        }

        if(FALSE === ($mime = File::mime($tmpdir.'/'.$filename)) or FALSE == ($ext = File::exts_by_mime($mime)) or empty($ext)){
            @unlink($tmpdir.'/'.$filename);
            throw new Exception('Unknown file format');
        }

        return array(
            'file' => $filename,
            'ext' => $ext
        );
    }

    public function expansion_is_allowed($ext){
        return in_array($ext, $this->config('allowedType'));
    }

    public function config($path = NULL){
        if(empty($path)){
            return $this->_config;
        }

        return Arr::path($this->_config, $path);
    }

    /* 
    * Contains directory path used for temporary files 
    * [!!] Do`t use system tmp folder
    */
    protected $_table = 'filesystem';
    protected $_config = array();

    protected function _dir_generate($time){
        return date('y', $time).date('W', $time);
    }

    protected function _dir_create($directory, $access = 0777){

        $path = explode('/', $directory);

        $directory = $this->config('filesystem').'/';

        foreach($path as $item){

            $directory .= $item.'/';

            if(is_dir(DOCROOT.$directory)) continue;

            if(!mkdir(DOCROOT.$directory , $access)) {
                return FALSE;
            }
        }

        return true;
    }

}