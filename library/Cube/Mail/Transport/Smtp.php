<?php

/**
 *
 * Cube Framework
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2020 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     2.2 [rev.2.2.01]
 */

namespace Cube\Mail\Transport;

/**
 * mailer transport class - using smtp protocol
 *
 * Class Smtp
 *
 * @package Cube\Mail\Transport
 */
class Smtp extends AbstractTransport
{
    /**
     * SMTP line break constant.
     *
     * @var string
     */
    const CRLF = "\r\n";

    /**
     *
     * SMTP server connection
     *
     * @var mixed
     */
    protected $_connection = null;

    /**
     *
     * Remote smtp hostname or i.p.
     *
     * @var string
     */
    protected $_host;

    /**
     * smtp server port
     *
     * @var int
     */
    protected $_port = 25;

    /**
     *
     * connection protocol "tcp" or "ssl"
     *
     * @var string
     */
    protected $_protocol = 'tcp';

    /**
     *
     * tls
     *
     * @var bool
     */
    protected $_tls = false;

    /**
     *
     * SMTP username
     *
     * @var string
     */
    protected $_username;

    /**
     *
     * SMTP password
     *
     * @var string
     */
    protected $_password;

    /**
     *
     * debug messages
     *
     * @var string
     */
    protected $_debug;

    /**
     *
     * class constructor
     *
     * @param string $host
     * @param array  $config
     */
    public function __construct($host = 'localhost', array $config = array())
    {
        parent::__construct($config);

        $this->setHost($host);
    }

    /**
     *
     * get remote smtp hostname
     *
     * @return string
     */
    public function getHost()
    {
        return $this->_host;
    }

    /**
     *
     * set remote smtp hostname
     *
     * @param string $host
     *
     * @return $this
     */
    public function setHost($host)
    {
        $this->_host = $host;

        return $this;
    }

    /**
     *
     * get smtp port
     *
     * @return int
     */
    public function getPort()
    {
        return $this->_port;
    }

    /**
     *
     * set remote smtp port
     *
     * 7.8: if port is 465, use ssl
     *
     * @param int $port
     *
     * @return $this
     */
    public function setPort($port)
    {
        $this->_port = $port;

        if ($port == '465') {
            $this->setProtocol('ssl');
        }

        return $this;
    }

    /**
     *
     * get secure string
     *
     * @return string
     */
    public function getProtocol()
    {
        return $this->_protocol;
    }

    /**
     *
     * set protocol
     *
     * @param string $protocol
     *
     * @return $this
     */
    public function setProtocol($protocol)
    {
        if ('tls' == $protocol) {
            $this->_protocol = 'tcp';
            $this->_tls = true;
        }
        else {
            $this->_protocol = $protocol;
            $this->_tls = false;
        }

        return $this;
    }

    /**
     *
     * get smtp username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->_username;
    }

    /**
     *
     * set smtp username
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
     * get smtp password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     *
     * set smtp password
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
     * get output messages from the smtp server
     *
     * @return string
     */
    public function getDebug()
    {
        return (string)$this->_debug;
    }

