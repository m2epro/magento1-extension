<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Requirements_Manager
{
    const CACHE_KEY = 'is_meet_requirements';

    //########################################

    public function isMeet()
    {
        $isMeetRequirements = Mage::helper('M2ePro/Data_Cache_Permanent')->getValue(self::CACHE_KEY);
        if ($isMeetRequirements !== false) {
            return (bool)$isMeetRequirements;
        }

        foreach ($this->getChecks() as $check) {
            if (!($isMeetRequirements = $check->isMeet())) {
                break;
            }
        }

        Mage::helper('M2ePro/Data_Cache_Permanent')->setValue(
            'is_meet_requirements',(int)$isMeetRequirements, array(), 60*60
        );

        return (bool)$isMeetRequirements;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Requirements_Checks_Abstract[]
     */
    public function getChecks()
    {
        $checks = array(
            Ess_M2ePro_Model_Requirements_Checks_MemoryLimit::NICK,
            Ess_M2ePro_Model_Requirements_Checks_ExecutionTime::NICK,
            Ess_M2ePro_Model_Requirements_Checks_MagentoVersion::NICK,
            Ess_M2ePro_Model_Requirements_Checks_PhpVersion::NICK,
        );

        $objects = array();
        foreach ($checks as $check) {
            $objects[] = Mage::getModel("M2ePro/Requirements_Checks_{$check}");
        }

        return $objects;
    }

    //########################################
}