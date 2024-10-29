<?php

use Ess_M2ePro_Model_Walmart_Connector_ProductType_GetInfo_Command as Command;

class Ess_M2ePro_Model_Walmart_Connector_ProductType_GetInfo_Processor
{
    /**
     * @param string $productTypeNick
     * @return Ess_M2ePro_Model_Walmart_Connector_ProductType_GetInfo_Response
     */
    public function process($productTypeNick, Ess_M2ePro_Model_Marketplace $marketplace)
    {
        /** @var Ess_M2ePro_Model_Walmart_Connector_Dispatcher $dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');

        /** @var Command $command */
        $command = $dispatcher->getConnectorByClass(
            'Ess_M2ePro_Model_Walmart_Connector_ProductType_GetInfo_Command',
            array(
                Command::PARAM_KEY_MARKETPLACE_ID => $marketplace->getNativeId(),
                Command::PARAM_KEY_PRODUCT_TYPE_NICK => $productTypeNick,
            )
        );

        $command->process();

        return $command->getResponseData();
    }
}