<?php defined('SYSPATH') or die('No direct script access.');

return array( 
    
    'tmpdir' => 'tmp',
    'filesystem' => 'filesystem',
    'maxSize' => '2M',
    
    'allowedType' => array(
        '7z', 'doc', 'docx', 'xls', 'ppt', 'pptx', 'djvu', 'djv',
        'mp3', 'mp4', 'wav', 'aac', 'aiff', 'midi',
        'avi', 'mov', 'mpg', 'flv', 'mpa',
	    'xlsx', 'jpeg', 'jpg', 'gif', 'otp', 'pdf',
	    'png', 'rar', 'txt', 'zip'  , 'swf'
    ),
    
);