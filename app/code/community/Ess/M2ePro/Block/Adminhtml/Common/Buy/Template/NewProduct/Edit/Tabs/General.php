<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Template_NewProduct_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('buyTemplateNewProductEditTabsGeneral');
        // ---------------------------------------

        $this->setTemplate('M2ePro/common/buy/template/newProduct/tabs/general.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $this->nodes = $connRead->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('m2epro_buy_dictionary_category'))
            ->where('parent_category_id = ?', 0)
            ->order('title ASC')
            ->query()
            ->fetchAll();
        !is_array($this->nodes) && $this->nodes = array();

        $this->attributes = Mage::helper('M2ePro/Magento_Attribute')->getGeneralFromAllAttributeSets();

        // ---------------------------------------
        $data = array(
            'id'      => 'category_confirm_button',
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => 'BuyTemplateNewProductHandlerObj.confirmCategory();',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('category_confirm_button',$buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'id'      => 'category_change_button',
            'label'   => Mage::helper('M2ePro')->__('Change Category'),
            'onclick' => 'BuyTemplateNewProductHandlerObj.changeCategory();',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('category_change_button',$buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'id'      => 'browse_category_button',
            'label'   => Mage::helper('M2ePro')->__('Browse'),
            'onclick' => 'BuyTemplateNewProductHandlerObj.browse_category.showCenter(true)'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('browse_category_button',$buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'id'      => 'search_category_button',
            'label'   => Mage::helper('M2ePro')->__('Search By Keywords'),
            'onclick' => 'BuyTemplateNewProductHandlerObj.search_category.showCenter(true)',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('search_category_button',$buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'id'      => 'search_category_popup_button',
            'label'   => Mage::helper('M2ePro')->__('Search'),
            'onclick' => 'BuyTemplateNewProductHandlerObj.searchClick()',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('search_category_popup_button',$buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'id'      => 'close_browse_popup_button',
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'onclick' => 'BuyTemplateNewProductHandlerObj.closeBrowseCategoryPopup()',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('close_browse_popup_button',$buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'id'      => 'close_search_popup_button',
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'onclick' => 'BuyTemplateNewProductHandlerObj.closeSearchCategoryPopup()',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('close_search_popup_button',$buttonBlock);
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################

    public function isAllowedUpcExemption($formData)
    {
        $isAllowedUpcExemption = Ess_M2ePro_Model_Buy_Template_NewProduct::isAllowedUpcExemption();
        $gtinMode = $formData['category']['gtin_mode'];
        return $isAllowedUpcExemption || $gtinMode == Ess_M2ePro_Model_Buy_Template_NewProduct_Core::GTIN_MODE_NONE;
    }

    //########################################
}