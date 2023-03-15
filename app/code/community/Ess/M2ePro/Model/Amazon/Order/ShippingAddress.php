<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Order_ShippingAddress extends Ess_M2ePro_Model_Order_ShippingAddress
{
    //########################################

    /**
     * @return array
     */
    public function getRawData()
    {
        return array(
            'buyer_name'     => $this->_order->getChildObject()->getBuyerName(),
            'email'          => $this->getBuyerEmail(),
            'recipient_name' => $this->getData('recipient_name'),
            'country_id'     => $this->getData('country_code'),
            'region'         => $this->getData('state'),
            'city'           => $this->getData('city') ? $this->getData('city') : $this->getCountryName(),
            'postcode'       => $this->getPostalCode(),
            'telephone'      => $this->getPhone(),
            'company'        => $this->getData('company'),
            'street'         => array_filter($this->getData('street'))
        );
    }

    protected function getBuyerEmail()
    {
        $email = $this->_order->getData('buyer_email');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = str_replace(' ', '-', strtolower($this->_order->getChildObject()->getBuyerName()));
            $email = mb_convert_encoding($email, "ASCII", 'UTF-8');
            $email .= Ess_M2ePro_Model_Magento_Customer::FAKE_EMAIL_POSTFIX;
        }

        return $email;
    }

    protected function getPostalCode()
    {
        $postalCode = $this->getData('postal_code');

        if ($postalCode == '') {
            $postalCode = '0000';
        }

        return $postalCode;
    }

    protected function getPhone()
    {
        $phone = $this->getData('phone');

        if ($phone == '') {
            $phone = '0000000000';
        }

        return $phone;
    }

    protected function getState()
    {
        $state = $this->getData('state');

        if (!$this->getCountry()->getId() || strtoupper($this->getCountry()->getId()) != 'US') {
            return $state;
        }

        return preg_replace('/[^ \w]+/', '', $state);
    }

    protected function isRegionOverrideRequired()
    {
        /** @var Ess_M2ePro_Model_Amazon_Account $account */
        $account = $this->_order->getAccount()->getChildObject();

        return $account->isRegionOverrideRequired();
    }
}