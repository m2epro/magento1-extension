<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * Provides simple API to work with address information from the order.
 */
abstract class Ess_M2ePro_Model_Order_ShippingAddress extends Varien_Object
{
    /** @var Ess_M2ePro_Model_Order */
    protected $_order;

    /** @var Mage_Directory_Model_Country */
    protected $_country;

    /** @var Mage_Directory_Model_Region */
    protected $_region;

    //########################################

    abstract public function getRawData();

    public function __construct(Ess_M2ePro_Model_Order $order)
    {
        $this->_order = $order;
    }

    public function getCountry()
    {
        if ($this->_country === null) {
            $this->_country = Mage::getModel('directory/country');

            try {
                $this->_country->loadByCode($this->getData('country_code'));
            } catch (Exception $e) {
            }
        }

        return $this->_country;
    }

    public function getRegion()
    {
        if (!$this->getCountry()->getId()) {
            return NULL;
        }

        if ($this->_region === null) {
            $countryRegions = $this->getCountry()->getRegionCollection();
            $countryRegions->getSelect()->where('code = ? OR default_name = ?', $this->getState());

            $this->_region = $countryRegions->getFirstItem();

            if ($this->isRegionValidationRequired() && !$this->_region->getId()) {
                throw new Ess_M2ePro_Model_Exception(
                    sprintf('State/Region "%s" in the shipping address is invalid.', $this->getState())
                );
            }
        }

        return $this->_region;
    }

    /**
     * @return bool
     */
    public function isRegionValidationRequired()
    {
        return false;
    }

    public function getCountryName()
    {
        if (!$this->getCountry()->getId()) {
            return $this->getData('country_code');
        }

        return $this->getCountry()->getName();
    }

    public function getRegionId()
    {
        $region = $this->getRegion();

        if ($region === null || $region->getId() === null) {
            return 1;
        }

        return $region->getId();
    }

    public function getRegionCode()
    {
        $region = $this->getRegion();

        if ($region === null || $region->getId() === null) {
            return '';
        }

        return $region->getCode();
    }

    protected function getState()
    {
        return $this->getData('state');
    }

    //########################################
}