<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Album extends Model_Db_Crud {
    /**
     * @SWG\DEFINITION (
     *   required={"order","image","date"},
     *   @SWG\ExternalDocumentation(
     *     description="find more info here",
     *     url="https://swagger.io/about"
     *   )
     * )
     */
    protected $_table = 'album';
    protected $_relations = array(
        /**
         * @SWG\Property(type="array")
         */
        'order' => array(
            'model' => 'Order',
            'attrs' => array(
                'except' => 'album',
            )
        ),
        'access' => array(
            'model' => 'Album_Users',
            'attrs' => array(
                'extra' => 'user-firstname,user-lastname,user-login'
            )
        ),
        'image' => array(
            'model' => 'Album_Image',
            'attrs' => array(
                'except' => 'album',
                'orderby' => array(
                    array('main', 'desc'),
                    array('id', 'desc')
                )
            )
        ),
           );
    protected $_fields = array(
        'group' => array(
            'external' => 'Album_Group',
            'name' => 'Catalog',
            'rules' => array(
                'not_empty' => NULL,
                'numeric' => NULL,
            ),
        ),
        'autor' => array(
            'external' => 'User',
            'default' => array(
                'Model_Db_Default_User::data' => array('id')
            ),
            'name' => 'Autor',
            'rules' => array(
                'not_empty' => NULL,
                'numeric' => NULL,
            ),
        ),
        'code' => array(
            'name' => 'Access code',
            'default' => array(
                'Text::random' => array('numeric', 10)
            ),
            'rules' => array(
                'not_empty' => NULL,
                'Model_Db_Callback_Unique::execute' => array('code', ':value', ':validation', ':context'),
                'max_length' => array(':value', 10),
                'min_length' => array(':value', 10),
            ),
        ),
                'open' => array(
            'name' => 'Open',
            'default' => 0,
            'rules' => array(
                'numeric' => NULL,
                'in_array' => array(':value', array(1, 0)),
            ),
        ),
        'public' => array(
            'name' => 'Public',
            'default' => 0,
            'rules' => array(
                'numeric' => NULL,
                'in_array' => array(':value', array(1, 0)),
            ),
        ),
        'date' => array(
            'name' => 'Registration date',
            'rules' => array(
                'date' => NULL,
            ),
        ),
        'modified' => array(
            'name' => 'Modified date',
            'rules' => array(
                'date' => NULL,
            ),
        ),
               'name' => array(
            'name' => 'Name',
            'rules' => array(
                'not_empty' => NULL,
                'max_length' => array(':value', 75),
            ),
        ),
               'config' => array(
            'name' => 'Setting',
            'filters' => array(
                'Model_Album::filter_config' => NULL,
            ),
            'rules' => array(
                'not_empty' => NULL,
                'Model_Album::check_config' => array(':value', ':validation', ':context'),
                'max_length' => array(':value', 5000),
            ),
        )
    );

    public function update(array $record, $identifier){
        unset($record['code']);
        return parent::update($record, $identifier);
    }




    static function filter_config($config, $id = NULL) {
        if (!empty($id)) {
            $record = Model::factory('Album')->find($id, array(
                'config'
            ));

            if (!empty($record)) {
                $config = array_merge($record['config'], $config);
            }
        }

        return json_encode($config);
    }


}
