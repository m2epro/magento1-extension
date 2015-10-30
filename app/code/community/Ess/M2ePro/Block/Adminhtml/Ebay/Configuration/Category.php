<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Category extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayConfigurationCategory');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_configuration_category';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = '';
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('save');
        $this->removeButton('edit');
        $this->removeButton('add');
        // ---------------------------------------

        // Set template
        // ---------------------------------------
        $this->setTemplate('M2ePro/widget/grid/container/only_content.phtml');
        // ---------------------------------------
    }

    //########################################
}