<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Relist_Response getResponseObject()
 */
class Ess_M2ePro_Model_Walmart_Connector_Product_Relist_Responser
    extends Ess_M2ePro_Model_Walmart_Connector_Product_Responser
{
    // ########################################

    protected function getSuccessfulMessage()
    {
        return 'Item was successfully Relisted';
    }

    // ########################################

    public function eventAfterExecuting()
    {
        parent::eventAfterExecuting();

        $additionalData = $this->_listingProduct->getAdditionalData();
        if (empty($additionalData['skipped_action_configurator_data'])) {
            return;
        }

        $configurator = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Configurator');
        $configurator->setData($additionalData['skipped_action_configurator_data']);

        $scheduledActionManager = Mage::getModel('M2ePro/Listing_Product_ScheduledAction_Manager');
        $scheduledActionManager->addReviseAction(
            $this->_listingProduct, $configurator, false, $this->_params['params']
        );

        unset($additionalData['skipped_action_configurator_data']);
        $this->_listingProduct->setSettings('additional_data', $additionalData)->save();
    }

    // ########################################
}
