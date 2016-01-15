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

use Shopware\Bundle\StoreFrontBundle;
use Shopware\Bundle\StoreFrontBundle\Service\AdditionalTextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;

/**
 * Deprecated Shopware Class to provide article export feeds
 *
 * @category  Shopware
 * @package   Shopware\Core
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class sExport
{
    public $sFeedID;
    public $sHash;
    public $sSettings;
    public $sDB;
    public $sApi;
    public $sSYSTEM;
    public $sPath;
    public $sTemplates;

    public $sCurrency;
    public $sCustomergroup;

    /**
     * @var array Contains shop data in array format
     */
    private $shopData;

    /**
     * @deprecated Use $shopData instead
     */
    public $sLanguage;

    /**
     * @deprecated Use $shopData instead
     */
    public $sMultishop;

    /**
     * @var \Shopware\Models\Shop\Shop
     */
    public $shop;

    /**
     * @var Enlight_Template_Manager
     */
    public $sSmarty;

    /**
     * @var \Shopware\Models\Article\Repository
     */
    protected $articleRepository = null;

    /**
     * @var \Shopware\Models\Media\Repository
     */
    protected $mediaRepository = null;

    /**
     * @var \Shopware\Models\Media\Album
     */
    protected $articleMediaAlbum = null;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var AdditionalTextServiceInterface
     */
    private $additionalTextService;

    /**
     * @var Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    private $db;

    /**
     * @var Shopware_Components_Config
     */
    private $config;
    
    /**
     * @param ContextServiceInterface $contextService
     * @param AdditionalTextServiceInterface $additionalTextService
     * @param Enlight_Components_Db_Adapter_Pdo_Mysql $db
     */
    public function __construct(
        ContextServiceInterface $contextService = null,
        AdditionalTextServiceInterface $additionalTextService = null,
        Enlight_Components_Db_Adapter_Pdo_Mysql $db = null,
        Shopware_Components_Config $config = null
    ) {
        $container = Shopware()->Container();

        $this->contextService = $contextService ?: $container->get('shopware_storefront.context_service');
        $this->additionalTextService = $container->get('shopware_storefront.additional_text_service');
        $this->db = $db ?: $container->get('db');
        $this->config = $config ?: $container->get('config');
    }

    /**
     * @param $currency
     * @return array
     */
    public function sGetCurrency($currency)
    {
        static $currencyCache = array();

        if (empty($currency)) {
            $currency = $this->shopData["currency_id"];
        }
        if (isset($currencyCache[$currency])) {
            return $currencyCache[$currency];
        }
        if (is_numeric($currency)) {
            $sql = "id=".$currency;
        } elseif (is_string($currency)) {
            $sql = "currency=".$this->db->quote(trim($currency));
        } else {
            return false;
        }

        $currencyCache[$currency] = $this->db->fetchRow("
            SELECT *
            FROM s_core_currencies
            WHERE $sql
        ");

        return $currencyCache[$currency];
    }

    /**
     * @param $customerGroup
     * @return bool
     */
    public function sGetCustomergroup($customerGroup)
    {
        static $cache = array();
        if (empty($customerGroup)) {
            $customerGroup = $this->sMultishop["defaultcustomergroup"];
        }
        if (isset($cache[$customerGroup])) {
            return $cache[$customerGroup];
        }
        if (is_int($customerGroup)) {
            $sql = "id=".$customerGroup;
        } elseif (is_string($customerGroup)) {
            $sql = "groupkey=".$this->db->quote(trim($customerGroup));
        } else {
            return false;
        }

        $cache[$customerGroup] = $this->db->fetchRow("
            SELECT *
            FROM s_core_customergroups
            WHERE $sql
        ");

        return $cache[$customerGroup];
    }

    /**
     * @param $id
     * @return mixed
     */
    private function getShopData($id)
    {
        static $cache = array();

        if (isset($cache[$id])) {
            return $cache[$id];
        }

        if (empty($id)) {
            $sql = "s.`default`=1";
        } elseif (is_numeric($id)) {
            $sql = "s.id=".$id;
        } elseif (is_string($id)) {
            $sql = "s.name=".$this->db->quote(trim($id));
        }

        $cache[$id] = $this->db->fetchRow("
            SELECT
              s.id,
              s.main_id,
              s.name,
              s.title,
              COALESCE (s.host, m.host) AS host,
              COALESCE (s.base_path, m.base_path) AS base_path,
              COALESCE (s.base_url, m.base_url) AS base_url,
              COALESCE (s.hosts, m.hosts) AS hosts,
              COALESCE (s.secure, m.secure) AS secure,
              COALESCE (s.secure_host, m.secure_host) AS secure_host,
              COALESCE (s.secure_base_path, m.secure_base_path) AS secure_base_path,
              COALESCE (s.template_id, m.template_id) AS template_id,
              COALESCE (s.document_template_id, m.document_template_id) AS document_template_id,
              s.category_id,
              s.currency_id,
              s.customer_group_id,
              s.fallback_id,
              s.customer_scope,
              s.`default`,
              s.active,
              s.always_secure
            FROM s_core_shops s
            LEFT JOIN s_core_shops m
              ON m.id=s.main_id
              OR (s.main_id IS NULL AND m.id=s.id)
            LEFT JOIN s_core_shop_currencies d
              ON d.shop_id=m.id
            WHERE s.active = 1 AND $sql
            GROUP BY s.id
        ");

        return $cache[$id];
    }

    /**
     * @deprecated Use getShopData
     *
     * @param $language
     * @return mixed
     */
    public function sGetMultishop($language)
    {
        static $cache = array();

        if (isset($cache[$language])) {
            return $cache[$language];
        }
        if (empty($language)) {
            $sql = "s.`default`=1";
        } elseif (is_numeric($language)) {
            $sql = "s.id=".$language;
        } elseif (is_string($language)) {
            $sql = "s.name=".$this->db->quote(trim($language));
        }

        $cache[$language] = $this->db->fetchRow("
            SELECT
              s.id AS id, s.id AS isocode, s.locale_id AS locale,
              s.category_id AS parentID, s.default AS skipbackend, s.name,
              (SELECT groupkey FROM s_core_customergroups WHERE id=s.customer_group_id) as defaultcustomergroup,
              (SELECT CONCAT('templates/', template) FROM s_core_templates WHERE id=m.template_id) as template,
              (SELECT CONCAT('templates/', template) FROM s_core_templates WHERE id=m.document_template_id) as doc_template,
              CONCAT(s.host, '\n', s.hosts) as domainaliase,
              GROUP_CONCAT(d.currency_id SEPARATOR '|') as switchCurrencies,
              (SELECT GROUP_CONCAT(id SEPARATOR '|') FROM s_core_shops WHERE id=m.id OR main_id=m.id)  as switchLanguages,
              s.currency_id AS defaultcurrency, s.default, s.fallback_id AS fallback
            FROM s_core_shops s
            LEFT JOIN s_core_shops m
            ON m.id=s.main_id
            OR (s.main_id IS NULL AND m.id=s.id)
            LEFT JOIN s_core_shop_currencies d
            ON d.shop_id=m.id
            WHERE s.active=1 AND $sql
            GROUP BY s.id
        ");

        return $cache[$language];
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


    public function sInitSettings()
    {
        $hash = $this->db->quote($this->sHash);

        /** @var $shopRepository \Shopware\Models\Shop\Repository */
        $shopRepository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');

        $sql = "
            SELECT
                id as feedID, s_export.*
            FROM
                s_export
            WHERE
                id = {$this->sFeedID}
            AND
                hash = $hash
            AND
                `active`=1
        ";
        $this->sSettings = $this->db->fetchRow($sql);

        if (empty($this->sSettings)) {
            die();
        }

        $this->sSettings["dec_separator"] = ",";
        switch ($this->sSettings["formatID"]) {
            case 1:
                $this->sSettings["fieldmark"] = "\"";
                $this->sSettings["escaped_fieldmark"] = "\"\"";
                $this->sSettings["separator"] = ";";
                $this->sSettings["escaped_separator"] = ";";
                $this->sSettings["line_separator"] = "\r\n";
                $this->sSettings["escaped_line_separator"] = "\r\n";
                break;
            case 2:
                $this->sSettings["fieldmark"] = "";
                $this->sSettings["escaped_fieldmark"] = "";
                $this->sSettings["separator"] = "\t";
                $this->sSettings["escaped_separator"] = "";
                $this->sSettings["line_separator"] = "\r\n";
                $this->sSettings["escaped_line_separator"] = "";
                break;
            case 4:
                $this->sSettings["fieldmark"] = "";
                $this->sSettings["escaped_fieldmark"] = "";
                $this->sSettings["separator"] = "|";
                $this->sSettings["escaped_separator"] = "";
                $this->sSettings["line_separator"] = "\r\n";
                $this->sSettings["escaped_line_separator"] = "";
                break;
            default:
                $this->sSettings["fieldmark"] = null;
                $this->sSettings["escaped_fieldmark"] = null;
                $this->sSettings["separator"] = null;
                $this->sSettings["escaped_separator"] = null;
                $this->sSettings["line_separator"] = null;
                $this->sSettings["escaped_line_separator"] = null;
        }

        if (!empty($this->sSettings['encodingID']) && $this->sSettings['encodingID']==2) {
            $this->sSettings['encoding'] = 'UTF-8';
        } else {
            $this->sSettings['encoding'] = 'ISO-8859-1';
        }

        if (empty($this->sSettings["languageID"])) {
            $defaultShop = $shopRepository->getDefault();
            //just a fall back for update reasons
            $this->sSettings["languageID"] = $defaultShop->getId();
        }

        $shop = $shopRepository->getActiveById($this->sSettings["languageID"]);
        $this->shopData = $this->getShopData($this->sSettings["languageID"]);

        $this->sLanguage = $this->sGetMultishop($this->sSettings["languageID"]);
        $this->sMultishop = $this->sLanguage;

        if (empty($this->sSettings["categoryID"])) {
            $this->sSettings["categoryID"] = $this->shopData["category_id"];
        }
        if (empty($this->sSettings["customergroupID"])) {
            $this->sSettings["customergroupID"] = $shop->getCustomerGroup()->getKey();
        } else {
            $this->sSettings["customergroupID"] = (int) $this->sSettings["customergroupID"];
        }
        if (empty($this->sSettings["currencyID"])) {
            $this->sSettings["currencyID"] = $this->shopData["currency_id"];
        }

        $this->sCurrency = $this->sGetCurrency($this->sSettings["currencyID"]);

        $this->sCustomergroup = $this->sGetCustomergroup($this->sSettings["customergroupID"]);

        $this->articleMediaAlbum = $this->getMediaRepository()
                ->getAlbumWithSettingsQuery(-1)
                ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);

        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Currency');
        $shop->setCurrency($repository->find($this->sCurrency['id']));
        $shop->registerResources(Shopware()->Bootstrap());

        $this->shop = $shop;

        $this->sSYSTEM->sCONFIG = Shopware()->Config();
    }

    public function sInitSmarty()
    {
        $this->sSYSTEM->sSMARTY->compile_id = "export_".$this->sFeedID;

        $this->sSYSTEM->sSMARTY->cache_lifetime = 0;
        $this->sSYSTEM->sSMARTY->debugging = 0;
        $this->sSYSTEM->sSMARTY->caching = 0;

        $this->sSmarty->registerPlugin('modifier', 'htmlentities', array(&$this, 'sHtmlEntities'));
        $this->sSmarty->registerPlugin('modifier', 'format', array(&$this, 'sFormatString'));
        $this->sSmarty->registerPlugin('modifier', 'escape', array(&$this, 'sEscapeString'));
        $this->sSmarty->registerPlugin('modifier', 'category', array(&$this, 'sGetArticleCategoryPath'));
        $this->sSmarty->registerPlugin('modifier', 'link', array(&$this, 'sGetArticleLink'));
        $this->sSmarty->registerPlugin('modifier', 'image', array(&$this, 'sGetImageLink'));
        $this->sSmarty->registerPlugin('modifier', 'articleImages', array(&$this, 'sGetArticleImageLinks'));
        $this->sSmarty->registerPlugin('modifier', 'shippingcost', array(&$this, 'sGetArticleShippingcost'));
        $this->sSmarty->registerPlugin('modifier', 'property', array(&$this, 'sGetArticleProperties'));

        $this->sSmarty->assign("sConfig", $this->sSYSTEM->sCONFIG);
        $this->sSmarty->assign("shopData", $this->shopData);
        $this->sSmarty->assign("sCurrency", $this->sCurrency);
        $this->sSmarty->assign("sCustomergroup", $this->sCustomergroup);
        $this->sSmarty->assign("sSettings", $this->sSettings);

        // deprecated: use shopData instead
        $this->sSmarty->assign("sLanguage", $this->sLanguage);
        $this->sSmarty->assign("sMultishop", $this->sMultishop);

        $this->sSmarty->config_vars["F"] = $this->sSettings["fieldmark"];
        $this->sSmarty->config_vars["EF"] = $this->sSettings["escaped_separator"];
        $this->sSmarty->config_vars["S"] = $this->sSettings["separator"];
        $this->sSmarty->config_vars["ES"] = $this->sSettings["escaped_fieldmark"];
        $this->sSmarty->config_vars["L"] = $this->sSettings["line_separator"];
        $this->sSmarty->config_vars["EL"] = $this->sSettings["escaped_line_separator"];
        if ($this->sSettings['encoding'] == 'UTF-8') {
            $this->sSmarty->config_vars['BOM'] = "\xEF\xBB\xBF";
        } else {
            $this->sSmarty->config_vars['BOM'] = '';
        }
        $this->sSmarty->config_vars['EN'] = $this->sSettings['encoding'];
    }

    public function sHtmlEntities($string, $char_set = null)
    {
        if (empty($char_set)) {
            $char_set = $this->sSettings['encoding'];
        }
        
        return htmlentities($string, ENT_COMPAT | ENT_HTML401, $char_set);
    }
    
    public function sFormatString($string, $esc_type = '', $char_set = null)
    {
        return $this->sEscapeString($string, $esc_type, $char_set);
    }

    public function sEscapeString($string, $esc_type = '', $char_set = null)
    {
        if (empty($esc_type)) {
            if (!empty($this->sSettings["formatID"]) && $this->sSettings["formatID"]==3) {
                $esc_type = "html";
            } else {
                $esc_type = "csv";
            }
        }

        if (empty($char_set)) {
            $char_set = $this->sSettings['encoding'];
        }

        switch ($esc_type) {
            case 'number':
                return number_format($string, 2, $this->sSettings["dec_separator"], '');
            case 'csv':
                if (empty($this->sSettings["escaped_line_separator"])) {
                    $string = preg_replace('#[\r\n]+#m', ' ', $string);
                } elseif ($this->sSettings["escaped_line_separator"]!=$this->sSettings["line_separator"]) {
                    $string = str_replace($this->sSettings["line_separator"], $this->sSettings['escaped_line_separator'], $string);
                }
                if (!empty($this->sSettings["fieldmark"])) {
                    $string = str_replace($this->sSettings["fieldmark"], $this->sSettings['escaped_fieldmark'], $string);
                } else {
                    $string = str_replace($this->sSettings['separator'], $this->sSettings['escaped_separator'], $string);
                }

                if ($char_set != 'UTF-8') {
                    $string = utf8_decode($string);
                }
                $string = html_entity_decode($string, ENT_NOQUOTES, $char_set);
                return $this->sSettings["fieldmark"].$string.$this->sSettings["fieldmark"];
            case 'xml':
                 if ($char_set != 'UTF-8') {
                     $string = utf8_decode($string);
                 }
                return $string;
               case 'html':
                $string = html_entity_decode($string, ENT_NOQUOTES, $char_set);
                   return htmlspecialchars($string, ENT_QUOTES, $char_set, false);
            case 'htmlall':
                return htmlentities($string, ENT_QUOTES, $char_set);
            case 'url':
                return rawurlencode($string);
            case 'urlpathinfo':
                return str_replace('%2F', '/', rawurlencode($string));

            case 'quotes':
                // escape unescaped single quotes
                return preg_replace("%(?<!\\\\)'%", "\\'", $string);

            case 'hex':
                // escape every character into hex
                $return = '';
                for ($x=0; $x < strlen($string); $x++) {
                    $return .= '%' . bin2hex($string[$x]);
                }
                return $return;

            case 'hexentity':
                $return = '';
                for ($x=0; $x < strlen($string); $x++) {
                    $return .= '&#x' . bin2hex($string[$x]) . ';';
                }
                return $return;

            case 'decentity':
                $return = '';
                for ($x=0; $x < strlen($string); $x++) {
                    $return .= '&#' . ord($string[$x]) . ';';
                }
                return $return;

            case 'javascript':
                // escape quotes and backslashes, newlines, etc.
                return strtr($string, array('\\'=>'\\\\', "'"=>"\\'", '"'=>'\\"', "\r"=>'\\r', "\n"=>'\\n', '</'=>'<\/'));

            case 'mail':
                // safe way to display e-mail address on a web page
                return str_replace(array('@', '.'), array(' [AT] ', ' [DOT] '), $string);

            case 'nonstd':
               // escape non-standard chars, such as ms document quotes
               $_res = '';
               for ($_i = 0, $_len = strlen($string); $_i < $_len; $_i++) {
                   $_ord = ord(substr($string, $_i, 1));
                   // non-standard char, escape it
                   if ($_ord >= 126) {
                       $_res .= '&#' . $_ord . ';';
                   } else {
                       $_res .= substr($string, $_i, 1);
                   }
               }
               return $_res;
        }
    }

    public function sGetArticleLink($articleID, $title="")
    {
        return Shopware()->Modules()->Core()->sRewriteLink($this->sSYSTEM->sCONFIG["sBASEFILE"]."?sViewport=detail&sArticle=$articleID", $title).(empty($this->sSettings["partnerID"])?"":"?sPartner=".urlencode($this->sSettings["partnerID"]));
    }

    public function sGetImageLink($hash, $imageSize = null)
    {
        if (empty($hash)) {
            return "";
        }

        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        // get the image directory
        $imageDir = 'media/image/';

        // if no imageSize was set, return the full image
        if (null === $imageSize) {
            return $mediaService->getUrl($imageDir . $hash);
        }

        // get filename and extension in order to insert thumbnail size later
        $extension = pathinfo($hash, PATHINFO_EXTENSION);
        $fileName = pathinfo($hash, PATHINFO_FILENAME);
        $thumbDir = $imageDir . 'thumbnail/';

        // get thumbnail sizes
        $sizes = $this->articleMediaAlbum
                ->getSettings()
                ->getThumbnailSize();

        foreach ($sizes as $key => &$size) {
            if (strpos($size, 'x') === 0) {
                $size = $size . 'x' . $size;
            }
        }

        if (isset($sizes[$imageSize])) {
            return $mediaService->getUrl($thumbDir . $fileName . '_' . $sizes[(int) $imageSize] . '.' . $extension);
        }

        return "";
    }

    /**
     * Returns the article image links with the frontend logic.
     * Checks the image restriction of variant articles, too.
     *
     * @param $articleId
     * @param $orderNumber
     * @param null $imageSize
     * @param string $separator
     * @return string
     */
    public function sGetArticleImageLinks($articleId, $orderNumber, $imageSize = null, $separator = "|")
    {
        $imageSize = ($imageSize == null) ? "original" : $imageSize;
        $returnData = array();
        if (empty($articleId) || empty($orderNumber)) {
            return "";
        }
        $imageData = Shopware()->Modules()->sArticles()->sGetArticlePictures($articleId, false, null, $orderNumber);
        $cover = Shopware()->Modules()->sArticles()->sGetArticlePictures($articleId, true, null, $orderNumber);
        $returnData[] = $cover["src"][$imageSize];
        foreach ($imageData as $image) {
            $returnData[] = $image["src"][$imageSize];
        }

        return implode($separator, $returnData);
    }

    /**
     * Returns an array with the article property data.
     * Needs to be parsed over the feed smarty template
     *
     * @param $articleId
     * @param $filterGroupId
     * @return string
     */
    public function sGetArticleProperties($articleId, $filterGroupId)
    {
        if (empty($articleId) || empty($filterGroupId)) {
            return "";
        }
        return Shopware()->Modules()->Articles()->sGetArticleProperties($articleId, $filterGroupId);
    }

    public function sMapTranslation($object, $objectData)
    {
        switch ($object) {
            case "detail":
            case "article":
                $map = array(
                    "txtshortdescription" => "description",
                    "txtlangbeschreibung" => "description_long",
                    "txtArtikel" => "name",
                    "txtzusatztxt" => "additionaltext"
                );
                for ($i=1; $i<=20; $i++) {
                    $map["attr$i"] = "attr$i";
                }
                break;
            case "link":
                $map = array("linkname" => "description");
                break;
            case "download":
                $map = array("downloadname" => "description");
                break;
        }
        if (empty($objectData)) {
            return array();
        }
        $objectData = unserialize($objectData);
        if (empty($objectData)) {
            return array();
        }
        $result = array();
        foreach ($map as $key => $value) {
            if (isset($objectData[$key])) {
                $result[$value] = $objectData[$key];
            }
        }
        return $result;
    }

    /**
     * Expects a string of type
     *
     * @param $line
     * @return array
     */
    public function _decode_line($line)
    {
        $separator = ";";
        $fieldmark = "\"";
        $elements = explode($separator, $line);
        $tmp_elements = array();
        for ($i = 0; $i < count($elements); $i++) {
            $nquotes = substr_count($elements[$i], $fieldmark);
            if ($nquotes %2 == 1) {
                if (isset($elements[$i+1])) {
                    $elements[$i+1] = $elements[$i].$separator.$elements[$i+1];
                }
            } else {
                if ($nquotes > 0) {
                    if (substr($elements[$i], 0, 1)==$fieldmark) {
                        $elements[$i] = substr($elements[$i], 1);
                    }
                    if (substr($elements[$i], -1, 1)==$fieldmark) {
                        $elements[$i] = substr($elements[$i], 0, -1);
                    }
                    $elements[$i] = str_replace($fieldmark.$fieldmark, $fieldmark, $elements[$i]);
                }
                $index = substr($elements[$i], 0, strpos($elements[$i], ':'));
                $elements[$i] = substr($elements[$i], strpos($elements[$i], ':')+1);
                $tmp_elements[$index] = $elements[$i];
            }
        }
        return $tmp_elements;
    }

    public function sCreateSql()
    {
        $sql_add_join   = array();
        $sql_add_select = array();
        $sql_add_where  = array();

        $skipBackend = $this->shop->get('skipbackend');
        $isoCode = $this->shop->get('isocode');
        if (empty($skipBackend) && !empty($isoCode)) {
            $sql_isocode = $this->db->quote($isoCode);
            $sql_add_join[] = "
                LEFT JOIN s_core_translations as ta
                ON ta.objectkey=a.id AND ta.objecttype='article' AND ta.objectlanguage=$sql_isocode

                LEFT JOIN s_core_translations as td
                ON td.objectkey=d.id AND td.objecttype='variant' AND td.objectlanguage=$sql_isocode
            ";
            $sql_add_select[] = "ta.objectdata as article_translation";
            $sql_add_select[] = "td.objectdata as detail_translation";

            //read the fallback for the case the translation is not going to be set
            $fallbackId = $this->shop->getFallback() ? $this->shop->getFallback()->getId() : null;
            if (!empty($fallbackId)) {
                $sqlFallbackLanguageId = $this->db->quote($fallbackId);
                $sql_add_join[] = "
                LEFT JOIN s_core_translations as taf
                    ON taf.objectkey=a.id AND taf.objecttype='article' AND taf.objectlanguage=$sqlFallbackLanguageId

                LEFT JOIN s_core_translations as tdf
                    ON tdf.objectkey=d.id AND tdf.objecttype='variant' AND tdf.objectlanguage=$sqlFallbackLanguageId
            ";
                $sql_add_select[] = "taf.objectdata as article_translation_fallback";
                $sql_add_select[] = "tdf.objectdata as detail_translation_fallback";
            }
        }

        if (!empty($this->sSettings["categoryID"])) {
            $sql_add_join[] = "
                INNER JOIN s_articles_categories_ro act
                    ON  act.articleID = a.id
                    AND act.categoryID = {$this->sSettings["categoryID"]}
                INNER JOIN s_categories c
                    ON  c.id = act.categoryID
                    AND c.active = 1
            ";
        }
        if (empty($this->sSettings["image_filter"])) {
            $sql_add_join[] = "
                LEFT JOIN s_articles_img as i
                ON i.articleID = a.id AND i.main=1 AND i.article_detail_id IS NULL
            ";
        } else {
            $sql_add_join[] = "
                JOIN s_articles_img as i
                ON i.articleID = a.id AND i.main=1 AND i.article_detail_id IS NULL
            ";
        }

        if (
            !empty($this->sCustomergroup["groupkey"])
            && empty($this->sCustomergroup["mode"])
            && $this->sCustomergroup["groupkey"] != "EK"
        ) {
            $sql_add_join[] = "
                LEFT JOIN s_articles_prices as p2
                ON p2.articledetailsID = d.id AND p2.`from`=1
                AND p2.pricegroup='{$this->sCustomergroup["groupkey"]}'
                AND p2.price!=0
            ";

            $sql_add_join[] = "
                LEFT JOIN s_articles_prices as pArbitrary
                ON pArbitrary.articledetailsID = d.id AND pArbitrary.`to`='beliebig'
                AND pArbitrary.pricegroup='{$this->sCustomergroup["groupkey"]}'
                AND pArbitrary.price!=0
            ";

            $pricefield = "IFNULL(p2.price, p.price)";
            $pseudoprice = "IFNULL(p2.pseudoprice, p.pseudoprice)";
            $baseprice = "IFNULL(p2.baseprice, p.baseprice)";
            $arbitraryPriceField = "IFNULL(pArbitrary.price, p.price)";

        } else {
            $pricefield = "p.price";
            $pseudoprice = "p.pseudoprice";
            $baseprice = "p.baseprice";
            $arbitraryPriceField = "pArbitrary.price";
        }


        if (empty($this->sSettings["variant_export"]) || $this->sSettings["variant_export"] == 1) {
            $sql_add_select[] = "IF(COUNT(d.ordernumber) <= 1, '', GROUP_CONCAT(DISTINCT(CONCAT('\"', d.id, ':', REPLACE(d.ordernumber,'\"','\"\"'),'\"')) SEPARATOR ';')) as group_ordernumber";
            $sql_add_select[] = "IF(COUNT(d.additionaltext) <= 1, '', GROUP_CONCAT(DISTINCT(CONCAT('\"', d.id, ':', REPLACE(d.additionaltext,'\"','\"\"'),'\"')) SEPARATOR ';')) as group_additionaltext";
            $sql_add_select[] = "IF(COUNT($pricefield)<=1,'',GROUP_CONCAT(ROUND(CAST($pricefield*(100-IF(pd.discount,pd.discount,0)-{$this->sCustomergroup["discount"]})/100*{$this->sCurrency["factor"]} AS DECIMAL(10,3)),2) SEPARATOR ';')) as group_pricenet";
            $sql_add_select[] = "IF(COUNT($pricefield)<=1,'',GROUP_CONCAT(ROUND(CAST($pricefield*(100+t.tax-IF(pd.discount,pd.discount,0)-{$this->sCustomergroup["discount"]})/100*{$this->sCurrency["factor"]} AS DECIMAL(10,3)),2) SEPARATOR ';')) as group_price";
            $sql_add_select[] = "IF(COUNT(d.active)<=1,'',GROUP_CONCAT(d.active SEPARATOR ';')) as group_active";
            $sql_add_select[] = "IF(COUNT(d.instock)<=1,'',GROUP_CONCAT(d.instock SEPARATOR ';')) as group_instock";

            $sql_add_group_by = "a.id";
            $sql_add_article_detail_join_condition = "AND d.kind=1";
        } elseif ($this->sSettings["variant_export"] == 2) {
            $sql_add_group_by = "d.id";
            $sql_add_article_detail_join_condition ='';
        }

        $grouppricefield = "gp.price";
        if (
            empty($this->sSettings["variant_export"])
            || $this->sSettings["variant_export"] == 2
            || $this->sSettings["variant_export"] == 1
        ) {
            $sql_add_join[] = "
                JOIN (SELECT NULL as `articleID` , NULL as `valueID` , NULL as `attr1` , NULL as `attr2` , NULL as `attr3` , NULL as `attr4` , NULL as `attr5` , NULL as `attr6` , NULL as `attr7` , NULL as `attr8` , NULL as `attr9` , NULL as `attr10` , NULL as `standard` , NULL as `active` , NULL as `ordernumber` , NULL as `instock`, NULL as `minpurchase`) as v
            ";
            $sql_add_join[] = "
                JOIN (SELECT NULL as articleID, NULL as valueID, NULL as groupkey, NULL as price, NULL as optionID) as gp
            ";
        }

        if (!empty($this->sSettings["active_filter"])) {
            $sql_add_where[] = "(a.active = 1 AND (v.active=1 OR (v.active IS NULL AND d.active=1)))";
        }
        if (!empty($this->sSettings["stockmin_filter"])) {
            $sql_add_where[] ="(v.instock>=d.stockmin OR (v.instock IS NULL AND d.instock>=d.stockmin))";
        }
        if (!empty($this->sSettings["instock_filter"])) {
            $sql_add_where[] ="(v.instock>={$this->sSettings["instock_filter"]} OR (v.instock IS NULL AND d.instock>={$this->sSettings["instock_filter"]}))";
        }
        if (!empty($this->sSettings["price_filter"])) {
            $sql_add_where[] = "ROUND(CAST(IFNULL($grouppricefield,$pricefield)*(100+t.tax-IF(pd.discount IS NULL,0,pd.discount)-{$this->sCustomergroup["discount"]})/100*{$this->sCurrency["factor"]} AS DECIMAL(10,3)),2)>=".$this->sSettings["price_filter"];
        }
        if (!empty($this->sSettings["own_filter"])&&trim($this->sSettings["own_filter"])) {
            $sql_add_where[] = "(".$this->sSettings["own_filter"].")";
        }
        if ($this->config->offsetGet('hideNoInstock')) {
            $sql_add_where[] = "(
                (a.laststock * v.instock >= a.laststock * v.minpurchase)
                OR
                (a.laststock * d.instock >= a.laststock * d.minpurchase)
            )";
        }

        $sql_add_join = implode(" ", $sql_add_join);
        if (!empty($sql_add_select)) {
            $sql_add_select = ", ".implode(", ", $sql_add_select);
        } else {
            $sql_add_select = "";
        }
        if (!empty($sql_add_where)) {
            $sql_add_where = " AND ".implode(" AND ", $sql_add_where);
        } else {
            $sql_add_where = "";
        }
        if (!empty($sql_add_group_by)) {
            $sql_add_group_by = "GROUP BY ($sql_add_group_by)";
        } else {
            $sql_add_group_by = "";
        }

        $sql = "
            SELECT
                a.id as `articleID`,
                a.name,
                a.description,
                a.description_long,
                a.main_detail_id,
                d.shippingtime,
                d.shippingfree,
                a.topseller,
                a.keywords,
                d.minpurchase,
                d.purchasesteps,
                d.maxpurchase,
                d.purchaseunit,
                d.referenceunit,
                a.taxID,
                a.filtergroupID,
                a.supplierID,
                d.unitID,
                IF(a.changetime!='0000-00-00 00:00:00',a.changetime,'') as `changed`,
                IF(a.datum!='0000-00-00',a.datum,'') as `added`,
                IF(d.releasedate!='0000-00-00',d.releasedate,'') as `releasedate`,
                a.active as active,

                d.id as `articledetailsID`,
                IF(v.ordernumber IS NOT NULL,v.ordernumber,d.ordernumber) as ordernumber,

                d.suppliernumber,
                d.ean,
                d.width,
                d.height,
                d.length,
                d.kind,
                IF(v.standard=1||kind=1,1,0) as standard,
                d.additionaltext,
                COALESCE(sai.impressions, 0) as impressions,
                d.sales,

                IF(v.active IS NOT NULL,IF(a.active=0,0,v.active),a.active) as active,
                IF(v.instock IS NOT NULL,v.instock,d.instock) as instock,
                (
                   SELECT AVG(av.points)
                   FROM s_articles_vote as av WHERE active=1
                   AND articleID=a.id
                ) as sVoteAverage,
                (
                   SELECT COUNT(*)
                   FROM s_articles_vote as av WHERE active=1
                   AND articleID=a.id
                ) as sVoteCount,
                d.stockmin,
                d.weight,
                d.position,

                at.attr1, at.attr2, at.attr3, at.attr4, at.attr5, at.attr6, at.attr7, at.attr8, at.attr9, at.attr10,
                at.attr11, at.attr12, at.attr13, at.attr14, at.attr15, at.attr16, at.attr17, at.attr18, at.attr19, at.attr20,

                s.name as supplier,
                u.unit,
                u.description as unit_description,
                t.tax,
                CONCAT(i.img, '.', i.extension) as image,

                a.configurator_set_id as configurator,

                ROUND(CAST(IFNULL($grouppricefield, $arbitraryPriceField)*(100-IF(pd.discount,pd.discount,0)-{$this->sCustomergroup["discount"]})/100*{$this->sCurrency["factor"]} AS DECIMAL(10,3)),2) as arbitraryNetprice,
                ROUND(CAST(IFNULL($grouppricefield, $arbitraryPriceField)*(100+t.tax)/100*(100-IF(pd.discount,pd.discount,0)-{$this->sCustomergroup["discount"]})/100*{$this->sCurrency["factor"]} AS DECIMAL(10,3)),2) as arbitraryPrice,

                ROUND(CAST(IFNULL($grouppricefield, $pricefield)*(100-IF(pd.discount,pd.discount,0)-{$this->sCustomergroup["discount"]})/100*{$this->sCurrency["factor"]} AS DECIMAL(10,3)),2) as netprice,
                ROUND(CAST(IFNULL($grouppricefield, $pricefield)*(100+t.tax)/100*(100-IF(pd.discount,pd.discount,0)-{$this->sCustomergroup["discount"]})/100*{$this->sCurrency["factor"]} AS DECIMAL(10,3)),2) as price,
                pd.discount,
                ROUND(CAST($pseudoprice*{$this->sCurrency["factor"]} AS DECIMAL(10,3)),2) as netpseudoprice,
                ROUND(CAST($pseudoprice*(100+t.tax)*{$this->sCurrency["factor"]}/100 AS DECIMAL(10,3)),2) as pseudoprice,
                $baseprice,
                IF(file IS NULL,0,1) as esd

                $sql_add_select

            FROM s_articles a
            INNER JOIN s_articles_details d
            ON d.articleID = a.id
            $sql_add_article_detail_join_condition
            LEFT JOIN s_articles_attributes at
            ON d.id = at.articledetailsID

            LEFT JOIN `s_core_units` as `u`
            ON d.unitID = u.id
            LEFT JOIN `s_core_tax` as `t`
            ON a.taxID = t.id
            LEFT JOIN `s_articles_supplier` as `s`
            ON a.supplierID = s.id

            LEFT JOIN s_core_pricegroups_discounts pd
            ON a.pricegroupActive=1
            AND	a.pricegroupID=groupID
            AND customergroupID = 1
            AND discountstart=1

            LEFT JOIN s_articles_esd e ON e.articledetailsID=d.id

            LEFT JOIN (
                SELECT articleID
                FROM
                    s_export_categories as ec,
                    s_articles_categories_ro as ac
                WHERE feedID={$this->sFeedID}
                AND ec.categoryID=ac.categoryID
                GROUP BY articleID
            ) AS bc
            ON bc.articleID=a.id

            LEFT JOIN s_export_suppliers AS bs
            ON (bs.supplierID=s.id AND bs.feedID={$this->sFeedID})

            LEFT JOIN s_export_articles AS ba
            ON (ba.articleID=a.id AND ba.feedID={$this->sFeedID})

            LEFT JOIN s_articles_prices AS p
            ON p.articledetailsID = d.id
            AND p.`from`=1
            AND p.pricegroup='EK'

            LEFT JOIN s_articles_prices AS pArbitrary
            ON pArbitrary.articledetailsID = d.id
            AND pArbitrary.`to`='beliebig'
            AND pArbitrary.pricegroup='EK'

            LEFT JOIN
            (
              SELECT articleId AS id, SUM(s.impressions) AS impressions
              FROM s_statistics_article_impression s
              GROUP BY articleId
            ) sai ON sai.id = a.id

            $sql_add_join

            WHERE bc.articleID IS NULL
            AND bs.supplierID IS NULL
            AND a.mode = 0
            AND d.kind != 3
            AND ba.articleID IS NULL
            $sql_add_where

            $sql_add_group_by
        ";

        if (!empty($this->sSettings["count_filter"])) {
            $sql .= "LIMIT ".$this->sSettings["count_filter"];
        }

        return $sql;
    }

    /**
     * executes the current product export
     *
     * @param resource $handleResource used as a file or the stdout to fetch the smarty output
     */
    public function executeExport($handleResource)
    {
        fwrite($handleResource, $this->sSmarty->fetch('string:' . $this->sSettings['header'], $this->sFeedID));

        $context = $this->contextService->getShopContext();

        $sql = $this->sCreateSql();

        $result = $this->db->query($sql);

        if ($result === false) {
            return;
        }

        // Update db with the latest values
        $count = (int) $result->rowCount();
        $this->db->update(
            's_export',
            array(
                'last_export' => new Zend_Date(),
                'cache_refreshed' => new Zend_Date(),
                'count_articles' => $count
            ),
            array('id = ?' => $this->sFeedID)
        );

        // fetches all required data to smarty
        $rows = array();
        for ($rowIndex = 1; $row = $result->fetch(); $rowIndex++) {
            if (!empty($row['group_ordernumber_2'])) {
                $row['group_ordernumber'] = $this->_decode_line($row['group_ordernumber_2']);
                $row['group_pricenet'] = explode(';', $row['group_pricenet_2']);
                $row['group_price'] = explode(';', $row['group_price_2']);
                $row['group_instock'] = explode(';', $row['group_instock_2']);
                $row['group_active'] = explode(';', $row['group_active_2']);
                unset($row['group_ordernumber_2'], $row['group_pricenet_2']);
                unset($row['group_price_2'], $row['group_instock_2'], $row['group_active_2']);
                for ($i = 1; $i <= 10; $i++) {
                    if (!empty($row['group_group' . $i])) {
                        $row['group_group' . $i] = $this->_decode_line($row['group_group' . $i]);
                    } else {
                        unset($row['group_group' . $i]);
                    }
                    if (!empty($row['group_option' . $i])) {
                        $row['group_option' . $i] = $this->_decode_line($row['group_option' . $i]);
                    } else {
                        unset($row['group_option' . $i]);
                    }
                }
                unset($row['group_additionaltext']);
            } elseif (!empty($row['group_ordernumber'])) {
                $row['group_ordernumber'] = $this->_decode_line($row['group_ordernumber']);
                $row['group_additionaltext'] = $this->_decode_line($row['group_additionaltext']);
                $row['group_pricenet'] = explode(';', $row['group_pricenet']);
                $row['group_price'] = explode(';', $row['group_price']);
                $row['group_instock'] = explode(';', $row['group_instock']);
                $row['group_active'] = explode(';', $row['group_active']);
            }

            if (!empty($row['article_translation_fallback'])) {
                $translation = $this->sMapTranslation('article', $row['article_translation_fallback']);
                if ($row['main_detail_id'] != $row['articledetailsID']) {
                    unset($translation['additionaltext']);
                }
                $row = array_merge($row, $translation);
            }
            if (!empty($row['article_translation'])) {
                $translation = $this->sMapTranslation('article', $row['article_translation']);
                if ($row['main_detail_id'] != $row['articledetailsID']) {
                    unset($translation['additionaltext']);
                }
                $row = array_merge($row, $translation);
            }

            if (!empty($row['detail_translation_fallback'])) {
                $translation = $this->sMapTranslation('detail', $row['detail_translation_fallback']);
                $row = array_merge($row, $translation);
            }
            if (!empty($row['detail_translation'])) {
                $translation = $this->sMapTranslation('detail', $row['detail_translation']);
                $row = array_merge($row, $translation);
            }

            $row['name'] = htmlspecialchars_decode($row['name']);
            $row['supplier'] = htmlspecialchars_decode($row['supplier']);

            //cast it to float to prevent the division by zero warning
            $row['purchaseunit'] = floatval($row['purchaseunit']);
            $row['referenceunit'] = floatval($row['referenceunit']);
            if (!empty($row['purchaseunit']) && !empty($row['referenceunit'])) {
                $row['referenceprice'] = Shopware()->Modules()->Articles()->calculateReferencePrice(
                    $row['price'],
                    $row['purchaseunit'],
                    $row['referenceunit']
                );
            }
            if ($row['configurator'] > 0) {
                if (empty($this->sSettings["variant_export"]) || $this->sSettings["variant_export"] == 1) {
                    $row['group_additionaltext'] = array();

                    if (!empty($row['group_ordernumber'])) {
                        foreach ($row['group_ordernumber'] as $orderNumber) {
                            $product = new StoreFrontBundle\Struct\ListProduct(
                                (int) $row['articleID'],
                                (int) $row["articledetailsID"],
                                $orderNumber
                            );

                            $product->setAdditional($row['additionaltext']);

                            $product = $this->additionalTextService->buildAdditionalText($product, $context);

                            if (array_key_exists($orderNumber, $row['group_additionaltext'])) {
                                $row['group_additionaltext'][$orderNumber] = $product->getAdditional();
                            }
                            if ($orderNumber == $row['ordernumber']) {
                                $row['additionaltext'] = $product->getAdditional();
                            }
                        }
                    }
                }
                $product = new StoreFrontBundle\Struct\ListProduct(
                    (int) $row['articleID'],
                    (int) $row["articledetailsID"],
                    $row['ordernumber']
                );

                $product->setAdditional($row['additionaltext']);

                $product = $this->additionalTextService->buildAdditionalText($product, $context);

                $row['additionaltext'] = $product->getAdditional();
            }
            $rows[] = $row;

            if ($rowIndex == $count || count($rows) >= 50) {
                @set_time_limit(30);

                $this->sSmarty->assign('sArticles', $rows);
                $rows = array();

                $template = 'string:{foreach $sArticles as $sArticle}' . $this->sSettings['body'] . '{/foreach}';

                fwrite($handleResource, $this->sSmarty->fetch($template, $this->sFeedID));
            }
        }
        fwrite($handleResource, $this->sSmarty->fetch('string:' . $this->sSettings['footer'], $this->sFeedID));
        fclose($handleResource);
    }

    public function sGetArticleCategoryPath($articleID, $separator = " > ", $categoryID=null)
    {
        if (empty($categoryID)) {
            $categoryID = $this->sSettings["categoryID"];
        }

        $articleCategoryId = $this->sSYSTEM->sMODULES["sCategories"]->sGetCategoryIdByArticleId($articleID, $categoryID);
        $breadcrumb = array_reverse(Shopware()->Modules()->sCategories()->sGetCategoriesByParent($articleCategoryId));

        foreach ($breadcrumb as $breadcrumbObj) {
            $breadcrumbs[] = $breadcrumbObj["name"];
        }
        return htmlspecialchars_decode(implode($separator, $breadcrumbs));
    }

    public function sGetCountry($country)
    {
        static $cache = array();
        if (empty($country)) {
            return false;
        }
        if (isset($cache[$country])) {
            return $cache[$country];
        }
        if (is_numeric($country)) {
            $sql = "c.id=".$country;
        } elseif (is_string($country)) {
            $sql = "c.countryiso=".$this->db->quote($country);
        } else {
            return false;
        }
        $sql = "
            SELECT
              c.id, c.id as countryID, countryname, countryiso,
              countryen, c.position, notice,
              c.shippingfree as shippingfree
            FROM s_core_countries c
            WHERE $sql
        ";
        return $cache[$country] = $this->db->fetchRow($sql);
    }

    public function sGetPaymentmean($payment)
    {
        static $cache = array();
        if (empty($payment)) {
            return false;
        }
        if (isset($cache[$payment])) {
            return $cache[$payment];
        }
        if (is_numeric($payment)) {
            $sql = "id=".$payment;
        } elseif (is_string($payment)) {
            $sql = "name=".$this->db->quote($payment);
        } else {
            return false;
        }
        $sql = "
            SELECT * FROM s_core_paymentmeans
            WHERE $sql
        ";
        $cache[$payment] = $this->db->fetchRow($sql);

        $cache[$payment]["country_surcharge"] = array();
        if (!empty($cache[$payment]["surchargestring"])) {
            foreach (explode(";", $cache[$payment]["surchargestring"]) as $countrySurcharge) {
                list($key, $value) = explode(":", $countrySurcharge);
                $value = floatval(str_replace(",", ".", $value));
                if (!empty($value)) {
                    $cache[$payment]["country_surcharge"][$key] = $value;
                }
            }
        }
        $cache[$payment]["surcharge"] = $cache[$payment]["surcharge"];
        return $cache[$payment];
    }

    public function sGetDispatch($dispatch = null, $country = null)
    {
        if (empty($dispatch)) {
            $sql_order = "";
        } elseif (is_numeric($dispatch)) {
            $sql_order = "IF(sd.id=".(int) $dispatch.",0,1),";
        } elseif (is_string($dispatch)) {
            $sql_order = "IF(name=".$this->db->quote($dispatch).",0,1),";
        } else {
            $sql_order = "";
        }

        if (empty($country)) {
            $sql_where = "";
        } elseif (is_numeric($country)) {
            $sql_where = "c.id=".$country;
        } elseif (is_string($country)) {
            $sql_where = "c.countryiso=".$this->db->quote($country);
        } else {
            $sql_where = "";
        }

        static $cache = array();
        if (isset($cache[$sql_order."|".$sql_where])) {
            return $cache[$sql_order."|".$sql_where];
        }

        if (!empty($sql_where)) {
            $sql_from = " s_premium_dispatch_countries sc,	s_core_countries c";
            $sql_where = "AND $sql_where AND c.id=sc.countryID";
        } else {
            $sql_from = "";
        }
        $sql = "
            SELECT sd.id, name, sd.description, sd.shippingfree
            FROM
                s_premium_dispatch sd,
                $sql_from
            WHERE sd.active = 1
            AND	sd.id = sc.dispatchID
            $sql_where
            ORDER BY $sql_order sd.position ASC LIMIT 1
        ";
        return $cache[$sql_order."|".$sql_where] = $this->db->fetchRow($sql);
    }

    public function sGetDispatchBasket($article, $countryID=null, $paymentID = null)
    {
        $sql_select = '';
        if (!empty($this->sSYSTEM->sCONFIG['sPREMIUMSHIPPIUNGASKETSELECT'])) {
            $sql_select .= ', '.$this->sSYSTEM->sCONFIG['sPREMIUMSHIPPIUNGASKETSELECT'];
        }
        $sql = 'SELECT id, calculation_sql FROM s_premium_dispatch WHERE calculation=3';
        $calculations = $this->db->fetchPairs($sql);
        if (!empty($calculations)) {
            foreach ($calculations as $dispatchID => $calculation) {
                if (empty($calculation)) {
                    $calculation = $this->db->quote($calculation);
                }
                $sql_select .= ', ('.$calculation.') as calculation_value_'.$dispatchID;
            }
        }

        $sql = "
            SELECT
                MIN(d.instock>=b.quantity) as instock,
                MIN(d.instock>=(b.quantity+d.stockmin)) as stockmin,
                MIN(a.laststock) as laststock,
                SUM(d.weight*b.quantity) as weight,
                SUM(IF(a.id,b.quantity,0)) as count_article,
                MAX(b.shippingfree) as shippingfree,
                SUM(IF(b.modus=0,b.quantity*b.price/b.currencyFactor,0)) as amount,
                MAX(t.tax) as max_tax, u.id as userID
                $sql_select
                , b.articleID
            FROM (
                SELECT
                    NULL as sessionID,
                    ? as articleID,
                    ? as ordernumber,
                    ? as shippingfree,
                    1 as quantity,
                    ? as price,
                    ? as netprice,
                    0 as modus,
                    ? as esdarticle,
                    '' as config,
                    ? as currencyFactor,
                    '' as ob_attr1,
                    '' as ob_attr2,
                    '' as ob_attr3,
                    '' as ob_attr4,
                    '' as ob_attr5,
                    '' as ob_attr6
            ) as b

            LEFT JOIN s_articles a
            ON b.articleID=a.id
            AND b.modus=0
            AND b.esdarticle=0

            LEFT JOIN s_articles_details d
            ON (d.ordernumber=b.ordernumber)
            AND d.articleID=a.id

            LEFT JOIN s_articles_attributes at
            ON at.articledetailsID=d.id

            LEFT JOIN s_core_tax t
            ON t.id=a.taxID

            LEFT JOIN s_user u
            ON u.id=NULL

            LEFT JOIN s_user_billingaddress ub
            ON ub.userID=u.id

            LEFT JOIN s_user_shippingaddress us
            ON us.userID=u.id

            GROUP BY b.sessionID
        ";

        try {
            $basket = $this->db->fetchRow($sql, array(
            $article["articleID"],
            $article["ordernumber"],
            $article["shippingfree"],
            $article["price"],
            $article["netprice"],
            $article["esd"],
            $this->sCurrency["factor"]
        ));
        } catch (Exception $e) {
            echo $e->getMessage();
            exit();
        }

        if (empty($basket)) {
            return false;
        }
        $basket['countryID'] = $countryID;
        $basket['paymentID'] = $paymentID;
        $basket['customergroupID'] = $this->sCustomergroup['id'];
        $basket['multishopID'] = $this->sMultishop['id'];
        $basket['sessionID'] = null;
        return $basket;
    }

    public function sGetArticleShippingcost($article, $payment, $country, $dispatch = null)
    {
        if (empty($article)||!is_array($article)) {
            return false;
        }
        $country = $this->sGetCountry($country);
        if (empty($country)) {
            return false;
        }
        $payment = $this->sGetPaymentmean($payment);
        if (empty($payment)) {
            return false;
        }
        if (!empty($payment["country_surcharge"][$country["countryiso"]])) {
            $payment["surcharge"] += $payment["country_surcharge"][$country["countryiso"]];
        }
        $payment['surcharge'] = round($payment['surcharge']*$this->sCurrency["factor"], 2);

        return $this->sGetArticlePremiumShippingcosts($article, $payment, $country, $dispatch);
    }

    public function sGetPremiumDispatch($basket, $dispatch = null)
    {
        if (empty($dispatch)) {
            $sql_order = "";
        } elseif (is_numeric($dispatch)) {
            $sql_order = "IF(d.id=".(int) $dispatch.",0,1),";
        } elseif (is_string($dispatch)) {
            $sql_order = "IF(d.name=".$this->db->quote($dispatch).",0,1),";
        } else {
            $sql_order = "";
        }

        $sql_add_join = "";
        if (!empty($basket['paymentID'])) {
            $sql_add_join .= "
                JOIN s_premium_dispatch_paymentmeans dp
                ON d.id = dp.dispatchID
                AND dp.paymentID={$basket['paymentID']}
            ";
        }
        if (!empty($basket['countryID'])) {
            $sql_add_join .= "
                JOIN s_premium_dispatch_countries dc
                ON d.id = dc.dispatchID
                AND dc.countryID={$basket['countryID']}
            ";
        }

        $sql = "SELECT id, bind_sql FROM s_premium_dispatch WHERE type IN (0) AND bind_sql IS NOT NULL";
        $statements = $this->db->fetchPairs($sql);

        $sql_where = "";
        foreach ($statements as $dispatchID => $statement) {
            $sql_where .= "
            AND ( d.id!=$dispatchID OR ($statement))
            ";
        }

        $sql_basket = array();
        foreach ($basket as $key => $value) {
            $sql_basket[] = $this->db->quote($value)." as `$key`";
        }
        $sql_basket = implode(', ', $sql_basket);

        $sql = "
            SELECT d.id, d.name, d.description, d.calculation, d.status_link, d.surcharge_calculation, d.bind_shippingfree, tax_calculation, t.tax as tax_calculation_value, d.shippingfree
            FROM s_premium_dispatch d

            JOIN ( SELECT $sql_basket ) b

            $sql_add_join
            LEFT JOIN (
                SELECT dc.dispatchID
                FROM s_articles_categories_ro ac,
                s_premium_dispatch_categories dc
                WHERE ac.articleID={$basket['articleID']}
                AND dc.categoryID=ac.categoryID
                GROUP BY dc.dispatchID
            ) as dk
            ON d.id = dk.dispatchID

            LEFT JOIN s_core_tax t
            ON t.id=d.tax_calculation

            LEFT JOIN s_user u
            ON u.id=0
            AND u.active=1

            LEFT JOIN s_user_billingaddress ub
            ON ub.userID=u.id

            LEFT JOIN s_user_shippingaddress us
            ON us.userID=u.id

            WHERE d.active = 1
            AND (bind_weight_from IS NULL OR bind_weight_from <= b.weight)
            AND (bind_weight_to IS NULL OR bind_weight_to >= b.weight)
            AND (bind_price_from IS NULL OR bind_price_from <= b.amount)
            AND (bind_price_to IS NULL OR bind_price_to >= b.amount)
            AND (bind_laststock=0 OR (bind_laststock=1 AND b.instock) OR (bind_laststock=2 AND b.stockmin))
            AND (bind_shippingfree!=1 OR NOT b.shippingfree)
            AND (d.multishopID IS NULL OR d.multishopID= b.multishopID)
            AND (d.customergroupID IS NULL OR d.customergroupID=b.customergroupID)
            AND dk.dispatchID IS NULL
            AND d.type IN (0)
            $sql_where
            ORDER BY $sql_order d.position, d.name
            LIMIT 1
        ";
        $dispatch = $this->db->fetchRow($sql);
        if (empty($dispatch)) {
            $sql = "
                SELECT
                    d.id, d.name,
                    d.description,
                    d.calculation,
                    d.status_link,
                    d.surcharge_calculation,
                    d.bind_shippingfree,
                    tax_calculation,
                    t.tax as tax_calculation_value
                FROM s_premium_dispatch d
                LEFT JOIN s_core_tax t
                ON t.id=d.tax_calculation
                WHERE d.active=1
                AND d.type=1
                ORDER BY d.position, d.name
                LIMIT 1
            ";
            $dispatch = $this->db->fetchRow($sql);
        }
        return $dispatch;
    }

    public function sGetPremiumDispatchSurcharge($basket)
    {
        if (empty($basket)) {
            return false;
        }

        $sql = 'SELECT id, bind_sql FROM s_premium_dispatch WHERE type=2 AND bind_sql IS NOT NULL';
        $statements = $this->db->fetchPairs($sql);

        $sql_where = '';
        foreach ($statements as $dispatchID => $statement) {
            $sql_where .= "
            AND ( d.id!=$dispatchID OR ($statement))
            ";
        }
        $sql_basket = array();
        foreach ($basket as $key => $value) {
            $sql_basket[] = $this->db->quote($value)." as `$key`";
        }
        $sql_basket = implode(', ', $sql_basket);

        $sql_add_join = "";
        if (!empty($basket['paymentID'])) {
            $sql_add_join .= "
                JOIN s_premium_dispatch_paymentmeans dp
                ON d.id = dp.dispatchID
                AND dp.paymentID={$basket['paymentID']}
            ";
        }
        if (!empty($basket['countryID'])) {
            $sql_add_join .= "
                JOIN s_premium_dispatch_countries dc
                ON d.id = dc.dispatchID
                AND dc.countryID={$basket['countryID']}
            ";
        }

        $sql = "
            SELECT d.id, d.calculation
            FROM s_premium_dispatch d

            JOIN ( SELECT $sql_basket ) b

            $sql_add_join

            LEFT JOIN (
                SELECT dc.dispatchID
                FROM s_articles_categories_ro ac,
                s_premium_dispatch_categories dc
                WHERE ac.articleID={$basket['articleID']}
                AND dc.categoryID=ac.categoryID
                GROUP BY dc.dispatchID
            ) as dk
            ON dk.dispatchID=d.id

            LEFT JOIN s_user u
            ON u.id=b.userID
            AND u.active=1

            LEFT JOIN s_user_billingaddress ub
            ON ub.userID=u.id

            LEFT JOIN s_user_shippingaddress us
            ON us.userID=u.id

            WHERE d.active=1
            AND (bind_weight_from IS NULL OR bind_weight_from <= b.weight)
            AND (bind_weight_to IS NULL OR bind_weight_to >= b.weight)
            AND (bind_price_from IS NULL OR bind_price_from <= b.amount)
            AND (bind_price_to IS NULL OR bind_price_to >= b.amount)
            AND (bind_instock=0 OR bind_instock IS NULL OR (bind_instock=1 AND b.instock) OR (bind_instock=2 AND b.stockmin))
            AND (bind_laststock=0 OR (bind_laststock=1 AND b.laststock))
            AND (bind_shippingfree=2 OR NOT b.shippingfree)

            AND (d.multishopID IS NULL OR d.multishopID=b.multishopID)
            AND (d.customergroupID IS NULL OR d.customergroupID=b.customergroupID)
            AND dk.dispatchID IS NULL
            AND d.type = 2
            AND (d.shippingfree IS NULL OR d.shippingfree > b.amount)
            $sql_where
            GROUP BY d.id
        ";
        $dispatches = $this->db->fetchAll($sql);
        $surcharge = 0;
        if (!empty($dispatches)) {
            foreach ($dispatches as $dispatch) {
                if (empty($dispatch['calculation'])) {
                    $from = round($basket['weight'], 3);
                } elseif ($dispatch['calculation']==1) {
                    $from = round($basket['amount'], 2);
                } elseif ($dispatch['calculation']==2) {
                    $from = round($basket['count_article']);
                } elseif ($dispatch['calculation']==3) {
                    $from = round($basket['calculation_value_'.$dispatch['id']]);
                } else {
                    continue;
                }
                $sql = "
                SELECT `value` , `factor`
                FROM `s_premium_shippingcosts`
                WHERE `from` <= $from
                AND `dispatchID` = {$dispatch['id']}
                ORDER BY `from` DESC
                LIMIT 1
            ";
                $result = $this->db->fetchRow($sql);
                if (!$result) {
                    continue;
                }
                $surcharge += $result['value'];
                if (!empty($result['factor'])) {
                    $surcharge +=  $result['factor']/100*$from;
                }
            }
        }
        return $surcharge;
    }

    public function sGetArticlePremiumShippingcosts($article, $payment, $country, $dispatch = null)
    {
        $basket = $this->sGetDispatchBasket($article, $country['id'], $payment['id']);
        if (empty($basket)) {
            return false;
        }
        $dispatch = $this->sGetPremiumDispatch($basket, $dispatch);
        if (empty($dispatch)) {
            return false;
        }

        if ((!empty($dispatch['shippingfree'])&&$dispatch['shippingfree']<=$basket['amount'])
            ||empty($basket['count_article'])
            ||(!empty($basket['shippingfree'])&&empty($dispatch['bind_shippingfree']))
        ) {
            if (empty($dispatch['surcharge_calculation'])) {
                return $payment['surcharge'];
            } else {
                return 0;
            }
        }

        if (empty($dispatch['calculation'])) {
            $from = round($basket['weight'], 3);
        } elseif ($dispatch['calculation']==1) {
            $from = round($basket['amount'], 2);
        } elseif ($dispatch['calculation']==2) {
            $from = round($basket['count_article']);
        } elseif ($dispatch['calculation']==3) {
            $from = round($basket['calculation_value_'.$dispatch['id']]);
        } else {
            return false;
        }

        $sql = "
            SELECT `value` , `factor`
            FROM `s_premium_shippingcosts`
            WHERE `from`<=$from
            AND `dispatchID`={$dispatch['id']}
            ORDER BY `from` DESC
            LIMIT 1
        ";
        $result = $this->db->fetchRow($sql);

        if (empty($result)) {
            return false;
        }

        $result['shippingcosts'] = $result['value'];
        if (!empty($result['factor'])) {
            $result['shippingcosts'] +=  $result['factor']/100*$from;
        }
        $result['surcharge'] = $this->sGetPremiumDispatchSurcharge($basket);
        if (!empty($result['surcharge'])) {
            $result['shippingcosts'] += $result['surcharge'];
        }
        $result['shippingcosts'] *= $this->sCurrency["factor"];
        $result['shippingcosts'] = round($result['shippingcosts'], 2);
        if (!empty($payment['surcharge'])&&$dispatch['surcharge_calculation']!=2&&(empty($article['shippingfree'])||empty($dispatch['surcharge_calculation']))) {
            $result['shippingcosts'] += $payment['surcharge'];
        }


        return $result['shippingcosts'];
    }
}
