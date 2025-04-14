<?php

class Ess_M2ePro_Model_Amazon_ProductType_Validation
    extends Ess_M2ePro_Model_Abstract
{
    const STATUS_INVALID = 0;
    const STATUS_VALID = 1;

    public function _construct()
    {
        parent::_construct();

        $this->_init('M2ePro/Amazon_ProductType_Validation');
    }

    public function getStatus()
    {
        return (int)$this->getData('status');
    }

    public function setValidStatus()
    {
        $this->setData('status', self::STATUS_VALID);
    }

    public function setInvalidStatus()
    {
        $this->setData('status', self::STATUS_INVALID);
    }

    public function getListingProductId()
    {
        return (int)$this->getData('listing_product_id');
    }

    public function setListingProductId($listingProductId)
    {
        $this->setData('listing_product_id', $listingProductId);
    }

    public function getErrorMessages()
    {
        return $this->getSettings('error_messages');
    }

    public function setErrorMessages(array $errorMessages)
    {
        $this->setSettings('error_messages', $errorMessages);
    }

    public function addErrorMessage($errorMessage)
    {
        $errorMessages = $this->getErrorMessages();
        $errorMessages[] = $errorMessage;

        $this->setErrorMessages($errorMessages);
    }

    public function isValid()
    {
        return $this->getStatus() === self::STATUS_VALID;
    }

    public function touchUpdateDate()
    {
        $date = Mage::helper('M2ePro')->createCurrentGmtDateTime()->format('Y-m-d H:i:s');
        $this->setData('update_date', $date);
    }

    public function touchCreateDate()
    {
        $date = Mage::helper('M2ePro')->createCurrentGmtDateTime()->format('Y-m-d H:i:s');
        $this->setData('create_date', $date);
    }
}