<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Marketplace getParentObject()
 */
class Ess_M2ePro_Model_Amazon_Marketplace extends Ess_M2ePro_Model_Component_Child_Amazon_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Marketplace');
    }

    //########################################

    /**
     * @param bool $asObjects
     * @param array $filters
     * @return array|Ess_M2ePro_Model_Abstract[]
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getAmazonItems($asObjects = false, array $filters = array())
    {
        return $this->getRelatedSimpleItems('Amazon_Item', 'marketplace_id', $asObjects, $filters);
    }

    /**
     * @param bool $asObjects
     * @param array $filters
     * @return array|Ess_M2ePro_Model_Abstract[]
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getDescriptionTemplates($asObjects = false, array $filters = array())
    {
        return $this->getRelatedSimpleItems('Amazon_Template_Description', 'marketplace_id', $asObjects, $filters);
    }

    //########################################

    public function getCurrency()
    {
        return $this->getData('default_currency');
    }

    //########################################

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
    public function isNewAsinAvailable()
    {
        return (bool)$this->getData('is_new_asin_available');
    }

    /**
     * @return bool
     */
    public function isMerchantFulfillmentAvailable()
    {
        return (bool)$this->getData('is_merchant_fulfillment_available');
    }

    /**
     * @return bool
     */
    public function isBusinessAvailable()
    {
        return (bool)$this->getData('is_business_available');
    }

    /**
     * @return bool
     */
    public function isVatCalculationServiceAvailable()
    {
        return (bool)$this->getData('is_vat_calculation_service_available');
    }

    /**
     * @return bool
     */
    public function isProductTaxCodePolicyAvailable()
    {
        return (bool)$this->getData('is_product_tax_code_policy_available');
    }

    /**
     * @return bool
     */
    public function isAutomaticTokenRetrievingAvailable()
    {
        return (bool)$this->getData('is_automatic_token_retrieving_available');
    }

    /**
     * @return bool
     */
    public function isUploadInvoicesAvailable()
    {
        return (bool)$this->getData('is_upload_invoices_available');
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
