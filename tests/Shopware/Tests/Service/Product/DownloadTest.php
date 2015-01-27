<?php

namespace Shopware\Tests\Service\Product;

use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Download;
use Shopware\Models\Article\Article;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\TestCase;

class DownloadTest extends TestCase
{
    /**
     * @param $number
     * @param Context $context
     * @param \Shopware\Models\Category\Category $category
     * @return Article
     */
    protected function getProduct($number, Context $context, Category $category = null)
    {
        $product = parent::getProduct($number, $context, $category);

        $product['downloads'] = array(
            array(
                'name' => 'first-download',
                'size' => 100,
                'file' => '/var/www/first.txt',
                'attribute' => array('id' => 20000)
            ),
            array(
                'name' => 'second-download',
                'size' => 200,
                'file' => '/var/www/second.txt',
                'attribute' => array('id' => 20000)
            )
        );

        return $product;
    }


    public function testSingleProduct()
    {
        $context = $this->getContext();
        $number = 'testSingleProduct';
        $data = $this->getProduct($number, $context);
        $this->helper->createArticle($data);

        $product = Shopware()->Container()->get('shopware_storefront.list_product_service_core')->get($number, $context);

        $downloads = Shopware()->Container()->get('shopware_storefront.product_download_service_core')->get($product, $context);

        $this->assertCount(2, $downloads);

        /**@var $download Download*/
        foreach ($downloads as $download) {
            $this->assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Product\Download', $download);
            $this->assertContains($download->getFile(), array('/var/www/first.txt', '/var/www/second.txt'));
            $this->assertCount(1, $download->getAttributes());
            $this->assertTrue($download->hasAttribute('core'));
        }
    }

    public function testDownloadList()
    {
        $numbers = array('testDownloadList-1', 'testDownloadList-2');
        $context = $this->getContext();
        foreach ($numbers as $number) {
            $data = $this->getProduct($number, $context);
            $this->helper->createArticle($data);
        }

        $products = Shopware()->Container()->get('shopware_storefront.list_product_service_core')
            ->getList($numbers, $context);

        $downloads = Shopware()->Container()->get('shopware_storefront.product_download_service_core')
            ->getList($products, $context);

        $this->assertCount(2, $downloads);

        foreach ($downloads as $number => $productDownloads) {
            $this->assertContains($number, $numbers);
            $this->assertCount(2, $productDownloads);
        }

        foreach ($numbers as $number) {
            $this->assertArrayHasKey($number, $downloads);
        }
    }
}
