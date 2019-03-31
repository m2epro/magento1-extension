<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Product_ProcessActions extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'walmart/listing/product/process_actions';

    //####################################

    protected function performActions()
    {
        $actionsProcessor = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Processor');
        $actionsProcessor->process();
    }

    //####################################
}