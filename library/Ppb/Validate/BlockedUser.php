<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.01]
 */

/**
 * blocked user validator class
 */

namespace Ppb\Validate;

use Cube\Validate\AbstractValidate,
    Ppb\Service\BlockedUsers as BlockedUsersService,
    Ppb\Db\Table\Row\BlockedUser as BlockedUserModel;

class BlockedUser extends AbstractValidate
{
    const USERNAME = 'username';
    const EMAIL = 'email';

    /**
     *
     * block action
     *
     * @var string
     */
    protected $_blockAction = BlockedUserModel::ACTION_REGISTER;

    /**
     *
     * blocker id
     *
     * @var int
     */
    protected $_blockerId = null;

    /**
     *
     * input variables
     *
     * @var array
     */
    protected $_variables = array();

    /**
     *
     * class constructor
     *
     * initialize the required settings
     *
     * @param array $data       data[0] -> variables;
     *                          data[1] -> blocker id;
     */
    public function __construct(array $data = null)
    {
        if (isset($data[0])) {
            $this->setVariables($data[0]);
        }
        if (isset($data[1])) {
            $this->setBlockerId($data[1]);
        }
    }

    /**
     *
     * get block action
     *
     * @return string
     */
    public function getBlockAction()
    {
        return $this->_blockAction;
    }

    /**
     *
     * set block action
     *
     * @param string $blockAction
     *
     * @return $this
     */
    public function setBlockAction($blockAction)
    {
        $this->_blockAction = $blockAction;

        return $this;
    }

    /**
     *
     * get blocker id
     *
     * @return int
     */
    public function getBlockerId()
    {
        return $this->_blockerId;
    }

    /**
     *
     * set blocker id
     *
     * @param int $blockerId
     *
     * @return $this
     */
    public function setBlockerId($blockerId)
    {
        $this->_blockerId = $blockerId;

        return $this;
    }

    /**
     *
     * get variables
     *
     * @return array
     */
    public function getVariables()
    {
        return $this->_variables;
    }

    /**
     *
     * set variables
     *
     * @param array $variables
     *
     * @return $this
     */
    public function setVariables($variables)
    {
        $this->_variables = $variables;

        return $this;
    }


    /**
     *
     * checks if the the user is blocked, return true otherwise
     *
     * @return bool
     */
    public function isValid()
    {
        $variables = $this->getVariables();

        $username = (!empty($variables[self::USERNAME])) ? $variables[self::USERNAME] : null;
        $email = (!empty($variables[self::EMAIL])) ? $variables[self::EMAIL] : null;

        $blockedUsersService = new BlockedUsersService();
        $blockedUser = $blockedUsersService->check(
            $this->getBlockAction(),
            array(
                'ip'       => $_SERVER['REMOTE_ADDR'],
                'username' => $username,
                'email'    => $email,
            ), $this->getBlockerId());

        if ($blockedUser !== null) {
            $this->setMessage($blockedUser->blockMessage());

            return false;
        }

        return true;
    }

}

