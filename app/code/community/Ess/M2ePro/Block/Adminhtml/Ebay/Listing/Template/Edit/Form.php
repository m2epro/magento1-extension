<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Template_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingTemplateEditForm');
        //------------------------------
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/adminhtml_ebay_template/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    // ####################################

    protected function _toHtml()
    {
        if ($this->getRequest()->getParam('step')) {
            $breadcrumb = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_ebay_listing_breadcrumb','',
                array('step' => $this->getRequest()->getParam('step',2))
            );

            return $breadcrumb->_toHtml() . parent::_toHtml();
        }

        return parent::_toHtml();
    }

    // ####################################
}