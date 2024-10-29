<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract as ChangeProcessorAbstract;
use Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor as SynchronizationChangeProcessor;

abstract class Ess_M2ePro_Model_Ebay_Listing_Product_Instruction_SynchronizationTemplate_Checker_Abstract
    extends Ess_M2ePro_Model_Listing_Product_Instruction_SynchronizationTemplate_Checker_Abstract
{
    //########################################

    protected function getReviseInstructionTypes()
    {
        return array_unique(
            array_merge(
                $this->getReviseQtyInstructionTypes(),
                $this->getRevisePriceInstructionTypes(),
                $this->getReviseTitleInstructionTypes(),
                $this->getReviseSubtitleInstructionTypes(),
                $this->getReviseDescriptionInstructionTypes(),
                $this->getReviseImagesInstructionTypes(),
                $this->getReviseCategoriesInstructionTypes(),
                $this->getRevisePartsInstructionTypes(),
                $this->getReviseShippingInstructionTypes(),
                $this->getRevisePaymentInstructionTypes(),
                $this->getReviseReturnInstructionTypes(),
                $this->getReviseOtherInstructionTypes()
            )
        );
    }

    // ---------------------------------------

    protected function getReviseQtyInstructionTypes()
    {
        return array(
            ChangeProcessorAbstract::INSTRUCTION_TYPE_PRODUCT_DATA_POTENTIALLY_CHANGED,
            ChangeProcessorAbstract::INSTRUCTION_TYPE_PRODUCT_QTY_DATA_POTENTIALLY_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_QTY_ENABLED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_QTY_DISABLED,
            SynchronizationChangeProcessor::INSTRUCTION_TYPE_REVISE_QTY_SETTINGS_CHANGED,
            Ess_M2ePro_Model_Ebay_Listing_Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Relist_Response::INSTRUCTION_TYPE_CHECK_QTY,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_STATUS_CHANGED,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_QTY_CHANGED,
            Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
        );
    }

    protected function getRevisePriceInstructionTypes()
    {
        return array(
            ChangeProcessorAbstract::INSTRUCTION_TYPE_PRODUCT_PRICE_DATA_POTENTIALLY_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_PRICE_DATA_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_PRICE_ENABLED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_PRICE_DISABLED,
            Ess_M2ePro_Model_Ebay_Listing_Product::INSTRUCTION_TYPE_CHANNEL_PRICE_CHANGED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Relist_Response::INSTRUCTION_TYPE_CHECK_PRICE,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRICE_CHANGED,
            Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
        );
    }

    protected function getReviseTitleInstructionTypes()
    {
        return array(
            Ess_M2ePro_Model_Ebay_Magento_Product_ChangeProcessor::INSTRUCTION_TYPE_TITLE_DATA_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_TITLE_DATA_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_TITLE_ENABLED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_TITLE_DISABLED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Relist_Response::INSTRUCTION_TYPE_CHECK_TITLE,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
        );
    }

    protected function getReviseSubtitleInstructionTypes()
    {
        return array(
            Ess_M2ePro_Model_Ebay_Magento_Product_ChangeProcessor::INSTRUCTION_TYPE_SUBTITLE_DATA_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_SUBTITLE_DATA_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_SUBTITLE_ENABLED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_SUBTITLE_DISABLED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Relist_Response::INSTRUCTION_TYPE_CHECK_SUBTITLE,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
        );
    }

    protected function getReviseDescriptionInstructionTypes()
    {
        return array(
            Ess_M2ePro_Model_Ebay_Magento_Product_ChangeProcessor::INSTRUCTION_TYPE_DESCRIPTION_DATA_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_DESCRIPTION_DATA_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_DESCRIPTION_ENABLED,
            SynchronizationChangeProcessor::INSTRUCTION_TYPE_REVISE_DESCRIPTION_DISABLED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Relist_Response::INSTRUCTION_TYPE_CHECK_DESCRIPTION,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
            Ess_M2ePro_Model_Ebay_Template_Description::INSTRUCTION_TYPE_MAGENTO_STATIC_BLOCK_IN_DESCRIPTION_CHANGED
        );
    }

    protected function getReviseImagesInstructionTypes()
    {
        return array(
            Ess_M2ePro_Model_Ebay_Magento_Product_ChangeProcessor::INSTRUCTION_TYPE_IMAGES_DATA_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_IMAGES_DATA_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_IMAGES_ENABLED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_IMAGES_DISABLED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Relist_Response::INSTRUCTION_TYPE_CHECK_IMAGES,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
            Ess_M2ePro_Model_Ebay_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_VARIATION_IMAGES_DATA_CHANGED,
        );
    }

    protected function getReviseVariationImagesInstructionTypes()
    {
        return array(
            Ess_M2ePro_Model_Ebay_Magento_Product_ChangeProcessor::INSTRUCTION_TYPE_IMAGES_DATA_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_IMAGES_DATA_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_IMAGES_ENABLED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_IMAGES_DISABLED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Relist_Response::INSTRUCTION_TYPE_CHECK_IMAGES,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
            Ess_M2ePro_Model_Ebay_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_VARIATION_IMAGES_DATA_CHANGED,
        );
    }

    protected function getReviseCategoriesInstructionTypes()
    {
        return array(
            Ess_M2ePro_Model_Ebay_Magento_Product_ChangeProcessor::INSTRUCTION_TYPE_CATEGORIES_DATA_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_CATEGORIES_DATA_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_CATEGORIES_ENABLED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_CATEGORIES_DISABLED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Relist_Response::INSTRUCTION_TYPE_CHECK_CATEGORIES,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
        );
    }

    protected function getRevisePartsInstructionTypes()
    {
        return array(
            Ess_M2ePro_Model_Ebay_Magento_Product_ChangeProcessor::INSTRUCTION_TYPE_PARTS_DATA_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_PARTS_DATA_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_PARTS_ENABLED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::
            INSTRUCTION_TYPE_REVISE_PARTS_DISABLED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Relist_Response::INSTRUCTION_TYPE_CHECK_PARTS,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
        );
    }

    protected function getReviseShippingInstructionTypes()
    {
        return array(
            Ess_M2ePro_Model_Ebay_Magento_Product_ChangeProcessor::INSTRUCTION_TYPE_SHIPPING_DATA_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_SHIPPING_DATA_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_SHIPPING_ENABLED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_SHIPPING_DISABLED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Relist_Response::INSTRUCTION_TYPE_CHECK_SHIPPING,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
        );
    }

    protected function getRevisePaymentInstructionTypes()
    {
        return array(
            Ess_M2ePro_Model_Ebay_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_PAYMENT_DATA_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_PAYMENT_ENABLED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_PAYMENT_DISABLED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Relist_Response::INSTRUCTION_TYPE_CHECK_PAYMENT,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
        );
    }

    protected function getReviseReturnInstructionTypes()
    {
        return array(
            Ess_M2ePro_Model_Ebay_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_RETURN_DATA_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_RETURN_ENABLED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_RETURN_DISABLED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Relist_Response::INSTRUCTION_TYPE_CHECK_RETURN,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
        );
    }

    protected function getReviseOtherInstructionTypes()
    {
        return array(
            Ess_M2ePro_Model_Ebay_Magento_Product_ChangeProcessor::INSTRUCTION_TYPE_OTHER_DATA_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_OTHER_DATA_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_OTHER_ENABLED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_REVISE_OTHER_DISABLED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Relist_Response::INSTRUCTION_TYPE_CHECK_OTHER,
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

        if ($this->_input->hasInstructionWithTypes($this->getRevisePriceInstructionTypes())) {
            $propertiesData[] = 'price';
        }

        if ($this->_input->hasInstructionWithTypes($this->getReviseTitleInstructionTypes())) {
            $propertiesData[] = 'title';
        }

        if ($this->_input->hasInstructionWithTypes($this->getReviseSubtitleInstructionTypes())) {
            $propertiesData[] = 'subtitle';
        }

        if ($this->_input->hasInstructionWithTypes($this->getReviseDescriptionInstructionTypes())) {
            $propertiesData[] = 'description';
        }

        if ($this->_input->hasInstructionWithTypes($this->getReviseImagesInstructionTypes())) {
            $propertiesData[] = 'images';
        }

        if ($this->_input->hasInstructionWithTypes($this->getReviseCategoriesInstructionTypes())) {
            $propertiesData[] = 'categories';
        }

        if ($this->_input->hasInstructionWithTypes($this->getRevisePartsInstructionTypes())) {
            $propertiesData[] = 'parts';
        }

        if ($this->_input->hasInstructionWithTypes($this->getReviseShippingInstructionTypes())) {
            $propertiesData[] = 'shipping';
        }

        if ($this->_input->hasInstructionWithTypes($this->getRevisePaymentInstructionTypes())) {
            $propertiesData[] = 'payment';
        }

        if ($this->_input->hasInstructionWithTypes($this->getReviseReturnInstructionTypes())) {
            $propertiesData[] = 'return';
        }

        if ($this->_input->hasInstructionWithTypes($this->getReviseOtherInstructionTypes())) {
            $propertiesData[] = 'other';
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

        $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
        $configurator->setData($additionalData['configurator']);

        $propertiesData = array();

        if ($configurator->isQtyAllowed()) {
            $propertiesData[] = 'qty';
        }

        if ($configurator->isPriceAllowed()) {
            $propertiesData[] = 'price';
        }

        if ($configurator->isTitleAllowed()) {
            $propertiesData[] = 'title';
        }

        if ($configurator->isSubtitleAllowed()) {
            $propertiesData[] = 'subtitle';
        }

        if ($configurator->isDescriptionAllowed()) {
            $propertiesData[] = 'description';
        }

        if ($configurator->isImagesAllowed()) {
            $propertiesData[] = 'images';
        }

        if ($configurator->isCategoriesAllowed()) {
            $propertiesData[] = 'categories';
        }

        if ($configurator->isPaymentAllowed()) {
            $propertiesData[] = 'payment';
        }

        if ($configurator->isShippingAllowed()) {
            $propertiesData[] = 'shipping';
        }

        if ($configurator->isReturnAllowed()) {
            $propertiesData[] = 'return';
        }

        if ($configurator->isOtherAllowed()) {
            $propertiesData[] = 'other';
        }

        return $propertiesData;
    }

    //########################################
}
