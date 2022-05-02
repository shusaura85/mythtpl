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

namespace MythTPL\Template;

/**
 * These are the built-in modifier functions used by MythTPL
 * To use functions as modifiers, the functions created must exist in the MythTPL\Template namespace or in the global \ namespace
 */


// escape the specified text using htmlspecialchars (by default uses UTF-8). You can optionally specify the encoding
// example: {$variable|escape} {$variable|escape:'ISO-8859-1'}
function escape(string $text, string $use_encoding = 'UTF-8'): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, $use_encoding, false);
}

// format a number to 2 decimals (or how many you specify)
// {$number|nice_num} {$number|nice_num:2}
function nicenum($str, int $decimals = 2)
{
    return number_format($str, $decimals, '.', '');
}







