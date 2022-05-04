<?php

/*-
 * Copyright © 2022 Shu Saura
 *
 * MythTPL is based on RainTPL 3
 * Copyright © 2011–2014 Federico Ulfo and a lot of awesome contributors
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * “Software”), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace MythTPL\Parser;

use \MythTPL\Error\Exception;
use \MythTPL\Error\NotFoundException;
use \MythTPL\Error\SyntaxException;

/**
 *  MythTPL
 *  --------
 *  Based on RainTPL by Federico Ulfo
 */
class Engine
{

    // variables
    //public $var = array();

    // configuration
    protected $config = array();

    // tags registered by the developers
    protected static $registered_tags = array();

    // tags natively supported
    // format: 'name' => 'match regex'
    protected static $tags = array(
        'loop' => '/{loop="(?<variable>\${0,1}[^"]*)"(?: as (?<key>\$.*?)(?: => (?<value>\$.*?)){0,1}){0,1}}/',    //    {loop="$var"} {loop="$var" as [$key=>]$value}
        'loop_close' => '/{\/loop}/',                                                                              //    {/loop}
        'loop_break' => '/{break}/',                                                                               //    {break} - used in loops
        'loop_continue' => '/{continue}/',                                                                         //    {continue} - used in loops
        'if' => '/{if="([^"]*)"}/',                                                                                //    {if="<condition>"}
        'elseif' => '/{elseif="([^"]*)"}/',                                                                        //    {elseif="<condition>"}
        'else' => '/{else}/',                                                                                      //    {else}        - can also be used inside {loop} for cases of empty array/iterable
        'if_close' => '/{\/if}/',                                                                                  //    {/if}
        'autoescape' => '/{autoescape="([^"]*)"}/',                                                                //    {autoescape="on|off"}    - if on, will automatically escape any output from tags inside, if off, disables automatic escape
        'autoescape_close' => '/{\/autoescape}/',                                                                  //    {/autoescape}            - returns to default setting
        'noparse' => '/{noparse}/',                                                                                //    {noparse}                - any tags inside will not be parsed and will be treated as plaintext
        'noparse_close' => '/{\/noparse}/',                                                                        //    {/noparse}
        'ignore' => '/{ignore}|{\*/',                                                                              //    {ignore}    (alternative) {*            - text inside this tag will not be present in compiled template
        'ignore_close' => '/{\/ignore}|\*}/',                                                                      //    {/ignore}   (alternative) *}
        'php' => '/{php}/',                                                                                        //    {php}                    - code inside will be treated as php code (if php is not enabled in configuration, it will be treated as an {ignore} tag)
        'php_close' => '/{\/php}/',                                                                                //    {/php}
        'include' => '/{include="([^"]*)"}/',                                                                      //    {include="relative/path/to/file.html"}    - includes the file specified. path is relative to template file that does the include
        'function' => '/{function="([\\\]*[a-zA-Z_][a-zA-Z_0-9\:\\\]*)(\(.*\)){0,1}"}/',                           //    {function="function_name(params)"}        - outputs the result of the specified function. Suports namespaces

        'ternary' => '/{(.[^{?}]*?)\?(.*?)\:(.*?)}/',                                                              //    {<condition>?"if true":'if false'}
        'variable' => '/{\$[^}]+?(?:(?:(\'|")[^\1]*?\1)[^}]*?)*}/',                                                //    {$variable} {$variable|modifier[|modifier]} {$variable|modifier:param2,param3,"param4"} {$variable|modifier:param2,param3,"param4"|another[|....]}
        'constant' => '/{\#[^}]+?(?:(?:(\'|")[^\1]*?\1)[^}]*?)*\#{0,1}}/',                                         //    {#CONSTANT} {#CONSTANT|modifier[|modifier]} {#CONSTANT|modifier:param2,param3,"param4"} {#CONSTANT|modifier:param2,param3,"param4"|another[|....]}
    );

    // safety check for templates - prevent accessing template file directly
    const SAFETY_CHECK = "<?php namespace MythTPL\Template; if(!class_exists('MythTPL\MythTPL')){exit;}?>";


    public function __construct(array $config, array $registered_tags)
    {
        $this->config = $config;
        static::$registered_tags = $registered_tags;
    }


