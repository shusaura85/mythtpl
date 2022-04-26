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

namespace MythTPL;

use Parser\Engine;
use Error\NotFoundException;


// require the built in modifiers
require_once __DIR__.'/Template/modifiers.php';

class MythTPL {

	const VERSION										= '1.0.0';

	// variables that should always be available - will remain even after calling reset()
	protected array				$persistent_data		= [];
	// variables
	protected array				$template_data			= [];

	protected string			$cache_dir				= 'cache/';
	protected string			$tpl_extension			= 'html';
	protected string			$tpl_dir				= 'templates/';
	protected bool				$debug					= false;
	
	protected bool				$tpl_tags_icase			= false;	// if the tags are case insensitive. will use the /i modifier in regex. doesn't apply to custom tags
	protected bool				$tpl_allow_php			= false;
	protected bool				$tpl_remove_comments	= false;
	
	protected bool				$tpl_autoescape			= false;
	protected string 			$tpl_autoescape_charset	= 'UTF-8';
	
	protected string			$config_checksum = '';
	
	// tags registered by the developers
	protected static array		$registered_tags		= [];
	
	protected ?Parser\Engine $parser = null;
	

	/**
	* Constructor
	*/
	public function __construct(?array $configuration = null)
		{
		if (is_array($configuration))
			{
			$this->set($configuration);
			}
		}
		
	
	/**
	* Checksum calculation - this string is used in compiled template file name
	*/
	protected function calculate_checksum():void
		{
		$this->config_checksum = serialize([
										$this->debug,
										$this->tpl_dir,
										$this->tpl_extension,
										$this->tpl_tags_icase,
										$this->tpl_allow_php,
										$this->tpl_remove_comments,
										$this->tpl_autoescape,
										$this->tpl_autoescape_charset
									]);
		}
	
	/**
	* Returns an array of configuration values needed by Parser\Engine
	*/
	protected function get_parser_config():array
		{
		return [
					'tpl_dir'			=> $this->tpl_dir,
					'cache_dir'			=> $this->cache_dir,
					'tags_icase'		=> $this->tpl_tags_icase,
					'allow_php'			=> $this->tpl_allow_php,
					'remove_comments'	=> $this->tpl_remove_comments,
					'auto_escape'		=> $this->tpl_autoescape,
					'charset'			=> $this->tpl_autoescape_charset
				];
		}
	
	
	
	/**
	 * Draw the template
	 *
	 * @param string $templateFilePath: name of the template file
	 * @param bool $toString: if the method should return a string
	 * or echo the output
	 *
	 * @return void, string: depending of the $toString
	 */
	public function draw(string $templateFilePath, bool $toString = false):string
		{
		extract($this->persistent_data);	// persistent variables (are not removed with $t->reset())
		extract($this->template_data);		// standard variables (are removed with $t->reset())

		ob_start();
		require $this->process_template($templateFilePath);
		$html = ob_get_clean();

		if ($toString)
			{ return $html; }
		else
			{
			echo $html;
			return '';
			}
		}

	/**
	 * Draw a string
	 *
	 * @param string $string: string in MythTpl format
	 * @param bool $toString: if the param
	 *
	 * @return void, string: depending of the $toString
	 */
	public function drawString(string $string, bool $toString = false):string
		{
		extract($this->persistent_data);	// persistent variables (are not removed with $t->reset())
		extract($this->template_data);		// standard variables (are removed with $t->reset())
		
		ob_start();
		require $this->process_string($string);
		$html = ob_get_clean();

		if ($toString)
			{ return $html; }
		else
			{
			echo $html;
			return '';
			}
		}


	/**
	 * Global configurations
	 *
	 * @param array $settings
	 * @return void
	 */
	public function set(array $settings):void
		{
		foreach ($settings as $key => $value)
			{
			switch (strtolower($key))
				{
				case 'cache_dir':	$this->set_cache_dir($value);
									break;
				case 'tpl_dir':		$this->set_tpl_dir($value);
									break;
				case 'tpl_ext':		$this->set_tpl_ext($value);
									break;
				case 'debug':		$this->set_debug($value);
									break;
				case 'tags_icase':	$this->set_tags_icase($value);
									break;
				case 'allow_php':	$this->set_tag_php($value);
									break;
				case 'remove_comments':	$this->tpl_remove_comments = (bool)$value;
									break;
				case 'auto_escape':	$this->set_tag_autoescape($value, ($settings['charset'] ?? 'UTF-8') );
									break;
				}
			}
		$this->calculate_checksum();
		}
	
	
	public function get_cache_dir():string	{	return $this->cache_dir;	}
	public function set_cache_dir(string $cache_dir = 'cache/'):self
		{
		$this->cache_dir = self::addTrailingSlash($cache_dir);
		$this->calculate_checksum();
		
		return $this;
		}
	
