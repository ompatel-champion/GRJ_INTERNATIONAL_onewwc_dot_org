<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.03]
 */

/**
 * users table rowset class
 */

namespace Ppb\Db\Table\Rowset;

class Users extends AbstractStatus
{

    /**
     *
     * row object class
     *
     * @var string
     */
    protected $_rowClass = '\Ppb\Db\Table\Row\User';

    /**
     *
     * activate users from the selected rowset
     *
     * @return $this
     */
    public function activate()
    {
        $this->resetCounter();

        /** @var \Ppb\Db\Table\Row\User $user */
        foreach ($this as $user) {
            $user->updateActive(1);
            $this->incrementCounter();
        }

        return $this;
    }

    /**
     *
     * approve users from the selected rowset
     *
     * @return $this
     */
    public function approve()
    {
        $this->resetCounter();

        $mail = new \Members\Model\Mail\Register();

        /** @var \Ppb\Db\Table\Row\User $user */
        foreach ($this as $user) {
            $user->save(array(
                'approved' => 1
            ));

            $mail->setData(
                $user->getData())
                ->registerApprovedUser()->send();

            $this->incrementCounter();
        }

        return $this;
    }

    /**
     *
     * suspend users from the selected rowset
     *
     * @return $this
     */
    public function suspend()
    {
        $this->resetCounter();

        /** @var \Ppb\Db\Table\Row\User $user */
        foreach ($this as $user) {
            $user->updateActive(0);
            $this->incrementCounter();
        }

        return $this;
    }

    /**
     *
     * verify email for users from the selected rowset
     *
     * @return $this
     */
    public function verifyEmail()
    {
        $this->resetCounter();

        /** @var \Ppb\Db\Table\Row\User $user */
        foreach ($this as $user) {
            $user->save(array(
                'mail_activated' => 1,
            ));

            $this->incrementCounter();
        }

        return $this;
    }

    /**
     *
     * delete all rows from the rowset individually
     *
     * @return $this
     */
    public function delete()
    {
        $this->resetCounter();

        /** @var \Ppb\Db\Table\Row\User $user */
        foreach ($this as $user) {
            if ($user->canDelete()) {
                $user->delete();
                $this->incrementCounter();
            }
            else {
                $translate = $this->getTranslate();
                $message = sprintf($translate->_("User account '%s' cannot be deleted."), $user['username']);
                $this->addMessage($message);
            }
        }

        return $this;
    }
}

