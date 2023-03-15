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

    abstract public function getRawData();

    abstract protected function isRegionOverrideRequired();

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
            return null;
        }

        if ($this->_region === null) {
            $countryRegions = $this->getCountry()->getRegionCollection();
            $countryRegions->getSelect()->where('code = ? OR default_name = ?', $this->getState());
            $this->_region = $countryRegions->getFirstItem();
        }

        $isRequired = Mage::helper('directory')->isRegionRequired($this->getCountry()->getId());
        if ($isRequired && !$this->_region->getId()) {
            if (!$this->isRegionOverrideRequired()) {
                throw new Ess_M2ePro_Model_Exception(
                    sprintf('Invalid Region/State value "%s" in the Shipping Address.', $this->getState())
                );
            }

            $countryRegions = $this->getCountry()->getRegionCollection();
            $this->_region = $countryRegions->getFirstItem();

            $msg = 'Invalid Region/State value: "%s" in the Shipping Address is overridden by "%s".';
            $this->_order->addInfoLog(sprintf($msg, $this->getState(), $this->_region->getDefaultName()), array(), array(), true);
        }

        return $this->_region;
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

    /**
     * @return bool
     */
    public function hasSameBuyerAndRecipient()
    {
        $rawAddressData = $this->_order->getShippingAddress()->getRawData();

        $buyerNameParts = array_map('strtolower', explode(' ', $rawAddressData['buyer_name']));
        $recipientNameParts = array_map('strtolower', explode(' ', $rawAddressData['recipient_name']));

        $buyerNameParts = array_map('trim', $buyerNameParts);
        $recipientNameParts = array_map('trim', $recipientNameParts);

        sort($buyerNameParts);
        sort($recipientNameParts);

        $diff = array_diff($buyerNameParts, $recipientNameParts);
        return empty($diff);
    }

    protected function getState()
    {
        return $this->getData('state');
    }
}