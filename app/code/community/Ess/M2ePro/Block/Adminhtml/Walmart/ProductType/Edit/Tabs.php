<?php

class Ess_M2ePro_Block_Adminhtml_Walmart_ProductType_Edit_Tabs
    extends Ess_M2ePro_Block_Adminhtml_Widget_Tabs
{
    /** @var Ess_M2ePro_Model_Walmart_ProductType $productType */
    private $productType;

    public function __construct($args)
    {
        parent::__construct($args);

        $this->productType = $args['productType'];
        $this->setId('walmartProductTypeEditTabs');
        $this->setDestElementId('edit_form');
    }

    protected function _prepareLayout()
    {
        $this->addTab(
            'general',
            array(
                'label' => Mage::helper('M2ePro')->__('General'),
                'title' => Mage::helper('M2ePro')->__('General'),
                'content' => $this->getLayout()
                    ->createBlock(
                        'M2ePro/adminhtml_walmart_productType_edit_tabs_general',
                        '',
                        array('productType' => $this->productType)
                    )
                    ->toHtml(),
            )
        );

        $this->addTab(
            'template',
            array(
                'label' => '%title%',
                'title' => '%title%',
                'is_hidden' => true,
                'content' => $this->getLayout()
                    ->createBlock(
                        'M2ePro/adminhtml_walmart_productType_edit_tabs_template',
                        '',
                        array('productType' => $this->productType)
                    )
                    ->toHtml(),
            )
        );

        $this->setActiveTab($this->getRequest()->getParam('tab', 'general'));

        return parent::_prepareLayout();
    }
}
