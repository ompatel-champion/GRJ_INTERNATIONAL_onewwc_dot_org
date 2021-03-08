<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2020 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.2 [rev.8.2.01]
 */

/**
 * user details view helper class
 */
/**
 * MOD:- PICKUP LOCATIONS
 *
 * @version 1.1
 */

namespace Members\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Ppb\Db\Table\Row\User,
    Ppb\Db\Table\Row\UserAddressBook,
    Ppb\Service,
    Ppb\View\Helper\Icon as IconHelper;
## -- ONE LINE :: ADD -- [ MOD:- PICKUP LOCATIONS ]
use Ppb\Model\Shipping as ShippingModel;

class UserDetails extends AbstractHelper
{

    const STORE_DESC_MAX_CHARS = 255;
    /**
     *
     * user model
     *
     * @var \Ppb\Db\Table\Row\User
     */
    protected $_user;

    /**
     *
     * settings array
     *
     * @var array
     */
    protected $_settings;

    /**
     *
     * admin roles
     *
     * @var array
     */
    protected $_adminRoles;

    /**
     *
     * main method, only returns object instance
     *
     * @param int|string|\Ppb\Db\Table\Row\User $user
     *
     * @return $this
     */
    public function userDetails($user = null)
    {
        if ($user !== null) {
            $this->setUser($user);
        }

        return $this;
    }

    /**
     *
     * get user model
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function getUser()
    {
        if (!$this->_user instanceof User) {
            throw new \InvalidArgumentException("The user model has not been instantiated");
        }

        return $this->_user;
    }

    /**
     *
     * set user model
     *
     * @param int|string|\Ppb\Db\Table\Row\User $user
     * @param bool                              $setAddress
     *
     * @return $this
     */
    public function setUser($user, $setAddress = true)
    {
        if (is_int($user) || is_string($user)) {
            $userService = new Service\Users();
            $user = $userService->findBy('id', $user);
        }

        $this->_user = $user;

        if ($setAddress) {
            $this->setAddress();
        }

        return $this;
    }

    /**
     *
     * set guest user model
     *
     * @return $this
     */
    public function setGuestUser()
    {
        $usersService = new Service\Users();

        $columns = $usersService->getTable()->info('cols');

        $this->setUser(
            new User(array(
                'table' => $usersService->getTable(),
                'data'  => array_fill_keys($columns, null),
            )), false);

        return $this;
    }

    /**
     *
     * get & initialize settings array
     *
     * @return array
     */
    public function getSettings()
    {
        if (empty($this->_settings)) {
            $this->_settings = $this->getView()->get('settings');
        }

        return $this->_settings;
    }

    /**
     *
     * get admin roles
     *
     * @return array
     */
    public function getAdminRoles()
    {
        if (empty($this->_adminRoles)) {
            $this->_adminRoles = array_keys(Service\Users::getAdminRoles());
        }

        return $this->_adminRoles;
    }

    /**
     *
     * set an address from the user's address book, or the primary address if id = null
     *
     * @param int|null|\Ppb\Db\Table\Row\UserAddressBook $address the address (id, object or null for primary address)
     *
     * @return $this
     */
    public function setAddress($address = null)
    {
        $user = null;

        try {
            $user = $this->getUser();
        } catch (\Exception $e) {

        }

        if ($user instanceof User) {
            if (!$address instanceof UserAddressBook) {
                $address = $user->getAddress($address);
            }

            if ($address instanceof UserAddressBook) {
                $this->_setAddressFields($address);
            }
        }

        return $this;
    }

    /**
     *
     * set address from an array
     *
     * @param array $address
     *
     * @return $this
     */
    public function setAddressFromArray(array $address)
    {
        $addressBookService = new Service\UsersAddressBook();
        $addressFields = $addressBookService->getAddressFields();

        $address = array_filter($address);

        if (empty(array_diff($addressFields, array_keys($address)))) {
            $this->_setAddressFields($address);
        }
        else {
            // clear address
            $this->_setAddressFields(array_fill_keys($addressFields, null));
        }

        return $this;
    }

