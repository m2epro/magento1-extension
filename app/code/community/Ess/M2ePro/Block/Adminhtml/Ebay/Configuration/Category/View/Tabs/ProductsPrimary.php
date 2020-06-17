<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Category_View_Tabs_ProductsPrimary
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayConfigurationCategoryViewProductsPrimary');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_configuration_category_view_tabs_productsPrimary';

        $this->_headerText = '';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton(
            'back',
            array(
               'label'     => Mage::helper('M2ePro')->__('Back'),
               'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/index') . '\')',
               'class'     => 'back',
            )
        );
    }

    //########################################
}
