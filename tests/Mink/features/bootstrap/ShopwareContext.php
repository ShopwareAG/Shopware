<?php

use Page\Emotion\Homepage;
use Behat\Gherkin\Node\TableNode;

require_once 'SubContext.php';

class ShopwareContext extends SubContext
{
    /**
     * @When /^I search for "(?P<searchTerm>[^"]*)"$/
     */
    public function iSearchFor($searchTerm)
    {
        $this->getPage('Homepage')->searchFor($searchTerm);
    }

    /**
     * @When /^I received the search-results for "(?P<searchTerm>[^"]*)"$/
     */
    public function iReceivedTheSearchResultsFor($searchTerm)
    {
        $this->getPage('Homepage')->receiveSearchResultsFor($searchTerm);
    }

    /**
     * @Then /^The comparison should look like this:$/
     */
    public function theComparisonShouldLookLikeThis(TableNode $articles)
    {
        /** @var Homepage $page */
        $page = $this->getPage('Homepage');

        /** @var \Element\Emotion\CompareColumn $element */
        $element = $this->getElement('CompareColumn');
        $element->setParent($page);

        $articles = $articles->getHash();

        foreach($articles as $article) {
            foreach($element as $key => $column) {
                $shopArray = array();
                $checkArray = array();

                foreach($article as $property => $subCheck) {
                    $shopValues = Helper::getValuesToCheck($column, $property);
                    $checkValues = array_fill_keys(array_keys($shopValues), $subCheck);

                    $shopArray[$property] = $shopValues;
                    $checkArray[$property] = $checkValues;
                }

                $result = Helper::compareArrays($shopArray, $checkArray);

                if ($result === true) {
                    break;
                }

                if ($key >= count($element)-1) {
                    $message = sprintf('Product "%s" not found in comparision!', $article['name']);
                    Helper::throwException($message);
                }
            }
        }
    }

    /**
     * @Then /^the cart should contain (?P<quantity>\d+) articles with a value of "(?P<amount>[^"]*)"$/
     */
    public function theCartShouldContainArticlesWithAValueOf($quantity, $amount)
    {
        $this->getElement('HeaderCart')->checkCart($quantity, $amount);
    }

    /**
     * @When /^I subscribe to the newsletter with "(?P<email>[^"]*)"$/
     * @When /^I subscribe to the newsletter with "(?P<email>[^"]*)" :$/
     */
    public function iSubscribeToTheNewsletterWith($email, TableNode $additionalData = null)
    {
        /** @var Homepage $page */
        $page = $this->getPage('Homepage');
        $controller = $page->getController();

        $data = array(
            array(
                'field' => 'newsletter',
                'value' => $email
            )
        );

        if ($controller === 'newsletter') {
            $page = $this->getPage('Newsletter');

            if ($additionalData) {
                $data = array_merge($data, $additionalData->getHash());
            }
        }

        $page->subscribeNewsletter($data);
    }

    /**
     * @When /^I unsubscribe the newsletter$/
     * @When /^I unsubscribe the newsletter with "(?P<email>[^"]*)"$/
     */
    public function iUnsubscribeTheNewsletter($email = null)
    {
        $data = array();

        if ($email) {
            $data = array(
                array(
                    'field' => 'newsletter',
                    'value' => $email
                )
            );
        }

        $this->getPage('Newsletter')->unsubscribeNewsletter($data);
    }

    /**
     * @When /^I click the link in my latest email$/
     * @When /^I click the links in my latest (\d+) emails$/
     */
    public function iConfirmTheLinkInTheEmail($limit = 1)
    {
        $sql = 'SELECT hash FROM s_core_optin ORDER BY id DESC LIMIT ' . $limit;
        $hashes = $this->getContainer()->get('db')->fetchAll($sql);

        $session = $this->getSession();
        $link = $session->getCurrentUrl();
        $query = parse_url($link, PHP_URL_QUERY);

        //Blogartikel-Bewertung
        if(empty($query)) {
            $mask = '%s/sConfirmation/%s';
        }
        else {
            parse_str($query, $args);

            switch($args['sAction']) {
                //Artikel-Benachrichtigungen
                case 'notify':
                    $mask = '%sConfirm&sNotificationConfirmation=%s&sNotify=1';
                    break;

                //Artikel-Bewertungen
                default:
                    $mask = '%s&sConfirmation=%s';
                    break;
            }
        }

        foreach ($hashes as $optin) {
            $confirmationLink = sprintf($mask, $link, $optin['hash']);
            $session->visit($confirmationLink);
        }
    }
}