    /**
     *
     * display username
     *
     * @param bool $private if set to true, we have a private auction related display
     * @param bool $absolutePath
     *
     * @return string
     */
    public function display($private = false, $absolutePath = false)
    {
        $output = array();
        $reputationIcon = null;

        try {
            $user = $this->getUser();
        } catch (\Exception $e) {
            $translate = $this->getTranslate();

            return '<em>' . $translate->_('Account Deleted') . '</em>';
        }


        $username = $user->getData('username');

        if ($private) {

            $username = substr($username, 0, 1) . '****' . substr($username, -1);
        }

        /** @var \Ppb\View\Helper\Icon $iconHelper */
        $iconHelper = $this->getView()->icon($absolutePath, IconHelper::TYPE_FEATHER_IMG);

        if (in_array($user->getData('role'), $this->getAdminRoles())) {
            return '<span class="badge badge-primary">' . $username . '</span>';
        }
        else {
            $translate = $this->getTranslate();
            $settings = $this->getSettings();

            $output[] = $username;

            if ($user->isVerified(false)) {
                $output[] = $iconHelper->icon()->render('check-square', $translate->_('Verified User'), 'text-success');
            }

            if ($settings['enable_reputation']) {
                $reputationScore = $user->getReputationScore();

                $reputationLink = $this->getView()->url($user->reputationLink());

                foreach (Service\Reputation::$icons as $key => $icon) {
                    if ($reputationScore >= $key) {
                        if (is_array($icon)) {
                            $reputationIcon = ' ' . $iconHelper->icon()->render($icon['name'], $icon['title'], $icon['class']);
                        }
                        else {
                            $reputationIcon = ' ' . $icon;
                        }
                    }
                }

                $output[] = '<small>('
                    . ((!$private) ? '<a href="' . $reputationLink . '">' : '')
                    . $reputationScore . $reputationIcon
                    . ((!$private) ? '</a>' : '')
                    . ')</small>';
            }

            return implode(' ', $output);
        }
    }

    /**
     *
     * display user location
     *
     * @param bool         $detailed display state as well
     * @param string|false $implode  if false return array, otherwise return the imploded array
     *
     * @return string|array
     */
    public function location($detailed = true, $implode = ', ')
    {
        $location = array(
            ## -- ONE LINE :: ADD -- [ MOD:- PICKUP LOCATIONS ]
            'city'    => null,
            'state'   => null,
            'country' => null,
        );

        $user = $this->getUser();

        $translate = $this->getTranslate();

        $country = $this->_getLocationName($user->getData('country'));
        if ($country !== null) {
            $location['country'] = $translate->_($country);
        }

        if ($detailed === true) {
            $state = $this->_getLocationName($user->getData('state'));
            if ($state !== null) {
                $location['state'] = $translate->_($state);
            }

            ## -- START :: ADD -- [ MOD:- PICKUP LOCATIONS ]
            $city = $this->_getLocationName($user->getData('city'));
            if ($city !== null) {
                $location['city'] = $translate->_($city);
            }
            ## -- END :: ADD -- [ MOD:- PICKUP LOCATIONS ]
        }

        if ($implode === false) {
            return $location;
        }

        $location = array_filter(array_values($location));

        return (($output = implode(', ', array_reverse($location))) != '') ? $output : null;
    }

