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

use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Condition\CustomerGroupCondition;
use Shopware\Bundle\SearchBundle\Condition\HasPriceCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Sorting\PopularitySorting;
use Shopware\Bundle\SearchBundle\Sorting\PriceSorting;
use Shopware\Bundle\SearchBundle\Sorting\ReleaseDateSorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Components\Model\Query\SqlWalker;

/**
 * @category  Shopware
 * @package   Shopware\Controllers\Widgets
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Widgets_Emotion extends Enlight_Controller_Action
{
    /**
     * Get emotion by category
     * @param $repository \Shopware\Models\Emotion\Repository
     * @return array
     */
    public function getEmotion($repository)
    {
        $categoryId = (int) $this->Request()->getParam('categoryId');
        $emotionId = (int) $this->Request()->getParam('emotionId');

        if ($emotionId) {
            $query = $repository->getEmotionDetailQuery($emotionId);
        } else {
            $query = $repository->getCategoryEmotionsQuery($categoryId);
        }

        $emotions = $query->getArrayResult();

        foreach ($emotions as &$emotion) {
            $emotion['cols'] = $emotion['grid']['cols'];
            $emotion['elements'] = $repository->getEmotionElementsQuery($emotion['id'])->getQuery()->getArrayResult();

            $emotion['cellHeight'] = $emotion['grid']['cellHeight'];
            $emotion['articleHeight'] = $emotion['grid']['articleHeight'];
            $emotion['gutter'] = $emotion['grid']['gutter'];
        }

        return $emotions;
    }

    /**
     * Action that will be triggered by product slider type top seller
     * @deprecated use emotionArticleSliderAction instead
     */
    public function emotionTopSellerAction()
    {
        $this->Request()->setParam('sort', 'topseller');
        $this->emotionArticleSliderAction();
    }

    /**
     * Action that will be triggered by product slider type newcomer
     * @deprecated use emotionArticleSliderAction instead
     */
    public function emotionNewcomerAction()
    {
        $this->Request()->setParam('sort', 'newcommer');
        $this->emotionArticleSliderAction();
    }

    /**
     * Action that will be triggered by product slider type top seller
     */
    public function emotionArticleSliderAction()
    {
        $this->View()->loadTemplate("widgets/emotion/slide_articles.tpl");
        $category = (int) $this->Request()->getParam("category");
        $limit = (int) $this->Request()->getParam("limit", 5);
        $elementHeight = $this->Request()->getParam("elementHeight");
        $elementWidth = $this->Request()->getParam("elementWidth");
        $sort = $this->Request()->getParam('sort', 'newcommer');

        $pages = $this->Request()->getParam("pages");
        $offset = (int) $this->Request()->getParam("start", $limit * ($pages-1));

        $max = $this->Request()->getParam("max");
        $maxPages = round($max / $limit);

        if (!$category || !$pages || !$limit) {
            $this->Response()->setHttpResponseCode(404);
            return;
        }

        $userGroupKey = Shopware()->Modules()->System()->sUSERGROUPDATA['key'];

        if (!$category) {
            $this->Response()->setHttpResponseCode(404);
            return;
        }

        $values = $this->getProductSliderData($category, $userGroupKey, $offset, $limit, $sort);

        $this->View()->assign('articles', $values["values"]);
        $this->View()->assign('pages', $values["pages"] > $maxPages ? $maxPages : $values["pages"]);
        $this->View()->assign('sPerPage', $limit);
        $this->View()->assign('sElementWidth', $elementWidth);
        $this->View()->assign('sElementHeight', $elementHeight);
    }

    /**
     * The getEmotions function selects all emotions for the passed category id
     * and sets the result into the view variable "sEmotions".
     */
    public function indexAction()
    {
        /**@var $repository \Shopware\Models\Emotion\Repository*/
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Emotion\Emotion');
        $emotions = $this->getEmotion($repository);
        //iterate all emotions to select the element data.

        foreach ($emotions as &$emotion) {
            // Support for emotions which are available on multiple devices.
            $emotion['device'] = explode(',', $emotion['device']);

            //for each emotion we have to iterate the elements to get the element data.
            foreach ($emotion['elements'] as &$element) {
                $component = $element['component'];
                $elementQuery = $repository->getElementDataQuery($element['id'], $element['componentId']);
                $componentData = $elementQuery->getArrayResult();
                $data = array();
                $data["objectId"] = md5($element["id"]);

                //we have to iterate the component data to decode the values.
                foreach ($componentData as $entry) {
                    $value = '';
                    switch (strtolower($entry['valueType'])) {
                        case "json":
                            $value = Zend_Json::decode($entry['value']);
                            break;
                        case "string":
                        default:
                            $value = $entry['value'];
                            break;
                    }
                    $data[$entry['name']] = $value;
                }

                $data = Enlight()->Events()->filter('Shopware_Controllers_Widgets_Emotion_AddElement', $data, array('subject' => $this, 'element' => $element));

                if (!empty($component['convertFunction'])) {
                    $categoryId = $this->Request()->getParam('categoryId') ?: $emotion['categories'][0]['id'];

                    $data = $this->$component['convertFunction']($data, $categoryId, $element);
                }

                $element['data'] = $data;
            }
        }

        if (empty($emotions[0]['template'])) {
            $this->View()->loadTemplate('widgets/emotion/index.tpl');
        } else {
            $this->View()->loadTemplate('widgets/emotion/' . $emotions[0]['template']['file']);
        }

        $this->View()->assign('categoryId', (int) $this->Request()->getParam('categoryId'));
        $this->View()->assign('sEmotions', $emotions, true);
        $this->View()->assign('Controller', (string) $this->Request()->getParam('controllerName'));
    }

    private function getArticle($data, $categoryId, $element)
    {
        if ($data["article_type"] == "newcomer") {
            // new product
            $data = array_merge($data, Shopware()->Modules()->Articles()->sGetPromotionById('new', $categoryId, 0, false));
        } elseif ($data["article_type"] == "topseller") {
            // top product
            $temp = Shopware()->Modules()->Articles()->sGetPromotionById('top', $categoryId, 0, false);
            if (empty($temp["articleID"])) {
                $data = array_merge($data, Shopware()->Modules()->Articles()->sGetPromotionById('random', $categoryId, 0, false));
            } else {
                $data = array_merge($data, $temp);
            }
        } elseif ($data["article_type"] == "random_article") {
            // random product
            $data = array_merge($data, Shopware()->Modules()->Articles()->sGetPromotionById('random', $categoryId, 0, false));
        } else {
            // Fix product
            $data = array_merge($data, $this->articleByNumber($data["article"]));
        }

        return $data;
    }

    /**
     * Gets a random blog entry from the database
     *
     * @param $category
     * @return array {Array} $result
     */
    private function getRandomBlogEntry($category)
    {
        $data = array('entry_amount' => 50);
        $result = $this->getBlogEntry($data, $category);

        return $result['entries'][array_rand($result['entries'])];
    }

    /**
     * Gets the specific blog entry from the database.
     *
     * @param $data
     * @param $category
     * @internal param $ {Array} $data
     * @return array {Array} $data
     */
    private function getBlogEntry($data, $category)
    {
        $entryAmount = (int) $data['entry_amount'];
        
        $category = $data['blog_entry_selection'] ?: $category;

        // If the blog element is already set but didn't have any thumbnail size, we need to set it here...
        if (!isset($data['thumbnail_size'])) {
            $data['thumbnail_size'] = 3;
        }

        if ($category === null) {
            return $data;
        }

        // Get the category model for the given category ID
        /** @var $category \Shopware\Models\Category\Category */
        $category = Shopware()->Models()->find('Shopware\Models\Category\Category', $category);

        if (!$category) {
            return $data;
        }

        $builder = Shopware()->Models()->createQueryBuilder();

        if ($data['blog_entry_selection']) {
            $builder->select(array('blog', 'media', 'mappingMedia'))
                ->from('Shopware\Models\Blog\Blog', 'blog')
                ->leftJoin('blog.media', 'mappingMedia', \Doctrine\ORM\Query\Expr\Join::WITH, 'mappingMedia.preview = 1')
                ->leftJoin('mappingMedia.media', 'media')
                ->leftJoin('blog.category', 'category')
                ->where('blog.active = 1')
                ->andWhere('blog.displayDate <= :displayDate')
                ->andWhere('blog.categoryId = :category')
                ->orderBy('blog.displayDate', 'DESC')
                ->setFirstResult(0)
                ->setMaxResults($entryAmount)
                ->setParameter('displayDate', date('Y-m-d H:i:s'))
                ->setParameter('category', $category->getId());
        } else {
            $builder->select(array('blog', 'media', 'mappingMedia'))
                ->from('Shopware\Models\Blog\Blog', 'blog')
                ->leftJoin('blog.media', 'mappingMedia', \Doctrine\ORM\Query\Expr\Join::WITH, 'mappingMedia.preview = 1')
                ->leftJoin('mappingMedia.media', 'media')
                ->leftJoin('blog.category', 'category')
                ->where('blog.active = 1')
                ->andWhere('blog.displayDate <= :displayDate')
                ->andWhere('(category.path LIKE :path OR category.id = :categoryId)')
                ->orderBy('blog.displayDate', 'DESC')
                ->setFirstResult(0)
                ->setMaxResults($entryAmount)
                ->setParameter('displayDate', date('Y-m-d H:i:s'))
                ->setParameter('categoryId', $category->getId())
                ->setParameter('path', '%|' . $category->getId() . '|%')
            ;
        }
        
        $query = $this->getForceIndexQuery($builder->getQuery(), 'emotion_get_blog_entry');
        $result = $query->getArrayResult();

        $mediaIds = [];
        foreach ($result as $entry) {
            $mediaIds = array_merge(
                array_column($entry['media'], 'mediaId'),
                $mediaIds
            );
        }
        $context = $this->get('shopware_storefront.context_service')->getShopContext();
        $medias = $this->get('shopware_storefront.media_service')->getList($mediaIds, $context);

        //now we get the configured image and thumbnail dir.
        $imageDir = $context->getBaseUrl() . '/media/image/';
        $imageDir = str_replace('/media/image/', DIRECTORY_SEPARATOR, $imageDir);

        foreach ($result as &$entry) {
            foreach ($entry['media'] as $media) {
                if (empty($media['mediaId'])) {
                    continue;
                }
                $id = $media['mediaId'];

                if (!isset($medias[$id])) {
                    continue;
                }

                $struct = $medias[$id];

                $mediaData = Shopware()->Container()->get('legacy_struct_converter')->convertMediaStruct($struct);
                $entry['media'] = $mediaData;

                if (Shopware()->Shop()->getTemplate()->getVersion() < 3) {
                    $thumbs = [];
                    foreach ($entry['media']['thumbnails'] as $thumb) {
                        $thumbs[] = str_replace($imageDir, '', $thumb);
                    }
                    $entry['media'] = [
                        'path' => str_replace($imageDir, '', $mediaData['source']),
                        'thumbnails' => $thumbs
                    ];
                }
            }
        }

        if (!empty($result)) {
            $data['totalCount'] = count($result);
            $data['entries'] = $result;
        }
        return $data;
    }


    /**
     * Helper function to set the FORCE INDEX path.
     * @param $query \Doctrine\ORM\Query
     * @param $index String
     * @return \Doctrine\ORM\Query
     */
    private function getForceIndexQuery($query, $index)
    {
        $query->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Shopware\Components\Model\Query\SqlWalker\ForceIndexWalker');
        $query->setHint(SqlWalker\ForceIndexWalker::HINT_FORCE_INDEX, $index);
        $query->setHint(SqlWalker\ForceIndexWalker::HINT_STRAIGHT_JOIN, true);
        return $query;
    }


    private function getArticleByNumber($data, $category, $element)
    {
        return $data;
    }

    private function getCategoryTeaser($data, $category, $element)
    {
        // First get category name
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select('category.name')
            ->from('Shopware\Models\Category\Category', 'category')
            ->where('category.id = ?1')
            ->setParameter(1, $data["category_selection"]);

        $categoryName = $builder->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $data["categoryName"] = $categoryName["name"];

        // Second get category image per random, if configured
        if ($data["image_type"] != "selected_image") {
            if ($data['blog_category']) {
                $result = $this->getRandomBlogEntry($data["category_selection"]);

                $data['image'] = $result['media'];

                if (Shopware()->Shop()->getTemplate()->getVersion() < 3) {
                    if (!empty($result['media']['thumbnails'])) {
                        $data['image'] = $result['media']['thumbnails'][2];
                        $data['images'] = $result['media']['thumbnails'];
                    } else {
                        $data['image'] = $result['media']['path'];
                    }
                }
            } else {
                // Get random article from selected $category
                $temp = Shopware()->Modules()->Articles()->sGetPromotionById('random', $data["category_selection"], 0, true);

                $data['image'] = $temp['image'];
                $data['images'] = $temp['images'];
                if (Shopware()->Shop()->getTemplate()->getVersion() < 3) {
                    $data['images'] = $temp['image']['src'];
                    $data["image"] = $temp["image"]["src"][2];
                }
            }
        } else {
            $mediaId = Shopware()->Db()->fetchOne('SELECT id FROM s_media WHERE path = ?', [$data['image']]);
            $context = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext();
            $media = Shopware()->Container()->get('shopware_storefront.media_service')->get($mediaId, $context);
            $data['media'] = Shopware()->Container()->get('legacy_struct_converter')->convertMediaStruct($media);
        }

        return $data;
    }

    private function articleByNumber($number)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select('article.id')
            ->from('Shopware\Models\Article\Article', 'article')
            ->join('article.details', 'details')
            ->where('details.number = ?1')
            ->setParameter(1, $number)
            ->setFirstResult(0)
            ->setMaxResults(1);

        $articleId = $builder->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $article = array();
        if (!empty($articleId['id'])) {
            $article = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, $articleId['id']);
        }
        return $article;
    }

    private function getBannerMappingLinks($data, $category, $element)
    {

        if (!empty($data['link'])) {
            preg_match('/^([a-z]*:\/\/|shopware\.php|mailto:)/i', $data['link'], $matches);

            if (empty($matches) && substr($data['link'], 0, 1) === '/') {
                $data['link'] = $this->Request()->getBaseUrl() . $data['link'];
            }
        }

        // Get image size of the banner
        if (isset($data['file']) && !empty($data['file'])) {
            $fullPath = getcwd() . DIRECTORY_SEPARATOR . $data['file'];
            list($bannerWidth, $bannerHeight) = getimagesize($fullPath);

            $data['fileInfo'] = array(
                'width' => $bannerWidth,
                'height' => $bannerHeight
            );
        }

        $mappings = $data['bannerMapping'];
        if (!empty($mappings)) {
            foreach ($mappings as $key => $mapping) {
                $number = $mapping['link'];

                if (!empty($number)) {
                    preg_match('/^([a-z]*:\/\/|shopware\.php|mailto:)/i', $number, $matches);

                    if (empty($matches)) {
                        if (substr($number, 0, 1) === '/') {
                            $mapping['link'] = $this->Request()->getBaseUrl() . $number;
                        } else {
                            $mapping['link'] = $this->articleByNumber($number);
                            $mapping['link'] = $mapping['link']['linkDetails'];
                        }
                    }
                }

                $mappings[$key] = $mapping;
            }
        }

        $mediaId = Shopware()->Db()->fetchOne("SELECT id FROM s_media WHERE path = ?", [$data['file']]);
        if ($mediaId) {
            $context = $this->get('shopware_storefront.context_service')->getShopContext();
            $media = $this->get('shopware_storefront.media_service')->get($mediaId, $context);
            $mediaData = $this->get('legacy_struct_converter')->convertMediaStruct($media);
            $data = array_merge($mediaData, $data);
        }

        $data['bannerMapping'] = $mappings;

        return $data;
    }

    private function getManufacturerSlider($data, $category, $element)
    {
        if (empty($data["manufacturer_type"])) {
            return $data;
        }

        // Get all manufacturers
        if ($data["manufacturer_type"] == "manufacturers_by_cat") {
            $data["values"] = Shopware()->Modules()->Articles()->sGetAffectedSuppliers($data["manufacturer_category"], 12);
        } else {
            $selectedManufacturers = $data["selected_manufacturers"];
            $manufacturers = array();

            foreach ($selectedManufacturers as $k => $manufacturer) {
                $manufacturers[] = $manufacturer["supplierId"];
            }

            $builder = Shopware()->Models()->createQueryBuilder();
            $builder->select('supplier.id', 'supplier.name', 'supplier.image', 'supplier.link', 'supplier.description')
                ->from('Shopware\Models\Article\Supplier', 'supplier')
                ->where('supplier.id IN (?1)')
                ->setParameter(1, $manufacturers);

            $data["values"] = $builder->getQuery()->getArrayResult();

            $temporaryValues = array();
            foreach ($manufacturers as $manufacturer) {
                foreach ($data["values"] as $value) {
                    if ($value["id"] == $manufacturer) {
                        $temporaryValues[] = $value;
                    }
                }
            }

            $data["values"] = $temporaryValues;

            foreach ($data["values"] as &$value) {
                $query = array(
                    'controller' => 'listing',
                    'action'     => 'manufacturer',
                    'sSupplier'  => $value['id']
                );
                if (!empty($category) && $category != Shopware()->Shop()->getCategory()->getId()) {
                    $query['sCategory'] = $category;
                }

                $value["link"] = Shopware()->Router()->assemble($query);
            }
        }

        return $data;
    }

    private function getBannerSlider($data, $category, $element)
    {
        $data["values"] = $data["banner_slider"];

        $mediaIds = array_column($data['values'], 'mediaId');
        $context = $this->get('shopware_storefront.context_service')->getShopContext();
        $media = $this->get('shopware_storefront.media_service')->getList($mediaIds, $context);

        foreach ($data["values"] as &$value) {
            if (!empty($value['link'])) {
                preg_match('/^(http|https):\/\//', $value['link'], $matches);

                if (empty($matches)) {
                    $value['link'] = $this->Request()->getBaseUrl() . $value['link'];
                }
            }
            $single = $media[$value['mediaId']];

            $single = $this->get('legacy_struct_converter')->convertMediaStruct($single);
            $value = array_merge($value, $single);
        }

        return $data;
    }

    private function getArticleSlider($data, $category, $element)
    {
        if (!isset($data["article_slider_select"])) {
            $data["article_slider_select"] = "horizontal";
        }

        // Determinate how many products showing on initial request
        if ($data["article_slider_select"] == "horizontal") {
            $perPage = $element['endCol'] - $element['startCol'] + 1;
        } else {
            $perPage = $element['endRow'] - $element['startRow'] + 1;
        }

        $customerGroupId = Shopware()->Modules()->System()->sUSERGROUPDATA['id'];
        $category = (int) $data['article_slider_category'] ? : $category;

        $values = array();

        $max = $data["article_slider_max_number"];
        $maxPages = round($max / $perPage);

        switch ($data["article_slider_type"]) {
            case "selected_article":
                foreach ($data["selected_articles"] as &$article) {
                    $articleId = $article["articleId"];
                    $entry = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, $articleId, false);
                    if (!empty($entry["articleID"])) {
                        $values[] = $entry;
                    }
                }
                break;
            case "topseller":
            case "newcomer":
            case "price_asc":
            case "price_desc":
                $temp = $this->getProductSliderData(
                    $category,
                    $customerGroupId,
                    0,
                    $perPage,
                    $data["article_slider_type"]
                );
                $values = $temp["values"];
                $data["pages"] = $temp["pages"] > $maxPages ? $maxPages : $temp["pages"];

                $query = array(
                    'controller' => 'emotion',
                    'module' => 'widgets',
                    'action' => 'emotionArticleSlider',
                    'sort' => $data["article_slider_type"]
                );
                $data["ajaxFeed"] = Shopware()->Router()->assemble($query);
                break;
            default;
                // Prevent the slider form endless loading
                $data['article_slider_type'] = 'selected_article';
                return $data;
        }

        $data["values"] = $values;
        $data['categoryId'] = $category;
        return $data;
    }

    /**
     * Returns a list of top sold products
     *
     * @param $category
     * @param $customerGroupId
     * @param int $offset
     * @param $limit
     * @param string $sort
     * @return array
     */
    private function getProductSliderData($category, $customerGroupId, $offset = 0, $limit, $sort = null)
    {
        $context = Shopware()->Container()->get('shopware_storefront.context_service')->getProductContext();
        $criteria = new Criteria();

        $criteria->addBaseCondition(new CategoryCondition([$category]));
        $criteria->addBaseCondition(new CustomerGroupCondition([$customerGroupId]));
        $criteria->addBaseCondition(new HasPriceCondition());

        $criteria->offset($offset)
            ->limit($limit);

        switch ($sort) {
             case 'price_asc':
                 $criteria->addSorting(new PriceSorting(SortingInterface::SORT_ASC));
                 break;
             case 'price_desc':
                 $criteria->addSorting(new PriceSorting(SortingInterface::SORT_DESC));
                 break;
             case 'topseller':
                 $criteria->addSorting(new PopularitySorting(SortingInterface::SORT_DESC));
                 break;
             case 'newcomer':
                $criteria->addSorting(new ReleaseDateSorting(SortingInterface::SORT_DESC));
                break;
            }


        /** @var $result \Shopware\Bundle\SearchBundle\ProductSearchResult */
        $result = Shopware()->Container()->get('shopware_search.product_search')
            ->search($criteria, $context);

        $data = array();

        foreach ($result->getProducts() as $product) {
            $article = Shopware()->Container()->get('legacy_struct_converter')->convertListProductStruct($product);
            $article = Shopware()->Container()->get('legacy_event_manager')->firePromotionByIdEvents(
                $article,
                $category,
                Shopware()->Modules()->Articles()
            );

            if ($article) {
                $data[] = $article;
            }
        }

        $count = $result->getTotalCount();
        $pages = round($count / $limit);

        if ($pages == 0 && $count > 0) {
            $pages = 1;
        }

        return array("values" => $data, "pages" => $pages);
    }

    /**
     * preview action method
     *
     * generates the backend iframe emotion preview
     */
    public function previewAction()
    {
        $emotionId = $this->Request()->getParam('emotionId');

        // fetch devices on responsive template or load full emotions for older templates.
        $templateVersion = Shopware()->Shop()->getTemplate()->getVersion();

        if ($templateVersion >= 3) {
            
            $emotion = $this->get('emotion_device_configuration')->getById($emotionId);

            $viewAssignments['emotion'] = $emotion;
            $viewAssignments['hasEmotion'] = (!empty($emotion));

            $viewAssignments['showListing'] = (bool) max(array_column($emotion, 'showListing'));

        } else {
            //check category emotions
            $emotion = $this->get('emotion_device_configuration')->getById($emotionId);
            $viewAssignments['hasEmotion'] = !empty($emotion);
        }

        $showListing = (empty($emotion) || !empty($emotion['show_listing']));
        $viewAssignments['showListing'] = $showListing;
        
        $this->View()->assign($viewAssignments);

        //fake to prevent rendering the templates with the widgets module.
        //otherwise the template engine don't accept to load templates of the `frontend` module
        $this->Request()->setModuleName('frontend');
    }
}
