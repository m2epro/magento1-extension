<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_AccountPickupStore_Synchronize_ProcessingRunner
    extends Ess_M2ePro_Model_Connector_Command_Pending_Processing_Single_Runner
{
    //########################################

    protected function eventBefore()
    {
        parent::eventBefore();

        $params = $this->getParams();

        Mage::getResourceModel('M2ePro/Ebay_Account_PickupStore_State')->markAsInProcessing(
            $params['pickup_store_state_ids']
        );
    }

    protected function eventAfter()
    {
        parent::eventAfter();

        $params = $this->getParams();

        Mage::getResourceModel('M2ePro/Ebay_Account_PickupStore_State')->unmarkAsInProcessing(
            $params['pickup_store_state_ids']
        );
    }

    //########################################
}