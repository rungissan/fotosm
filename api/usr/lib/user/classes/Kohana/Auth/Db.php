<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * File Auth driver.
 * [!!] this Auth driver does not support roles nor autologin.
 *
 * @package    Kohana/Auth
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Auth_Db extends Auth {

    protected $_user = NULL;
    
    protected $_default_user_data = array(
        
        'login'     => 'guest@example.com',
        'firstname' => NULL,

        
        'group' => array(
            'id' => NULL,
            'name' => 'guest'
        )
    );

    protected $is_auth = FALSE;
    protected $_user_load_data = FALSE;

    /**
     * 
     * @param type $config
     */
    public function __construct($config = array()) {
        parent::__construct($config);
        

        $this->_user = $this->_default_user_data;

        /* session exists? */
        $this->is_auth = ($this->get_user() !== NULL);

        if ($this->logged_in() and !$this->_load_user($this->get_user())) {
            $this->logout();
        }

    }


//    public function generate_token()
//    {
//        if(!$this->is_auth){
//            return NULL;
//        }
//
//        return $this->hash($this->get_user('login')).date('W');
//    }
    
    /**
     * Log out a user by removing the related session variables.
     *
     * @param   boolean  $destroy     Completely destroy the session
     * @param   boolean  $logout_all  Remove all tokens for user
     * @return  boolean
     */
    public function logout($destroy = FALSE, $logout_all = FALSE)
    {
            $logged_in = parent::logout($destroy, $logout_all);
           
            if(!$logged_in){
                $this->_user = $this->_default_user_data;
                return TRUE;
            }
            
            return FALSE;
    }

    /**
     * Gets the currently logged in user from the session.
     * Returns NULL if no user is currently logged in.
     * 
     * @return  mixed
     */
    public function get_user($key = NULL, $default = NULL) {

        return (!empty($key)) ?
                Arr::path($this->_user, $key, $default) :
                $this->_session->get($this->_config['session_key'], $default);
    }
            
 /**
     * Gets the currently logged in user from the session.
     * Returns NULL if no user is currently logged in.
     * 
     * @return  mixed
     */
    public function change_data($path = NULL, $value = NULL) {
        Arr::set_path($this->_user, $path, $value);
        return $this;
        
    }
    
    public function is_allowed($resource = NULL, $privilege = NULL, $context = NULL)
    {
            return Acl::instance()->is_allowed($this->get_role(), $resource, $privilege, $context);
    }
    

    public function get_role()
    {
            $role = $this->get_user('group.id');

            return (!empty($role))? 'group'.$role:'guest';
    }
    
    /**
     * Get user data as array
     * @return array
     */
    public function as_array(){
        return $this->_user;
    }

    /**
     * Attempt to log in a user by using an ORM object and plain-text password.
     *
     * @param   string   $username  Username to log in
     * @param   string   $password  Password to check against
     * @param   boolean  $remember  Enable autologin
     * @return  boolean
     */
    public function login($username, $password, $remember = true)
    {
        return parent::login($username, $password, $remember);
    }


    /**
     * Check if there is an active session. Optionally allows checking for a
     * specific role.
     *
     * @param   string  $role  role name
     * @return  mixed
     */
    public function logged_in($user_group = NULL) {

        if (empty($user_group)) {
            return $this->is_auth;
        }

        return ($this->is_auth and $user_group == Arr::get($this->get_group(), is_string($user_group)? 'name':'id'));
    }

    /**
     * Logs a user in.
     *
     * @param   string   $username  Username
     * @param   string   $password  Password
     * @param   boolean  $remember  Enable autologin (not supported)
     * @return  boolean
     */
    protected function _login($username, $password, $remember) {

        if (is_string($password)) {
            // Create a hashed password
            $password = $this->hash($password);
        }


        if ($this->_load_user($username) and $this->check_password($password)) {
            return $this->complete_login($username, $remember);
        } else {
            $this->_user = $this->_default_user_data;
        }


        // Login failed
        return FALSE;
    }

    /**
     * Forces a user to be logged in, without specifying a password.
     *
     * @param   mixed    $username  Username
     * @return  boolean
     */



    public function force_login($username, $remember = TRUE) {

        if ($this->_load_user($username)) {
            return $this->complete_login($username, $remember);
        }

        return FALSE;
    }


    protected function complete_login($user, $remember = TRUE)
    {

        if($remember){

           parent::complete_login($user);
        }

        $this->is_auth = true;

        Acl::instance();

        return TRUE;
    }

    /**
     * Get the stored password for a username.
     *
     * @param   mixed   $username  Username
     * @return  string
     */
    public function password($username){
        return $this->get_user('password');
    }

    /**
     * Compare password with original (plain text). Works for current (logged in) user
     *
     * @param   string   $password  Password
     * @return  boolean
     */
    public function check_password($password) {
        return ($password === $this->password(NULL));
    }

    /**
     * Get user group
     * @return type
     */
    function get_group() {
        return $this->_user['group'];
    }

    /**
     * Gets the currently logged in user from the session.
     * Returns NULL if no user is currently logged in.
     * 
     * @return  mixed
     */
    protected function _load_user($username) {

        if($this->_user_load_data){
            return TRUE;
        }

        if (
                FALSE === ($user = Model::factory('User')->find(array('login' => $username, 'active' => 1))) or
                FALSE === ($group = Model::factory('User_Group')->find(array('only' => array('id', 'name'), 'id' => $user['group'], 'active' => 1)))
           ) {
            
             return FALSE;
        }


        $this->_user = $user;
        $this->_user['group'] = $group;

        $this->_user_load_data = true;

        
        return TRUE;
    }

}

// End Auth File
