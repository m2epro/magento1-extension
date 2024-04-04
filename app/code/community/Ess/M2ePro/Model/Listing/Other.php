<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Listing_Other as AmazonListingOther;
use Ess_M2ePro_Model_Ebay_Listing_Other as EbayListingOther;
use Ess_M2ePro_Model_Walmart_Listing_Other as WalmartListingOther;

/**
 * @method AmazonListingOther|EbayListingOther|WalmartListingOther getChildObject()
 */
class Ess_M2ePro_Model_Listing_Other extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    const MOVING_LISTING_PRODUCT_DESTINATION_KEY = 'moved_to_listing_product_id';

    /**
     * @var Ess_M2ePro_Model_Account
     */
    protected $_accountModel = null;

    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    protected $_marketplaceModel = null;

    /**
     * @var Ess_M2ePro_Model_Magento_Product_Cache
     */
    protected $_magentoProductModel = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Other');
    }

    //########################################

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isLocked()
    {
        if ($this->isComponentModeEbay() && $this->getAccount()->getChildObject()->isModeSandbox()) {
            return false;
        }

        return parent::isLocked();
    }

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->_accountModel = null;
        $temp && $this->_marketplaceModel = null;
        $temp && $this->_magentoProductModel = null;
        return $temp;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        if ($this->_accountModel === null) {
            $this->_accountModel = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                $this->getComponentMode(), 'Account', $this->getData('account_id')
            );
        }

        return $this->_accountModel;
    }

    /**
     * @param Ess_M2ePro_Model_Account $instance
     */
    public function setAccount(Ess_M2ePro_Model_Account $instance)
    {
         $this->_accountModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        if ($this->_marketplaceModel === null) {
            $this->_marketplaceModel = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                $this->getComponentMode(), 'Marketplace', $this->getData('marketplace_id')
            );
        }

        return $this->_marketplaceModel;
    }

    /**
     * @param Ess_M2ePro_Model_Marketplace $instance
     */
    public function setMarketplace(Ess_M2ePro_Model_Marketplace $instance)
    {
         $this->_marketplaceModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getMagentoProduct()
    {
        if ($this->_magentoProductModel) {
            return $this->_magentoProductModel;
        }

        if ($this->getProductId() === null) {
            throw new Ess_M2ePro_Model_Exception('Product id is not set');
        }

        return $this->_magentoProductModel = Mage::getModel('M2ePro/Magento_Product_Cache')
                                                 ->setStoreId($this->getChildObject()->getRelatedStoreId())
                                                 ->setProductId($this->getProductId());
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product_Cache $instance
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product_Cache $instance)
    {
        $this->_magentoProductModel = $instance;
    }

    //########################################

    /**
     * @return int
     */
    public function getAccountId()
    {
        return (int)$this->getData('account_id');
    }

    /**
     * @return int
     */
    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
    }

    /**
     * @return int|null
     */
    public function getProductId()
    {
        $temp = $this->getData('product_id');
        return $temp === null ? null : (int)$temp;
    }

    public function getAdditionalData()
    {
        return $this->getSettings('additional_data');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getStatus()
    {
        return (int)$this->getData('status');
    }

    /**
     * @return int
     */
    public function getStatusChanger()
    {
        return (int)$this->getData('status_changer');
    }

    //########################################

    /**
     * @return bool
     */
    public function isNotListed()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;
    }

    /**
     * @return bool
     */
    public function isUnknown()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN;
    }

    /**
     * @return bool
     */
    public function isBlocked()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isListed()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN;
    }

    public function isInactive()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE;
    }

    //########################################

    public function unmapDeletedProduct($product)
    {
        $productId = $product instanceof Mage_Catalog_Model_Product ?
                        (int)$product->getId() : (int)$product;

        $listingsOther = Mage::getModel('M2ePro/Listing_Other')
                                    ->getCollection()
                                    ->addFieldToFilter('product_id', $productId)
                                    ->getItems();

        foreach ($listingsOther as $listingOther) {
            $listingOther->unmapProduct();
        }
    }

    // ---------------------------------------

    /**
     * @param int $productId
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function mapProduct($productId)
    {
        $this->addData(array('product_id'=>$productId))->save();
        $this->getChildObject()->afterMapProduct();
    }

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function unmapProduct()
    {
        $this->getChildObject()->beforeUnmapProduct();
        $this->setData('product_id', null)->save();
    }

    // ---------------------------------------

    public function moveToListingSucceed()
    {
        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::getModel('M2ePro/Listing_Product')->load(
            (int)$this->getSetting('additional_data', self::MOVING_LISTING_PRODUCT_DESTINATION_KEY)
        );

        if ($listingProduct->getId()) {
            $listingLogModel = Mage::getModel('M2ePro/Listing_Log');
            $listingLogModel->setComponentMode($this->getComponentMode());
            $actionId = $listingLogModel->getResource()->getNextActionId();
            $listingLogModel->addProductMessage(
                $listingProduct->getListingId(),
                $listingProduct->getProductId(),
                $listingProduct->getId(),
                Ess_M2ePro_Helper_Data::INITIATOR_USER,
                $actionId,
                Ess_M2ePro_Model_Listing_Log::ACTION_MOVE_FROM_OTHER_LISTING,
                'Item was Moved.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_INFO
            );
        }

        $this->deleteInstance();
    }

    //########################################
}
