<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract as ChangeProcessorAbstract;
use Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor as SynchronizationChangeProcessor;

class Ess_M2ePro_Model_Ebay_Listing_Product_Instruction_PickupStore_Handler
    implements Ess_M2ePro_Model_Listing_Product_Instruction_Handler_Interface
{
    //########################################

    protected function getAffectedInstructionTypes()
    {
        return array(
            ChangeProcessorAbstract::INSTRUCTION_TYPE_PRODUCT_QTY_DATA_POTENTIALLY_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_QTY_ENABLED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_QTY_DISABLED,
            SynchronizationChangeProcessor::INSTRUCTION_TYPE_REVISE_QTY_SETTINGS_CHANGED,
            Ess_M2ePro_Model_Ebay_Listing_Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_STATUS_CHANGED,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_QTY_CHANGED,
            Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
        );
    }

    //########################################

    public function process(Ess_M2ePro_Model_Listing_Product_Instruction_Handler_Input $input)
    {
        if (!$input->hasInstructionWithTypes($this->getAffectedInstructionTypes())) {
            return;
        }

        $listingProduct = $input->getListingProduct();

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();
        if (!$ebayListingProduct->getEbayAccount()->isPickupStoreEnabled()) {
            return;
        }

        $ebaySynchronizationTemplate = $ebayListingProduct->getEbaySynchronizationTemplate();
        if (!$ebaySynchronizationTemplate->isReviseUpdateQty()) {
            return;
        }

        $pickupStoreStateUpdater = Mage::getModel('M2ePro/Ebay_Listing_Product_PickupStore_State_Updater');
        $pickupStoreStateUpdater->setListingProduct($listingProduct);

        if ($ebaySynchronizationTemplate->isReviseUpdateQtyMaxAppliedValueModeOn()) {
            $pickupStoreStateUpdater->setMaxAppliedQtyValue(
                $ebaySynchronizationTemplate->getReviseUpdateQtyMaxAppliedValue()
            );
        }

        $pickupStoreStateUpdater->process();
    }

    //########################################
}
