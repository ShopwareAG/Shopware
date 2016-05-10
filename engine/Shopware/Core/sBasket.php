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

use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle;

/**
 * Shopware Class that handles cart operations
 */
class sBasket
{
    /**
     * Database connection which used for each database operation in this class.
     * Injected over the class constructor
     *
     * @var Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    private $db;

    /**
     * Event manager which is used for the event system of shopware.
     * Injected over the class constructor
     *
     * @var Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * Shopware configuration object which used for
     * each config access in this class.
     * Injected over the class constructor
     *
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * Shopware session object.
     * Injected over the class constructor
     *
     * @var Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * Request wrapper object
     *
     * @var Enlight_Controller_Front
     */
    private $front;

    /**
     * The snippet manager
     *
     * @var Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @var Shopware_Components_Snippet_Manager
     */
    public $snippetObject;

    /**
     * Module manager for core class instances
     *
     * @var Shopware_Components_Modules
     */
    private $moduleManager;

    /**
     * @var StoreFrontBundle\Service\ContextServiceInterface
     */
    private $contextService;

    /**
     * @var StoreFrontBundle\Service\AdditionalTextServiceInterface
     */
    private $additionalTextService;

    /**
     * Pointer to sSystem object
     * Used for legacy purposes
     *
     * @var sSystem
     * @deprecated
     */
    public $sSYSTEM;

    public function __construct(
        Enlight_Components_Db_Adapter_Pdo_Mysql $db                 = null,
        Enlight_Event_EventManager              $eventManager       = null,
        Shopware_Components_Snippet_Manager     $snippetManager     = null,
        Shopware_Components_Config              $config             = null,
        Enlight_Components_Session_Namespace    $session            = null,
        Enlight_Controller_Front                $front              = null,
        Shopware_Components_Modules             $moduleManager      = null,
        sSystem                                 $systemModule       = null,

        StoreFrontBundle\Service\ContextServiceInterface $contextService = null,
        StoreFrontBundle\Service\AdditionalTextServiceInterface $additionalTextService = null
    ) {
        $this->db = $db ? : Shopware()->Db();
        $this->eventManager = $eventManager ? : Shopware()->Events();
        $this->snippetManager = $snippetManager ? : Shopware()->Snippets();
        $this->config = $config ? : Shopware()->Config();
        $this->session = $session ? : Shopware()->Session();
        $this->front = $front ? : Shopware()->Front();
        $this->moduleManager = $moduleManager ? : Shopware()->Modules();
        $this->sSYSTEM = $systemModule ? : Shopware()->System();

        $this->contextService = $contextService;
        $this->additionalTextService = $additionalTextService;

        if ($this->contextService == null) {
            $this->contextService = Shopware()->Container()->get('shopware_storefront.context_service');
        }

        if ($this->additionalTextService == null) {
            $this->additionalTextService = Shopware()->Container()->get('shopware_storefront.additional_text_service');
        }
    }
    /**
     * Get total value of current user's cart
     * Used in multiple locations
     *
     * @return array Total amount of the user's cart
     */
    public function sGetAmount()
    {
        $result = $this->db->fetchRow(
            'SELECT SUM(quantity*(floor(price * 100 + .55)/100)) AS totalAmount
                FROM s_order_basket
                WHERE sessionID = ? GROUP BY sessionID',
            array($this->session->get('sessionId'))
        );

        return ($result === false ? array() : $result);
    }

    /**
     * Get total value of current user's cart (only products)
     * Used only internally in sBasket
     *
     * @return array Total amount of the user's cart (only products)
     */
    public function sGetAmountArticles()
    {
        $result = $this->db->fetchRow(
            'SELECT SUM(quantity*(floor(price * 100 + .55)/100)) AS totalAmount
                FROM s_order_basket
                WHERE sessionID = ? AND modus = 0
                GROUP BY sessionID',
            array($this->session->get('sessionId'))
        );

        return ($result === false ? array() : $result);
    }

    /**
     * Check if all positions in cart are available
     * Used in CheckoutController
     *
     * @return array
     */
    public function sCheckBasketQuantities()
    {
        $result = $this->db->fetchAll(
            'SELECT (d.instock - b.quantity) as diffStock, b.ordernumber,
                a.laststock, IF(a.active=1, d.active, 0) as active
            FROM s_order_basket b
            LEFT JOIN s_articles_details d
              ON d.ordernumber = b.ordernumber
              AND d.articleID = b.articleID
            LEFT JOIN s_articles a
              ON a.id = d.articleID
            WHERE b.sessionID = ?
              AND b.modus = 0
            GROUP BY b.ordernumber',
            array($this->session->get('sessionId'))
        );
        $hideBasket = false;
        foreach ($result as $article) {
            if (empty($article['active'])
              || (!empty($article['laststock']) && $article['diffStock'] < 0)
            ) {
                $hideBasket = true;
                $articles[$article['ordernumber']]['OutOfStock'] = true;
            } else {
                $articles[$article['ordernumber']]['OutOfStock'] = false;
            }
        }
        return array('hideBasket' => $hideBasket, 'articles' => $articles);
    }

    /**
     * Get cart amount for certain products / suppliers
     * Used only internally in sBasket
     *
     * @param array $articles Articles numbers to filter
     * @param int $supplier Supplier id to filter
     * @return array Amount of articles in current basket that match the current filter
     */
    public function sGetAmountRestrictedArticles($articles, $supplier)
    {
        if (!is_array($articles) && empty($supplier)) {
            return $this->sGetAmountArticles();
        }

        $extraConditions = array();
        if (is_array($articles)) {
            $extraConditions[] = $this->db->quoteInto('ordernumber IN (?) ', $articles);
        }
        if (!empty($supplier)) {
            $extraConditions[] .= $this->db->quoteInto('s_articles.supplierID = ?', $supplier);
        }

        if (count($extraConditions)) {
            $sqlExtra = ' AND ( '.implode(' OR ', $extraConditions).' ) ';
        } else {
            $sqlExtra = '';
        }

        $result = $this->db->fetchRow(
            "SELECT SUM(quantity*(floor(price * 100 + .55)/100)) AS totalAmount
                FROM s_order_basket, s_articles
                WHERE sessionID = ?
                AND modus = 0
                AND s_order_basket.articleID = s_articles.id
                $sqlExtra
                GROUP BY sessionID",
            array($this->session->get('sessionId'))
        );

        return ($result === false ? array() : $result);
    }

    /**
     * Update vouchers in cart
     * Used only internally in sBasket
     */
    public function sUpdateVoucher()
    {
        $voucher = $this->db->fetchRow(
            'SELECT id basketID, ordernumber, articleID as voucherID
                FROM s_order_basket
                WHERE modus = 2 AND sessionID = ?',
            array($this->session->get('sessionId'))
        );
        if ($voucher) {
            $voucher['code'] = $this->db->fetchOne(
                'SELECT vouchercode FROM s_emarketing_vouchers WHERE ordercode = ?',
                array($voucher['ordernumber'])
            );
            if (empty($voucher['code'])) {
                $voucher['code'] = $this->db->fetchOne(
                    'SELECT code FROM s_emarketing_voucher_codes WHERE id = ?',
                    array($voucher['voucherID'])
                );
            }
            $this->sDeleteArticle($voucher['basketID']);
            $this->sAddVoucher($voucher['code']);
        }
    }

    /**
     * Insert basket discount
     * Used only internally in sBasket::sGetBasket()
     */
    public function sInsertDiscount()
    {
        // Get possible discounts
        $getDiscounts = $this->db->fetchAll('
            SELECT basketdiscount, basketdiscountstart
                FROM s_core_customergroups_discounts
                WHERE groupID = ?
                ORDER BY basketdiscountstart ASC',
            array($this->sSYSTEM->sUSERGROUPDATA["id"])
        );


        $this->db->query(
            'DELETE FROM s_order_basket WHERE sessionID = ? AND modus = 3',
            array($this->session->get('sessionId'))
        );

        // No discounts
        if (!count($getDiscounts)) {
            return;
        }

        $sql = 'SELECT SUM(quantity*(floor(price * 100 + .55)/100)) AS totalAmount
              FROM s_order_basket
              WHERE sessionID = ? AND modus != 4
              GROUP BY sessionID';
        $params = array($this->session->get('sessionId'));

        $sql = Enlight()->Events()->filter(
            'Shopware_Modules_Basket_InsertDiscount_FilterSql_BasketAmount',
            $sql,
            array('subject' => $this, 'params' => $params)
        );
        $basketAmount = $this->db->fetchOne($sql, $params);

        // If no articles in basket, return
        if (!$basketAmount) {
            return;
        }

        // Iterate through discounts and find nearly one
        foreach ($getDiscounts as $discountRow) {
            if ($basketAmount < $discountRow["basketdiscountstart"]) {
                break;
            } else {
                $basketDiscount = $discountRow["basketdiscount"];
            }
        }

        if (!$basketDiscount) {
            return;
        }

        $discount = $basketAmount / 100 * $basketDiscount;
        $discount = $discount * -1;
        $discount = round($discount, 2);

        $taxMode = $this->config->get('sTAXAUTOMODE');
        if (!empty($taxMode)) {
            $tax = $this->getMaxTax();
        } else {
            $tax = $this->config->get('sDISCOUNTTAX');
        }

        $taxAutoMode = $this->config->get("sTAXAUTOMODE");
        if (!empty($taxAutoMode)) {
            $tax = $this->getMaxTax();
        }
        if (!$tax) {
            $tax = 19;
        }

        if (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"]) {
            $discountNet = $discount;
        } else {
            $discountNet = round($discount / (100+$tax) * 100, 3);
        }

        $this->sSYSTEM->sUSERGROUPDATA["basketdiscount"] = $basketDiscount;

        $discountNumber = $this->config->get('sDISCOUNTNUMBER');
        $name = isset($discountNumber) ? $discountNumber: "DISCOUNT";

        $discountName = - $basketDiscount . ' % ' . $this->snippetManager
                ->getNamespace('backend/static/discounts_surcharges')
                ->get('discount_name');

        $this->db->insert(
            's_order_basket',
            array(
                'sessionID' => $this->session->get('sessionId'),
                'articlename' => $discountName,
                'articleID' => 0,
                'ordernumber' => $name,
                'quantity' => 1,
                'price' => $discount,
                'netprice' => $discountNet,
                'tax_rate' => $tax,
                'datum' => date("Y-m-d H:i:s"),
                'modus' => 3,
                'currencyFactor' => $this->sSYSTEM->sCurrency["factor"]
            )
        );
    }

    /**
     * Check if any discount is in the cart
     * Used only internally in sBasket
     *
     * @return bool
     */
    public function sCheckForDiscount()
    {
        $discount = $this->db->fetchOne(
            'SELECT id FROM s_order_basket WHERE sessionID = ? AND modus = 3',
            array($this->session->get('sessionId'))
        );

        return (bool) $discount;
    }

