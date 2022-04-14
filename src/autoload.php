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

if(!defined("BASE_DIR"))
	define("BASE_DIR", __DIR__);

// register the autoloader
spl_autoload_register( "MythTplAutoloader" );


// autoloader
function MythTplAutoloader( $class )
	{
	// it only autoload class into the MythTPL scope
	if (strpos($class,'MythTPL\\') !== false)
		{
		// remove first part of namespace
		$class = substr($class, 8);

		// transform the namespace in path
		$path = str_replace("\\", DIRECTORY_SEPARATOR, $class );
		
		// filepath
		$abs_path = BASE_DIR . DIRECTORY_SEPARATOR . $path . ".php";
	
		if (is_file($abs_path))
		// require the file
		require_once $abs_path;
		}

	}
