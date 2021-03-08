<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2018 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     2.0 [rev.2.0.02]
 */

/**
 * processes an input value and renders it as forced text
 * applies nl2br as well
 */

namespace Cube\View\Helper;

class RenderText extends AbstractHelper
{

    /**
     *
     * output formatted string
     *
     * @param string $string
     * @param bool   $nl2br
     * @param int    $maxChars
     *
     * @return string
     */
    public function renderText($string, $nl2br = false, $maxChars = null)
    {
        $output = trim(str_ireplace(
            array("'", '"', '<', '>'), array('&#039;', '&quot;', '&lt;', '&gt;'),
            strip_tags(stripslashes(rawurldecode($string)))));

        if ($maxChars !== null) {
            $length = strlen($output);
            $output = substr($output, 0, $maxChars) . (($length > $maxChars) ? '...' : '');
        }

        return ($nl2br) ? nl2br($output) : $output;
    }

}

