<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Transferring_Step_Policy extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingTransferringStepPolicy');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/listing/transferring/step/policy.phtml');
    }

    //########################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/adminhtml_ebay_template/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------
        $data = array(
            'id'      => 'back_button_policy',
            'class'   => 'back back_button',
            'label'   => Mage::helper('M2ePro')->__('Back'),
            'onclick' => 'EbayListingTransferringHandlerObj.back();',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('back_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'id'      => 'continue_button_policy',
            'class'   => 'next continue_button',
            'label'   => Mage::helper('M2ePro')->__('Continue'),
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('continue_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'id'      => 'confirm_button_policy',
            'class'   => 'confirm_button',
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => 'EbayListingTransferringHandlerObj.confirm();',
            'style'   => 'display: none;'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('confirm_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $params = array(
            'allowed_tabs'        => array('general'),
            'policy_localization' => $this->getData('policy_localization')
        );
        $templatesBlock = $this->getLayout()
                               ->createBlock('M2ePro/adminhtml_ebay_listing_transferring_template_edit', '', $params);

        $this->setChild('templates', $templatesBlock);
        // ---------------------------------------
    }

    //########################################

    public function isAllowedStep()
    {
        return (bool)$this->getData('is_allowed');
    }

    //########################################

    public function isUseCustomSettings()
    {
        $productsIds = $this->getData('products_ids');
        $productsIds = explode(',', $productsIds);
        $productsIds = array_filter($productsIds);

        if (empty($productsIds)) {
            return false;
        }

        // ---------------------------------------
        $paymentTemplateColumnName = Mage::getModel('M2ePro/Ebay_Template_Manager')
            ->setTemplate(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT)->getModeColumnName();
        $shippingTemplateColumnName = Mage::getModel('M2ePro/Ebay_Template_Manager')
            ->setTemplate(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING)->getModeColumnName();
        $returnTemplateColumnName = Mage::getModel('M2ePro/Ebay_Template_Manager')
            ->setTemplate(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN)->getModeColumnName();
        // ---------------------------------------

        $listingProducts = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product')
            ->addFieldToFilter('id', array('in' => $productsIds))
            ->addFieldToFilter($paymentTemplateColumnName,  Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT)
            ->addFieldToFilter($shippingTemplateColumnName, Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT)
            ->addFieldToFilter($returnTemplateColumnName,   Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT);

        return (int)$listingProducts->getSize() != count($productsIds);
    }

    //########################################
}