	public function get_tpl_dir():string	{	return $this->tpl_dir;	}
	public function set_tpl_dir(string $tpl_dir = 'templates/'):self
		{
		$this->tpl_dir = self::addTrailingSlash($tpl_dir);
		$this->calculate_checksum();
		
		return $this;
		}
	
	public function get_tpl_ext():string	{	return $this->tpl_extension;	}
	public function set_tpl_ext(string $tpl_ext = 'html'):self
		{
		$this->tpl_extension = $tpl_ext;
		$this->calculate_checksum();
		
		return $this;
		}
	
	public function get_debug():bool	{	return $this->debug;	}
	public function set_debug(bool $enabled = false):self
		{
		$this->debug = $enabled;
		$this->calculate_checksum();
		
		return $this;
		}
	
	public function get_tag_php():bool	{	return $this->tpl_allow_php;	}
	public function set_tag_php(bool $enabled = false):self
		{
		$this->tpl_allow_php = $enabled;
		$this->calculate_checksum();
		
		return $this;
		}
	
	public function get_tags_icase():bool	{	return $this->tpl_tags_icase;	}
	public function set_tags_icase(bool $enabled = false):self
		{
		$this->tpl_tags_icase = $enabled;
		$this->calculate_checksum();
		
		return $this;
		}
	
	public function get_tag_autoescape():bool	{	return $this->tpl_autoescape;	}
	public function set_tag_autoescape(bool $enabled = false, string $charset = 'UTF-8'):self
		{
		$this->tpl_autoescape = $enabled;
		$this->tpl_autoescape_charset = $charset;
		$this->calculate_checksum();
		
		return $this;
		}
	
	public function get_html_comments():bool	{	return $this->tpl_remove_comments;	}
	public function set_html_comments(bool $enabled = false):self
		{
		$this->tpl_remove_comments = $enabled;
		$this->calculate_checksum();
		
		return $this;
		}
	

	/**
	 * Assign persistent variables
	 * eg.     $t->p_assign(['name'=>'mike','age'=>29]);
	 *
	 * @param array $variables Name of template variable or associative array name/value
	 *
	 * @return self
	 */
	public function p_assign(array $variables):self
		{
		$this->persistent_data = $variables + $this->persistent_data;

		return $this;
		}
		
	
	/**
	 * Assign variable
	 * eg.     $t->assign('name','mickey');
	 *
	 * @param array $variable Name of template variable or associative array name/value
	 *
	 * @return self
	 */
	public function assign(array $variables):self
		{
		$this->template_data = $variables + $this->template_data;

		return $this;
		}
	
	
	/**
	 * Assign variable
	 * eg.     $t->assign('name','mickey');
	 *
	 * @param string $variable Name of template variable or associative array name/value
	 * @param mixed $value Value of variable to be assigned
	 * @param bool $is_persistent If the variable should be persistent or not
	 *
	 * @return self
	 */
	public function assign_var(string $variable, $value = null, bool $is_persistent = false):self
		{
		if ($is_persistent)
			{
			$this->persistent_data[$variable] = $value;
			}
		else
			{
			$this->template_data[$variable] = $value;
			}

		return $this;
		}
	
	
	/**
	 * Reset variables
	 * eg.     $t->reset();
	 *
	 * @param mixed $variable Name of template variable or associative array name/value
	 * @param mixed $value value assigned to this variable. Not set if variable_name is an associative array
	 *
	 * @return self
	 */
	public function reset():self
		{
		// clear assigned variables
		$this->template_data = [];

		return $this;
		}


	/**
	 * Clean the expired files from cache
	 * @param int $expireTime Set the expiration time
	 */
	public function clean(int $expireTime = 2592000):void
		{
		$files = glob($this->cache_dir . "*.mtpl.php");
		$time = time() - $expireTime;
		
		foreach ($files as $file)
			{
			if ($time > filemtime($file) )
				{
				unlink($file);
				}
			}
		}

