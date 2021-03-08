<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2019 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.01]
 */

/**
 * listing bid / buy / make offer / add to cart form
 */

namespace Listings\Form;

use Ppb\Form\AbstractBaseForm,
    Ppb\Db\Table\Row\User as UserModel,
    Ppb\Db\Table\Row\Listing as ListingModel,
    Ppb\Model\Elements;

class Purchase extends AbstractBaseForm
{

    /**
     * submit buttons
     */
    const BTN_PLACE_BID = 'btn_place_bid';
    const BTN_BUY_OUT = 'btn_buy_out';
    const BTN_MAKE_OFFER = 'btn_make_offer';
    const BTN_ADD_TO_CART = 'btn_add_to_cart';

    /**
     * button that will require confirmation
     */
    const BTN_CONFIRM = 'btn_confirm';

    /**
     * form types
     */
    const FORM_TYPE_BID = 'bid';
    const FORM_TYPE_BUY = 'buy';
    const FORM_TYPE_OFFER = 'offer';
    const FORM_TYPE_CART = 'cart';

    /**
     *
     * form types array
     *
     * @var array
     */
    public static $formTypes = array(
        self::FORM_TYPE_BID,
        self::FORM_TYPE_BUY,
        self::FORM_TYPE_OFFER,
        self::FORM_TYPE_CART,
    );

    /**
     *
     * submit buttons values
     *
     * @var array
     */
    protected $_buttons = array(
        self::BTN_PLACE_BID   => 'Place Bid',
        self::BTN_BUY_OUT     => 'Buy Out',
        self::BTN_MAKE_OFFER  => 'Make Offer',
        self::BTN_ADD_TO_CART => 'Add to Cart',
    );

    /**
     *
     * listing model
     *
     * @var \Ppb\Db\Table\Row\Listing
     */
    protected $_listing;

    /**
     *
     * form type
     *
     * @var string
     */
    protected $_type;

    /**
     *
     * details page flag
     *
     * @var bool
     */
    protected $_details = false;

    /**
     *
     * do not add submit button by setData method
     *
     * @var bool
     */
    protected $_addSubmitButton = false;

    /**
     *
     * class constructor
     *
     * @param mixed                     $formId  the type of form to be created (bid|buy|offer|cart)
     * @param \Ppb\Db\Table\Row\Listing $listing listing model
     * @param \Ppb\Db\Table\Row\User    $buyer   buyer user model
     * @param string                    $action  the form's action
     */
    public function __construct($formId, ListingModel $listing, UserModel $buyer = null, $action = null)
    {
        parent::__construct($action);

        $translate = $this->getTranslate();

        $title = $translate->_('Confirm Purchase');

        if (!is_array($formId)) {
            switch ($formId) {
                case 'bid':
                    $title = $translate->_('Confirm Bid');
                    break;
                case 'buy':
                    $title = $translate->_('Confirm Purchase');
                    break;
                case 'offer':
                    $title = $translate->_('Confirm Offer');
                    break;
                case 'cart':
                    $title = $translate->_('Add to Cart');
                    break;
            }
        }
        $this->setTitle($title);

        $this->setMethod(self::METHOD_POST);

        $includedForms = $this->getIncludedForms();

        $this->setIncludedForms(
            array_merge($includedForms, (array)$formId));

        $this->setListing($listing);

        $model = new Elements\Purchase($formId);
        $model->setListing($listing)
            ->setBuyer($buyer);

        $this->setModel($model);

        $this->addElements(
            $this->getModel()->getElements());

        if (count($this->getElements()) > 0) {
            $this->setPartial('forms/purchase.phtml');
        }
    }

    /**
     *
     * get listing object
     *
     * @return \Ppb\Db\Table\Row\Listing
     */
    public function getListing()
    {
        return $this->_listing;
    }

    /**
     *
     * set listing object
     *
     * @param \Ppb\Db\Table\Row\Listing $listing
     *
     * @return $this
     */
    public function setListing($listing)
    {
        $this->_listing = $listing;

        return $this;
    }

    /**
     *
     * get post type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     *
     * set post type
     *
     * @param string $postType
     *
     * @return $this
     */
    public function setType($postType)
    {
        $this->_type = $postType;

        return $this;
    }

    /**
     *
     * get details flag
     *
     * @return bool
     */
    public function isDetails()
    {
        return $this->_details;
    }

    /**
     *
     * set details flag
     *
     * @param bool $details
     *
     * @return $this
     */
    public function setDetails($details)
    {
        $this->_details = $details;

        return $this;
    }

    /**
     *
     * set form data
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data = null)
    {
        parent::setData($data);

        if (!empty($data['product_attributes'])) {
            /** @var \Cube\Form\Element $element */
            foreach ($this->_elements as $element) {
                $elementName = $element->getName();
                if (0 === strpos($elementName, 'product_attributes')) {
                    $id = preg_replace("/[^0-9]/", "", $elementName);
                    if (!empty($data['product_attributes'][$id])) {
                        $element->setData($data['product_attributes'][$id]);
                    }
                }
            }
        }

        return $this;
    }

    /**
     *
     * checks if the form is valid
     * will use as a validator the canPurchase method too.
     *
     * @return bool
     */
    public function isValid()
    {
        $valid = true;

        $listing = $this->getListing();
        $type = $this->getType();

        $canPurchase = $listing->canPurchase($type);

        if ($canPurchase !== true) {
            $valid = false;
            $this->setMessage($canPurchase);
        }

        $elementsValid = parent::isValid();


        return ($valid && $elementsValid);
    }
}