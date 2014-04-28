<?php

namespace Shopware\Gateway\Search\Condition;

use Shopware\Gateway\Search\Condition;

class Category extends Condition
{
    public $id;

    function __construct($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return 'category';
    }
}