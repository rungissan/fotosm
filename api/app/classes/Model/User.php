<?php defined('SYSPATH') or die('No direct script access.');

class Model_User extends Kohana_Model_User {
    /**
     * @SWG\Property(type="integer", format="int64")
     */
    static $ADMIN = 1;
    static $PHOTOGRAPHER = 5;
    static $CLIENT = 6;

    protected $_relations = array(
              
        'order' => array(
            'model' => 'Order',
            'attrs' => array(
                'extra' => 'album-name',
                'except' => 'user'
            )
        ),

    );
    


}