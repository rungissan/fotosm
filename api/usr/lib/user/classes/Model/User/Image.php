<?php defined('SYSPATH') or die('No direct script access.');

class Model_User_Image extends Kohana_Model_Image{
    protected $_context = 'user';
    protected $_table = 'user/image';
}