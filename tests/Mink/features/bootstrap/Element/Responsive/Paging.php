<?php

namespace Responsive;

class Paging extends \Emotion\Paging
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.listing--paging');

    public $cssLocator = array(
        'previous' => 'a.pagination--link.paging--prev',
        'next' => 'a.pagination--link.paging--next'
    );
}
