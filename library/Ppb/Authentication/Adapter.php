<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2019 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.1 [rev.8.1.02]
 */

/**
 * authentication adapter
 */

namespace Ppb\Authentication;

use Cube\Authentication\Adapter\AdapterInterface,
    Cube\Authentication\Result as AuthenticationResult,
    Cube\Translate,
    Cube\Translate\Adapter\AbstractAdapter as TranslateAdapter,
    Cube\Controller\Front,
    Cube\Db\Expr,
    Ppb\Service\Users as UsersService,
    Ppb\Service\BlockedUsers as BlockedUsersService,
    Ppb\Db\Table\Row\BlockedUser as BlockedUserModel,
    Ppb\Db\Table\Row\User as UserModel;

class Adapter implements AdapterInterface
{

    /**
     *
     * whether to check old v6.x passwords
     */
    const V6_HASHES = true;

    /**
     *
     * user id
     *
     * @var int
     */
    protected $_id = null;
    /**
     *
     * username
     *
     * @var string
     */
    protected $_username = null;

    /**
     *
     * password
     *
     * @var string
     */
    protected $_password = null;

    /**
     *
     * email address
     *
     * @var string
     */
    protected $_email = null;

    /**
     *
     * allowed roles
     *
     * @var array
     */
    protected $_allowedRoles = array();

    /**
     *
     * denied roles
     *
     * @var array
     */
    protected $_deniedRoles = array();

    /**
     *
     * check for blocked user / ip
     *
     * @var bool
     */
    protected $_checkBlockedUser = true;

    /**
     *
     * translate adapter
     *
     * @var \Cube\Translate\Adapter\AbstractAdapter
     */
    protected $_translate;

    public function __construct($params = array(), $id = null, $allowedRoles = array(), $deniedRoles = array())
    {
        if (array_key_exists('username', $params)) {
            $this->setUsername(
                $params['username']);
        }
        if (array_key_exists('password', $params)) {
            $this->setPassword(
                $params['password']);
        }
        if (array_key_exists('email', $params)) {
            $this->setEmail(
                $params['email']);
        }

        $this->setId($id)
            ->setAllowedRoles($allowedRoles)
            ->setDeniedRoles($deniedRoles);
    }

    /**
     *
     * get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     *
     * set id
     *
     * @param int $id
     *
     * @return $this;
     */
    public function setId($id)
    {
        $this->_id = $id;

        return $this;
    }

    /**
     *
     * get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->_username;
    }

    /**
     *
     * set username
     *
     * @param string $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->_username = $username;

        return $this;
    }

    /**
     *
     * get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     *
     * set password
     *
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->_password = $password;

        return $this;
    }

    /**
     *
     * get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->_email;
    }

    /**
     *
     * set email
     *
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->_email = $email;

        return $this;
    }

    /**
     *
     * get allowed roles
     *
     * @return array
     */
    public function getAllowedRoles()
    {
        return $this->_allowedRoles;
    }

    /**
     *
     * set allowed roles
     *
     * @param array $allowedRoles
     *
     * @return $this
     */
    public function setAllowedRoles($allowedRoles)
    {
        $this->_allowedRoles = $allowedRoles;

        return $this;
    }

    /**
     *
     * get denied roles
     *
     * @return array
     */
    public function getDeniedRoles()
    {
        return $this->_deniedRoles;
    }

    /**
     *
     * set denied roles
     *
     * @param array $deniedRoles
     *
     * @return $this
     */
    public function setDeniedRoles($deniedRoles)
    {
        $this->_deniedRoles = $deniedRoles;

        return $this;
    }

    /**
     * get check blocked user
     *
     * @return boolean
     */
    public function isCheckBlockedUser()
    {
        return $this->_checkBlockedUser;
    }

    /**
     *
     * set check blocked user
     *
     * @param boolean $checkBlockedUser
     *
     * @return $this
     */
    public function setCheckBlockedUser($checkBlockedUser)
    {
        $this->_checkBlockedUser = $checkBlockedUser;

        return $this;
    }

    /**
     *
     * set translate adapter
     *
     * @param \Cube\Translate\Adapter\AbstractAdapter $translate
     *
     * @return $this
     */
    public function setTranslate(TranslateAdapter $translate)
    {
        $this->_translate = $translate;

        return $this;
    }

    /**
     *
     * get translate adapter
     *
     * @return \Cube\Translate\Adapter\AbstractAdapter
     */
    public function getTranslate()
    {
        if (!$this->_translate instanceof TranslateAdapter) {
            $translate = Front::getInstance()->getBootstrap()->getResource('translate');
            if ($translate instanceof Translate) {
                $this->setTranslate(
                    $translate->getAdapter());
            }
        }

        return $this->_translate;
    }

    /**
     *
     * authenticate user by username and password or if id is set, authenticate directly
     *
     * @return AuthenticationResult
     */
    public function authenticate()
    {
        $usersService = new UsersService();

        $user = null;

        $id = $this->getId();
        $username = $this->getUsername();
        $email = $this->getEmail();
        $password = $this->getPassword();

        if ($id !== null) {
            $user = $usersService->findBy('id', $id);
        }
        else if ($username !== null || $email !== null) {
            $user = $usersService->findBy('username', $username);
            if (!$user && $email !== null) {
                $user = $usersService->findBy('email', $email);
            }
        }

        $success = false;
        $blockedUser = null;

        $translate = $this->getTranslate();

        $messages = array(
            $translate->_('The login details you have submitted are invalid.'));


        if ($this->isCheckBlockedUser()) {
            $blockedUsersService = new BlockedUsersService();
            $blockedUser = $blockedUsersService->check(
                BlockedUserModel::ACTION_REGISTER,
                array(
                    'ip'       => $_SERVER['REMOTE_ADDR'],
                    'username' => $username,
                    'email'    => $email,
                ));
        }

        if ($blockedUser !== null) {
            $success = false;
            $messages = array($blockedUser->blockMessage());
        }
        else if ($user instanceof UserModel) {
            $allowedRoles = $this->getAllowedRoles();
            $deniedRoles = $this->getDeniedRoles();

            if ($id !== null) {
                $success = true;
            }
            else if (strcmp($usersService->hashPassword($password, $user['salt']), $user['password']) === 0) {
                $success = true;
            }
            else if (self::V6_HASHES && strcmp(md5(md5($password) . $user['salt']), $user['password']) === 0) {
                $success = true;
            }

            if (count($allowedRoles) > 0 && !array_key_exists($user['role'], $allowedRoles)) {
                $success = false;
            }

            if (array_key_exists($user['role'], $deniedRoles)) {
                $success = false;
            }
        }

        if ($success === true) {
            $user->save(array(
                'last_login' => new Expr('now()'),
                'ip_address' => (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '',
            ));

            return new AuthenticationResult(true, array(
                'id'       => $user['id'],
                'username' => $user['username'],
                'role'     => $user['role'],
            ));
        }
        else {
            return new AuthenticationResult(false, array(), $messages);
        }
    }

}

