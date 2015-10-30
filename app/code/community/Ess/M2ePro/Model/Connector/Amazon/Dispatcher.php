<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Amazon_Dispatcher
{
    //########################################

    /**
     * @throws Exception
     * @param string $entity
     * @param string $type
     * @param string $name
     * @param array $params
     * @param null|int|Ess_M2ePro_Model_Account $account
     * @param null|string $ormPrefixToConnector
     * @return Ess_M2ePro_Model_Connector_Amazon_Requester|Ess_M2ePro_Model_Connector_Amazon_Abstract
     */
    public function getConnector($entity, $type, $name,
                                 array $params = array(),
                                 $account = NULL,
                                 $ormPrefixToConnector = NULL)
    {
        $className = empty($ormPrefixToConnector) ? 'Ess_M2ePro_Model_Connector_Amazon' : $ormPrefixToConnector;

        $entity = uc_words(trim($entity));
        $type   = uc_words(trim($type));
        $name   = uc_words(trim($name));

        $entity != '' && $className .= '_'.$entity;
        $type   != '' && $className .= '_'.$type;
        $name   != '' && $className .= '_'.$name;

        if (is_int($account) || is_string($account)) {
            $account = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Account',(int)$account);
        }

        $object = new $className($params, $account);
        return $object;
    }

    /**
     * @param string $entity
     * @param string $type
     * @param string $name
     * @param array $requestData
     * @param string|null $responseDataKey
     * @param null|int|Ess_M2ePro_Model_Account $account
     * @param array|null $requestInfo
     * @return Ess_M2ePro_Model_Connector_Amazon_Virtual
     */
    public function getVirtualConnector($entity, $type, $name,
                                        array $requestData = array(),
                                        $responseDataKey = NULL,
                                        $account = NULL,
                                        $requestInfo = NULL)
    {
        $params = array();
        $params['__command__'] = array($entity,$type,$name);
        $params['__request_info__'] = $requestInfo;
        $params['__request_data__'] = $requestData;
        $params['__response_data_key__'] = $responseDataKey;

        return $this->getConnector('virtual', '', '', $params, $account);
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Connector_Amazon_Requester|Ess_M2ePro_Model_Connector_Amazon_Abstract $connector
     * @return mixed
     */
    public function process($connector)
    {
        return $connector->process();
    }

    //########################################
}