<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Listing_Product as AmazonListingProduct;
use Ess_M2ePro_Model_Ebay_Listing_Product as EbayListingProduct;
use Ess_M2ePro_Model_Walmart_Listing_Product as WalmartListingProduct;

use Ess_M2ePro_Model_Amazon_Listing_Product_Action_Processing as AmazonActionProcessing;
use Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processing as EbayActionProcessing;
use Ess_M2ePro_Model_Walmart_Listing_Product_Action_Processing as WalmartActionProcessing;

/**
 * @method AmazonListingProduct|EbayListingProduct|WalmartListingProduct getChildObject()
 *
 * @method setActionConfigurator(Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator)
 * @method Ess_M2ePro_Model_Listing_Product_Action_Configurator getActionConfigurator()
 *
 * @method AmazonActionProcessing|EbayActionProcessing|WalmartActionProcessing getProcessingAction()
 * @method setProcessingAction(AmazonActionProcessing|EbayActionProcessing|WalmartActionProcessing $action)
 */
class Ess_M2ePro_Model_Listing_Product extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    const ACTION_LIST   = 1;
    const ACTION_RELIST = 2;
    const ACTION_REVISE = 3;
    const ACTION_STOP   = 4;
    const ACTION_DELETE = 5;

    const STATUS_NOT_LISTED = 0;
    const STATUS_SOLD       = 1;
    const STATUS_LISTED     = 2;
    const STATUS_STOPPED    = 3;
    const STATUS_FINISHED   = 4;
    const STATUS_UNKNOWN    = 5;
    const STATUS_BLOCKED    = 6;
    const STATUS_HIDDEN     = 7;
    const STATUS_INACTIVE   = 8;

    const STATUS_CHANGER_UNKNOWN   = 0;
    const STATUS_CHANGER_SYNCH     = 1;
    const STATUS_CHANGER_USER      = 2;
    const STATUS_CHANGER_COMPONENT = 3;
    const STATUS_CHANGER_OBSERVER  = 4;

    const MOVING_LISTING_OTHER_SOURCE_KEY = 'moved_from_listing_other_id';

    const GROUPED_PRODUCT_MODE_OPTIONS = 0;
    const GROUPED_PRODUCT_MODE_SET     = 1;

    /**
     * It allows to delete an object without checking if it is isLocked()
     * @var bool
     */
    protected $_canBeForceDeleted = false;

    protected $_eventPrefix = 'm2epro_listing_product';

    /**
     * @var Ess_M2ePro_Model_Listing
     */
    protected $_listingModel = null;

    /**
     * @var Ess_M2ePro_Model_Magento_Product_Cache
     */
    protected $_magentoProductModel = null;

    /** @var Ess_M2ePro_Model_ActiveRecord_Factory */
    protected $_activeRecordFactory;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Product');
        $this->_activeRecordFactory = Mage::getSingleton('M2ePro/ActiveRecord_Factory');
    }

    //########################################

    public function delete()
    {
        $temp = parent::delete();

        $scheduledActions = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $scheduledActions->addFieldToFilter('listing_product_id', $this->getId());
        foreach ($scheduledActions->getItems() as $item) {
            /**@var Ess_M2ePro_Model_Listing_Product_ScheduledAction $item */
            $item->delete();
        }

        $instructionCollection = Mage::getResourceModel('M2ePro/Listing_Product_Instruction_Collection');
        $instructionCollection->addFieldToFilter('listing_product_id', $this->getId());
        foreach ($instructionCollection->getItems() as $item) {
            /**@var Ess_M2ePro_Model_Listing_Product_Instruction $item */
            $item->delete();
        }

        return $temp;
    }

    //########################################

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isLocked()
    {
        if ($this->canBeForceDeleted()) {
            return false;
        }

        if ($this->isComponentModeEbay() && $this->getAccount()->getChildObject()->isModeSandbox()) {
            return false;
        }

        if (parent::isLocked()) {
            return true;
        }

        if ($this->getStatus() == self::STATUS_LISTED) {
            return true;
        }

        return false;
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $variations = $this->getVariations(true);
        foreach ($variations as $variation) {
            $variation->deleteInstance();
        }

        $instructions = $this->_activeRecordFactory->getObjectCollection('Listing_Product_Instruction');
        $instructions->addFieldToFilter('listing_product_id', $this->getId());
        foreach ($instructions->getItems() as $instruction) {
            /** @var Ess_M2ePro_Model_Listing_Product_Instruction $instruction */
            $instruction->deleteInstance();
        }

        $scheduledActions = $this->_activeRecordFactory->getObjectCollection('Listing_Product_ScheduledAction');
        $scheduledActions->addFieldToFilter('listing_product_id', $this->getId());
        foreach ($scheduledActions->getItems() as $scheduledAction) {
            /** @var Ess_M2ePro_Model_Listing_Product_ScheduledAction $scheduledAction */
            $scheduledAction->deleteInstance();
        }

        $this->logProductMessage(
            'Item was Deleted',
            Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
            Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_LISTING,
            Ess_M2ePro_Model_Log_Abstract::TYPE_INFO
        );

        $this->_listingModel = null;
        $this->_magentoProductModel = null;

        $this->deleteChildInstance();
        $this->delete();

        return true;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        if ($this->_listingModel === null) {
            $this->_listingModel = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                $this->getComponentMode(),
                'Listing',
                $this->getData('listing_id')
            );
        }

        return $this->_listingModel;
    }

    /**
     * @param Ess_M2ePro_Model_Listing $instance
     */
    public function setListing(Ess_M2ePro_Model_Listing $instance)
    {
        $this->_listingModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    public function getMagentoProduct()
    {
        if ($this->_magentoProductModel === null) {
            $this->_magentoProductModel = Mage::getModel('M2ePro/Magento_Product_Cache');
            $this->_magentoProductModel->setProductId($this->getProductId());

            if ($this->_magentoProductModel->isGroupedType()) {
                $this->_magentoProductModel->setGroupedProductMode($this->getGroupedProductMode());
            }
        }

        return $this->prepareMagentoProduct($this->_magentoProductModel);
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product_Cache $instance
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product_Cache $instance)
    {
        $this->_magentoProductModel = $this->prepareMagentoProduct($instance);
    }

    protected function prepareMagentoProduct(Ess_M2ePro_Model_Magento_Product_Cache $instance)
    {
        $instance->setStoreId($this->getListing()->getStoreId());
        $instance->setStatisticId($this->getId());

        if (method_exists($this->getChildObject(), 'prepareMagentoProduct')) {
            $instance = $this->getChildObject()->prepareMagentoProduct($instance);
        }

        return $instance;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        return $this->getListing()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        return $this->getListing()->getMarketplace();
    }

    //########################################

    /**
     * @param bool $asObjects
     * @param array $filters
     * @return Ess_M2ePro_Model_Listing_Product_Variation[]
     */
    public function getVariations($asObjects = false, array $filters = array())
    {
        /** @var Ess_M2ePro_Helper_Data_Cache_Runtime $runtimeCache */
        $runtimeCache = Mage::helper('M2ePro/Data_Cache_Runtime');
        /** @var Ess_M2ePro_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('M2ePro');
        /** @var Ess_M2ePro_Helper_Component $componentHelper */
        $componentHelper = Mage::helper('M2ePro/Component');

        $storageKey = "listing_product_{$this->getId()}_variations_" .
            sha1((string)$asObjects . $dataHelper->jsonEncode($filters));

        if ($cacheData = $runtimeCache->getValue($storageKey)) {
            return $cacheData;
        }

        /** @var Ess_M2ePro_Model_Resource_ActiveRecord_CollectionAbstract $collection */
        $collection = $componentHelper->getComponentCollection(
            $this->getComponentMode(),
            'Listing_Product_Variation'
        );
        $collection->addFieldToFilter('listing_product_id', $this->getId());

        foreach ($filters as $filterName => $filterValue) {
            $collection->addFieldToFilter($filterName, $filterValue);
        }

        foreach ($collection->getItems() as $variation) {
            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation->setListingProduct($this);
        }

        if ($asObjects) {
            $result = $collection->getItems();
        } else {
            $result = $collection->toArray();
            $result = $result['items'];
        }

        $runtimeCache->setValue(
            $storageKey,
            $result,
            array(
                'listing_product',
                "listing_product_{$this->getId()}",
                "listing_product_{$this->getId()}_variations"
            )
        );

        return $result;
    }

    //########################################

    /**
     * @return int
     */
    public function getListingId()
    {
        return (int)$this->getData('listing_id');
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return (int)$this->getData('product_id');
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

    // ---------------------------------------

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getAdditionalData()
    {
        return $this->getSettings('additional_data');
    }

    //########################################

    /**
     * @return null|int
     */
    public function getGroupedProductMode()
    {
        if (!$this->getMagentoProduct()->isGroupedType()) {
            return null;
        }

        if ($this->isListable()) {
            return Mage::helper('M2ePro/Module_Configuration')->getGroupedProductMode();
        }

        return (int)$this->getSetting('additional_data', 'grouped_product_mode', self::GROUPED_PRODUCT_MODE_OPTIONS);
    }

    /**
     * @return bool
     */
    public function isGroupedProductModeSet()
    {
        return $this->getGroupedProductMode() === self::GROUPED_PRODUCT_MODE_SET;
    }

    //########################################

    /**
     * @return bool
     */
    public function isNotListed()
    {
        return $this->getStatus() == self::STATUS_NOT_LISTED;
    }

    /**
     * @return bool
     */
    public function isUnknown()
    {
        return $this->getStatus() == self::STATUS_UNKNOWN;
    }

    /**
     * @return bool
     */
    public function isBlocked()
    {
        return $this->getStatus() == self::STATUS_BLOCKED;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isListed()
    {
        return $this->getStatus() == self::STATUS_LISTED;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->getStatus() == self::STATUS_HIDDEN;
    }

    /**
     * @return bool
     */
    public function isSold()
    {
        return $this->getStatus() == self::STATUS_SOLD;
    }

    /**
     * @return bool
     */
    public function isStopped()
    {
        return $this->getStatus() == self::STATUS_STOPPED;
    }

    /**
     * @return bool
     */
    public function isFinished()
    {
        return $this->getStatus() == self::STATUS_FINISHED;
    }

    public function isInactive()
    {
        return $this->getStatus() == self::STATUS_INACTIVE;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isListable()
    {
        return !$this->isBlocked()
            && (
                $this->isNotListed()
                || $this->isSold()
                || $this->isStopped()
                || $this->isFinished()
                || $this->isHidden()
                || $this->isUnknown()
                || $this->isInactive()
            );
    }

    /**
     * @return bool
     */
    public function isRelistable()
    {
        return !$this->isBlocked()
            && (
                $this->isSold()
                || $this->isStopped()
                || $this->isFinished()
                || $this->isUnknown()
                || $this->isInactive()
            );
    }

    /**
     * @return bool
     */
    public function isRevisable()
    {
        return !$this->isBlocked()
            && (
                $this->isListed()
                || $this->isHidden()
                || $this->isUnknown()
            );
    }

    /**
     * @return bool
     */
    public function isStoppable()
    {
        return !$this->isBlocked()
            && (
                $this->isListed()
                || $this->isHidden()
                || $this->isUnknown()
            );
    }

    //########################################

    public function remapProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $exMagentoProductId = $this->getProductId();
        $newMagentoProductId = $magentoProduct->getProductId();
        $data = array('product_id' => $newMagentoProductId);

        if ($this->getMagentoProduct()->isStrictVariationProduct()
            && $magentoProduct->isSimpleTypeWithoutCustomOptions()) {
            $data['is_variation_product'] = 0;
            $data['is_variation_parent'] = 0;
            $data['variation_parent_id'] = null;
        }

        $this->addData($data)->save();
        $this->getChildObject()->mapChannelItemProduct();

        $instruction = Mage::getModel('M2ePro/Listing_Product_Instruction');
        $instruction->setData(
            array(
                'listing_product_id' => $this->getId(),
                'component'          => $this->getComponentMode(),
                'type'               => Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
                'initiator'          => Ess_M2ePro_Model_Listing::INSTRUCTION_INITIATOR_REMAPING_PRODUCT_FROM_LISTING,
                'priority'           => 50,
            )
        );

        $instruction->save();
        
        $this->logProductMessage(
            sprintf(
                "Item was relinked from Magento Product ID [%s] to ID [%s]",
                $exMagentoProductId,
                $newMagentoProductId
            ),
            Ess_M2ePro_Helper_Data::INITIATOR_USER,
            Ess_M2ePro_Model_Listing_Log::ACTION_REMAP_LISTING_PRODUCT,
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS
        );
    }

    //########################################

    public function canBeForceDeleted($value = null)
    {
        if ($value === null) {
            return $this->_canBeForceDeleted;
        }

        $this->_canBeForceDeleted = $value;

        return $this;
    }

    //########################################
    
    public function logProductMessage($text, $initiator, $action, $type)
    {
        /** @var Ess_M2ePro_Model_Listing_Log $log */
        $log = Mage::getModel('M2ePro/Listing_Log');
        $log->setComponentMode($this->getComponentMode());
        $actionId = $log->getResource()->getNextActionId();
        $log->addProductMessage(
            $this->getListingId(),
            $this->getProductId(),
            $this->getId(),
            $initiator,
            $actionId,
            $action,
            $text,
            $type
        );
    }
    
    //########################################
}
