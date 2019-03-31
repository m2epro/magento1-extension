<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processor_Connector_Multiple_Command_VirtualWithoutCall as Connector;

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processor_Connector_Multiple_Dispatcher
    extends Ess_M2ePro_Model_Ebay_Connector_Dispatcher
{
    //####################################

    /**
     * @param Connector[] $connectors
     * @param bool $asynchronous
     */
    public function processMultiple(array $connectors, $asynchronous = false)
    {
        /** @var Ess_M2ePro_Model_Connector_Connection_Multiple $multipleConnection */
        $multipleConnection = Mage::getModel('M2ePro/Connector_Connection_Multiple');
        $multipleConnection->setAsynchronous($asynchronous);

        foreach ($connectors as $key => $connector) {

            /** @var Ess_M2ePro_Model_Connector_Connection_Multiple_RequestContainer $requestContainer */
            $requestContainer = Mage::getModel('M2ePro/Connector_Connection_Multiple_RequestContainer');
            $requestContainer->setRequest($connector->getCommandConnection()->getRequest());
            $requestContainer->setTimeout($connector->getCommandConnection()->getTimeout());

            $multipleConnection->addRequestContainer($key, $requestContainer);
        }

        $multipleConnection->process();

        foreach ($connectors as $key => $connector) {
            $connector->getCommandConnection()->setResponse($multipleConnection->getResponse($key));
            $connector->process();
        }
    }

    //####################################
}