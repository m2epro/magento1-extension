<?php

class Ess_M2ePro_Block_Adminhtml_Amazon_Mapping_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonMappingEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_mapping';
        $this->_mode = 'edit';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('edit');

        $this->_headerText = Mage::helper('M2ePro')->__('Mapping');
    }
}