    /**
     * Compile the file and save it in the cache
     *
     * @param string $templateDirectory : path to template root
     * @param string $templateFilepath : input template file
     * @param string $parsedTemplateFilepath : cache file where to save the template
     */
    public function compileFile(string $templateDirectory, string $templateFilepath, string $parsedTemplateFilepath): void
    {
        // create directories
        if (!is_dir($this->config['cache_dir'])) {
            $old_umask = umask(0);    // get current umask
            mkdir($this->config['cache_dir'], 0755, TRUE);
            umask($old_umask);    // restore current umask
        }

        // check if the cache is writable
        if (!is_writable($this->config['cache_dir'])) {
            throw new Exception('Cache directory ' . $this->config['cache_dir'] . 'doesn\'t have write permission. Set write permission.');
        }

        if (is_file($templateFilepath)) {
            $code = file_get_contents($templateFilepath);

            // always use unix line endings
            $code = str_replace("\r\n", "\n", $code);

            // xml substitution
            $code = preg_replace("/<\?xml(.*?)\?>/s", /*<?*/ "##XML\\1XML##", $code);

            // disable php tags
            $code = str_ireplace(array("<?php", "<?=", "?>"), array("&lt;?php", "&lt;?=", "?&gt;"), $code);

            // xml re-substitution
            $code = preg_replace_callback("/##XML(.*?)XML##/s", function ($match) {
                return "<?php echo '<?xml " . stripslashes($match[1]) . " ?>'; ?>";
            }, $code);

            $parsedCode = self::SAFETY_CHECK . $this->compileTemplate($code, $isString = false, $templateDirectory, $templateFilepath);

            // fix the php-eating-newline-after-closing-tag-problem
            $parsedCode = str_replace("?>\n", "?>\n\n", $parsedCode);

            // write compiled file
            file_put_contents($parsedTemplateFilepath, $parsedCode);
        }

    }

    /**
     * Compile a string and save it in the cache
     *
     * @param string $code : code to compile
     * @param string $parsedTemplateFilepath : cache file where to save the template
     */
    public function compileString(string $code, string $parsedTemplateFilepath): void
    {
        // create directories
        if (!is_dir($this->config['cache_dir'])) {
            $old_umask = umask(0);    // get current umask
            mkdir($this->config['cache_dir'], 0755, TRUE);
            umask($old_umask);    // restore current umask
        }

        // check if the cache is writable
        if (!is_writable($this->config['cache_dir'])) {
            throw new Exception('Cache directory ' . $this->config['cache_dir'] . 'doesn\'t have write permission. Set write permission.');
        }

        // always use unix line endings
        $code = str_replace("\r\n", "\n", $code);
        
        // xml substitution
        $code = preg_replace("/<\?xml(.*?)\?>/s", "##XML\\1XML##", $code);

        // disable php tags
        $code = str_ireplace(array("<?php", "<?=", "?>"), array("&lt;?php", "&lt;?=", "?&gt;"), $code);

        // xml re-substitution
        $code = preg_replace_callback("/##XML(.*?)XML##/s", function ($match) {
            return "<?php echo '<?xml " . stripslashes($match[1]) . " ?>'; ?>";
        }, $code);

        $parsedCode = self::SAFETY_CHECK . $this->compileTemplate($code, $isString = true, $templateDirectory = '', $templateFilepath = '');

        // fix the php-eating-newline-after-closing-tag-problem
        $parsedCode = str_replace("?>\n", "?>\n\n", $parsedCode);

        // write compiled file
        file_put_contents($parsedTemplateFilepath, $parsedCode);
    }


    /**
     * Split the template code into an array based on the tags
     * This was initially done via preg_split in compileTemplate but that failed parsing more complex tags such as ($variable|modifier:"string with {special} chars"}
     * @access protected
     *
     * @param string $code : code to split
     * @return array
     */
    protected function splitHtml(string $code): array
    {
        $total_len = strlen($code);
        $arr = [];

        // get tags and positions
        preg_match_all("/({[^'\"}]+((\"[^\"]*\"|'[^']*')[^'\"}]*)*})/", $code, $output, PREG_OFFSET_CAPTURE);

        $pos = 0;
        foreach ($output[0] as $key => $tag) {
            $offset = $tag[1];
            $len = strlen($tag[0]);

            // copy text from last pos to current pos
            if ($offset > $pos) {
                $arr[] = substr($code, $pos, $offset - $pos);
                $pos = $offset;
            }
            // copy current tag
            $arr[] = $tag[0];
            $pos += $len;
        }

        // copy remaining text
        if ($pos < $total_len) {
            $arr[] = substr($code, $pos);
        }

        return $arr;
    }

