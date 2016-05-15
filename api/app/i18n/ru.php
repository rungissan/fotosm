<?php defined('SYSPATH') or die('No direct script access.');

return array( 

     
    /* validation */
    /* default message */
    ':field must be an email address'                       => 'Поле ":field" должно быть электронным адресом',
    ':field must not be empty'                              => 'Поле ":field" обязательно к заполнению',
    ':field must contain only letters'                      => 'Поле ":field" может состоять только из букв латинского алфавита',
    ':field must contain only numbers, letters and dashes'  => 'Поле ":field" может состоять только из цифр, букв латинского алфавита и знака тире',
    ':field must contain only letters and numbers'          => 'Поле ":field" может состоять только из цифр и букв латинского алфавита',
    ':field must be a color'                                => 'Поле ":field" должно быть цветом',
    ':field must be a credit card number'                   => 'Поле ":field" должно быть номером кредитной карты',
    ':field must be a date'                                 => 'Поле ":field" должно быть датой',
    ':field must be a decimal with :param2 places'          => 'Поле ":field" должно быть десятичным числом с :param2 знаками после запятой',
    ':field must be a digit'                                => 'Поле ":field" должно быть целым числом',
    ':field must be a email address'                        => 'Поле ":field" должно быть корректным email адресом',
    ':field must contain a valid email domain'              => 'Поле ":field" должно содержать корректный домен электронной почты',
    ':field must equal :param2'                             => 'Поле ":field" должно быть равно :param2',
    ':field must be exactly :param2 characters long'        => 'Длина поля ":field" должна быть равной :param2 символа(ов)',
    ':field must be one of the available options'           => 'Поле ":field" может содержать один из доступных вариантов',
    ':field must be an ip address'                          => 'Поле ":field" должно быть правильным IP адресом',
    ':field must be the same as :param2'                    => 'Поле ":field" должно совпадать с полем ":param2"',
    ':field must be at least :param2 characters long'       => 'Поле ":field" должно быть не менее :param2 символа(ов)',
    ':field must be less than :param2 characters long'      => 'Поле ":field" должно быть не более :param2 символа(ов)',
    ':field must be numeric'                                => 'Поле ":field" должно быть числом',
    ':field must be a phone number'                         => 'Поле ":field" должно быть телефонным номером',
    ':field must be within the range of :param2 to :param3' => 'Поле ":field" должно быть в промежутке от :param2 до :param3',
    ':field does not match the required format'             => 'Недопустимый формат поля ":field"',
    ':field must be a url'                                  => 'Поле ":field" должно быть корректным адресом web сайта',
    
    /* plugin upload */
    'multimedia :field must be attached' => 'Вы не прикрепили файл ":field"',
    'multimedia :field not meet the requirements' => 'Файл ":field" не соответствует представленным требованиям', 
   
);