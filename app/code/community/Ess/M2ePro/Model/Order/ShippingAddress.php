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
    protected $order;

    /** @var Mage_Directory_Model_Country */
    protected $country;

    /** @var Mage_Directory_Model_Region */
    protected $region;

    //########################################

    abstract public function getRawData();

    public function __construct(Ess_M2ePro_Model_Order $order)
    {
        $this->order = $order;
    }

    public function getCountry()
    {
        if (is_null($this->country)) {
            $this->country = Mage::getModel('directory/country');

            try {
                $this->country->loadByCode($this->getData('country_code'));
            } catch (Exception $e) {}
        }

        return $this->country;
    }

    public function getRegion()
    {
        if (!$this->getCountry()->getId()) {
            return NULL;
        }

        if (is_null($this->region)) {
            $countryRegions = $this->getCountry()->getRegionCollection();
            $countryRegions->getSelect()->where('code = ? OR default_name = ?', $this->getState());

            $this->region = $countryRegions->getFirstItem();

            if ($this->isRegionValidationRequired() && !$this->region->getId()) {
                throw new Ess_M2ePro_Model_Exception(
                    sprintf('State/Region "%s" in the shipping address is invalid.', $this->getState())
                );
            }
        }

        return $this->region;
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

        if (is_null($region) || is_null($region->getId())) {
            return 1;
        }

        return $region->getId();
    }

    public function getRegionCode()
    {
        $region = $this->getRegion();

        if (is_null($region) || is_null($region->getId())) {
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