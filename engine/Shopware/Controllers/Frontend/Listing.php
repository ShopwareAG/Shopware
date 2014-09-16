<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
 * Listing controller
 *
 * @category  Shopware
 * @package   Shopware\Controllers\Frontend
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Frontend_Listing extends Enlight_Controller_Action
{
    /**
     * Translation handler.
     * @var Shopware_Components_Translation
     */
    private $translator;

    /**
     * Index action method
     */
    public function indexAction()
    {
        $supplierId = $this->Request()->getParam('sSupplier');

        $categoryId = $this->Request()->getParam('sCategory');
        $categoryContent = Shopware()->Modules()->Categories()->sGetCategoryContent($categoryId);
        $categoryId = $categoryContent['id'];


        /** @var $mapper \Shopware\Components\QueryAliasMapper */
        $mapper = $this->get('query_alias_mapper');
        $mapper->replaceShortRequestQueries($this->Request());

        Shopware()->System()->_GET['sCategory'] = $categoryId;

        if (!empty($categoryContent['external'])) {
            $location = $categoryContent['external'];
        } elseif (empty($categoryContent)) {
            $location = array('controller' => 'index');
        } elseif (Shopware()->Config()->categoryDetailLink && $categoryContent['articleCount'] == 1) {
            /**@var $repository \Shopware\Models\Category\Repository*/
            $repository = Shopware()->Models()->getRepository('Shopware\Models\Category\Category');
            $articleId = $repository->getActiveArticleIdByCategoryId($categoryContent['id']);
            if (!empty($articleId)) {
                $location = array(
                    'sViewport' => 'detail',
                    'sArticle' => $articleId
                );
            }
        }
        if (isset($location)) {
            return $this->redirect($location, array('code' => 301));
        }

        if (Shopware()->Config()->get('seoSupplier') === true && $categoryContent['parentId'] == 1 && $this->Request()->getParam('sSupplier', false)) {
            $supplier = Shopware()->Models()->getRepository('Shopware\Models\Article\Supplier')->find($this->Request()->getParam('sSupplier'));

            $supplierName = $supplier->getName();
            $supplierTitle = $supplier->getMetaTitle();
            $categoryContent['metaDescription'] = $supplier->getMetaDescription();
            $categoryContent['metaKeywords'] = $supplier->getMetaKeywords();
            if (!Shopware()->Shop()->getDefault()) {
                $translation = $this->getTranslator()->read(Shopware()->Shop()->getId(), 'supplier', $supplier->getId());
                if (array_key_exists('metaTitle', $translation)) {
                    $supplierTitle = $translation['metaTitle'];
                }
                if (array_key_exists('metaDescription', $translation)) {
                    $categoryContent['metaDescription'] = $translation['metaDescription'];
                }
                if (array_key_exists('metaKeywords', $translation)) {
                    $categoryContent['metaKeywords'] = $translation['metaKeywords'];
                }
            }
            $path = $this->Front()->Router()->assemble(array(
                'sViewport' => 'supplier',
                'sSupplier' => $supplier->getId(),
            ));
            if ($path) {
                $categoryContent['sSelfCanonical'] = $path;
            }
            if (!empty($supplierTitle)) {
                $categoryContent['title'] = $supplierTitle.' | '.Shopware()->Shop()->getName();
            } elseif (!empty($supplierName)) {
                $categoryContent['title'] = $supplierName;
            }
            $categoryContent['canonicalTitle'] = $supplierName;
        }

        /**@var $repository \Shopware\Models\Emotion\Repository*/
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Emotion\Emotion');
        $query = $repository->getCampaignByCategoryQuery($categoryId);
        $campaignsResult = $query->getArrayResult();
        $campaigns = array();
        foreach ($campaignsResult as $campaign) {
            $campaign['categoryId'] = $categoryId;
            $campaigns[$campaign['landingPageBlock']][] = $campaign;
        }

        $showListing = true;
        $hasEmotion = false;
        $viewAssignments = array(
            'sBanner' => Shopware()->Modules()->Marketing()->sBanner($categoryId),
            'sBreadcrumb' => $this->getBreadcrumb($categoryId),
            'sCategoryContent' => $categoryContent,
            'campaigns' => $campaigns,
            'sCategoryInfo' => $categoryContent
        );

        if (!$this->Request()->getQuery('sSupplier')
            && !$this->Request()->getQuery('sPage')
            && !$this->Request()->getQuery('sFilterProperties')
            && !$this->Request()->getParam('sRss')
            && !$this->Request()->getParam('sAtom')
        ) {
            // Check if is a emotion grid is active for this category
            $emotion = Shopware()->Db()->fetchRow("
                SELECT e.id, e.show_listing
                FROM s_emotion_categories ec, s_emotion e
                WHERE ec.category_id = ?
                AND e.id = ec.emotion_id
                AND e.is_landingpage = 0
                AND e.active = 1
                AND (e.valid_to >= NOW() OR e.valid_to IS NULL)
            ", array($categoryId));
            $hasEmotion = !empty($emotion['id']);
            $showListing = !$hasEmotion || !empty($emotion['show_listing']);
        }

        $viewAssignments['showListing'] = $showListing;
        $viewAssignments['hasEmotion'] = $hasEmotion;
        //assign the variables here for the emotion view
        $this->View()->assign($viewAssignments);
        if (!$showListing) {
            return;
        }

        $categoryArticles = Shopware()->Modules()->Articles()->sGetArticlesByCategory($categoryId);

        if(empty($categoryContent['noViewSelect'])
            && !empty($categoryArticles['sTemplate'])
            && !empty($categoryContent['layout'])) {
            if ($categoryArticles['sTemplate'] == 'table') {
                if ($categoryContent['layout'] == '1col') {
                    $categoryContent['layout'] = '3col';
                    $categoryContent['template'] = 'article_listing_3col.tpl';
                }
            } else {
                $categoryContent['layout'] = '1col';
                $categoryContent['template'] = 'article_listing_1col.tpl';
            }
        }

        $newTemplateLoaded = false;
        if ($this->Request()->getParam('sRss') || $this->Request()->getParam('sAtom')) {
            $this->Response()->setHeader('Content-Type', 'text/xml');
            $type = $this->Request()->getParam('sRss') ? 'rss' : 'atom';

            $this->View()->loadTemplate('frontend/listing/' . $type . '.tpl');
            $newTemplateLoaded = true;

        } elseif (!empty($categoryContent['template']) && empty($categoryContent['layout'])) {
            $this->view->loadTemplate('frontend/listing/' . $categoryContent['template']);
            $newTemplateLoaded = true;
        }

        if ($newTemplateLoaded) {
            //assign it again because load template was called
            $this->View()->assign($viewAssignments);
        }

        $this->View()->assign($categoryArticles);

        $this->View()->assign(array(
            'sCategoryContent' => $categoryContent,
            'activeFilterGroup' => $this->request->getQuery('sFilterGroup')
        ));
    }

    /**
     * Helper function which checks the configuration for listing filters.
     * @return boolean
     */
    protected function displayFiltersInListing()
    {
        return Shopware()->Config()->get('displayFiltersInListings', true);
    }

    /**
     * Returns listing breadcrumb
     *
     * @param int $categoryId
     * @return array
     */
    public function getBreadcrumb($categoryId)
    {
        $breadcrumb = Shopware()->Modules()->Categories()->sGetCategoriesByParent($categoryId);
        return array_reverse($breadcrumb);
    }

    /**
     * @return \Shopware_Components_Translation
     */
    private function getTranslator()
    {
        if (null === $this->translator) {
            $this->translator = new Shopware_Components_Translation();
        }

        return $this->translator;
    }

    /**
     * Gets a Callback-Function (callback) and the Id of an category (categoryID) from Request and read its first child-level
     */
    public function getCategoryAction()
    {
        $callback = $this->Request()->getParam('callback');

        if (empty($callback)) {
            $this->returnJsonCallback('');
            return;
        }

        $categoryId = $this->Request()->getParam('categoryId');
        $categoryId = intval($categoryId);

        if (empty($categoryId)) {
            $this->returnJsonCallback($callback);
            return;
        }

        $category = $this->getCategoryById($categoryId);

        $this->returnJsonCallback($callback, $category);
    }

    /**
     * Helper function to return the category information by category id
     * @param integer $categoryId
     * @return mixed
     */
    private function getCategoryById($categoryId)
    {
        /** @var \Shopware\Models\Category\Repository $categoryRepository */
        $categoryRepository = $this->get('models')->getRepository('Shopware\Models\Category\Category');
        $category = $categoryRepository->getCategoryByIdQuery($categoryId)->getArrayResult();

        if (empty($category)) {
            return array();
        }

        $category = $category[0];

        $category['link'] = $this->getCategoryLink($categoryId, $category['name'], $category['blog']);

        foreach ($category['children'] as &$child) {
            $child['link'] = $this->getCategoryLink($child['id'], $child['name'], $child['blog']);
        }

        return $category;
    }

    /**
     * Helper function to create a category link
     * @param integer $categoryId
     * @param string $categoryName
     * @param bool $blog
     * @return mixed|string
     */
    private function getCategoryLink($categoryId, $categoryName, $blog = false)
    {
        $sViewport = 'cat';

        if ($blog) {
            $sViewport = 'blog';
        }

        $link = $this->Front()->Router()->assemble(
            array(
                'sViewport' => $sViewport,
                'sCategory' => $categoryId,
                'title' => $categoryName
            )
        );

        return $link;
    }

    /**
     * Helper function to return a JSON-Callback
     * @param string $callback
     * @param array $data
     */
    private function returnJsonCallback($callback, $data = array())
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        $this->Front()->setParam('disableOutputBuffering', true);
        $this->Front()->returnResponse(true);

        $this->Response()->setHeader('Content-Type', 'text/javascript; charset=utf-8');
        $this->Response()->sendResponse();

        $jsonArray = array(
            'success' => !empty($data),
            'data' => $data
        );

        echo $callback . "('" . json_encode($jsonArray) . "')";
    }
}
