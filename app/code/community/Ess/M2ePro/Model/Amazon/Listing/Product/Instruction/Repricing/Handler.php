<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract as ChangeProcessorAbstract;

class Ess_M2ePro_Model_Amazon_Listing_Product_Instruction_Repricing_Handler
    implements Ess_M2ePro_Model_Listing_Product_Instruction_Handler_Interface
{
    //########################################

    protected function getAffectedInstructionTypes()
    {
        return array(
            ChangeProcessorAbstract::INSTRUCTION_TYPE_PRODUCT_PRICE_DATA_POTENTIALLY_CHANGED,
            ChangeProcessorAbstract::INSTRUCTION_TYPE_PRODUCT_STATUS_DATA_POTENTIALLY_CHANGED,
            Ess_M2ePro_Model_Amazon_Magento_Product_ChangeProcessor::INSTRUCTION_TYPE_REPRICING_DATA_CHANGED,
            Ess_M2ePro_Model_Amazon_Account_Repricing_ChangeProcessor::INSTRUCTION_TYPE_ACCOUNT_REPRICING_DATA_CHANGED,
            Ess_M2ePro_Model_Amazon_Repricing_Synchronization_General::INSTRUCTION_TYPE_STATUS_CHANGED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_INITIATOR_MOVING_PRODUCT_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            Ess_M2ePro_Model_Amazon_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_PRICE_DATA_CHANGED,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRICE_CHANGED,
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

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();

        if (!$amazonListingProduct->isRepricingUsed()) {
            return;
        }

        if ($this->isProcessRequired($amazonListingProduct->getRepricing())) {
            $amazonListingProduct->getRepricing()->setIsProcessRequired(true);
            $amazonListingProduct->getRepricing()->save();
            return;
        }

        if ($amazonListingProduct->getRepricing()->isProcessRequired()) {
            $amazonListingProduct->getRepricing()->setIsProcessRequired(false);
            $amazonListingProduct->getRepricing()->save();
        }
    }

    //########################################

    protected function isProcessRequired(Ess_M2ePro_Model_Amazon_Listing_Product_Repricing $listingProductRepricing)
    {
        $isDisabled         = $listingProductRepricing->isDisabled();
        $isRepricingManaged = $listingProductRepricing->isOnlineManaged();

        if ($isDisabled && !$isRepricingManaged) {
            return false;
        }

        if ($isDisabled                                 == $listingProductRepricing->getLastUpdatedIsDisabled() &&
            $listingProductRepricing->getRegularPrice() == $listingProductRepricing->getLastUpdatedRegularPrice() &&
            $listingProductRepricing->getMinPrice()     == $listingProductRepricing->getLastUpdatedMinPrice() &&
            $listingProductRepricing->getMaxPrice()     == $listingProductRepricing->getLastUpdatedMaxPrice()
        ) {
            return false;
        }

        return true;
    }

    //########################################
}
