<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processing extends Ess_M2ePro_Model_Abstract
{
    const TYPE_LIST   = 'list';
    const TYPE_RELIST = 'relist';
    const TYPE_REVISE = 'revise';
    const TYPE_STOP   = 'stop';

    //####################################

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    private $listingProduct = NULL;

    /** @var Ess_M2ePro_Model_Processing $processing */
    private $processing = NULL;

    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Listing_Product_Action_Processing');
    }

    //####################################

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->listingProduct = $listingProduct;
        return $this;
    }

    public function getListingProduct()
    {
        if (!$this->getId()) {
            throw new Ess_M2ePro_Model_Exception_Logic('Instance must be loaded first.');
        }

        if (!is_null($this->listingProduct)) {
            return $this->listingProduct;
        }

        return $this->listingProduct = Mage::helper('M2ePro')->getObject(
            'Listing_Product', $this->getListingProductId()
        );
    }

    // ---------------------------------------

    public function setProcessing(Ess_M2ePro_Model_Processing $processing)
    {
        $this->processing = $processing;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Processing
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getProcessing()
    {
        if (!$this->getId()) {
            throw new Ess_M2ePro_Model_Exception_Logic('Instance must be loaded first.');
        }

        if (!is_null($this->processing)) {
            return $this->processing;
        }

        return $this->processing = Mage::helper('M2ePro')->getObject('Processing', $this->getProcessingId());
    }

    //####################################

    public function getListingProductId()
    {
        return (int)$this->getData('listing_product_id');
    }

    public function getProcessingId()
    {
        return (int)$this->getData('processing_id');
    }

    public function getType()
    {
        return $this->getData('type');
    }

    public function getRequestTimeOut()
    {
        return (int)$this->getData('request_timeout');
    }

    public function getRequestData()
    {
        return $this->getSettings('request_data');
    }

    //####################################
}