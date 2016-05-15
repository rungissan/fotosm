<?php defined('SYSPATH') OR die('No direct script access.');

return array(

    'user' => array(
	'restore-password' => array(
            'subject' => 'Восстановления пароля',
            'body' => '
                    Уважаемый,  :lastname :firstname!
                    Код для восстановления пароля: :code. 

                '
        ),
     )   
);
