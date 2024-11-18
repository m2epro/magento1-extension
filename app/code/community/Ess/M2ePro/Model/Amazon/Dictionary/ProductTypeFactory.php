<?php


class Ess_M2ePro_Model_Amazon_Dictionary_ProductTypeFactory
{
    /**
     * @param Ess_M2ePro_Model_Marketplace $marketplace
     * @param string $nick
     * @param string $title
     * @param array $schema
     * @param array $variationThemes
     * @param array $attributesGroups
     * @param DateTime $serverUpdateDate
     * @param DateTime $clientUpdateDate
     * @return Ess_M2ePro_Model_Amazon_Dictionary_ProductType
     */
    public function create(
        Ess_M2ePro_Model_Marketplace $marketplace,
        $nick,
        $title,
        array $schema,
        array $variationThemes,
        array $attributesGroups,
        \DateTime $serverUpdateDate,
        \DateTime $clientUpdateDate
    ) {
        $entity = $this->createEmpty();
        $entity->create(
            $marketplace,
            $nick,
            $title,
            $schema,
            $variationThemes,
            $attributesGroups,
            $serverUpdateDate,
            $clientUpdateDate
        );

        return $entity;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Dictionary_ProductType
     */
    public function createEmpty()
    {
        /** @var Ess_M2ePro_Model_Amazon_Dictionary_ProductType */
        return Mage::getModel('M2ePro/Amazon_Dictionary_ProductType');
    }

}