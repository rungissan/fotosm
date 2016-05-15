<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Api_Album_Users extends Controller_Rest_Template {
    
    protected $_action_map = array
    (
        HTTP_Request::GET       => 'read',
        HTTP_Request::POST      => 'create',
        HTTP_Request::DELETE    => 'delete',
    );

    protected $_model = 'Album_Users';

    function action_item($identifier = null, array $args = array()) 
    {
        $identifier = $this->request->param('id');

        if (FALSE === ($album = Model::factory('Album')->find($identifier))) {
            throw new HTTP_Exception_404('Record not found');
        }

//        if(!$this->_is_allowed('read', $album)){
//            throw new HTTP_Exception_403('Access denied');
//        }
//

        return (null != $album['group'])? parent::action_read(array(
            'or:album' => $album['id'],
            'or:group' => $album['group']
        )): parent::action_read(array(
            'album' => $album['id'],
        ));
    }

    
}