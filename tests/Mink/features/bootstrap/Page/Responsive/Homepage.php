<?php

namespace Responsive;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page, Behat\Mink\Exception\ResponseTextException,
    Behat\Behat\Context\Step;

class Homepage extends \Emotion\Homepage
{
    public $cssLocator = array(
        'emotionElement' => 'li.emotion--element.%s-element',
        'emotionSliderElement' => 'li.emotion--element.%s-slider-element',
        'bannerImage' => 'div.emotion--element-banner',
        'bannerLink' => 'a.element-banner--link',
        'bannerMapping' => 'a.element-banner--mapping',
        'sliderSlide' => 'div.slide',
        'slideImage' => 'img',
        'slideLink' => 'a',
        'slideSupplier' => 'div.supplier',
        'slideArticle' => 'div.outer-article-box > div.article_box',
        'slideArticleImageLink' => 'a.article-thumb-wrapper',
        'slideArticleTitleLink' => 'a.title',
        'slideArticlePrice' => 'p.price',
        'blogEntry' => 'div.blog-entry > div.blog-entry-inner',
        'blogEntryImage' => 'div.blog_img > a',
        'blogEntryTitle' => 'h2 > a',
        'blogEntryText' => 'p',
        'youtubeVideo' => 'iframe',
        'categoryTeaserImage' => 'div.teaser_img',
        'categoryTeaserLink' => 'a',
        'categoryTeaserHeader' => 'h3',
        'articleImage' => 'a.artbox_thumb',
        'articleTitle' => 'a.title',
        'articleDescription' => 'p.desc',
        'articlePrice' => 'p.price',
        'articleMore' => 'a.more'
    );

    protected $srcAttribute = 'data-image-src';
}
