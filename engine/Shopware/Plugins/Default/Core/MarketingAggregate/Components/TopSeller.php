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
 * Top seller component which contains all logic about the shopware
 * top seller functions.
 * This components refresh and initials the top seller data
 * in the s_articles_top_seller
 *
 * @category  Shopware
 * @package   Shopware\Plugins\MarketingAggregate\Components
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Components_TopSeller extends Enlight_Class
{
    /**
     * This function is used to increment the sales count for the passed
     * article id.
     *
     * @param int $articleId
     * @param int $quantity
     */
    public function incrementTopSeller(int $articleId, int $quantity)
    {
        $sql = "
            INSERT INTO s_articles_top_seller (article_id, sales, last_cleared)
            VALUES (:article_id, :quantity, now())
            ON DUPLICATE KEY UPDATE sales = sales + :quantity, last_cleared=now();
            ";

        Shopware()->Db()->query($sql, array(
            'article_id' => $articleId,
            'quantity'   => $quantity,
        ));
    }

    /**
     * Helper function to refresh the top seller data for a single article.
     * @param $articleId
     * @throws Exception
     */
    public function refreshTopSellerForArticleId($articleId)
    {
        if (empty($articleId)) {
            throw new Exception('No valid article id passed.');
        }
        Shopware()->Db()->query('DELETE FROM s_articles_top_seller WHERE article_id = :articleId', array(
            'articleId' => (int) $articleId
        ));

        $select = $this->getTopSellerSelect();
        $orderTime = $this->getTopSellerOrderTime();

        $sql = "
            INSERT IGNORE INTO s_articles_top_seller (article_id, last_cleared, sales)
            SELECT 	articles.id as article_id,
                    NOW() as last_cleared,
            " . $select . "
            FROM s_articles articles
                LEFT JOIN s_order_details details
                    ON  articles.id = details.articleID
                    AND details.modus = 0
                LEFT JOIN s_order
                    ON  s_order.status >= 0
                    AND s_order.id = details.orderID
                    AND s_order.ordertime >= :orderTime
            WHERE articles.id = :articleId
        ";

        Shopware()->Db()->query($sql, array(
            'orderTime' => $orderTime->format('Y-m-d 00:00:00'),
            'articleId' => (int) $articleId
        ));
    }

    /**
     * Initials the top seller data.
     * This function is used from the backend controller when the user
     * want to refresh the top seller data manuel.
     */
    public function initTopSeller($offset = null, $limit = null)
    {
        $select = $this->getTopSellerSelect();
        $orderTime = $this->getTopSellerOrderTime();

        $limitSelect = '';
        if ($offset !== null && $limit !== null) {
            $limitSelect = 'LIMIT ' . $offset . ',' . $limit;
        }

        $sql = "
            INSERT IGNORE INTO s_articles_top_seller (article_id, last_cleared, sales)
            SELECT 	articles.id as article_id,
                    DATE_SUB(NOW(), INTERVAL articles.id MOD 4 DAY) as last_cleared,
            " . $select . "
            FROM s_articles articles
                LEFT JOIN s_order_details details
                    ON  articles.id = details.articleID
                    AND details.modus = 0
                LEFT JOIN s_order
                    ON  s_order.status >= 0
                    AND s_order.id = details.orderID
                    AND s_order.ordertime >= :orderTime
            GROUP BY articles.id
            " . $limitSelect;

        Shopware()->Db()->query($sql, array(
            'orderTime' => $orderTime->format('Y-m-d 00:00:00')
        ));
    }

    /**
     * Refresh the elapsed top seller data of the s_articles_top_seller table.
     * This function is used
     * @param $limit int Limit the update count.
     * @param bool $timeShuffle
     */
    public function updateElapsedTopSeller($limit = null, $timeShuffle = false) {
        $select = $this->getTopSellerSelect();
        $orderTime = $this->getTopSellerOrderTime();
        $validationTime = $this->getTopSellerValidationTime();

        $limitSelect = '';
        //if a limit value passed, we only want to update the passed limit count.
        if ($limit !== null) {
            $limitSelect = 'LIMIT ' . (int) $limit;
        }

        $timeSelect = ' now(), ';
        if ($timeShuffle === true) {
            $timeSelect = ' DATE_SUB(NOW(), INTERVAL s_articles_top_seller.id MOD 4 DAY), ';
        }

        $sql = "
            UPDATE s_articles_top_seller
            SET last_cleared = $timeSelect
                sales = (
                    SELECT
                       ". $select . "
                    FROM s_articles articles
                        LEFT JOIN s_order_details details
                            ON  articles.id = details.articleID
                            AND details.modus = 0
                        LEFT JOIN s_order
                            ON  s_order.status >= 0
                            AND s_order.id = details.orderID
                            AND s_order.ordertime >= :orderTime
                    WHERE articles.id = s_articles_top_seller.article_id
                )
            WHERE last_cleared <= :validationTime
            $limitSelect
        ";

        Shopware()->Db()->query($sql, array(
            'orderTime'      => $orderTime->format('Y-m-d 00:00:00'),
            'validationTime' => $validationTime->format('Y-m-d 00:00:00')
        ));
    }


    /**
     * Returns a DateTime instance which can be used to refresh or update the top seller
     * data.
     * Used from the updateElapsedTopSeller and initTopSeller function.
     *
     * @return DateTime
     */
    protected function getTopSellerOrderTime()
    {
        //get top seller order time interval
        $interval = Shopware()->Config()->get('topSellerOrderTime', 10);

        //create a new date time object to create the current date subtract the configured date interval.
        $orderTime = new DateTime();
        $orderTime->sub(new DateInterval('P'. $interval .'D'));

        return $orderTime;
    }


    /**
     * Returns a DateTime instance which can be used to validate the top seller
     * data.
     * Used from the updateElapsedTopSeller and initTopSeller function.
     *
     * @return DateTime
     */
    protected function getTopSellerValidationTime()
    {
        //get top seller order time interval
        $interval = Shopware()->Config()->get('topSellerValidationTime', 10);

        //create a new date time object to create the current date subtract the configured date interval.
        $orderTime = new DateTime();
        $orderTime->sub(new DateInterval('P'. $interval .'D'));

        return $orderTime;
    }


    /**
     * Returns the SUM() select path of the top seller calculation statement.
     * Used from the updateElapsedTopSeller and initTopSeller function.
     * If the topSellerPseudoSales configuration set to true, the s_articles.pseudosales
     * column will be add to the sum value.
     *
     * @return string
     */
    protected function getTopSellerSelect()
    {
        //check the pseudo sales configuration value
        $usePseudoSales = Shopware()->Config()->get('topSellerPseudoSales', 1);
        $sumSelect = " SUM(IF(s_order.id, IFNULL(details.quantity, 0), 0))  ";
        if ($usePseudoSales) {
            //if this value is set to true, the articles.pseudosales column has to be added to the sales value.
            $sumSelect = $sumSelect . " + articles.pseudosales ";
        }
        $sumSelect = $sumSelect . " as sales ";

        return $sumSelect;
    }


}