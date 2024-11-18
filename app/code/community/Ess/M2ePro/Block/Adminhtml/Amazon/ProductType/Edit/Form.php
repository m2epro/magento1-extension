<?php

class Ess_M2ePro_Block_Adminhtml_Amazon_ProductType_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    public function _construct()
    {
        parent::_construct();

        $this->setId('amazonProductTypeEditForm');
    }

    protected function _prepareForm()
    {
        /** @var Ess_M2ePro_Model_Amazon_Template_ProductType $productType */
        $productType = $this->getData('data_object');

        $form = new Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form(
            array(
                'id' => 'edit_form',
                'method' => 'post',
                'action' => $this->getUrl('*/*/save'),
                'enctype' => 'multipart/form-data',
            )
        );

        $form->addField(
            'is_new_product_type',
            'hidden',
            array(
                'value' => $productType->isObjectNew() ? '1' : '0',
                'name' => 'is_new_product_type'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}

