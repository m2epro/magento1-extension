<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Ebay_Dispatcher
{
    //########################################

    /**
     * @throws Exception
     * @param string $entity
     * @param string $type
     * @param string $name
     * @param array $params
     * @param null|int|Ess_M2ePro_Model_Marketplace $marketplace
     * @param null|int|Ess_M2ePro_Model_Account $account
     * @param null|int $mode
     * @param null|string $ormPrefixToConnector
     * @return Ess_M2ePro_Model_Connector_Ebay_Abstract
     */
    public function getConnector($entity, $type, $name,
                                 array $params = array(),
                                 $marketplace = NULL,
                                 $account = NULL,
                                 $mode = NULL,
                                 $ormPrefixToConnector = NULL)
    {
        $className = empty($ormPrefixToConnector) ? 'Ess_M2ePro_Model_Connector_Ebay' : $ormPrefixToConnector;

        $entity = uc_words(trim($entity));
        $type   = uc_words(trim($type));
        $name   = uc_words(trim($name));

        $entity != '' && $className .= '_'.$entity;
        $type   != '' && $className .= '_'.$type;
        $name   != '' && $className .= '_'.$name;

        if (is_int($marketplace) || is_string($marketplace)) {
            $marketplace = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Marketplace',(int)$marketplace);
        }

        if (is_int($account) || is_string($account)) {
            $account = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Account',(int)$account);
        }

        $object = new $className($params, $marketplace, $account, $mode);
        return $object;
    }

    /**
     * @param string $entity
     * @param string $type
     * @param string $name
     * @param array $requestData
     * @param string|null $responseDataKey
     * @param null|int|Ess_M2ePro_Model_Marketplace $marketplace
     * @param null|int|Ess_M2ePro_Model_Account $account
     * @param null|int $mode
     * @param array|null $requestInfo
     * @return Ess_M2ePro_Model_Connector_Ebay_Virtual
     */
    public function getVirtualConnector($entity, $type, $name,
                                        array $requestData = array(),
                                        $responseDataKey = NULL,
                                        $marketplace = NULL,
                                        $account = NULL,
                                        $mode = NULL,
                                        $requestInfo = NULL)
    {
        $params = array();
        $params['__command__'] = array($entity,$type,$name);
        $params['__request_info__'] = $requestInfo;
        $params['__request_data__'] = $requestData;
        $params['__response_data_key__'] = $responseDataKey;

        return $this->getConnector('virtual','','',$params,$marketplace,$account,$mode);
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Connector_Ebay_Requester|Ess_M2ePro_Model_Connector_Ebay_Abstract $connector
     * @return mixed
     */
    public function process($connector)
    {
        return $connector->process();
    }

    //########################################
}