    /**
     * Compile template
     * @access protected
     *
     * @param string $code : code to compile
     */
    protected function compileTemplate(string $code, bool $isString, string $templateDirectory, string $templateFilepath): string
    {
        // set tags
        $tagMatch = static::$tags;

        // if case insensitive tags, add the /i modifier
        if ($this->config['tags_icase']) {
            // add /i modifier to tags
            foreach ($tagMatch as $tag_name => $tag_regex) {
                $tagMatch[$tag_name] .= 'i';
            }
        }

        //Remove comments
        if ($this->config['remove_comments']) {
            $code = preg_replace('/<!--(.*)-->/Uis', '', $code);
        }

        $codeSplit = $this->splitHtml($code);

        //variables initialization
        $parsedCode = $commentIsOpen = $ignoreIsOpen = $phpIsOpen = NULL;
        $openIf = $loopLevel = 0;

        // if the template is not empty
        if ($codeSplit) {
            //read all parsed code
            foreach ($codeSplit as $html) {
                // if not a possible tag, just continue
                if ($html[0] != '{') {
                    if ((!$commentIsOpen) && (!$ignoreIsOpen)) {
                        $parsedCode .= $html;
                    }
                    continue;
                }

                //close php tag
                if (!$commentIsOpen && preg_match($tagMatch['php_close'], $html)) {
                    if ($this->config['allow_php']) {
                        $parsedCode .= " ?>";
                        $phpIsOpen = FALSE;
                    } else {
                        $parsedCode .= "<?php /* {/php} tag detected but not enabled */ ?>";
                        $ignoreIsOpen = FALSE;
                    }
                    continue;    // go to next loop step
                }

                //close ignore tag
                if (!$commentIsOpen && preg_match($tagMatch['ignore_close'], $html)) {
                    $ignoreIsOpen = FALSE;
                    continue;    // go to next loop step
                }

                //code between tag ignore is deleted
                if ($ignoreIsOpen) {
                    //ignore the code
                    continue;    // go to next loop step
                }

                //close no parse tag
                if (preg_match($tagMatch['noparse_close'], $html)) {
                    $commentIsOpen = FALSE;
                    continue;    // go to next loop step
                }

                //code between tag noparse (and php if enabled) is not compiled
                if ($commentIsOpen || $phpIsOpen) {
                    $parsedCode .= $html;
                    continue;    // go to next loop step
                }

                //ignore
                if (preg_match($tagMatch['ignore'], $html)) {
                    $ignoreIsOpen = TRUE;
                    continue;    // go to next loop step
                }

                //noparse
                if (preg_match($tagMatch['noparse'], $html)) {
                    $commentIsOpen = TRUE;
                    continue;    // go to next loop step
                }

                //php code (if enabled) - if not enabled, is treated as {ignore}
                if (preg_match($tagMatch['php'], $html)) {
                    if ($this->config['allow_php']) {
                        $phpIsOpen = TRUE;
                        $parsedCode .= '<?php ';
                    } else {
                        $parsedCode .= "<?php /* {php} tag detected but not enabled */ ?>";
                        $ignoreIsOpen = TRUE;
                    }
                    continue;    // go to next loop step
                }

                //include tag
                if (preg_match($tagMatch['include'], $html, $matches)) {
                    //get the folder of the actual template
                    $actualFolder = $templateDirectory;

                    if (substr($actualFolder, 0, strlen($this->config['tpl_dir'])) == $this->config['tpl_dir']) {
                        $actualFolder = substr($actualFolder, strlen($this->config['tpl_dir']));
                    }

                    //get the included template
                    if (strpos($matches[1], '$') !== false) {
                        $includeTemplate = "'$actualFolder'." . $this->varReplace($matches[1], $loopLevel);
                    } else {
                        $includeTemplate = $actualFolder . $this->varReplace($matches[1], $loopLevel);
                    }

                    // reduce the path
                    $includeTemplate = Engine::reducePath($includeTemplate);

                    if (strpos($matches[1], '$') !== false) {
                        //dynamic include
                        $parsedCode .= '<?php require $this->processTemplatee(' . $includeTemplate . ');?>';

                    } else {
                        //dynamic include
                        $parsedCode .= '<?php require $this->processTemplatee("' . $includeTemplate . '");?>';
                    }

                    continue;    // go to next loop step
                }

                //loop
                if (preg_match($tagMatch['loop'], $html, $matches)) {

                    // increase the loop counter
                    $loopLevel++;

                    //replace the variable in the loop
                    $var = $this->varReplace($matches['variable'], $loopLevel - 1, $escape = FALSE);
                    if (preg_match('#\(#', $var)) {
                        $newvar = "\$newvar{$loopLevel}";
                        $assignNewVar = "$newvar=$var;";
                    } else {
                        $newvar = $var;
                        if (strpos($newvar, '$') === false) {
                            $newvar = '$' . $newvar;
                        }
                        $assignNewVar = null;
                    }

                    //loop variables
                    $counter = "\$counter$loopLevel";       // count iteration

                    if (isset($matches['key']) && isset($matches['value'])) {
                        $key = $matches['key'];
                        $value = $matches['value'];
                    } elseif (isset($matches['key'])) {
                        $key = "\$key$loopLevel";               // key
                        $value = $matches['key'];
                    } else {
                        $key = "\$key$loopLevel";               // key
                        $value = "\$value$loopLevel";           // value
                    }


                    //loop code
                    $parsedCode .= "<?php $counter=-1; $assignNewVar if( ($newvar !== null) && ( is_array($newvar) || $newvar instanceof Traversable ) && sizeof($newvar) ) foreach( $newvar as $key => $value ){ $counter++; ?>";

                    continue;    // go to next loop step
                }

                //close loop tag
                if (preg_match($tagMatch['loop_close'], $html)) {
                    //iterator
                    $counter = "\$counter$loopLevel";

                    //decrease the loop counter
                    $loopLevel--;

                    //close loop code
                    $parsedCode .= "<?php } ?>";

                    continue;    // go to next loop step
                }

                //break loop tag
                if (preg_match($tagMatch['loop_break'], $html)) {
                    //close loop code
                    $parsedCode .= "<?php break; ?>";

                    continue;    // go to next loop step
                }

                //continue loop tag
                if (preg_match($tagMatch['loop_continue'], $html)) {
                    //close loop code
                    $parsedCode .= "<?php continue; ?>";

                    continue;    // go to next loop step
                }

                //if
                if (preg_match($tagMatch['if'], $html, $matches)) {
                    //increase open if counter (for intendation)
                    $openIf++;

                    //tag
                    $tag = $matches[0];

                    //condition attribute
                    $condition = $matches[1];

                    //variable substitution into condition (no delimiter into the condition)
                    $parsedCondition = $this->varReplace($condition, $loopLevel, $escape = FALSE);

                    //if code
                    $parsedCode .= "<?php if( $parsedCondition ){ ?>";

                    continue;    // go to next loop step
                }

                //elseif
                if (preg_match($tagMatch['elseif'], $html, $matches)) {
                    //tag
                    $tag = $matches[0];

                    //condition attribute
                    $condition = $matches[1];

                    //variable substitution into condition (no delimiter into the condition)
                    $parsedCondition = $this->varReplace($condition, $loopLevel, $escape = FALSE);

                    //elseif code
                    $parsedCode .= "<?php }elseif( $parsedCondition ){ ?>";

                    continue;    // go to next loop step
                }

                //else
                if (preg_match($tagMatch['else'], $html)) {
                    //else code
                    $parsedCode .= '<?php }else{ ?>';

                    continue;    // go to next loop step
                }

                //close if tag
                if (preg_match($tagMatch['if_close'], $html)) {
                    //decrease if counter
                    $openIf--;

                    // close if code
                    $parsedCode .= '<?php } ?>';

                    continue;    // go to next loop step
                }

                // autoescape off
                if (preg_match($tagMatch['autoescape'], $html, $matches)) {
                    // get function
                    $mode = $matches[1];
                    $this->config['auto_escape_old'] = $this->config['auto_escape'];

                    if ($mode == 'off' or $mode == 'false' or $mode == '0' or $mode == null) {
                        $this->config['auto_escape'] = false;
                    } else {
                        $this->config['auto_escape'] = true;
                    }

                    continue;    // go to next loop step
                }

                // autoescape on
                if (preg_match($tagMatch['autoescape_close'], $html, $matches)) {
                    $this->config['auto_escape'] = $this->config['auto_escape_old'];
                    unset($this->config['auto_escape_old']);

                    continue;    // go to next loop step
                }

                // function
                if (preg_match($tagMatch['function'], $html, $matches)) {
                    // get function
                    $function = str_replace("/", "\\", $matches[1]);

                    // var replace
                    if (isset($matches[2]))
                        $parsedFunction = $function . $this->varReplace($matches[2], $loopLevel, $escape = FALSE, $echo = FALSE);
                    else
                        $parsedFunction = $function . "()";

                    // function
                    $parsedCode .= "<?php echo $parsedFunction; ?>";

                    continue;    // go to next loop step
                }

                //ternary
                if (preg_match($tagMatch['ternary'], $html, $matches)) {
                    $parsedCode .= "<?php echo " . '(' . $this->varReplace($matches[1], $loopLevel, $escape = TRUE, $echo = FALSE) . '?' . $this->varReplace($matches[2], $loopLevel, $escape = TRUE, $echo = FALSE) . ':' . $this->varReplace($matches[3], $loopLevel, $escape = TRUE, $echo = FALSE) . ')' . "; ?>";

                    continue;    // go to next loop step
                }

                //variables
                if (preg_match($tagMatch['variable'], $html, $matches)) {
                    //variables substitution (es. {$title})
                    /*	$parsedCode .= "<?php " . $this->varReplace($matches[1], $loopLevel, $escape = TRUE, $echo = TRUE) . "; ?>"; */
                    $parsedCode .= "<?php " . $this->varReplace(substr($matches[0], 1, -1), $loopLevel, $escape = TRUE, $echo = TRUE) . "; ?>";

                    continue;    // go to next loop step
                }

                //constants
                if (preg_match($tagMatch['constant'], $html, $matches)) {
                    $matched_const = substr($matches[0], 1, -1);
                    if ($matched_const[0] == '#') {
                        $matched_const = substr($matched_const, 1);
                    }
                    if (substr($matched_const[0], -1) == '#') {
                        $matched_const = substr($matched_const, 0, -1);
                    }
                    /*	$parsedCode .= "<?php echo " . $this->conReplace($matches[1], $loopLevel) . "; ?>";
                    */
                    $parsedCode .= "<?php echo " . $this->conReplace($matched_const, $loopLevel) . "; ?>";

                    continue;    // go to next loop step
                }

                // registered tags
                $found = FALSE;
                foreach (static::$registered_tags as $tags => $array) {
                    if (preg_match_all('/' . $array['parse'] . '/', $html, $matches)) {
                        $found = true;
                        $parsedCode .= "<?php echo call_user_func( static::\$registered_tags['$tags']['function'], " . var_export($matches, 1) . " ); ?>";
                    }
                }

                if (!$found) {
                    $parsedCode .= $html;
                }
            }
        }


        if ($isString) {
            if ($openIf > 0) {
                $trace = debug_backtrace();
                $caller = array_shift($trace);

                $e = new SyntaxException("Error! You need to close an {if} tag in the string, loaded by " . $caller['file'] . " at line " . $caller['line']);
                throw $e->templateFile($templateFilepath);
            }

            if ($loopLevel > 0) {
                $trace = debug_backtrace();
                $caller = array_shift($trace);
                $e = new SyntaxException("Error! You need to close the {loop} tag in the string, loaded by " . $caller['file'] . " at line " . $caller['line']);
                throw $e->templateFile($templateFilepath);
            }
        } else {
            if ($openIf > 0) {
                $e = new SyntaxException("Error! You need to close an {if} tag in " . $templateFilepath . " template");
                throw $e->templateFile($templateFilepath);
            }

            if ($loopLevel > 0) {
                $e = new SyntaxException("Error! You need to close the {loop} tag in " . $templateFilepath . " template");
                throw $e->templateFile($templateFilepath);
            }
        }

        $parsedCode = str_replace('?><?php', ' ', $parsedCode);

        return $parsedCode;
    }