    /**
     *
     * display user account status
     *
     * @param bool $enhanced
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function status($enhanced = false)
    {
        $output = array();

        $user = $this->getUser();

        $translate = $this->getTranslate();

        if (
            isset($user['approved']) &&
            isset($user['mail_activated']) &&
            isset($user['preferred_seller']) &&
            isset($user['active']) &&
            isset($user['payment_status'])
        ) {
            $settings = $this->getSettings();

            if ($user['payment_status'] != 'confirmed') {
                $output[] = '<span class="badge badge-warning">' . $translate->_('Signup Fee Not Paid') . '</span>';
            }

            if (!$user['mail_activated']) {
                $output[] = '<span class="badge badge-secondary">' . $translate->_('Email Not Verified') . '</span>';
            }

            if (!$user['approved']) {
                $output[] = '<span class="badge badge-danger">' . $translate->_('Unapproved') . '</span>';
            }
            else if (!$user['active']) {
                $output[] = '<span class="badge badge-warning">' . $translate->_('Suspended') . '</span>';
            }
            else {
                $output[] = '<span class="badge badge-success">' . $translate->_('Active') . '</span>';
            }

            if (!in_array($user['role'], $this->getAdminRoles())) {
                if ($settings['private_site']) {
                    if ($user['is_seller']) {
                        $output[] = '<span class="badge badge-seller">' . $translate->_('Seller') . '</span>';
                    }
                    else {
                        $output[] = '<span class="badge badge-buyer">' . $translate->_('Buyer') . '</span>';
                    }
                }

                if ($user->userPaymentMode() == 'live') {
                    $output[] = '<span class="badge badge-live-mode">' . $translate->_('Live Payment') . '</span>';
                }
                else if ($user->userPaymentMode() == 'account') {
                    $output[] = '<span class="badge badge-account-mode">' . $translate->_('Account Mode') . '</span>';

                    $balance = $this->getView()->amount(abs($user['balance']), null, null, true);
                    if ($user['balance'] > 0) {
                        $output[] = '<span class="badge badge-danger">' . $balance . ' ' . $translate->_('Debit') . '</span>';
                    }
                    else {
                        $output[] = '<span class="badge badge-success">' . $balance . ' ' . $translate->_('Credit') . '</span>';
                    }
                }

                if ($user->isSeller()) {
                    if ($user['store_active']) {
                        if ($user['store_subscription_id']) {
                            $subscription = $user->findParentRow('\Ppb\Db\Table\StoresSubscriptions');
                            $storeAccount = ($subscription) ? $translate->_($subscription->getData('name')) : $translate->_('Unknown');
                        }
                        else {
                            $storeAccount = $translate->_('Default');
                        }

                        $description = $storeAccount . ' ' . $translate->_('Store');
                        if ($user['store_next_payment'] > 0) {
                            $description .= ' ' . $translate->_('until') . ' ' . $this->getView()->date($user['store_next_payment'],
                                    true);
                        }
                        $output[] = '<span class="badge badge-store-info">' . $description . ' </span>';

                    }
                    else if ($settings['enable_stores'] && $settings['force_stores'] && $enhanced) {
                        $output[] = '<span class="badge badge-store-info">'
                            . sprintf($translate->_('Open a store if listing > %s items'), $settings['force_stores']) . '</span>';
                    }
                }

                if ($settings['user_verification']) {
                    if ($user->isVerified()) {
                        $description = $translate->_('Verified');
                        if ($user['user_verified_next_payment'] > 0) {
                            $description .= ' ' . $translate->_('until') . ' ' . $this->getView()->date($user['user_verified_next_payment'],
                                    true);
                        }
                        $output[] = '<span class="badge badge-verified">' . $description . '</span>';
                    }
                    else {
                        $output[] = '<span class="badge badge-danger">' . $translate->_('Not Verified') . '</span>';
                    }
                }

                if ($user['preferred_seller']) {
                    $description = $translate->_('Preferred Seller');
                    if ($user['preferred_seller_expiration'] > 0) {
                        $description .= ' ' . $translate->_('until') . ' ' . $this->getView()->date($user['preferred_seller_expiration'],
                                true);
                    }
                    $output[] = '<span class="badge badge-preferred">' . $description . '</span>';
                }
            }
            else {
                $output[] = '<span class="badge badge-primary">' . $user['role'] . '</span>';
            }

            return implode(' ', $output);
        }
        else {
            throw new \InvalidArgumentException("The user object must include values for
                'preferred_seller', 'approved', 'mail_activated' and 'active' keys.");
        }
    }

    /**
     *
     * display the full name of the user
     *
     * @return string
     */
    public function displayFullName()
    {
        $user = $this->getUser();

        $fullName = trim($user['name']['first'] . ' ' . $user['name']['last']);

        return (!empty($fullName)) ? $fullName : $user['username'];
    }

    /**
     *
     * display the user's address
     *
     * @param string $separator
     *
     * @return string
     */
    public function displayAddress($separator = '<br>')
    {
        try {
            $user = $this->getUser();
        } catch (\Exception $e) {
            $translate = $this->getTranslate();

            return '<em>' . $translate->_('Account Deleted') . '</em>';
        }

        $location = $this->location(true, false);

        $settings = $this->getSettings();

        $address = array();

        $address[] = $user->getData('address');
        ## -- ONE LINE :: CHANGE -- [ MOD:- PICKUP LOCATIONS ]
        $address[] = $location['city'];

        if ($settings['address_display_format'] == 'default') {
            $address[] = $user->getData('zip_code');
            $address[] = $location['state'];
        }
        else {
            $address[] = $location['state'];
            $address[] = $user->getData('zip_code');
        }

        $address[] = $location['country'];

        return implode($separator, array_filter($address));
    }

