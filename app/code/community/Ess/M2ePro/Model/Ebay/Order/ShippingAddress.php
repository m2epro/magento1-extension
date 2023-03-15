<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Order_ShippingAddress extends Ess_M2ePro_Model_Order_ShippingAddress
{
    //########################################

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getRawData()
    {
        $buyerName = $this->_order->getChildObject()->getBuyerName();
        $recipientName = $this->getData('recipient_name');
        return array(
            'buyer_name'     => $buyerName,
            'recipient_name' => $recipientName ? $recipientName : $buyerName,
            'email'          => $this->getBuyerEmail(),
            'country_id'     => $this->getData('country_code'),
            'region'         => $this->getData('state'),
            'city'           => $this->getData('city') ? $this->getData('city') : $this->getCountryName(),
            'postcode'       => $this->getPostalCode(),
            'telephone'      => $this->getPhone(),
            'company'        => $this->getData('company'),
            'street'         => $this->getStreet()
        );
    }

    protected function getBuyerEmail()
    {
        $email = $this->_order->getData('buyer_email');

        if (stripos($email, 'Invalid Request') !== false || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = str_replace(' ', '-', strtolower($this->_order->getChildObject()->getBuyerUserId()));
            $email .= Ess_M2ePro_Model_Magento_Customer::FAKE_EMAIL_POSTFIX;
        }

        return $email;
    }

    protected function getPostalCode()
    {
        $postalCode = $this->getData('postal_code');

        if (stripos($postalCode, 'Invalid Request') !== false || $postalCode == '') {
            $postalCode = '0000';
        }

        return $postalCode;
    }

    protected function getPhone()
    {
        $phone = $this->getData('phone');

        if (stripos($phone, 'Invalid Request') !== false || $phone == '') {
            $phone = '0000000000';
        }

        return $phone;
    }

    protected function getStreet()
    {
        $street = $this->getData('street');

        if ($this->_order->getChildObject()->getEbayAccount()->isSkipEvtinModeOn()) {
            $street = array_map(
                function ($streetLine) {
                    $ebayPos = strpos($streetLine, 'ebay');

                    return $ebayPos === false ? $streetLine : trim(substr($streetLine, 0, $ebayPos));
                },
                $street
            );
        }

        return array_filter($street);
    }

    protected function isRegionOverrideRequired()
    {
        /** @var Ess_M2ePro_Model_Ebay_Account $account */
        $account = $this->_order->getAccount()->getChildObject();

        return $account->isRegionOverrideRequired();
    }
}
