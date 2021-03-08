<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.1 [rev.8.1.01]
 */

/**
 * newsletters subscribers table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\NewslettersSubscribers as NewslettersSubscribersTable,
    Cube\Db\Expr,
    Ppb\Db\Table\Row\User as UserModel;

class NewslettersSubscribers extends AbstractService
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new NewslettersSubscribersTable());
    }


    /**
     *
     * create or update an newsletter subscriber
     *
     *
     * @param array $data (newsletter_subscription [flag], email, user_id)
     *
     * @return bool
     */
    public function save($data)
    {
        $row = null;
        $user = null;

        $saved = false;

        if (array_key_exists('user_id', $data)) {
            $usersService = new Users();
            $user = $usersService->findBy('id', $data['user_id']);
        }

        if (array_key_exists('newsletter_subscription', $data)) {
            // unsubscribe by email
            if (!$data['newsletter_subscription']) {
                if ($user instanceof UserModel) {
                    $this->deleteOne('user_id', $user['id']);
                }

                $this->deleteOne('email', $data['email']);

                $saved = true;
            }
        }

        if ($saved === false) {
            $confirmed = (isset($data['confirmed'])) ? $data['confirmed'] : 1;
            // we have a subscription creation / update
            $saved = $this->_saveSubscription($user, $data['email'], $confirmed);
        }

        return $saved;
    }

    /**
     *
     * delete subscriber(s) and uncheck newsletter_subscription checkbox if thats the case
     *
     * @param string $column
     * @param mixed  $value
     *
     * @return int     returns the number of affected rows
     */
    public function deleteOne($column, $value)
    {
        $adapter = $this->_table->getAdapter();

        $where[] = $adapter->quoteInto("$column = ?", $value);

        $usersService = new Users();

        $user = null;
        if ($column == 'user_id') {
            $user = $usersService->findBy('id', $value);
        }
        else if ($column == 'email') {
            $user = $usersService->findBy('email', $value);
        }

        if ($user instanceof UserModel) {
            $user->save(array(
                'newsletter_subscription' => 0,
            ));
        }

        return $this->_table->delete($where);
    }

    /**
     *
     * create or update a subscriber
     *
     * @param \PPb\Db\Table\Row\User $user
     * @param string                 $email
     * @param int                    $confirmed
     *
     * @return bool
     */
    protected function _saveSubscription($user, $email, $confirmed)
    {
        $rowByEmail = $this->findBy('email', $email);

        $rowByUser = null;
        if ($user instanceof UserModel) {
            $rowByUser = $this->findBy('user_id', $user['id']);
        }

        $saved = true;
        if ($rowByUser !== null) {
            if ($rowByEmail !== null)
                $this->_table->update(array('email' => $email, 'confirmed' => $confirmed), "id='{$rowByUser['id']}'");
        }
        else if ($rowByEmail !== null) {
            if ($user !== null) {
                $this->_table->update(array('user_id' => $user['id'], 'confirmed' => $confirmed), "id='{$rowByEmail['id']}'");
            }
            else {
                $saved = false;
            }
        }
        else { // we have a new row altogether
            $this->_table->insert(array(
                'user_id'    => (!empty($user['id'])) ? $user['id'] : new Expr('null'),
                'email'      => $email,
                'confirmed'  => $confirmed,
                'created_at' => new Expr('now()'),
            ));
        }

        return $saved;
    }
}

