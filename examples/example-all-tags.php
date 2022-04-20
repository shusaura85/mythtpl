<?php

// include
require "../src/autoload.php";

// namespace
use MythTPL\MythTPL;


// config
$config = array(
    "tpl_dir"       => "./templates/all_tags/",
    "cache_dir"     => "./cache/",
    "remove_comments" => true,
    "debug"         => true, // set to false to improve the speed
);


// set variables
$var = array(
	"path"		=> "templates/all_tags/",
    "variable"  => "Hello World!",
    "bad_variable"  => "<script>alert('evil javascript here');</script>",
    "safe_variable"  => "<script>console.log('this is safe')</script>",
    "version"   => "3.1.1",
    "menu"		=> array(
        array("name" => "Home", "link" => "index.php", "selected" => true ),
        array("name" => "FAQ", "link" => "index.php/FAQ/", "selected" => null ),
        array("name" => "Documentation", "link" => "index.php/doc/", "selected" => null )
    ),
    "week"		=> array( "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday" ),
    "user"		=> (object) array("name"=>"Myth", "citizen" => "Earth", "race" => "Human" ),
    "numbers"	=> array( 3, 2, 1 ),
    "bad_text"	=> 'Hey this is a malicious XSS <script>alert("auto_escape is always enabled");</script>',
    "table"		=> array( array( "Apple", "1996" ), array( "PC", "1997" ) ),
    "title"		=> "Myth TPL - Easy and Fast template engine",
    "copyright" => "Copyright 2022 Myth TPL<br>Project By Shu Saura",
    "num1" => 10,
    "num2" => 20,

);

// add a tag: {@text@}
MythTPL::registerTag(	"tag_name_here",
    "{@(.*?)@}", // preg match
    function( $params ){ // function called by the tag
        $value = $params[1][0];
        return "Translate: <b>$value</b>";
    }
);


// add a tag: {%text1|text2%}
MythTPL::registerTag(	"tag_name_here",
    "{%(.*?)(?:\|(.*?))%}", // preg match
    function( $params ){ // function called by the tag
        $value = $params[1][0];
        $value2 = $params[2][0];

        return "Translate: <b>$value</b> in <b>$value2</b>";
    }
);

// draw
$tpl = new MythTPL( $config );
$tpl->set_tag_php(true);
$tpl->assign( $var );
echo $tpl->draw( "test-all-tags" );



class Test{
    static public function method( $variable ){
        echo "Hi I am a static method, and this is the parameter passed to me: $variable!";
    }
}

// end