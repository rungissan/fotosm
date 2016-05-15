<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Model_User_Session extends Model_Db
{
    
    protected $_fields = array(
        'ip' => array(
            'name' => 'IP адрес',
        ),
        'user' => array(
            'name' => 'Пользователь',
            'external' => 'User',
        ),
        'start' => array(
            'name' => 'Время начала',
        ),
        'end' => array(
            'name' => 'Время завершения',
        ),
    );

    public function begin($user, $ip) {
       
        if(!is_numeric($user)){
            return FALSE;
        }
        
        $this->complete($user);

        $record = array(
            'ip' => (!empty($ip))? @inet_pton($ip): NULL,
            'user' => $user,
            'start' => DB::expr('NOW()'),
        );

        $identifier = DB::insert($this->_table, array_keys($record))->
                values(array_values($record))->
                execute();

        return $identifier[0];
    }

    public function complete($user) {

        DB::update($this->_table)
                ->set(array('end' => DB::expr('NOW()')))
                ->where('user', '=', $user)
                ->where('end', 'is', NULL)
                ->execute();
    }
    
    public function load(array $param = array(), $pagination = FALSE, Closure $mixin = NULL, $restriction = FALSE)  {
        $response = parent::load($param, $pagination, $mixin, $restriction);
        
        if($pagination){
            $data = &$response['data'];
        } else {
            $data = &$response;
        }
        foreach ($data as $key => &$item){
            if(!empty($item['ip'])){
                $item['ip'] = @inet_ntop($item['ip']);
            }
            
        }
        
        return $response;
    }

    protected $_table = 'user/session';

}