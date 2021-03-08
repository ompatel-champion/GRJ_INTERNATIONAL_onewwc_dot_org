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
 * section / entry status helper
 */

namespace App\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Ppb\Db\Table\Row\ContentSection as ContentSectionModel,
    Ppb\Db\Table\Row\ContentEntry as ContentEntryModel;

class SectionEntryStatus extends AbstractHelper
{

    /**
     *
     * section / entry status helper
     *
     * @param \Ppb\Db\Table\Row\ContentSection $section
     * @param \Ppb\Db\Table\Row\ContentEntry   $entry
     *
     * @return string
     */
    public function sectionEntryStatus($section = null, $entry = null)
    {
        $output = array();

        $translate = $this->getTranslate();

        if ($section instanceof ContentSectionModel) {
            if ($section->isActive()) {
                $output[] = '<span class="badge badge-green">' . $translate->_('Section Active') . '</span>';
            }
            else {
                $output[] = '<span class="badge badge-red">' . $translate->_('Section Inactive') . '</span>';
            }
        }
        else {
            $output[] = '<span class="badge badge-red">' . $translate->_('No Section') . '</span>';
        }

        if ($entry instanceof ContentEntryModel) {
            if ($entry->isDraft()) {
                $output[] = '<span class="badge badge-blue">' . $translate->_('Draft Entry') . '</span>';
            }

            if ($entry->isExpired()) {
                $output[] = '<span class="badge badge-gold ">' . sprintf($translate->_('Entry Expired on %s'), $this->getView()->date($entry['expiry_date'])) . '</span>';
            }

            if ($entry->isActive()) {
                $output[] = '<span class="badge badge-green">' . $translate->_('Entry Active') . '</span>';
            }
        }
        else {
            $output[] = '<span class="badge badge-red">' . $translate->_('No Entry') . '</span>';
        }

        return implode(' ', $output);
    }

}

