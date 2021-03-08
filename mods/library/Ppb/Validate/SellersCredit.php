<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */
/**
 * sellers credit payment validator - used in purchase/checkout forms to check
 * for necessary balance for mandatory payments
 */
/**
 * MOD:- SELLERS CREDIT
 */

namespace Ppb\Validate;

use Cube\Controller\Front,
    Cube\Validate\AbstractValidate,
    Ppb\Db\Table\Row\User as UserModel;

class SellersCredit extends AbstractValidate
{

    const NO_BUYER = 1;
    const BUYER_NO_ACCOUNT_MODE = 2;
    const BUYER_NOT_ENOUGH_CREDIT = 3;

    protected $_messages = array(
        self::NO_BUYER                => "You must be logged in to proceed.",
        self::BUYER_NO_ACCOUNT_MODE   => "Your account must be in account mode.",
        self::BUYER_NOT_ENOUGH_CREDIT => "You must have at least %value% credit in your account balance to proceed. <br><br> <a href=\"%link%\" class='btn btn-primary'>Top Up Now</a>"
    );

    /**
     *
     * buyer model
     *
     * @var \Ppb\Db\Table\Row\User
     */
    protected $_buyer;

    /**
     *
     * currency code
     *
     * @var string
     */
    protected $_currency;


    /**
     *
     * get buyer model
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function getBuyer()
    {
        return $this->_buyer;
    }

    /**
     *
     * set buyer model
     *
     * @param \Ppb\Db\Table\Row\User $buyer
     *
     * @return $this
     */
    public function setBuyer($buyer)
    {
        $this->_buyer = $buyer;

        return $this;
    }

    /**
     *
     * get currency code
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->_currency;
    }

    /**
     *
     * set currency code
     *
     * @param string $currency
     *
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->_currency = $currency;

        return $this;
    }


    /**
     *
     * check validator
     *
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $buyer = $this->getBuyer();
        $currency = $this->getCurrency();

        if (!$buyer) {
            $this->setMessage($this->_messages[self::NO_BUYER]);

            return false;
        }
        else if ($buyer->userPaymentMode() != 'account') {
            $this->setMessage($this->_messages[self::BUYER_NO_ACCOUNT_MODE]);

            return false;
        }
        else if (($missingAmount = $buyer->canPaySellersCredit($this->_value, $currency)) !== true) {
            $view = Front::getInstance()->getBootstrap()->getResource('view');
            $amount = $view->amount($this->_value, $currency);
            $link = $view->url(array('module' => 'app', 'controller' => 'payment', 'action' => 'credit-balance', 'amount' => $missingAmount));

            $this->setMessage(
                str_replace(array('%value%', '%link%'), array($amount, $link), $this->_messages[self::BUYER_NOT_ENOUGH_CREDIT]));

            return false;
        }

        return true;


    }

}

