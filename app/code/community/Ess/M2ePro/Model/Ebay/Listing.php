<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Listing getParentObject()
 */
class Ess_M2ePro_Model_Ebay_Listing extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    const ADDING_MODE_ADD_AND_ASSIGN_CATEGORY = 2;

    const PARTS_COMPATIBILITY_MODE_EPIDS  = 'epids';
    const PARTS_COMPATIBILITY_MODE_KTYPES = 'ktypes';

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Category
     */
    protected $_autoGlobalAddingCategoryTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    protected $_autoGlobalAddingOtherCategoryTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Category
     */
    protected $_autoWebsiteAddingCategoryTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    protected $_autoWebsiteAddingOtherCategoryTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Manager[]
     */
    protected $_templateManagers = array();

    /**
     * @var Ess_M2ePro_Model_Template_SellingFormat
     */
    protected $_sellingFormatTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Template_Synchronization
     */
    protected $_synchronizationTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Template_Description
     */
    protected $_descriptionTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Payment
     */
    protected $_paymentTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Return
     */
    protected $_returnTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    protected $_shippingTemplateModel = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Listing');
    }

    //########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->_templateManagers                            = array();
        $this->_autoGlobalAddingCategoryTemplateModel       = null;
        $this->_autoGlobalAddingOtherCategoryTemplateModel  = null;
        $this->_autoWebsiteAddingCategoryTemplateModel      = null;
        $this->_autoWebsiteAddingOtherCategoryTemplateModel = null;
        $this->_sellingFormatTemplateModel                  = null;
        $this->_synchronizationTemplateModel                = null;
        $this->_descriptionTemplateModel                    = null;
        $this->_paymentTemplateModel                        = null;
        $this->_returnTemplateModel                         = null;
        $this->_shippingTemplateModel                       = null;

        $this->delete();
        return true;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category
     */
    public function getAutoGlobalAddingCategoryTemplate()
    {
        if ($this->_autoGlobalAddingCategoryTemplateModel === null) {
            try {
                $this->_autoGlobalAddingCategoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                    'Ebay_Template_Category', (int)$this->getAutoGlobalAddingTemplateCategoryId(),
                    null, array('template')
                );
            } catch (Exception $exception) {
                return $this->_autoGlobalAddingCategoryTemplateModel;
            }
        }

        return $this->_autoGlobalAddingCategoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Category $instance
     */
    public function setAutoGlobalAddingCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_Category $instance)
    {
         $this->_autoGlobalAddingCategoryTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    public function getAutoGlobalAddingOtherCategoryTemplate()
    {
        if ($this->_autoGlobalAddingOtherCategoryTemplateModel === null) {
            try {
                $this->_autoGlobalAddingOtherCategoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                    'Ebay_Template_OtherCategory', (int)$this->getAutoGlobalAddingTemplateOtherCategoryId(),
                    null, array('template')
                );
            } catch (Exception $exception) {
                return $this->_autoGlobalAddingOtherCategoryTemplateModel;
            }
        }

        return $this->_autoGlobalAddingOtherCategoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_OtherCategory $instance
     */
    public function setAutoGlobalAddingOtherCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_OtherCategory $instance)
    {
         $this->_autoGlobalAddingOtherCategoryTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category
     */
    public function getAutoWebsiteAddingCategoryTemplate()
    {
        if ($this->_autoWebsiteAddingCategoryTemplateModel === null) {
            try {
                $this->_autoWebsiteAddingCategoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                    'Ebay_Template_Category', (int)$this->getAutoWebsiteAddingTemplateCategoryId(),
                    null, array('template')
                );
            } catch (Exception $exception) {
                return $this->_autoWebsiteAddingCategoryTemplateModel;
            }
        }

        return $this->_autoWebsiteAddingCategoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Category $instance
     */
    public function setAutoWebsiteAddingCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_Category $instance)
    {
         $this->_autoWebsiteAddingCategoryTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    public function getAutoWebsiteAddingOtherCategoryTemplate()
    {
        if ($this->_autoWebsiteAddingOtherCategoryTemplateModel === null) {
            try {
                $this->_autoWebsiteAddingOtherCategoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                    'Ebay_Template_OtherCategory', (int)$this->getAutoWebsiteAddingTemplateOtherCategoryId(),
                    null, array('template')
                );
            } catch (Exception $exception) {
                return $this->_autoWebsiteAddingOtherCategoryTemplateModel;
            }
        }

        return $this->_autoWebsiteAddingOtherCategoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_OtherCategory $instance
     */
    public function setAutoWebsiteAddingOtherCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_OtherCategory $instance)
    {
         $this->_autoWebsiteAddingOtherCategoryTemplateModel = $instance;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        return $this->getParentObject()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Account
     */
    public function getEbayAccount()
    {
        return $this->getAccount()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        return $this->getParentObject()->getMarketplace();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Marketplace
     */
    public function getEbayMarketplace()
    {
        return $this->getMarketplace()->getChildObject();
    }

    //########################################

    /**
     * @param $template
     * @return Ess_M2ePro_Model_Ebay_Template_Manager
     */
    public function getTemplateManager($template)
    {
        if (!isset($this->_templateManagers[$template])) {
            /** @var Ess_M2ePro_Model_Ebay_Template_Manager $manager */
            $manager                            = Mage::getModel('M2ePro/Ebay_Template_Manager')->setOwnerObject($this);
            $this->_templateManagers[$template] = $manager->setTemplate($template);
        }

        return $this->_templateManagers[$template];
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        if ($this->_sellingFormatTemplateModel === null) {
            $template                          = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT;
            $this->_sellingFormatTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->_sellingFormatTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_SellingFormat $instance
     */
    public function setSellingFormatTemplate(Ess_M2ePro_Model_Template_SellingFormat $instance)
    {
         $this->_sellingFormatTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        if ($this->_synchronizationTemplateModel === null) {
            $template                            = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION;
            $this->_synchronizationTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->_synchronizationTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_Synchronization $instance
     */
    public function setSynchronizationTemplate(Ess_M2ePro_Model_Template_Synchronization $instance)
    {
         $this->_synchronizationTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Description
     */
    public function getDescriptionTemplate()
    {
        if ($this->_descriptionTemplateModel === null) {
            $template                        = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION;
            $this->_descriptionTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->_descriptionTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_Description $instance
     */
    public function setDescriptionTemplate(Ess_M2ePro_Model_Template_Description $instance)
    {
         $this->_descriptionTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Payment
     */
    public function getPaymentTemplate()
    {
        if ($this->_paymentTemplateModel === null) {
            $template                    = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT;
            $this->_paymentTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->_paymentTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Payment $instance
     */
    public function setPaymentTemplate(Ess_M2ePro_Model_Ebay_Template_Payment $instance)
    {
         $this->_paymentTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Return
     */
    public function getReturnTemplate()
    {
        if ($this->_returnTemplateModel === null) {
            $template                   = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN;
            $this->_returnTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->_returnTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Return $instance
     */
    public function setReturnTemplate(Ess_M2ePro_Model_Ebay_Template_Return $instance)
    {
         $this->_returnTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    public function getShippingTemplate()
    {
        if ($this->_shippingTemplateModel === null) {
            $template                     = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING;
            $this->_shippingTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->_shippingTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Shipping $instance
     */
    public function setShippingTemplate(Ess_M2ePro_Model_Ebay_Template_Shipping $instance)
    {
         $this->_shippingTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_SellingFormat
     */
    public function getEbaySellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Synchronization
     */
    public function getEbaySynchronizationTemplate()
    {
        return $this->getSynchronizationTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Description
     */
    public function getEbayDescriptionTemplate()
    {
        return $this->getDescriptionTemplate()->getChildObject();
    }

    //########################################

    public function getProducts($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getProducts($asObjects, $filters);
    }

    //########################################

    public function getAutoGlobalAddingTemplateCategoryId()
    {
        return $this->getData('auto_global_adding_template_category_id');
    }

    public function getAutoGlobalAddingTemplateOtherCategoryId()
    {
        return $this->getData('auto_global_adding_template_other_category_id');
    }

    // ---------------------------------------

    public function isAutoGlobalAddingModeAddAndAssignCategory()
    {
        return $this->getParentObject()->getAutoGlobalAddingMode() == self::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY;
    }

    //########################################

    public function getAutoWebsiteAddingTemplateCategoryId()
    {
        return $this->getData('auto_website_adding_template_category_id');
    }

    public function getAutoWebsiteAddingTemplateOtherCategoryId()
    {
        return $this->getData('auto_website_adding_template_other_category_id');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isAutoWebsiteAddingModeAddAndAssignCategory()
    {
        return $this->getParentObject()->getAutoWebsiteAddingMode() == self::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY;
    }

    //########################################

    public function gePartsCompatibilityMode()
    {
        return $this->getData('parts_compatibility_mode');
    }

    public function isPartsCompatibilityModeKtypes()
    {
        if ($this->getEbayMarketplace()->isMultiMotorsEnabled()) {
            return $this->gePartsCompatibilityMode() == self::PARTS_COMPATIBILITY_MODE_KTYPES ||
                   $this->gePartsCompatibilityMode() === null;
        }

        return $this->getEbayMarketplace()->isKtypeEnabled();
    }

    public function isPartsCompatibilityModeEpids()
    {
        if ($this->getEbayMarketplace()->isMultiMotorsEnabled()) {
            return $this->gePartsCompatibilityMode() == self::PARTS_COMPATIBILITY_MODE_EPIDS;
        }

        return $this->getEbayMarketplace()->isEpidEnabled();
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Other $listingOtherProduct
     * @param int $initiator
     * @param bool $checkingMode
     * @param bool $checkHasProduct
     * @return bool|Ess_M2ePro_Model_Listing_Product
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function addProductFromOther(
        Ess_M2ePro_Model_Listing_Other $listingOtherProduct,
        $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
        $checkingMode = false,
        $checkHasProduct = true
    ) {
        if (!$listingOtherProduct->getProductId()) {
            return false;
        }

        $productId = $listingOtherProduct->getProductId();
        $result = $this->getParentObject()->addProduct($productId, $initiator, $checkingMode, true);

        if ($checkingMode) {
            return $result;
        }

        if (!($result instanceof Ess_M2ePro_Model_Listing_Product)) {
            return false;
        }

        $listingProduct = $result;

        /** @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Ebay_Item')->getCollection()
            ->addFieldToFilter('account_id', $listingOtherProduct->getAccount()->getId())
            ->addFieldToFilter('item_id', $listingOtherProduct->getChildObject()->getItemId());

        $ebayItem = $collection->getLastItem();
        if (!$ebayItem->getId()) {
            $ebayItem->setData(
                array(
                'account_id'     => $listingOtherProduct->getAccount()->getId(),
                'marketplace_id' => $listingOtherProduct->getMarketplace()->getId(),
                'item_id'        => $listingOtherProduct->getChildObject()->getItemId(),
                'product_id'     => $listingOtherProduct->getProductId(),
                )
            );
        }

        $ebayItem->setData('store_id', $this->getParentObject()->getStoreId())
                 ->save();

        /** @var Ess_M2ePro_Model_Ebay_Listing_Other $ebayListingProduct */
        $ebayListingProduct = $listingOtherProduct->getChildObject();

        $dataForUpdate = array(
            'ebay_item_id'         => $ebayItem->getId(),

            'online_sku'           => $ebayListingProduct->getSku(),
            'online_title'         => $ebayListingProduct->getTitle(),
            'online_duration'      => $ebayListingProduct->getOnlineDuration(),
            'online_current_price' => $ebayListingProduct->getOnlinePrice(),
            'online_qty'           => $ebayListingProduct->getOnlineQty(),
            'online_qty_sold'      => $ebayListingProduct->getOnlineQtySold(),
            'online_bids'          => $ebayListingProduct->getOnlineBids(),
            'start_date'           => $ebayListingProduct->getStartDate(),
            'end_date'             => $ebayListingProduct->getEndDate(),

            'status'               => $listingOtherProduct->getStatus(),
            'status_changer'       => $listingOtherProduct->getStatusChanger()
        );

        $listingOtherAdditionalData = $listingOtherProduct->getAdditionalData();

        if (!empty($listingOtherAdditionalData['out_of_stock_control'])) {
            $listingProductAdditionalData = $listingProduct->getAdditionalData();
            $additionalDataForUpdate = array_merge(
                $listingProductAdditionalData, array('out_of_stock_control' => true)
            );
            $dataForUpdate['additional_data'] = Mage::helper('M2ePro')->jsonEncode($additionalDataForUpdate);
        }

        $listingProduct->addData($dataForUpdate);
        $listingProduct->setSetting(
            'additional_data', $listingProduct::MOVING_LISTING_OTHER_SOURCE_KEY, $listingOtherProduct->getId()
        );
        $listingProduct->save();

        $listingOtherProduct->setSetting(
            'additional_data', $listingOtherProduct::MOVING_LISTING_PRODUCT_DESTINATION_KEY, $listingProduct->getId()
        );
        $listingOtherProduct->save();

        $instruction = Mage::getModel('M2ePro/Listing_Product_Instruction');
        $instruction->setData(
            array(
            'listing_product_id' => $listingProduct->getId(),
            'component'          => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'type'               => Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            'initiator'          => Ess_M2ePro_Model_Listing::INSTRUCTION_INITIATOR_MOVING_PRODUCT_FROM_OTHER,
            'priority'           => 20,
            )
        );
        $instruction->save();

        return $listingProduct;
    }

    public function addProductFromAnotherEbaySite(
        Ess_M2ePro_Model_Listing_Product $sourceListingProduct,
        Ess_M2ePro_Model_Listing $sourceListing
    ){
        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = $this->getParentObject()->addProduct(
            $sourceListingProduct->getProductId(),
            Ess_M2ePro_Helper_Data::INITIATOR_USER
        );

        $logModel = Mage::getModel('M2ePro/Listing_Log');
        $logModel->setComponentMode($this->getComponentMode());
        $actionId = $logModel->getResource()->getNextActionId();

        if ($listingProduct instanceof Ess_M2ePro_Model_Listing_Product) {
            $logModel->addProductMessage(
                $sourceListing->getId(),
                $sourceListingProduct->getProductId(),
                $sourceListingProduct->getId(),
                Ess_M2ePro_Helper_Data::INITIATOR_USER,
                $actionId,
                Ess_M2ePro_Model_Listing_Log::ACTION_SELL_ON_ANOTHER_EBAY_SITE,
                'Item was added to the selected Listing',
                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return $listingProduct;
        }

        $logModel->addProductMessage(
            $sourceListing->getId(),
            $sourceListingProduct->getProductId(),
            $sourceListingProduct->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_USER,
            $actionId,
            Ess_M2ePro_Model_Listing_Log::ACTION_SELL_ON_ANOTHER_EBAY_SITE,
            'Product already exists in the selected Listing',
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
        );

        return false;
    }

    public function addProductFromListing(
        Ess_M2ePro_Model_Listing_Product $listingProduct,
        Ess_M2ePro_Model_Listing $sourceListing
    ){
        if (!$this->getParentObject()->addProductFromListing($listingProduct, $sourceListing, true)) {
            return false;
        }

        if ($this->getParentObject()->getStoreId() != $sourceListing->getStoreId()) {
            if (!$listingProduct->isNotListed()) {
                if ($item = $listingProduct->getChildObject()->getEbayItem()) {
                    $item->setData('store_id', $this->getParentObject()->getStoreId());
                    $item->save();
                }
            }
        }

        return true;
    }

    //########################################

    public function getEstimatedFees()
    {
        return $this->getParentObject()
            ->getSetting('additional_data', array('estimated_fees', 'data'), array());
    }

    public function setEstimatedFees(array $data)
    {
        $this->getParentObject()
            ->setSetting('additional_data', array('estimated_fees', 'data'), $data);
        return $this;
    }

    public function getEstimatedFeesSourceProductName()
    {
        return $this->getParentObject()
            ->getSetting('additional_data', array('estimated_fees', 'source_product_name'), null);
    }

    public function setEstimatedFeesSourceProductName($name)
    {
        $this->getParentObject()
            ->setSetting('additional_data', array('estimated_fees', 'source_product_name'), $name);
        return $this;
    }

    public function getEstimatedFeesObtainAttemptCount()
    {
        return $this->getParentObject()
            ->getSetting('additional_data', array('estimated_fees', 'obtain_attempt_count'), 0);
    }

    public function setEstimatedFeesObtainAttemptCount($count)
    {
        $this->getParentObject()
            ->setSetting('additional_data', array('estimated_fees', 'obtain_attempt_count'), $count);
        return $this;
    }

    public function getEstimatedFeesObtainRequired()
    {
        return $this->getParentObject()
            ->getSetting('additional_data', array('estimated_fees', 'obtain_required'), true);
    }

    public function setEstimatedFeesObtainRequired($required)
    {
        $this->getParentObject()
            ->setSetting('additional_data', array('estimated_fees', 'obtain_required'), (bool)$required);
        return $this;
    }

    //########################################

    public function increaseEstimatedFeesObtainAttemptCount()
    {
        $count = $this->getEstimatedFeesObtainAttemptCount();
        $this->setEstimatedFeesObtainAttemptCount(++$count);
        $this->getParentObject()->save();
    }

    /**
     * @return bool
     */
    public function isEstimatedFeesObtainRequired()
    {
        if (!$this->getEstimatedFeesObtainRequired()) {
            return false;
        }

        if ($this->getEstimatedFeesObtainAttemptCount() >= 3) {
            return false;
        }

        return true;
    }

    //########################################

    /**
     * @return array
     */
    public function getAddedListingProductsIds()
    {
        $ids = $this->getData('product_add_ids');
        $ids = array_filter((array)Mage::helper('M2ePro')->jsonDecode($ids));
        return array_values(array_unique($ids));
    }

    //########################################

    public function updateLastPrimaryCategory($path,$data)
    {
        $settings = $this->getParentObject()->getSettings('additional_data');
        $temp = &$settings;

        foreach ($path as $i => $part) {
            if (!array_key_exists($part, $temp)) {
                $temp[$part] = array();
            }

            if ($i == count($path) - 1) {
                $temp[$part] = $data;
            }

            $temp = &$temp[$part];
        }

        $this->getParentObject()->setSettings('additional_data', $settings)->save();
    }

    public function getLastPrimaryCategory($key)
    {
        return (array)$this->getSetting('additional_data', $key);
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
