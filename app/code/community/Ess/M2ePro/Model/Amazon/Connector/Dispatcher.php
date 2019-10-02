<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Connector_Dispatcher
{
    //####################################

    public function getConnector($entity, $type, $name, array $params = array(), $account = NULL)
    {
        $className = 'Ess_M2ePro_Model_Amazon_Connector';

        $entity = uc_words(trim($entity));
        $type   = uc_words(trim($type));
        $name   = uc_words(trim($name));

        $entity != '' && $className .= '_'.$entity;
        $type   != '' && $className .= '_'.$type;
        $name   != '' && $className .= '_'.$name;

        if (is_int($account) || is_string($account)) {
            $account = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Account', (int)$account);
        }

        /** @var Ess_M2ePro_Model_Connector_Command_Abstract $connectorObject */
        $connectorObject = new $className($params, $account);
        $connectorObject->setProtocol($this->getProtocol());

        return $connectorObject;
    }

    public function getCustomConnector($modelName, array $params = array(), $account = NULL)
    {
        if (is_int($account) || is_string($account)) {
            $account = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Account', (int)$account);
        }

        $className = 'Ess_M2ePro_Model_'.$modelName;

        /** @var Ess_M2ePro_Model_Connector_Command_Abstract $connectorObject */
        $connectorObject = new $className($params, $account);
        $connectorObject->setProtocol($this->getProtocol());

        return $connectorObject;
    }

    /**
     * @param string $entity
     * @param string $type
     * @param string $name
     * @param array $requestData
     * @param string|null $responseDataKey
     * @param null|int|Ess_M2ePro_Model_Account $account
     * @return Ess_M2ePro_Model_Connector_Command_RealTime_Virtual
     */
    public function getVirtualConnector(
        $entity,
        $type,
        $name,
        array $requestData = array(),
        $responseDataKey = null,
        $account = null
    ) {
        return $this->getCustomVirtualConnector(
            'Connector_Command_RealTime_Virtual',
            $entity, $type, $name,
            $requestData, $responseDataKey, $account
        );
    }

    public function getCustomVirtualConnector(
        $modelName,
        $entity,
        $type,
        $name,
        array $requestData = array(),
        $responseDataKey = null,
        $account = null
    ) {
        $virtualConnector = Mage::getModel('M2ePro/' . $modelName);
        $virtualConnector->setProtocol($this->getProtocol());
        $virtualConnector->setCommand(array($entity, $type, $name));
        $virtualConnector->setResponseDataKey($responseDataKey);

        if (is_int($account) || is_string($account)) {
            $account = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Account', (int)$account);
        }

        if ($account instanceof Ess_M2ePro_Model_Account) {
            $requestData['account'] = $account->getChildObject()->getServerHash();
        }

        $virtualConnector->setRequestData($requestData);

        return $virtualConnector;
    }

    //####################################

    public function process(Ess_M2ePro_Model_Connector_Command_Abstract $connector)
    {
        $connector->process();
    }

    //####################################

    protected function getProtocol()
    {
        return Mage::getModel('M2ePro/Amazon_Connector_Protocol');
    }

    //####################################
}
