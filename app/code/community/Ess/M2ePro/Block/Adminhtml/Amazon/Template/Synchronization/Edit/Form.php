<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Template_Synchronization_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonTemplateSynchronizationEditForm');
        $this->setTemplate('M2ePro/amazon/template/synchronization/form.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->setChild(
            'amazon_template_synchronization_edit_form_tabs_list',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_amazon_template_synchronization_edit_tabs_list',
                '',
                array(
                    'form_data' => $this->getFormData()
                )
            )
        );

        $this->setChild(
            'amazon_template_synchronization_edit_form_tabs_revise',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_amazon_template_synchronization_edit_tabs_revise',
                '',
                array(
                    'form_data' => $this->getFormData()
                )
            )
        );

        $this->setChild(
            'amazon_template_synchronization_edit_form_tabs_relist',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_amazon_template_synchronization_edit_tabs_relist',
                '',
                array(
                    'form_data' => $this->getFormData()
                )
            )
        );

        $this->setChild(
            'amazon_template_synchronization_edit_form_tabs_stop',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_amazon_template_synchronization_edit_tabs_stop',
                '',
                array(
                    'form_data' => $this->getFormData()
                )
            )
        );

        return parent::_beforeToHtml();
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    //########################################

    public function getFormData()
    {
        $template = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        if ($template === null || $template->getId() === null) {
            return array();
        }

        return $template->getData();
    }

    //########################################
}
