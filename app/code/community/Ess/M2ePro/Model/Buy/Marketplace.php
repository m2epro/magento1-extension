<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Marketplace extends Ess_M2ePro_Model_Component_Child_Buy_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Buy_Marketplace');
    }

    //########################################

    /**
     * @param bool $asObjects
     * @param array $filters
     * @return array|Ess_M2ePro_Model_Abstract[]
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getBuyItems($asObjects = false, array $filters = array())
    {
        return $this->getRelatedSimpleItems('Buy_Item','marketplace_id',$asObjects,$filters);
    }

    //########################################

    public function getCurrency()
    {
        return Ess_M2ePro_Helper_Component_Buy::DEFAULT_CURRENCY;
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('marketplace');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('marketplace');
        return parent::delete();
    }

    //########################################
}
