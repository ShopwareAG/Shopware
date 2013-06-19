<?php

class Shopware_Tests_Plugins_Core_MarketingAggregate_Components_SimilarShownTest extends Shopware_Tests_Plugins_Core_MarketingAggregate_AbstractMarketing
{

    protected function getDemoData()
    {
        return require __DIR__ . '/fixtures/similarShown.php';
    }

    /**
     * The demo data contains 144 combinations of the similar shown articles for three users.
     */
    protected function insertDemoData()
    {
        $this->Db()->query("DELETE FROM s_emarketing_lastarticles");
        $statement = $this->Db()->prepare("
            INSERT INTO s_emarketing_lastarticles (img, name, articleID, sessionID, time, userID, shopID)
            VALUES(:img, :name, :articleID, :sessionID, :time, :userID, :shopID)"
        );
        foreach($this->getDemoData() as $data) {
            $statement->execute($data);
        }
    }

    protected function getAllSimilarShown($condition = '')
    {
        return $this->Db()->fetchAll('SELECT * FROM s_articles_similar_shown_ro ' . $condition);
    }

    protected function resetSimilarShown($condition = '')
    {
        $this->Db()->query("DELETE FROM s_articles_similar_shown_ro " . $condition);
    }

    public function testResetSimilarShown()
    {
        $this->SimilarShown()->resetSimilarShown();
        $this->assertCount(0, $this->getAllSimilarShown());
    }

    public function testInitSimilarShown()
    {
        $this->insertDemoData();

        $this->SimilarShown()->initSimilarShown();

        $data = $this->getAllSimilarShown();

        $this->assertCount(144, $data);
    }

    public function testUpdateElapsedSimilarShownArticles()
    {
        $this->insertDemoData();

        $this->Db()->query(" UPDATE s_articles_similar_shown_ro SET init_date = '2010-01-01' ");

        $this->SimilarShown()->updateElapsedSimilarShownArticles(10);

        $articles = $this->getAllSimilarShown(" WHERE init_date > '2010-01-01' ");

        $this->assertCount(10, $articles);

        $this->SimilarShown()->updateElapsedSimilarShownArticles();

        $articles = $this->getAllSimilarShown(" WHERE init_date > '2010-01-01' ");

        $this->assertCount(
            count($this->getAllSimilarShown()),
            $articles
        );
    }


}