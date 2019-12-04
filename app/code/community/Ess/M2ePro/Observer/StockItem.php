<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract as ChangeProcessorAbstract;

class Ess_M2ePro_Observer_StockItem extends Ess_M2ePro_Observer_Abstract
{
    /**
     * @var null|int
     */
    protected $_productId = null;

    protected $_affectedListingsProducts = array();

    //########################################

    public function beforeProcess()
    {
        $productId = (int)$this->getEventObserver()->getData('item')->getData('product_id');

        if ($productId <= 0) {
            throw new Ess_M2ePro_Model_Exception('Product ID should be greater than 0.');
        }

        $this->_productId = $productId;
    }

    public function process()
    {
        if (!$this->areThereAffectedItems()) {
            return;
        }

        $this->addListingProductInstructions();

        $this->processQty();
        $this->processStockAvailability();
    }

    // ---------------------------------------

    protected function processQty()
    {
        $oldValue = (int)$this->getEventObserver()->getData('item')->getOrigData('qty');
        $newValue = (int)$this->getEventObserver()->getData('item')->getData('qty');

        if ($oldValue == $newValue) {
            return;
        }

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {

            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */

            $this->logListingProductMessage(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_QTY,
                $oldValue, $newValue
            );
        }
    }

    protected function processStockAvailability()
    {
        $oldValue = (bool)$this->getEventObserver()->getData('item')->getOrigData('is_in_stock');
        $newValue = (bool)$this->getEventObserver()->getData('item')->getData('is_in_stock');

        $oldValue = $oldValue ? 'IN Stock' : 'OUT of Stock';
        $newValue = $newValue ? 'IN Stock' : 'OUT of Stock';

        if ($oldValue == $newValue) {
            return;
        }

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {

            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */

            $this->logListingProductMessage(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_STOCK_AVAILABILITY,
                $oldValue, $newValue
            );
        }
    }

    //########################################

    protected function getProductId()
    {
        return $this->_productId;
    }

    protected function addListingProductInstructions()
    {
        $synchronizationInstructionsData = array();

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {
            /** @var Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract $changeProcessor */
            $changeProcessor = Mage::getModel(
                'M2ePro/'.ucfirst($listingProduct->getComponentMode()).'_Magento_Product_ChangeProcessor'
            );
            $changeProcessor->setListingProduct($listingProduct);
            $changeProcessor->setDefaultInstructionTypes(
                array(
                ChangeProcessorAbstract::INSTRUCTION_TYPE_PRODUCT_STATUS_DATA_POTENTIALLY_CHANGED,
                ChangeProcessorAbstract::INSTRUCTION_TYPE_PRODUCT_QTY_DATA_POTENTIALLY_CHANGED,
                )
            );
            $changeProcessor->process();
        }

        Mage::getResourceModel('M2ePro/Listing_Product_Instruction')->add(
            $synchronizationInstructionsData
        );
    }

    //########################################

    protected function areThereAffectedItems()
    {
        $products = $this->getAffectedListingsProducts();
        return !empty($products);
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing_Product[]
     */
    protected function getAffectedListingsProducts()
    {
        if (!empty($this->_affectedListingsProducts)) {
            return $this->_affectedListingsProducts;
        }

        return $this->_affectedListingsProducts = Mage::getResourceModel('M2ePro/Listing_Product')
                                                      ->getItemsByProductId($this->getProductId());
    }

    //########################################

    protected function logListingProductMessage(
        Ess_M2ePro_Model_Listing_Product $listingProduct,
        $action,
        $oldValue,
        $newValue
    ) {
        $log = Mage::getModel('M2ePro/Listing_Log');
        $log->setComponentMode($listingProduct->getComponentMode());
        $actionId = $log->getResource()->getNextActionId();

        $log->addProductMessage(
            $listingProduct->getListingId(),
            $listingProduct->getProductId(),
            $listingProduct->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
            $actionId,
            $action,
            Mage::helper('M2ePro/Module_Log')->encodeDescription(
                'From [%from%] to [%to%].',
                array('!from'=>$oldValue,'!to'=>$newValue)
            ),
            Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
        );
    }

    //########################################
}
