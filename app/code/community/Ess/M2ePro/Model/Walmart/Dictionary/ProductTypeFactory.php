<?php

class Ess_M2ePro_Model_Walmart_Dictionary_ProductTypeFactory
{
    /**
     * @param int $marketplaceId
     * @param string $productTypeNick
     * @param string $productTypeTitle
     * @param array $attributes
     * @param array $variationAttributes
     * @return Ess_M2ePro_Model_Walmart_Dictionary_ProductType
     */
    public function create(
        $marketplaceId,
        $productTypeNick,
        $productTypeTitle,
        array $attributes,
        array $variationAttributes
    ) {
        $entity = $this->createEmpty();
        $entity->init(
            $marketplaceId,
            $productTypeNick,
            $productTypeTitle,
            $attributes,
            $variationAttributes
        );

        return $entity;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Dictionary_ProductType
     */
    public function createEmpty()
    {
        /** @var Ess_M2ePro_Model_Walmart_Dictionary_ProductType */
        return Mage::getModel('M2ePro/Walmart_Dictionary_ProductType');
    }
}
