<?php

/**
 * @method Ess_M2ePro_Model_Marketplace getParentObject()
 */
class Ess_M2ePro_Model_Walmart_Marketplace extends Ess_M2ePro_Model_Component_Child_Walmart_Abstract
{
    const CODE_CANADA = 'CA';
    const CODE_UNITED_STATES = 'US';

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Walmart_Marketplace');
    }

    public function getCurrency()
    {
        return $this->getData('default_currency');
    }

    public function getDeveloperKey()
    {
        return $this->getData('developer_key');
    }

    public function getDefaultCurrency()
    {
        return $this->getData('default_currency');
    }

    /**
     * @return bool
     */
    public function isUnitedStates()
    {
        return $this->getParentObject()->getCode() === self::CODE_UNITED_STATES;
    }

    /**
     * @return bool
     */
    public function isCanada()
    {
        return $this->getParentObject()->getCode() === self::CODE_CANADA;
    }

    /**
     * @return bool
     */
    public function isSupportedProductType()
    {
        return $this->isUnitedStates();
    }

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
}
