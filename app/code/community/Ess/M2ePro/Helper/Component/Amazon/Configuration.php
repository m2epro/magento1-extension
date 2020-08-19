<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Amazon_Configuration extends Mage_Core_Helper_Abstract
{
    const CONFIG_GROUP = '/amazon/configuration/';

    //########################################

    public function getBusinessMode()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'business_mode'
        );
    }

    public function isEnabledBusinessMode()
    {
        return $this->getBusinessMode() == 1;
    }

    //########################################

    public function setConfigValues(array $values)
    {
        if (isset($values['business_mode'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP, 'business_mode', $values['business_mode']
            );
        }
    }

    //########################################
}
