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
 * classified setup fee class
 */
/**
 * MOD:- ADVANCED CLASSIFIEDS
 */

namespace Ppb\Service\Fees;

use Ppb\Db\Table\Row\Listing as ListingModel;

class ClassifiedSetup extends ListingSetup
{


    /**
     *
     * standard fees
     *
     * @var array
     */
    protected $_fees = array(
        self::CLASSIFIED_SETUP => 'Classified Setup',
        self::HPFEAT           => 'Home Page Featuring',
        self::CATFEAT          => 'Category Pages Featuring',
        self::HIGHLIGHTED      => 'Highlighted Item',
        self::IMAGES           => 'Listing Images',
        self::MEDIA            => 'Listing Media',
        self::ADDL_CATEGORY    => 'Additional Category Listing',
    );

    /**
     *
     * fee type = reverse
     *
     * @var string
     */
    protected $_type = 'classified';


    /**
     *
     * class constructor
     *
     * @param \Ppb\Db\Table\Row\Listing             $listing listing object
     * @param integer|string|\Ppb\Db\Table\Row\User $user    the user that will be paying
     */
    public function __construct(ListingModel $listing = null, $user = null)
    {
        parent::__construct($listing, $user);

        if ($listing !== null) {
            $this->setAmount($listing['buyout_price']);
        }
    }


    /**
     *
     * check whether to apply the requested fee
     * do not apply any fees if the listing is a draft
     * classified setup fee is only charged on initial setup
     *
     * @param string $name the name of the fee
     *
     * @return bool
     */
    protected function _applyFee($name)
    {
        if ($this->_listing['draft']) {
            return false;
        }
        else if (in_array($name, $this->_feesTiers) && (!$this->_savedListing instanceof ListingModel)) {
            return true;
        }
        else if (($name == self::CLASSIFIED_SETUP) && (!$this->_savedListing instanceof ListingModel)) {
            return true;
        }
        else if (
            ($this->_listing[$name] && !$this->_savedListing[$name]) ||
            ($this->_listing['category_id'] != $this->_savedListing['category_id'])
        ) {
            $val = $this->_listing[$name];
            if (is_numeric($val)) {
                $val = floatval($val);
            }

            return (!empty($val)) ? true : false;
        }

        return false;
    }
}

