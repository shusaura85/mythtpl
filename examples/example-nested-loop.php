<?php
// include
require "../src/autoload.php";

// namespace
use MythTPL\MythTPL;


// config
$config = array(
    "tpl_dir"       => "templates/nested_loop/",
    "cache_dir"     => "cache/",
    "debug"         => true // set to false to improve the speed
);



$user = array(
    array(
        'name' => 'Jupiter',
        'color' => 'yellow',
        'orders' => array(
            array('order_id' => '123', 'order_name' => 'o1d'),
            array('order_id' => '1sn24', 'order_name' => 'o2d')
        )
    ),
    array(
        'name' => 'Mars',
        'color' => 'red',
        'orders' => array(
            array('order_id' => '3rf22', 'order_name' => '¥Aj')
        )
    ),
    array(
        'name' => 'Empty',
        'color' => 'blue',
        'orders' => array(
        )
    ),
    array(
        'name' => 'Earth',
        'color' => 'blue',
        'orders' => array(
            array('order_id' => '2315', 'order_name' => '日本国'),
            array('order_id' => 'rf2123', 'order_name' => '¥215'),
            array('order_id' => '0231', 'order_name' => 'にっぽんこく'),
            array('order_id' => 'sn09-0fsd', 'order_name' => '君が代')
        )
    )
);


// draw
$tpl = new MythTPL( $config );
$tpl->assign_var( "path", 'templates/nested_loop/' );
$tpl->assign_var( "user", $user );
echo $tpl->draw( "test" );



class Test{
    static public function method( $variable ){
        echo "Hi I am a static method, and this is the parameter passed to me: $variable!";
    }
}

// end