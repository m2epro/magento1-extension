<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Other_Channel_SynchronizeData_Blocked_Requester
    extends Ess_M2ePro_Model_Amazon_Connector_Inventory_Get_Blocked_ItemsRequester
{
    //########################################

    protected function getProcessingRunnerModelName()
    {
        return 'Cron_Task_Amazon_Listing_Other_Channel_SynchronizeData_Blocked_ProcessingRunner';
    }

    //########################################
}
