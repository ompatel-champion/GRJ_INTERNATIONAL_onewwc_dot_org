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

namespace Ppb\Form\Element\MultiUpload;

use Ppb\Form\Element\MultiUpload as MultiUploadElement;

class Sortable extends MultiUploadElement
{

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $this->_thumbDivId = $name . 'Sortable';

        $this->setBodyCode('<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
			    integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>')
            ->setBodyCode('<script type="text/javascript">
                    $("#' . $this->_thumbDivId . '").sortable();
                </script>');
    }

}

