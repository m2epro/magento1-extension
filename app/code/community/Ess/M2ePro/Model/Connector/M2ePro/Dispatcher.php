<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_M2ePro_Dispatcher
{
    //########################################

    /**
     * @throws Exception
     * @param string $entity
     * @param string $type
     * @param string $name
     * @param array $params
     * @return Ess_M2ePro_Model_Connector_M2ePro_Abstract
     */
    public function getConnector($entity, $type, $name,
                                 array $params = array())
    {
        $entity = uc_words(trim($entity));
        $type   = uc_words(trim($type));
        $name   = uc_words(trim($name));

        $className = 'Ess_M2ePro_Model_Connector_M2ePro';
        $entity != '' && $className .= '_'.$entity;
        $type   != '' && $className .= '_'.$type;
        $name   != '' && $className .= '_'.$name;

        $object = new $className($params);
        return $object;
    }

    //########################################

    /**
     * @param string $entity
     * @param string $type
     * @param string $name
     * @param array $requestData
     * @param string|null $responseDataKey
     * @return mixed
     */
    public function getVirtualConnector($entity, $type, $name,
                                        array $requestData = array(),
                                        $responseDataKey = NULL)
    {
        $params = array();
        $params['__command__'] = array($entity,$type,$name);
        $params['__request_data__'] = $requestData;
        $params['__response_data_key__'] = $responseDataKey;

        return $this->getConnector('virtual','','',$params);
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Connector_M2ePro_Abstract $connector
     * @return mixed
     */
    public function process($connector)
    {
        return $connector->process();
    }

    //########################################
}