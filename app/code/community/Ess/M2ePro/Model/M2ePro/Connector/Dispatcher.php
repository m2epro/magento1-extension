<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_M2ePro_Connector_Dispatcher
{
    //####################################

    public function getConnector($entity, $type, $name, array $params = array())
    {
        $className = 'Ess_M2ePro_Model_M2ePro_Connector';

        $entity = uc_words(trim($entity));
        $type   = uc_words(trim($type));
        $name   = uc_words(trim($name));

        $entity != '' && $className .= '_'.$entity;
        $type   != '' && $className .= '_'.$type;
        $name   != '' && $className .= '_'.$name;

        /** @var Ess_M2ePro_Model_Connector_Command_Abstract $connectorObject */
        $connectorObject = new $className($params);
        $connectorObject->setProtocol($this->getProtocol());

        return $connectorObject;
    }

    /**
     * @param string $entity
     * @param string $type
     * @param string $name
     * @param array $requestData
     * @param string|null $responseDataKey
     * @return Ess_M2ePro_Model_Connector_Command_RealTime_Virtual
     */
    public function getVirtualConnector($entity, $type, $name,
                                        array $requestData = array(),
                                        $responseDataKey = NULL)
    {
        $virtualConnector = Mage::getModel('M2ePro/Connector_Command_RealTime_Virtual');
        $virtualConnector->setProtocol($this->getProtocol());
        $virtualConnector->setCommand(array($entity, $type, $name));
        $virtualConnector->setResponseDataKey($responseDataKey);

        $virtualConnector->setRequestData($requestData);

        return $virtualConnector;
    }

    //####################################

    public function process(Ess_M2ePro_Model_Connector_Command_Abstract $connector)
    {
        $connector->process();
    }

    //####################################

    private function getProtocol()
    {
        return Mage::getModel('M2ePro/M2ePro_Connector_Protocol');
    }

    //####################################
}