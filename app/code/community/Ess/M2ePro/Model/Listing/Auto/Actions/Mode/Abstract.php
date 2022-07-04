<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Listing_Auto_Actions_Mode_Abstract
{
    /**
     * @var null|Mage_Catalog_Model_Product
     */
    protected $_product = null;

    //########################################

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    public function setProduct(Mage_Catalog_Model_Product $product)
    {
        $this->_product = $product;
    }

    //########################################

    /**
     * @return Mage_Catalog_Model_Product
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getProduct()
    {
        if (!($this->_product instanceof Mage_Catalog_Model_Product)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Property "Product" should be set first.');
        }

        return $this->_product;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing $listing
     * @return Ess_M2ePro_Model_Listing_Auto_Actions_Listing
     */
    protected function getListingObject(Ess_M2ePro_Model_Listing $listing)
    {
        $componentMode = ucfirst($listing->getComponentMode());

        /** @var Ess_M2ePro_Model_Amazon_Listing_Auto_Actions_Listing $object */
        $object = Mage::getModel('M2ePro/'.$componentMode.'_Listing_Auto_Actions_Listing');
        $object->setListing($listing);

        return $object;
    }

    //########################################

    /**
     * Preventing duplicate products in listings in one channel account and a marketplace via auto-adding
     *
     * @param Ess_M2ePro_Model_Listing $listing
     * @return bool
     */
    protected function existsDuplicateListingProduct($listing)
    {
        $collection = Mage::getModel('M2ePro/Listing_Product')->getCollection();

        $collection->getSelect()
            ->join(
                array('lst' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
                'lst.id = main_table.listing_id',
                array('marketplace_id' => 'marketplace_id', 'account_id' => 'account_id')
            )
            ->where(
                'lst.account_id = ' . $listing->getAccountId() .
                ' AND lst.marketplace_id = ' . $listing->getMarketplaceId()
            );

        $collection->addFieldToFilter('main_table.component_mode', $listing->getComponentMode());
        $collection->addFieldToFilter('lst.account_id', $listing->getAccountId());
        $collection->addFieldToFilter('lst.marketplace_id', $listing->getMarketplaceId());

        foreach ($collection->getItems() as $listingProduct) {
            if ($this->getProduct()->getId() == $listingProduct->getProductId()) {
                $this->writeDuplicateProductLog($listing->getComponentMode(), $listing->getId(), $listingProduct->getId());
                
                return true;
            }
        }

        return false;
    }

    //########################################

    /**
     * @param string $componentMode
     * @param int $listingId
     * @param int $listingProductId
     */
    private function writeDuplicateProductLog($componentMode, $listingId, $listingProductId)
    {
        $logModel = Mage::getModel('M2ePro/Listing_Log');
        $logModel->setComponentMode($componentMode);

        $logModel->addProductMessage(
            $listingId,
            $this->getProduct()->getId(),
            $listingProductId,
            Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
            $logModel->getResource()->getNextActionId(),
            Ess_M2ePro_Model_Listing_Log::ACTION_ADD_PRODUCT_TO_LISTING,
            'Product was not added since the item is already presented in another Listing related to 
            the Channel account and marketplace.',
            Ess_M2ePro_Model_Log_Abstract::TYPE_INFO
        );
    }

    //########################################
}
