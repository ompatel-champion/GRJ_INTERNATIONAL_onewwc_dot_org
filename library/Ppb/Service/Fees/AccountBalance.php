<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2019 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.1 [rev.8.1.01]
 */

/**
 * user account balance crediting/debiting fee class
 */

namespace Ppb\Service\Fees;

use Ppb\Service,
    Ppb\Db\Table\Row\User as UserModel;

class AccountBalance extends Service\Fees
{

    /**
     *
     * completed payment redirect path
     *
     * @var array
     */
    protected $_redirect = array(
        'module'     => 'members',
        'controller' => 'summary',
        'action'     => 'index'
    );

    /**
     *
     * update the balance of the selected user
     *
     * @param bool  $ipn  true if payment is completed, false otherwise
     * @param array $post array keys: {user_id, amount}
     *
     * @return $this
     */
    public function callback($ipn, array $post)
    {
        if ($ipn) {
            $usersService = new Service\Users();
            $user = $usersService->findBy('id', $post['user_id']);

            if ($user instanceof UserModel) {
                $user->updateBalance((-1) * $post['amount']);
            }
        }

        return $this;
    }
}

