<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Add_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonListingEditForm');
        // ---------------------------------------
    }

    //########################################

    protected function _prepareForm()
    {
        // Prepare action
        // ---------------------------------------
        $step = $this->getRequest()->getParam('step');

        if ($step === null) {
            // Edit listing mode
            $action = $this->getUrl('*/adminhtml_amazon_listing/save');
        } else {
            // Add listing mode
            $action = $this->getUrl(
                '*/adminhtml_amazon_listing/add', array(
                    'step' => (int)$step
                )
            );
        }

        // ---------------------------------------

        $form = new Varien_Data_Form(
            array(
                'id'      => 'edit_form',
                'action'  => $action,
                'method'  => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        // Add listing mode
        // ---------------------------------------
        $child = null;
        $step = $this->getRequest()->getParam('step');

        if ($step == 1) {
            $child = $this->getLayout()
                ->createBlock('M2ePro/adminhtml_amazon_listing_add_tabs_general');
        } else if ($step == 2) {
            $child = $this->getLayout()
                ->createBlock('M2ePro/adminhtml_amazon_listing_add_tabs_selling');
        } elseif ($step == 3) {
            $child = $this->getLayout()
                ->createBlock('M2ePro/adminhtml_amazon_listing_add_tabs_search');
        }

        if ($child !== null) {
            $this->setTemplate('M2ePro/amazon/listing/add.phtml');
            $this->setChild(
                'main', $this->getLayout()
                ->createBlock('M2ePro/adminhtml_amazon_listing_add_main')
            );
            $this->setChild('content', $child);
        }

        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################

    protected function _toHtml()
    {
        $breadcrumb = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_listing_breadcrumb', '',
            array('step' => $this->getRequest()->getParam('step', 1))
        );

        return $breadcrumb->_toHtml() . parent::_toHtml();
    }

    //########################################
}
