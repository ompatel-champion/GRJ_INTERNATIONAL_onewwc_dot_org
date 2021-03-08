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
 * action messages display view helper class
 */

namespace App\View\Helper;

use Ppb\View\Helper\AbstractHelper;

class ActionMessages extends AbstractHelper
{

    /**
     *
     * the view partial to be used
     *
     * @var string
     */
    protected $_partial = 'partials/action-messages.phtml';

    /**
     *
     * messages
     *
     * @var array
     */
    protected $_messages = null;

    /**
     *
     * get messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     *
     * set messages array
     *
     * @param array $messages
     *
     * @return $this
     */
    public function setMessages($messages)
    {
        $this->_messages = $messages;

        return $this;
    }

    /**
     *
     * action messages helper main method
     *
     * @param string $partial
     *
     * @return $this
     */
    public function actionMessages($partial = null)
    {
        if ($partial !== null) {
            $this->setPartial($partial);
        }

        return $this;
    }

    /**
     *
     * render partial
     *
     * @return string
     */
    public function render()
    {
        $messages = (array)$this->getMessages();

        if (count($messages) > 0) {
            $view = $this->getView();

            $view->setVariables(array(
                'messages' => $messages,
            ));

            return $view->process(
                $this->getPartial(), true);
        }

        return '';
    }
}

