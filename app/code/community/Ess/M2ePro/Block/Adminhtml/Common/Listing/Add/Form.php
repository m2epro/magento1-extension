<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Add_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected $component;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('listingEditForm');
        // ---------------------------------------
    }

    protected function _prepareForm()
    {
        // Prepare action
        // ---------------------------------------
        $step = $this->getRequest()->getParam('step');

        if (is_null($step)) {
            // Edit listing mode
            $action = $this->getUrl('*/adminhtml_common_' . $this->component . '_listing/save');
        } else {
            // Add listing mode
            $action = $this->getUrl('*/adminhtml_common_' . $this->component . '_listing/add', array(
                'step' => (int)$step
            ));
        }
        // ---------------------------------------

        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $action,
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        // Add listing mode
        // ---------------------------------------
        $child = NULL;
        $step = $this->getRequest()->getParam('step');

        if ($step == 1) {
            $child = $this->getLayout()
                          ->createBlock('M2ePro/adminhtml_common_' . $this->component . '_listing_add_tabs_general');
        } else if ($step == 2) {
            $child = $this->getLayout()
                          ->createBlock('M2ePro/adminhtml_common_' . $this->component . '_listing_add_tabs_selling');
        } elseif ($step == 3) {
            $child = $this->getLayout()
                          ->createBlock('M2ePro/adminhtml_common_' . $this->component . '_listing_add_tabs_search');
        }

        if (!is_null($child)) {
            $this->setTemplate('M2ePro/common/listing/add.phtml');
            $this->setChild('main', $this->getLayout()
                                            ->createBlock('M2ePro/adminhtml_common_listing_add_main'));
            $this->setChild('content', $child);
        }
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################

    protected function _toHtml()
    {
        $breadcrumb = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_common_listing_breadcrumb','',
            array('step' => $this->getRequest()->getParam('step', 1))
        );

        return $breadcrumb->_toHtml() . parent::_toHtml();
    }

    //########################################
}