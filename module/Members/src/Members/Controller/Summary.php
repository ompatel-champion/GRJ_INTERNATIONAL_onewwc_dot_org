<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     8.0 [rev.8.0.02]
 */

/**
 * members module - summary controller
 */

namespace Members\Controller;

use Members\Controller\Action\AbstractAction;

class Summary extends AbstractAction
{

    public function Index()
    {
        return array(
            'headline' => $this->_('Dashboard'),
            'user'     => $this->_user,
            'messages' => $this->_flashMessenger->getMessages(),
        );
    }

    public function RequestSellingPrivileges()
    {
        if ($this->_user->canRequestSellingPrivileges()) {
            // send admin notification
            $mail = new \Admin\Model\Mail\Admin();
            $mail->sellingPrivilegesRequest($this->_user)->send();

            $this->_user->save(array(
                'request_selling_privileges' => 1,
            ));

            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('You have successfully requested selling privileges for your account.'),
                'class' => 'alert-success',
            ));
        }

        $this->_helper->redirector()->redirect('index');
    }

}

