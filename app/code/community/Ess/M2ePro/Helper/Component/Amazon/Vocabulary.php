<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Amazon_Vocabulary extends Mage_Core_Helper_Abstract
{
    const VOCABULARY_AUTO_ACTION_NOT_SET = 0;
    const VOCABULARY_AUTO_ACTION_YES = 1;
    const VOCABULARY_AUTO_ACTION_NO = 2;

    const VALUE_TYPE_ATTRIBUTE = 'attribute';
    const VALUE_TYPE_OPTION    = 'option';

    const LOCAL_DATA_REGISTRY_KEY  = 'amazon_vocabulary_local';
    const SERVER_DATA_REGISTRY_KEY = 'amazon_vocabulary_server';

    //########################################

    public function addAttribute($productAttribute, $channelAttribute)
    {
        if ($productAttribute == $channelAttribute) {
            return;
        }

        $needRunProcessors = false;

        if (!$this->isAttributeExistsInLocalStorage($productAttribute, $channelAttribute)) {
            $this->addAttributeToLocalStorage($productAttribute, $channelAttribute);
            $needRunProcessors = true;
        }

        if (!$this->isAttributeExistsInServerStorage($productAttribute, $channelAttribute)) {
            $this->addAttributeToServerStorage($productAttribute, $channelAttribute);
            $needRunProcessors = true;
        }

        if (!$needRunProcessors) {
            return;
        }

        $affectedParentListingsProducts = $this->getParentListingsProductsAffectedToAttribute($channelAttribute);
        if (empty($affectedParentListingsProducts)) {
            return;
        }

        $massProcessor = Mage::getModel(
            'M2ePro/Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Mass'
        );
        $massProcessor->setListingsProducts($affectedParentListingsProducts);
        $massProcessor->setForceExecuting(false);

        $massProcessor->execute();
    }

    // ---------------------------------------

    public function addAttributeToLocalStorage($productAttribute, $channelAttribute)
    {
        /** @var Ess_M2ePro_Model_Registry $registry */
        $registry = Mage::getModel('M2ePro/Registry')->load(self::LOCAL_DATA_REGISTRY_KEY, 'key');

        $vocabularyData = $registry->getSettings('value');
        $vocabularyData[$channelAttribute]['names'][] = $productAttribute;

        if (!isset($vocabularyData[$channelAttribute]['options'])) {
            $vocabularyData[$channelAttribute]['options'] = array();
        }

        $registry->setData('key', self::LOCAL_DATA_REGISTRY_KEY);
        $registry->setSettings('value', $vocabularyData)->save();

        $this->removeLocalDataCache();
    }

    public function addAttributeToServerStorage($productAttribute, $channelAttribute)
    {
        try {

            /** @var $dispatcherObject Ess_M2ePro_Model_Connector_Amazon_Dispatcher */
            $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'product','add','vocabulary',
                array(
                    'type'     => self::VALUE_TYPE_ATTRIBUTE,
                    'original' => $channelAttribute,
                    'value'    => $productAttribute
                )
            );

            $dispatcherObject->process($connectorObj);

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }
    }

    // ---------------------------------------

    public function removeAttributeFromLocalStorage($productAttribute, $channelAttribute)
    {
        /** @var Ess_M2ePro_Model_Registry $registry */
        $registry = Mage::getModel('M2ePro/Registry')->load(self::LOCAL_DATA_REGISTRY_KEY, 'key');

        $vocabularyData = $registry->getSettings('value');
        if (empty($vocabularyData[$channelAttribute]['names'])) {
            return;
        }

        if (($nameKey = array_search($productAttribute, $vocabularyData[$channelAttribute]['names'])) === false) {
            return;
        }

        unset($vocabularyData[$channelAttribute]['names'][$nameKey]);

        $vocabularyData[$channelAttribute]['names'] = array_values($vocabularyData[$channelAttribute]['names']);

        $registry->setData('key', self::LOCAL_DATA_REGISTRY_KEY);
        $registry->setSettings('value', $vocabularyData)->save();

        $this->removeLocalDataCache();
    }

    // ---------------------------------------

    public function isAttributeExistsInLocalStorage($productAttribute, $channelAttribute)
    {
        return $this->isAttributeExists(
            $productAttribute, $channelAttribute, $this->getLocalData()
        );
    }

    public function isAttributeExistsInServerStorage($productAttribute, $channelAttribute)
    {
        return $this->isAttributeExists(
            $productAttribute, $channelAttribute, $this->getServerData()
        );
    }

    // ---------------------------------------

    public function isAttributeExists($productAttribute, $channelAttribute, $vocabularyData)
    {
        if (empty($vocabularyData[$channelAttribute]['names'])) {
            return false;
        }

        if (!in_array($productAttribute, $vocabularyData[$channelAttribute]['names'])) {
            return false;
        }

        return true;
    }

    // ---------------------------------------

    public function isAttributeAutoActionNotSet()
    {
        return !$this->isAttributeAutoActionEnabled() && !$this->isAttributeAutoActionDisabled();
    }

    public function isAttributeAutoActionEnabled()
    {
        $configValue = $this->getConfigValue('/amazon/vocabulary/attribute/auto_action/', 'enabled');
        if (is_null($configValue)) {
            return false;
        }

        return (bool)$configValue;
    }

    public function isAttributeAutoActionDisabled()
    {
        $configValue = $this->getConfigValue('/amazon/vocabulary/attribute/auto_action/', 'enabled');
        if (is_null($configValue)) {
            return false;
        }

        return !(bool)$configValue;
    }

    // ---------------------------------------

    public function enableAttributeAutoAction()
    {
        return $this->setConfigValue('/amazon/vocabulary/attribute/auto_action/', 'enabled', 1);
    }

    public function disableAttributeAutoAction()
    {
        return $this->setConfigValue('/amazon/vocabulary/attribute/auto_action/', 'enabled', 0);
    }

    public function unsetAttributeAutoAction()
    {
        return $this->unsetConfigValue('/amazon/vocabulary/attribute/auto_action/', 'enabled');
    }

    //########################################

    public function addOption($productOption, $channelOption, $channelAttribute)
    {
        if ($productOption == $channelOption) {
            return;
        }

        $needRunProcessors = false;

        if (!$this->isOptionExistsInLocalStorage($productOption, $channelOption, $channelAttribute)) {
            $this->addOptionToLocalStorage($productOption, $channelOption, $channelAttribute);
            $needRunProcessors = true;
        }

        if (!$this->isOptionExistsInServerStorage($productOption, $channelOption, $channelAttribute)) {
            $this->addOptionToServerStorage($productOption, $channelOption, $channelAttribute);
            $needRunProcessors = true;
        }

        if (!$needRunProcessors) {
            return;
        }

        $affectedParentListingsProducts = $this->getParentListingsProductsAffectedToOption(
            $channelAttribute, $channelOption
        );

        if (empty($affectedParentListingsProducts)) {
            return;
        }

        $massProcessor = Mage::getModel(
            'M2ePro/Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Mass'
        );
        $massProcessor->setListingsProducts($affectedParentListingsProducts);
        $massProcessor->setForceExecuting(false);

        $massProcessor->execute();
    }

    // ---------------------------------------

    public function addOptionToLocalStorage($productOption, $channelOption, $channelAttribute)
    {
        /** @var Ess_M2ePro_Model_Registry $registry */
        $registry = Mage::getModel('M2ePro/Registry')->load(self::LOCAL_DATA_REGISTRY_KEY, 'key');

        $vocabularyData = $registry->getSettings('value');

        if (!isset($vocabularyData[$channelAttribute]['names'])) {
            $vocabularyData[$channelAttribute]['names'] = array();
        }

        if (!isset($vocabularyData[$channelAttribute]['options'])) {
            $vocabularyData[$channelAttribute]['options'] = array();
        }

        $isAdded = false;
        foreach ($vocabularyData[$channelAttribute]['options'] as &$options) {
            if (!in_array($channelOption, $options)) {
                continue;
            }

            $options[] = $productOption;
            $isAdded = true;
        }

        if (!$isAdded) {
            $vocabularyData[$channelAttribute]['options'][] = array(
                $channelOption,
                $productOption,
            );
        }

        $registry->setData('key', self::LOCAL_DATA_REGISTRY_KEY);
        $registry->setSettings('value', $vocabularyData)->save();

        $this->removeLocalDataCache();
    }

    public function addOptionToServerStorage($productOption, $channelOption, $channelAttribute)
    {
        try {

            /** @var $dispatcherObject Ess_M2ePro_Model_Connector_Amazon_Dispatcher */
            $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'product','add','vocabulary',
                array(
                    'type'      => self::VALUE_TYPE_ATTRIBUTE,
                    'attribute' => $channelAttribute,
                    'original'  => $channelOption,
                    'value'     => $productOption
                )
            );

            $dispatcherObject->process($connectorObj);

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }
    }

    // ---------------------------------------

    public function removeOptionFromLocalStorage($productOption, $productOptionsGroup, $channelAttribute)
    {
        /** @var Ess_M2ePro_Model_Registry $registry */
        $registry = Mage::getModel('M2ePro/Registry')->load(self::LOCAL_DATA_REGISTRY_KEY, 'key');

        $vocabularyData = $registry->getSettings('value');
        if (empty($vocabularyData[$channelAttribute]['options'])) {
            return;
        }

        foreach ($vocabularyData[$channelAttribute]['options'] as $optionsGroupKey => &$options) {

            $comparedOptions = array_diff($productOptionsGroup, $options);
            $nameKey = array_search($productOption, $options);

            if (empty($comparedOptions) && $nameKey !== false) {

                unset($options[$nameKey]);

                $vocabularyData[$channelAttribute]['options'][$optionsGroupKey] = array_values($options);

                if (count($options) == 1) {
                    unset($vocabularyData[$channelAttribute]['options'][$optionsGroupKey]);
                }
                break;
            }
        }

        $registry->setData('key', self::LOCAL_DATA_REGISTRY_KEY);
        $registry->setSettings('value', $vocabularyData)->save();

        $this->removeLocalDataCache();
    }

    // ---------------------------------------

    public function isOptionExistsInLocalStorage($productOption, $channelOption, $channelAttribute)
    {
        return $this->isOptionExists(
            $productOption, $channelOption, $channelAttribute, $this->getLocalData()
        );
    }

    public function isOptionExistsInServerStorage($productOption, $channelOption, $channelAttribute)
    {
        return $this->isOptionExists(
            $productOption, $channelOption, $channelAttribute, $this->getServerData()
        );
    }

    // ---------------------------------------

    public function isOptionExists($productOption, $channelOption, $channelAttribute, $vocabularyData)
    {
        if (empty($vocabularyData[$channelAttribute])) {
            return false;
        }

        $attributeData = $vocabularyData[$channelAttribute];
        if (empty($attributeData['options']) || !is_array($attributeData['options'])) {
            return false;
        }

        foreach ($attributeData['options'] as $options) {
            if (in_array($channelOption, $options) && in_array($productOption, $options)) {
                return true;
            }
        }

        return false;
    }

    // ---------------------------------------

    public function isOptionAutoActionNotSet()
    {
        return !$this->isOptionAutoActionEnabled() && !$this->isOptionAutoActionDisabled();
    }

    public function isOptionAutoActionEnabled()
    {
        $configValue = $this->getConfigValue('/amazon/vocabulary/option/auto_action/', 'enabled');
        if (is_null($configValue)) {
            return false;
        }

        return (bool)$configValue;
    }

    public function isOptionAutoActionDisabled()
    {
        $configValue = $this->getConfigValue('/amazon/vocabulary/option/auto_action/', 'enabled');
        if (is_null($configValue)) {
            return false;
        }

        return !(bool)$configValue;
    }

    // ---------------------------------------

    public function enableOptionAutoAction()
    {
        return $this->setConfigValue('/amazon/vocabulary/option/auto_action/', 'enabled', 1);
    }

    public function disableOptionAutoAction()
    {
        return $this->setConfigValue('/amazon/vocabulary/option/auto_action/', 'enabled', 0);
    }

    public function unsetOptionAutoAction()
    {
        return $this->unsetConfigValue('/amazon/vocabulary/option/auto_action/', 'enabled');
    }

    //########################################

    public function getLocalData()
    {
        $cacheData = $this->getLocalDataCache();
        if (is_array($cacheData)) {
            return $cacheData;
        }

        /** @var Ess_M2ePro_Model_Registry $registry */
        $registry = Mage::getModel('M2ePro/Registry')->load(self::LOCAL_DATA_REGISTRY_KEY, 'key');
        $vocabularyData = $registry->getSettings('value');

        $this->setLocalDataCache($vocabularyData);

        return $vocabularyData;
    }

    public function getLocalAttributeNames($attribute)
    {
        return $this->getAttributeNames($attribute, $this->getLocalData());
    }

    public function getLocalOptionNames($attribute, $option)
    {
        return $this->getOptionNames($attribute, $option, $this->getLocalData());
    }

    // ---------------------------------------

    public function getServerData()
    {
        $cacheData = $this->getServerDataCache();
        if (is_array($cacheData)) {
            return $cacheData;
        }

        /** @var Ess_M2ePro_Model_Registry $registry */
        $registry = Mage::getModel('M2ePro/Registry')->load(self::SERVER_DATA_REGISTRY_KEY, 'key');
        $vocabularyData = $registry->getSettings('value');

        $this->setServerDataCache($vocabularyData);

        return $vocabularyData;
    }

    public function getServerAttributeNames($attribute)
    {
        return $this->getAttributeNames($attribute, $this->getServerData());
    }

    public function getServerOptionNames($attribute, $option)
    {
        return $this->getOptionNames($attribute, $option, $this->getServerData());
    }

    // ---------------------------------------

    public function getAttributeNames($attribute, $vocabularyData)
    {
        if (empty($vocabularyData[$attribute]['names'])) {
            return array();
        }

        return $vocabularyData[$attribute]['names'];
    }

    public function getOptionNames($attribute, $option, $vocabularyData)
    {
        if (empty($vocabularyData[$attribute]['options'])) {
            return array();
        }

        $resultNames = array();

        foreach ($vocabularyData[$attribute]['options'] as $optionNames) {
            $preparedOption      = strtolower($option);
            $preparedOptionNames = array_map('strtolower', $optionNames);

            if (!in_array($preparedOption, $preparedOptionNames)) {
                continue;
            }

            $resultNames = array_merge($resultNames, $optionNames);
        }

        return $resultNames;
    }

    //########################################

    public function setLocalData(array $data)
    {
        /** @var Ess_M2ePro_Model_Registry $registry */
        $registry = Mage::getModel('M2ePro/Registry')->load(self::LOCAL_DATA_REGISTRY_KEY, 'key');

        $registry->setData('key', self::LOCAL_DATA_REGISTRY_KEY);
        $registry->setSettings('value', $data)->save();

        $this->removeLocalDataCache();
    }

    public function setServerData(array $data)
    {
        /** @var Ess_M2ePro_Model_Registry $registry */
        $registry = Mage::getModel('M2ePro/Registry')->load(self::SERVER_DATA_REGISTRY_KEY, 'key');

        $registry->setData('key', self::SERVER_DATA_REGISTRY_KEY);
        $registry->setSettings('value', $data)->save();

        $this->removeServerDataCache();
    }

    //########################################

    public function getParentListingsProductsAffectedToAttribute($channelAttribute)
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $existListingProductCollection */
        $existListingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $existListingProductCollection->addFieldToFilter('is_variation_parent', 1);
        $existListingProductCollection->addFieldToFilter('general_id', array('notnull' => true));

        $existListingProductCollection->getSelect()->where(
            'additional_data NOT REGEXP ?', '"variation_matched_attributes":{.+}'
        );
        $existListingProductCollection->getSelect()->where(
            'additional_data REGEXP ?', '"variation_channel_attributes_sets":.*"'.$channelAttribute.'":'
        );

        $affectedListingsProducts = $existListingProductCollection->getItems();

        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingProductCollection */
        $newListingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $newListingProductCollection->addFieldToFilter('is_variation_parent', 1);
        $newListingProductCollection->addFieldToFilter('is_general_id_owner', 1);
        $newListingProductCollection->addFieldToFilter('general_id', array('null' => true));

        $newListingProductCollection->getSelect()->where(
            'additional_data NOT REGEXP ?', '"variation_channel_theme":\s*".*"'
        );

        /** @var Ess_M2ePro_Model_Listing_Product[] $newListingsProducts */
        $newListingsProducts = $newListingProductCollection->getItems();

        if (empty($newListingsProducts)) {
            return $affectedListingsProducts;
        }

        $productRequirementsCache = array();

        foreach ($newListingsProducts as $newListingProduct) {
            if (isset($affectedListingsProducts[$newListingProduct->getId()])) {
                continue;
            }

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct      = $newListingProduct->getChildObject();
            $amazonDescriptionTemplate = $amazonListingProduct->getAmazonDescriptionTemplate();

            $productAttributes = $amazonListingProduct->getVariationManager()->getTypeModel()->getProductAttributes();
            if (empty($productAttributes)) {
                continue;
            }

            if (isset($productRequirementsCache[$amazonDescriptionTemplate->getId()][count($productAttributes)])) {
                $affectedListingsProducts[$newListingProduct->getId()] = $newListingProduct;
                continue;
            }

            $marketplaceDetails = Mage::getModel('M2ePro/Amazon_Marketplace_Details');
            $marketplaceDetails->setMarketplaceId($newListingProduct->getListing()->getMarketplaceId());

            $productDataNick = $amazonDescriptionTemplate->getProductDataNick();

            foreach ($marketplaceDetails->getVariationThemes($productDataNick) as $themeNick => $themeData) {
                $themeAttributes = $themeData['attributes'];

                if (count($themeAttributes) != count($productAttributes)) {
                    continue;
                }

                if (!in_array($channelAttribute, $themeAttributes)) {
                    continue;
                }

                $affectedListingsProducts[$newListingProduct->getId()] = $newListingProduct;
                $productRequirementsCache[$amazonDescriptionTemplate->getId()][count($productAttributes)] = true;

                break;
            }
        }

        return $affectedListingsProducts;
    }

    public function getParentListingsProductsAffectedToOption($channelAttribute, $channelOption)
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('is_variation_parent', 1);
        $listingProductCollection->addFieldToFilter('general_id', array('notnull' => true));

        $listingProductCollection->getSelect()->where(
            'additional_data REGEXP ?', '"variation_matched_attributes":{.+}'
        );
        $listingProductCollection->getSelect()->where(
            'additional_data REGEXP ?',
            '"variation_channel_attributes_sets":.*"'.$channelAttribute.'":\s*[\[|{].*'.$channelOption.'.*[\]|}]'
        );

        return $listingProductCollection->getItems();
    }

    //########################################

    private function getLocalDataCache()
    {
        $cacheKey = __CLASS__.self::LOCAL_DATA_REGISTRY_KEY;
        return Mage::helper('M2ePro/Data_Cache_Permanent')->getValue($cacheKey);
    }

    private function getServerDataCache()
    {
        $cacheKey = __CLASS__.self::SERVER_DATA_REGISTRY_KEY;
        return Mage::helper('M2ePro/Data_Cache_Permanent')->getValue($cacheKey);
    }

    // ---------------------------------------

    private function setLocalDataCache(array $data)
    {
        $cacheKey = __CLASS__.self::LOCAL_DATA_REGISTRY_KEY;
        Mage::helper('M2ePro/Data_Cache_Permanent')->setValue($cacheKey, $data);
    }

    private function setServerDataCache(array $data)
    {
        $cacheKey = __CLASS__.self::SERVER_DATA_REGISTRY_KEY;
        Mage::helper('M2ePro/Data_Cache_Permanent')->setValue($cacheKey, $data);
    }

    // ---------------------------------------

    private function removeLocalDataCache()
    {
        $cacheKey = __CLASS__.self::LOCAL_DATA_REGISTRY_KEY;
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeValue($cacheKey);
    }

    private function removeServerDataCache()
    {
        $cacheKey = __CLASS__.self::SERVER_DATA_REGISTRY_KEY;
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeValue($cacheKey);
    }

    //########################################

    private function getConfigValue($group, $key)
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue($group, $key);
    }

    private function setConfigValue($group, $key, $value)
    {
        return Mage::helper('M2ePro/Module')->getConfig()->setGroupValue($group, $key, $value);
    }

    private function unsetConfigValue($group, $key)
    {
        return Mage::helper('M2ePro/Module')->getConfig()->deleteGroupValue($group, $key);
    }

    //########################################
}