    protected function varReplace(string $html, ?int $loopLevel = NULL, bool $escape = TRUE, bool $echo = FALSE): string
    {
        // change variable name if loop level
        if (!empty($loopLevel)) {
            $html = preg_replace(array('/(\$key)\b/', '/(\$value)\b/', '/(\$counter)\b/'), array('${1}' . $loopLevel, '${1}' . $loopLevel, '${1}' . $loopLevel), $html);
        }

        // if it is a variable. don't match modifier part (starting with |)
        if (preg_match_all('/(\$[a-z_A-Z][^\s]*)/', $html, $matches)) {
            // substitute . and [] with [" "]
            for ($i = 0; $i < count($matches[1]); $i++) {
                $rep = preg_replace('/\[(\${0,1}[a-zA-Z_0-9]*)\]/', '["$1"]', $matches[1][$i]);
                //$rep = preg_replace('/\.(\${0,1}[a-zA-Z_0-9]*)/', '["$1"]', $rep);
                $rep = preg_replace('/\.(\${0,1}[a-zA-Z_0-9]*(?![a-zA-Z_0-9]*(\'|\")))/', '["$1"]', $rep);
                $html = str_replace($matches[0][$i], $rep, $html);
            }

            // update modifier
            $html = $this->modifierReplace($html);

            // if does not initialize a value, e.g. {$a = 1}
            if (!preg_match('/\$.*=.*/', $html)) {
                // escape character
                if ($this->config['auto_escape'] && $escape) {
                    $html = "htmlspecialchars( $html, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, '" . $this->config['charset'] . "', FALSE )";
                }

                // if is an assignment it doesn't add echo
                if ($echo) {
                    $html = "echo " . $html;
                }
            }
        }

        return $html;
    }

