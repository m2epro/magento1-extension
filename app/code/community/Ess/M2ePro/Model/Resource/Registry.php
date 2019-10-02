<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Registry
    extends Ess_M2ePro_Model_Resource_Abstract
{
    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Registry', 'id');
    }

    //########################################

    public function loadByKey(Ess_M2ePro_Model_Registry $object, $key)
    {
        $this->load($object, $key, 'key');
        if (!$object->getId()) {
            $object->setData('key', $key);
        }

        return $object;
    }

    //########################################
}
