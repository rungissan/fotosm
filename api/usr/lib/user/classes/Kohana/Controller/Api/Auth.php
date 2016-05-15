<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Controller_Api_Auth extends Controller_Rest {

    public function action_read(){

        $auth = Auth::instance();

        return array(
            'id'        => $auth->get_user('id'),
            'login'     => $auth->get_user('login'),
            'firstname' => $auth->get_user('firstname'),
            'lastname'  => $auth->get_user('lastname'),
            'group'     => $auth->get_user('group')
        );
    }

    public function action_create(){

        $auth = Auth::instance();

//        if($auth->logged_in()){
//            throw new HTTP_Exception_403('You are already logged in!');
//        }

        $login      = $this->query('login');
        $password   = $this->query('password');

        if($auth->login($login, $password)){

            Model::factory('User_Session')->begin($auth->get_user('id'), Request::$client_ip);

            Model::factory('Activity')->register('auth', 'login');

            //$newToken = $auth->generate_token();
            
            $newToken = Session::instance()->id();

            Model::factory('User')->update(array('token' => $newToken), $auth->get_user('id'));
 

            $this->response->headers('Authorization', $newToken);
            

            return Arr::merge(
                $this->action_read(),
                array('token' => $newToken)
            );

        } else {

            throw new HTTP_Exception_403('Login or password is not correct.');
        }
    }


    public function action_delete(){
       
       $auth = Auth::instance();


        Model::factory('Activity')->register('auth', 'logout');

       Model::factory('User_Session')->complete($auth->get_user('id')); 
       $auth->logout();
        
       return __('Goodbye!');
    }  
    
    
}