    /**
     *
     * connect method
     *
     * @return bool
     */
    public function connect()
    {
        $host = $this->getHost();

        $hostname = (($this->_protocol == 'ssl') ? 'ssl://' : '') . $host;
        $this->_connection = fsockopen($hostname, $this->_port, $errno, $errstr, 30);

        // response
        if ($this->_getCode() !== 220) {
            return false;
        }

        fputs($this->_connection, 'EHLO ' . $host . "\r\n");
        if ($this->_getCode() !== 250) {
            fputs($this->_connection, 'HELO ' . $host . "\r\n");
            if ($this->_getCode() !== 250) {
                return false;
            }
        }

        if ($this->_tls === true) {
            fputs($this->_connection, 'STARTTLS' . "\r\n");
            if ($this->_getCode() !== 220) {
                return false;
            }

            stream_socket_enable_crypto($this->_connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

            fputs($this->_connection, 'EHLO ' . $host . "\r\n");
            if ($this->_getCode() !== 250) {
                fputs($this->_connection, 'HELO ' . $host . "\r\n");
                if ($this->_getCode() !== 250) {
                    return false;
                }
            }
        }

        if ($host != 'localhost') {
            fputs($this->_connection, 'AUTH LOGIN' . "\r\n");
            if ($this->_getCode() !== 334) {
                return false;
            }

            fputs($this->_connection, base64_encode($this->_username) . "\r\n");
            if ($this->_getCode() !== 334) {
                return false;
            }
            fputs($this->_connection, base64_encode($this->_password) . "\r\n");
            if ($this->_getCode() !== 235) {
                return false;
            }
        }

        return true;
    }

    /**
     *
     * disconnect method
     *
     * @return $this
     */
    public function disconnect()
    {
        if ($this->isConnection()) {
            $this->_command('QUIT');
            fclose($this->_connection);
        }

        return $this;
    }

    /**
     *
     * check if we have an open connection
     *
     * @1.9: add additional headers
     * @1.9: if message is in html, generate text/plain version as well
     *
     * @return bool
     */
    public function isConnection()
    {
        return ($this->_connection) ? true : false;
    }

    /**
     *
     * send mail method
     *
     * @return bool
     */
    public function send()
    {
        $result = false;

        if ($this->connect()) {
            // deliver the email
            $mail = $this->getMail();
            $from = $mail->getFrom();
            $replyTo = $mail->getReplyTo();
            $cc = $mail->getCc();
            $bcc = $mail->getBcc();
            $contentType = $mail->getContentType();
            $charset = $mail->getCharset();
            $body = $mail->getBody();


            foreach ($mail->getTo() as $to) {
                $this->_command("MAIL FROM:<{$from['address']}>");
                $this->_command("RCPT TO:<" . $to['address'] . ">");
                $this->_command("DATA");

                @fputs($this->_connection, "To: " . $this->_formatAddress($to) . self::CRLF
                    . "From: " . $this->_formatAddress($from) . self::CRLF
                    . "Subject: " . $mail->getSubject() . self::CRLF);

                if (count($cc) > 0) {
                    @fputs($this->_connection, "Cc: {$cc['address']}" . self::CRLF);
                }

                if (count($bcc) > 0) {
                    @fputs($this->_connection, "Bcc: {$cc['address']}" . self::CRLF);
                }

                if (count($replyTo) > 0) {
                    @fputs($this->_connection, "Reply-to: {$replyTo['address']}" . self::CRLF);
                }

                @fputs($this->_connection, "X-Sender: <{$from['address']}>" . self::CRLF
                    . "Return-Path: <{$from['address']}>" . self::CRLF
                    . "Errors-To: <{$from['address']}>" . self::CRLF
                    . "Date: " . $mail->getDate() . self::CRLF
                    . "Message-ID: " . $mail->getMessageId() . self::CRLF
                    . "X-Mailer: Cube Framework/SMTP" . self::CRLF
                    . "X-Priority: 3" . self::CRLF
                    . "MIME-Version: 1.0" . self::CRLF
                    . "Content-Type: " . sprintf('%s; charset="%s"', $contentType, $charset) . self::CRLF
                    . self::CRLF
                    . $body . self::CRLF
                    . "." . self::CRLF);
                $this->_getServerResponse();
            }

            $result = true;
        }

        // disconnect
        $this->disconnect();

        // return
        return $result;
    }

    protected function _command($command, $description = null, $result = null)
    {
        $code = null;

        if ($this->isConnection()) {
            @fputs($this->_connection, $command . self::CRLF);
            if ($description === null) {
                $description = $command;
            }
            $this->_debug .= '<code>' . $description . '</code>';

            $code = $this->_getCode();
        }

        return ($code === $result) ? true : false;
    }

    /**
     *
     * format an address field
     *
     * @param array $data array of data
     *
     * @return string       formatted address
     */
    protected function _formatAddress($data)
    {
        $address = array();

        if (array_key_exists('address', $data)) {
            $data = array($data);
        }

        foreach ((array)$data as $field) {
            if (isset($field['name'])) {
                $address[] = $field['name'] . ' <' . $field['address'] . '>';
            }
            else {
                $address[] = $field['address'];
            }
        }

        return implode('; ', $address);
    }

    /**
     *
     * get server response
     *
     * @return string
     */
    protected function _getServerResponse()
    {
        $response = "";
        while ($str = fgets($this->_connection, 4096)) {
            $response .= $str;
            if (substr($str, 3, 1) == " ") {
                break;
            }
        }

        $this->_debug .= '<code>' . $response . '</code><br/>';

        return $response;
    }

    /**
     *
     * get the code from the server response
     *
     * @return int
     */
    protected function _getCode()
    {
        // filter code from response
        return (int)substr($this->_getServerResponse(), 0, 3);
    }
}

