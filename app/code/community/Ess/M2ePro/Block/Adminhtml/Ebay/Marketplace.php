<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Marketplace extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayMarketplace');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_marketplace';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = '';
        // ---------------------------------------

        $this->removeButton('save');
        $this->removeButton('reset');
        $this->removeButton('back');

        $this->_addButton('run_update_all', array(
            'label'     => Mage::helper('M2ePro')->__('Update All Now'),
            'onclick'   => 'MarketplaceHandlerObj.updateAction()',
            'class'     => 'save update_all_marketplaces'
        ));

        $this->_addButton('run_save_and_synch', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'MarketplaceHandlerObj.saveAction();',
            'class'     => 'save save_and_update_marketplaces'
        ));
    }

    //########################################

    protected function _toHtml()
    {
        return '<div id="marketplaces_progress_bar"></div>' .
               '<div id="marketplaces_content_container">' .
               parent::_toHtml() .
               '</div>';
    }

    //########################################
}