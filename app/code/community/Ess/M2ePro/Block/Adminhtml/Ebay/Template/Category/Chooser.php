<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Helper_Component_Ebay_Category as Category;
use Ess_M2ePro_Model_Ebay_Template_Category as TemplateCategory;

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Category_Chooser extends Mage_Adminhtml_Block_Widget
{
    const MODE_BOTH_CATEGORY   = 'both';
    const MODE_EBAY_CATEGORY   = 'ebay';
    const MODE_EBAY_PRIMARY    = 'ebay_primary';
    const MODE_EBAY_SECONDARY  = 'ebay_secondary';
    const MODE_STORE_CATEGORY  = 'store';

    //########################################

    protected $_marketplaceId;
    protected $_accountId;
    protected $_categoryMode = self::MODE_BOTH_CATEGORY;

    protected $_isEditCategoryAllowed = true;

    protected $_attributes     = array();
    protected $_categoriesData = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayTemplateCategoryChooser_');
        $this->setTemplate('M2ePro/ebay/template/category/chooser.phtml');

        $this->_attributes = Mage::helper('M2ePro/Magento_Attribute')->filterByInputTypes(
            Mage::helper('M2ePro/Magento_Attribute')->getAll(),
            array('text', 'select')
        );
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

    //----------------------------------------

    public function getAccountId()
    {
        return $this->_accountId;
    }

    public function setAccountId($accountId)
    {
        $this->_accountId = $accountId;
        return $this;
    }

    //----------------------------------------

    public function getAttributes()
    {
        return $this->_attributes;
    }

    //----------------------------------------

    public function getCategoriesData()
    {
        return $this->_categoriesData;
    }

    public function setCategoriesData(array $data)
    {
        $this->_categoriesData = $data;
        return $this;
    }

    //----------------------------------------

    public function setCategoryMode($mode)
    {
        $this->_categoryMode = $mode;
        return $this;
    }

    public function getCategoryMode()
    {
        return $this->_categoryMode;
    }

    public function isCategoryModeBoth()
    {
        return $this->getCategoryMode() === self::MODE_BOTH_CATEGORY;
    }

    public function isCategoryModeEbay()
    {
        return $this->getCategoryMode() === self::MODE_EBAY_CATEGORY;
    }

    public function isCategoryModeEbayPrimary()
    {
        return $this->getCategoryMode() === self::MODE_EBAY_PRIMARY;
    }

    public function isCategoryModeEbaySecondary()
    {
        return $this->getCategoryMode() === self::MODE_EBAY_SECONDARY;
    }

    public function isCategoryModeStore()
    {
        return $this->getCategoryMode() === self::MODE_STORE_CATEGORY;
    }

    //----------------------------------------

    public function getIsEditCategoryAllowed()
    {
        return $this->_isEditCategoryAllowed;
    }

    public function setIsEditCategoryAllowed($isEditCategoryAllowed)
    {
        $this->_isEditCategoryAllowed = $isEditCategoryAllowed;
    }

    //----------------------------------------

    public function isItemSpecificsRequired()
    {
        if (!isset($this->_categoriesData[Category::TYPE_EBAY_MAIN]['value'])) {
            return false;
        }

        return Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->hasRequiredSpecifics(
            $this->_categoriesData[Category::TYPE_EBAY_MAIN]['value'],
            $this->getMarketplaceId()
        );
    }

    //########################################

    public function hasStoreCatalog()
    {
        if ($this->getAccountId() === null) {
            return false;
        }

        $storeCategories = Mage::helper('M2ePro/Component_Ebay')
            ->getCachedObject('Account', (int)$this->getAccountId())
            ->getChildObject()
            ->getEbayStoreCategories();

        return !empty($storeCategories);
    }

    //########################################

    public function getCategoryPathHtml($categoryType)
    {
        $helper = Mage::helper('M2ePro');
        if (!isset($this->_categoriesData[$categoryType]['mode']) ||
            $this->_categoriesData[$categoryType]['mode'] == TemplateCategory::CATEGORY_MODE_NONE
        ) {
            return <<<HTML
<span style="font-style: italic; color: grey">{$helper->__('Not Selected')}</span>
HTML;
        }

        $category = $this->_categoriesData[$categoryType];
        return $category['mode'] == TemplateCategory::CATEGORY_MODE_EBAY
            ? "{$category['path']} ({$category['value']})"
            : $category['path'];
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $data = array(
            'class'   => 'save done',
            'label'   => Mage::helper('M2ePro')->__('Save'),
            'onclick' => 'EbayListingCategoryObj.saveCategorySettings();'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('done', $buttonBlock);
    }

    //########################################
}
