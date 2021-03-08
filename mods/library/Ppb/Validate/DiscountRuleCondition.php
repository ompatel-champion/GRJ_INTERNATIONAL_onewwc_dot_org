<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */
/**
 * discount rule condition validator class
 */
/**
 * MOD:- DISCOUNT RULES
 */

namespace Ppb\Validate;

use Cube\Validate\AbstractValidate;

class DiscountRuleCondition extends AbstractValidate
{

    protected $_message = "'%s' does not contain a valid set of rules. Rule error: %value%.";

    /**
     *
     * checks if the variable contains a valid set of conditions
     *
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $value = $this->getValue();

        if (!empty($value)) {
            if (preg_match_all('/\(/', $value) != preg_match_all('/\)/', $value)) {
                $this->_message = str_replace('%value%', 'invalid parentheses count', $this->_message);

                return false;
            }

            $nbSingleQuotes = preg_match_all('/\'/', $value);
            if (($nbSingleQuotes % 2) != 0) {
                $this->_message = str_replace('%value%', 'there must be an even number of single quotes in the condition', $this->_message);

                return false;
            }

            $nbPercentChars = preg_match_all('/\%/', $value);
            if (($nbSingleQuotes % 2) != 0) {
                $this->_message = str_replace('%value%', 'there must be an even number of single quotes in the condition', $this->_message);

                return false;
            }

            $array = preg_split("/ (AND|OR) /", $value);

            foreach ($array as $value) {
                if ($this->_checkSingleCondition($value) == false) {
                    $this->_message = str_replace('%value%', '<code>' . $value . '</code> - the syntax is invalid', $this->_message);

                    return false;
                }
            }
        }

        return true;
    }

    /**
     *
     * return formatted value with no html chars
     *
     * @return string
     */
    public function getValue()
    {
        return str_ireplace(
            array('&amp;', '&#039;', '&quot;', '&lt;', '&gt;', '&nbsp;'), array('&', "'", '"', '<', '>', ' '), $this->_value);

    }
    /**
     *
     * checks a single condition and returns true if its valid and false otherwise
     *
     * @param string $input
     *
     * @return bool
     */
    protected function _checkSingleCondition($input)
    {
        $input = trim($input);

        if (empty($input)) {
            return false;
        }

        $result = preg_match("/(userId|listingId|purchasedListingId|price|name|description)\s?(=|!=|<|<=|>|>=|IN|NOT IN|LIKE)\s?\(?\'.+\'\)?/", $input);

        return ($result > 0) ? true : false;
    }
}

