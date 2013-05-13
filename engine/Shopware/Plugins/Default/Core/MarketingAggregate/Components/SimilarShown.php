<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Similar shown component which contains all logic about the shopware
 * similar shown articles functions.
 * This components refresh and initials the similar shown data
 * in the s_articles_similar_shown
 *
 * @category  Shopware
 * @package   Shopware\Plugins\MarketingAggregate\Components
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Components_SimilarShown extends Enlight_Class
{
    /**
     * Resets the similar show article data.
     */
    public function resetSimilarShown()
    {
        $sql = "DELETE FROM s_articles_similar_shown";
        Shopware()->Db()->query($sql);
    }

    /**
     * This function initials the similar shown marketing data.
     */
    public function initSimilarShown($offset = null, $limit = null)
    {
        $limitSql = '';
        if ($limit !== null && $offset !== null) {
            $limitSql = ' LIMIT ' . $offset . ' , ' . $limit;
        } else if ($limit !== null) {
            $limitSql = " LIMIT " . $limit;
        }

        $sql = "
            INSERT IGNORE INTO s_articles_similar_shown (article_id, related_article_id, viewed, init_date)
            SELECT
                article1.articleID as article_id,
                article2.articleID as related_article_id,
                COUNT(article2.articleID) as viewed,
                now() as init_date
            FROM s_emarketing_lastarticles article1
               INNER JOIN s_emarketing_lastarticles article2
                  ON  article1.sessionID  = article2.sessionID
                  AND article1.articleID != article2.articleID
            GROUP BY article1.articleID, article2.articleID
            $limitSql
        ";

        Shopware()->Db()->query($sql);
    }

    /**
     * Helper function to refresh the elapsed similar shown article data.
     * This function use the getSimilarShownValidationTime to get the date
     * of invalid data.
     */
    public function updateElapsedSimilarShownArticles($limit = null)
    {
        $limitSql = "";
        if ($limit !== null) {
            $limitSql = " LIMIT " . $limit;
        }

        $validationTime = $this->getSimilarShownValidationTime();

        $sql = "
              UPDATE s_articles_similar_shown shown
              SET init_date = now(),
                  viewed = (
                      SELECT COUNT(article2.articleID) as viewed
                      FROM s_emarketing_lastarticles article1
                         INNER JOIN s_emarketing_lastarticles article2
                            ON  article1.sessionID  = article2.sessionID

                            AND article1.articleID != article2.articleID
                      WHERE article1.id = shown.article_id
                      GROUP BY article2.articleID
              )
            WHERE init_date <= :validationTime
            $limitSql
            ";

        Shopware()->Db()->query($sql, array('validationTime' => $validationTime->format('Y-m-d 00:00:00')));
    }

    /**
     * Helper function to get the validation time of the similar shown
     * articles table.
     * This function is used to update or delete elapsed similar shown article data.
     *
     * @return DateTime
     */
    protected function getSimilarShownValidationTime()
    {
        //get top seller order time interval
        $interval = Shopware()->Config()->get('similarShownValidationTime', 10);

        //create a new date time object to create the current date subtract the configured date interval.
        $orderTime = new DateTime();
        $orderTime->sub(new DateInterval('P'. $interval .'D'));

        return $orderTime;
    }

    /**
     * This function is used to insert or update the similar shown table
     * for a single buy combination of two articles.
     *
     * @param $articleId
     * @param $relatedArticleId
     */
    public function refreshSimilarShown($articleId, $relatedArticleId)
    {
        $sql = "
            INSERT INTO s_articles_similar_shown (article_id, related_article_id, viewed, init_date)
            VALUES (:articleId, :relatedArticleId, 1, now())
            ON DUPLICATE KEY UPDATE viewed = viewed + 1;
        ";

        Shopware()->Db()->query($sql, array(
            'articleId'        => $articleId,
            'relatedArticleId' => $relatedArticleId
        ));
    }
}