<?php

namespace Shopware\Service;

use Shopware\Struct as Struct;
use Shopware\Gateway\DBAL as Gateway;

/**
 * @package Shopware\Service
 */
class Property
{
    /**
     * @var \Shopware\Gateway\DBAL\Property
     */
    private $propertyGateway;

    /**
     * @var Translation
     */
    private $translationService;

    function __construct(Gateway\Property $propertyGateway, Translation $translationService)
    {
        $this->propertyGateway = $propertyGateway;
        $this->translationService = $translationService;
    }

    /**
     * @param array $valueIds
     * @param Struct\Context $context
     * @return Struct\Property\Set[]
     */
    public function getList(array $valueIds, Struct\Context $context)
    {
        $properties = $this->propertyGateway->getList($valueIds);

        return $properties;
    }

    /**
     * Returns a single \Struct\Property\Set for the passed value id.
     *
     * @param $id
     * @param Struct\Context $context
     * @return Struct\Property\Set
     */
    public function get($id, Struct\Context $context)
    {
        $properties = $this->getList(array($id), $context);

        return array_shift($properties);
    }

    /**
     * @param \Shopware\Struct\ListProduct $product
     * @param \Shopware\Struct\Context $context
     *
     * @return array|\Shopware\Struct\Property\Set
     */
    public function getProductProperty(Struct\ListProduct $product, Struct\Context $context)
    {
        $set = $this->propertyGateway->getProductSet($product);

        if (!$set) {
            return null;
        }

        $this->translationService->translatePropertySet($set, $context->getShop());

        return $set;
    }
}