<?php defined('SYSPATH') or die('No direct script access.');

/**
 * @SWG\POST
 * user(
 *     path="/api/post",
 *     description="Returns all pets from the system that the user has access to",
 *     operationId="findPets",
 *     produces={"application/json", "application/xml", "text/xml", "text/html"},
 *     @SWG\Parameter(
 *         name="tags",
 *         in="query",
 *         description="tags to filter by",
 *         required=false,
 *         type="array",
 *         @SWG\Items(type="string"),
 *         collectionFormat="csv"
 *     ),
 *     @SWG\Parameter(
 *         name="limit",
 *         in="query",
 *         description="maximum number of results to return",
 *         required=false,
 *         type="integer",
 *         format="int32"
 *     ),
 *     @SWG\Response(
 *         response=200,
 *         description="pet response",
 *         @SWG\Schema(
 *             type="array",
 *             @SWG\Items(ref="#/definitions/pet")
 *         ),
 *     ),
 *     @SWG\Response(
 *         response="default",
 *         description="unexpected error",
 *         @SWG\Schema(
 *             ref="#/definitions/errorModel"
 *         )
 *     )
 * )
 */

class Controller_Api_Signup extends Controller_Rest_Template
{ 
    protected $_model = 'User';
    
    protected $_action_map = array
    (
        HTTP_Request::POST => 'create',
    );

    public function action_create(){
        $result = parent::action_create();

        if(!isset($result['id'])){
            return $result;
        }

        $auth = Auth::instance();

        if($auth->login($this->query('login'), $this->query('password'))){

            Model::factory('User_Session')->begin($auth->get_user('id'), Request::$client_ip);


        }

        return $result;
    }
    

    protected function _is_allowed($privilege = NULL, $context = NULL){

        if(isset($context['group']) and !in_array($context['group'], array(Auth_Db::$CLIENT, Auth_Db::$PHOTOGRAPHER))){
            return FALSE;
        }

        return !Auth::instance()->logged_in();
    }
   
}