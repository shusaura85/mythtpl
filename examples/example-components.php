<?php

// include
require "../src/autoload.php";

// example modifiers
require "./inc.example-modifiers.php";

// namespace
use MythTPL\MythTPL;


// config
$config = array(
    "tpl_dir"       => "./templates/component/",
	"components_dir"	=> "./components/",
    "cache_dir"     => "./cache/",
    "remove_comments" => true,
    "debug"         => true, // set to false to improve the speed
);


// set variables
$var = array(
	"path"		=> "templates/include/",
	"title"		=> "MythTPL Component template example",
    "inputs"		=> [
        'name'  => array("label" => "Give me a name", "name" => "username", "id" => "user-name" ),
        'email'  => array("label" => "Give me an email address", "name" => "usermail", "id" => "user-email" ),
    ],

);

// add a tag: {@text@}
MythTPL::registerTag(	"simple_custom_tag",
    "{@(.*?)@}", // preg match
    function( $params ){ // function called by the tag
        $value = $params[1][0];
        return "Translate: <b>$value</b>";
    }
);


// add a tag: {%text1|text2%}
MythTPL::registerTag(	"another_custom_tag",
    "{%(.*?)(?:\|(.*?))%}", // preg match
    function( $params ){ // function called by the tag
        $value = $params[1][0];
        $value2 = $params[2][0];

        return "Translate: <b>$value</b> in <b>$value2</b>";
    }
);

// draw
$tpl = new MythTPL( $config );
$tpl->setTagPhp(true);
$tpl->assign( $var );
echo $tpl->draw( "test-form" );



class Test{
    static public function method( $variable ){
        echo "Hi I am a static method, and this is the parameter passed to me: $variable!";
    }
}

// end