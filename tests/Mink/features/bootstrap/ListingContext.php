<?php

use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
require_once 'SubContext.php';

class ListingContext extends SubContext
{
    /**
     * @Given /^I am on the listing page:$/
     * @Given /^I go to the listing page:$/
     */
    public function iAmOnTheListingPage(TableNode $params)
    {
        $params = $params->getHash();

        $this->getPage('Listing')->openListing($params);
    }

    /**
     * @When /^I set the filter to:$/
     * @When /^I reset all filters$/
     */
    public function iSetTheFilterTo(TableNode $filter = null)
    {
        $properties = array();

        if($filter)
        {
            $properties = $filter->getHash();
        }

        $this->getPage('Listing')->filter($properties);
    }

    /**
     * @Then /^I should see (?P<num>\d+) articles$/
     */
    public function iShouldSeeArticles($count)
    {
        $this->getPage('Listing')->countArticles($count);
    }

    /**
     * @Then /^the articles should be shown in a table-view$/
     */
    public function theArticlesShouldBeShownInATableView()
    {
        $this->getPage('Listing')->checkView('table');
    }

    /**
     * @Then /^the articles should be shown in a list-view$/
     */
    public function theArticlesShouldBeShownInAListView()
    {
        $this->getPage('Listing')->checkView('list');
    }

    /**
     * @Then /^the article on position (?P<num>\d+) should have this properties:$/
     */
    public function theArticleOnPositionShouldHaveThisProperties($position, TableNode $properties = null)
    {
        $properties = $properties->getHash();

        Helper::getMultipleElement($this, 'ArticleBox', $position)->checkProperties($properties);
    }

    /**
     * @Then /^The price of the article on position (?P<num>\d+) should be "([^"]*)"$/
     */
    public function thePriceOfTheArticleOnPositionShouldBe($position, $price)
    {
        $this->getPage('Listing')->checkPrice($position, $price);
    }

    /**
     * @When /^I order the article on position (?P<position>\d+)$/
     */
    public function iOrderTheArticleOnPosition($position)
    {
        $language = $this->getElement('LanguageSwitcher')->getCurrentLanguage();
        Helper::getMultipleElement($this, 'ArticleBox', $position)->clickActionLink('order', $language);
    }

    /**
     * @When /^I set the article on position (?P<position>\d+) to the comparison list$/
     */
    public function iSetTheArticleOnPositionToTheComparisonList($position)
    {
        $language = $this->getElement('LanguageSwitcher')->getCurrentLanguage();
        Helper::getMultipleElement($this, 'ArticleBox', $position)->clickActionLink('compare', $language);
    }

    /**
     * @When /^I go to the detail page of the article on position (?P<position>\d+)$/
     */
    public function iGoToTheDetailPageOfTheArticleOnPosition($position)
    {
        $language = $this->getElement('LanguageSwitcher')->getCurrentLanguage();

        /** @var \Emotion\ArticleBox $articleBox */
        $articleBox = Helper::getMultipleElement($this, 'ArticleBox', $position);
//        var_dump($articleBox->getXpath());
        $articleBox->clickActionLink('details', $language);
    }

    /**
     * @When /^I browse to "([^"]*)" page$/
     * @When /^I browse to "([^"]*)" page (\d+) times$/
     */
    public function iBrowseTimesToPage($direction, $steps = 1)
    {
        $this->getElement('Paging')->moveDirection($direction, $steps);
    }

    /**
     * @Then /^I should not be able to browse to "([^"]*)" page$/
     */
    public function iShouldNotBeAbleToBrowseToPage($direction)
    {
        $this->getElement('Paging')->noElement($direction);
    }

    /**
     * @When /^I browse to page (\d+)$/
     */
    public function iBrowseToPage($page)
    {
        $this->getElement('Paging')->moveToPage($page);
    }

    /**
     * @Given /^I should not be able to browse to page (\d+)$/
     */
    public function iShouldNotBeAbleToBrowseToPage2($page)
    {
        $this->getElement('Paging')->noElement($page);
    }

    /**
     * @Then /^I should see the article "([^"]*)" in listing$/
     */
    public function iShouldSeeTheArticleInListing($name)
    {
        $this->getPage('Listing')->checkListing($name);
    }

    /**
     * @Given /^I should not see the article "([^"]*)" in listing$/
     */
    public function iShouldNotSeeTheArticleInListing($name)
    {
        $this->getPage('Listing')->checkListing($name, true);
    }

}