    /**
     * Add premium products to cart
     * Used internally in sBasket and in CheckoutController
     *
     * @return bool|int
     */
    public function sInsertPremium()
    {
        static $lastPremium;

        $sBasketAmount = $this->sGetAmount();
        $sBasketAmount = empty($sBasketAmount["totalAmount"]) ? 0 : $sBasketAmount["totalAmount"];
        $sBasketAmount = (float) $sBasketAmount;

        $addPremium = $this->front->Request()->getQuery('sAddPremium');
        if (empty($addPremium)) {
            $deletePremium = $this->db->fetchCol(
                'SELECT basket.id
                FROM s_order_basket basket
                LEFT JOIN s_addon_premiums premium
                ON premium.ordernumber_export = basket.ordernumber
                AND premium.startprice <= ?
                WHERE basket.modus = 1
                AND premium.id IS NULL
                AND basket.sessionID = ?',
                array($sBasketAmount, $this->session->get('sessionId'))
            );
            if (empty($deletePremium)) {
                return true;
            }

            $this->db->delete(
                's_order_basket',
                array('id IN (?)' => $deletePremium)
            );
            return true;
        }

        if (isset($lastPremium) && $lastPremium == $addPremium) {
            return false;
        }

        $lastPremium = $addPremium;

        $this->db->delete(
            's_order_basket',
            array(
                'sessionID = ?' => $this->session->get('sessionId'),
                'modus = 1'
            )
        );

        $premium = $this->db->fetchRow('
            SELECT premium.id, detail.ordernumber, article.id as articleID, article.name as articleName,
              article.main_detail_id,
              detail.id as variantID, detail.additionaltext, premium.ordernumber_export,
              article.configurator_set_id
            FROM
                s_addon_premiums premium,
                s_articles_details detail,
                s_articles article,
                s_articles_details detail2
            WHERE detail.ordernumber = ?
            AND premium.startprice <= ?
            AND premium.ordernumber = detail2.ordernumber
            AND detail2.articleID = detail.articleID
            AND detail.articleID = article.id',
            array(
                $addPremium,
                $sBasketAmount
            )
        );

        if (!$premium) {
            return false;
        }

        // Load translations for article or variant
        if ($premium['main_detail_id'] != $premium['variantID']) {
            $premium = $this->moduleManager->Articles()->sGetTranslation(
                $premium,
                $premium['variantID'],
                "variant"
            );
        } else {
            $premium = $this->moduleManager->Articles()->sGetTranslation(
                $premium,
                $premium['articleID'],
                "article"
            );
        }

        if ($premium['configurator_set_id'] > 0) {
            $premium = $this->moduleManager->Articles()->sGetTranslation(
                $premium, $premium["variantID"], "variant"
            );

            $product = new StoreFrontBundle\Struct\ListProduct(
                $premium['articleID'],
                $premium["variantID"],
                $premium['ordernumber']
            );

            $product->setAdditional($premium['additionaltext']);

            $context = $this->contextService->getShopContext();
            $product = $this->additionalTextService->buildAdditionalText($product, $context);
            $premium['additionaltext'] = $product->getAdditional();
        }

        if (!empty($premium['configurator_set_id'])) {
            $number = $premium['ordernumber'];
        } else {
            $number = $premium['ordernumber_export'];
        }

        return $this->db->insert(
            's_order_basket',
            array(
                'sessionID' => $this->session->get('sessionId'),
                'articlename' => trim($premium["articleName"] . " " . $premium["additionaltext"]),
                'articleID' => $premium['articleID'],
                'ordernumber' => $number,
                'quantity' => 1,
                'price' => 0,
                'netprice' => 0,
                'tax_rate' => 0,
                'datum' => new Zend_Date(),
                'modus' => 1,
                'currencyFactor' => $this->sSYSTEM->sCurrency["factor"]
            )
        );
    }

    /**
     * Get the max tax rate in applied in the current basket
     * Used in several places
     *
     * @return int|false May tax value, or false if none found
     */
    public function getMaxTax()
    {
        $sessionId = $this->session->get('sessionId');

        return $this->db->fetchOne(
            'SELECT MAX(tax_rate) as max_tax
                FROM s_order_basket b
                WHERE b.sessionID = ? AND b.modus = 0
                GROUP BY b.sessionID',
            array(empty($sessionId) ? session_id() : $sessionId)
        );
    }

    /**
     * Add voucher to cart
     * Used in several places
     *
     * @param string $voucherCode Voucher code
     * @param string $basket
     * @return array|bool True if successful, false if stopped by an event, array with error data if one occurred
     */
    public function sAddVoucher($voucherCode, $basket = '')
    {
        if ($this->eventManager->notifyUntil(
            'Shopware_Modules_Basket_AddVoucher_Start',
            array('subject' => $this, 'code' => $voucherCode, 'basket' => $basket)
        )) {
            return false;
        }

        $voucherCode = stripslashes($voucherCode);
        $voucherCode = strtolower($voucherCode);

        // Load the voucher details
        $voucherDetails = $this->db->fetchRow(
            'SELECT *
              FROM s_emarketing_vouchers
              WHERE modus != 1
              AND LOWER(vouchercode) = ?
              AND (
                (valid_to >= now() AND valid_from <= now())
                OR valid_to IS NULL
              )',
            array($voucherCode)
        ) ? : array();

        $userId = $this->session->get('sUserId');

        // Check if voucher has already been cashed
        $sErrorMessages = $this->filterUsedVoucher($userId, $voucherDetails);
        if (!empty($sErrorMessages)) {
            return array("sErrorFlag" => true, "sErrorMessages" => $sErrorMessages);
        }

        if ($voucherDetails["id"]) {
            // If we have voucher details, its a reusable code
            // We need to check how many times it has already been used
            $usedVoucherCount = $this->db->fetchRow('
                SELECT COUNT(id) AS vouchers
                FROM s_order_details
                WHERE articleordernumber = ?
                AND s_order_details.ordernumber != \'0\'',
                array($voucherDetails["ordercode"])
            ) ? : array();
        } else {
            // If we don't have voucher details yet, need to check if its a one-time code
            $voucherDetails = $this->db->fetchRow(
                'SELECT s_emarketing_voucher_codes.id AS id, s_emarketing_voucher_codes.code AS vouchercode,
                    description, numberofunits, customergroup, value, restrictarticles,
                    minimumcharge, shippingfree, bindtosupplier, taxconfig, valid_from,
                    valid_to, ordercode, modus, percental, strict, subshopID
                FROM s_emarketing_vouchers, s_emarketing_voucher_codes
                WHERE modus = 1
                AND s_emarketing_vouchers.id = s_emarketing_voucher_codes.voucherID
                AND LOWER(code) = ?
                AND cashed != 1
                AND (
                      (s_emarketing_vouchers.valid_to >= now()
                          AND s_emarketing_vouchers.valid_from <= now()
                      )
                      OR s_emarketing_vouchers.valid_to is NULL
                )',
                array($voucherCode)
            );
            $individualCode = ($voucherDetails && $voucherDetails["description"]);
        }

        // Interrupt the operation if one of the following occurs:
        // 1 - No voucher details were found (individual or reusable)
        // 2 - No voucher code
        // 3 - Voucher is reusable and has already been used to the limit
        if (!$voucherDetails
            || !$voucherCode
            || ($voucherDetails["numberofunits"] <= $usedVoucherCount["vouchers"] && !$individualCode)
        ) {
            $sErrorMessages[] = $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                'VoucherFailureNotFound',
                'Voucher could not be found or is not valid anymore'
            );
            return array("sErrorFlag" => true, "sErrorMessages" => $sErrorMessages);
        }

        $restrictDiscount = !empty($voucherDetails["strict"]);

        // If voucher is limited to a specific subshop, filter that and return on failure
        $sErrorMessages = $this->filterSubShopVoucher($voucherDetails);
        if (!empty($sErrorMessages)) {
            return array("sErrorFlag" => true, "sErrorMessages" => $sErrorMessages);
        }

