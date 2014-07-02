<?php

namespace Shopware\Tests\Service\Price;

use Shopware\Struct\Context;
use Shopware\Struct\Product\Price;

class GraduatedPricesTest extends \Enlight_Components_Test_TestCase
{
    /**
     * @var \Shopware\Tests\Service\Helper
     */
    private $helper;

    protected function setUp()
    {
        $this->helper = new \Shopware\Tests\Service\Helper();
        parent::setUp();
    }

    protected function tearDown()
    {
        $this->helper->cleanUp();
        parent::tearDown();
    }

    /**
     * @return Context
     */
    private function getContext()
    {
        $tax = $this->helper->createTax();
        $customerGroup = $this->helper->createCustomerGroup();
        $fallback = $this->helper->createCustomerGroup(array('key'=> 'BACK'));
        $shop = $this->helper->getShop();

        return $this->helper->createContext(
            $customerGroup,
            $shop,
            array($tax),
            $fallback
        );
    }

    private function getProduct($number, Context $context)
    {
        $data = $this->helper->getSimpleProduct(
            $number,
            array_shift($context->getTaxRules()),
            $context->getCurrentCustomerGroup()
        );

        $data['mainDetail']['prices'] = array_merge(
            $data['mainDetail']['prices'],
            $this->helper->getGraduatedPrices(
                $context->getFallbackCustomerGroup()->getKey(),
                -20
            )
        );

        return $data;
    }

    public function testSimpleGraduation()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);

        $this->helper->createArticle($data);

        $listProduct = $this->helper->getListProduct($number, $context);
        $graduation = $listProduct->getPrices();

        $this->assertCount(3, $graduation);
        foreach($graduation as $price) {
            $this->assertEquals('PHP', $price->getCustomerGroup()->getKey());
            $this->assertGreaterThan(0, $price->getCalculatedPrice());
        }
    }

    public function testFallbackGraduation()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);

        $this->helper->createArticle($data);

        $context->getCurrentCustomerGroup()->setKey('NOT');

        $listProduct = $this->helper->getListProduct($number, $context);
        $graduation = $listProduct->getPrices();

        $this->assertCount(3, $graduation);
        foreach($graduation as $price) {
            $this->assertEquals('BACK', $price->getCustomerGroup()->getKey());
            $this->assertGreaterThan(0, $price->getCalculatedPrice());
        }
    }

    public function testVariantGraduation()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);

        $configurator = $this->helper->getConfigurator(
            $context->getCurrentCustomerGroup(),
            $number
        );
        $data = array_merge($data, $configurator);

        foreach($data['variants'] as &$variant) {
            $variant['prices'] = $this->helper->getGraduatedPrices(
                $context->getCurrentCustomerGroup()->getKey(),
                100
            );
        }

        $variantNumber = $data['variants'][1]['number'];

        $this->helper->createArticle($data);

        /**@var $first Price*/
        $listProduct = $this->helper->getListProduct($number, $context);
        $this->assertCount(3, $listProduct->getPrices());
        $first = array_shift($listProduct->getPrices());
        $this->assertEquals(100, $first->getCalculatedPrice());


        /**@var $first Price*/
        $listProduct = $this->helper->getListProduct($variantNumber, $context);

        $this->assertCount(3, $listProduct->getPrices());
        $first = array_shift($listProduct->getPrices());
        $this->assertEquals(200, $first->getCalculatedPrice());
    }

    public function testGraduationByPriceGroup()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        
        $data = $this->getProduct($number, $context);
        $data['mainDetail']['prices'] = array(array(
            'from' => 1,
            'to' => null,
            'price' => 40,
            'customerGroupKey' => $context->getCurrentCustomerGroup()->getKey(),
            'pseudoPrice' => 110
        ));

        $priceGroup = $this->helper->createPriceGroup();
        $data['priceGroupId'] = $priceGroup->getId();
        $data['priceGroupActive'] = true;

        $this->helper->createArticle($data);

        $listProduct = $this->helper->getListProduct($number, $context);

        $graduations = $listProduct->getPrices();
        $this->assertCount(3, $graduations);

        $this->assertEquals(36, $graduations[0]->getCalculatedPrice());
        $this->assertEquals(1, $graduations[0]->getFrom());
        $this->assertEquals(4, $graduations[0]->getTo());

        $this->assertEquals(32, $graduations[1]->getCalculatedPrice());
        $this->assertEquals(5, $graduations[1]->getFrom());
        $this->assertEquals(9, $graduations[1]->getTo());

        $this->assertEquals(28, $graduations[2]->getCalculatedPrice());
        $this->assertEquals(10, $graduations[2]->getFrom());
        $this->assertEquals(null, $graduations[2]->getTo());
    }
}