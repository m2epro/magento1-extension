<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Server extends Mage_Core_Helper_Abstract
{
    public function getEndpoint()
    {
        return rtrim(Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/server/', 'host'));
    }

    public function getApplicationKey()
    {
        return (string)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/server/', 'application_key');
    }
}
