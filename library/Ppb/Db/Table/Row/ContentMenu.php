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
 * content menus table row object model
 */

namespace Ppb\Db\Table\Row;

use Ppb\Service\Table\Relational\ContentSections as ContentSectionsService;

class ContentMenu extends AbstractRow
{

    /**
     *
     * output menu name
     *
     * @return string
     */
    public function name()
    {
        return $this->getData('name');
    }

    /**
     *
     * get sections rowset
     *
     * @return \Ppb\Db\Table\Rowset\ContentSections|null
     */
    public function getSections()
    {
        $content = array_filter((array)\Ppb\Utility::unserialize($this->getData('content')));
        if (array_key_exists('sections', $content)) {
            $sectionsIds = array_filter((array)$content['sections']);

            $contentSectionsService = new ContentSectionsService();

            $adapter = $contentSectionsService->getTable()->getAdapter();

            $select = $contentSectionsService->getTable()->select()
                ->where('active = ?', 1)
                ->where('id IN (?)', $sectionsIds)
                ->order(
                    $adapter->quoteInto('FIELD (ID, ?)', $sectionsIds)
                );

            /** @var \Ppb\Db\Table\Rowset\ContentSections $contentSections */
            $contentSections = $contentSectionsService->fetchAll($select);

            return $contentSections;
        }

        return null;
    }
}

