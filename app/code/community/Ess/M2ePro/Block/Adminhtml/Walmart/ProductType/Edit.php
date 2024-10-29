<?php

class Ess_M2ePro_Block_Adminhtml_Walmart_ProductType_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /** @var Ess_M2ePro_Model_Walmart_ProductType */
    private $productType;

    public function __construct($args)
    {
        parent::__construct();

        $this->productType = $args['productType'];

        $title = $this->productType->isObjectNew()
            ? Mage::helper('M2ePro')->__('Add Product Type Settings')
            : Mage::helper('M2ePro')->__('Edit Product Type Settings "%title%"', $this->productType->getTitle());
        $this->_headerText = $title;

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartProductTypeEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_walmart_productType';
        $this->_mode = 'edit';
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('delete');
        $this->removeButton('save');
        $this->removeButton('reset');
        // ---------------------------------------

        $isSaveAndClose = (bool)$this->getRequest()->getParam('close_on_save', false);
        $isEditMode = (bool)$this->productType->getId();

        if (!$isSaveAndClose && $isEditMode) {
            $this->addButton(
                'delete',
                array(
                    'label' => Mage::helper('M2ePro')->__('Delete'),
                    'onclick' => 'WalmartProductTypeObj.deleteClick()',
                    'class' => 'delete M2ePro_delete_button primary',
                )
            );
        }

        if ($isSaveAndClose) {
            $this->_addButton(
                'save_and_close',
                array(
                    'id' => 'save_and_close',
                    'label' => Mage::helper('M2ePro')->__('Save And Close'),
                    'onclick' => 'WalmartProductTypeObj.saveAndCloseClick()',
                    'class' => 'save'
                )
            );

            $this->_addButton(
                'save_and_continue',
                array(
                    'id' => 'save_and_continue',
                    'label' => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                    'onclick' => 'WalmartProductTypeObj.saveAndEditClick(true)',
                    'class' => 'save'
                )
            );

            $this->removeButton('back');

            return;
        }

        $this->_addButton(
            'save_and_continue',
            array(
                'id' => 'save_and_continue',
                'label' => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                'onclick' => 'WalmartProductTypeObj.saveAndEditClick(true)',
                'class' => 'save'
            )
        );

        $this->_addButton(
            'save_and_back',
            array(
                'id' => 'save_and_continue',
                'label' => Mage::helper('M2ePro')->__('Save And Back'),
                'onclick' =>  'WalmartProductTypeObj.saveClick(true)',
                'class' => 'save'
            )
        );
    }

    protected function _prepareLayout()
    {
        Mage::helper('M2ePro/View')->getJsTranslatorRenderer()->addTranslations(
            array(
                'Save Product Type Settings' => Mage::helper('M2ePro')->__('Save Product Type Settings'),
                'Delete Product Type' => Mage::helper('M2ePro')->__('Delete Product Type'),
                'Search Product Type' => Mage::helper('M2ePro')->__('Search Product Type'),
            )
        );

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addUrls(array(
                'formSubmit' => $this->getUrl(
                    '*/adminhtml_walmart_productType/save',
                    array('id' => $this->productType->getId())
                ),
                'deleteAction' => $this->getUrl(
                    '*/adminhtml_walmart_productType/delete',
                    array('id' => $this->productType->getId())
                ),
            )
        );

        return parent::_prepareLayout();
    }

    protected function _beforeToHtml()
    {
        $this->setDataObject($this->productType);

        return parent::_beforeToHtml();
    }
}
