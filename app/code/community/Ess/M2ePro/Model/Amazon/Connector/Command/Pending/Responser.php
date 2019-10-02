<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Amazon_Connector_Command_Pending_Responser
    extends Ess_M2ePro_Model_Connector_Command_Pending_Responser
{
    protected $_cachedParamsObjects = array();

    //########################################

    protected function getObjectByParam($model, $idKey)
    {
        if (isset($this->_cachedParamsObjects[$idKey])) {
            return $this->_cachedParamsObjects[$idKey];
        }

        if (!isset($this->_params[$idKey])) {
            return NULL;
        }

        $this->_cachedParamsObjects[$idKey] = Mage::helper('M2ePro/Component_Amazon')
                                                  ->getObject($model, $this->_params[$idKey]);

        return $this->_cachedParamsObjects[$idKey];
    }

    //########################################
}
