<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Listing as AmazonListing;
use Ess_M2ePro_Model_Ebay_Listing as EbayListing;
use Ess_M2ePro_Model_Walmart_Listing as WalmartListing;
use Ess_M2ePro_Model_Amazon_Listing_Product as AmazonProduct;
use Ess_M2ePro_Model_Walmart_Listing_Product as WalmartProduct;

/**
 * @method AmazonListing|EbayListing|WalmartListing getChildObject()
 */
class Ess_M2ePro_Model_Listing extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    const INSTRUCTION_TYPE_PRODUCT_ADDED       = 'listing_product_added';
    const INSTRUCTION_INITIATOR_ADDING_PRODUCT = 'adding_product_to_listing';

    const INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER       = 'listing_product_moved_from_other';
    const INSTRUCTION_INITIATOR_MOVING_PRODUCT_FROM_OTHER = 'moving_product_from_other_to_listing';

    const INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING       = 'listing_product_moved_from_listing';
    const INSTRUCTION_INITIATOR_MOVING_PRODUCT_FROM_LISTING = 'moving_product_from_listing_to_listing';

    const INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING         = 'listing_product_remap_from_listing';
    const INSTRUCTION_INITIATOR_REMAPING_PRODUCT_FROM_LISTING = 'remaping_product_from_listing_to_listing';

    const SOURCE_PRODUCTS_CUSTOM     = 1;
    const SOURCE_PRODUCTS_CATEGORIES = 2;

    const AUTO_MODE_NONE     = 0;
    const AUTO_MODE_GLOBAL   = 1;
    const AUTO_MODE_WEBSITE  = 2;
    const AUTO_MODE_CATEGORY = 3;

    const ADDING_MODE_NONE = 0;
    const ADDING_MODE_ADD  = 1;

    const AUTO_ADDING_ADD_NOT_VISIBLE_NO  = 0;
    const AUTO_ADDING_ADD_NOT_VISIBLE_YES = 1;

    const DELETING_MODE_NONE        = 0;
    const DELETING_MODE_STOP        = 1;
    const DELETING_MODE_STOP_REMOVE = 2;

    /**
     * @var Ess_M2ePro_Model_Account
     */
    protected $_accountModel = null;

    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    protected $_marketplaceModel = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing');
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

        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::getModel('M2ePro/Listing_Product')
            ->getCollection()
            ->addFieldToFilter('listing_id', $this->getId())
            ->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_LISTED)
            ->getSize();
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $products = $this->getProducts(true);
        foreach ($products as $product) {
            $product->deleteInstance();
        }

        $categoriesGroups = $this->getAutoCategoriesGroups(true);
        foreach ($categoriesGroups as $categoryGroup) {
            $categoryGroup->deleteInstance();
        }

        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode($this->getComponentMode());
        $actionId = $tempLog->getResource()->getNextActionId();
        $tempLog->addListingMessage(
            $this->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
            $actionId,
            Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_LISTING,
            'Listing was deleted',
            Ess_M2ePro_Model_Log_Abstract::TYPE_INFO
        );

        $this->_accountModel = null;
        $this->_marketplaceModel = null;

        $this->deleteChildInstance();
        $this->delete();

        return true;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        if ($this->_accountModel === null) {
            $this->_accountModel = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                $this->getComponentMode(),
                'Account',
                $this->getAccountId()
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
                $this->getComponentMode(),
                'Marketplace',
                $this->getMarketplaceId()
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

    //########################################

    /**
     * @param bool $asObjects
     * @param array $filters
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getProducts($asObjects = false, array $filters = array())
    {
        /** @var Ess_M2ePro_Model_Resource_ActiveRecord_CollectionAbstract $collection */
        $collection = Mage::helper('M2ePro/Component')->getComponentCollection(
            $this->getComponentMode(),
            'Listing_Product'
        );
        $collection->addFieldToFilter('listing_id', $this->getId());

        foreach ($filters as $filterName => $filterValue) {
            $collection->addFieldToFilter($filterName, $filterValue);
        }

        foreach ($collection->getItems() as $product) {
            /** @var $product Ess_M2ePro_Model_Listing_Product */
            $product->setListing($this);
        }

        if (!$asObjects) {
            $result = $collection->toArray();

            return $result['items'];
        }

        return $collection->getItems();
    }

    /**
     * @param bool $asObjects
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getAutoCategoriesGroups($asObjects = false)
    {
        /** @var Ess_M2ePro_Model_Resource_ActiveRecord_CollectionAbstract $collection */
        $collection = Mage::helper('M2ePro/Component')->getComponentCollection(
            $this->getComponentMode(),
            'Listing_Auto_Category_Group'
        );
        $collection->addFieldToFilter('listing_id', $this->getId());

        if (!$asObjects) {
            $result = $collection->toArray();

            return $result['items'];
        }

        return $collection->getItems();
    }

    //########################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    // ---------------------------------------

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
     * @return int
     */
    public function getStoreId()
    {
        return (int)$this->getData('store_id');
    }

    // ---------------------------------------

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }

    public function getUpdateDate()
    {
        return $this->getData('update_date');
    }

    //########################################

    /**
     * @return bool
     */
    public function isSourceProducts()
    {
        return (int)$this->getData('source_products') == self::SOURCE_PRODUCTS_CUSTOM;
    }

    /**
     * @return bool
     */
    public function isSourceCategories()
    {
        return (int)$this->getData('source_products') == self::SOURCE_PRODUCTS_CATEGORIES;
    }

    //########################################

    /**
     * @return int
     */
    public function getAutoMode()
    {
        return (int)$this->getData('auto_mode');
    }

    /**
     * @return bool
     */
    public function isAutoModeNone()
    {
        return $this->getAutoMode() == self::AUTO_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isAutoModeGlobal()
    {
        return $this->getAutoMode() == self::AUTO_MODE_GLOBAL;
    }

    /**
     * @return bool
     */
    public function isAutoModeWebsite()
    {
        return $this->getAutoMode() == self::AUTO_MODE_WEBSITE;
    }

    /**
     * @return bool
     */
    public function isAutoModeCategory()
    {
        return $this->getAutoMode() == self::AUTO_MODE_CATEGORY;
    }

    //########################################

    /**
     * @return bool
     */
    public function isAutoGlobalAddingAddNotVisibleYes()
    {
        return $this->getData('auto_global_adding_add_not_visible') == self::AUTO_ADDING_ADD_NOT_VISIBLE_YES;
    }

    /**
     * @return bool
     */
    public function isAutoWebsiteAddingAddNotVisibleYes()
    {
        return $this->getData('auto_website_adding_add_not_visible') == self::AUTO_ADDING_ADD_NOT_VISIBLE_YES;
    }

    //########################################

    /**
     * @return int
     */
    public function getAutoGlobalAddingMode()
    {
        return (int)$this->getData('auto_global_adding_mode');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isAutoGlobalAddingModeNone()
    {
        return $this->getAutoGlobalAddingMode() == self::ADDING_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isAutoGlobalAddingModeAdd()
    {
        return $this->getAutoGlobalAddingMode() == self::ADDING_MODE_ADD;
    }

    //########################################

    /**
     * @return int
     */
    public function getAutoWebsiteAddingMode()
    {
        return (int)$this->getData('auto_website_adding_mode');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isAutoWebsiteAddingModeNone()
    {
        return $this->getAutoWebsiteAddingMode() == self::ADDING_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isAutoWebsiteAddingModeAdd()
    {
        return $this->getAutoWebsiteAddingMode() == self::ADDING_MODE_ADD;
    }

    //########################################

    /**
     * @return int
     */
    public function getAutoWebsiteDeletingMode()
    {
        return (int)$this->getData('auto_website_deleting_mode');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isAutoWebsiteDeletingModeNone()
    {
        return $this->getAutoWebsiteDeletingMode() == self::DELETING_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isAutoWebsiteDeletingModeStop()
    {
        return $this->getAutoWebsiteDeletingMode() == self::DELETING_MODE_STOP;
    }

    /**
     * @return bool
     */
    public function isAutoWebsiteDeletingModeStopRemove()
    {
        return $this->getAutoWebsiteDeletingMode() == self::DELETING_MODE_STOP_REMOVE;
    }

    //########################################

    public function addProduct(
        $product,
        $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
        $checkingMode = false,
        $checkHasProduct = true,
        array $logAdditionalInfo = array()
    ) {
        if ($product instanceof Mage_Catalog_Model_Product) {
            $productId = (int)$product->getId();
            $productType = $product->getTypeId();
        } else {
            $productId = (int)$product;
            $productType = Ess_M2ePro_Model_Magento_Product::getTypeIdByProductId($productId);
        }

        if ($productType == 'virtual') {
            return false;
        }

        if ($checkHasProduct && $this->hasProduct($productId)) {
            return false;
        }

        if ($checkingMode) {
            return true;
        }

        $data = array(
            'listing_id'     => $this->getId(),
            'product_id'     => $productId,
            'status'         => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED,
            'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN
        );

        $listingProductTemp =
            Mage::helper('M2ePro/Component')->getComponentModel($this->getComponentMode(), 'Listing_Product')
                ->setData($data)->save();

        $listingProductTemp->getChildObject()->afterSaveNewEntity();

        $variationUpdaterModel = ucwords($this->getComponentMode()) . '_Listing_Product_Variation_Updater';
        /** @var Ess_M2ePro_Model_Listing_Product_Variation_Updater $variationUpdaterObject */
        $variationUpdaterObject = Mage::getModel('M2ePro/' . $variationUpdaterModel);
        $variationUpdaterObject->process($listingProductTemp);
        $variationUpdaterObject->afterMassProcessEvent();

        // Add message for listing log
        // ---------------------------------------
        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode($this->getComponentMode());
        $actionId = $tempLog->getResource()->getNextActionId();
        $tempLog->addProductMessage(
            $this->getId(),
            $productId,
            $listingProductTemp->getId(),
            $initiator,
            $actionId,
            Ess_M2ePro_Model_Listing_Log::ACTION_ADD_PRODUCT_TO_LISTING,
            'Product was Added',
            Ess_M2ePro_Model_Log_Abstract::TYPE_INFO,
            $logAdditionalInfo
        );
        // ---------------------------------------

        $instruction = Mage::getModel('M2ePro/Listing_Product_Instruction');
        $instruction->setData(
            array(
                'listing_product_id' => $listingProductTemp->getId(),
                'component'          => $this->getComponentMode(),
                'type'               => self::INSTRUCTION_TYPE_PRODUCT_ADDED,
                'initiator'          => self::INSTRUCTION_INITIATOR_ADDING_PRODUCT,
                'priority'           => 70,
            )
        );
        $instruction->save();

        return $listingProductTemp;
    }

    // ---------------------------------------

    public function addProductsFromCategory(
        $categoryId,
        $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN
    ) {
        $categoryProductsArray = $this->getProductsFromCategory($categoryId);
        foreach ($categoryProductsArray as $productTemp) {
            $this->addProduct($productTemp, $initiator);
        }
    }

    public function getProductsFromCategory($categoryId, $hideProductsPresentedInAnotherListings = false)
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Magento_Product_Collection */
        $collection = Mage::getConfig()->getModelInstance(
            'Ess_M2ePro_Model_Resource_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource()
        );

        if ($hideProductsPresentedInAnotherListings) {
            $table = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();
            $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                ->select()
                ->from($table, new Zend_Db_Expr('DISTINCT `product_id`'))
                ->where('`component_mode` = ?', (string)$this->getComponentMode());

            $collection->getSelect()->where('`e`.`entity_id` NOT IN (' . $dbSelect->__toString() . ')');
        }

        $table = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('catalog_category_product');
        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
            ->from($table, new Zend_Db_Expr('DISTINCT `product_id`'))
            ->where("`category_id` = ?", (int)$categoryId);

        $collection->getSelect()->where('`e`.`entity_id` IN (' . $dbSelect->__toString() . ')');

        $sqlQuery = $collection->getSelect()->__toString();

        $categoryProductsArray = Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($sqlQuery);

        return (array)$categoryProductsArray;
    }

    // ---------------------------------------

    public function addProductFromListing(
        Ess_M2ePro_Model_Listing_Product $listingProduct,
        Ess_M2ePro_Model_Listing $sourceListing,
        $checkHasProduct = true
    ) {
        $logModel = Mage::getModel('M2ePro/Listing_Log');
        $logModel->setComponentMode($this->getComponentMode());
        $actionId = $logModel->getResource()->getNextActionId();

        if ($listingProduct->isSetProcessingLock() ||
            $listingProduct->isSetProcessingLock('in_action')) {
            $logModel->addProductMessage(
                $sourceListing->getId(),
                $listingProduct->getProductId(),
                $listingProduct->getId(),
                Ess_M2ePro_Helper_Data::INITIATOR_USER,
                $actionId,
                Ess_M2ePro_Model_Listing_Log::ACTION_MOVE_TO_LISTING,
                'Item was not Moved because it is in progress state now',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
            );

            return false;
        }

        // Add attribute set filter
        // ---------------------------------------
        $table = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('catalog_product_entity');
        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
            ->from($table, new Zend_Db_Expr('DISTINCT `entity_id`'))
            ->where('`entity_id` = ?', (int)$listingProduct->getProductId());

        $productArray = Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($dbSelect);

        if (empty($productArray)) {
            $logModel->addProductMessage(
                $sourceListing->getId(),
                $listingProduct->getProductId(),
                $listingProduct->getId(),
                Ess_M2ePro_Helper_Data::INITIATOR_USER,
                $actionId,
                Ess_M2ePro_Model_Listing_Log::ACTION_MOVE_TO_LISTING,
                'Item was not Moved',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
            );

            return false;
        }

        // ---------------------------------------

        if ($checkHasProduct && $this->hasProduct($listingProduct->getProductId())) {
            $logModel->addProductMessage(
                $sourceListing->getId(),
                $listingProduct->getProductId(),
                $listingProduct->getId(),
                Ess_M2ePro_Helper_Data::INITIATOR_USER,
                $actionId,
                Ess_M2ePro_Model_Listing_Log::ACTION_MOVE_TO_LISTING,
                'Product already exists in the selected Listing',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
            );

            return false;
        }

        $logMessage = Mage::helper('M2ePro')->__(
            'Product was transferred from %previous_listing_name% Listing to %current_listing_name% Listing.',
            $sourceListing->getTitle(),
            $this->getTitle()
        );

        $logModel->addProductMessage(
            $sourceListing->getId(),
            $listingProduct->getProductId(),
            $listingProduct->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_USER,
            $actionId,
            Ess_M2ePro_Model_Listing_Log::ACTION_MOVE_TO_LISTING,
            $logMessage,
            Ess_M2ePro_Model_Log_Abstract::TYPE_INFO
        );

        $logModel->addProductMessage(
            $this->getId(),
            $listingProduct->getProductId(),
            $listingProduct->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_USER,
            $actionId,
            Ess_M2ePro_Model_Listing_Log::ACTION_MOVE_TO_LISTING,
            $logMessage,
            Ess_M2ePro_Model_Log_Abstract::TYPE_INFO
        );

        // ---------------------------------------
        $listingProduct->setData('listing_id', $this->getId());
        $listingProduct->save();
        $listingProduct->setListing($this);
        // ---------------------------------------

        // ---------------------------------------
        $instruction = Mage::getModel('M2ePro/Listing_Product_Instruction');
        $instruction->setData(
            array(
                'listing_product_id' => $listingProduct->getId(),
                'component'          => $this->getComponentMode(),
                'type'               => Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
                'initiator'          => Ess_M2ePro_Model_Listing::INSTRUCTION_INITIATOR_MOVING_PRODUCT_FROM_LISTING,
                'priority'           => 20,
            )
        );
        $instruction->save();

        // ---------------------------------------

        return true;
    }

    //########################################

    /**
     * @param int $productId
     * @return bool
     */
    public function hasProduct($productId)
    {
        $products = $this->getProducts(false, array('product_id' => $productId));

        return !empty($products);
    }

    public function removeDeletedProduct($product)
    {
        $productId = $product instanceof Mage_Catalog_Model_Product ?
            (int)$product->getId() : (int)$product;

        $processedListings = array();

        // Delete Products
        // ---------------------------------------
        $listingsProducts = Mage::getModel('M2ePro/Listing_Product')
            ->getCollection()
            ->addFieldToFilter('product_id', $productId)
            ->getItems();

        $listingsProductsForRemove = array();

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($listingsProducts as $listingProduct) {
            $message = Mage::helper('M2ePro')->__('Item was deleted from Magento.');
            if ($listingProduct->getStatus() != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
                $message = Mage::helper('M2ePro')->__('Item was deleted from Magento and stopped on the Channel.');
            }

            if (!isset($listingsProductsForRemove[$listingProduct->getId()])) {
                $listingProduct->deleteProcessingLocks();

                if ($listingProduct->isComponentModeEbay() &&
                    $listingProduct->getChildObject()->isOutOfStockControlEnabled()) {
                    $listingProduct->isStoppable() && Mage::getModel('M2ePro/StopQueue')->add(
                        $listingProduct,
                        Ess_M2ePro_Model_Listing_Product::ACTION_REVISE
                    );

                    if ($listingProduct->getStatus() != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
                        $message = Mage::helper('M2ePro')->__(
                            'Item was deleted from Magento and hidden on the Channel.'
                        );
                    }

                    $listingProduct->setStatus(Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN)->save();
                } else {
                    $listingProduct->isStoppable() && Mage::getModel('M2ePro/StopQueue')->add($listingProduct);

                    $listingProduct->setStatus(Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE)->save();
                }

                if ($listingProduct->isComponentModeAmazon() || $listingProduct->isComponentModeWalmart()) {
                    /** @var AmazonProduct|WalmartProduct $componentListingProduct */
                    $componentListingProduct = $listingProduct->getChildObject();
                    $variationManager = $componentListingProduct->getVariationManager();

                    if (!$variationManager->isRelationChildType() ||
                        !isset($listingsProducts[$variationManager->getVariationParentId()])) {
                        $listingsProductsForRemove[$listingProduct->getId()] = $listingProduct;
                    }
                } else {
                    $listingsProductsForRemove[$listingProduct->getId()] = $listingProduct;
                }
            }

            $listingId = $listingProduct->getListingId();
            $componentMode = $listingProduct->getComponentMode();

            if (isset($processedListings[$listingId . '_' . $componentMode])) {
                continue;
            }

            $processedListings[$listingId . '_' . $componentMode] = 1;

            $logModel = Mage::getModel('M2ePro/Listing_Log');
            $logModel->setComponentMode($componentMode);
            $actionId = $logModel->getResource()->getNextActionId();

            $logModel->addProductMessage(
                $listingId,
                $productId,
                $listingProduct->getId(),
                Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                $actionId,
                Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_MAGENTO,
                $message,
                Ess_M2ePro_Model_Log_Abstract::TYPE_INFO
            );
        }

        $processedListings = array();

        // Delete Options
        // ---------------------------------------
        $variationOptions = Mage::getModel('M2ePro/Listing_Product_Variation_Option')
            ->getCollection()
            ->addFieldToFilter('product_id', $productId)
            ->getItems();

        $processedVariationsIds = array();

        /** @var $variationOption Ess_M2ePro_Model_Listing_Product_Variation_Option */
        foreach ($variationOptions as $variationOption) {
            if (in_array($variationOption->getListingProductVariationId(), $processedVariationsIds)) {
                continue;
            }

            $processedVariationsIds[] = $variationOption->getListingProductVariationId();

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $listingProduct = $variationOption->getListingProduct();

            if ($variationOption->isComponentModeEbay()) {

                /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation $ebayVariation */
                $variation = $variationOption->getListingProductVariation();
                $ebayVariation = $variation->getChildObject();

                if (!$ebayVariation->isNotListed()) {
                    $additionalData = $listingProduct->getAdditionalData();
                    $variationsThatCanNotBeDeleted = isset($additionalData['variations_that_can_not_be_deleted'])
                        ? $additionalData['variations_that_can_not_be_deleted'] : array();

                    $specifics = array();

                    try {
                        foreach ($variation->getOptions(true) as $option) {
                            $specifics[$option->getAttribute()] = $option->getOption();
                        }

                        // @codingStandardsIgnoreLine
                    } catch (\Ess_M2ePro_Model_Exception_Logic $exception) {
                    }

                    $tempVariation = array(
                        'qty'       => 0,
                        'price'     => $ebayVariation->getOnlinePrice(),
                        'sku'       => $ebayVariation->getOnlineSku(),
                        'add'       => 0,
                        'delete'    => 1,
                        'specifics' => $specifics,
                        'has_sales' => true,
                    );

                    if ($ebayVariation->isDelete()) {
                        $tempVariation['sku'] = 'del-' . sha1(microtime(1) . $ebayVariation->getOnlineSku());
                    }

                    $specificsReplacements = $listingProduct->getChildObject()->getVariationSpecificsReplacements();
                    if (!empty($specificsReplacements)) {
                        $tempVariation['variations_specifics_replacements'] = $specificsReplacements;
                    }

                    $variationAdditionalData = $variation->getAdditionalData();
                    if (isset($variationAdditionalData['online_product_details'])) {
                        $tempVariation['details'] = $variationAdditionalData['online_product_details'];
                    }

                    $variationsThatCanNotBeDeleted[] = $tempVariation;
                    $additionalData['variations_that_can_not_be_deleted'] = $variationsThatCanNotBeDeleted;

                    $listingProduct->setSettings('additional_data', $additionalData)->save();
                }

                $variation->deleteInstance();
            } else {
                $listingProduct->deleteProcessingLocks();

                if ($listingProduct->isStoppable()) {
                    Mage::getModel('M2ePro/StopQueue')->add($listingProduct);

                    $listingProduct->setStatus(Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE)->save();
                }

                $listingsProductsForRemove[$listingProduct->getId()] = $listingProduct;
            }

            $listingId = $listingProduct->getListingId();
            $componentMode = $listingProduct->getComponentMode();

            if (isset($processedListings[$listingId . '_' . $componentMode])) {
                continue;
            }

            $processedListings[$listingId . '_' . $componentMode] = 1;

            $logModel = Mage::getModel('M2ePro/Listing_Log');
            $logModel->setComponentMode($componentMode);
            $actionId = $logModel->getResource()->getNextActionId();

            $logModel->addProductMessage(
                $listingId,
                $productId,
                $listingProduct->getId(),
                Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                $actionId,
                Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_MAGENTO,
                // M2ePro_TRANSLATIONS
                // Variation Option was deleted. Item was reset.
                'Variation Option was deleted. Item was reset.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING
            );
        }

        $parentListingProductsForRemove = array();

        foreach ($listingsProductsForRemove as $listingProduct) {
            if ($listingProduct->isComponentModeAmazon() || $listingProduct->isComponentModeWalmart()) {
                /** @var AmazonProduct|WalmartProduct $componentListingProduct */
                $componentListingProduct = $listingProduct->getChildObject();
                $variationManager = $componentListingProduct->getVariationManager();

                if ($variationManager->isRelationChildType()) {
                    /** @var AmazonProduct|WalmartProduct $parentProduct */
                    $parentProduct = $variationManager->getTypeModel()->getParentListingProduct()->getChildObject();
                    $listingProduct->deleteInstance();
                    $parentProduct->getVariationManager()->getTypeModel()->getProcessor()->process();
                    continue;
                }

                if ($variationManager->isVariationParent()) {
                    $parentListingProductsForRemove[] = $listingProduct;
                    continue;
                }
            }

            $listingProduct->deleteInstance();
        }

        foreach ($parentListingProductsForRemove as $listingProduct) {
            $listingProduct->deleteInstance();
        }

        // ---------------------------------------
    }

    /**
     * @return void
     * @throws \Ess_M2ePro_Model_Exception_Logic
     */
    public function deleteListingProductsForce()
    {
        $listingProducts = $this->getProducts(true);

        /**@var Ess_M2ePro_Model_Listing_Product $listingProduct */
        foreach ($listingProducts as $listingProduct) {
            $listingProduct->canBeForceDeleted(true);
            $listingProduct->deleteInstance();
        }
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('listing');

        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('listing');

        return parent::delete();
    }

    //########################################
}
