<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Other_Synchronization_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingOtherSynchronizationEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing_other_synchronization';
        $this->_mode = 'edit';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        if (!Mage::helper('M2ePro/View_Ebay_Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Ebay')->getTitle();
            $headerText = Mage::helper('M2ePro')->__("Edit %component_name% 3rd Party Synchronization Settings",
                $componentName);
        } else {
            $headerText = Mage::helper('M2ePro')->__("Edit 3rd Party Synchronization Settings");
        }

        $this->_headerText = $headerText;
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
        $url = $this->getRequest()->getParam('back');
        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'EbayListingOtherSynchronizationHandlerObj.save_click(\''.$url.'\')',
            'class'     => 'save'
        ));
        // ---------------------------------------

        // ---------------------------------------
        $back = $this->getRequest()->getParam('back');
        $this->_addButton('save_and_continue', array(
            'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
            'onclick'   => 'EbayListingOtherSynchronizationHandlerObj.' .
                           'save_and_edit_click(\''.$back.'\',\'ebayListingOtherSynchronizationEditTabs\')',
            'class'     => 'save'
        ));
        // ---------------------------------------
    }

    protected function _toHtml()
    {
        $translations = array();

        $text = 'Inconsistent Settings in Relist and Stop Rules.';
        $translations[$text] = Mage::helper('M2ePro')->__($text);

        $text = 'Must be greater than "Min".';
        $translations[$text] = Mage::helper('M2ePro')->__($text);

        $translations = json_encode($translations);

        $javascriptBefore =<<<HTML
<script type="text/javascript">
    M2ePro.translator.add({$translations});
    EbayListingOtherSynchronizationHandlerObj = new EbayListingOtherSynchronizationHandler();
</script>
HTML;

        return $javascriptBefore . parent::_toHtml();

    }

    //########################################
}