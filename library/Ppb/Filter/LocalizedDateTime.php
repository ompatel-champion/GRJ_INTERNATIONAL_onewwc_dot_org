<?php
/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2019 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.1 [rev.8.1.01]
 */

/**
 * localized to mysql datetime filter
 */

namespace Ppb\Filter;

use Cube\Filter\AbstractFilter,
    Cube\Locale\DateTime as LocaleFormat;

class LocalizedDateTime extends AbstractFilter
{

    /**
     *
     * replace localized date format with mysql datetime format
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
                    $element = LocaleFormat::getInstance()->localizedToDateTime($element);
                }
            });

            return $value;
        }

        return LocaleFormat::getInstance()->localizedToDateTime($value);
    }
}