<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Category_View extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayConfigurationCategoryView');
        $this->setTemplate('M2ePro/ebay/configuration/category/view.phtml');

        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Ebay')->getTitle();
            $this->_headerText = Mage::helper('M2ePro')->__('Edit %component_name% Category', $componentName);
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('Edit Category');
        }

        $this->removeButton('back');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
    }

    //########################################

    protected function _prepareLayout()
    {
        Mage::helper('M2ePro/View')->getJsRenderer()->addOnReadyJs(<<<JS
    CommonObj = new Common();
JS
        );

        $infoBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_configuration_category_view_info', '',
            array('template_id' => $this->getRequest()->getParam('template_id'))
        );
        $this->setChild('info', $infoBlock);

        $tabsBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_configuration_category_view_tabs'
        );
        $this->setChild('tabs', $tabsBlock);

        return parent::_prepareLayout();
    }

    //########################################
}
