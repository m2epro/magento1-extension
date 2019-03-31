<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Translation_Connector_Dispatcher
{
    //####################################

    public function getConnector($entity, $type, $name, array $params = array(), $account = NULL)
    {
        $className = 'Ess_M2ePro_Model_Translation_Connector';

        $entity = uc_words(trim($entity));
        $type   = uc_words(trim($type));
        $name   = uc_words(trim($name));

        $entity != '' && $className .= '_'.$entity;
        $type   != '' && $className .= '_'.$type;
        $name   != '' && $className .= '_'.$name;

        if (is_int($account) || is_string($account)) {
            $account = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Account',(int)$account);
        }

        /** @var Ess_M2ePro_Model_Connector_Command_Abstract $connectorObject */
        $connectorObject = new $className($params, $account);
        $connectorObject->setProtocol($this->getProtocol());

        return $connectorObject;
    }

    public function getCustomConnector($modelName, array $params = array(), $account = NULL)
    {
        if (is_int($account) || is_string($account)) {
            $account = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Account',(int)$account);
        }

        $className = 'Ess_M2ePro_Model_'.$modelName;

        /** @var Ess_M2ePro_Model_Connector_Command_Abstract $connectorObject */
        $connectorObject = new $className($params,  $account);
        $connectorObject->setProtocol($this->getProtocol());

        return $connectorObject;
    }

    public function getVirtualConnector($entity, $type, $name,
                                        array $requestData = array(), $responseDataKey = NULL,
                                        $account = NULL)
    {
        $virtualConnector = Mage::getModel('M2ePro/Connector_Command_RealTime_Virtual');
        $virtualConnector->setProtocol($this->getProtocol());
        $virtualConnector->setCommand(array($entity, $type, $name));
        $virtualConnector->setResponseDataKey($responseDataKey);

        if (is_int($account) || is_string($account)) {
            $account = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Account', (int)$account);
        }

        if ($account instanceof Ess_M2ePro_Model_Account) {
            $requestData['account'] = $account->getChildObject()->getTranslationHash();
        }

        $virtualConnector->setRequestData($requestData);

        return $virtualConnector;
    }

    //####################################

    public function process(Ess_M2ePro_Model_Connector_Command_Abstract $connector)
    {
        return $connector->process();
    }

    //####################################

    private function getProtocol()
    {
        return Mage::getModel('M2ePro/Translation_Connector_Protocol');
    }

    //####################################
}