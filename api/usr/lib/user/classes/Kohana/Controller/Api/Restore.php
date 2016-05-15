<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Controller_Api_Restore extends Controller_Rest {

     
    public function action_create(){

            $user = Model::factory('User')->find(array('login' => $this->query('login')));

            if(empty($user)){
                throw new HTTP_Exception_404('Пользователь не найден.');
            }

            Email::notify($user['login'], 'user.restore-password', array(
                ':firstname' => $user['firstname'],
                ':lastname' => $user['lastname'],
                ':code' => $this->_gencode($user)
            ));
            
            return 'success';
                
       }
       
        public function action_update(){

            $model = Model::factory('User');
            
            $user = $model->find(array('login' => $this->query('login')));

            if(empty($user)){
                throw new HTTP_Exception_404('Пользователь не найден.');
            }
            
            if($this->query('code') != $this->_gencode($user)){
                return array(
                    '_status' => array(
                      'code' => 406,
                       'msg' => 'Request is invalid' 
                    ),
                    'errors' => array(
                        'code' => 'Код недействителен.'
                    )
                );
            }            

           
            $identifier = $errors = $model->update(array('password' => $this->query('password')), $user['id']);
            
            if (is_numeric($identifier)) {
            
                Request::factory('auth')
                ->method(Request::POST)
                    ->post(array(
                        'login' => $user['login'],
                        'password' => $this->query('password')
                    ))
                ->execute();
            
                return 'success';
                
            } else {

                return array(
                    '_status' => array(
                      'code' => 406,
                       'msg' => 'Request is invalid' 
                    ),
                    'errors' => $errors
                );
            }
                
       }       
       
       protected function _gencode(array $user){
           return 
           strlen($user['login']) . strrev(substr($user['password'], 1, 3)) .
           (date('yyddmm')%$user['id']) . strrev(substr($user['password'], -3));
       }
    
    
}