    /**
     *
     * display the user's full address (includes full name and phone, used for invoices etc)
     * @8.2 include company name as well, if business account
     *
     * @param string $separator
     *
     * @return string
     */
    public function displayFullAddress($separator = ', ')
    {
        try {
            $user = $this->getUser();
        } catch (\Exception $e) {
            $translate = $this->getTranslate();

            return '<em>' . $translate->_('Account Deleted') . '</em>';
        }

        $settings = $this->getSettings();

        $translate = $this->getTranslate();

        $address = $this->displayAddress($separator);

        if (!empty($address)) {
            $display = '';
            $businessAddress = !empty($user['business_account']) && $user['business_account'];

            if ($businessAddress) {
                $companyName = (!empty($user['company_name'])) ? $user['company_name'] : '';
                $display .= '<div class="mb-2">'
                    . '<strong>' . $companyName . '</strong>'
                    . '</div>';
            }

            $fullName = $this->displayFullName();
            $fullName = ($businessAddress) ?
                '<em>' . $fullName . '</em>' : '<strong>' . $fullName . '</strong>';

            $display .= $fullName
                . ' '
                . '<br>'
                . $this->displayAddress($separator)
                . ' '
                . '<br>'
                . ($settings['sale_phone_numbers'] && (!empty($user['phone'])) ? '<small>' . $translate->_('Phone:') . ' ' . $user['phone'] . '</small>' : '');

            return $display;
        }

        return null;
    }

    /**
     *
     * get the user's store description and display either in full html format, or in short format,
     * with the html stripped out
     *
     * @param bool $full
     *
     * @return string
     */
    public function storeDescription($full = true)
    {
        $storeSettings = $this->getUser()->getStoreSettings();
        $output = $this->getView()->renderHtml(
            ((!empty($storeSettings['store_description'])) ? $storeSettings['store_description'] : null), false, false);

        if ($full !== true) {
            $output = strip_tags($output);
            if (strlen($output) > self::STORE_DESC_MAX_CHARS) {
                $output = substr($output, 0, self::STORE_DESC_MAX_CHARS) . ' ... ';
            }
        }

        return $output;
    }

    /**
     *
     * display seller vacation mode message
     *
     * @return bool|string
     */
    public function vacationMode()
    {
        try {
            $user = $this->getUser();
        } catch (\Exception $e) {
            return false;
        }

        if ($user->isVacation()) {
            $translate = $this->getTranslate();

            $returnDate = $user->getGlobalSettings('vacation_mode_return_date');
            $relatedInformation = $user->getGlobalSettings('vacation_mode_related_information');

            if (!empty($returnDate)) {
                $output = sprintf($translate->_('The seller is in vacation until %s.'), $this->getView()->date($returnDate, true));
            }
            else {
                $output = $translate->_('The seller is currently in vacation.');
            }

            if (!empty($relatedInformation)) {
                $output .= '<hr>' . $relatedInformation;
            }

            return $output;
        }

        return false;
    }

    ## -- START :: ADD -- [ MOD:- PICKUP LOCATIONS ]
    public function displayClosestPickupLocation()
    {
        $user = $this->getUser();
        $translate = $this->getTranslate();

        if (!empty($user['country'])) {
            $shippingModel = new ShippingModel($user);
            $shippingModel->setAddress($user->getAddress()->toArray())
                ->setLocationId($user['country'])
                ->setPostCode($user['zip_code']);

            $pickupLocations = $shippingModel->getStorePickupLocations();

            if (count($pickupLocations) > 0) {
                /** @var \Ppb\Db\Table\Row\StorePickupLocation $closestLocation */
                $closestLocation = $pickupLocations[0];

                return $translate->_('Closest Pickup Location:')
                    . ' '
                    . $closestLocation->display(true) . ' - ' . round($closestLocation->getData('distance'), 1) . ' km away';
            }
        }

        return null;
    }
    ## -- END :: ADD -- [ MOD:- PICKUP LOCATIONS ]

    /**
     *
     * get the name of a location based on its id
     *
     * @param int|string $location
     * @param string     $key
     *
     * @return array|null|string
     */
    protected function _getLocationName($location, $key = 'name')
    {
        if (empty($location)) {
            return null;
        }
        if (is_numeric($location)) {
            $locations = new Service\Table\Relational\Locations();
            $row = $locations->findBy('id', (int)$location);
            if ($row != null) {
                $location = $row->getData($key);
            }
        }

        return $location;
    }

    /**
     *
     * set selected address fields in user model
     *
     * @param mixed $address
     *
     * @return $this
     */
    protected function _setAddressFields($address)
    {
        foreach ($address as $key => $value) {
            if ($key != 'id') {
                $this->_user[$key] = $value;
            }
        }

        return $this;
    }
}

