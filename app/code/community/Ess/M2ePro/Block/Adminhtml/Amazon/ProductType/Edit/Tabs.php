<?php

class Ess_M2ePro_Block_Adminhtml_Amazon_ProductType_Edit_Tabs
    extends Ess_M2ePro_Block_Adminhtml_Widget_Tabs
{
    /** @var Ess_M2ePro_Model_Amazon_Template_ProductType $productType */
    private $productType;

    public function __construct($args)
    {
        $this->productType = $args['productType'];

        parent::__construct($args);

        $this->setId('amazonProductTypeEditTabs');
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
                        'M2ePro/adminhtml_amazon_productType_edit_tabs_general',
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
                        'M2ePro/adminhtml_amazon_productType_edit_tabs_template',
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

