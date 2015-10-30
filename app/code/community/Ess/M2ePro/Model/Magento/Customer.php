<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Customer extends Mage_Core_Model_Abstract
{
    const FAKE_EMAIL_POSTFIX = '@dummy.email';

    /** @var $customer Mage_Customer_Model_Customer */
    private $customer = NULL;

    //########################################

    public function getCustomer()
    {
        return $this->customer;
    }

    //########################################

    public function buildCustomer()
    {
        $password = Mage::helper('core')->getRandomString(6);

        $this->customer = Mage::getModel('customer/customer')
            ->setData('firstname', $this->getData('customer_firstname'))
            ->setData('lastname', $this->getData('customer_lastname'))
            ->setData('website_id', $this->getData('website_id'))
            ->setData('group_id', $this->getData('group_id'))
            ->setData('email', $this->getData('email'))
            ->setData('confirmation', $password);
        $this->customer->setPassword($password);
        $this->customer->save();

        $this->customer->setOrigData();

        // Add customer address
        // do not replace setCustomerId with setData('customer_id', ..)
        $customerAddress = Mage::getModel('customer/address')
            ->setData('firstname', $this->getData('firstname'))
            ->setData('lastname', $this->getData('lastname'))
            ->setData('country_id', $this->getData('country_id'))
            ->setData('region', $this->getData('region'))
            ->setData('region_id', $this->getData('region_id'))
            ->setData('city', $this->getData('city'))
            ->setData('postcode', $this->getData('postcode'))
            ->setData('telephone', $this->getData('telephone'))
            ->setData('street', $this->getData('street'))
            ->setCustomerId($this->customer->getId())
            ->setIsDefaultBilling(true)
            ->setIsDefaultShipping(true);

        $customerAddress->implodeStreetAddress();
        $customerAddress->save();
        // ---------------------------------------
    }

    //########################################
}