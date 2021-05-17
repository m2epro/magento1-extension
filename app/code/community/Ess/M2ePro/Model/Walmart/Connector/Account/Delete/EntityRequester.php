<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Connector_Account_Delete_EntityRequester
    extends Ess_M2ePro_Model_Walmart_Connector_Command_RealTime
{
    //########################################

    /**
     * @return array
     */
    public function getRequestData()
    {
        return array();
    }

    /**
     * @return array
     */
    protected function getCommand()
    {
        return array('account','delete','entity');
    }

    //########################################
}
