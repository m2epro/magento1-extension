<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Listing_Auto_Actions_Listing
{
    const INSTRUCTION_TYPE_STOP            = 'auto_actions_stop';
    const INSTRUCTION_TYPE_STOP_AND_REMOVE = 'auto_actions_stop_and_remove';

    const INSTRUCTION_INITIATOR = 'auto_actions';

    /**
     * @var null|Ess_M2ePro_Model_Listing
     */
    protected $_listing = null;

    //########################################

    public function setListing(Ess_M2ePro_Model_Listing $listing)
    {
        $this->_listing = $listing;
    }

    /**
     * @return Ess_M2ePro_Model_Listing
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getListing()
    {
        if (!($this->_listing instanceof Ess_M2ePro_Model_Listing)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Property "Listing" should be set first.');
        }

        return $this->_listing;
    }

    //########################################

    public function deleteProduct(Mage_Catalog_Model_Product $product, $deletingMode)
    {
        if ($deletingMode == Ess_M2ePro_Model_Listing::DELETING_MODE_NONE) {
            return;
        }

        $listingsProducts = $this->getListing()->getProducts(true, array('product_id'=>(int)$product->getId()));

        if (empty($listingsProducts)) {
            return;
        }

        foreach ($listingsProducts as $listingProduct) {
            if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
                return;
            }

            if ($deletingMode == Ess_M2ePro_Model_Listing::DELETING_MODE_STOP && !$listingProduct->isStoppable()) {
                continue;
            }

            try {
                $instructionType = self::INSTRUCTION_TYPE_STOP;

                if ($deletingMode == Ess_M2ePro_Model_Listing::DELETING_MODE_STOP_REMOVE) {
                    $instructionType = self::INSTRUCTION_TYPE_STOP_AND_REMOVE;
                }

                $instruction = Mage::getModel('M2ePro/Listing_Product_Instruction');
                $instruction->setData(
                    array(
                    'listing_product_id' => $listingProduct->getId(),
                    'component'          => $listingProduct->getComponentMode(),
                    'type'               => $instructionType,
                    'initiator'          => self::INSTRUCTION_INITIATOR,
                    'priority'           => $listingProduct->isStoppable() ? 60 : 0,
                    )
                );
                $instruction->save();
            } catch (Exception $exception) {
            }
        }
    }

    //########################################

    abstract public function addProductByCategoryGroup(
        Mage_Catalog_Model_Product $product,
        Ess_M2ePro_Model_Listing_Auto_Category_Group $categoryGroup
);

    abstract public function addProductByGlobalListing(
        Mage_Catalog_Model_Product $product,
        Ess_M2ePro_Model_Listing $listing
);

    abstract public function addProductByWebsiteListing(
        Mage_Catalog_Model_Product $product,
        Ess_M2ePro_Model_Listing $listing
);

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function logAddedToMagentoProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode($this->getListing()->getComponentMode());
        $actionId = $tempLog->getResource()->getNextActionId();
        $tempLog->addProductMessage(
            $this->getListing()->getId(),
            $listingProduct->getProductId(),
            $listingProduct->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
            $actionId,
            Ess_M2ePro_Model_Listing_Log::ACTION_ADD_PRODUCT_TO_MAGENTO,
            'Product was Added',
            Ess_M2ePro_Model_Log_Abstract::TYPE_INFO
        );
    }

    //########################################
}
