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
 * bad words filter
 */

namespace Ppb\Filter;

use Cube\Filter\AbstractFilter,
    Ppb\Service\Table\WordFilter as WordFilterService;

class BadWords extends AbstractFilter
{
    const REPLACEMENT = '#@$%';

    protected $_words = null;

    /**
     *
     * set words array
     *
     * @param array $words
     *
     * @return $this
     */
    public function setWords(array $words)
    {
        $this->_words = $words;

        return $this;
    }

    /**
     *
     * get words array, initialize if not set
     *
     * @return array
     */
    public function getWords()
    {
        if ($this->_words === null) {
            $service = new WordFilterService();
            $data = $service->fetchAll();

            $words = array();
            foreach ($data as $word) {
                $words[] = '#' . $word['word'] . '#';
            }

            $this->setWords($words);
        }

        return $this->_words;
    }

    /**
     *
     * clear words array
     *
     * @return $this
     */
    public function clearWords()
    {
        $this->_words = null;

        return $this;
    }

    /**
     *
     * replace all bad words found in the input with the standard replacement
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function filter($value)
    {
        $words = $this->getWords();

        return preg_replace($words, self::REPLACEMENT, $value);
    }
} 