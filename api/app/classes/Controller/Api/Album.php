<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Api_Album extends Controller_Rest_Template {

    protected $_model = 'Album';

    function action_share()
    {
        $auth = Auth::instance();

        if(!$auth->logged_in()){
            throw new HTTP_Exception_403('Access denied');
        }

        $identifier = $this->request->param('id');

        $args['code'] = $identifier;

        if (FALSE === ($record = $this->_model->find($args))) {
            throw new HTTP_Exception_404('Record not found');
        }

        Model::factory('Album_Access')->create(array(
            'user' => $auth->get_user('id'),
            'album' => $record['id']
        ));


        return $record;
    }

    
    
    function action_users() 
    {
        foreach($this->query() as $key => $value){
            if(false !== strpos($key, 'or:')){
                throw new HTTP_Exception_403('Method OR not allowed');
            }
        }        

//        if(!$this->_is_allowed('users')){
//            throw new HTTP_Exception_403('Access denied');
//        }
        
        
        $identifier = $this->request->param('id');

        if (FALSE === $this->_model->exists($identifier)) {
            throw new HTTP_Exception_404('Record not found');
        }

        foreach($this->query() as $key => $value){

            if(false !== strpos($key, 'or:')){
                throw new HTTP_Exception_403('Method OR not allowed');
            }
        }   


        return $this->query();
    }
    
   
}