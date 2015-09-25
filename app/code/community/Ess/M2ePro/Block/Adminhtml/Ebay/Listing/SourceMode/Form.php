<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_SourceMode_Form extends Mage_Adminhtml_Block_Widget_Form
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingSourceMode');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/listing/source_mode.phtml');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/*'),
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
        $this->setData('source', $this->getRequest()->getParam('source','products'));
        //------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################

    protected function _toHtml()
    {
        return parent::_toHtml();
    }

    // ########################################
}