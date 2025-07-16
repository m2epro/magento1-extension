<?php

class Ess_M2ePro_Model_Walmart_Connector_Account_GetGrantAccessUrl_Processor
{
    /**
     * @param $backUrl
     * @return Ess_M2ePro_Model_Walmart_Connector_Account_GetGrantAccessUrl_Response
     * @throws Ess_M2ePro_Model_Exception
     */
    public function process($backUrl)
    {
        /** @var Ess_M2ePro_Model_Walmart_Connector_Dispatcher $dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');

        /** @var Ess_M2ePro_Model_Walmart_Connector_Account_GetGrantAccessUrl_Command $command */
        $command = $dispatcher->getConnectorByClass(
            'Ess_M2ePro_Model_Walmart_Connector_Account_GetGrantAccessUrl_Command',
            array(
                Ess_M2ePro_Model_Walmart_Connector_Account_GetGrantAccessUrl_Command::PARAM_KEY_BACK_URL => $backUrl
            )
        );

        $command->process();

        /** @var Ess_M2ePro_Model_Walmart_Connector_Account_GetGrantAccessUrl_Response */
        return $command->getResponseData();
    }
}
