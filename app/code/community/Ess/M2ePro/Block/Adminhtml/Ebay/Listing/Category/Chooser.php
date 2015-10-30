<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Chooser extends Mage_Adminhtml_Block_Widget
{
    const INTERFACE_MODE_FULL     = 1;
    const INTERFACE_MODE_COMPACT  = 2;

    //########################################

    protected $_marketplaceId = null;

    protected $_accountId = null;

    protected $_attributes = array();

    protected $_internalData = array();

    protected $_divId = null;

    protected $_selectCallback = '';

    protected $_unselectCallback = '';

    protected $_interfaceMode = self::INTERFACE_MODE_FULL;

    protected $_isSingleCategoryMode = false;

    protected $_singleCategoryType = Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN;

    protected $_isShowEditLinks = true;

    protected $_isAjax = false;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingCategoryChooser');
        // ---------------------------------------

        // Set template
        // ---------------------------------------
        $this->setTemplate('M2ePro/ebay/listing/category/chooser.phtml');
        // ---------------------------------------

        $this->_isAjax = $this->getRequest()->isXmlHttpRequest();
    }

    //########################################

    public function getMarketplaceId()
    {
        return $this->_marketplaceId;
    }

    public function setMarketplaceId($marketplaceId)
    {
        $this->_marketplaceId = $marketplaceId;
        return $this;
    }

    public function getAccountId()
    {
        return $this->_accountId;
    }

    public function setAccountId($accountId)
    {
        $this->_accountId = $accountId;
        return $this;
    }

    public function getAttributes()
    {
        if (!empty($this->_attributes)) {
            return $this->_attributes;
        }

        $attributes = Mage::helper('M2ePro/Magento_Attribute')->getGeneralFromAllAttributeSets();
        if (empty($attributes)) {
            return array();
        }

        $this->_attributes = Mage::helper('M2ePro/Magento_Attribute')->filterByInputTypes(
            $attributes, array('text', 'select')
        );

        return $attributes;
    }

    public function setAttributes($attributes)
    {
        $this->_attributes = $attributes;
        return $this;
    }

    public function getInternalData()
    {
        return $this->_internalData;
    }

    public function setInternalData(array $data)
    {
        $categoryTypePrefixes = array(
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN => 'category_main_',
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_SECONDARY => 'category_secondary_',
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_MAIN => 'store_category_main_',
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_SECONDARY => 'store_category_secondary_',
        );

        foreach ($categoryTypePrefixes as $type => $prefix) {
            if (!isset($data[$prefix.'mode'])) {
                continue;
            }

            switch ($data[$prefix.'mode']) {
                case Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY:
                    if (!empty($data[$prefix.'path'])) {
                        $path = $data[$prefix.'path'];
                    } else {
                        $path = $this->_prepareEbayCategoryPath($data[$prefix.'id'], $type);
                    }

                    $this->_internalData[$type] = array(
                        'mode' => $data[$prefix.'mode'],
                        'value' => $data[$prefix.'id'],
                        'path' => $path . ' (' . $data[$prefix.'id'] . ')'
                    );

                    break;

                case Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE:
                    if (!empty($data[$prefix.'path'])) {
                        $path = $data[$prefix.'path'];
                    } else {
                        $path = $this->_prepareAttributeCategoryPath($data[$prefix.'attribute']);
                    }

                    $this->_internalData[$type] = array(
                        'mode' => $data[$prefix.'mode'],
                        'value' => $data[$prefix.'attribute'],
                        'path' => $path
                    );

                    break;

                case Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE:
                    if (!empty($data[$prefix.'message'])) {
                        $this->_internalData[$type] = array(
                            'mode' => $data[$prefix.'mode'],
                            'message' => $data[$prefix.'message']
                        );
                    }

                    break;
            }
        }

        return $this;
    }

    public function setConvertedInternalData(array $data)
    {
        $this->_internalData = $data;
        return $this;
    }

    public function setSingleCategoryData(array $data)
    {
        if (empty($data['path'])) {
            $data['path'] = $this->_preparePath($data['mode'], $data['value'], $this->getSingleCategoryType());
        }

        if ($data['mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
            $data['path'] .= ' (' . $data['value'] . ')';
        }

        $this->_internalData[$this->_singleCategoryType] = $data;
        return $this;
    }

    public function getDivId()
    {
        if (is_null($this->_divId)) {
            $this->_divId = Mage::helper('core/data')->uniqHash('category_chooser_');
        }

        return $this->_divId;
    }

    public function setDivId($divId)
    {
        $this->_divId = $divId;
        return $this;
    }

    public function getSelectCallback()
    {
        return $this->_selectCallback;
    }

    public function setSelectCallback($callback)
    {
        $this->_selectCallback = $callback;
        return $this;
    }

    public function getUnselectCallback()
    {
        return $this->_unselectCallback;
    }

    public function setUnselectCallback($callback)
    {
        $this->_unselectCallback = $callback;
        return $this;
    }

    public function getInterfaceMode()
    {
        return $this->_interfaceMode;
    }

    public function setInterfaceMode($mode)
    {
        $this->_interfaceMode = $mode;
        return $this;
    }

    public function isInterfaceModeFull()
    {
        return $this->_interfaceMode == self::INTERFACE_MODE_FULL;
    }

    public function setInterfaceModeFull()
    {
        $this->_interfaceMode = self::INTERFACE_MODE_FULL;
        return $this;
    }

    public function isInterfaceModeCompact()
    {
        return $this->_interfaceMode == self::INTERFACE_MODE_COMPACT;
    }

    public function setInterfaceModeCompact()
    {
        $this->_interfaceMode = self::INTERFACE_MODE_COMPACT;
        return $this;
    }

    public function isSingleCategoryMode()
    {
        return $this->_isSingleCategoryMode;
    }

    public function setSingleCategoryMode($mode = true)
    {
        $this->_isSingleCategoryMode = $mode;
        return $this;
    }

    public function getSingleCategoryType()
    {
        return $this->_singleCategoryType;
    }

    public function setSingleCategoryType($type)
    {
        $this->_singleCategoryType = $type;
        return $this;
    }

    public function isShowEditLinks()
    {
        return $this->_isShowEditLinks;
    }

    public function setShowEditLinks($isShow = true)
    {
        $this->_isShowEditLinks = $isShow;
        return $this;
    }

    //########################################

    public function isShowStoreCatalog()
    {
        if (is_null($this->getAccountId())) {
            return false;
        }

        $storeCategories = Mage::helper('M2ePro/Component_Ebay')
            ->getCachedObject('Account', (int)$this->getAccountId())
            ->getChildObject()
            ->getEbayStoreCategories();

        return !empty($storeCategories);
    }

    //########################################

    protected function _preparePath($mode, $value, $type)
    {
        if ($mode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
            return $this->_prepareEbayCategoryPath($value, $type);
        } elseif ($mode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            return $this->_prepareAttributeCategoryPath($value);
        }

        return '';
    }

    protected function _prepareEbayCategoryPath($value, $type)
    {
        if (in_array($type, Mage::helper('M2ePro/Component_Ebay_Category')->getEbayCategoryTypes())) {
            return Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getPath(
                $value, $this->getMarketplaceId()
            );
        }

        return Mage::helper('M2ePro/Component_Ebay_Category_Store')->getPath($value, $this->getAccountId());
    }

    protected function _prepareAttributeCategoryPath($attributeCode)
    {
        $attributeLabel = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($attributeCode);

        return Mage::helper('M2ePro')->__('Magento Attribute') . ' > ' . $attributeLabel;
    }

    //########################################
}