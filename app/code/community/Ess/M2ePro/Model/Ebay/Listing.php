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
    private $autoGlobalAddingCategoryTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    private $autoGlobalAddingOtherCategoryTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Category
     */
    private $autoWebsiteAddingCategoryTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    private $autoWebsiteAddingOtherCategoryTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Manager[]
     */
    private $templateManagers = array();

    // ---------------------------------------

    /**
     * @var Ess_M2ePro_Model_Template_SellingFormat
     */
    private $sellingFormatTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Template_Synchronization
     */
    private $synchronizationTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Template_Description
     */
    private $descriptionTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Payment
     */
    private $paymentTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Return
     */
    private $returnTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    private $shippingTemplateModel = NULL;

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

        $this->templateManagers = array();
        $this->autoGlobalAddingCategoryTemplateModel = NULL;
        $this->autoGlobalAddingOtherCategoryTemplateModel = NULL;
        $this->autoWebsiteAddingCategoryTemplateModel = NULL;
        $this->autoWebsiteAddingOtherCategoryTemplateModel = NULL;
        $this->sellingFormatTemplateModel = NULL;
        $this->synchronizationTemplateModel = NULL;
        $this->descriptionTemplateModel = NULL;
        $this->paymentTemplateModel = NULL;
        $this->returnTemplateModel = NULL;
        $this->shippingTemplateModel = NULL;

        $this->delete();
        return true;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category
     */
    public function getAutoGlobalAddingCategoryTemplate()
    {
        if (is_null($this->autoGlobalAddingCategoryTemplateModel)) {

            try {
                $this->autoGlobalAddingCategoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                    'Ebay_Template_Category', (int)$this->getAutoGlobalAddingTemplateCategoryId(),
                    NULL, array('template')
                );
            } catch (Exception $exception) {
                return $this->autoGlobalAddingCategoryTemplateModel;
            }
        }

        return $this->autoGlobalAddingCategoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Category $instance
     */
    public function setAutoGlobalAddingCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_Category $instance)
    {
         $this->autoGlobalAddingCategoryTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    public function getAutoGlobalAddingOtherCategoryTemplate()
    {
        if (is_null($this->autoGlobalAddingOtherCategoryTemplateModel)) {

            try {
                $this->autoGlobalAddingOtherCategoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                    'Ebay_Template_OtherCategory', (int)$this->getAutoGlobalAddingTemplateOtherCategoryId(),
                    NULL, array('template')
                );
            } catch (Exception $exception) {
                return $this->autoGlobalAddingOtherCategoryTemplateModel;
            }
        }

        return $this->autoGlobalAddingOtherCategoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_OtherCategory $instance
     */
    public function setAutoGlobalAddingOtherCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_OtherCategory $instance)
    {
         $this->autoGlobalAddingOtherCategoryTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category
     */
    public function getAutoWebsiteAddingCategoryTemplate()
    {
        if (is_null($this->autoWebsiteAddingCategoryTemplateModel)) {

            try {
                $this->autoWebsiteAddingCategoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                    'Ebay_Template_Category', (int)$this->getAutoWebsiteAddingTemplateCategoryId(),
                    NULL, array('template')
                );
            } catch (Exception $exception) {
                return $this->autoWebsiteAddingCategoryTemplateModel;
            }
        }

        return $this->autoWebsiteAddingCategoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Category $instance
     */
    public function setAutoWebsiteAddingCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_Category $instance)
    {
         $this->autoWebsiteAddingCategoryTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    public function getAutoWebsiteAddingOtherCategoryTemplate()
    {
        if (is_null($this->autoWebsiteAddingOtherCategoryTemplateModel)) {

            try {
                $this->autoWebsiteAddingOtherCategoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                    'Ebay_Template_OtherCategory', (int)$this->getAutoWebsiteAddingTemplateOtherCategoryId(),
                    NULL, array('template')
                );
            } catch (Exception $exception) {
                return $this->autoWebsiteAddingOtherCategoryTemplateModel;
            }
        }

        return $this->autoWebsiteAddingOtherCategoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_OtherCategory $instance
     */
    public function setAutoWebsiteAddingOtherCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_OtherCategory $instance)
    {
         $this->autoWebsiteAddingOtherCategoryTemplateModel = $instance;
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
        if (!isset($this->templateManagers[$template])) {
            /** @var Ess_M2ePro_Model_Ebay_Template_Manager $manager */
            $manager = Mage::getModel('M2ePro/Ebay_Template_Manager')->setOwnerObject($this);
            $this->templateManagers[$template] = $manager->setTemplate($template);
        }

        return $this->templateManagers[$template];
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        if (is_null($this->sellingFormatTemplateModel)) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT;
            $this->sellingFormatTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->sellingFormatTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_SellingFormat $instance
     */
    public function setSellingFormatTemplate(Ess_M2ePro_Model_Template_SellingFormat $instance)
    {
         $this->sellingFormatTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        if (is_null($this->synchronizationTemplateModel)) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION;
            $this->synchronizationTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->synchronizationTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_Synchronization $instance
     */
    public function setSynchronizationTemplate(Ess_M2ePro_Model_Template_Synchronization $instance)
    {
         $this->synchronizationTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Description
     */
    public function getDescriptionTemplate()
    {
        if (is_null($this->descriptionTemplateModel)) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION;
            $this->descriptionTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->descriptionTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_Description $instance
     */
    public function setDescriptionTemplate(Ess_M2ePro_Model_Template_Description $instance)
    {
         $this->descriptionTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Payment
     */
    public function getPaymentTemplate()
    {
        if (is_null($this->paymentTemplateModel)) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT;
            $this->paymentTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->paymentTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Payment $instance
     */
    public function setPaymentTemplate(Ess_M2ePro_Model_Ebay_Template_Payment $instance)
    {
         $this->paymentTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Return
     */
    public function getReturnTemplate()
    {
        if (is_null($this->returnTemplateModel)) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN;
            $this->returnTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->returnTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Return $instance
     */
    public function setReturnTemplate(Ess_M2ePro_Model_Ebay_Template_Return $instance)
    {
         $this->returnTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    public function getShippingTemplate()
    {
        if (is_null($this->shippingTemplateModel)) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING;
            $this->shippingTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->shippingTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Shipping $instance
     */
    public function setShippingTemplate(Ess_M2ePro_Model_Ebay_Template_Shipping $instance)
    {
         $this->shippingTemplateModel = $instance;
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
        return $this->getParentObject()->getProducts($asObjects,$filters);
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
                   is_null($this->gePartsCompatibilityMode());
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
    public function addProductFromOther(Ess_M2ePro_Model_Listing_Other $listingOtherProduct,
                                        $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
                                        $checkingMode = false,
                                        $checkHasProduct = true)
    {
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

        /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Ebay_Item')->getCollection()
            ->addFieldToFilter('account_id', $listingOtherProduct->getAccount()->getId())
            ->addFieldToFilter('item_id', $listingOtherProduct->getChildObject()->getItemId());

        $ebayItem = $collection->getLastItem();
        if (!$ebayItem->getId()) {

            $ebayItem->setData(array(
                'account_id'     => $listingOtherProduct->getAccount()->getId(),
                'marketplace_id' => $listingOtherProduct->getMarketplace()->getId(),
                'item_id'        => $listingOtherProduct->getChildObject()->getItemId(),
                'product_id'     => $listingOtherProduct->getProductId(),
            ));
        }
        $ebayItem->setData('store_id',$this->getParentObject()->getStoreId())
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
        $instruction->setData(array(
            'listing_product_id' => $listingProduct->getId(),
            'component'          => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'type'               => Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            'initiator'          => Ess_M2ePro_Model_Listing::INSTRUCTION_INITIATOR_MOVING_PRODUCT_FROM_OTHER,
            'priority'           => 20,
        ));
        $instruction->save();

        return $listingProduct;
    }

    public function addProductFromAnotherEbaySite(Ess_M2ePro_Model_Listing_Product $sourceListingProduct,
                                                  Ess_M2ePro_Model_Listing $sourceListing
    ){
        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = $this->getParentObject()->addProduct(
            $sourceListingProduct->getProductId(),
            Ess_M2ePro_Helper_Data::INITIATOR_USER
        );

        $logModel = Mage::getModel('M2ePro/Listing_Log');
        $logModel->setComponentMode($this->getComponentMode());

        if ($listingProduct instanceof Ess_M2ePro_Model_Listing_Product) {

            $logModel->addProductMessage(
                $sourceListing->getId(),
                $sourceListingProduct->getProductId(),
                $sourceListingProduct->getId(),
                Ess_M2ePro_Helper_Data::INITIATOR_USER,
                NULL,
                Ess_M2ePro_Model_Listing_Log::ACTION_SELL_ON_ANOTHER_EBAY_SITE,
                // M2ePro_TRANSLATIONS
                // Item was successfully Added into Destination Listing
                'Item was successfully Added into Destination Listing',
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
            NULL,
            Ess_M2ePro_Model_Listing_Log::ACTION_SELL_ON_ANOTHER_EBAY_SITE,
            // M2ePro_TRANSLATIONS
            // Product already exists in the selected Listing
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
            ->getSetting('additional_data', array('estimated_fees', 'source_product_name'), NULL);
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

            if (!array_key_exists($part,$temp)) {
                $temp[$part] = array();
            }

            if ($i == count($path) - 1) {
                $temp[$part] = $data;
            }

            $temp = &$temp[$part];
        }

        $this->getParentObject()->setSettings('additional_data',$settings)->save();
    }

    public function getLastPrimaryCategory($key)
    {
        return (array)$this->getSetting('additional_data',$key);
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