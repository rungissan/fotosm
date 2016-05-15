<?php defined('SYSPATH') OR die('No direct access allowed.');

return array
( 

	// Enables Disqus comments on the API and User Guide pages
	'show_comments' => Kohana::$environment === Kohana::DEVELOPMENT,

);
