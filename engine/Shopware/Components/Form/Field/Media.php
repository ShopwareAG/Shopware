<?php

namespace Shopware\Components\Form\Field;

use Shopware\Components\Form\Field;

class Media extends Field
{
    /**
     * Requires to set a name for the field
     * @param $name
     */
    function __construct($name)
    {
        $this->name = $name;
    }
}