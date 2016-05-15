<?php defined('SYSPATH') or die('No direct script access.');


Route::set('media', 'media(/<file>)', array('file' => '.+'))
    ->defaults(array(
        'controller' => 'media',
        'action'     => 'index',
        'file'       => NULL,
    ));

// User guide pages, in modules
Route::set('docs/guide', 'guide(/<module>(/<page>.htm))', array(
		'page' => '.+',
	))
	->defaults(array(
		'controller' => 'userguide',
		'action'     => 'docs',
		'module'     => '',
	)); 

// Simple autoloader used to encourage PHPUnit to behave itself.
class Markdown_Autoloader {
	public static function autoload($class)
	{
		if ($class == 'Markdown_Parser' OR $class == 'MarkdownExtra_Parser')
		{
			include_once Kohana::find_file('vendor', 'markdown/markdown');
		}
	}
}

// Register the autoloader
spl_autoload_register(array('Markdown_Autoloader', 'autoload'));
