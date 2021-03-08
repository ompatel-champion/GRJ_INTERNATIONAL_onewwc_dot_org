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
 * statistics view helper class
 */

namespace App\View\Helper;

use Ppb\View\Helper\AbstractHelper,
    Cube\Db\Select,
    Cube\Db\Expr,
    Ppb\Db\Expr\DateTime,
    Ppb\Service;

class Statistics extends AbstractHelper
{

    /**
     *
     * admin flag
     *
     * @var bool
     */
    protected $_admin = false;

    /**
     *
     * set admin flag
     *
     * @param boolean $admin
     *
     * @return $this
     */
    public function setAdmin($admin)
    {
        $this->_admin = $admin;

        return $this;
    }

    /**
     *
     * get admin flag
     *
     * @return boolean
     */
    public function getAdmin()
    {
        return $this->_admin;
    }

    /**
     *
     * main method, returns the object instance
     *
     * @return $this
     */
    public function statistics()
    {
        return $this;
    }

    /**
     *
     * count the number of registered users
     *
     * @param array $params
     *
     * @return int
     */
    public function countUsers($params = array())
    {
        $usersService = new Service\Users();

        $select = $usersService->getTable()->select()
            ->reset(Select::COLUMNS)
            ->columns(array('nb_rows' => new Expr('count(*)')))
            ->where('role not in (?)', array_keys(Service\Users::getAdminRoles()));

        if (array_key_exists('days', $params)) {
            $select->where('created_at > ?',
                new Expr('(now() - interval ' . intval($params['days']) . ' day)'));
        }

        if (array_key_exists('awaiting_approval', $params)) {
            $select->where('approved = ?', 0);
        }

        $stmt = $select->query();

        return (integer)$stmt->fetchColumn('nb_rows');
    }

    /**
     *
     * count the number of listings, having the possibility to filter by certain variables
     *
     * @param array $params
     *
     * @return int
     */
    public function countListings($params = array())
    {
        $listingsService = new Service\Listings();

        $selectType = ($this->getUser() && !$this->getAdmin()) ? Service\Listings::SELECT_MEMBERS : Service\Listings::SELECT_SIMPLE;

        $select = $listingsService->select($selectType, $params)
            ->reset(Select::COLUMNS)
            ->columns(array('nb_rows' => new Expr('count(*)')));

        if (array_key_exists('closing', $params)) {
            $select->where('end_time is not null and end_time < ?', new DateTime($params['closing']));
        }

        $stmt = $select->query();

        return (integer)$stmt->fetchColumn('nb_rows');
    }

    public function countFeedback($filter = null)
    {
        $reputationService = new Service\Reputation();

        $select = $reputationService->getTable()->select()
            ->reset(Select::COLUMNS)
            ->columns(array('nb_rows' => new Expr('count(*)')));

        $user = $this->getUser();

        switch ($filter) {
            case 'pending':
                $select->where('poster_id = ?', $user['id'])
                    ->where('posted = ?', 0);
                break;
            case 'left':
                $select->where('poster_id = ?', $user['id'])
                    ->where('posted = ?', 1);
                break;
            case 'from_buyers':
                $select->where('user_id = ?', $user['id'])
                    ->where('posted = ?', 1)
                    ->where('reputation_type = ?', 'sale');
                break;

            case 'from_sellers':
                $select->where('user_id = ?', $user['id'])
                    ->where('posted = ?', 1)
                    ->where('reputation_type = ?', 'purchase');
                break;
            default:
                $select->where('user_id = ?', $user['id'])
                    ->where('posted = ?', 1);
                break;
        }

        $stmt = $select->query();

        return (integer)$stmt->fetchColumn('nb_rows');
    }

    /**
     *
     * count the number of newsletter subscribers
     *
     * @return int
     */
    public function countNewsletterSubscribers()
    {
        $newslettersSubscribersService = new Service\NewslettersSubscribers();

        $select = $newslettersSubscribersService->getTable()->select()
            ->reset(Select::COLUMNS)
            ->columns(array('nb_rows' => new Expr('count(*)')));

        $stmt = $select->query();

        return (integer)$stmt->fetchColumn('nb_rows');
    }

    /**
     *
     * return date of last newsletter sent
     *
     * @return datetime|null
     */
    public function lastNewsletterSentOn()
    {
        $newslettersService = new Service\Newsletters();

        $select = $newslettersService->getTable()->select()
            ->where('updated_at is not null')
            ->order('updated_at DESC');

        $newsletter = $newslettersService->fetchAll($select)->getRow(0);

        if ($newsletter !== null) {
            return $newsletter['updated_at'];
        }

        return null;
    }

    /**
     *
     * count number of rows in the users statistics table
     *
     * @param array $params
     *
     * @return int
     */
    public function countOnlineUsers($params = array())
    {
        $usersStatisticsService = new Service\UsersStatistics();

        $select = $usersStatisticsService->getTable()->select()
            ->reset(Select::COLUMNS)
            ->columns(array('nb_rows' => new Expr('count(*)')))
            ->where('http_accept_language != ?', '');

        if (array_key_exists('minutes', $params)) {
            $select->where('updated_at > ?',
                new Expr('(now() - interval ' . intval($params['minutes']) . ' minute)'));
        }

        $stmt = $select->query();

        return (integer)$stmt->fetchColumn('nb_rows');
    }
}

