<?php

class Ess_M2ePro_Block_Adminhtml_Amazon_ProductType_Validation_Result
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('validateProductTypes');
        $this->_blockGroup = 'M2ePro';
        $this->_headerText = Mage::helper('M2ePro')->__("Product Type Data Validation");
        $this->_controller = 'adminhtml_amazon_productType_validation_result';
        $this->initToolbarButtons();
    }

    private function initToolbarButtons()
    {
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->addButton('back',
            array(
                'label' => Mage::helper('M2ePro')->__('Back'),
                'onclick' => 'window.close()',
                'class' => 'back',
            )
        );
    }

    public function getGridHtml()
    {
        $this->getChild('grid')->setData('listingProductIds', $this->getData('listingProductIds'));
        $progressBarHtml = sprintf('<div id="%s"></div>','product_type_validation_progress_bar');

        return $progressBarHtml
            . parent::getGridHtml();
    }
}