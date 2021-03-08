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
 * content sections service view helper class
 */

namespace Ppb\View\Helper;

use Ppb\Service\Table\Relational\ContentSections as ContentSectionsService;

class ContentSections extends AbstractHelper
{

    /**
     *
     * content sections table service
     *
     * @var \Ppb\Service\Table\Relational\ContentSections
     */
    protected $_contentSections;

    /**
     *
     * data resulted from a previous fetch operation
     *
     * @var array
     */
    protected $_data = array();

    public function __construct()
    {
        $this->setContentSections();
    }

    /**
     *
     * get content sections table service
     *
     * @return \Ppb\Service\Table\Relational\ContentSections
     */
    public function getContentSections()
    {
        return $this->_contentSections;
    }

    /**
     *
     * set content sections table service
     *
     * @param \Ppb\Service\Table\Relational\ContentSections $contentSections
     *
     * @return $this
     */
    public function setContentSections(ContentSectionsService $contentSections = null)
    {
        if (!$contentSections instanceof ContentSectionsService) {
            $contentSections = new ContentSectionsService();
        }

        $this->_contentSections = $contentSections;

        return $this;
    }

    public function getData()
    {
        return $this->_data;
    }

    /**
     *
     * get all content sections having a certain parent id
     *
     * @param string|\Cube\Db\Select $where SQL where clause, or a select object
     *
     * @return array|$this
     */
    public function contentSections($where = null)
    {
        if ($where === null) {
            return $this;
        }


        $this->_data = $this->getContentSections()->fetchAll($where);

        return $this->_data;
    }

}