	/**
	 * Allows the developer to register a tag.
	 *
	 * @param string $tag_name name for the custom tag
	 * @param string $parse_regex regular expression to parse the tag
	 * @param callable $function: action to do when the tag is parsed. can be anonymous function / function name / etc
	 */
	public static function registerTag(string $tag_name, string $parse_regex, callable $function):void
		{
		static::$registered_tags[$tag_name] = array("parse" => $parse_regex, "function" => $function);
		}


	/**
	 * Check if the template exist and compile it if necessary
	 *
	 * @param string $template: name of the file of the template
	 *
	 * @throw \MythTPL\Error\NotFoundException the file doesn't exists
	 * @return string: full filepath that php must use to include
	 */
	protected function process_template(string $template):string
		{
		// set filename
		$templateName = basename($template);
		$templateBasedir = (strpos($template, '/') !== false) ? dirname($template) . '/' : null;
		$templateDirectory = null;
		$templateFilepath = null;
		$parsedTemplateFilepath = null;

		$templateDirectory = $this->tpl_dir;

		$tpl_file_not_found = true;
		
		$tpl_cache_filename = str_replace("/", ".", $template);

		// absolute path
		if ($template[0] == '/')
			{
			$templateDirectory = $templateBasedir;
			$templateFilepath = $templateDirectory . $templateName . '.' . $this->tpl_extension;
			$parsedTemplateFilepath = $this->cache_dir . $tpl_cache_filename . "." . hash("crc32b", $templateDirectory . $this->config_checksum) . '.mtpl.php';
			// For check templates are exists
			if (file_exists($templateFilepath))
				{
				$tpl_file_not_found = false;
				}
			}
		else
			{
			$templateDirectory .= $templateBasedir;
			$templateFilepath = $templateDirectory . $templateName . '.' . $this->tpl_extension;
			$parsedTemplateFilepath = $this->cache_dir . $tpl_cache_filename . "." . hash("crc32b", $templateDirectory . $this->config_checksum) . '.mtpl.php';

			// For check templates are exists
			if (file_exists($templateFilepath))
				{
				$tpl_file_not_found = false;
				}
			}

		// if the template doesn't exsist throw an error
		if ($tpl_file_not_found)
			{
			$e = new Error\NotFoundException('Template ' . $templateName . ' not found!');
			throw $e->templateFile($templateFilepath);
			}

		// Compile the template if the original has been updated
		if ($this->debug || !file_exists($parsedTemplateFilepath) || ( filemtime($parsedTemplateFilepath) < filemtime($templateFilepath) ))
			{
			if (!($this->parser instanceof Parser\Engine))
				{
				$this->parser = new Parser\Engine($this->get_parser_config(), static::$registered_tags);
				}
			
			$this->parser->compileFile($templateName, $templateDirectory, $templateFilepath, $parsedTemplateFilepath);
			}
		return $parsedTemplateFilepath;
		}

	/**
	 * Compile a string if necessary
	 *
	 * @param string $string: MythTpl template string to compile
	 *
	 * @return string: full filepath that php must use to include
	 */
	protected function process_string(string $string):string
		{
		// set filename
		$templateName = md5($string . $this->config_checksum);
		$parsedTemplateFilepath = $this->cache_dir . $templateName . '.s.mtpl.php';
		$templateFilepath = '';


		// Compile the template if the original has been updated
		if ($this->debug || !file_exists($parsedTemplateFilepath))
			{
			if (!($this->parser instanceof Parser\Engine))
				{
				$this->parser = new Parser\Engine($this->get_parser_config(), static::$registered_tags);
				}
			
			$this->parser->compileString($templateName, $templateFilepath, $parsedTemplateFilepath, $string);
			}

		return $parsedTemplateFilepath;
		}

	private static function addTrailingSlash($folder)
		{
		if (is_array($folder))
			{
			foreach($folder as &$f)
				{
				$f = self::addTrailingSlash($f);
				}
			}
	//	elseif ( strlen($folder) > 0 && $folder[0] != '/' )
		elseif ( (strlen($folder) > 0) && (substr($folder,-1) != '/') )
			{
			$folder = $folder . "/";
			}
		return $folder;

		}

}
