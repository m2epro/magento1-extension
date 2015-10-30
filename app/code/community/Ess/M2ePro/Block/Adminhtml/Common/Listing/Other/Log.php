<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Other_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('commonListingOtherLog');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_listing_other_log';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $otherListingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        if (isset($otherListingData['id'])) {
            $tempTitle = Mage::helper('M2ePro/Component_'.ucfirst($otherListingData['component_mode']))
                ->getObject('Listing_Other',$otherListingData['id'])
                ->getChildObject()->getTitle();

            $this->_headerText = Mage::helper('M2ePro')->__("Log For ");
            $this->_headerText .= ' "' . $this->escapeHtml($tempTitle) . '"';
        } else {
            $this->_headerText = '';
        }
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        if ($this->_headerText != '') {
            $this->addButton('show_general_log', array(
                'label'     => Mage::helper('M2ePro')->__('Show General Log'),
                'onclick'   => 'setLocation(\'' .$this->getUrl('*/adminhtml_common_log/listingOther', array(
                        'channel' => $this->getRequest()->getParam('channel',
                            Mage::helper('M2ePro/View_Common_Component')->getDefaultComponent())
                    )).'\')',
                'class'     => 'button_link'
            ));
        } else {
            $this->setTemplate('M2ePro/widget/grid/container/only_content.phtml');
        }
    }

    //########################################
}