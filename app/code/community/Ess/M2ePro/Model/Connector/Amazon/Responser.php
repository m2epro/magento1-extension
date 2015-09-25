<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Amazon_Responser extends Ess_M2ePro_Model_Connector_Responser
{
    private $cachedParamsObjects = array();

    // ########################################

    protected function getObjectByParam($model, $idKey)
    {
        if (isset($this->cachedParamsObjects[$idKey])) {
            return $this->cachedParamsObjects[$idKey];
        }

        if (!isset($this->params[$idKey])) {
            return NULL;
        }

        $this->cachedParamsObjects[$idKey] = Mage::helper('M2ePro/Component_Amazon')
                    ->getObject($model,$this->params[$idKey]);

        return $this->cachedParamsObjects[$idKey];
    }

    // ########################################
}