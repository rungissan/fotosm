<?php defined('SYSPATH') OR die('No direct access allowed.');



class Auth_Db extends Kohana_Auth_Db {

    static $ADMIN = 1;
    static $CLIENT = 6;
    static $PHOTOGRAPHER = 5;

    public function is_admin(){
        return $this->logged_in(self::$ADMIN);
    }

    public function is_client(){
        return $this->logged_in(self::$CLIENT);
    }

    public function is_photographer(){
        return $this->logged_in(self::$PHOTOGRAPHER);
    }

    

    public function get_albums($level = null){
        $albums = array();

        if($this->logged_in()){

            if(null == $level){

                $albums = array_keys($this->_user['external-resource']['album']);

            } else if ('owner' == $level){

                $albums = $this->_user['internal-resource']['album'];

            }  else {

                $albums = array_keys(array_filter($this->_user['external-resource']['album'], function($item) use ($level){
                    return $item == $level;
                }));
            }
        }
        
        if(empty($albums)){
            // add noop item
            array_push($albums, 0);
        }
        
        return $albums;
    }


    
}