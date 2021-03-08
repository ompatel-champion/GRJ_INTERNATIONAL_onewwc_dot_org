<?php
/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2017 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.9 [rev.7.9.01]
 */
/**
 * localized numeric number filter
 */

namespace Ppb\Filter;

use Cube\Filter\AbstractFilter,
    Cube\Locale\Format as LocaleFormat;

class LocalizedNumeric extends AbstractFilter
{

    /**
     *
     * replace a localized numeric format with a standard number
     * accepts arrays as well, and will convert to numeric each localized numeric value
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function filter($value)
    {
        if (is_array($value)) {
            array_walk_recursive($value, function (&$element) {
                if (!is_array($element)) {
                    $element = LocaleFormat::getInstance()->localizedToNumeric($element, true);
                }
            });

            return $value;
        }

        return LocaleFormat::getInstance()->localizedToNumeric($value, true);
    }
} 