<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Marketplace extends Mage_Adminhtml_Block_Widget_Form_Container
{

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartMarketplace');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_walmart_marketplace';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = '';
        // ---------------------------------------

        $this->removeButton('save');
        $this->removeButton('reset');
        $this->removeButton('back');

        $this->addButton('run_update_all', array(
            'label'     => Mage::helper('M2ePro')->__('Update All Now'),
            'onclick'   => 'MarketplaceHandlerObj.updateAction()',
            'class'     => 'save update_all_marketplace'
        ));
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton('run_synch_now', array(
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