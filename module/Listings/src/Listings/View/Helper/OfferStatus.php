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
 * offer status view helper class
 */

namespace Listings\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Ppb\Db\Table\Row\Offer as OfferModel;

class OfferStatus extends AbstractHelper
{

    /**
     *
     * offer status helper
     *
     * @param \Ppb\Db\Table\Row\Offer $offer
     *
     * @return string
     */
    public function offerStatus(OfferModel $offer, $enhanced = false)
    {
        $output = array();

        $translate = $this->getTranslate();

        if ($enhanced === true) {
            switch ($offer->getData('type')) {
                case 'offer':
                    $output[] = '<span class="badge badge-offer">' . $translate->_('Make Offer') . '</span>';
                    break;
                default:
                    $output[] = '<span class="badge badge-default">' . $translate->_('N/A') . '</span>';
                    break;
            }
        }

        switch ($offer->getData('status')) {
            case OfferModel::STATUS_ACCEPTED:
                $output[] = '<span class="badge badge-success">' . $translate->_(OfferModel::$statuses[OfferModel::STATUS_ACCEPTED]) . '</span>';
                break;
            case OfferModel::STATUS_DECLINED:
                $output[] = '<span class="badge badge-danger">' . $translate->_(OfferModel::$statuses[OfferModel::STATUS_DECLINED]) . '</span>';
                break;
            case OfferModel::STATUS_PENDING:
                $output[] = '<span class="badge badge-pending">' . $translate->_(OfferModel::$statuses[OfferModel::STATUS_PENDING]) . '</span>';
                break;
            case OfferModel::STATUS_WITHDRAWN:
                $output[] = '<span class="badge badge-withdrawn">' . $translate->_(OfferModel::$statuses[OfferModel::STATUS_WITHDRAWN]) . '</span>';
                break;
            case OfferModel::STATUS_COUNTER:
                $output[] = '<span class="badge badge-counter">' . $translate->_(OfferModel::$statuses[OfferModel::STATUS_COUNTER]) . '</span>';
                break;
            default:
                $output[] = '<span class="badge badge-text">' . $translate->_('n/a') . '</span>';
                break;
        }

        return implode(' ', $output);
    }

}

