<?php

use Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetInfoWithDetails_Command as Command;

class Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetInfoWithDetails_Processor
{
    /**
     * @return Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetInfoWithDetails_Response
     */
    public function process(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        /** @var Ess_M2ePro_Model_Walmart_Connector_Dispatcher $dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');

        /** @var Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetInfoWithDetails_Command $command */
        $command = $dispatcher->getConnectorByClass(
            'Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetInfoWithDetails_Command',
            array(
                Command::PARAM_KEY_MARKETPLACE_ID => $marketplace->getNativeId(),
            )
        );

        $command->process();

        return $command->getResponseData();
    }
}