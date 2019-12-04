<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract as ProductChangeProcessor;
use Ess_M2ePro_Model_Walmart_Template_Synchronization_ChangeProcessor as SynchronizationChangeProcessor;
use Ess_M2ePro_Model_Walmart_Listing_Product_Action_Configurator as ActionConfigurator;

abstract class Ess_M2ePro_Model_Walmart_Listing_Product_Instruction_SynchronizationTemplate_Checker_Abstract
    extends Ess_M2ePro_Model_Listing_Product_Instruction_SynchronizationTemplate_Checker_Abstract
{
    //########################################

    protected function getReviseInstructionTypes()
    {
        return array_unique(
            array_merge(
                $this->getReviseQtyInstructionTypes(),
                $this->getReviseLagTimeInstructionTypes(),
                $this->getRevisePriceInstructionTypes(),
                $this->getRevisePromotionsInstructionTypes(),
                $this->getReviseDetailsInstructionTypes()
            )
        );
    }

    // ---------------------------------------

    protected function getReviseQtyInstructionTypes()
    {
        return array(
            ProductChangeProcessor::INSTRUCTION_TYPE_PRODUCT_QTY_DATA_POTENTIALLY_CHANGED,
            Ess_M2ePro_Model_Walmart_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_QTY_DATA_CHANGED,

            SynchronizationChangeProcessor::INSTRUCTION_TYPE_REVISE_QTY_ENABLED,
            SynchronizationChangeProcessor::INSTRUCTION_TYPE_REVISE_QTY_DISABLED,
            SynchronizationChangeProcessor::INSTRUCTION_TYPE_REVISE_QTY_SETTINGS_CHANGED,

            Ess_M2ePro_Model_Walmart_Listing_Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,

            Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Relist_Response::INSTRUCTION_TYPE_CHECK_QTY,
            Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_List_Response::INSTRUCTION_TYPE_CHECK_QTY,

            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_STATUS_CHANGED,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_QTY_CHANGED,
            ProductChangeProcessor::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_ProcessReviseTotal::INSTRUCTION_TYPE,
        );
    }

    protected function getReviseLagTimeInstructionTypes()
    {
        return array(
            Ess_M2ePro_Model_Walmart_Magento_Product_ChangeProcessor::INSTRUCTION_TYPE_LAG_TIME_DATA_CHANGED,
            Ess_M2ePro_Model_Walmart_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_LAG_TIME_DATA_CHANGED,

            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,

            Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Relist_Response::INSTRUCTION_TYPE_CHECK_LAG_TIME,
            Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_List_Response::INSTRUCTION_TYPE_CHECK_LAG_TIME,

            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            ProductChangeProcessor::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_ProcessReviseTotal::INSTRUCTION_TYPE,
        );
    }

    protected function getRevisePriceInstructionTypes()
    {
        return array(
            ProductChangeProcessor::INSTRUCTION_TYPE_PRODUCT_PRICE_DATA_POTENTIALLY_CHANGED,
            Ess_M2ePro_Model_Walmart_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_PRICE_DATA_CHANGED,

            SynchronizationChangeProcessor::INSTRUCTION_TYPE_REVISE_PRICE_ENABLED,
            SynchronizationChangeProcessor::INSTRUCTION_TYPE_REVISE_PRICE_DISABLED,
            SynchronizationChangeProcessor::INSTRUCTION_TYPE_REVISE_PRICE_SETTINGS_CHANGED,

            Ess_M2ePro_Model_Walmart_Listing_Product::INSTRUCTION_TYPE_CHANNEL_PRICE_CHANGED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,

            Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Relist_Response::INSTRUCTION_TYPE_CHECK_PRICE,
            Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_List_Response::INSTRUCTION_TYPE_CHECK_PRICE,

            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRICE_CHANGED,
            ProductChangeProcessor::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_ProcessReviseTotal::INSTRUCTION_TYPE,
        );
    }

    protected function getRevisePromotionsInstructionTypes()
    {
        return array(
            Ess_M2ePro_Model_Walmart_Magento_Product_ChangeProcessor::INSTRUCTION_TYPE_PROMOTIONS_DATA_CHANGED,
            Ess_M2ePro_Model_Walmart_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_PROMOTIONS_DATA_CHANGED,

            SynchronizationChangeProcessor::INSTRUCTION_TYPE_REVISE_PROMOTIONS_ENABLED,
            SynchronizationChangeProcessor::INSTRUCTION_TYPE_REVISE_PROMOTIONS_DISABLED,

            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,

            Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Relist_Response::INSTRUCTION_TYPE_CHECK_PROMOTIONS,
            Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_List_Response::INSTRUCTION_TYPE_CHECK_PROMOTIONS,

            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            ProductChangeProcessor::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_ProcessReviseTotal::INSTRUCTION_TYPE,
        );
    }

    protected function getReviseDetailsInstructionTypes()
    {
        return array(
            Ess_M2ePro_Model_Walmart_Magento_Product_ChangeProcessor::INSTRUCTION_TYPE_DETAILS_DATA_CHANGED,
            Ess_M2ePro_Model_Walmart_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_DETAILS_DATA_CHANGED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Relist_Response::INSTRUCTION_TYPE_CHECK_DETAILS,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            ProductChangeProcessor::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_ProcessReviseTotal::INSTRUCTION_TYPE,
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

        if ($this->_input->hasInstructionWithTypes($this->getReviseLagTimeInstructionTypes())) {
            $propertiesData[] = 'lag_time';
        }

        if ($this->_input->hasInstructionWithTypes($this->getRevisePriceInstructionTypes())) {
            $propertiesData[] = 'price';
        }

        if ($this->_input->hasInstructionWithTypes($this->getRevisePromotionsInstructionTypes())) {
            $propertiesData[] = 'promotions';
        }

        if ($this->_input->hasInstructionWithTypes($this->getReviseDetailsInstructionTypes())) {
            $propertiesData[] = 'details';
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

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_Configurator $configurator */
        $configurator = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Configurator');
        $configurator->setData($additionalData['configurator']);

        $propertiesData = array();

        if ($configurator->isQtyAllowed()) {
            $propertiesData[] = 'qty';
        }

        if ($configurator->isLagTimeAllowed()) {
            $propertiesData[] = 'lag_time';
        }

        if ($configurator->isPriceAllowed()) {
            $propertiesData[] = 'price';
        }

        if ($configurator->isPromotionsAllowed()) {
            $propertiesData[] = 'promotions';
        }

        if ($configurator->isDetailsAllowed()) {
            $propertiesData[] = 'details';
        }

        return $propertiesData;
    }

    //########################################

    protected function checkUpdatePriceOrPromotionsFeedsLock(
        ActionConfigurator $configurator,
        array &$tags,
        $action
    ){
        if (count($configurator->getAllowedDataTypes()) !== 1) {
            return;
        }

        if (!$configurator->isPriceAllowed() && !$configurator->isPromotionsAllowed()) {
            return;
        }

        if (!$this->isLockedForUpdatePriceOrPromotions()) {
            return;
        }

        if ($configurator->isPriceAllowed()) {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData(
                'Item Price cannot yet be submitted. Walmart allows updating the Price information no sooner than
                24 hours after the relevant product is listed on their website.',
                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_WARNING
            );

            $configurator->disallowPrice();
            unset($tags['price']);
        } else {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData(
                'Item Promotion Price cannot yet be submitted. Walmart allows updating the Promotion Price
                information no sooner than 24 hours after the relevant product is listed on their website.',
                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_WARNING
            );

            $configurator->disallowPromotions();
            unset($tags['promotions']);
        }

        $logger = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Logger');
        $logger->setAction($action);
        $logger->setActionId(Mage::getResourceModel('M2ePro/Listing_Log')->getNextActionId());
        $logger->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);
        $logger->logListingProductMessage($this->_input->getListingProduct(), $message);
    }

    protected function isLockedForUpdatePriceOrPromotions()
    {
        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $this->_input->getListingProduct()->getChildObject();

        if ($walmartListingProduct->getListDate() === null) {
            return false;
        }

        try {
            $borderDate = new DateTime($walmartListingProduct->getListDate(), new DateTimeZone('UTC'));
            $borderDate->modify('+24 hours');
        } catch (\Exception $exception) {
            return false;
        }

        if ($borderDate < new DateTime('now', new DateTimeZone('UTC'))) {
            return false;
        }

        return true;
    }

    //########################################
}
