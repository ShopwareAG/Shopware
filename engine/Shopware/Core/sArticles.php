<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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

use Shopware\Bundle\SearchBundle;
use Shopware\Bundle\StoreFrontBundle;
use Shopware\Components\QueryAliasMapper;

/**
 * Deprecated Shopware Class that handle articles
 *
 * @category  Shopware
 * @package   Shopware\Core\Class
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class sArticles
{
    /**
     * Pointer to sSystem object
     *
     * @var sSystem
     */
    public $sSYSTEM;

    /**
     * @var \Shopware\Models\Category\Category
     */
    public $category;

    /**
     * @var int
     */
    public $categoryId;

    /**
     * @var int
     */
    public $translationId;

    /**
     * @var int
     */
    public $customerGroupId;

    /**
     * @var \Shopware\Models\Article\Repository
     */
    protected $articleRepository = null;

    /**
     * @var \Shopware\Models\Media\Repository
     */
    protected $mediaRepository = null;

    /**
     * @var StoreFrontBundle\Service\ContextServiceInterface
     */
    private $contextService;

    /**
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * @var StoreFrontBundle\Service\ListProductServiceInterface
     */
    private $listProductService;

    /**
     * @var StoreFrontBundle\Service\ProductServiceInterface
     */
    private $productService;

    /**
     * @var StoreFrontBundle\Service\ConfiguratorServiceInterface
     */
    private $configuratorService;

    /**
     * @var StoreFrontBundle\Service\PropertyServiceInterface
     */
    private $propertyService;

    /**
     * @var StoreFrontBundle\Service\AdditionalTextServiceInterface
     */
    private $additionalTextService;

    /**
     * @var SearchBundle\ProductSearch
     */
    private $searchService;

    /**
     * @var Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    private $db;

    /**
     * @var \Shopware\Components\Compatibility\LegacyStructConverter
     */
    private $legacyStructConverter;

    /**
     * @var \Shopware\Components\Compatibility\LegacyEventManager
     */
    private $legacyEventManager;

    /**
     * @var QueryAliasMapper
     */
    private $queryAliasMapper;

    /**
     * @var Enlight_Controller_Front
     */
    private $frontController;

    /**
     * @var SearchBundle\ProductNumberSearchInterface
     */
    private $productNumberSearch;

    /**
     * @var Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * @var SearchBundle\StoreFrontCriteriaFactory
     */
    private $storeFrontCriteriaFactory;

    /**
     * @var sArticlesComparisons
     */
    private $articleComparisons;

    public function __construct(
        \Shopware\Models\Category\Category $category = null,
        $translationId = null,
        $customerGroupId = null
    ) {
        $container = Shopware()->Container();

        $this->category        = $category ?: Shopware()->Shop()->getCategory();
        $this->categoryId      = $this->category->getId();
        $this->translationId   = $translationId ?: (!Shopware()->Shop()->getDefault() ? Shopware()->Shop()->getId() : null);
        $this->customerGroupId = $customerGroupId ?: ((int) Shopware()->Modules()->System()->sUSERGROUPDATA['id']);

        $this->config                    = $container->get('config');
        $this->db                        = $container->get('db');
        $this->eventManager              = $container->get('events');
        $this->contextService            = $container->get('context_service');
        $this->listProductService        = $container->get('list_product_service');
        $this->productService            = $container->get('product_service');
        $this->productNumberSearch       = $container->get('product_number_search');
        $this->configuratorService       = $container->get('configurator_service');
        $this->propertyService           = $container->get('property_service');
        $this->additionalTextService     = $container->get('additional_text_service');
        $this->searchService             = $container->get('product_search');
        $this->queryAliasMapper          = $container->get('query_alias_mapper');
        $this->frontController           = $container->get('front');
        $this->legacyStructConverter     = $container->get('legacy_struct_converter');
        $this->legacyEventManager        = $container->get('legacy_event_manager');
        $this->session                   = $container->get('session');
        $this->storeFrontCriteriaFactory = $container->get('store_front_criteria_factory');

        $this->articleComparisons = new sArticlesComparisons($this, $container);
    }

    /**
     * Helper function to get access to the media repository.
     * @return \Shopware\Models\Media\Repository
     */
    private function getMediaRepository()
    {
        if ($this->mediaRepository === null) {
            $this->mediaRepository = Shopware()->Models()->getRepository('Shopware\Models\Media\Media');
        }
        return $this->mediaRepository;
    }

    /**
     * Helper function to get access to the article repository.
     * @return \Shopware\Models\Article\Repository
     */
    private function getArticleRepository()
    {
        if ($this->articleRepository === null) {
            $this->articleRepository = Shopware()->Models()->getRepository('Shopware\Models\Article\Article');
        }
        return $this->articleRepository;
    }

    /**
     * Delete articles from comparision chart
     * @param int $article Unique article id - refers to s_articles.id
     */
    public function sDeleteComparison($article)
    {
        $this->articleComparisons->sDeleteComparison($article);
    }

    /**
     * Delete all articles from comparision chart
     */
    public function sDeleteComparisons()
    {
        $this->articleComparisons->sDeleteComparisons();
    }

    /**
     * Insert articles in comparision chart
     * @param int $article s_articles.id
     * @return bool true/false
     */
    public function sAddComparison($article)
    {
        return $this->articleComparisons->sAddComparison($article);
    }

    /**
     * Get all articles from comparision chart
     * @return array Associative array with all articles or empty array
     */
    public function sGetComparisons()
    {
        return $this->articleComparisons->sGetComparisons();
     }

    /**
     * Get all articles and a table of their properties as an array
     * @return array Associative array with all articles or empty array
     */
    public function sGetComparisonList()
    {
        return $this->articleComparisons->sGetComparisonList();
    }

    /**
     * Returns all filterable properties depending on the given articles
     *
     * @param array $articles
     * @return array
     */
    public function sGetComparisonProperties($articles)
    {
        return $this->articleComparisons->sGetComparisonProperties($articles);
    }

    /**
     * fills the article properties with the values and fills up empty values
     *
     * @param array $properties
     * @param array $articles
     * @return array
     */
    public function sFillUpComparisonArticles($properties, $articles)
    {
        return $this->articleComparisons->sFillUpComparisonArticles($properties, $articles);
    }

    /**
     * Get all properties from one article
     *
     * @param int $articleId - s_articles.id
     * @return array
     */
    public function sGetArticleProperties($articleId)
    {
        $orderNumber = $this->getOrdernumberByArticleId($articleId);
        if (!$orderNumber) {
            return [];
        }

        $productContext = $this->contextService->getProductContext();
        $product = $this->listProductService->get($orderNumber, $productContext);
        if (!$product || !$product->hasProperties()) {
            return [];
        }

        $set = $this->propertyService->get($product, $productContext);

        return $this->legacyStructConverter->convertPropertySetStruct($set);
    }

    /**
     * Save a new article comment / voting
     * Reads several values directly from _POST
     * @param int $article - s_articles.id
     * @throws Enlight_Exception
     * @return null
     */
    public function sSaveComment($article)
    {
        $request = $this->frontController->Request();

        $sVoteName    = strip_tags($request->getPost('sVoteName'));
        $sVoteSummary = strip_tags($request->getPost('sVoteSummary'));
        $sVoteComment = strip_tags($request->getPost('sVoteComment'));
        $sVoteStars   = doubleval($request->getPost('sVoteStars'));
        $sVoteMail    = strip_tags($request->getPost('sVoteMail'));

        if ($sVoteStars < 1 || $sVoteStars > 10) {
            $sVoteStars = 0;
        }

        $sVoteStars = $sVoteStars / 2;

        if ($this->config['sVOTEUNLOCK']) {
            $active = 0;
        } else {
            $active = 1;
        }

        $sBADWORDS = "#sex|porn|viagra|url\=|src\=|link\=#i";
        if (preg_match($sBADWORDS, $sVoteComment)) {
            return false;
        }

        if (!empty($this->session['sArticleCommentInserts'][$article])) {
            $sql = '
                DELETE FROM s_articles_vote WHERE id=?
            ';
            $this->db->executeUpdate($sql, array(
                $this->session['sArticleCommentInserts'][$article]
            ));
        }

        $date = date("Y-m-d H:i:s");

        $sql = '
            INSERT INTO s_articles_vote (articleID, name, headline, comment, points, datum, active, email)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ';

        $insertComment = $this->db->executeUpdate($sql, array(
            $article,
            $sVoteName,
            $sVoteSummary,
            $sVoteComment,
            $sVoteStars,
            $date,
            $active,
            $sVoteMail
        ));

        if (empty($insertComment)) {
            throw new Enlight_Exception("sSaveComment #00: Could not save comment");
        }

        $insertId = $this->db->lastInsertId();
        if (!isset($this->session['sArticleCommentInserts'])) {
            $this->session['sArticleCommentInserts'] = new ArrayObject();
        }

        $this->session['sArticleCommentInserts'][$article] = $insertId;
    }

    /**
     * Get id from all articles, that belongs to a specific supplier
     * @param int $supplierID Supplier id (s_articles.supplierID)
     * @return array
     */
    public function sGetArticlesBySupplier($supplierID = null)
    {
        if (!empty($supplierID)) {
            $this->frontController->Request()->setQuery('sSearch', $supplierID);
        }

        if (!$this->frontController->Request()->getQuery('sSearch')) {
            return;
        }
        $sSearch = intval($this->frontController->Request()->getQuery('sSearch'));

        $getArticles = $this->db->fetchAll(
            "SELECT id FROM s_articles WHERE supplierID=? AND active=1 ORDER BY topseller DESC",
            array($sSearch)
        );

        return $getArticles;
    }

    /**
     * @param null $categoryId
     * @param SearchBundle\Criteria $criteria
     * @throws Enlight_Exception
     * @return array|bool|mixed
     */
    public function sGetArticlesByCategory($categoryId = null, SearchBundle\Criteria $criteria = null)
    {
        if (Enlight()->Events()->notifyUntil('Shopware_Modules_Articles_sGetArticlesByCategory_Start', array(
                'subject' => $this,
                'id'      => $categoryId
            ))) {
            return false;
        }


        $context = $this->contextService->getProductContext();

        $request = Shopware()->Container()->get('front')->Request();

        if (!$criteria) {
            $criteria = $this->storeFrontCriteriaFactory->createListingCriteria($request, $context);
        }

        $result = $this->getListing($categoryId, $context, $request, $criteria);

        $result = $this->legacyEventManager->fireArticlesByCategoryEvents($result, $categoryId, $this);

        return $result;
    }

    /**
     * Get supplier by id
     *
     * Uses the new Supplier Manager
     *
     * TestCase: /_tests/Shopware/Tests/Modules/Articles/SuppliersTest.php
     *
     * @param int $id - s_articles_supplier.id
     * @return array
     */
    public function sGetSupplierById($id)
    {
        $id = (int) $id;
        $categoryId = (int) $this->frontController->Request()->getQuery('sCategory');

        $supplierRepository = Shopware()->Models()->getRepository(
            'Shopware\Models\Article\Supplier'
        );
        $supplier = $supplierRepository->find($id);
        if (!is_object($supplier)) {
            return array();
        }
        $supplier = Shopware()->Models()->toArray($supplier);
        if (!Shopware()->Shop()->getDefault()) {
            $supplier = $this->sGetTranslation($supplier, $supplier['id'], 'supplier');
        }
        $supplier['link'] = $this->config['sBASEFILE'];
        $supplier['link'] .= '?sViewport=cat&sCategory=' . $categoryId . '&sPage=1&sSupplier=0';

        return $supplier;
    }

    /**
     * Get all available suppliers from a specific category
     * @param int $id - category id
     * @param int $limit
     * @return array
     */
    public function sGetAffectedSuppliers($id = null, $limit = null)
    {
        $id = empty($id) ? (int) $this->frontController->Request()->getQuery("sCategory") : (int) $id;
        $configLimit = $this->config['sMAXSUPPLIERSCATEGORY'] ? $this->config['sMAXSUPPLIERSCATEGORY'] : 30;
        $limit = empty($limit) ? $configLimit : (int) $limit;

        $sql = "
            SELECT s.id AS id, COUNT(DISTINCT a.id) AS countSuppliers, s.name AS name, s.img AS image
            FROM s_articles a
                INNER JOIN s_articles_categories_ro ac
                    ON  ac.articleID = a.id
                    AND ac.categoryID = ?
                INNER JOIN s_categories c
                    ON  c.id = ac.categoryID
                    AND c.active = 1

            JOIN s_articles_supplier s
            ON s.id=a.supplierID

            LEFT JOIN s_articles_avoid_customergroups ag
            ON ag.articleID=a.id
            AND ag.customergroupID={$this->customerGroupId}

            WHERE ag.articleID IS NULL
            AND a.active = 1

            GROUP BY s.id
            ORDER BY s.name ASC
            LIMIT 0, $limit
        ";
        $getSupplier = $this->db->fetchAll($sql, array(
            $id
        ));

        foreach ($getSupplier as $supplierKey => $supplierValue) {
            if (!Shopware()->Shop()->getDefault()) {
                $getSupplier[$supplierKey] = $this->sGetTranslation($supplierValue, $supplierValue['id'], 'supplier');
            }
            if ($supplierValue["image"]) {
                $getSupplier[$supplierKey]["image"] = $supplierValue["image"];
            }


            if ($id !== Shopware()->Shop()->getCategory()->getId()) {
                $query = array(
                    'sViewport' => 'cat',
                    'sCategory' => $id,
                    'sPage' => 1,
                    'sSupplier' => $supplierValue["id"]
                );
            } else {
                $query = array(
                    'sViewport' => 'supplier',
                    'sSupplier' => $supplierValue["id"]
                );
            }

            $getSupplier[$supplierKey]["link"] = Shopware()->Router()->assemble($query);
        }

        return $getSupplier;
    }

    /**
     * Article price calucation
     * @param double $price
     * @param double $tax
     * @param int $taxId
     * @param array $article article data as an array
     * @throws Enlight_Exception
     * @return double $price formated price
     */
    public function sCalculatingPrice($price, $tax, $taxId = 0, $article = array())
    {
        if (empty($taxId)) {
            throw new Enlight_Exception("Empty taxID in sCalculatingPrice");
        }

        $price = (float) $price;

        // Support tax rate defined by certain conditions
        $getTaxByConditions = $this->getTaxRateByConditions($taxId);
        if ($getTaxByConditions === false) $tax = (float) $tax; else $tax = (float) $getTaxByConditions;

        // Calculate global discount
        if ($this->sSYSTEM->sUSERGROUPDATA["mode"] && $this->sSYSTEM->sUSERGROUPDATA["discount"]) {
            $price = $price - ($price / 100 * $this->sSYSTEM->sUSERGROUPDATA["discount"]);
        }
        if ($this->sSYSTEM->sCurrency["factor"]) {
            $price = $price * floatval($this->sSYSTEM->sCurrency["factor"]);
        }

        // Condition Output-Netto AND NOT overwrite by customer-group
        // OR Output-Netto NOT SET AND tax-settings provided by customer-group
        if ((!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])) {
            $price = $this->sFormatPrice($price);
        } else {
            //if ($price > 100)         die(round($price * (100 + $tax) / 100, 5));
            $price = $this->sFormatPrice(round($price * (100 + $tax) / 100, 3));
        }
        return $price;
    }

    /**
     * @param int $taxId
     * @return double|false
     */
    public function getTaxRateByConditions($taxId)
    {
        $context = $this->contextService->getProductContext();
        $taxRate = $context->getTaxRule($taxId);
        if ($taxRate) {
            return number_format($taxRate->getTax(), 2);
        } else {
            return false;
        }
    }

    /**
     * Article price calucation unformated return
     * @param double $price
     * @param double $tax
     * @param bool $doNotRound
     * @param bool $ignoreTax
     * @param int $taxId
     * @param bool $ignoreCurrency
     * @param array $article article data as an array
     * @throws Enlight_Exception
     * @return double $price  price unformated
     */
    public function sCalculatingPriceNum($price, $tax, $doNotRound = false, $ignoreTax = false, $taxId = 0, $ignoreCurrency = false, $article = array())
    {
        if (empty($taxId)) {
            throw new Enlight_Exception ("Empty tax id in sCalculatingPriceNum");
        }
        // Calculating global discount
        if ($this->sSYSTEM->sUSERGROUPDATA["mode"] && $this->sSYSTEM->sUSERGROUPDATA["discount"]) {
            $price = $price - ($price / 100 * $this->sSYSTEM->sUSERGROUPDATA["discount"]);
        }

        // Support tax rate defined by certain conditions
        $getTaxByConditions = $this->getTaxRateByConditions($taxId);
        if ($getTaxByConditions===false) $tax = (float) $tax; else $tax = (float) $getTaxByConditions;

        if (!empty($this->sSYSTEM->sCurrency["factor"]) && $ignoreCurrency == false) {
            $price = floatval($price) * floatval($this->sSYSTEM->sCurrency["factor"]);
        }

        if ($ignoreTax == true)  return round($price,2);

        // Show brutto or netto ?
        // Condition Output-Netto AND NOT overwrite by customer-group
        // OR Output-Netto NOT SET AND tax-settings provided by customer-group
        if ($doNotRound == true) {
            if ((!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])) {

            } else {
                $price = $price * (100 + $tax) / 100;
            }
        } else {
            if ((!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])) {
                $price = round($price, 3);
            } else {
                $price = round($price * (100 + $tax) / 100, 3);
            }
        }

        return $price;

    }

    /**
     * Get article topsellers for a specific category
     * @param $category int category id
     * @return array
     */
    public function sGetArticleCharts($category = null)
    {
        $sLimitChart = $this->config['sCHARTRANGE'];
        if (empty($sLimitChart)) {
            $sLimitChart = 20;
        }

        if (!empty($category)) {
            $category = (int) $category;
        } elseif ($this->frontController->Request()->getQuery('sCategory')) {
            $category = (int) $this->frontController->Request()->getQuery('sCategory');
        } else {
            $category = $this->categoryId;
        }

        $sql = "
            SELECT STRAIGHT_JOIN DISTINCT
              a.id AS articleID,
              s.sales AS quantity
            FROM s_articles_top_seller_ro s
            INNER JOIN s_articles_categories_ro ac
              ON  ac.articleID = s.article_id
              AND ac.categoryID = :categoryId
            INNER JOIN s_categories c
              ON  ac.categoryID = c.id
              AND c.active = 1
            INNER JOIN s_articles a
              ON  a.id = s.article_id
              AND a.active = 1

            LEFT JOIN s_articles_avoid_customergroups ag
              ON ag.articleID=a.id
              AND ag.customergroupID = :customerGroupId

            INNER JOIN s_articles_details d
              ON d.id = a.main_detail_id
              AND d.active = 1

            INNER JOIN s_articles_attributes at
              ON at.articleID=a.id

            INNER JOIN s_core_tax t
              ON t.id = a.taxID

            WHERE ag.articleID IS NULL
            ORDER BY s.sales DESC, s.article_id DESC
            LIMIT $sLimitChart
        ";


        $queryChart = $this->db->fetchAssoc($sql, array(
            'categoryId'      => $category,
            'customerGroupId' => $this->customerGroupId
        ));

        $articles = array();
        if (!empty($queryChart)) {
            foreach ($queryChart as $articleID => $quantity) {
                $article = $this->sGetPromotionById('fix', 0, (int) $articleID);
                if (!empty($article["articleID"])) {
                    $article['quantity'] = $quantity;
                    $articles[] = $article;
                }
            }
        }

        Enlight()->Events()->notify(
            'Shopware_Modules_Articles_GetArticleCharts',
            array('subject' => $this, 'category' => $category, 'articles' => $articles)
        );

        return $articles;
    }

    /**
     * Check if an article has instant download
     * @param int $id s_articles.id
     * @param int $detailsID s_articles_details.id
     * @param bool $realtime deprecated
     * @return bool
     */
    public function sCheckIfEsd($id, $detailsID, $realtime = false)
    {
        // Check if this article is esd-only (check in variants, too -> later)

        if ($detailsID) {
            $sqlGetEsd = "
            SELECT id, serials FROM s_articles_esd WHERE articleID=$id
            AND articledetailsID=$detailsID
            ";
        } else {
            $sqlGetEsd = "
            SELECT id, serials FROM s_articles_esd WHERE articleID=$id
            ";
        }

        $getEsd = $this->db->fetchRow($sqlGetEsd);
        if (isset($getEsd["id"])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Read the id from all articles that are in the same category as the article specified by parameter (For article navigation in top of detailpage)
     *
     * @param string $orderNumber
     * @param int $categoryId
     * @param Enlight_Controller_Request_RequestHttp $request
     * @return array
     */
    public function getProductNavigation($orderNumber, $categoryId, Enlight_Controller_Request_RequestHttp $request)
    {
        $context = $this->contextService->getProductContext();

        $criteria = $this->storeFrontCriteriaFactory->createProductNavigationCriteria(
            $request,
            $context,
            $categoryId
        );

        $searchResult = $this->productNumberSearch->search(
            $criteria,
            $context
        );

        $navigation = $this->buildNavigation(
            $searchResult,
            $orderNumber,
            $categoryId,
            $context
        );

        $navigation["currentListing"]["link"] = $this->buildCategoryLink($categoryId, $request);

        return $navigation;
    }

    /**
     * @param SearchBundle\ProductNumberSearchResult $searchResult
     * @param $orderNumber
     * @param $categoryId
     * @param StoreFrontBundle\Struct\ProductContextInterface $context
     * @return array
     */
    private function buildNavigation(
        SearchBundle\ProductNumberSearchResult $searchResult,
        $orderNumber,
        $categoryId,
        StoreFrontBundle\Struct\ProductContextInterface $context
    ) {
        $products = $searchResult->getProducts();
        $products = array_values($products);

        if (empty($products)) {
            return array();
        }

        /** @var $currentProduct SearchBundle\SearchProduct */
        foreach ($products as $index => $currentProduct) {
            if ($currentProduct->getNumber() != $orderNumber) {
                continue;
            }

            $previousProduct = isset($products[$index - 1]) ? $products[$index - 1] : null;
            $nextProduct     = isset($products[$index + 1]) ? $products[$index + 1] : null;

            $navigation = array();

            if ($previousProduct) {
                $previousProduct = $this->listProductService->get($previousProduct->getNumber(), $context);
                $navigation["previousProduct"]["orderNumber"] = $previousProduct->getNumber();
                $navigation["previousProduct"]["link"] = $this->config->get('sBASEFILE') . "?sViewport=detail&sDetails=" . $previousProduct->getId() . "&sCategory=" . $categoryId;
                $navigation["previousProduct"]["name"] = $previousProduct->getName();
                $navigation["previousProduct"]["image"] = $previousProduct->getCover()->getThumbnail(4);
            }

            if ($nextProduct) {
                $nextProduct = $this->listProductService->get($nextProduct->getNumber(), $context);

                $navigation["nextProduct"]["orderNumber"] = $nextProduct->getNumber();
                $navigation["nextProduct"]["link"] = $this->config->get('sBASEFILE') . "?sViewport=detail&sDetails=" . $nextProduct->getId() . "&sCategory=" . $categoryId;
                $navigation["nextProduct"]["name"] = $nextProduct->getName();
                $navigation["nextProduct"]["image"] = $nextProduct->getCover()->getThumbnail(4);
            }

            $navigation["currentListing"]["position"] = $index + 1;
            $navigation["currentListing"]["totalCount"] = $searchResult->getTotalCount();

            return $navigation;
        }

        return array();
    }

    /**
     * @param $categoryId
     * @param Enlight_Controller_Request_RequestHttp $request
     * @return string
     */
    private function buildCategoryLink($categoryId, Enlight_Controller_Request_RequestHttp $request)
    {
        $params = $this->queryAliasMapper->replaceLongParams($request->getParams());

        unset($params['ordernumber']);
        unset($params['categoryId']);
        unset($params['module']);
        unset($params['controller']);
        unset($params['action']);

        $params = array_merge(
            $params,
            [
                'sViewport' => 'cat',
                'sCategory' => $categoryId
            ]
        );

        $queryPrams = http_build_query($params);
        $listingLink = $this->config->get('sBASEFILE') . "?" . $queryPrams;

        return $listingLink;
    }

    /**
     * Read the unit types from a certain article
     * @param int $id s_articles.id
     * @return array
     */
    public function sGetUnit($id)
    {
        static $cache = array();
        if (isset($cache[$id])) {
            return $cache[$id];
        }
        $unit = $this->db->fetchRow("
          SELECT unit, description FROM s_core_units WHERE id=?
        ", array($id));

        if (!empty($unit) && !Shopware()->Shop()->get('skipbackend')) {
            $sql = "SELECT objectdata FROM s_core_translations WHERE objecttype='config_units' AND objectlanguage=" . Shopware()->Shop()->getId();
            $translation = $this->db->fetchOne($sql);
            if (!empty($translation)) {
                $translation = unserialize($translation);
            }
            if (!empty($translation[$id])) {
                $unit = array_merge($unit, $translation[$id]);
            }
        }
        return $cache[$id] = $unit;
    }

    /**
     * Get discounts and discount table for a certain article
     * @param string $customergroup id of customergroup key
     * @param string $groupID customer group id
     * @param float $listprice default price
     * @param int $quantity
     * @param bool $doMatrix Return array with all block prices
     * @param array $articleData current article
     * @param bool $ignore deprecated
     * @return array|float|null
     */
    public function sGetPricegroupDiscount($customergroup, $groupID, $listprice, $quantity, $doMatrix = true, $articleData = array(), $ignore = false)
    {
        $getBlockPricings = array();
        $laststart = null;
        $divPercent = null;

        if (!empty($this->sSYSTEM->sUSERGROUPDATA["groupkey"])) {
            $customergroup = $this->sSYSTEM->sUSERGROUPDATA["groupkey"];

        }
        if (!$customergroup || !$groupID) return false;

        $sql = "
        SELECT s_core_pricegroups_discounts.discount AS discount,discountstart
        FROM
            s_core_pricegroups_discounts,
            s_core_customergroups AS scc
        WHERE
            groupID=$groupID AND customergroupID = scc.id
        AND
            scc.groupkey = ?
        GROUP BY discount
        ORDER BY discountstart ASC
        ";

        $getGroups = $this->db->fetchAll($sql, array($customergroup));

        if (count($getGroups)) {
            foreach ($getGroups as $group) {
                $priceMatrix[$group["discountstart"]] = array("percent" => $group["discount"]);
                if (!empty($group["discount"])) $discountsFounds = true;
            }

            if (empty($discountsFounds)) {
                if (empty($doMatrix)) {

                    return $listprice;
                } else {
                    return;
                }
            }

            if (!empty($doMatrix) && count($priceMatrix) == 1) {
                return;
            }

            if (empty($doMatrix)) {
                // Getting price rule matching to quantity
                foreach ($priceMatrix as $start => $percent) {
                    if ($start <= $quantity) {
                        $matchingPercent = $percent["percent"];
                    }
                }

                if ($matchingPercent) {
                    //echo "Percent discount via pricegroup $groupID - $matchingPercent Discount\n";
                    return ($listprice / 100 * (100 - $matchingPercent));
                }
            } else {
                $i = 0;
                // Building price-ranges
                foreach ($priceMatrix as $start => $percent) {
                    $to = $start - 1;
                    if ($laststart && $to) $priceMatrix[$laststart]["to"] = $to;
                    $laststart = $start;
                }

                foreach ($priceMatrix as $start => $percent) {

                    $getBlockPricings[$i]["from"] = $start;
                    $getBlockPricings[$i]["to"] = $percent["to"];
                    if ($i == 0 && $ignore) {

                        $getBlockPricings[$i]["price"] = $this->sCalculatingPrice(($listprice / 100 * (100)), $articleData["tax"], $articleData["taxID"], $articleData);
                        $divPercent = $percent["percent"];
                    } else {
                        if ($ignore) $percent["percent"] -= $divPercent;
                        $getBlockPricings[$i]["price"] = $this->sCalculatingPrice(($listprice / 100 * (100 - $percent["percent"])), $articleData["tax"], $articleData["taxID"], $articleData);
                    }
                    $i++;

                }


                return $getBlockPricings;
            }
        }
        if (!empty($doMatrix)) {

            return;
        } else {

            return $listprice;
        }
    }

    /**
     * Get the cheapest price for a certain article
     * @param int $article id
     * @param int $group customer group id
     * @param int $pricegroup pricegroup id
     * @param bool $usepricegroups consider pricegroups
     * @param bool $realtime
     * @param bool $returnArrayIfConfigurator
     * @param bool $checkLiveshopping
     * @return float cheapest price or null
     */
    public function sGetCheapestPrice($article, $group, $pricegroup, $usepricegroups, $realtime = false, $returnArrayIfConfigurator = false, $checkLiveshopping = false)
    {
        if ($group != $this->sSYSTEM->sUSERGROUP) {
            $fetchGroup = $group;
        } else {
            $fetchGroup = $this->sSYSTEM->sUSERGROUP;
        }

        if (empty($usepricegroups)) {
            $sql = "
            SELECT price FROM s_articles_prices, s_articles_details WHERE
            s_articles_details.id=s_articles_prices.articledetailsID AND
            pricegroup=?
            AND s_articles_details.articleID=?
            GROUP BY ROUND(price,2)
            ORDER BY price ASC
            LIMIT 2
        ";
        } else {
            $sql = "
            SELECT price FROM s_articles_details
            LEFT JOIN
            s_articles_prices ON s_articles_details.id=s_articles_prices.articledetailsID AND
            pricegroup=? AND s_articles_prices.from = '1'
            WHERE
            s_articles_details.articleID=?
            GROUP BY ROUND(price,2)
            ORDER BY price ASC
            LIMIT 2
            ";
        }

        $queryCheapestPrice = $this->db->fetchAll(
            $sql,
            array($fetchGroup, $article)
        );

        if (count($queryCheapestPrice) > 1) {
            $cheapestPrice = $queryCheapestPrice[0]["price"];
            if (empty($cheapestPrice)) {
                // No Price for this customer-group fetch defaultprice
                $sql = "
                SELECT price FROM s_articles_details
                LEFT JOIN s_articles_prices
                  ON s_articles_details.id=s_articles_prices.articledetailsID
                  AND pricegroup='EK'
                  AND s_articles_prices.from = '1'
                WHERE
                  s_articles_details.articleID=$article
                GROUP BY ROUND(price,2)
                ORDER BY price ASC
                LIMIT 2
                ";

                $queryCheapestPrice = $this->db->fetchAll($sql);
                if (count($queryCheapestPrice) > 1) {
                    $cheapestPrice = $queryCheapestPrice[0]["price"];
                } else {
                    $cheapestPrice = 0;
                    $basePrice = $queryCheapestPrice[0]["price"];
                }
            }
            $foundPrice = true;
        } else {
            $cheapestPrice = 0;
            $basePrice = $queryCheapestPrice[0]["price"];
        }

        $sql = "
        SELECT s_core_pricegroups_discounts.discount AS discount,discountstart
        FROM
            s_core_pricegroups_discounts,
            s_core_customergroups AS scc
        WHERE
            groupID=? AND customergroupID = scc.id
        AND
            scc.groupkey = ?
        GROUP BY discount
        ORDER BY discountstart ASC
        ";

        $getGroups = $this->db->fetchAll($sql, array($pricegroup, $this->sSYSTEM->sUSERGROUP));

        //if there are no discounts for this customergroup don't show "ab:"
        if (empty($getGroups)) {
            return $cheapestPrice;
        }


        // Updated / Fixed 28.10.2008 - STH
        if (!empty($usepricegroups)) {

            if (!empty($cheapestPrice)) {

                $basePrice = $cheapestPrice;
            } else {
                $foundPrice = true;
            }

            $returnPrice = $this->sGetPricegroupDiscount(
                $this->sSYSTEM->sUSERGROUP,
                $pricegroup,
                $basePrice,
                99999,
                false
            );

            if (!empty($returnPrice) && $foundPrice) {


                $cheapestPrice = $returnPrice;
            } elseif (!empty($foundPrice) && $returnPrice == 0.00) {

                $cheapestPrice = "0.00";
            } else {

                $cheapestPrice = "0";
            }
        }

        if (isset($queryCheapestPrice[0]["count"]) && $queryCheapestPrice[0]["count"] > 1 && empty($queryCheapestPrice[1]["price"]) && !empty($returnArrayIfConfigurator)) {
            return (array($cheapestPrice, $queryCheapestPrice[0]["count"]));
        }

        return $cheapestPrice;
    }

    /**
     * Get one article with all available data
     * @param int $id article id
     * @param null $sCategoryID
     * @param null $number
     * @param array $selection
     * @return array
     */
    public function sGetArticleById($id = 0, $sCategoryID = null, $number = null, array $selection = null)
    {
        if ($sCategoryID === null) {
            $sCategoryID = $this->frontController->Request()->getParam('sCategory', null);
        };

        /**
         * Validates the passed configuration array for the configurator selection
         */
        $configuration = $this->getCurrentConfiguration($selection);

        /**
         * Checks which product id should be used.
         * If an order number passed, the id of the order number should be used.
         */
        $productId = $this->getCurrentProductId($id, $number);

        /**
         * Checks which product number should be loaded. If a configuration passed.
         */
        $productNumber = $this->getCurrentProductNumber(
            $productId,
            $number,
            $configuration
        );

        $type = $this->getConfiguratorType($productId);

        /**
         * Check if a variant should be loaded. And load the configuration for the variant for pre selection.
         *
         * Requires the following scenario:
         * 1. $number has to be set (without a number we can't load a configuration)
         * 2. $number is equals to $productNumber (if the order number is invalid or inactive fallback to main variant)
         * 3. $configuration is empty (Customer hasn't not set an own configuration)
         */
        if ($number && $number == $productNumber && empty($configuration) || $type == 0) {
            $configuration = $this->getConfigurationByNumber($productNumber);
        }

        $categoryId = (int) $sCategoryID;
        if (empty($categoryId) || $categoryId == Shopware()->Shop()->getId()) {
            $categoryId = Shopware()->Modules()->Categories()->sGetCategoryIdByArticleId($id);
        }

        $product = $this->getProduct(
            $productNumber,
            $categoryId,
            $configuration
        );

        if ($product) {
            $product = $this->legacyEventManager->fireArticleByIdEvents($product, $this);
        }

        return $product;
    }

    private function getConfiguratorType($productId)
    {
        return $this->db->fetchOne(
            'SELECT type
             FROM s_article_configurator_sets configuratorSet
              INNER JOIN s_articles product
                ON product.configurator_set_id = configuratorSet.id
             WHERE product.id = ?',
            array($productId)
        );
    }

    /**
     * calculates the reference price with the base price data
     *
     * @since 4.1.4
     * @param $price | the final price which will be shown
     * @param float $purchaseUnit
     * @param float $referenceUnit
     * @return float
     */
    public function calculateReferencePrice($price, $purchaseUnit, $referenceUnit)
    {
        $purchaseUnit = (float) $purchaseUnit;
        $referenceUnit = (float) $referenceUnit;

        $price = floatval(str_replace(",", ".", $price));

        return $price / $purchaseUnit * $referenceUnit;
    }

    /**
     * Formats article prices
     * @param float $price
     * @return float price
     */
    public function sFormatPrice($price)
    {
        $price = str_replace(",", ".", $price);
        $price = $this->sRound($price);
        $price = str_replace(".", ",", $price); // Replaces points with commas
        $commaPos = strpos($price, ",");
        if ($commaPos) {

            $part = substr($price, $commaPos + 1, strlen($price) - $commaPos);
            switch (strlen($part)) {
                case 1:
                    $price .= "0";
                    break;
                case 2:
                    break;
            }
        } else {
            if (!$price) {
                $price = "0";
            } else {
                $price .= ",00";
            }
        }

        return $price;
    }

    /**
     * Round article price
     *
     * @param float $moneyfloat
     * @return float price
     */
    public function sRound($moneyfloat = null)
    {
        $money_str = explode(".", $moneyfloat);
        if (empty($money_str[1])) $money_str[1] = 0;
        $money_str[1] = substr($money_str[1], 0, 3); // convert to rounded (to the nearest thousandth) string

        $money_str = $money_str[0] . "." . $money_str[1];

        return round($money_str, 2);
    }

    /**
     * @param string $ordernumber
     * @return array
     */
    public function sGetProductByOrdernumber($ordernumber)
    {
        if (Enlight()->Events()->notifyUntil('Shopware_Modules_Articles_sGetProductByOrdernumber_Start', array('subject' => $this, 'value' => $ordernumber))) {
            return false;
        }

        $listProduct = $this->listProductService->get($ordernumber, $this->contextService->getProductContext());
        $getPromotionResult = $this->legacyStructConverter->convertListProductStruct($listProduct);

        $getPromotionResult = Enlight()->Events()->filter(
            'Shopware_Modules_Articles_sGetProductByOrdernumber_FilterResult',
            $getPromotionResult,
            array('subject' => $this, 'value' => $ordernumber)
        );

        return $getPromotionResult;
    }

    /**
     * Get basic article data in various modes (firmly definied by id, random, top,new)
     * @param string $mode Modus (fix, random, top, new)
     * @param int $category filter by category
     * @param int $value article id / ordernumber for firmly definied articles
     * @param bool $withImage
     * @return array
     */
    public function sGetPromotionById($mode, $category = 0, $value = 0, $withImage = false)
    {
        $notifyUntil = $this->eventManager->notifyUntil(
            'Shopware_Modules_Articles_GetPromotionById_Start',
            array(
                'subject' => $this,
                'mode' => $mode,
                'category' => $category,
                'value' => $value
            )
        );

        if ($notifyUntil) {
            return false;
        }

        $value = $this->getPromotionNumberByMode($mode, $category, $value, $withImage);

        if (!$value) {
            return false;
        }

        $result = $this->getPromotion($category, $value);

        if (!$result) {
            return false;
        }

        $result = $this->legacyEventManager->firePromotionByIdEvents(
            $result,
            $category,
            $this
        );

        return $result;
    }

    private function getPromotionNumberByMode($mode, $category, $value, $withImage)
    {
        switch ($mode) {
            case 'top':
            case 'new':
            case 'random':
                $value = $this->getRandomArticle($mode, $category, $value, $withImage);
                break;
            case "fix":
                break;
        }

        if (!$value) {
            return false;
        }

        $number = $this->getOrdernumberByArticleId($value);

        if ($number) {
            $value = $number;
        }

        return $value;
    }

    private function getRandomArticle($mode, $category = 0, $value = 0, $withImage = false)
    {
        $category = (int) $category;
        $categoryJoin = "";

        if (!empty($category)) {
            $categoryJoin = "
                INNER JOIN s_articles_categories_ro ac
                    ON  ac.articleID  = a.id
                    AND ac.categoryID = $category
                INNER JOIN s_categories c
                    ON  c.id = ac.categoryID
                    AND c.active = 1
            ";
        }

        $withImageJoin = "";
        if ($withImage) {
            $withImageJoin = "
                JOIN s_articles_img ai
                ON ai.articleID=a.id
                AND ai.main=1
                AND ai.article_detail_id IS NULL
            ";
        }

        if ($mode == 'top') {
            $promotionTime = !empty($this->config['sPROMOTIONTIME']) ? (int) $this->config['sPROMOTIONTIME'] : 30;
            $now = $this->db->quote(date('Y-m-d H:00:00'));
            $sql = "
                SELECT od.articleID
                FROM s_order as o, s_order_details od, s_articles a $withImageJoin

                $categoryJoin

                LEFT JOIN s_articles_avoid_customergroups ag
                ON ag.articleID=a.id
                AND ag.customergroupID={$this->customerGroupId}
                WHERE o.ordertime > DATE_SUB($now, INTERVAL $promotionTime DAY)
                AND o.id=od.orderID
                AND od.modus=0 AND od.articleID=a.id
                AND a.active=1
                AND ag.articleID IS NULL
                GROUP BY od.articleID
                ORDER BY COUNT(od.articleID) DESC
                LIMIT 100
            ";
        } else {
            $sql = "
                SELECT a.id as articleID
                FROM  s_articles a $withImageJoin
                $categoryJoin
                LEFT JOIN s_articles_avoid_customergroups ag
                ON ag.articleID=a.id
                AND ag.customergroupID={$this->customerGroupId}
                WHERE a.active=1 AND a.mode=0
                AND ag.articleID IS NULL
                ORDER BY a.datum DESC
                LIMIT 100
            ";
        }
        $sql = Enlight()->Events()->filter(
            'Shopware_Modules_Articles_GetPromotionById_FilterSqlRandom',
            $sql,
            array('subject' => $this, 'mode' => $mode, 'category' => $category, 'value' => $value)
        );
        $articleIDs = $this->db->fetchCol($sql);

        if ($mode == 'random') {
            $value = array_rand($articleIDs);
            $value = $articleIDs[$value];
        } else {
            $value = current($articleIDs);
        }

        return $value;
    }

    /**
     * Optimize text, strip html tags etc.
     * @param string $text
     * @return string $text
     */
    public function sOptimizeText($text)
    {
        $text = html_entity_decode($text, ENT_NOQUOTES, 'UTF-8');
        $text = preg_replace('!<[^>]*?>!u', ' ', $text);
        $text = preg_replace('/\s\s+/u', ' ', $text);
        $text = trim($text);
        return $text;
    }

    /**
     * Internal helper function to convert the image data from the database to the frontend structure.
     * @param $image
     * @param $articleAlbum \Shopware\Models\Media\Album
     * @return array
     */
    private function getDataOfArticleImage($image, $articleAlbum)
    {
        //initial the data array
        $imageData = array();

        if (empty($image["path"])) {
            return $imageData;
        }

        //first we get all thumbnail sizes of the article album
        $sizes = $articleAlbum->getSettings()->getThumbnailSize();

        //now we get the configured image and thumbnail dir.
        $imageDir = $this->sSYSTEM->sPathArticleImg;
        $thumbDir = $imageDir. 'thumbnail/';

        //if no extension is configured, shopware use jpg as default extension
        if (empty($image['extension'])) $image['extension'] = 'jpg';

        $imageData['src']['original'] = $imageDir . $image["path"] . "." . $image["extension"];
        $imageData["res"]["original"]["width"] = $image["width"];
        $imageData["res"]["original"]["height"] = $image["height"];
        $imageData["res"]["description"] = $image["description"];
        $imageData["position"] = $image["position"];
        $imageData["extension"] = $image["extension"];
        $imageData["main"] = $image["main"];
        $imageData["id"] = $image["id"];
        $imageData["parentId"] = $image["parentId"];

        // attributes as array as they come from non configurator aricles
        if (!empty($image['attribute'])) {
            unset($image['attribute']['id']);
            unset($image['attribute']['articleImageId']);
            $imageData['attribute'] = $image['attribute'];
        } else {
            $imageData['attribute'] = array();
        }

        // attributes as keys as they come from configurator articles
        if (!empty($image['attribute1'])) {
            $imageData['attribute']['attribute1'] = $image['attribute1'];
        }

        if (!empty($image['attribute2'])) {
            $imageData['attribute']['attribute2'] = $image['attribute2'];
        }

        if (!empty($image['attribute1'])) {
            $imageData['attribute']['attribute3'] = $image['attribute3'];
        }

        foreach ($sizes as $key => $size) {
            if (strpos($size, 'x')===0) {
                $size = $size.'x'.$size;
            }
            $imageData["src"][$key] = $thumbDir . $image['path'] . '_'. $size .'.'. $image['extension'];
        }

        $translation = $this->sGetTranslation(array(), $imageData['id'], "articleimage");

        if (!empty($translation)) {
            if (!empty($translation['description'])) {
                $imageData["res"]["description"] = $translation['description'];
            }
        }

        return $imageData;
    }

    /**
     * Internal helper function to get the cover image of an article.
     * If the orderNumber parameter is set, the function checks first
     * if an variant image configured. If this is the case, this
     * image will be used as cover image. Otherwise the function calls the
     * getArticleMainCover function which returns the absolute main image
     *
     * @param $articleId
     * @param $orderNumber
     * @param $articleAlbum
     * @return array
     */
    public function getArticleCover($articleId, $orderNumber, $articleAlbum)
    {
        if (!empty($orderNumber)) {
            //check for specify variant images. For example:
            //if the user is on a detail page of a shoe and select the color "red"
            //we have to check if the current variant has an own configured picture for a red shoe.
            //the query selects orders the result at first by the image main flag, at second for the position.
            $cover = $this->getArticleRepository()
                ->getVariantImagesByArticleNumberQuery($orderNumber, 0, 1)
                ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        }

        //if we have found a configured article image which has the same options like the passed article order number
        //we have to return this one.
        if (!empty($cover)) {
            return $this->getDataOfArticleImage($cover, $articleAlbum);
        }

        //if we haven't found and variant image we have to select the first image which has no configuration.
        //the query orders the result at first by the image main flag, at second by the position.
        $cover = $this->getArticleRepository()
            ->getArticleCoverImageQuery($articleId)
            ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        if (!empty($cover)) {
            return $this->getDataOfArticleImage($cover, $articleAlbum);
        }

        //if no variant or normal article image is found we will return the main image of the article even if this image has a variant restriction
        return $this->getArticleMainCover($articleId, $articleAlbum);
    }

    /**
     * Returns the the absolute main article image
     * This method returns the main cover depending on the main flag no matter if any variant restriction is set
     *
     * @param $articleId
     * @param $articleAlbum
     * @return array
     */
    public function getArticleMainCover($articleId, $articleAlbum)
    {
        $cover = $this->getArticleRepository()->getArticleFallbackCoverQuery($articleId)->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        return $this->getDataOfArticleImage($cover, $articleAlbum);
    }

    /**
     * Wrapper method to specialize the sGetArticlePictures method for the listing images
     *
     * @param $articleId
     * @param bool $forceMainImage | if true this will return the main image no matter which variant restriction is set
     * @return array
     */
    public function getArticleListingCover($articleId, $forceMainImage = false)
    {
        return $this->sGetArticlePictures($articleId, true, 0, null, null, null, $forceMainImage);
    }

    /**
     * Get all pictures from a certain article
     * @param        $sArticleID
     * @param bool $onlyCover
     * @param int $pictureSize | unused variable
     * @param string $ordernumber
     * @param bool $allImages | unused variable
     * @param bool $realtime | unused variable
     * @param bool $forceMainImage | will return the main image no matter which variant restriction is set
     * @return array
     */
    public function sGetArticlePictures($sArticleID, $onlyCover = true, $pictureSize = 0, $ordernumber = null, $allImages = false, $realtime = false, $forceMainImage = false)
    {
        static $articleAlbum;
        if ($articleAlbum === null) {
            //now we search for the default article album of the media manager, this album contains the thumbnail configuration.
            /**@var $model \Shopware\Models\Media\Album*/
            $articleAlbum = $this->getMediaRepository()
                ->getAlbumWithSettingsQuery(-1)
                ->getOneOrNullResult();
        }

        //first we convert the passed article id into an integer to prevent sql injections
        $articleId = (int) $sArticleID;

        Enlight()->Events()->notify('Shopware_Modules_Articles_GetArticlePictures_Start', array('subject' => $this, 'id' => $articleId));

        //first we get the article cover
        if ($forceMainImage) {
            $cover = $this->getArticleMainCover($articleId, $articleAlbum);
        } else {
            $cover = $this->getArticleCover($articleId, $ordernumber, $articleAlbum);
        }


        if ($onlyCover) {
            $cover = Enlight()->Events()->filter('Shopware_Modules_Articles_GetArticlePictures_FilterResult', $cover, array('subject' => $this, 'id' => $articleId));
            return $cover;
        }

        //now we select all article images of the passed article id.
        $articleImages = $this->getArticleRepository()
            ->getArticleImagesQuery($articleId)
            ->getArrayResult();

        //if an order number passed to the function, we have to select the configured variant images
        $variantImages = array();
        if (!empty($ordernumber)) {
            $variantImages = $this->getArticleRepository()
                ->getVariantImagesByArticleNumberQuery($ordernumber)
                ->getArrayResult();
        }
        //we have to collect the already added image ids, otherwise the images
        //would be displayed multiple times.
        $addedImages = array($cover['id']);
        $images = array();

        //first we add all variant images, this images has a higher priority as the normal article images
        foreach ($variantImages as $variantImage) {

            //if the image wasn't added already, we can add the image
            if (!in_array($variantImage['id'], $addedImages)) {

                //first we have to convert the image data, to resolve the image path and get the thumbnail configuration
                $image = $this->getDataOfArticleImage($variantImage, $articleAlbum);

                //after the data was converted we add the image to the result array and add the id to the addedImages array
                $images[] = $image;
                $addedImages[] = $variantImage['id'];
            }
        }

        //after the variant images added, we can add the normal images, this images has a lower priority as the variant images
        foreach ($articleImages as $articleImage) {
            //add only normal images without any configuration
            //if the image wasn't added already, we can add the image
            if (!in_array($articleImage['id'], $addedImages)) {

                //first we have to convert the image data, to resolve the image path and get the thumbnail configuration
                $image = $this->getDataOfArticleImage($articleImage, $articleAlbum);

                //after the data was converted we add the image to the result array and add the id to the addedImages array
                $images[] = $image;
                $addedImages[] = $articleImage['id'];
            }
        }

        $images = Enlight()->Events()->filter('Shopware_Modules_Articles_GetArticlePictures_FilterResult', $images, array('subject' => $this, 'id' => $articleId));

        return $images;
    }

    /**
     * Get article id by ordernumber
     * @param string $ordernumber
     * @return int $id or false
     */
    public function sGetArticleIdByOrderNumber($ordernumber)
    {
        $checkForArticle = $this->db->fetchRow("
        SELECT articleID AS id FROM s_articles_details WHERE ordernumber=?
        ", array($ordernumber));

        if (isset($checkForArticle["id"])) {
            return $checkForArticle["id"];
        } else {
            return false;
        }
    }

    /**
     * Get name from a certain article by order number
     * @param string $orderNumber
     * @param bool $returnAll return only name or additional data, too
     * @return string or array
     */
    public function sGetArticleNameByOrderNumber($orderNumber, $returnAll = false)
    {
        $article = $this->db->fetchRow("
            SELECT
                s_articles.id,
                s_articles.main_detail_id,
                s_articles_details.id AS did,
                s_articles.name AS articleName,
                additionaltext,
                s_articles.configurator_set_id
            FROM s_articles_details, s_articles
            WHERE ordernumber = :orderNumber
                AND s_articles.id = s_articles_details.articleID
        ", array(
            'orderNumber' => $orderNumber
            )
        );

        if (!$article) {
            return false;
        }

        // Load translations for article or variant
        if ($article['did'] != $article['main_detail_id']) {
            $article = $this->sGetTranslation(
                $article,
                $article['did'],
                "variant"
            );
        } else {
            $article = $this->sGetTranslation(
                $article,
                $article['id'],
                "article"
            );
        }

        // If article has variants, we need to append the additional text to the name
        if ($article['configurator_set_id'] > 0) {
            $product = new StoreFrontBundle\Struct\ListProduct();
            $product->setAdditional($article['additionaltext']);
            $product->setVariantId($article["did"]);
            $product->setNumber($orderNumber);

            $context = $this->contextService->getShopContext();
            $product = $this->additionalTextService->buildAdditionalText($product, $context);

            if (!$returnAll) {
                return $article["articleName"] . ' ' . $product->getAdditional();
            }

            $article['additionaltext'] = $product->getAdditional();
        }

        if (!$returnAll) {
            return $article["articleName"];
        }
        return $article;
    }

    /**
     * Get article name by s_articles.id
     * @param int $articleId
     * @param bool $returnAll
     * @return string name
     */
    public function sGetArticleNameByArticleId($articleId, $returnAll = false)
    {
        $ordernumber = $this->db->fetchOne("
            SELECT ordernumber FROM s_articles_details WHERE kind=1 AND articleID=?
        ", array($articleId));

        return $this->sGetArticleNameByOrderNumber($ordernumber, $returnAll);
    }

    /**
     * Get article taxrate by id
     * @param int $id article id
     * @return float tax or false
     */
    public function sGetArticleTaxById($id)
    {
        $checkForArticle = $this->db->fetchRow("
        SELECT s_core_tax.tax AS tax FROM s_core_tax, s_articles WHERE s_articles.id=? AND
        s_articles.taxID = s_core_tax.id
        ", array($id));

        if (isset($checkForArticle["tax"])) {
            return $checkForArticle["tax"];
        } else {
            return false;
        }
    }

    /**
     * Read translation for one or more articles
     * @param $data
     * @param $object
     * @return array
     */
    public function sGetTranslations($data, $object)
    {
        if (Shopware()->Shop()->get('skipbackend') || empty($data)) {
            return $data;
        }
        $language = Shopware()->Shop()->getId();
        $fallback = Shopware()->Shop()->get('fallback');
        $ids = $this->db->quote(array_keys($data));

        switch ($object) {
            case 'article':
                $map = array(
                    'txtshortdescription' => 'description',
                    'txtlangbeschreibung' => 'description_long',
                    'txtArtikel' => 'articleName',
                    'txtzusatztxt' => 'additionaltext',
                    'txtkeywords' => 'keywords',
                    'txtpackunit' => 'packunit'
                );
                break;
            case 'configuratorgroup':
                $map = array(
                    'description' => 'groupdescription',
                    'name' => 'groupname',
                );
                break;
            default:
                return $data;
        }

        $object = $this->db->quote($object);

        $sql = '';
        if (!empty($fallback)) {
            $sql .= "
                SELECT s.objectdata, s.objectkey
                FROM s_core_translations s
                WHERE
                    s.objecttype = $object
                AND
                    s.objectkey IN ($ids)
                AND
                    s.objectlanguage = '$fallback'
            UNION ALL
            ";
        }
        $sql .= "
            SELECT s.objectdata, s.objectkey
            FROM s_core_translations s
            WHERE
                s.objecttype = $object
            AND
                s.objectkey IN ($ids)
            AND
                s.objectlanguage = '$language'
        ";

        $translations = $this->db->fetchAll($sql);

        if (empty($translations)) {
            return $data;
        }

        foreach ($translations as $translation) {
            $article = (int) $translation['objectkey'];
            $object = unserialize($translation['objectdata']);
            foreach ($object as $translateKey => $value) {
                if (isset($map[$translateKey])) {
                    $key = $map[$translateKey];
                } else {
                    $key = $translateKey;
                }
                if (!empty($value) && array_key_exists($key, $data[$article])) {
                    $data[$article][$key] = $value;
                }
            }
        }

        return $data;
    }

    /**
     * Get translation for an object (article / variant / link / download / supplier)
     * @param $data
     * @param $id
     * @param $object
     * @param $language
     * @return array
     */
    public function sGetTranslation($data, $id, $object, $language = null)
    {
        if (Shopware()->Shop()->get('skipbackend')) {
            return $data;
        }
        $id = (int) $id;
        $language = $language ? : Shopware()->Shop()->getId();
        $fallback = Shopware()->Shop()->get('fallback');

        switch ($object) {
            case 'article':
                $map = array(
                    'txtshortdescription' => 'description',
                    'txtlangbeschreibung' => 'description_long',
                    'txtArtikel' => 'articleName',
                    'txtzusatztxt' => 'additionaltext',
                    'txtkeywords' => 'keywords',
                    'txtpackunit' => 'packunit'
                );
                break;
            case 'variant':
                $map = array('txtzusatztxt' => 'additionaltext', 'txtpackunit' => 'packunit');
                break;
            case 'link':
                $map = array('linkname' => 'description');
                break;
            case 'download':
                $map = array('downloadname' => 'description');
                break;
            case 'configuratoroption':
                $map = array(
                    'name' => 'optionname',
                );
                break;
            case 'configuratorgroup':
                $map = array(
                    'description' => 'groupdescription',
                    'name' => 'groupname',
                );
                break;
            case 'supplier':
                $map = array(
                    'meta_title' => 'title',
                    'description' => 'description',
                );
                break;
        }

        $sql = "
            SELECT objectdata FROM s_core_translations
            WHERE objecttype = '$object'
            AND objectkey = ?
            AND objectlanguage = '$language'
        ";
        $objectData = $this->db->fetchOne($sql, array($id));
        if (!empty($objectData)) {
            $objectData = unserialize($objectData);
        } else {
            $objectData = array();
        }
        if (!empty($fallback)) {
            $sql = "
                SELECT objectdata FROM s_core_translations
                WHERE objecttype = '$object'
                AND objectkey = $id
                AND objectlanguage = '$fallback'
            ";
            $objectFallback = $this->db->fetchOne($sql);
            if (!empty($objectFallback)) {
                $objectFallback = unserialize($objectFallback);
                $objectData = array_merge($objectFallback, $objectData);
            }
        }
        if (!empty($objectData)) {
            foreach ($objectData as $translateKey => $value) {
                if (isset($map[$translateKey])) {
                    $key = $map[$translateKey];
                } else {
                    $key = $translateKey;
                }
                $data[$key] = $value;
            }
        }
        return $data;
    }

    /**
     * Get array of images from a certain configurator combination
     * @param array $sArticle Associative array with all article data
     * @param string $sCombination Currencly active combination
     * @return array
     */
    public function sGetConfiguratorImage($sArticle, $sCombination = "")
    {
        if (!empty($sArticle["sConfigurator"]) || !empty($sCombination)) {

            $foundImage = false;
            $configuratorImages = false;
            $mainKey = 0;

            if (empty($sCombination)) {
                $sArticle["image"]["description"] = $sArticle["image"]["res"]["description"];
                $sArticle["image"]["relations"] = $sArticle["image"]["res"]["relations"];
                foreach ($sArticle["sConfigurator"] as $key => $group) {
                    foreach ($group["values"] as $key2 => $option) {

                        $groupVal = $group["groupnameOrig"] ? $group["groupnameOrig"] : $group["groupname"];
                        $groupVal = str_replace("/", "", $groupVal);
                        $groupVal = str_replace(" ", "", $groupVal);
                        $optionVal = $option["optionnameOrig"] ? $option["optionnameOrig"] : $option["optionname"];
                        $optionVal = str_replace("/", "", $optionVal);
                        $optionVal = str_replace(" ", "", $optionVal);
                        if (!empty($option["selected"])) {
                            $referenceImages[strtolower($groupVal . ":" . str_replace(" ", "", $optionVal))] = true;
                        }
                    }
                }
                foreach (array_merge($sArticle["images"], array(count($sArticle["images"]) => $sArticle["image"])) as $value) {
                    if (preg_match("/(.*){(.*)}/", $value["relations"])) {
                        $configuratorImages = true;

                        break;
                    }
                }
            } else {
                $referenceImages = array_flip(explode("$$", $sCombination));
                foreach ($referenceImages as $key => $v) {
                    $keyNew = str_replace("/", "", $key);
                    unset($referenceImages[$key]);
                    $referenceImages[$keyNew] = $v;
                }
                $sArticle = array("images" => $sArticle, "image" => array());
                foreach ($sArticle["images"] as $k => $value) {
                    if (preg_match("/(.*){(.*)}/", $value["relations"])) {
                        $configuratorImages = true;


                    }
                    if ($value["main"] == 1) {
                        $mainKey = $k;
                    }
                }
                if (empty($configuratorImages)) {

                    return $sArticle["images"][$mainKey];
                }

            }


            if (!empty($configuratorImages)) {

                $sArticle["images"] = array_merge($sArticle["images"], array(count($sArticle["images"]) => $sArticle["image"]));

                unset($sArticle["image"]);

                $debug = false;

                foreach ($sArticle["images"] as $imageKey => $image) {
                    if (empty($image["src"]["original"]) || empty($image["relations"])) {
                        continue;
                    }
                    $string = $image["relations"];
                    // Parsing string
                    $stringParsed = array();

                    preg_match("/(.*){(.*)}/", $string, $stringParsed);

                    $relation = $stringParsed[1];
                    $available = explode("/", $stringParsed[2]);

                    if (!@count($available)) $available = array(0 => $stringParsed[2]);

                    $imageFailedCheck = array();

                    foreach ($available as $checkKey => $checkCombination) {
                        $getCombination = explode(":", $checkCombination);
                        $group = $getCombination[0];
                        $option = $getCombination[1];

                        if (isset($referenceImages[strtolower($checkCombination)])) {

                            $imageFailedCheck[] = true;

                        }
                    }
                    if (count($imageFailedCheck) && count($imageFailedCheck) >= 1 && count($available) >= 1 && $relation == "||") { // ODER Verknï¿½pfunbg
                        if (!empty($debug)) echo $string . " matching combination\n";
                        $sArticle["images"][$imageKey]["relations"] = "";
                        $positions[$image["position"]] = $imageKey;
                    } elseif (count($imageFailedCheck) == count($available) && $relation == "&") { // UND VERKNï¿½PFUNG
                        $sArticle["images"][$imageKey]["relations"] = "";
                        $positions[$image["position"]] = $imageKey;
                    } else {
                        if (!empty($debug)) echo $string . " doesnt match combination\n";
                        unset($sArticle["images"][$imageKey]);
                    }
                }
                ksort($positions);
                $posKeys = array_keys($positions);

                $sArticle["image"] = $sArticle["images"][$positions[$posKeys[0]]];
                unset($sArticle["images"][$positions[$posKeys[0]]]);

                if (!empty($sCombination)) {
                    return $sArticle["image"];
                }

            } else {

            }
        }

        if (!empty($sArticle["images"])) {
            foreach ($sArticle["images"] as $key => $image) {
                if ($image["relations"] == "&{}" || $image["relations"] == "||{}") {
                    $sArticle["images"][$key]["relations"] = "";
                }
            }
        }
        return $sArticle;
    }

    /**
     * Returns a minified product which can be used for listings,
     * sliders or emotions.
     *
     * @param $category
     * @param $number
     * @return array|bool
     */
    private function getPromotion($category, $number)
    {
        $context = $this->contextService->getProductContext();

        $product = $this->listProductService->get(
            $number,
            $context
        );

        if (!$product) {
            return false;
        }

        $promotion = $this->legacyStructConverter->convertListProductStruct($product);
        if (!empty($category) && $category != $context->getShop()->getCategory()->getId()) {
            $promotion["linkDetails"] .= "&sCategory=$category";
        }

        //check if the product has a configured property set which stored in s_filter.
        //the mini product doesn't contains this data so we have to load this lazy.
        if (!$product->hasProperties()) {
            return $promotion;
        }

        $propertySet = $this->propertyService->get(
            $product,
            $context
        );

        if (!$propertySet) {
            return $promotion;
        }

        $promotion['sProperties'] = $this->legacyStructConverter->convertPropertySetStruct($propertySet);
        $promotion['filtergroupID'] = $propertySet->getId();

        return $promotion;
    }

    /**
     * Returns a listing of products. Used for the backward compatibility category listings.
     * This function calls the new shopware core and converts the result to the old listing structure.
     *
     * @param $categoryId
     * @param StoreFrontBundle\Struct\ProductContextInterface $context
     * @param Enlight_Controller_Request_RequestHttp $request
     * @param SearchBundle\Criteria $criteria
     * @return array
     */
    private function getListing(
        $categoryId,
        StoreFrontBundle\Struct\ProductContextInterface $context,
        Enlight_Controller_Request_RequestHttp $request,
        SearchBundle\Criteria $criteria
    ) {
        $searchResult = $this->searchService->search(
            $criteria,
            $context
        );

        $articles = array();

        /**@var $product StoreFrontBundle\Struct\ListProduct */
        foreach ($searchResult->getProducts() as $product) {
            $article = $this->legacyStructConverter->convertListProductStruct($product);

            if (!empty($categoryId) && $categoryId != $context->getShop()->getCategory()->getId()) {
                $article["linkDetails"] .= "&sCategory=$categoryId";
            }

            if ($article['sVoteAverange']) {
                // the listing pages use a 0 - 5 based average
                $article['sVoteAverange']['averange'] = $article['sVoteAverange']['averange'] / 2;
            }

            if ($this->config->get('useShortDescriptionInListing') && strlen($article['description']) > 5) {
                $article["description_long"] = $article['description'];
            }
            $article['description_long'] = $this->sOptimizeText($article['description_long']);

            $articles[] = $article;
        }

        $pageSizes = explode("|", $this->config->get('numberArticlesToShow'));

        return array(
            'sArticles'       => $articles,
            'criteria'        => $criteria,
            'facets'          => $searchResult->getFacets(),
            'sPage'           => $request->getParam('sPage', 1),
            'pageSizes'       => $pageSizes,
            'sPerPage'        => $criteria->getLimit(),
            'sNumberArticles' => $searchResult->getTotalCount(),
            'shortParameters' => $this->queryAliasMapper->getQueryAliases(),
            'sTemplate'       => $request->getParam('sTemplate'),
            'sSort'           => $request->getParam('sSort', $this->config->get('defaultListingSorting'))
        );
    }

    /**
     * Helper function which loads a full product struct and converts the product struct
     * to the shopware 3 array structure.
     *
     * @param $number
     * @param $categoryId
     * @param array $selection
     * @return array
     */
    private function getProduct($number, $categoryId, array $selection)
    {
        $context = $this->contextService->getProductContext();
        $product = $this->productService->get(
            $number,
            $context
        );

        if (!$product) {
            return array();
        }

        $data = $this->legacyStructConverter->convertProductStruct($product, $categoryId);

        $relatedArticles = array();
        foreach($data['sRelatedArticles'] as $related) {
            $related = $this->legacyEventManager->firePromotionByIdEvents($related, null, $this);
            if ($related) {
                $relatedArticles[] = $related;
            }
        }
        $data['sRelatedArticles'] = $relatedArticles;

        $similarArticles = array();
        foreach($data['sSimilarArticles'] as $similar) {
            $similar = $this->legacyEventManager->firePromotionByIdEvents($similar, null, $this);
            if ($similar) {
                $similarArticles[] = $similar;
            }
        }
        $data['sSimilarArticles'] = $similarArticles;

        $data['categoryID'] = $categoryId;

        $configurator = $this->configuratorService->getProductConfigurator(
            $product,
            $context,
            $selection
        );

        $convertedConfigurator = $this->legacyStructConverter->convertConfiguratorStruct($product, $configurator);

        $data = array_merge($data, $convertedConfigurator);

        $data = array_merge($data, $this->getLinksOfProduct($product, $categoryId));

        $data["articleName"] = $this->sOptimizeText($data["articleName"]);
        $data["description_long"] = htmlspecialchars_decode($data["description_long"]);

        $data['mainVariantNumber'] = $this->db->fetchOne(
            "SELECT variant.ordernumber
             FROM s_articles_details variant
             INNER JOIN s_articles product
                ON product.main_detail_id = variant.id
                AND product.id = ?",
            array($product->getId())
        );

        $data["sDescriptionKeywords"] = $this->getDescriptionKeywords(
            $data["description_long"]
        );

        return $data;
    }

    /**
     * Creates different links for the product like `add to basket`, `add to note`, `view detail page`, ...
     *
     * @param StoreFrontBundle\Struct\ListProduct $product
     * @param null $categoryId
     * @return array
     */
    private function getLinksOfProduct(StoreFrontBundle\Struct\ListProduct $product, $categoryId = null)
    {
        $baseFile = $this->config->get('baseFile');
        $context = $this->contextService->getShopContext();

        $detail = $baseFile . "?sViewport=detail&sArticle=" . $product->getId();
        if ($categoryId) {
            $detail .= '&sCategory=' . $categoryId;
        }
        $rewrite = Shopware()->Modules()->Core()->sRewriteLink($detail, $product->getName());

        $basket = $baseFile . "?sViewport=basket&sAdd=" . $product->getNumber();
        $note = $baseFile . "?sViewport=note&sAdd=" . $product->getNumber();
        $friend = $baseFile . "?sViewport=tellafriend&sDetails=" . $product->getId();
        $pdf = $baseFile . "?sViewport=detail&sDetails=" . $product->getId() . "&sLanguage=" . $context->getShop()->getId() . "&sPDF=1";

        return array(
            'linkBasket' => $basket,
            'linkDetails' => $detail,
            'linkDetailsRewrited' => $rewrite,
            'linkNote' => $note,
            'linkTellAFriend' => $friend,
            'linkPDF' => $pdf,
        );
    }

    private function getDescriptionKeywords($longDescription)
    {
        //sDescriptionKeywords
        $string = (strip_tags(html_entity_decode($longDescription, null, 'UTF-8')));
        $string = str_replace(',', '', $string);
        $words = preg_split('/ /', $string, -1, PREG_SPLIT_NO_EMPTY);
        $badWords = explode(",", $this->config->get('badwords'));
        $words = array_diff($words, $badWords);
        $words = array_count_values($words);
        foreach (array_keys($words) as $word) {
            if (strlen($word) < 2) {
                unset($words[$word]);
            }
        }
        arsort($words);

        return htmlspecialchars(
            implode(", ", array_slice(array_keys($words), 0, 20)),
            ENT_QUOTES,
            'UTF-8',
            false
        );
    }

    /**
     * Returns a single order number for the passed product configuration selection.
     * Used for the product detail page.
     *
     * @param $productId
     * @param array $selection
     * @return mixed
     */
    private function getNumberBySelection($productId, array $selection)
    {
        $query = Shopware()->Models()->getDBALQueryBuilder();
        $query->select(array('variant.ordernumber'))
            ->from('s_articles_details', 'variant')
            ->where('variant.articleID = :productId')
            ->andWhere('variant.active = 1')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->setParameter(':productId', $productId);

        foreach ($selection as $optionId) {
            $alias = 'option_' . (int) $optionId;

            $query->innerJoin(
                'variant',
                's_article_configurator_option_relations',
                $alias,
                'variant.id = ' . $alias . '.article_id
                 AND ' . $alias . '.option_id = :' . $alias
            );
            $query->setParameter(':' . $alias, (int) $optionId);
        }

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        return $statement->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * Helper function which checks the different parameters which has to
     * be considered on the product detail page.
     *
     * The passed $selection has the highest priority. This property contains
     * the customer selection of the configuration options.
     * If the selection isn't empty, the function returns the first possible
     * variant number for the selection.
     *
     * The passed $number parameter has the second priority. If an order number
     * passed to the product detail page, the detail page should be loaded
     * directly for the specify product variation.
     *
     * At least the passed $id parameter is used to get the order number
     * of the main variation.
     *
     * If all that does not lead to an valid ordernumber the
     * first variants ordernumber is returned as fallback.
     *
     * @param int           $id
     * @param string        $number
     * @param array         $selection
     * @return false|string
     */
    private function getCurrentProductNumber($id, $number, $selection)
    {
        $selected = null;
        if (!empty($selection)) {
            $selected = $this->getNumberBySelection($id, $selection);
        }

        if ($selected) {
            return $selected;
        }

        if ($number !== null) {
            $selected = $this->getOrdernumberByOrdernumber($number);
        } else {
            $selected = $this->getOrdernumberByProductId($id);
        }

        if ($selected) {
            return $selected;
        }

        $selected = $this->getFallbackVariant($id);

        return $selected;
    }

    /**
     * @param string $number
     * @return false|string
     */
    private function getOrdernumberByOrdernumber($number)
    {
        $query = Shopware()->Models()->getDBALQueryBuilder();
        $query->select(array('variant.ordernumber'));
        $query->from('s_articles_details', 'variant');
        $query->innerJoin(
            'variant',
            's_articles',
            'product',
            'product.id = variant.articleID
             AND variant.active = 1'
        );

        $query->where('variant.ordernumber = :number')
            ->setParameter(':number', $number);

        $statement = $query->execute();
        $selected = $statement->fetch(\PDO::FETCH_COLUMN);

        return $selected;
    }

    /**
     * @param int $productId
     * @return false|string
     */
    private function getOrdernumberByProductId($productId)
    {
        $query = Shopware()->Models()->getDBALQueryBuilder();
        $query->select(array('variant.ordernumber'));
        $query->from('s_articles_details', 'variant');
        $query->innerJoin(
            'variant',
            's_articles',
            'product',
            'product.id = variant.articleID
             AND variant.active = 1'
        );
        $query->where('variant.id = product.main_detail_id')
            ->andWhere('product.id = :productId')
            ->setParameter(':productId', $productId);

        $statement = $query->execute();
        $selected = $statement->fetch(\PDO::FETCH_COLUMN);

        return $selected;
    }


    /**
     * Returns the first active variant ordernumber
     *
     * @param int $productId
     * @return string
     */
    private function getFallbackVariant($productId)
    {
        $query = Shopware()->Models()->getDBALQueryBuilder();
        $query->select(array('variant.ordernumber'));
        $query->from('s_articles_details', 'variant');
        $query->innerJoin(
            'variant',
            's_articles',
            'product',
            'product.id = variant.articleID
             AND variant.active = 1 AND product.id = :productId'
        );
        $query->setMaxResults(1);
        $query->setParameter(':productId', $productId);

        $statement = $query->execute();
        $selected = $statement->fetch(\PDO::FETCH_COLUMN);

        return $selected;
    }

    /**
     * Helper function which used to get the configuration selection of
     * the passed product number.
     * The result array contains a simple array which elements are indexed by
     * the configurator group id and the value contains the configurator option id.
     *
     * This function is required to load different product variations on the product
     * detail page via order number.
     *
     * @param $number
     * @return array
     */
    private function getConfigurationByNumber($number)
    {
        $query = Shopware()->Models()->getDBALQueryBuilder();
        $query->select(array('groups.id', 'options.id'))
            ->from('s_article_configurator_option_relations', 'configuration');

        $query->innerJoin(
            'configuration',
            's_article_configurator_options',
            'options',
            'options.id = configuration.option_id'
        );

        $query->innerJoin(
            'options',
            's_article_configurator_groups',
            'groups',
            'groups.id = options.group_id'
        );

        $query->innerJoin(
            'configuration',
            's_articles_details',
            'variant',
            'variant.id = configuration.article_id
             AND variant.ordernumber = :number'
        );

        $query->setParameter(':number', $number);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        return $statement->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    /**
     * Helper function which checks which product id should be used.
     * If only a product number passed to the product detail page,
     * the function returns the associated product id for the product variation.
     *
     * The product id is required for following selections.
     *
     * @param $id
     * @param null $number
     * @return string
     */
    private function getCurrentProductId($id, $number = null)
    {
        if ($number == null) {
            return $id;
        }

        return $this->db->fetchOne(
            "SELECT articleID FROM s_articles_details WHERE ordernumber = ?",
            array($number)
        );
    }

    /**
     * Helper function which checks the passed $selection parameter for empty.
     * If this is the case the function implements the fallback on the legacy
     * _POST access to get the configuration selection directly of the request object.
     *
     * Additionally the function removes empty array elements.
     * Array elements of the configuration selection can be empty, if the user resets the
     * different group selections.
     *
     * @param $selection
     * @return mixed
     */
    private function getCurrentConfiguration($selection)
    {
        if (empty($selection) && $this->frontController && $this->frontController->Request()) {
            $selection = $this->frontController->Request()->getParam('group');
        }

        foreach ($selection as $groupId => $optionId) {
            if (!$groupId || !$optionId) {
                unset($selection[$groupId]);
            }
        }

        return $selection;
    }

    /**
     * @param $articleId
     * @return string
     */
    private function getOrdernumberByArticleId($articleId)
    {
        $number = $this->db->fetchOne(
            "SELECT ordernumber
             FROM s_articles_details
                INNER JOIN s_articles
                  ON s_articles.main_detail_id = s_articles_details.id
             WHERE articleID = ?",
            [$articleId]
        );

        return $number;
    }

}
