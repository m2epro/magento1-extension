<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Template_NewProduct_Edit_Tabs_Attributes extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyTemplateNewProductEditTabsAttributes');
        //------------------------------

        $this->setTemplate('M2ePro/common/buy/template/newProduct/tabs/attributes.phtml');
    }

    //------------------------------

    public function getAttributesJsHtml()
    {
        $html = '';

        $allAttributes = $this->attributes = Mage::helper('M2ePro/Magento_Attribute')->getAll();
        $attributes = Mage::helper('M2ePro/Magento_Attribute')->filterByInputTypes(
            $allAttributes, array('text', 'price', 'select')
        );

        foreach($attributes as $attribute) {
            $code = Mage::helper('M2ePro')->escapeHtml($attribute['code']);
            $html .= sprintf('<option value="%s">%s</option>', $code, $attribute['label']);
        }

        return Mage::helper('M2ePro')->escapeJs($html);
    }

    // ####################################
}