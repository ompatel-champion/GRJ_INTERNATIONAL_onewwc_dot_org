<?php

/**
 * 
 * PHP Pro Bid
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2018 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     8.0 [rev.8.0.01]
 */
/**
 * bid status view helper class
 */

namespace Listings\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Ppb\Db\Table\Row\Bid as BidModel;

class BidStatus extends AbstractHelper
{

    /**
     * 
     * bid status helper
     * 
     * @param \Ppb\Db\Table\Row\Bid $bid
     * @return string
     */
    public function bidStatus(BidModel $bid)
    {
        $translate = $this->getTranslate();

        if ($bid->getData('outbid')) {
            return '<span class="badge badge-danger">' . $translate->_(BidModel::$statuses[BidModel::STATUS_OUTBID]) . '</span>';
        }
        else {
            return '<span class="badge badge-success">' . $translate->_(BidModel::$statuses[BidModel::STATUS_HIGH_BID]) . '</span>';
        }
    }

}

