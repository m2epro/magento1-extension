<?php

use Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Command as Command;

class Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Processor
{
    /**
     * @param Ess_M2ePro_Model_Marketplace $marketplace
     * @param int $partNumber
     * @return Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Response
     */
    public function process(Ess_M2ePro_Model_Marketplace $marketplace, $partNumber)
    {
        /** @var Ess_M2ePro_Model_Walmart_Connector_Dispatcher $dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');

        /** @var Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Command $command */
        $command = $dispatcher->getConnectorByClass(
            'Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Command',
            array(
                Command::PARAM_KEY_MARKETPLACE_ID => $marketplace->getNativeId(),
                Command::PARAM_KEY_PART_NUMBER => $partNumber,
            )
        );

        $command->process();

        return $command->getResponseData();
    }
}
