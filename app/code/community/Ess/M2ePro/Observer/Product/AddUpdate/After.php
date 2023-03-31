<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract as ChangeProcessorAbstract;

class Ess_M2ePro_Observer_Product_AddUpdate_After extends Ess_M2ePro_Observer_Product_AddUpdate_Abstract
{
    protected $_listingsProductsChangedAttributes = array();
    protected $_attributeAffectOnStoreIdCache     = array();

    //########################################

    public function beforeProcess()
    {
        parent::beforeProcess();

        if (!$this->isProxyExist()) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                'Before proxy should be defined earlier than after Action
                is performed.'
            );
        }

        if ($this->getProductId() <= 0) {
            throw new Ess_M2ePro_Model_Exception_Logic('Product ID should be defined for "after save" event.');
        }

        $this->reloadProduct();
    }

    // ---------------------------------------

    public function process()
    {
        if (!$this->isAddingProductProcess()) {
            $this->updateProductsNamesInLogs();

            if ($this->areThereAffectedItems()) {
                $this->performStatusChanges();
                $this->performPriceChanges();
                $this->performSpecialPriceChanges();
                $this->performSpecialPriceFromDateChanges();
                $this->performSpecialPriceToDateChanges();
                $this->performTierPriceChanges();
                $this->performTrackingAttributesChanges();
                $this->performDefaultQtyChanges();

                $this->addListingProductInstructions();

                $this->updateListingsProductsVariations();
            }
        } else {
            $this->performGlobalAutoActions();
        }

        $this->performWebsiteAutoActions();
        $this->performCategoryAutoActions();
    }

    //########################################

    protected function updateProductsNamesInLogs()
    {
        if (!$this->isAdminDefaultStoreId()) {
            return;
        }

        $name = $this->getProduct()->getName();

        if ($this->getProxy()->getData('name') == $name) {
            return;
        }

        Mage::getModel('M2ePro/Listing_Log')->getResource()->updateProductTitle($this->getProductId(), $name);
    }

    protected function updateListingsProductsVariations()
    {
        /** @var Ess_M2ePro_Model_Listing_Product_Variation_Updater[] $variationUpdatersByComponent */
        $variationUpdatersByComponent = array();

        /** @var Ess_M2ePro_Model_Listing_Product[] $listingsProductsForProcess */
        $listingsProductsForProcess   = array();

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {

            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */

            if (!isset($variationUpdatersByComponent[$listingProduct->getComponentMode()])) {
                $variationUpdaterModel = ucwords($listingProduct->getComponentMode())
                    .'_Listing_Product_Variation_Updater';
                /** @var Ess_M2ePro_Model_Listing_Product_Variation_Updater $variationUpdaterObject */
                $variationUpdaterObject = Mage::getModel('M2ePro/'.$variationUpdaterModel);
                $variationUpdatersByComponent[$listingProduct->getComponentMode()] = $variationUpdaterObject;
            }

            $listingsProductsForProcess[$listingProduct->getId()] = $listingProduct;
        }

        // for amazon and walmart, variation updater must not be called for parent and his children in one time
        foreach ($listingsProductsForProcess as $listingProduct) {
            if (!$listingProduct->isComponentModeAmazon() && !$listingProduct->isComponentModeWalmart()) {
                continue;
            }

            $channelListingProduct = $listingProduct->getChildObject();

            $variationManager = $channelListingProduct->getVariationManager();

            if ($variationManager->isRelationChildType() &&
                isset($listingsProductsForProcess[$variationManager->getVariationParentId()])) {
                unset($listingsProductsForProcess[$listingProduct->getId()]);
            }
        }

        foreach ($listingsProductsForProcess as $listingProduct) {
            $listingProduct->getMagentoProduct()->enableCache();

            $variationUpdater = $variationUpdatersByComponent[$listingProduct->getComponentMode()];
            $variationUpdater->process($listingProduct);
        }

        foreach ($variationUpdatersByComponent as $variationUpdater) {
            /** @var Ess_M2ePro_Model_Listing_Product_Variation_Updater $variationUpdater */
            $variationUpdater->afterMassProcessEvent();
        }

        foreach ($listingsProductsForProcess as $listingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            if ($listingProduct->isDeleted()) {
                continue;
            }

            $listingProduct->getMagentoProduct()->disableCache();
        }
    }

    //########################################

    protected function performStatusChanges()
    {
        $oldValue = (int)$this->getProxy()->getData('status');
        $newValue = (int)$this->getProduct()->getStatus();

        if ($oldValue == $newValue) {
            return;
        }

        $oldValue = ($oldValue == Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ? 'Enabled' : 'Disabled';
        $newValue = ($newValue == Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ? 'Enabled' : 'Disabled';

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {
            if (!$this->isAttributeAffectOnStoreId('status', $listingProduct->getListing()->getStoreId())) {
                continue;
            }

            $this->_listingsProductsChangedAttributes[$listingProduct->getId()][] = 'status';

            $this->logListingProductMessage(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_STATUS,
                $oldValue, $newValue
            );
        }
    }

    protected function performPriceChanges()
    {
        $oldValue = round((float)$this->getProxy()->getData('price'), 2);
        $newValue = round((float)$this->getProduct()->getPrice(), 2);

        if ($oldValue == $newValue) {
            return;
        }

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {
            $this->_listingsProductsChangedAttributes[$listingProduct->getId()][] = 'price';

            $this->logListingProductMessage(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_PRICE,
                $oldValue, $newValue
            );
        }
    }

    protected function performSpecialPriceChanges()
    {
        $oldValue = round((float)$this->getProxy()->getData('special_price'), 2);
        $newValue = round((float)$this->getProduct()->getSpecialPrice(), 2);

        if ($oldValue == $newValue) {
            return;
        }

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {
            $this->_listingsProductsChangedAttributes[$listingProduct->getId()][] = 'special_price';

            $this->logListingProductMessage(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE,
                $oldValue, $newValue
            );
        }
    }

    protected function performSpecialPriceFromDateChanges()
    {
        $oldValue = $this->getProxy()->getData('special_price_from_date');
        $newValue = $this->getProduct()->getSpecialFromDate();

        if ($oldValue == $newValue) {
            return;
        }

        ($oldValue === null || $oldValue === false || $oldValue == '') && $oldValue = 'None';
        ($newValue === null || $newValue === false || $newValue == '') && $newValue = 'None';

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {
            $this->_listingsProductsChangedAttributes[$listingProduct->getId()][] = 'special_price_from_date';

            $this->logListingProductMessage(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_FROM_DATE,
                $oldValue, $newValue
            );
        }
    }

    protected function performSpecialPriceToDateChanges()
    {
        $oldValue = $this->getProxy()->getData('special_price_to_date');
        $newValue = $this->getProduct()->getSpecialToDate();

        if ($oldValue == $newValue) {
            return;
        }

        ($oldValue === null || $oldValue === false || $oldValue == '') && $oldValue = 'None';
        ($newValue === null || $newValue === false || $newValue == '') && $newValue = 'None';

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {
            $this->_listingsProductsChangedAttributes[$listingProduct->getId()][] = 'special_price_to_date';

            $this->logListingProductMessage(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_TO_DATE,
                $oldValue, $newValue
            );
        }
    }

    protected function performTierPriceChanges()
    {
        $oldValue = $this->getProxy()->getData('tier_price');
        $newValue = $this->getProduct()->getTierPrice();

        if ($oldValue == $newValue) {
            return;
        }

        $oldValue = $this->convertTierPriceForLog($oldValue);
        $newValue = $this->convertTierPriceForLog($newValue);

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {
            $this->_listingsProductsChangedAttributes[$listingProduct->getId()][] = 'tier_price';

            $this->logListingProductMessage(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_TIER_PRICE,
                $oldValue, $newValue
            );
        }
    }

    // ---------------------------------------

    protected function performTrackingAttributesChanges()
    {
        foreach ($this->getProxy()->getAttributes() as $attributeCode => $attributeValue) {
            $oldValue = $attributeValue;
            $newValue = $this->getMagentoProduct()->getAttributeValue($attributeCode);

            if ($oldValue == $newValue) {
                continue;
            }

            foreach ($this->getAffectedListingsProductsByTrackingAttribute($attributeCode) as $listingProduct) {
                if (!$this->isAttributeAffectOnStoreId($attributeCode, $listingProduct->getListing()->getStoreId())) {
                    continue;
                }

                $this->_listingsProductsChangedAttributes[$listingProduct->getId()][] = $attributeCode;

                $this->logListingProductMessage(
                    $listingProduct,
                    Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_CUSTOM_ATTRIBUTE,
                    $oldValue, $newValue, 'of attribute "'.$attributeCode.'"'
                );
            }
        }
    }

    // ---------------------------------------

    protected function performDefaultQtyChanges()
    {
        if (!Mage::helper('M2ePro/Magento_Product')->isGroupedType($this->getProduct()->getTypeId())) {
            return;
        }

        $values = $this->getProxy()->getData('default_qty');
        foreach ($this->getProduct()->getTypeInstance()->getAssociatedProducts() as $childProduct) {

            $sku = $childProduct->getSku();
            $newValue = (int)$childProduct->getQty();
            $oldValue = isset($values[$sku]) ? (int)$values[$sku] : 0;

            unset($values[$sku]);
            if ($oldValue == $newValue) {
                continue;
            }

            foreach ($this->getAffectedListingsProducts() as $listingProduct) {

                $this->_listingsProductsChangedAttributes[$listingProduct->getId()][] = 'qty';

                $this->logListingProductMessage(
                    $listingProduct,
                    Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_QTY,
                    $oldValue,
                    $newValue,
                    "SKU {$sku}: Default QTY was changed."
                );
            }
        }

        //----------------------------------------

        foreach ($values as $sku => $defaultQty) {
            foreach ($this->getAffectedListingsProducts() as $listingProduct) {

                $this->_listingsProductsChangedAttributes[$listingProduct->getId()][] = 'qty';

                $this->logListingProductMessage(
                    $listingProduct,
                    Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_QTY,
                    $defaultQty,
                    0,
                    "SKU {$sku} was removed from the Product Set."
                );
            }
        }
    }

    // ---------------------------------------

    protected function addListingProductInstructions()
    {
        foreach ($this->getAffectedListingsProducts() as $listingProduct) {
            /** @var Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract $changeProcessor */
            $changeProcessor = Mage::getModel(
                'M2ePro/'.ucfirst($listingProduct->getComponentMode()).'_Magento_Product_ChangeProcessor'
            );
            $changeProcessor->setListingProduct($listingProduct);
            $changeProcessor->setDefaultInstructionTypes(
                array(
                ChangeProcessorAbstract::INSTRUCTION_TYPE_PRODUCT_STATUS_DATA_POTENTIALLY_CHANGED,
                ChangeProcessorAbstract::INSTRUCTION_TYPE_PRODUCT_QTY_DATA_POTENTIALLY_CHANGED,
                ChangeProcessorAbstract::INSTRUCTION_TYPE_PRODUCT_PRICE_DATA_POTENTIALLY_CHANGED,
                )
            );

            $changedAttributes = !empty($this->_listingsProductsChangedAttributes[$listingProduct->getId()]) ?
                $this->_listingsProductsChangedAttributes[$listingProduct->getId()] :
                array();

            $changeProcessor->process($changedAttributes);
        }
    }

    //########################################

    protected function performGlobalAutoActions()
    {
        /** @var Ess_M2ePro_Model_Listing_Auto_Actions_Mode_Global $object */
        $object = Mage::getModel('M2ePro/Listing_Auto_Actions_Mode_Global');
        $object->setProduct($this->getProduct());
        $object->synch();
    }

    protected function performWebsiteAutoActions()
    {
        /** @var Ess_M2ePro_Model_Listing_Auto_Actions_Mode_Website $object */
        $object = Mage::getModel('M2ePro/Listing_Auto_Actions_Mode_Website');
        $object->setProduct($this->getProduct());

        $websiteIdsOld = $this->getProxy()->getWebsiteIds();
        $websiteIdsNew = $this->getProduct()->getWebsiteIds();

        // website for admin values
        $this->isAddingProductProcess() && $websiteIdsNew[] = 0;

        $addedWebsiteIds = array_diff($websiteIdsNew, $websiteIdsOld);
        foreach ($addedWebsiteIds as $websiteId) {
            $object->synchWithAddedWebsiteId($websiteId);
        }

        $deletedWebsiteIds = array_diff($websiteIdsOld, $websiteIdsNew);
        foreach ($deletedWebsiteIds as $websiteId) {
            $object->synchWithDeletedWebsiteId($websiteId);
        }
    }

    protected function performCategoryAutoActions()
    {
        /** @var Ess_M2ePro_Model_Listing_Auto_Actions_Mode_Category $object */
        $object = Mage::getModel('M2ePro/Listing_Auto_Actions_Mode_Category');
        $object->setProduct($this->getProduct());

        $categoryIdsOld = $this->getProxy()->getCategoriesIds();
        $categoryIdsNew = $this->getProduct()->getCategoryIds();
        $addedCategories = array_diff($categoryIdsNew, $categoryIdsOld);
        $deletedCategories = array_diff($categoryIdsOld, $categoryIdsNew);

        $websiteIdsOld = $this->getProxy()->getWebsiteIds();
        $websiteIdsNew  = $this->getProduct()->getWebsiteIds();
        $addedWebsites = array_diff($websiteIdsNew, $websiteIdsOld);
        $deletedWebsites = array_diff($websiteIdsOld, $websiteIdsNew);

        $websitesChanges = array(
            // website for default store view
            0 => array(
                'added' => $addedCategories,
                'deleted' => $deletedCategories
            )
        );

        foreach (Mage::app()->getWebsites() as $website) {
            $websiteId = (int)$website->getId();

            $websiteChanges = array(
                'added' => array(),
                'deleted' => array()
            );

            // website has been enabled
            if (in_array($websiteId, $addedWebsites)) {
                $websiteChanges['added'] = $categoryIdsNew;
            // website is enabled
            } else if (in_array($websiteId, $websiteIdsNew)) {
                $websiteChanges['added'] = $addedCategories;
            }

            // website has been disabled
            if (in_array($websiteId, $deletedWebsites)) {
                $websiteChanges['deleted'] = $categoryIdsOld;
                // website is enabled
            } else if (in_array($websiteId, $websiteIdsNew)) {
                $websiteChanges['deleted'] = $deletedCategories;
            }

            $websitesChanges[$websiteId] = $websiteChanges;
        }

        foreach ($websitesChanges as $websiteId => $changes) {
            foreach ($changes['added'] as $categoryId) {
                $object->synchWithAddedCategoryId($categoryId, $websiteId);
            }

            foreach ($changes['deleted'] as $categoryId) {
                $object->synchWithDeletedCategoryId($categoryId, $websiteId);
            }
        }
    }

    //########################################

    protected function isAddingProductProcess()
    {
        return ($this->getProxy()->getProductId() <= 0 && $this->getProductId() > 0) ||
               (string)$this->getEvent()->getProduct()->getOrigData('sku') == '';
    }

    // ---------------------------------------

    protected function isProxyExist()
    {
        $key = $this->getProductId().'_'.$this->getStoreId();
        if (isset(Ess_M2ePro_Observer_Product_AddUpdate_Before::$proxyStorage[$key])) {
            return true;
        }

        $key = $this->getProduct()->getSku();
        return isset(Ess_M2ePro_Observer_Product_AddUpdate_Before::$proxyStorage[$key]);
    }

    /**
     * @return Ess_M2ePro_Observer_Product_AddUpdate_Before_Proxy
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getProxy()
    {
        if (!$this->isProxyExist()) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                'Before proxy should be defined earlier than after Action
                is performed.'
            );
        }

        $key = $this->getProductId().'_'.$this->getStoreId();
        if (isset(Ess_M2ePro_Observer_Product_AddUpdate_Before::$proxyStorage[$key])) {
            return Ess_M2ePro_Observer_Product_AddUpdate_Before::$proxyStorage[$key];
        }

        $key = $this->getProduct()->getSku();
        return Ess_M2ePro_Observer_Product_AddUpdate_Before::$proxyStorage[$key];
    }

    //########################################

    protected function isAttributeAffectOnStoreId($attributeCode, $onStoreId)
    {
        $cacheKey = $attributeCode.'_'.$onStoreId;

        if (isset($this->_attributeAffectOnStoreIdCache[$cacheKey])) {
            return $this->_attributeAffectOnStoreIdCache[$cacheKey];
        }

        $attributeInstance = Mage::getModel('eav/config')->getAttribute('catalog_product', $attributeCode);

        if (!($attributeInstance instanceof Mage_Catalog_Model_Resource_Eav_Attribute)) {
            return $this->_attributeAffectOnStoreIdCache[$cacheKey] = false;
        }

        $attributeScope = (int)$attributeInstance->getData('is_global');

        if ($attributeScope == Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL ||
            $this->getStoreId() == $onStoreId) {
            return $this->_attributeAffectOnStoreIdCache[$cacheKey] = true;
        }

        if ($this->getStoreId() == Mage_Core_Model_App::ADMIN_STORE_ID) {

            /** @var Mage_Catalog_Model_Product $productTemp */
            $productTemp = Mage::getModel('catalog/product')->setStoreId($onStoreId)
                                                            ->load($this->getProductId());

            return $this->_attributeAffectOnStoreIdCache[$cacheKey] =
                    ($productTemp->getAttributeDefaultValue($attributeCode) === false);
        }

        if ($attributeScope == Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE) {
            return $this->_attributeAffectOnStoreIdCache[$cacheKey] = false;
        }

        $affectedStoreIds = Mage::getModel('core/store')->load($this->getStoreId())->getWebsite()->getStoreIds();
        $affectedStoreIds = array_map('intval', array_values(array_unique($affectedStoreIds)));

        return $this->_attributeAffectOnStoreIdCache[$cacheKey] = in_array($onStoreId, $affectedStoreIds);
    }

    //########################################

    /**
     * @param $attributeCode
     * @return Ess_M2ePro_Model_Listing_Product[]
     */
    protected function getAffectedListingsProductsByTrackingAttribute($attributeCode)
    {
        $result = array();

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {
            /** @var Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract $changeProcessor */
            $changeProcessor = Mage::getModel(
                'M2ePro/'.ucfirst($listingProduct->getComponentMode()).'_Magento_Product_ChangeProcessor'
            );
            $changeProcessor->setListingProduct($listingProduct);

            if (in_array($attributeCode, $changeProcessor->getTrackingAttributes())) {
                $result[] = $listingProduct;
            }
        }

        return $result;
    }

    //########################################

    protected function convertTierPriceForLog($tierPrice)
    {
        if (empty($tierPrice) || !is_array($tierPrice)) {
            return 'None';
        }

        $result = array();
        foreach ($tierPrice as $tierPriceData) {
            $result[] = sprintf(
                "[price = %s, qty = %s]",
                $tierPriceData["website_price"],
                $tierPriceData["price_qty"]
            );
        }

        return implode(",", $result);
    }

    //########################################

    protected function logListingProductMessage(
        Ess_M2ePro_Model_Listing_Product $listingProduct,
        $action,
        $oldValue,
        $newValue,
        $messagePostfix = ''
    ) {
        $log = Mage::getModel('M2ePro/'.ucfirst($listingProduct->getComponentMode()).'_Listing_Log');
        $actionId = $log->getResource()->getNextActionId();

        $oldValue = strlen($oldValue) > 150 ? substr($oldValue, 0, 150) . ' ...' : $oldValue;
        $newValue = strlen($newValue) > 150 ? substr($newValue, 0, 150) . ' ...' : $newValue;

        $messagePostfix = trim(trim($messagePostfix), '.');
        if (!empty($messagePostfix)) {
            $messagePostfix = ' '.$messagePostfix;
        }

        if ($listingProduct->isComponentModeEbay() && is_array($listingProduct->getData('found_options_ids'))) {
            $collection = Mage::getModel('M2ePro/Listing_Product_Variation_Option')->getCollection()
                ->addFieldToFilter('main_table.id', array('in' => $listingProduct->getData('found_options_ids')));

            $additionalData = array();
            foreach ($collection as $listingProductVariationOption) {
                /** @var Ess_M2ePro_Model_Listing_Product_Variation_Option $listingProductVariationOption  */
                $additionalData['variation_options'][$listingProductVariationOption
                    ->getAttribute()] = $listingProductVariationOption->getOption();
            }

            if (!empty($additionalData['variation_options']) &&
                Mage::helper('M2ePro/Magento_Product')->isBundleType($collection->getFirstItem()->getProductType())) {
                foreach ($additionalData['variation_options'] as $attribute => $option) {
                    $log->addProductMessage(
                        $listingProduct->getListingId(),
                        $listingProduct->getProductId(),
                        $listingProduct->getId(),
                        Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                        $actionId,
                        $action,
                        Mage::helper('M2ePro/Module_Log')->encodeDescription(
                            'From [%from%] to [%to%]'.$messagePostfix.'.',
                            array('!from'=>$oldValue,'!to'=>$newValue)
                        ),
                        Ess_M2ePro_Model_Log_Abstract::TYPE_INFO,
                        array('variation_options' => array($attribute => $option))
                    );
                }

                return;
            }

            $log->addProductMessage(
                $listingProduct->getListingId(),
                $listingProduct->getProductId(),
                $listingProduct->getId(),
                Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                $actionId,
                $action,
                Mage::helper('M2ePro/Module_Log')->encodeDescription(
                    'From [%from%] to [%to%]'.$messagePostfix.'.',
                    array('!from'=>$oldValue,'!to'=>$newValue)
                ),
                Ess_M2ePro_Model_Log_Abstract::TYPE_INFO,
                $additionalData
            );

            return;
        }

        $log->addProductMessage(
            $listingProduct->getListingId(),
            $listingProduct->getProductId(),
            $listingProduct->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
            $actionId,
            $action,
            Mage::helper('M2ePro/Module_Log')->encodeDescription(
                'From [%from%] to [%to%]'.$messagePostfix.'.',
                array('!from'=>$oldValue,'!to'=>$newValue)
            ),
            Ess_M2ePro_Model_Log_Abstract::TYPE_INFO
        );
    }

    //########################################
}
