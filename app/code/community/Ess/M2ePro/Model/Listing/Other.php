<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Listing_Other extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Account
     */
    private $accountModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    private $marketplaceModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Magento_Product_Cache
     */
    protected $magentoProductModel = NULL;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Other');
    }

    //########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->accountModel = NULL;
        $temp && $this->marketplaceModel = NULL;
        $temp && $this->magentoProductModel = NULL;
        return $temp;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        if (is_null($this->accountModel)) {
            $this->accountModel = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                $this->getComponentMode(),'Account',$this->getData('account_id')
            );
        }

        return $this->accountModel;
    }

    /**
     * @param Ess_M2ePro_Model_Account $instance
     */
    public function setAccount(Ess_M2ePro_Model_Account $instance)
    {
         $this->accountModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        if (is_null($this->marketplaceModel)) {
            $this->marketplaceModel = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                $this->getComponentMode(),'Marketplace',$this->getData('marketplace_id')
            );
        }

        return $this->marketplaceModel;
    }

    /**
     * @param Ess_M2ePro_Model_Marketplace $instance
     */
    public function setMarketplace(Ess_M2ePro_Model_Marketplace $instance)
    {
         $this->marketplaceModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getMagentoProduct()
    {
        if ($this->magentoProductModel) {
            return $this->magentoProductModel;
        }

        if (is_null($this->getProductId())) {
            throw new Ess_M2ePro_Model_Exception('Product id is not set');
        }

        return $this->magentoProductModel = Mage::getModel('M2ePro/Magento_Product_Cache')
                                                    ->setStoreId($this->getChildObject()->getRelatedStoreId())
                                                    ->setProductId($this->getProductId());
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product_Cache $instance
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product_Cache $instance)
    {
        $this->magentoProductModel = $instance;
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
        return is_null($temp) ? NULL : (int)$temp;
    }

    public function getAdditionalData()
    {
        $additionalData = $this->getData('additional_data');
        is_string($additionalData) && $additionalData = json_decode($additionalData,true);
        return is_array($additionalData) ? $additionalData : array();
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
    public function isSold()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_SOLD;
    }

    /**
     * @return bool
     */
    public function isStopped()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
    }

    /**
     * @return bool
     */
    public function isFinished()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isListable()
    {
        return ($this->isNotListed() || $this->isSold() ||
                $this->isStopped() || $this->isFinished() ||
                $this->isUnknown()) &&
                !$this->isBlocked();
    }

    /**
     * @return bool
     */
    public function isRelistable()
    {
        return ($this->isSold() || $this->isStopped() ||
                $this->isFinished() || $this->isUnknown()) &&
                !$this->isBlocked();
    }

    /**
     * @return bool
     */
    public function isRevisable()
    {
        return ($this->isListed() || $this->isUnknown()) &&
                !$this->isBlocked();
    }

    /**
     * @return bool
     */
    public function isStoppable()
    {
        return ($this->isListed() || $this->isUnknown()) &&
                !$this->isBlocked();
    }

    //########################################

    public function reviseAction(array $params = array())
    {
        return $this->getChildObject()->reviseAction($params);
    }

    public function relistAction(array $params = array())
    {
        return $this->getChildObject()->relistAction($params);
    }

    public function stopAction(array $params = array())
    {
        return $this->getChildObject()->stopAction($params);
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
            $listingOther->unmapProduct(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);
        }
    }

    // ---------------------------------------

    /**
     * @param int $productId
     * @param int $logsInitiator
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function mapProduct($productId, $logsInitiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN)
    {
        $this->addData(array('product_id'=>$productId))->save();
        $this->getChildObject()->afterMapProduct();

        $logModel = Mage::getModel('M2ePro/Listing_Other_Log');
        $logModel->setComponentMode($this->getComponentMode());
        $logModel->addProductMessage($this->getId(),
            $logsInitiator,
            NULL,
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_MAP_LISTING,
            // M2ePro_TRANSLATIONS
            // Item was successfully Mapped
            'Item was successfully Mapped',
            Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
    }

    /**
     * @param int $logsInitiator
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function unmapProduct($logsInitiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN)
    {
        $this->getChildObject()->beforeUnmapProduct();
        $this->setData('product_id', NULL)->save();

        $logModel = Mage::getModel('M2ePro/Listing_Other_Log');
        $logModel->setComponentMode($this->getComponentMode());
        $logModel->addProductMessage($this->getId(),
            $logsInitiator,
            NULL,
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_UNMAP_LISTING,
            // M2ePro_TRANSLATIONS
            // Item was successfully Unmapped
            'Item was successfully Unmapped',
            Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
    }

    //########################################
}