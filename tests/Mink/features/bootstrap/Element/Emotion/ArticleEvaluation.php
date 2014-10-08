<?php

namespace Element\Emotion;

use Behat\Mink\Element\NodeElement;

require_once 'tests/Mink/features/bootstrap/Element/Emotion/BlogComment.php';

class ArticleEvaluation extends BlogComment
{
    /** @var array $selector */
    protected $selector = array('css' => 'div.comment_block:not(.answer)');

    public $cssLocator = array(
        'author' => 'div.left_container > .author > .name',
        'date' => 'div.left_container > .date',
        'stars' => 'div.left_container > .star',
        'headline' => 'div.right_container > h3',
        'comment' => 'div.right_container > p',
        'answer' => 'div + div.answer > div.right_container'
    );

    /**
     * @param NodeElement $element
     * @return string
     */
    protected function getAnswer(NodeElement $element)
    {
        return $element->getText();
    }
}