    protected function conReplace(string $html): string
    {
        $html = $this->modifierReplace($html);
        return $html;
    }


    /**
     * Process variables and constants with modifiers
     * @access protected
     *
     * @param string $html : the html piece to process (the inside of {$variable|modifier|modifier:"params"})
     */
    protected function modifierReplace(string $html): string
    {
        $first_modifier_pos = strpos($html, '|');
        //	if (strpos($html,'|') !== false && substr($html,strpos($html,'|')+1,1) != "|")
        if (($first_modifier_pos !== false) && ($html[$first_modifier_pos + 1] != "|")) {
            $modifiers = substr($html, $first_modifier_pos);
            if ((strpos($modifiers, '"') === false) && (strpos($modifiers, "'") === false)) {
                // modifiers don't contain strings - use explode
                $res = explode("|", $html);
            } else {
                // at least one string appears to be present - use preg_split to not split inside param string values
                $res = preg_split('/\|+(?=(?:(?:[^"]*"){2})*[^"]*$)(?=(?:(?:[^\']*\'){2})*[^\']*$)/', $html, -1);
            }
            /*
            $res[0] = var name		$res[1] = modifier		$res[2] = modifier	...	$res[n] = modifier
            */
            $replacement = $res[0];

            $len = count($res);
            for ($i = 1; $i < $len; $i++) {
                $res[$i] = str_replace("::", "@double_dot@", $res[$i]);
                $parts = explode(":", $res[$i], 2);    // split function name and params
                $function = str_replace('@double_dot@', '::', $parts[0]);
                $params = isset($parts[1]) ? "," . $parts[1] : "";

                $replacement = $function . "(" . $replacement . $params . ")";
            }

            $html = $replacement;
        }

        return $html;
    }

    public static function reducePath(string $path): string
    {
        // reduce the path
        $path = str_replace("://", "@not_replace@", $path);
        $path = preg_replace("#(/+)#", "/", $path);
        $path = preg_replace("#(/\./+)#", "/", $path);
        $path = str_replace("@not_replace@", "://", $path);
        while (preg_match('#\w+\.\./#', $path)) {
            $path = preg_replace('#\w+/\.\./#', '', $path);
        }

        return $path;
    }
}
