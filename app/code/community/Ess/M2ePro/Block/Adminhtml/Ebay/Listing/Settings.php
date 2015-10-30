<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Settings extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingSettings');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing_settings';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------

        $this->_headerText = Mage::helper('M2ePro')->__('Set Products Settings');
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

        // ---------------------------------------
        $url = $this->getUrl('*/*/deleteAll',array('_current' => true));
        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'class'     => 'back',
            'onclick'   => 'setLocation(\''.$url.'\')'
        ));
        // ---------------------------------------

        // ---------------------------------------
        if (Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            $this->_addButton('auto_action', array(
                'label'     => Mage::helper('M2ePro')->__('Auto Add/Remove Rules'),
                'onclick'   => 'ListingAutoActionHandlerObj.loadAutoActionHtml();'
            ));
        }
        // ---------------------------------------

        // ---------------------------------------
        $onClick = <<<JS
    EbayListingSettingsGridHandlerObj.continue();
JS;

        $this->_addButton('continue', array(
            'label'     => Mage::helper('M2ePro')->__('Continue'),
            'class'     => 'next',
            'onclick'   => $onClick
        ));
        // ---------------------------------------
    }

    public function getGridHtml()
    {
        $listingId = (int)$this->getRequest()->getParam('listing_id');

        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header','',
            array('listing' => Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId))
        );

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_settings_help');

        return $viewHeaderBlock->toHtml() .
               $helpBlock->toHtml() .
               parent::getGridHtml();
    }

    //########################################
}