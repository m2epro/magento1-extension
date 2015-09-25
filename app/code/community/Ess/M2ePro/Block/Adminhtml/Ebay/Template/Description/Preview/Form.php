<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Description_Preview_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //#############################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayTemplateDescriptionPreviewForm');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/template/description/preview/form.phtml');
    }

    //#############################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/preview'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'id' => 'display_product_description',
                'label'   => Mage::helper('M2ePro')->__('View'),
                'type' => 'submit'
            ) );
        $this->setChild('display_product_description',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'id' => 'display_random_product_description',
                'label'   => Mage::helper('M2ePro')->__('View Random Product'),
                'type' => 'submit',
                'onclick' => '$(\'product_id\').value = \'\'; return true;'
            ) );
        $this->setChild('display_random_product_description',$buttonBlock);
        //------------------------------

        //------------------------------
        $this->setChild(
            'store_switcher',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_storeSwitcher', '', array('id'       => 'store_id',
                                                            'selected' => $this->getData('store_id'))
            )
        );
        //------------------------------

        return parent::_beforeToHtml();
    }

    //#############################################
}