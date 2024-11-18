<?php

class Ess_M2ePro_Model_Amazon_Dictionary_Marketplace_Repository
{
    /**
     * @return Ess_M2ePro_Model_Amazon_Dictionary_Marketplace|null
     */
    public function findByMarketplace(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        return $this->findByMarketplaceId((int)$marketplace->getId());
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Dictionary_Marketplace|null
     */
    public function findByMarketplaceId($marketplaceId)
    {
        $collection = Mage::getResourceModel('M2ePro/Amazon_Dictionary_Marketplace_Collection');
        $collection->addFieldToFilter(
            Ess_M2ePro_Model_Resource_Amazon_Dictionary_Marketplace::COLUMN_MARKETPLACE_ID,
            array('eq' => $marketplaceId)
        );

        $dictionary = $collection->getFirstItem();
        if ($dictionary->isObjectNew()) {
            return null;
        }

        return $dictionary;
    }

    /**
     * @return void
     */
    public function create(Ess_M2ePro_Model_Amazon_Dictionary_Marketplace $dictionaryMarketplace)
    {
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableAmazonListingProduct = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_amazon_dictionary_marketplace');
        $connWrite
            ->insert(
                $tableAmazonListingProduct,
                array(
                    'marketplace_id' => $dictionaryMarketplace->getMarketplaceId(),
                    'product_types' => $dictionaryMarketplace->getData('product_types')
                )
            );
    }

    /**
     * @return void
     */
    public function removeByMarketplace(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableAmazonListingProduct = Mage::helper('M2ePro/Module_Database_Structure')
                                         ->getTableNameWithPrefix('m2epro_amazon_dictionary_marketplace');
        $connWrite
            ->delete(
                $tableAmazonListingProduct,
                array('marketplace_id = ?' => $marketplace->getId())
            );
    }

}