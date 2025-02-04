<?php
// vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker:
// +--------------------------------------------------------------------+
// |                       BIFE - Buil It FastEr                        |
// +--------------------------------------------------------------------+
// | This file is part of BIFE.                                         |
// |                                                                    |
// | BIFE is free software; you can redistribute it and/or modify it    |
// | under the terms of the GNU General Public License as published by  |
// | the Free Software Foundation; either version 2 of the License, or  |
// | (at your option) any later version.                                |
// |                                                                    |
// | BIFE is distributed in the hope that it will be useful, but        |
// | WITHOUT ANY WARRANTY; without even the implied warranty of         |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU   |
// | General Public License for more details.                           |
// |                                                                    |
// | You should have received a copy of the GNU General Public License  |
// | along with Hooks; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA      |
// +--------------------------------------------------------------------+
// | Created: Wed May 17 18:16:54 ART 2003                              |
// | Authors: Leandro Lucarella <luca@lugmen.org.ar>                    |
// +--------------------------------------------------------------------+
//
// $Id$
//

// Inicialization {{{
umask('002');
require_once 'HTML/Template/HIT.php';
require_once 'BIFE/Parser.php';
require_once 'BIFE/Translate.php';
// }}}

// Selects the file to view {{{
if (@$_SERVER['PATH_INFO']) {
    $file = $_SERVER['PATH_INFO'];
    // If it ends with a slash it's a directory so we add index.xbf.
    if (substr($_SERVER['PATH_INFO'], strlen($_SERVER['PATH_INFO'])-1) == '/') {
        $file .= 'index.xbf';
    }
} else {
    $file = 'index.xbf';
}
// }}}

// Looks if we want to show the source {{{
if (@$_REQUEST['S']) {
    if (@$_REQUEST['B']) {
        // We want to see the BIFE file source.
        exec('enscript -q -p - -Ehtml --language=html --color '
            . escapeshellarg($file), $buffer);
        $buffer = join("\n", $buffer);
        $buffer = strstr($buffer, '<PRE>');
        $buffer = substr($buffer, 0, strpos($buffer, '</PRE>'));
        $buffer = str_replace(array('<FONT COLOR="', '</FONT>'),
            array('<SPAN style="color: ', '</SPAN>'), $buffer);
        echo $buffer;
        #echo htmlentities(join('', file($file)));
    } else {
        // We want to see the php file source.
        highlight_file($_SERVER['SCRIPT_FILENAME']);
    }
    exit;
}
// }}}

// If we are not looking at the source, we use BIFE to show the page {{{
$template =& new HTML_Template_HIT('templates');
$parser   =& new BIFE_Parser('BIFE_Translate');
$page     =& $parser->parseFile($file);
$parser->__destruct();
// We now see if we want to show the HTML output
if (@$_REQUEST['H']) {
        $f = fopen("$file.tmp.html", 'w');
        fputs($f, $page->render($template));
        fclose($f);
        exec('enscript -q -p - -Ehtml --language=html --color '
            . escapeshellarg("$file.tmp.html"), $buffer);
        unlink("$file.tmp.html");
        $buffer = join("\n", $buffer);
        $buffer = strstr($buffer, '<PRE>');
        $buffer = substr($buffer, 0, strpos($buffer, '</PRE>'));
        $buffer = str_replace(array('<FONT COLOR="', '</FONT>'),
            array('<SPAN style="color: ', '</SPAN>'), $buffer);
        echo $buffer;
    //echo '<PRE>' . htmlentities($page->render($template)) . '</PRE>';
} else {
    echo $page->render($template);
}
// }}}

?>
