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
 * time ago view helper class
 */

namespace Ppb\View\Helper;

use Cube\View\Helper\Pluralize;

class TimeAgo extends Pluralize
{

    public function timeAgo($date)
    {
        $translate = $this->getTranslate();

        $date = strtotime($date);
        $currentTime = time();
        $timeElapsed = $currentTime - $date;
        $seconds = $timeElapsed;
        $minutes = round($timeElapsed / 60);
        $hours = round($timeElapsed / 3600);
        $days = round($timeElapsed / 86400);
        $weeks = round($timeElapsed / 604800);
        $months = round($timeElapsed / 2600640);
        $years = round($timeElapsed / 31207680);

        // Seconds
        if ($seconds <= 60) {
            return $this->pluralize(
                $seconds,
                $translate->_('just now'),
                sprintf($translate->_('%s seconds ago'), $seconds)
            );
        }
        //Minutes
        else if ($minutes <= 60) {
            return $this->pluralize(
                $minutes,
                $translate->_('a minute ago'),
                sprintf($translate->_('%s minutes ago'), $minutes)
            );
        }
        //Hours
        else if ($hours <= 24) {
            return $this->pluralize(
                $hours,
                $translate->_('an hour ago'),
                sprintf($translate->_('%s hours ago'), $hours)
            );
        }
        //Days
        else if ($days <= 7) {
            return $this->pluralize(
                $days,
                $translate->_('yesterday'),
                sprintf($translate->_('%s days ago'), $days)
            );
        }
        //Weeks
        else if ($weeks <= 4.3) {
            return $this->pluralize(
                $weeks,
                $translate->_('a week ago'),
                sprintf($translate->_('%s weeks ago'), $weeks)
            );
        }
        //Months
        else if ($months <= 12) {
            return $this->pluralize(
                $months,
                $translate->_('a month ago'),
                sprintf($translate->_('%s months ago'), $months)
            );
        }
        //Years
        else {
            return $this->pluralize(
                $years,
                $translate->_('a year ago'),
                sprintf($translate->_('%s years ago'), $years)
            );
        }
    }

}

