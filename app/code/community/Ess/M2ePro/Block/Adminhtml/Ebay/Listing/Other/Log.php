<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Other_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingOtherLog');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing_other_log';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = '';

        $otherListingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        if (isset($otherListingData['id'])) {

            if (!Mage::helper('M2ePro/View_Ebay_Component')->isSingleActiveComponent()) {
                $component = Mage::helper('M2ePro/Component')->getComponentTitle($otherListingData['component_mode']);
                $headerText = Mage::helper('M2ePro')->__("Log For %component_name% 3rd Party Listing", $component);
            } else {
                $headerText = Mage::helper('M2ePro')->__("Log For 3rd Party Listing");
            }
            $tempTitle = Mage::helper('M2ePro/Component_'.ucfirst($otherListingData['component_mode']))
                ->getObject('Listing_Other',$otherListingData['id'])
                ->getChildObject()->getTitle();

            $this->_headerText = $headerText;
            $this->_headerText .= ' "' . $this->escapeHtml($tempTitle) . '"';
        } else {

            // Set template
            //------------------------------
            $this->setTemplate('M2ePro/widget/grid/container/only_content.phtml');
            //------------------------------

        }
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->addButton('show_general_log', array(
            'label'     => Mage::helper('M2ePro')->__('Show General Log'),
            'onclick'   => 'setLocation(\'' .$this->getUrl('*/adminhtml_ebay_log/listingOther').'\')',
            'class'     => 'button_link'
        ));
        //------------------------------
    }

    // ########################################
}