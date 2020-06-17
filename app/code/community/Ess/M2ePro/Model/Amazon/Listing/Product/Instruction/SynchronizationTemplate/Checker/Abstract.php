<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract as ChangeProcessorAbstract;
use Ess_M2ePro_Model_Amazon_Template_Synchronization_ChangeProcessor as SynchronizationChangeProcessor;

abstract class Ess_M2ePro_Model_Amazon_Listing_Product_Instruction_SynchronizationTemplate_Checker_Abstract
    extends Ess_M2ePro_Model_Listing_Product_Instruction_SynchronizationTemplate_Checker_Abstract
{
    //########################################

    protected function getReviseInstructionTypes()
    {
        return array_unique(
            array_merge(
                $this->getReviseQtyInstructionTypes(),
                $this->getRevisePriceRegularInstructionTypes(),
                $this->getRevisePriceBusinessInstructionTypes(),
                $this->getReviseDetailsInstructionTypes(),
                $this->getReviseImagesInstructionTypes()
            )
        );
    }

    // ---------------------------------------

    protected function getReviseQtyInstructionTypes()
    {
        return array(
            ChangeProcessorAbstract::INSTRUCTION_TYPE_PRODUCT_QTY_DATA_POTENTIALLY_CHANGED,
            Ess_M2ePro_Model_Amazon_Magento_Product_ChangeProcessor::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
            Ess_M2ePro_Model_Amazon_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
            SynchronizationChangeProcessor::INSTRUCTION_TYPE_REVISE_QTY_ENABLED,
            SynchronizationChangeProcessor::INSTRUCTION_TYPE_REVISE_QTY_DISABLED,
            SynchronizationChangeProcessor::INSTRUCTION_TYPE_REVISE_QTY_SETTINGS_CHANGED,
            Ess_M2ePro_Model_Amazon_Listing_Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Relist_Response::INSTRUCTION_TYPE_CHECK_QTY,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_STATUS_CHANGED,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_QTY_CHANGED,
            Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
        );
    }

    protected function getRevisePriceRegularInstructionTypes()
    {
        return array(
            ChangeProcessorAbstract::INSTRUCTION_TYPE_PRODUCT_PRICE_DATA_POTENTIALLY_CHANGED,
            Ess_M2ePro_Model_Amazon_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_PRICE_DATA_CHANGED,
            SynchronizationChangeProcessor::INSTRUCTION_TYPE_REVISE_PRICE_ENABLED,
            SynchronizationChangeProcessor::INSTRUCTION_TYPE_REVISE_PRICE_DISABLED,
            Ess_M2ePro_Model_Amazon_Listing_Product::INSTRUCTION_TYPE_CHANNEL_REGULAR_PRICE_CHANGED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Relist_Response::INSTRUCTION_TYPE_CHECK_PRICE_REGULAR,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRICE_CHANGED,
            Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
        );
    }

    protected function getRevisePriceBusinessInstructionTypes()
    {
        return array(
            ChangeProcessorAbstract::INSTRUCTION_TYPE_PRODUCT_PRICE_DATA_POTENTIALLY_CHANGED,
            Ess_M2ePro_Model_Amazon_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_PRICE_DATA_CHANGED,
            SynchronizationChangeProcessor::INSTRUCTION_TYPE_REVISE_PRICE_ENABLED,
            SynchronizationChangeProcessor::INSTRUCTION_TYPE_REVISE_PRICE_DISABLED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Relist_Response::INSTRUCTION_TYPE_CHECK_PRICE_BUSINESS,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRICE_CHANGED,
            Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
        );
    }

    protected function getReviseDetailsInstructionTypes()
    {
        return array(
            SynchronizationChangeProcessor::INSTRUCTION_TYPE_REVISE_DETAILS_ENABLED,
            SynchronizationChangeProcessor::INSTRUCTION_TYPE_REVISE_DETAILS_DISABLED,
            Ess_M2ePro_Model_Amazon_Magento_Product_ChangeProcessor::INSTRUCTION_TYPE_DETAILS_DATA_CHANGED,
            Ess_M2ePro_Model_Amazon_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_DETAILS_DATA_CHANGED,
            Ess_M2ePro_Model_Amazon_Listing_ChangeProcessor::INSTRUCTION_TYPE_CONDITION_DATA_CHANGED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Relist_Response::INSTRUCTION_TYPE_CHECK_DETAILS,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
        );
    }

    protected function getReviseImagesInstructionTypes()
    {
        return array(
            SynchronizationChangeProcessor::INSTRUCTION_TYPE_REVISE_IMAGES_ENABLED,
            SynchronizationChangeProcessor::INSTRUCTION_TYPE_REVISE_IMAGES_DISABLED,
            Ess_M2ePro_Model_Amazon_Magento_Product_ChangeProcessor::INSTRUCTION_TYPE_IMAGES_DATA_CHANGED,
            Ess_M2ePro_Model_Amazon_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_IMAGES_DATA_CHANGED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Relist_Response::INSTRUCTION_TYPE_CHECK_IMAGES,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
        );
    }

    //########################################

    protected function getPropertiesDataFromInputInstructions()
    {
        if (!$this->_input->hasInstructionWithTypes($this->getReviseInstructionTypes())) {
            return array();
        }

        $propertiesData = array();

        if ($this->_input->hasInstructionWithTypes($this->getReviseQtyInstructionTypes())) {
            $propertiesData[] = 'qty';
        }

        if ($this->_input->hasInstructionWithTypes($this->getRevisePriceRegularInstructionTypes())) {
            $propertiesData[] = 'price_regular';
        }

        if ($this->_input->hasInstructionWithTypes($this->getRevisePriceBusinessInstructionTypes())) {
            $propertiesData[] = 'price_business';
        }

        if ($this->_input->hasInstructionWithTypes($this->getReviseDetailsInstructionTypes())) {
            $propertiesData[] = 'details';
        }

        if ($this->_input->hasInstructionWithTypes($this->getReviseImagesInstructionTypes())) {
            $propertiesData[] = 'images';
        }

        return $propertiesData;
    }

    protected function getPropertiesDataFromInputScheduledAction()
    {
        if (!$this->_input->getScheduledAction() || !$this->_input->getScheduledAction()->isActionTypeRevise()) {
            return array();
        }

        $additionalData = $this->_input->getScheduledAction()->getAdditionalData();
        if (empty($additionalData['configurator'])) {
            return array();
        }

        $configurator = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Configurator');
        $configurator->setData($additionalData['configurator']);

        $propertiesData = array();

        if ($configurator->isQtyAllowed()) {
            $propertiesData[] = 'qty';
        }

        if ($configurator->isRegularPriceAllowed()) {
            $propertiesData[] = 'price_regular';
        }

        if ($configurator->isBusinessPriceAllowed()) {
            $propertiesData[] = 'price_business';
        }

        if ($configurator->isDetailsAllowed()) {
            $propertiesData[] = 'details';
        }

        if ($configurator->isImagesAllowed()) {
            $propertiesData[] = 'images';
        }

        return $propertiesData;
    }

    //########################################
}
