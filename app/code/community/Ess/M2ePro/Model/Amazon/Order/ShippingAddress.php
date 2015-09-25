<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Order_ShippingAddress extends Ess_M2ePro_Model_Order_ShippingAddress
{
    // ########################################

    public function getRawData()
    {
        return array(
            'buyer_name'     => $this->order->getChildObject()->getBuyerName(),
            'email'          => $this->getBuyerEmail(),
            'recipient_name' => $this->getData('recipient_name'),
            'country_id'     => $this->getData('country_code'),
            'region'         => $this->getData('state'),
            'city'           => $this->getData('city'),
            'postcode'       => $this->getPostalCode(),
            'telephone'      => $this->getPhone(),
            'company'        => $this->getData('company'),
            'street'         => array_filter($this->getData('street'))
        );
    }

    public function hasSameBuyerAndRecipient()
    {
        $rawAddressData = $this->order->getShippingAddress()->getRawData();

        $buyerNameParts =  array_map('strtolower', explode(' ', $rawAddressData['buyer_name']));
        $recipientNameParts = array_map('strtolower', explode(' ', $rawAddressData['recipient_name']));

        $buyerNameParts = array_map('trim', $buyerNameParts);
        $recipientNameParts = array_map('trim', $recipientNameParts);

        sort($buyerNameParts);
        sort($recipientNameParts);

        return count(array_diff($buyerNameParts, $recipientNameParts)) == 0;
    }

    private function getBuyerEmail()
    {
        $email = $this->order->getData('buyer_email');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = str_replace(' ', '-', strtolower($this->order->getChildObject()->getBuyerName()));
            $email .= Ess_M2ePro_Model_Magento_Customer::FAKE_EMAIL_POSTFIX;
        }

        return $email;
    }

    private function getPostalCode()
    {
        $postalCode = $this->getData('postal_code');

        if ($postalCode == '') {
            $postalCode = '0000';
        }

        return $postalCode;
    }

    private function getPhone()
    {
        $phone = $this->getData('phone');

        if ($phone == '') {
            $phone = '0000000000';
        }

        return $phone;
    }

    public function isRegionValidationRequired()
    {
        if (!$this->getCountry()->getId() || strtoupper($this->getCountry()->getId()) != 'US') {
            return false;
        }

        $collection = Mage::getResourceModel('directory/region_collection');
        $collection->addCountryFilter($this->getCountry()->getId());

        return $collection->getSize() > 0;
    }

    protected function getState()
    {
        $state = $this->getData('state');

        if (!$this->getCountry()->getId() || strtoupper($this->getCountry()->getId()) != 'US') {
            return $state;
        }

        return preg_replace('/[^ \w]+/', '', $state);
    }

    // ########################################
}