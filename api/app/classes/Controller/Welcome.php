<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @SWG\Info(title="My First API", version="0.1")
 */

/**
 * @SWG\Get(
 *     path="/api/resource.json",
 *     @SWG\Response(response="200", description="An example resource")
 * )
 */
class Controller_Welcome extends Controller {

    public function action_index(){

        $this->response->body('hello world!');
    }
}
