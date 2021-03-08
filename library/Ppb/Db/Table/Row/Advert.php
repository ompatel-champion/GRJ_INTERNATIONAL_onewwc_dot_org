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
 * adverts table row object model
 */

namespace Ppb\Db\Table\Row;

class Advert extends AbstractRow
{
    /**
     *
     * count number of views
     *
     * @return $this
     */
    public function addView()
    {
        $nbViews = $this->getData('nb_views') + 1;
        $this->save(array(
            'nb_views' => $nbViews,
        ));

        return $this;
    }

    /**
     *
     * count number of clicks (image adverts only)
     *
     * @return $this
     */
    public function addClick()
    {
        $nbClicks = $this->getData('nb_clicks') + 1;
        $this->save(array(
            'nb_clicks' => $nbClicks,
        ));

        return $this;
    }

    /**
     *
     * generate advert redirect url
     *
     * @return array
     */
    public function link()
    {
        if ($this->getData('direct_link')) {
            return $this->getData('url');
        }

        return array(
            'module'     => 'app',
            'controller' => 'index',
            'action'     => 'advert-redirect',
            'id'         => $this->getData('id')
        );
    }

    /**
     *
     * get advert image title
     *
     * @return string
     */
    public function imageTitle()
    {
        return ($imageTitle = $this->getData('image_title')) ? $imageTitle : $this->getData('name');
    }

}

