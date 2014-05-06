<?php

namespace Shopware\Service;

use Shopware\Struct;
use Shopware\Gateway\DBAL as Gateway;

class ListProduct
{
    /**
     * @var Gateway\ListProduct
     */
    private $productGateway;

    /**
     * @var Media
     */
    private $mediaService;

    /**
     * @var CheapestPrice
     */
    private $cheapestPriceService;

    /**
     * @var GraduatedPrices
     */
    private $graduatedPricesService;

    /**
     * @var PriceCalculation
     */
    private $priceCalculationService;

    /**
     * @var Translation
     */
    private $translationService;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    function __construct(
        Gateway\ListProduct $productGateway,
        GraduatedPrices $graduatedPricesService,
        CheapestPrice $cheapestPriceService,
        PriceCalculation $priceCalculationService,
        Media $mediaService,
        Translation $translationService,
        \Enlight_Event_EventManager $eventManager
    ) {
        $this->productGateway = $productGateway;
        $this->graduatedPricesService = $graduatedPricesService;
        $this->cheapestPriceService = $cheapestPriceService;
        $this->priceCalculationService = $priceCalculationService;
        $this->mediaService = $mediaService;
        $this->translationService = $translationService;
        $this->eventManager = $eventManager;
    }

    /**
     * Returns a minified product variant which contains only
     * simplify data of a variant.
     *
     * The product data is fully calculated, which means
     * that the product data is already translated and
     * the product prices are calculated to the current global state
     * of the shop.
     *
     * This product type is normally used for product overviews
     * like listings or sliders.
     *
     * To get the whole product data you can use the `get` function.
     *
     * @param array $numbers
     * @param \Shopware\Struct\Context $context
     * @return Struct\ListProduct[]
     */
    public function getList(array $numbers, Struct\Context $context)
    {
        $products = $this->productGateway->getList($numbers, $context);

        $covers = $this->mediaService->getCovers($products, $context);

        $graduatedPrices = $this->graduatedPricesService->getList($products, $context);

        $cheapestPrices = $this->cheapestPriceService->getList($products, $context);

        $result = array();
        foreach ($products as $product) {
            $key = $product->getNumber();
            $product->setCover($covers[$key]);

            $product->setPriceRules($graduatedPrices[$key]);

            $product->setCheapestPriceRule($cheapestPrices[$key]);

            if (!$product->hasState(Struct\ListProduct::STATE_PRICE_CALCULATED)) {
                $this->priceCalculationService->calculateProduct($product, $context);
            }

            if (!$product->hasState(Struct\ListProduct::STATE_TRANSLATED)) {
//                $this->translationService->translateProduct($product, $context->getShop());
            }

            $result[$product->getNumber()] = $product;
        }

        return $result;
    }

    /**
     * Returns a minified product variant which contains only
     * simplify data of a variant.
     *
     * The product data is fully calculated, which means
     * that the product data is already translated and
     * the product prices are calculated to the current global state
     * of the shop.
     *
     * This product type is normally used for product overviews
     * like listings or sliders.
     *
     * To get the whole product data you can use the `get` function.
     *
     * @param string $number
     * @param \Shopware\Struct\Context $context
     * @return Struct\ListProduct
     */
    public function get($number, Struct\Context $context)
    {
        $products = $this->getList(array($number), $context);

        return array_shift($products);
    }
}
