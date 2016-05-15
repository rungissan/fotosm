<?php defined('SYSPATH') or die('No direct script access.');

abstract class Kohana_Model_File extends Model_Db_Crud {

    protected $_context;


    protected $_fields = array(

        'file' => array(
            'name' => 'File',
            'rules' => array(
                'not_empty'  => NULL,
                'Kohana_Model_File::check_file'  => array(':value', ':validation'),
            ),
        ),
        'name' => array(
            'name' => 'Filename',
            'rules' => array(
                'not_empty'  => NULL,
            ),
        ),
    );

    static function check_file($file, Validation $validation){

        $filesystem = Model::factory('Filesystem');
        $file = str_replace(array('.','/','\\'), '', $file);

        $tmpfile = $filesystem->tmppath($file);

        if(!file_exists($tmpfile)){
            $validation->error('file', 'file_not_found', array($file));
            return;
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
        $param['thumb'] = NULL;

        $record = parent::load($param, false, function($query){
            $query->select(DB::expr('COUNT(*) as count'));
        }, $restriction);

        return $record[0]['count'];
    }

    public function load(array $param = array(), $pagination = FALSE, Closure $mixin = NULL, $restriction = FALSE) {


        $joinFS = function($query){

            $query->
                join(array('filesystem', 'fs'))->
                on('root.file', '=', 'fs.id');

            $filesystem = Model::factory('Filesystem');

            $query->select(
                DB::expr("CONCAT('".Route::url('filesystem', array())."/', fs.id, '-', fs.hash ,'.',fs.ext) as filelink"),
                DB::expr("CONCAT('".$filesystem->config('basepath')."/',fs.folder, '/', fs.id ,'.',fs.ext) as realpath"),
                'fs.folder',
                'fs.ext'
            );

        };

        return   parent::load($param, $pagination, $joinFS, $restriction);
    }

    public function remove($identifier, Closure $access = NULL, Closure $beforeRemove = NULL, Closure $afterRemove = NULL){

        if(empty($identifier)){
            return FALSE;
        }

        if(is_numeric($identifier)){
            $identifier = array('id' => $identifier);
        }


        $records = $this->load($identifier);

        if(empty($records)){
            return FALSE;
        }
        $filesystem = Model::factory('Filesystem');


        foreach ($records as $file){

            $filesystem->remove($file['file']);
        }

        DB::delete($this->getTable())->where('id', 'in', Arr::path($records, '*.id'))->execute();


        return TRUE;
    }


    public function update(array $record, $identifier) {
        return parent::update(Arr::extract($record, array('alt', 'thumb')), $identifier);
    }

    public function create(array $record)
    {
        unset($record['_relation']);

        $filesystem = Model::factory('Filesystem');

        try{

            $file = $filesystem->register(array(
                $record['file'],
                $record['name']
            ), TRUE);

            $record['file'] = $file['id'];

            $identifier = DB::insert($this->getTable(), array_keys($record))->
                values(array_values($record))->
                execute();


        } catch (Exception $ex) {
            Log::instance()->add(Log::ERROR, 'Cannot attach file: '.$ex->getMessage());
            return FALSE;
        }

        return  $identifier[0];

    }
}