        // Check if the basket already has a voucher, and break if it does
        $chkBasket = $this->db->fetchRow(
            'SELECT id
            FROM s_order_basket
            WHERE sessionID = ? AND modus = 2',
            array($this->session->get('sessionId'))
        );
        if ($chkBasket) {
            $sErrorMessages[] = $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                'VoucherFailureOnlyOnes',
                'Only one voucher can be processed in order'
            );
            return array("sErrorFlag" => true, "sErrorMessages" => $sErrorMessages);
        }

        // Check if the voucher is limited to a certain customer group, and validate that
        $sErrorMessages = $this->filterCustomerGroupVoucher($userId, $voucherDetails);
        if (!empty($sErrorMessages)) {
            return array("sErrorFlag" => true, "sErrorMessages" => $sErrorMessages);
        }

        // Check if the voucher is limited to certain articles, and validate that
        list($sErrorMessages, $restrictedArticles) = $this->filterArticleVoucher($voucherDetails);
        if (!empty($sErrorMessages)) {
            return array("sErrorFlag" => true, "sErrorMessages" => $sErrorMessages);
        }

        // Check if the voucher is limited to certain supplier, and validate that
        $sErrorMessages = $this->filterSupplierVoucher($voucherDetails);
        if (!empty($sErrorMessages)) {
            return array("sErrorFlag" => true, "sErrorMessages" => $sErrorMessages);
        }

        // Calculate the amount in the basket
        $allowedSupplierId = $voucherDetails["bindtosupplier"];
        if (!empty($restrictDiscount) && (!empty($restrictedArticles) || !empty($allowedSupplierId))) {
            $amount = $this->sGetAmountRestrictedArticles($restrictedArticles, $allowedSupplierId);
        } else {
            $amount = $this->sGetAmountArticles();
        }

        // Including currency factor
        if ($this->sSYSTEM->sCurrency["factor"] && empty($voucherDetails["percental"])) {
            $factor = $this->sSYSTEM->sCurrency["factor"];
            $voucherDetails["value"] *= $factor;
        } else {
            $factor = 1;
        }

        $basketValue = 0;
        if ($factor != 0) {
            $basketValue = $amount["totalAmount"] / $factor;
        }
        // Check if the basket's value is above the voucher's
        if ($basketValue < $voucherDetails["minimumcharge"]) {
            $sErrorMessages[] = str_replace(
                "{sMinimumCharge}",
                $voucherDetails["minimumcharge"],
                $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                    'VoucherFailureMinimumCharge',
                    'The minimum charge for this voucher is {sMinimumCharge}'
                )
            );
            return array( "sErrorFlag" => true, "sErrorMessages" => $sErrorMessages);
        }

        $timeInsert = date("Y-m-d H:i:s");

        $voucherName = $this->snippetManager
            ->getNamespace('backend/static/discounts_surcharges')
            ->get('voucher_name', 'Voucher');

        if ($voucherDetails["percental"]) {
            $value = $voucherDetails["value"];
            $voucherName .= " ".$value." %";
            $voucherDetails["value"] = ($amount["totalAmount"] / 100) * floatval($value);
        }

        // Tax calculation for vouchers
        list($taxRate, $tax, $voucherDetails, $freeShipping) = $this->calculateVoucherValues($voucherDetails);

        // Finally, add the discount entry to the basket
        $sql = "
        INSERT INTO s_order_basket (
          sessionID, articlename, articleID, ordernumber, shippingfree,
          quantity, price, netprice,tax_rate, datum, modus, currencyFactor
        )
        VALUES (?,?,?,?,?,1,?,?,?,?,2,?)
        ";
        $params = array(
            $this->session->get('sessionId'),
            $voucherName,
            $voucherDetails["id"],
            $voucherDetails["ordercode"],
            $freeShipping,
            $voucherDetails["value"],
            $tax,
            $taxRate,
            $timeInsert,
            $this->sSYSTEM->sCurrency["factor"]
        );
        $sql = $this->eventManager->filter(
            'Shopware_Modules_Basket_AddVoucher_FilterSql',
            $sql,
            array(
                'subject' => $this,
                "params" => $params,
                "voucher" => $voucherDetails,
                "vouchername" => $voucherName,
                "shippingfree" => $freeShipping,
                "tax" => $tax
            )
        );

        return (bool) ($this->db->query($sql, $params));
    }

    /**
     * Get articleId of all products from cart
     * Used in CheckoutController
     *
     * @return array|null List of article ids in current basket, or null if none
     */
    public function sGetBasketIds()
    {
        $articles = $this->db->fetchCol(
            'SELECT DISTINCT articleID
                FROM s_order_basket
                WHERE sessionID = ?
                AND modus = 0
                ORDER BY modus ASC, datum DESC',
            array($this->session->get('sessionId'))
        );

        return empty($articles) ? null : $articles;
    }

    /**
     * Check if minimum charging is reached
     * Used only in CheckoutController::getMinimumCharge()
     *
     * @return double|false Minimum order value in current currency, or false
     */
    public function sCheckMinimumCharge()
    {
        $minimumOrder = $this->sSYSTEM->sUSERGROUPDATA["minimumorder"];
        $factor = $this->sSYSTEM->sCurrency["factor"];

        if ($minimumOrder && !$this->sSYSTEM->sUSERGROUPDATA["minimumordersurcharge"]) {
            $amount = $this->sGetAmount();
            if ($amount["totalAmount"] < ($minimumOrder * $factor)) {
                return ($minimumOrder * $factor);
            }
        }
        return false;
    }

    /**
     * Add surcharge for payment means to cart
     * Used only internally in sBasket::sGetBasket
     *
     * @return null|false False on failure, null on success
     */
    public function sInsertSurcharge()
    {
        $name = $this->config->get('sSURCHARGENUMBER', "SURCHARGE");

        // Delete previous inserted discounts
        $this->db->delete(
            's_order_basket',
            array(
                'sessionID = ?' => $this->session->get('sessionId'),
                'ordernumber = ?' => $name
            )
        );

        if (!$this->sCountBasket()) {
            return false;
        }

        $minimumOrder = $this->sSYSTEM->sUSERGROUPDATA['minimumorder'];
        $minimumOrderSurcharge = $this->sSYSTEM->sUSERGROUPDATA['minimumordersurcharge'];
        if ($minimumOrder && $minimumOrderSurcharge) {
            $amount = $this->sGetAmount();

            if ($amount["totalAmount"] < $minimumOrder) {
                $taxAutoMode = $this->config->get('sTAXAUTOMODE');
                if (!empty($taxAutoMode)) {
                    $tax = $this->getMaxTax();
                } else {
                    $tax = $this->config->get('sDISCOUNTTAX');
                }

                if (empty($tax)) {
                    $tax = 19;
                }

                if ((!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])) {
                    $discountNet = $minimumOrderSurcharge;
                } else {
                    $discountNet = round($minimumOrderSurcharge / (100+$tax) * 100, 3);
                }

                if ($this->sSYSTEM->sCurrency["factor"]) {
                    $factor = $this->sSYSTEM->sCurrency["factor"];
                    $discountNet *= $factor;
                } else {
                    $factor = 1;
                }

                $surcharge = $minimumOrderSurcharge * $factor;
                $surchargeName = $this->snippetManager
                    ->getNamespace('backend/static/discounts_surcharges')
                    ->get('surcharge_name');

                $this->db->insert(
                    's_order_basket',
                    $params = array(
                        'sessionID'      => $this->session->get('sessionId'),
                        'articlename'    => $surchargeName,
                        'articleID'      => 0,
                        'ordernumber'    => $name,
                        'quantity'       => 1,
                        'price'          => $surcharge,
                        'netprice'       => $discountNet,
                        'tax_rate'       => $tax,
                        'datum'          => new Zend_Date(),
                        'modus'          => 4,
                        'currencyFactor' => $this->sSYSTEM->sCurrency["factor"]
                    )
                );
            }
        }
    }

    /**
     * Add percentual surcharge
     * Used only internally in sBasket::sGetBasket
     *
     * @return void|false False on failure, null on success
     */
    public function sInsertSurchargePercent()
    {
        if (!$this->session->get('sUserId')) {
            if (!$this->session->get('sPaymentID')) {
                return false;
            } else {
                $paymentInfo = $this->db->fetchRow(
                    'SELECT debit_percent
                    FROM s_core_paymentmeans
                    WHERE id = ?',
                    array($this->session->get('sPaymentID'))
                ) ? : array();
            }
        } else {
            $userData =  $this->db->fetchRow(
                'SELECT paymentID FROM s_user WHERE id = ?',
                array(intval($this->session->get('sUserId')))
            ) ? : array();
            $paymentInfo = $this->db->fetchRow(
                'SELECT debit_percent FROM s_core_paymentmeans WHERE id = ?',
                array($userData["paymentID"])
            ) ? : array();
        }

        $name = $this->config->get('sPAYMENTSURCHARGENUMBER', 'PAYMENTSURCHARGE');
        // Depends on payment mean
        $percent = $paymentInfo["debit_percent"];

        $this->db->query(
            'DELETE FROM s_order_basket WHERE sessionID = ? AND ordernumber = ?',
            array($this->session->get('sessionId'), $name)
        );

        if (!$this->sCountBasket()) {
            return false;
        }

        if (!empty($percent)) {
            $amount = $this->sGetAmount();

            if ($percent >= 0) {
                $surchargeName = $this->snippetManager
                    ->getNamespace('backend/static/discounts_surcharges')
                    ->get('payment_surcharge_add');
            } else {
                $surchargeName = $this->snippetManager
                    ->getNamespace('backend/static/discounts_surcharges')
                    ->get('payment_surcharge_dev');
            }

            $surcharge = $amount["totalAmount"] / 100 * $percent;

            $taxAutoMode = $this->config->get('sTAXAUTOMODE');
            if (!empty($taxAutoMode)) {
                $tax = $this->getMaxTax();
            } else {
                $tax = $this->config->get('sDISCOUNTTAX');
            }

            if (!$tax) {
                $tax = 119;
            }

            if ((!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])) {
                $discountNet = $surcharge;
            } else {
                $discountNet = round($surcharge / (100+$tax) * 100, 3);
            }

            $this->db->insert(
                's_order_basket',
                array(
                    'sessionID'      => $this->session->get('sessionId'),
                    'articlename'    => $surchargeName,
                    'articleID'      => 0,
                    'ordernumber'    => $name,
                    'quantity'       => 1,
                    'price'          => $surcharge,
                    'netprice'       => $discountNet,
                    'tax_rate'       => $tax,
                    'datum'          => new Zend_Date(),
                    'modus'          => 4,
                    'currencyFactor' => $this->sSYSTEM->sCurrency['factor']
                )
            );
        }
    }

    /**
     * Fetch count of products in basket
     * Used in multiple locations
     *
     * @return array Number
     */
    public function sCountBasket()
    {
        return $this->db->fetchOne(
            'SELECT COUNT(*) FROM s_order_basket WHERE modus = 0 AND sessionID = ?',
            array($this->session->get('sessionId'))
        );
    }

    /**
     * Get all basket positions
     * Used in multiple location
     *
     * @return array Basket content
     */
    public function sGetBasket()
    {
        // Refresh basket prices
        $basketData = $this->db->fetchAll(
            'SELECT id, modus, quantity
            FROM s_order_basket
            WHERE sessionID = ?',
            array($this->session->get('sessionId'))
        );
        foreach ($basketData as $basketContent) {
            if (empty($basketContent["modus"])) {
                $this->sUpdateArticle($basketContent["id"], $basketContent["quantity"]);
            }
        }

        // Check, if we have some free products for the client
        $this->sInsertPremium();

        // Delete previous given discounts
        $premiumShipping = $this->config->get('sPREMIUMSHIPPIUNG');
        if (empty($premiumShipping)) {
            $this->db->delete(
                's_order_basket',
                array(
                    'sessionID = ?' => $this->session->get('sessionId'),
                    'modus = 3'
                )
            );
        }
        // Check for surcharges
        $this->sInsertSurcharge();

        // Check for skonto / percent surcharges
        $this->sInsertSurchargePercent();

        // Calculate global basket discount
        $this->sInsertDiscount();

        $getArticles = $this->loadBasketArticles();

        if (empty($getArticles)) {
            return array();
        }

        // Reformatting data, add additional data fields to array
        list(
            $getArticles,
            $totalAmount,
            $totalAmountWithTax,
            $totalCount,
            $totalAmountNet
        ) = $this->getBasketArticles($getArticles);

        if ($totalAmount < 0 || empty($totalCount)) {
            if (!$this->eventManager->notifyUntil('Shopware_Modules_Basket_sGetBasket_AllowEmptyBasket', array(
                'articles' => $getArticles,
                'totalAmount' => $totalAmount,
                'totalAmountWithTax' => $totalAmountWithTax,
                'totalCount' => $totalCount,
                'totalAmountNet' => $totalAmountNet
            ))) {
                return array();
            }
        }

        $totalAmountNumeric = $totalAmount;
        $totalAmount = $this->moduleManager->Articles()->sFormatPrice($totalAmount);

        $totalAmountWithTaxNumeric = $totalAmountWithTax;
        $totalAmountWithTax = $this->moduleManager->Articles()->sFormatPrice($totalAmountWithTax);

        $totalAmountNetNumeric = $totalAmountNet;

        $totalAmountNet = $this->moduleManager->Articles()->sFormatPrice($totalAmountNet);

        $result = array(
            "content" => $getArticles,
            "Amount" => $totalAmount,
            "AmountNet" => $totalAmountNet,
            "Quantity" => $totalCount,
            "AmountNumeric" => $totalAmountNumeric,
            "AmountNetNumeric" => $totalAmountNetNumeric,
            "AmountWithTax" => $totalAmountWithTax,
            "AmountWithTaxNumeric" => $totalAmountWithTaxNumeric
        );

        $lastArticle = $this->session->get('sLastArticle');
        if (!empty($lastArticle)) {
            $result["sLastActiveArticle"] = array(
                "id" => $lastArticle,
                "link" => $this->config->get('sBASEFILE')
                    . "?sViewport=detail&sDetails=" . $lastArticle
            );
        }

        if (!empty($result["content"])) {
            foreach ($result["content"] as $key => $value) {
                if (!empty($value['amountWithTax'])) {
                    $t = round(str_replace(",", ".", $value['amountWithTax']), 2);
                } else {
                    $t = str_replace(",", ".", $value["price"]);
                    $t = floatval(round($t * $value["quantity"], 2));
                }
                if (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"]) {
                    $p = floatval($this->moduleManager->Articles()->sRound(
                        $this->moduleManager->Articles()->sRound(
                            round($value["netprice"], 2) * $value["quantity"])
                        )
                    );
                } else {
                    $p = floatval($this->moduleManager->Articles()->sRound(
                        $this->moduleManager->Articles()->sRound(
                            $value["netprice"] * $value["quantity"])
                        )
                    );
                }
                $calcDifference = $this->moduleManager->Articles()->sFormatPrice($t - $p);
                $result["content"][$key]["tax"] = $calcDifference;
            }
        }
        $result = $this->eventManager->filter(
            'Shopware_Modules_Basket_GetBasket_FilterResult',
            $result,
            array('subject' => $this)
        );

        return $result;
    }

    /**
     * Add product to wishlist
     * Used only in NoteController::addAction()
     *
     * @param int $articleID
     * @param string $articleName
     * @param string $articleOrderNumber
     * @throws Enlight_Exception If entry could not be added to database
     * @return bool If operation was successful
     */
    public function sAddNote($articleID, $articleName, $articleOrderNumber)
    {
        $cookieData = $this->front->Request()->getCookie();
        $uniqueId = $this->front->Request()->getCookie('sUniqueID');

        if (!empty($cookieData) && empty($uniqueId)) {
            $uniqueId = md5(uniqid(rand()));
            $this->front->Response()->setCookie('sUniqueID', $uniqueId, Time()+(86400*360), '/');
        }

        // Check if this article is already noted
        $checkForArticleId = $this->db->fetchOne(
            'SELECT id FROM s_order_notes WHERE sUniqueID = ? AND ordernumber = ?',
            array($uniqueId, $articleOrderNumber)
        );

        if (!$checkForArticleId) {
            $queryNewPrice = $this->db->insert(
                's_order_notes',
                array(
                    'sUniqueID'   => empty($uniqueId) ? $this->session->get('sessionId') : $uniqueId,
                    'userID'      => $this->session->get('sUserId') ? : '0',
                    'articlename' => $articleName,
                    'articleID'   => $articleID,
                    'ordernumber' => $articleOrderNumber,
                    'datum'       => date("Y-m-d H:i:s")
                )
            );

            if (!$queryNewPrice) {
                throw new Enlight_Exception("sBasket##sAddNote##01 Error in SQL-query");
            }
        }
        return true;
    }

    /**
     * Get all products current on wishlist
     * Used in the NoteController
     *
     * @return array Article notes
     */
    public function sGetNotes()
    {
        $notes = $this->getNoteProducts();
        if (empty($notes)) {
            return $notes;
        }

        $numbers = array_column($notes, 'ordernumber');

        $context = Shopware()->Container()->get('shopware_storefront.context_service')->getProductContext();

        $products = Shopware()->Container()->get('shopware_storefront.list_product_service')
            ->getList($numbers, $context);

        $products = Shopware()->Container()->get('shopware_storefront.additional_text_service')
            ->buildAdditionalTextLists($products, $context);

        $promotions = array();
        /**@var $product ListProduct */
        foreach ($products as $product) {
            $note = $notes[$product->getNumber()];

            $promotions[] = $this->convertListProductToNote($product, $note);
        }

        return $this->eventManager->filter(
            'Shopware_Modules_Basket_GetNotes_FilterPromotions',
            $promotions,
            array('products' => $products)
        );
    }

    /**
     * @param ListProduct $product
     * @param array $note
     * @return array
     */
    private function convertListProductToNote(ListProduct $product, array $note)
    {
        $structConverter = Shopware()->Container()->get('legacy_struct_converter');
        $promotion = $structConverter->convertListProductStruct($product);

        if ($voteAverage = $product->getVoteAverage()) {
            $average = $structConverter->convertVoteAverageStruct($voteAverage);
            $average['averange'] = $average['averange'] / 2;
            $promotion['sVoteAverange'] = $average;
        }

        $promotion["id"] = $note["id"];
        $promotion["datum_add"] = $note["datum"];
        $promotion["articlename"] = $promotion["articleName"];
        if ($product->hasConfigurator() && $product->getAdditional()) {
            $promotion["articlename"] .= ' ' . $product->getAdditional();
        }
        $promotion["linkDelete"] = $this->config->get('sBASEFILE')."?sViewport=note&sDelete=". $note['id'];

        return $promotion;
    }

    private function getNoteProducts()
    {
        $responseCookies = $this->front->Response()->getCookies();

        if (isset($responseCookies['sUniqueID']['value']) && $responseCookies['sUniqueID']['value']) {
            $uniqueId = $responseCookies['sUniqueID']['value'];
        } else {
            $uniqueId = $this->front->Request()->getCookie('sUniqueID');
        }

        $notes = $this->db->fetchAssoc('
            SELECT n.ordernumber as arrayKey, n.*
            FROM s_order_notes n, s_articles a
            WHERE (sUniqueID = ? OR (userID != 0 AND userID = ?))
            AND a.id = n.articleID AND a.active = 1
            ORDER BY n.datum DESC',
            array(
                empty($uniqueId) ? $this->session->get('sessionId') : $uniqueId,
                $this->session->get('sUserId', 0)
            )
        );

        return $notes;
    }

    /**
     * Returns the number of wishlist entries
     * Used in several locations
     *
     * @return int
     */
    public function sCountNotes()
    {
        $responseCookies = $this->front->Response()->getCookies();

        if (isset($responseCookies['sUniqueID']['value']) && $responseCookies['sUniqueID']['value']) {
            $uniqueId = $responseCookies['sUniqueID']['value'];
        } else {
            $uniqueId = $this->front->Request()->getCookie('sUniqueID');
        }

        $count = (int) $this->db->fetchOne('
            SELECT COUNT(*) FROM s_order_notes n, s_articles a
            WHERE (sUniqueID = ? OR (userID != 0 AND userID = ?))
            AND a.id = n.articleID AND a.active = 1
        ', array(
            empty($uniqueId) ? $this->session->get('sessionId') : $uniqueId,
            $this->session->get('sUserId', 0)
        ));
        return $count;
    }

    /**
     * Delete a certain position from note
     * Used internally in sBasket and in NoteController
     *
     * @param int $id Id of the wishlist line
     * @throws Enlight_Exception If entry could not be deleted from database
     * @return bool if the operation was successful
     */
    public function sDeleteNote($id)
    {
        $id = (int) $id;

        if (empty($id)) {
            return false;
        }

        $delete = $this->db->query(
            'DELETE FROM s_order_notes
            WHERE (sUniqueID = ? OR (userID = ?  AND userID != 0))
            AND id=?',
            array(
                $this->front->Request()->getCookie('sUniqueID'),
                $this->session->get('sUserId'),
                $id
            )
        );
        if (!$delete) {
            throw new Enlight_Exception('Basket sDeleteNote ##01 Could not delete item');
        }
        return true;
    }

    /**
     * Update quantity / price of a certain cart position
     * Used in several locations
     *
     * @param int $id Basket entry id
     * @param int $quantity Quantity
     * @throws Enlight_Exception If database could not be updated
     * @return boolean
     */
    public function sUpdateArticle($id, $quantity)
    {
        $quantity = intval($quantity);
        $id = intval($id);

        if (
            $this->eventManager->notifyUntil(
                'Shopware_Modules_Basket_UpdateArticle_Start',
                array('subject' => $this, 'id' => $id, "quantity" => $quantity)
            )
        ) {
            return false;
        }

        if (!$this->session->get('sessionId') || !$id) {
            return false;
        }

        list($queryAdditionalInfo, $quantity) = $this->getAdditionalInfoForUpdateArticle($id, $quantity);
        $queryNewPrice = $this->getPriceForUpdateArticle($id, $quantity, $queryAdditionalInfo);


        if (empty($queryNewPrice["price"]) && empty($queryNewPrice["config"])) {
            // If no price is set for default customer group, delete article from basket
            $this->sDeleteArticle($id);
            return false;
        }

        list($taxRate, $netPrice, $grossPrice) = $this->getTaxesForUpdateArticle($quantity, $queryNewPrice, $queryAdditionalInfo);

        $sql = "
            UPDATE s_order_basket
            SET quantity = ?, price = ?, netprice = ?, currencyFactor = ?, tax_rate = ?
            WHERE id = ? AND sessionID = ? AND modus = 0
            ";
        $sql = $this->eventManager->filter(
            'Shopware_Modules_Basket_UpdateArticle_FilterSqlDefault',
            $sql,
            array(
                'subject' => $this,
                'id' => $id,
                "quantity" => $quantity,
                "price" => $grossPrice,
                "netprice" => $netPrice,
                "currencyFactor" => $this->sSYSTEM->sCurrency["factor"]
            )
        );

        if ($taxRate === false) {
            $taxRate = ($grossPrice == $netPrice) ? 0.00 : $queryNewPrice["tax"];
        }

        $update = $this->db->query(
            $sql,
            array(
                $quantity,
                $grossPrice,
                $netPrice,
                $this->sSYSTEM->sCurrency["factor"],
                $taxRate,
                $id,
                $this->session->get('sessionId')
            )
        );

        $this->sUpdateVoucher();

        if (!$update || !$queryNewPrice) {
            throw new Enlight_Exception("Basket Update ##01 Could not update quantity".$sql);
        }
    }

    /**
     * Check if the current basket has any ESD article
     * Used in sAdmin and CheckoutController
     *
     * @return bool If an ESD article is present in the current basket
     */
    public function sCheckForESD()
    {
        $getArticlesId = $this->db->fetchOne(
            'SELECT id
            FROM s_order_basket
            WHERE sessionID = ?
            AND esdarticle = 1
            LIMIT 1;',
            array($this->session->get('sessionId'))
        );

        return (bool) $getArticlesId;
    }

    /**
     * Truncate cart
     * Used on sAdmin tests and SwagBonusSystem
     * See @ticket PT-1845
     *
     * @return void|false False on no session, null otherwise
     */
    public function sDeleteBasket()
    {
        $sessionId = $this->session->get('sessionId');
        if (empty($sessionId)) {
            return false;
        }

        $this->db->delete(
            's_order_basket',
            array('sessionID = ?' => $sessionId)
        );
    }


    /**
     * Delete a certain position from the basket
     * Used in multiple locations
     *
     * @param int $id Id of the basket line
     * @throws Enlight_Exception If entry could not be deleted from the database
     * @return null
     */
    public function sDeleteArticle($id)
    {
        $id = (int) $id;
        $modus = $this->db->fetchOne(
            'SELECT modus FROM s_order_basket WHERE id = ?',
            array($id)
        );

        if ($id && $id != "voucher") {
            $this->db->delete(
                's_order_basket',
                array(
                    'sessionID = ?' => $this->session->get('sessionId'),
                    'id = ?' => $id
                )
            );
            if (empty($modus)) {
                $this->sUpdateVoucher();
            }
        }
    }

    /**
     * Add product to cart
     * Used in multiple locations
     *
     * @param string $ordernumber Order number (s_articles_details.ordernumber)
     * @param int $quantity Amount
     * @throws Enlight_Exception If no price could be determined, or a database error occurs
     * @return int|false Id of the inserted basket entry, or false on failure
     */
    public function sAddArticle($ordernumber, $quantity = 1)
    {
        $sessionId = $this->session->get('sessionId');
        if ($this->session->get('Bot') || empty($sessionId)) {
            return false;
        }

        $quantity = (empty($quantity) || !is_numeric($quantity)) ? 1 : (int) $quantity;
        if ($quantity <= 0) {
            $quantity = 1;
        }

        if (
            $this->eventManager->notifyUntil(
                'Shopware_Modules_Basket_AddArticle_Start',
                array(
                    'subject' => $this,
                    'id' => $ordernumber,
                    "quantity" => $quantity
                )
            )
        ) {
            return false;
        }

        $article = $this->getArticleForAddArticle($ordernumber);

        if (!$article) {
            return false;
        }

        $chkBasketForArticle = $this->checkIfArticleIsInBasket(
            $article["articleID"],
            $article["ordernumber"],
            $sessionId
        );

        // Shopware 3.5.0 / sth / laststock - instock check
        if (!empty($chkBasketForArticle["id"])) {
            if (
                $article["laststock"] == true
                && $article["instock"] < ($chkBasketForArticle["quantity"] + $quantity)
            ) {
                $quantity -= $chkBasketForArticle["quantity"];
            }
        } else {
            if ($article["laststock"] == true && $article["instock"] <= $quantity) {
                $quantity = $article["instock"];
                if ($quantity <= 0) {
                    return;
                }
            }
        }

        if ($chkBasketForArticle) {
            // Article is already in basket, update quantity
            $quantity += $chkBasketForArticle["quantity"];

            $this->sUpdateArticle($chkBasketForArticle["id"], $quantity);
            return $chkBasketForArticle["id"];
        }

        $price = $this->getPriceForAddArticle($article);

        // For variants, extend the article name
        if ($article["additionaltext"]) {
            $article["articleName"] .= " " . $article["additionaltext"];
        }

        if (!$article["shippingfree"]) {
            $article["shippingfree"] = "0";
        }

        // Check if article is an esd-article
        // - add flag to basket
        $getEsd = $this->db->fetchOne(
            'SELECT s_articles_esd.id AS id, serials
            FROM s_articles_esd, s_articles_details
            WHERE s_articles_esd.articleID = ?
            AND s_articles_esd.articledetailsID = s_articles_details.id
            AND s_articles_details.ordernumber = ?',
            array($article["articleID"], $article["ordernumber"])
        );

        $sEsd = $getEsd ? '1' : '0';

        $quantity = (int) $quantity;
        $sql = "
            INSERT INTO s_order_basket (id, sessionID, userID, articlename, articleID,
                ordernumber, shippingfree, quantity, price, netprice,
                datum, esdarticle, partnerID, config)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )
        ";

        $params = array(
            '',
            (string) $sessionId,
            (string) $this->session->get('sUserId'),
            $article["articleName"],
            $article["articleID"],
            (string) $article["ordernumber"],
            $article["shippingfree"],
            $quantity,
            $price["price"],
            $price["netprice"],
            date("Y-m-d H:i:s"),
            $sEsd,
            (string) $this->session->get('sPartner'),
            ''
        );

        $sql = $this->eventManager->filter(
            'Shopware_Modules_Basket_AddArticle_FilterSql',
            $sql,
            array(
                'subject' => $this,
                'article' => $article,
                'price' => $price,
                'esd' => $sEsd,
                'quantity' => $quantity,
                'partner' => $this->session->get('sPartner')
            )
        );

        $result = $this->db->query($sql, $params);

        if (!$result) {
            throw new Enlight_Exception("BASKET-INSERT #02 SQL-Error".$sql);
        }
        $insertId = $this->db->lastInsertId();

        $this->db->insert(
            's_order_basket_attributes',
            array(
                'basketID' => $insertId,
                'attribute1' => ''
            )
        );

        $this->sUpdateArticle($insertId, $quantity);

        return $insertId;
    }

    /**
     * Check if article is already in basket
     *
     * @param int    $articleId
     * @param string $ordernumber
     * @param string $sessionId
     * @return array Example: ["id" => "731", "quantity" => "100"]
     */
    private function checkIfArticleIsInBasket($articleId, $ordernumber, $sessionId)
    {
        $builder = Shopware()->Models()->getConnection()->createQueryBuilder();

        $builder->select('id', 'quantity')
            ->from('s_order_basket', 'basket')
            ->where('articleID = :articleId')
            ->andWhere('sessionID = :sessionId')
            ->andWhere('ordernumber = :ordernumber')
            ->andWhere('modus != 1')
            ->setParameter('articleId', $articleId)
            ->setParameter('sessionId', $sessionId)
            ->setParameter('ordernumber', $ordernumber);

        $this->eventManager->notify(
            'Shopware_Modules_Basket_AddArticle_CheckBasketForArticle',
            array(
                'queryBuilder' => $builder,
                'subject'      => $this
            )
        );

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $builder->execute();

        return $statement->fetch() ?: array();
    }

    /**
     * Clear basket for current user
     */
    public function clearBasket()
    {
        $this->db->executeUpdate(
            'DELETE FROM s_order_basket WHERE sessionID= :sessionId',
            ['sessionId' => $this->session->get('sessionId')]
        );
    }

    /**
     * Refresh basket after login / currency change
     * Used in multiple locations
     */
    public function sRefreshBasket()
    {
        // Update basket data
        $this->moduleManager->Admin()->sGetUserData();
        $this->sGetBasket();
        $this->moduleManager->Admin()->sGetPremiumShippingcosts();

        // Update basket data in session
        $this->session->offsetSet('sBasketCurrency', Shopware()->Shop()->getCurrency()->getId());
        $this->session->offsetSet('sBasketQuantity', $this->sCountBasket());
        $amount = $this->sGetAmount();
        $this->session->offsetSet('sBasketAmount', empty($amount) ? 0 : array_shift($amount));
    }

    /**
     * Checks if the vouchers on the current basket have already been used.
     * Return true if the current cart doesn't have a voucher or if the voucher is valid
     * Returns false if the current voucher has already been used.
     *
     * @param $sessionId
     * @param $userId
     * @return bool
     */
    public function validateVoucher($sessionId, $userId)
    {
        $sql = "
            SELECT
                vouchers.modus AS voucherMode,
                details.articleID as voucherId,
                details.articleordernumber AS voucherOrderNumber,
                vouchers.numorder AS maxPerUser,
                vouchers.numberofunits AS maxGlobal
            FROM s_emarketing_vouchers AS vouchers
            LEFT JOIN s_order_details details ON vouchers.ordercode = details.articleordernumber
            LEFT JOIN s_order AS orders ON details.orderID = orders.id
            WHERE orders.temporaryID = :sessionId
            AND details.modus = 2
        ";

        $voucherData = $this->db->fetchRow($sql, array('sessionId' => $sessionId));

        if (!$voucherData) {
            return true;
        }

        if ($voucherData['voucherMode'] == 1) {
            $sql = "
                SELECT id
                FROM s_emarketing_voucher_codes
                WHERE id = :voucherId AND cashed != 1
            ";

            $result = $this->db->fetchRow($sql, array('voucherId' => $voucherData['voucherId']));

            return (bool) $result;
        }

        $sql = "
            SELECT
              COUNT(DISTINCT details.id) AS usedVoucherCount,
              SUM(CASE WHEN orders.userID = :userID THEN 1 ELSE 0 END) usedByUserVoucherCount
            FROM s_order_details details
            LEFT JOIN s_order orders ON orders.id = details.orderID
            WHERE articleordernumber = :voucherOrderNumber
            AND details.ordernumber != 0
        ";

        $result = $this->db->fetchRow(
            $sql,
            array(
                'voucherOrderNumber' => $voucherData['voucherOrderNumber'],
                'userID' => $userId
            )
        );

        if (!$result) {
            return true;
        }

        $passedVoucherPerUserLimit = $result['usedByUserVoucherCount'] < $voucherData['maxPerUser'];
        $passedVoucherGlobalLimit = $result['usedVoucherCount'] < $voucherData['maxGlobal'];

        return ($passedVoucherPerUserLimit && $passedVoucherGlobalLimit);
    }

    /**
     * Check if voucher has already been cashed
     *
     * @param int $userId The current user id
     * @param array $voucherDetails The voucher details
     * @return array Messages for detected errors
     */
    private function filterUsedVoucher($userId, $voucherDetails)
    {
        $sErrorMessages = array();
        if ($userId && $voucherDetails["id"]) {
            $queryVoucher = $this->db->fetchAll(
                'SELECT s_order_details.id AS id
                    FROM s_order, s_order_details
                    WHERE s_order.userID = ?
                    AND s_order_details.orderID = s_order.id
                    AND s_order_details.articleordernumber = ?
                    AND s_order_details.ordernumber != \'0\'',
                array(
                    $userId,
                    $voucherDetails["ordercode"]
                )
            );

            if (count($queryVoucher) >= $voucherDetails["numorder"] && !$voucherDetails["modus"]) {
                $sErrorMessages[] = $this->snippetManager
                    ->getNamespace('frontend/basket/internalMessages')->get(
                        'VoucherFailureAlreadyUsed',
                        'This voucher was used in an previous order'
                    );
                return $sErrorMessages;
            }
        }
        return $sErrorMessages;
    }

    /**
     * Filter voucher by subshop
     *
     * @param array $voucherDetails
     * @return array Messages for detected errors
     */
    private function filterSubShopVoucher($voucherDetails)
    {
        $sErrorMessages = array();

        if (!empty($voucherDetails["subshopID"])) {
            if ($this->contextService->getShopContext()->getShop()->getId() != $voucherDetails["subshopID"]) {
                $sErrorMessages[] = $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                    'VoucherFailureNotFound',
                    'Voucher could not be found or is not valid anymore'
                );
            }
        }

        return $sErrorMessages;
    }

    /**
     * Filter voucher by customer group
     *
     * @param int $userId The current user id
     * @param array $voucherDetails The voucher details
     * @return array Messages for detected errors
     */
    private function filterCustomerGroupVoucher($userId, $voucherDetails)
    {
        $sErrorMessages = array();

        if (!empty($voucherDetails["customergroup"])) {
            if (!empty($userId)) {
                // Get customer group
                $queryCustomerGroup = $this->db->fetchRow(
                    'SELECT s_core_customergroups.id, customergroup
                    FROM s_user, s_core_customergroups
                    WHERE s_user.id = ?
                    AND s_user.customergroup = s_core_customergroups.groupkey',
                    array($userId)
                );
            }
            $customerGroup = $queryCustomerGroup["customergroup"];
            if ($customerGroup != $voucherDetails["customergroup"]
                && $voucherDetails["customergroup"] != $queryCustomerGroup["id"]
                && $voucherDetails["customergroup"] != $this->sSYSTEM->sUSERGROUPDATA["id"]
            ) {
                $sErrorMessages[] = $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                    'VoucherFailureCustomerGroup',
                    'This voucher is not available for your customer group'
                );
            }
        }

        return $sErrorMessages;
    }

    /**
     * Filter voucher by article id
     *
     * @param array $voucherDetails The voucher details
     * @return array Array of arrays, containing messages for detected errors and restricted articles
     */
    private function filterArticleVoucher($voucherDetails)
    {
        $sErrorMessages = array();

        if (!empty($voucherDetails["restrictarticles"]) && strlen($voucherDetails["restrictarticles"]) > 5) {
            $restrictedArticles = explode(";", $voucherDetails["restrictarticles"]);
            if (count($restrictedArticles) == 0) {
                $restrictedArticles[] = $voucherDetails["restrictarticles"];
            }

            $foundMatchingArticle = $this->db->fetchOne($this->db
                    ->select()
                    ->from('s_order_basket', 'id')
                    ->where('sessionID = ?', $this->session->get('sessionId'))
                    ->where('modus = 0')
                    ->where('ordernumber IN (?)', $restrictedArticles)
            );

            if (empty($foundMatchingArticle)) {
                $sErrorMessages[] = $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                    'VoucherFailureProducts',
                    'This voucher is only available in combination with certain products'
                );
            }
        }

        return array($sErrorMessages, $restrictedArticles);
    }

    /**
     * Filter voucher by article id
     *
     * @param array $voucherDetails The voucher details
     * @return array Messages for detected errors
     */
    private function filterSupplierVoucher($voucherDetails)
    {
        $sErrorMessages = array();

        $allowedSupplierId = $voucherDetails["bindtosupplier"];
        if ($allowedSupplierId) {
            $allowedBasketEntriesBySupplier = $this->db->fetchRow(
                'SELECT s_order_basket.id
                FROM s_order_basket, s_articles, s_articles_supplier
                WHERE s_order_basket.articleID = s_articles.id
                AND s_articles.supplierID = ?
                AND s_order_basket.sessionID = ?',
                array($allowedSupplierId, $this->session->get('sessionId'))
            );

            if (!$allowedBasketEntriesBySupplier) {
                $allowedSupplierName = $this->db->fetchRow(
                    'SELECT name FROM s_articles_supplier WHERE id = ?',
                    array($allowedSupplierId)
                );

                $sErrorMessages[] = str_replace(
                    "{sSupplier}",
                    $allowedSupplierName["name"],
                    $this->snippetManager->getNamespace('frontend/basket/internalMessages')->get(
                        'VoucherFailureSupplier',
                        'This voucher is only available for products from {sSupplier}'
                    )
                );
            }
        }
        return $sErrorMessages;
    }

    /**
     * @param $voucherDetails
     * @return array
     */
    private function calculateVoucherValues($voucherDetails)
    {
        $taxRate = 0;
        if (
            (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])
            || $voucherDetails["taxconfig"] == "none"
        ) {
            // if net customer group - calculate without tax
            $tax = $voucherDetails["value"] * -1;
            if ($voucherDetails["taxconfig"] == "default" || empty($voucherDetails["taxconfig"])) {
                $taxRate = $this->config->get('sVOUCHERTAX');
            } elseif ($voucherDetails["taxconfig"] == "auto") {
                $taxRate = $this->getMaxTax();
            } elseif (intval($voucherDetails["taxconfig"])) {
                $temporaryTax = $voucherDetails["taxconfig"];
                $getTaxRate = $this->db->fetchOne(
                    'SELECT tax FROM s_core_tax WHERE id = ?',
                    array($temporaryTax)
                );
                $taxRate = $getTaxRate;
            }
        } else {
            if ($voucherDetails["taxconfig"] == "default" || empty($voucherDetails["taxconfig"])) {
                $tax = round($voucherDetails["value"] / (100 + $this->config->get('sVOUCHERTAX')) * 100, 3) * -1;
                $taxRate = $this->config->get('sVOUCHERTAX');
                // Pre 3.5.4 behaviour
            } elseif ($voucherDetails["taxconfig"] == "auto") {
                // Check max. used tax-rate from basket
                $tax = $this->getMaxTax();
                $taxRate = $tax;
                $tax = round($voucherDetails["value"] / (100 + $tax) * 100, 3) * -1;
            } elseif (intval($voucherDetails["taxconfig"])) {
                // Fix defined tax
                $temporaryTax = $voucherDetails["taxconfig"];
                $getTaxRate = $this->db->fetchOne(
                    'SELECT tax FROM s_core_tax WHERE id = ?',
                    array($temporaryTax)
                );
                $taxRate = $getTaxRate;
                $tax = round($voucherDetails["value"] / (100 + (intval($getTaxRate))) * 100, 3) * -1;
            } else {
                // No tax
                $tax = $voucherDetails["value"] * -1;
            }
        }

        $voucherDetails["value"] = $voucherDetails["value"] * -1;

        if ($voucherDetails["shippingfree"]) {
            $freeShipping = "1";
        } else {
            $freeShipping = "0";
        }
        return array($taxRate, $tax, $voucherDetails, $freeShipping);
    }

    /**
     * Proxy to a cached version of self::getBasketArticlesUncached()
     *
     * Caches the result of self::getBasketArticlesUncached() in a
     * static variable using a hash of the input paramter
     * to invalidate the cache.
     *
     * @param array $getArticles
     * @return array
     */
    private function getBasketArticles(array $getArticles)
    {
        static $cache;
        static $cacheHash;

        $hash = md5(serialize($getArticles));
        if ($cache && $hash === $cacheHash) {
            return $cache;
        }

        $result = $this->getBasketArticlesUncached($getArticles);

        $cache = $result;
        $cacheHash = $hash;

        return $result;
    }

    /**
     * Loads relevant associated data for the provided articles
     * Used in sGetBasket
     *
     * @param $getArticles
     * @return array
     */
    private function getBasketArticlesUncached($getArticles)
    {
        $totalAmount = 0;
        $discount = 0;
        $totalAmountWithTax = 0;
        $totalAmountNet = 0;
        $totalCount = 0;

        foreach (array_keys($getArticles) as $key) {
            $getArticles[$key] = $this->eventManager->filter(
                'Shopware_Modules_Basket_GetBasket_FilterItemStart',
                $getArticles[$key],
                array('subject' => $this, 'getArticles' => $getArticles)
            );

            $getArticles[$key]["shippinginfo"] = (empty($getArticles[$key]["modus"]));

            if (
                !empty($getArticles[$key]["releasedate"])
                && strtotime($getArticles[$key]["releasedate"]) <= time()
            ) {
                $getArticles[$key]["sReleaseDate"] = $getArticles[$key]["releasedate"] = "";
            }
            $getArticles[$key]["esd"] = $getArticles[$key]["esdarticle"];

            if (empty($getArticles[$key]["minpurchase"])) {
                $getArticles[$key]["minpurchase"] = 1;
            }
            if (empty($getArticles[$key]["purchasesteps"])) {
                $getArticles[$key]["purchasesteps"] = 1;
            }
            if ($getArticles[$key]["purchasesteps"] <= 0) {
                unset($getArticles[$key]["purchasesteps"]);
            }

            if (empty($getArticles[$key]["maxpurchase"])) {
                $getArticles[$key]["maxpurchase"] = $this->config->get('sMAXPURCHASE');
            }
            if (
                !empty($getArticles[$key]["laststock"])
                && $getArticles[$key]["instock"] < $getArticles[$key]["maxpurchase"]
            ) {
                $getArticles[$key]["maxpurchase"] = $getArticles[$key]["instock"];
            }

            // Get additional basket meta data for each product
            if ($getArticles[$key]["modus"] == 0) {
                $tempArticle = $this->moduleManager->Articles()->sGetProductByOrdernumber($getArticles[$key]['ordernumber']);

                if (empty($tempArticle)) {
                    $getArticles[$key]["additional_details"] = array("properties" => array());
                } else {
                    $getArticles[$key]['additional_details'] = $tempArticle;
                    $properties = '';
                    if (isset($getArticles[$key]['additional_details']['sProperties'])) {
                        foreach ($getArticles[$key]['additional_details']['sProperties'] as $property) {
                            $properties .= $property['name'] . ':&nbsp;' . $property['value'] . ',&nbsp;';
                        }
                    }

                    $getArticles[$key]['additional_details']['properties'] = substr($properties, 0, -7);
                }
            }

            // If unitID is set, query it
            if (!empty($getArticles[$key]["unitID"])) {
                $getUnitData = $this->moduleManager->Articles()->sGetUnit($getArticles[$key]["unitID"]);
                $getArticles[$key]["itemUnit"] = $getUnitData["description"];
            } else {
                unset($getArticles[$key]["unitID"]);
            }

            if (!empty($getArticles[$key]["packunit"])) {
                $getPackUnit = array();

                // If we are loading a variant, look for a translation in the variant translation set
                if ($getArticles[$key]['mainDetailId'] != $getArticles[$key]['articleDetailId']) {
                    $getPackUnit = $this->moduleManager->Articles()->sGetTranslation(
                        array(),
                        $getArticles[$key]["articleDetailId"],
                        "variant",
                        $this->sSYSTEM->sLanguage
                    );
                }

                // If we are using the main variant or the variant has no translation
                // look for translation in the article translation set
                if (
                    $getArticles[$key]['mainDetailId'] == $getArticles[$key]['articleDetailId']
                    || empty($getPackUnit["packunit"])
                ) {
                    $getPackUnit = $this->moduleManager->Articles()->sGetTranslation(
                        array(),
                        $getArticles[$key]["articleID"],
                        "article",
                        $this->sSYSTEM->sLanguage
                    );
                }

                if (!empty($getPackUnit["packunit"])) {
                    $getArticles[$key]["packunit"] = $getPackUnit["packunit"];
                }
            }

            $quantity = $getArticles[$key]["quantity"];
            $price = $getArticles[$key]["price"];
            $netprice = $getArticles[$key]["netprice"];

            if ($getArticles[$key]["modus"] == 2) {
                $ticketResult = $this->db->fetchRow(
                    'SELECT vouchercode,taxconfig
                    FROM s_emarketing_vouchers
                    WHERE ordercode = ?',
                    array($getArticles[$key]["ordernumber"])
                ) ? : array();

                if (!$ticketResult["vouchercode"]) {
                    // Query Voucher-Code
                    $queryVoucher = $this->db->fetchRow(
                        'SELECT code
                        FROM s_emarketing_voucher_codes
                        WHERE id = ? AND cashed != 1',
                        array($getArticles[$key]["articleID"])
                    ) ? : array();
                    $ticketResult["vouchercode"] = $queryVoucher["code"];
                }
                $this->sDeleteArticle($getArticles[$key]["id"]);

                //if voucher was deleted, do not restore
                if ($this->front->Request()->getQuery('sDelete') != 'voucher') {
                    $this->sAddVoucher($ticketResult["vouchercode"]);
                }
            }

            $tax = $getArticles[$key]["tax_rate"];

            // If shop is in net mode, we have to consider
            // the tax separately
            if (
                ($this->config->get('sARTICLESOUTPUTNETTO') && !$this->sSYSTEM->sUSERGROUPDATA["tax"])
                || (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])
            ) {
                if (empty($getArticles[$key]["modus"])) {
                    $priceWithTax = round($netprice, 2) / 100 * (100 + $tax);

                    $getArticles[$key]["amountWithTax"] = $quantity * $priceWithTax;
                    // If basket comprised any discount, calculate brutto-value for the discount
                    if ($this->sSYSTEM->sUSERGROUPDATA["basketdiscount"] && $this->sCheckForDiscount()) {
                        $discount += ($getArticles[$key]["amountWithTax"] / 100 * $this->sSYSTEM->sUSERGROUPDATA["basketdiscount"]);
                    }
                } elseif ($getArticles[$key]["modus"] == 3) {
                    $getArticles[$key]["amountWithTax"] = round(1 * (round($price, 2) / 100 * (100 + $tax)), 2);
                    // Basket discount
                } elseif ($getArticles[$key]["modus"] == 2) {
                    $getArticles[$key]["amountWithTax"] = round(1 * (round($price, 2) / 100 * (100 + $tax)), 2);

                    if ($this->sSYSTEM->sUSERGROUPDATA["basketdiscount"] && $this->sCheckForDiscount()) {
                        $discount += ($getArticles[$key]["amountWithTax"] / 100 * ($this->sSYSTEM->sUSERGROUPDATA["basketdiscount"]));
                    }
                } elseif ($getArticles[$key]["modus"] == 4 || $getArticles[$key]["modus"] == 10) {
                    $getArticles[$key]["amountWithTax"] = round(1 * ($price / 100 * (100 + $tax)), 2);
                    if ($this->sSYSTEM->sUSERGROUPDATA["basketdiscount"] && $this->sCheckForDiscount()) {
                        $discount += ($getArticles[$key]["amountWithTax"] / 100 * $this->sSYSTEM->sUSERGROUPDATA["basketdiscount"]);
                    }
                }
            }

            $getArticles[$key]["amount"] = $quantity * round($price, 2);

            //reset purchaseunit and save the original value in purchaseunitTemp
            if ($getArticles[$key]["purchaseunit"] > 0) {
                $getArticles[$key]["purchaseunitTemp"] = $getArticles[$key]["purchaseunit"];
                $getArticles[$key]["purchaseunit"] = 1;
            }

            // If price per unit is not referring to 1, calculate base-price
            // Choose 1000, quantity refers to 500, calculate price / 1000 * 500 as reference
            if ($getArticles[$key]["purchaseunit"] != 0) {
                $getArticles[$key]["itemInfo"] = $getArticles[$key]["purchaseunit"] . " {$getUnitData["description"]} / " . $this->moduleManager->Articles()->sFormatPrice($getArticles[$key]["amount"] / $quantity * $getArticles[$key]["purchaseunit"]);
                $getArticles[$key]["itemInfoArray"]["reference"] = $getArticles[$key]["purchaseunit"];
                $getArticles[$key]["itemInfoArray"]["unit"] = $getUnitData;
                $getArticles[$key]["itemInfoArray"]["price"] = $this->moduleManager->Articles()->sFormatPrice($getArticles[$key]["amount"] / $quantity * $getArticles[$key]["purchaseunit"]);
            }


            if ($getArticles[$key]["modus"] == 2) {
                // Gutscheine
                if (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"]) {
                    $getArticles[$key]["amountnet"] = $quantity * round($price, 2);
                } else {
                    $getArticles[$key]["amountnet"] = $quantity * round($netprice, 2);
                }
            } else {
                if (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"]) {
                    $getArticles[$key]["amountnet"] = $quantity * round($netprice, 2);
                } else {
                    $getArticles[$key]["amountnet"] = $quantity * $netprice;
                }
            }

            $totalAmount += round($getArticles[$key]["amount"], 2);
            // Needed if shop is in net-mode
            $totalAmountWithTax += round($getArticles[$key]["amountWithTax"], 2);
            // Ignore vouchers and premiums by counting articles
            if (!$getArticles[$key]["modus"]) {
                $totalCount++;
            }

            $totalAmountNet += round($getArticles[$key]["amountnet"], 2);

            $getArticles[$key]["priceNumeric"] = $getArticles[$key]["price"];
            $getArticles[$key]["price"] = $this->moduleManager->Articles()
                ->sFormatPrice($getArticles[$key]["price"]);
            $getArticles[$key]["amount"] = $this->moduleManager->Articles()
                ->sFormatPrice($getArticles[$key]["amount"]);
            $getArticles[$key]["amountnet"] = $this->moduleManager->Articles()
                ->sFormatPrice($getArticles[$key]["amountnet"]);

            if (!empty($getArticles[$key]["purchaseunitTemp"])) {
                $getArticles[$key]["purchaseunit"] = $getArticles[$key]["purchaseunitTemp"];
                $getArticles[$key]["itemInfo"] = $getArticles[$key]["purchaseunit"] . " {$getUnitData["description"]} / " . $this->moduleManager->Articles()->sFormatPrice(str_replace(",", ".", $getArticles[$key]["amount"]) / $quantity);
            }

            if ($getArticles[$key]["articleID"]) {
                // Article image
                if (!empty($getArticles[$key]["ob_attr1"])) {
                    $getArticles[$key]["image"] = $this->moduleManager->Articles()
                        ->sGetConfiguratorImage(
                            $this->moduleManager->Articles()->sGetArticlePictures(
                                $getArticles[$key]["articleID"],
                                false,
                                $this->config->get('sTHUMBBASKET'),
                                false,
                                true
                            ),
                            $getArticles[$key]["ob_attr1"]
                        );
                } else {
                    $getArticles[$key]["image"] = $this->moduleManager->Articles()
                        ->sGetArticlePictures(
                            $getArticles[$key]["articleID"],
                            true,
                            $this->config->get('sTHUMBBASKET'),
                            $getArticles[$key]["ordernumber"]
                        );
                }
            }
            // Links to details, basket
            $getArticles[$key]["linkDetails"] = $this->config->get('sBASEFILE') . "?sViewport=detail&sArticle=" . $getArticles[$key]["articleID"];
            if ($getArticles[$key]["modus"] == 2) {
                $getArticles[$key]["linkDelete"] = $this->config->get('sBASEFILE') . "?sViewport=basket&sDelete=voucher";
            } else {
                $getArticles[$key]["linkDelete"] = $this->config->get('sBASEFILE') . "?sViewport=basket&sDelete=" . $getArticles[$key]["id"];
            }

            $getArticles[$key]["linkNote"] = $this->config->get('sBASEFILE') . "?sViewport=note&sAdd=" . $getArticles[$key]["ordernumber"];

            $getArticles[$key] = $this->eventManager->filter(
                'Shopware_Modules_Basket_GetBasket_FilterItemEnd',
                $getArticles[$key],
                array('subject' => $this, 'getArticles' => $getArticles)
            );
        }
        return array($getArticles, $totalAmount, $totalAmountWithTax, $totalCount, $totalAmountNet);
    }

    /**
     * @return array
     */
    private function loadBasketArticles()
    {
        $sql = "
        SELECT
            s_order_basket.*,
            COALESCE (NULLIF(ad.packunit, ''), mad.packunit) AS packunit,
            a.main_detail_id AS mainDetailId,
            ad.id AS articleDetailId,
            ad.minpurchase,
            a.taxID,
            ad.instock AS instock,
            ad.suppliernumber,
            ad.maxpurchase,
            ad.purchasesteps,
            ad.purchaseunit,
            COALESCE (ad.unitID, mad.unitID) AS unitID,
            a.laststock,
            ad.shippingtime,
            ad.releasedate,
            ad.releasedate AS sReleaseDate,
            COALESCE (ad.ean, mad.ean) AS ean,
            ad.stockmin,
            s_order_basket_attributes.attribute1 as ob_attr1,
            s_order_basket_attributes.attribute2 as ob_attr2,
            s_order_basket_attributes.attribute3 as ob_attr3,
            s_order_basket_attributes.attribute4 as ob_attr4,
            s_order_basket_attributes.attribute5 as ob_attr5,
            s_order_basket_attributes.attribute6 as ob_attr6
        FROM s_order_basket
        LEFT JOIN s_articles_details AS ad ON ad.ordernumber = s_order_basket.ordernumber
        LEFT JOIN s_articles a ON (a.id = ad.articleID)
        LEFT JOIN s_articles_details AS mad ON mad.id = a.main_detail_id
        LEFT JOIN s_order_basket_attributes ON s_order_basket.id = s_order_basket_attributes.basketID
        WHERE sessionID=?
        ORDER BY id ASC, datum DESC
        ";
        $sql = $this->eventManager->filter(
            'Shopware_Modules_Basket_GetBasket_FilterSQL',
            $sql,
            array('subject' => $this)
        );

        $getArticles = $this->db->fetchAll($sql, array($this->session->get('sessionId')));

        return $getArticles;
    }

    /**
     * Gets article additional info for sUpdateArticle
     *
     * @param $id
     * @param $quantity
     * @return array
     */
    private function getAdditionalInfoForUpdateArticle($id, $quantity)
    {
        // Query to get minimum surcharge
        $queryAdditionalInfo = $this->db->fetchRow("
            SELECT s_articles_details.minpurchase, s_articles_details.purchasesteps,
            s_articles_details.maxpurchase, s_articles_details.purchaseunit,
            pricegroupID,pricegroupActive, s_order_basket.ordernumber, s_order_basket.articleID

            FROM s_articles, s_order_basket, s_articles_details
            WHERE s_order_basket.articleID = s_articles.id
            AND s_order_basket.ordernumber = s_articles_details.ordernumber
            AND s_order_basket.id = ?
            AND s_order_basket.sessionID = ?
            ",
            array($id, $this->session->get('sessionId'))
        ) ? : array();

        // Check if quantity matches minimum purchase
        if (!$queryAdditionalInfo["minpurchase"]) {
            $queryAdditionalInfo["minpurchase"] = 1;
        }

        if ($quantity < $queryAdditionalInfo["minpurchase"]) {
            $quantity = $queryAdditionalInfo["minpurchase"];
        }

        // Check if quantity matches the step requirements
        if (!$queryAdditionalInfo["purchasesteps"]) {
            $queryAdditionalInfo["purchasesteps"] = 1;
        }

        if (($quantity / $queryAdditionalInfo["purchasesteps"]) != intval($quantity / $queryAdditionalInfo["purchasesteps"])) {
            $quantity = intval($quantity / $queryAdditionalInfo["purchasesteps"]) * $queryAdditionalInfo["purchasesteps"];
        }

        $maxPurchase = $this->config->get('sMAXPURCHASE');
        if (empty($queryAdditionalInfo["maxpurchase"]) && !empty($maxPurchase)) {
            $queryAdditionalInfo["maxpurchase"] = $maxPurchase;
        }

        // Check if quantity matches max purchase
        if ($quantity > $queryAdditionalInfo["maxpurchase"] && !empty($queryAdditionalInfo["maxpurchase"])) {
            $quantity = $queryAdditionalInfo["maxpurchase"];
        }

        if (!empty($queryAdditionalInfo["purchaseunit"])) {
            $queryAdditionalInfo["purchaseunit"] = 1;
        }

        return array($queryAdditionalInfo, $quantity);
    }

    /**
     * Gets article base price info for sUpdateArticle
     *
     * @param $id
     * @param $quantity
     * @param $queryAdditionalInfo
     * @return array
     */
    private function getPriceForUpdateArticle($id, $quantity, $queryAdditionalInfo)
    {
        // Price groups
        if ($queryAdditionalInfo["pricegroupActive"]) {
            $quantitySQL = 'AND s_articles_prices.from = 1 LIMIT 1';
        } else {
            $quantitySQL = $this->db->quoteInto(
                ' AND s_articles_prices.from <= ? AND (s_articles_prices.to >= ? OR s_articles_prices.to = 0)',
                $quantity
            );
        }

        // Get the order number
        $sql = 'SELECT s_articles_prices.price AS price, taxID, s_core_tax.tax AS tax,
              tax_rate, s_articles_details.id AS articleDetailsID, s_articles_details.articleID,
              s_order_basket.config, s_order_basket.ordernumber
            FROM s_articles_details, s_articles_prices, s_order_basket,
              s_articles, s_core_tax
            WHERE s_order_basket.id = ? AND s_order_basket.sessionID = ?
            AND s_order_basket.ordernumber = s_articles_details.ordernumber
            AND s_articles_details.id=s_articles_prices.articledetailsID
            AND s_articles_details.articleID = s_articles.id
            AND s_articles.taxID = s_core_tax.id
            AND s_articles_prices.pricegroup = ?';

        $queryNewPrice = $this->db->fetchRow(
            $sql . ' ' . $quantitySQL,
            array(
                $id,
                $this->session->get('sessionId'),
                $this->sSYSTEM->sUSERGROUP
            )
        ) ? : array();

        // Load prices from default group if article prices are not defined
        if (!$queryNewPrice["price"]) {
            // In the case no price is available for this customer group, use price of default customer group
            $sql = 'SELECT s_articles_prices.price AS price, taxID, s_core_tax.tax AS tax,
              s_articles_details.id AS articleDetailsID, s_articles_details.articleID,
              s_order_basket.config, s_order_basket.ordernumber
            FROM s_articles_details, s_articles_prices, s_order_basket,
              s_articles, s_core_tax
            WHERE s_order_basket.id = ? AND s_order_basket.sessionID = ?
            AND s_order_basket.ordernumber = s_articles_details.ordernumber
            AND s_articles_details.id=s_articles_prices.articledetailsID
            AND s_articles_details.articleID = s_articles.id
            AND s_articles.taxID = s_core_tax.id
            AND s_articles_prices.pricegroup = \'EK\'';

            $queryNewPrice = $this->db->fetchRow(
                $sql . ' ' . $quantitySQL,
                array(
                    $id,
                    $this->session->get('sessionId')
                )
            ) ? : array();
        }

        $queryNewPrice = $this->eventManager->filter('Shopware_Modules_Basket_getPriceForUpdateArticle_FilterPrice',
            $queryNewPrice,
            array(
                "id" => $id,
                'subject'=> $this,
                "quantity" => $quantity
            )
        );

        return $queryNewPrice;
    }

    /**
     * Calculates article tax values for sUpdateArticle
     *
     * @param $quantity
     * @param $queryNewPrice
     * @param $queryAdditionalInfo
     * @return array
     */
    private function getTaxesForUpdateArticle($quantity, $queryNewPrice, $queryAdditionalInfo)
    {
        // Determinate tax rate for this cart position
        $taxRate = $this->moduleManager->Articles()->getTaxRateByConditions($queryNewPrice["taxID"]);

        $netPrice = $queryNewPrice["price"];

        // Recalculate price if purchase unit is set
        $grossPrice = $this->moduleManager->Articles()->sCalculatingPriceNum(
            $netPrice,
            $queryNewPrice["tax"],
            false,
            false,
            $queryNewPrice["taxID"],
            false,
            $queryNewPrice
        );

        // Check if tax free
        if (
            ($this->config->get('sARTICLESOUTPUTNETTO') && !$this->sSYSTEM->sUSERGROUPDATA["tax"])
            || (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])
        ) {
            $netPrice = round($grossPrice, 2);
        } else {
            // Round to right value, if no purchase unit is set
            if ($queryAdditionalInfo["purchaseunit"] == 1) {
                $grossPrice = round($grossPrice, 2);
            }
            // Consider global discount for net price
            $netPrice = $grossPrice / (100 + $taxRate) * 100;
        }

        // Recalculate price per item, if purchase unit is set
        if ($queryAdditionalInfo["purchaseunit"] != 0) {
            $grossPrice = $grossPrice / $queryAdditionalInfo["purchaseunit"];
            $netPrice = $netPrice / $queryAdditionalInfo["purchaseunit"];
        }

        if (empty($this->sSYSTEM->sCurrency["factor"])) {
            $this->sSYSTEM->sCurrency["factor"] = 1;
        }

        if ($queryAdditionalInfo["pricegroupActive"]) {
            $grossPrice = $this->moduleManager->Articles()->sGetPricegroupDiscount(
                $this->sSYSTEM->sUSERGROUP,
                $queryAdditionalInfo["pricegroupID"],
                $grossPrice,
                $quantity,
                false
            );
            $grossPrice = $this->moduleManager->Articles()->sRound($grossPrice);
            if (
                ($this->config->get('sARTICLESOUTPUTNETTO') && !$this->sSYSTEM->sUSERGROUPDATA["tax"]) ||
                (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])
            ) {
                $netPrice = $this->moduleManager->Articles()->sRound(
                    $this->moduleManager->Articles()->sGetPricegroupDiscount(
                        $this->sSYSTEM->sUSERGROUP,
                        $queryAdditionalInfo["pricegroupID"],
                        $netPrice,
                        $quantity,
                        false
                    )
                );
            } else {
                $netPrice = $grossPrice / (100 + $taxRate) * 100;
                $netPrice = number_format($netPrice, 3, ".", "");
            }
        }
        return array($taxRate, $netPrice, $grossPrice);
    }

    /**
     * @param $article
     * @return array
     * @throws Enlight_Exception
     */
    private function getPriceForAddArticle($article)
    {
        // Read price from default price table
        $price = $this->db->fetchRow(
            'SELECT price, s_core_tax.tax AS tax
            FROM s_articles_prices, s_core_tax
            WHERE s_articles_prices.pricegroup = ?
            AND s_articles_prices.articledetailsID = ?
            AND s_core_tax.id = ?',
            array(
                $this->sSYSTEM->sUSERGROUP,
                $article["articledetailsID"],
                $article["taxID"]
            )
        ) ? : array();

        if (empty($price["price"])) {
            $price = $this->db->fetchRow(
                'SELECT price, s_core_tax.tax AS tax
                FROM s_articles_prices, s_core_tax
                WHERE s_articles_prices.pricegroup = \'EK\'
                AND s_articles_prices.articledetailsID = ?
                AND s_core_tax.id = ?',
                array($article["articledetailsID"], $article["taxID"])
            ) ? : array();
        }

        if (!$price["price"] && !$article["free"]) {
            // No price could acquired
            throw new Enlight_Exception("BASKET-INSERT #01 No price acquired");
        }

        // If configuration article
        if (
            ($this->config->get('sARTICLESOUTPUTNETTO') && !$this->sSYSTEM->sUSERGROUPDATA["tax"])
            || (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])
        ) {
            // If netto set both values to net-price
            $price["price"] = $this->moduleManager->Articles()->sCalculatingPriceNum(
                $price["price"],
                $price["tax"],
                false,
                false,
                $article["taxID"],
                false,
                $article
            );
            $price["netprice"] = $price["price"];
        } else {
            // If brutto, save net
            $price["netprice"] = $price["price"];
            $price["price"] = $this->moduleManager->Articles()->sCalculatingPriceNum(
                $price["price"],
                $price["tax"],
                false,
                false,
                $article["taxID"],
                false,
                $article
            );
        }
        return $price;
    }

    /**
     * Get article data for sAddArticle
     *
     * @param string $ordernumber Article ordernumber
     * @return array|false Article data, or false if none found
     */
    private function getArticleForAddArticle($ordernumber)
    {
        $sql = "
            SELECT s_articles.id AS articleID, s_articles.main_detail_id, name AS articleName, taxID,
              additionaltext, s_articles_details.shippingfree, laststock, instock,
              s_articles_details.id as articledetailsID, ordernumber,
              s_articles.configurator_set_id
            FROM s_articles, s_articles_details
            WHERE s_articles_details.ordernumber = ?
            AND s_articles_details.articleID = s_articles.id
            AND s_articles.active = 1
            AND (
                SELECT articleID
                FROM s_articles_avoid_customergroups
                WHERE articleID = s_articles.id AND customergroupID = ?
            ) IS NULL
        ";

        $article = $this->db->fetchRow(
            $sql,
            array($ordernumber, $this->sSYSTEM->sUSERGROUPDATA["id"])
        );

        $article = $this->eventManager->filter('Shopware_Modules_Basket_getArticleForAddArticle_FilterArticle',
            $article,
            array(
                "id" => $ordernumber,
                'subject'=> $this,
                "partner" => $this->sSYSTEM->_SESSION["sPartner"]
            )
        );

        if (!$article) {
            return false;
        }

        $article = $this->moduleManager->Articles()->sGetTranslation(
            $article,
            $article['articleID'],
            "article"
        );

        $article = $this->moduleManager->Articles()->sGetTranslation(
            $article,
            $article['articledetailsID'],
            "variant"
        );

        if ($article['configurator_set_id'] > 0) {
            $context = $this->contextService->getProductContext();
            $product = Shopware()->Container()->get('shopware_storefront.list_product_service')->get($article['ordernumber'], $context);
            $product = $this->additionalTextService->buildAdditionalText($product, $context);
            $article['additionaltext'] = $product->getAdditional();
        }

        return $article;
    }
}
