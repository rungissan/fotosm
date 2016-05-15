<?php defined('SYSPATH') or die('No direct script access.');

Route::set('filesystem', 'files(/<file>.<ext>)', array('file' => '\d+\-\w+', 'ext' => '[\d\w]+'))
    ->defaults(array(
        'controller' => 'filesystem',
        'action'     => 'index',
        'file'       => NULL,
    ));
