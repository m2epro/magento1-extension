<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processor_Connector_Multiple_Command_VirtualWithoutCall
    extends Ess_M2ePro_Model_Connector_Command_RealTime_Virtual
{
    // ########################################

    public function process()
    {
        if (is_null($this->getConnection()->getResponse())) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                'This object must be processed in Ess_M2ePro_Model_Connector_Connection_Multiple.'
            );
        }

        if (!$this->validateResponse()) {
            throw new Ess_M2ePro_Model_Exception('Validation Failed. The Server response data is not valid.');
        }

        $this->prepareResponseData();
    }

    // ########################################

    public function getCommandConnection()
    {
        return $this->getConnection();
    }

    // ########################################
}