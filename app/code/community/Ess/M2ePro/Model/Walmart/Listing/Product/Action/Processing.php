<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_Processing extends Ess_M2ePro_Model_Abstract
{
    const TYPE_ADD    = 'add';
    const TYPE_UPDATE = 'update';

    //####################################

    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct */
    protected $_listingProduct = null;

    /** @var Ess_M2ePro_Model_Processing $_processing */
    protected $_processing = null;

    /** @var Ess_M2ePro_Model_Request_Pending_Single $_requestPendingSingle */
    protected $_requestPendingSingle = null;

    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Walmart_Listing_Product_Action_Processing');
    }

    //####################################

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->_listingProduct = $listingProduct;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        if (!$this->getId()) {
            throw new Ess_M2ePro_Model_Exception_Logic('Instance must be loaded first.');
        }

        if ($this->_listingProduct !== null) {
            return $this->_listingProduct;
        }

        return $this->_listingProduct = Mage::helper('M2ePro')->getObject(
            'Listing_Product', $this->getListingProductId()
        );
    }

    // ---------------------------------------

    public function setProcessing(Ess_M2ePro_Model_Processing $processing)
    {
        $this->_processing = $processing;
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

        if ($this->_processing !== null) {
            return $this->_processing;
        }

        return $this->_processing = Mage::helper('M2ePro')->getObject('Processing', $this->getProcessingId());
    }

    //------------------------------------

    public function setRequestPendingSingle(Ess_M2ePro_Model_Request_Pending_Single $requestPendingSingle)
    {
        $this->_requestPendingSingle = $requestPendingSingle;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Request_Pending_Single
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getRequestPendingSingle()
    {
        if (!$this->getId()) {
            throw new Ess_M2ePro_Model_Exception_Logic('Instance must be loaded first.');
        }

        if (!$this->getRequestPendingSingleId()) {
            return null;
        }

        if ($this->_requestPendingSingle !== null) {
            return $this->_requestPendingSingle;
        }

        return $this->_requestPendingSingle = Mage::helper('M2ePro')->getObject(
            'Request_Pending_Single', $this->getRequestPendingSingleId()
        );
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

    public function getRequestPendingSingleId()
    {
        return (int)$this->getData('request_pending_single_id');
    }

    public function getType()
    {
        return $this->getData('type');
    }

    public function isPrepared()
    {
        return (bool)$this->getData('is_prepared');
    }

    public function getGroupHash()
    {
        return $this->getData('group_hash');
    }

    public function getRequestData()
    {
        return $this->getSettings('request_data');
    }

    //####################################
}