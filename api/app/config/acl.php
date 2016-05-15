<?php defined('SYSPATH') OR die('No direct access allowed.');

return array( 


    
    
    
    'album' => array( 
        
        'privilege' => array(
            
            'delete' => array( 
                'predefined' => true,
                'assert' => array(
                    'IAmPhotographer' => array('default' => true),
                    'MyAlbum' => array('default' => true)
                )
            ),
            'create' => array( 
                'predefined' => true,
                'assert' => array(
                    'IAmPhotographer' => array('default' => true),
                    'MyAlbum' => array('default' => true)
                )
            ),
            'read' => array( 
                'predefined' => true,
                'assert' => array(
                    'MyAlbum' => array('default' => true)
                )
            ),
            'update' => array(
                'predefined' => true,
                'assert' => array(
                    'IAmPhotographer' => array('predefined' => true),
                    'MyAlbum' => array('default' => true)
                )
            ),
            
            
        ),
    ),

    'order' => array(

        'privilege' => array(

            'delete' => array('predefined' => false),
            'create' => array(
                'predefined' => true,

                // проверка на то, что у меня есть доступ к альбому
                'assert' => array('MyOrder' => array('default' => true))
            ),
            'read' => array(
                'predefined' => true,

                // вижу свои заказы, заказы где я автор альбома или менджер альбома
                'assert' => array('MyOrder' => array('default' => true))
            ),
            'update' => array(
                'predefined' => true,

                 // можно редактировать если я автор или менджер альбома
                'assert' => array('MyOrder' => array('default' => true))
            ),


        ),
    ),

    'album/image' => array(

        'privilege' => array(

            'delete' => array(
                'predefined' => true,

                // проверка на то, что у это моя фото
                'assert' => array('MyImage' => array('default' => true))
            ),
            'create' => array(
                'predefined' => true,

                // если я фотограф
                'IAmPhotographer' => array('default' => true),

                // добавить фото могу только в свой альбом
                'assert' => array('MyImage' => array('default' => true))
            ),
            'read' => array(
                'predefined' => true,

                // вижу все фото, к котором мне дали доступ
                'assert' => array('MyImage' => array('default' => true))
            ),
            'update' => array(
                'predefined' => true,

                // редактировать могу только свое фото
                'assert' => array('MyImage' => array('default' => true))
            ),


        ),
    ),

    'order/image' => array(

        'privilege' => array(

            'delete' => array(
                'predefined' => true,
                'assert' => array('MyOrderImage' => array('default' => true))
            ),
            'create' => array(
                'predefined' => true,
                'assert' => array('MyOrderImage' => array('default' => true))
            ),
            'read' => array(
                'predefined' => true,
                'assert' => array('MyOrderImage' => array('default' => true))
            ),
            'update' => array(
                'predefined' => true,
                'assert' => array('MyOrderImage' => array('default' => true))
            ),


        ),
    ),

    'user' => array(

        'privilege' => array(

            'delete' => array('predefined' => false),

            'update' => array(
                'predefined' => true,
                'assert' => array('IsMyProfile' => array('default' => true))
            ),

            'create' => array('predefined' => true),
            'read' => array('predefined' => true),

        ),
    ),

    'album/users' => array(

        'privilege' => array(

            'delete' => array(
                'predefined' => true,
                'assert' => array('MySubordinate' => array('default' => true))
            ),
            'create' => array(
                'predefined' => true,
                'assert' => array('MySubordinate' => array('default' => true))
            ),
            'read' => array(
                'predefined' => true,
                'assert' => array('MySubordinate' => array('default' => true))
            ),
            'update' => array(
                'predefined' => true,
                'assert' => array('MySubordinate' => array('default' => true))
            ),


        ),
    ),


    


);