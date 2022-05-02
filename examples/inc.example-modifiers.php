<?php
// all modifiers must be declared inside the MythTPL\Template namespace or the global namespace
namespace MythTPL\Template;

/*
with the exception of the 1st function parameter, all aditionals parameters should have a default value declared so you can use it like this:
|modifier
|modifier:param1
|modifier:param1,param2
|modifier:param1,param2,param3

*/

function test_modifier($string, $before_text = null, $after_text = null, $after_text2 = null)
{
    $s = 'Modifying the given string: "' . $string . '" with the following params:<br>';
    $s .= 'param1: ' . ($before_text ?? 'not given') . '<br>';
    $s .= 'param2: ' . ($after_text ?? 'not given') . '<br>';
    $s .= 'param3: ' . ($after_text2 ?? 'not given') . '<br>';
    $s .= 'Final string is: ' . ($before_text . $string . $after_text . $after_text2);

    return $s;
}
