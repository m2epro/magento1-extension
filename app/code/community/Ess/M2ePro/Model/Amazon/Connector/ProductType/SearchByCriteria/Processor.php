<?php

class Ess_M2ePro_Model_Amazon_Connector_ProductType_SearchByCriteria_Processor
{
    /** @var Ess_M2ePro_Model_Amazon_Connector_Dispatcher */
    private $dispatcher;

    public function __construct()
    {
        $this->dispatcher = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
    }

    public function process(Ess_M2ePro_Model_Amazon_Connector_ProductType_SearchByCriteria_Request $request)
    {

        /** @var Ess_M2ePro_Model_Amazon_Connector_ProductType_SearchByCriteria_Command $command */
        $command = $this->dispatcher->getConnectorByClass(
            'Ess_M2ePro_Model_Amazon_Connector_ProductType_SearchByCriteria_Command',
            array(Ess_M2ePro_Model_Amazon_Connector_ProductType_SearchByCriteria_Command::REQUEST_PARAM_KEY => $request)
        );

        $this->dispatcher->process($command);

        return $command->getPreparedResponse();
    }
}