<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2017 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     2.0 [rev.2.0.01]
 */
/**
 * mailer transport class - using php mail function
 */

namespace Cube\Mail\Transport;

class Mail extends AbstractTransport
{

    /**
     *
     * send mail method
     *
     * @return bool
     */
    public function send()
    {
        $result = false;

        $mail = $this->getMail();

        $mailHeader = $mail->createHeader();
        $mailBody = $mail->getBody();

        $from = $mail->getFrom();

        $params = sprintf("-oi -f %s", $from['address']);

        if (!ini_get('safe_mode')) {
            ini_set('sendmail_from', $from['address']);
        }

        foreach ($mail->getTo() as $to) {
            $result = @mail($to['address'], $mail->getSubject(), $mailBody, $mailHeader, $params);
        }

        if (function_exists('ini_restore')) {
            ini_restore('sendmail_from');
        }


        return $result;
    }

}

