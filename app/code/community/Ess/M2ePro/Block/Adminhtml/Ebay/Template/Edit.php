<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // ---------------------------------------
        $args = func_get_args();
        if (empty($args[0]) || !is_array($args[0])) {
            $args[0] = array();
        }
        $this->addData($args[0]);
        // ---------------------------------------

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayTemplateEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_template';
        $this->_mode = 'edit';
        // ---------------------------------------

        // ---------------------------------------
        $nick = $this->getTemplateNick();
        $template = Mage::helper('M2ePro/Data_Global')->getValue("ebay_template_{$nick}");
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        if ($template->getId()) {
            $this->_headerText =
                Mage::helper('M2ePro')->__('Edit "%template_title%" %template_name% Policy',
                $this->escapeHtml($template->getTitle()),
                $this->getTemplateName()
            );
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('Add %template_name% Policy',
                $this->getTemplateName());
        }
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        // ---------------------------------------
        if ((bool)$this->getRequest()->getParam('back',false)) {
            $url = $this->getUrl('*/adminhtml_ebay_template/index');
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'EbayTemplateEditHandlerObj.back_click(\'' . $url . '\')',
                'class'     => 'back'
            ));
        }
        // ---------------------------------------

        // ---------------------------------------
        if ($template->getId() && !(bool)$this->getRequest()->getParam('wizard',false)) {
            $duplicateHeaderText = Mage::helper('M2ePro')->escapeJs(
                Mage::helper('M2ePro')->__('Add %template_name% Policy', $this->getTemplateName())
            );

            $onclickHandler = $nick == Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION
                ? 'EbayTemplateDescriptionHandlerObj'
                : 'EbayTemplateEditHandlerObj';

            $this->_addButton('duplicate', array(
                'label'     => Mage::helper('M2ePro')->__('Duplicate'),
                'onclick'   => $onclickHandler.'.duplicate_click(
                    \'ebay-template\', \''.$duplicateHeaderText.'\', \''.$nick.'\'
                )',
                'class'     => 'add M2ePro_duplicate_button'
            ));
        }
        // ---------------------------------------

        // ---------------------------------------
        if ($template->getId() && !(bool)$this->getRequest()->getParam('wizard',false)) {
            $url = $this->getUrl('*/adminhtml_ebay_template/delete');
            $this->_addButton('delete', array(
                'label'     => Mage::helper('M2ePro')->__('Delete'),
                'onclick'   => 'EbayTemplateEditHandlerObj.delete_click(\'' . $url . '\')',
                'class'     => 'delete M2ePro_delete_button'
            ));
        }
        // ---------------------------------------

        $saveConfirmation = '';
        if ($template->getId()) {
            $saveConfirmation = Mage::helper('M2ePro')->escapeJs(
                Mage::helper('M2ePro')->__('<br/>
<b>Note:</b> All changes you have made will be automatically applied to all M2E Pro Listings where this Policy is used.'
                )
            );
        }

        // ---------------------------------------
        if (!(bool)$this->getRequest()->getParam('wizard',false)) {
            $url = $this->getUrl('*/adminhtml_ebay_template/save');
            $this->_addButton('save', array(
                'label'     => Mage::helper('M2ePro')->__('Save'),
                'onclick'   => 'EbayTemplateEditHandlerObj.save_click('
                    . '\'' . $url . '\','
                    . '\'' . $saveConfirmation . '\','
                    . '\'' . $nick . '\''
                . ')',
                'class'     => 'save'
            ));
        }
        // ---------------------------------------

        // ---------------------------------------
        $backUrl = Mage::helper('M2ePro')->makeBackUrlParam('edit', array());
        $url = $this->getUrl('*/adminhtml_ebay_template/save', array('back' => $backUrl));
        $this->_addButton('save_and_continue', array(
            'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
            'onclick'   => 'EbayTemplateEditHandlerObj.save_and_edit_click('
                . '\'' . $url . '\','
                . '\'\','
                . '\'' . $saveConfirmation . '\','
                . '\'' . $nick . '\''
            . ')',
            'class'     => 'save'
        ));
        // ---------------------------------------
    }

    //########################################

    public function getTemplateNick()
    {
        if (!isset($this->_data['template_nick'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Policy nick is not set.');
        }

        return $this->_data['template_nick'];
    }

    public function getTemplateObject()
    {
        $nick = $this->getTemplateNick();
        $template = Mage::helper('M2ePro/Data_Global')->getValue("ebay_template_{$nick}");

        return $template;
    }

    public function getTemplateName()
    {
        $title = '';

        switch ($this->getTemplateNick()) {
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT:
                $title = Mage::helper('M2ePro')->__('Payment');
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING:
                $title = Mage::helper('M2ePro')->__('Shipping');
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN:
                $title = Mage::helper('M2ePro')->__('Return');
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT:
                $title = Mage::helper('M2ePro')->__('Price, Quantity and Format');
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION:
                $title = Mage::helper('M2ePro')->__('Description');
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION:
                $title = Mage::helper('M2ePro')->__('Synchronization');
                break;
        }

        return $title;
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $general = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_template_edit_general');
        $this->setChild('general', $general);
    }

    public function getFormHtml()
    {
        return $this->getChildHtml('general') . parent::getFormHtml();
    }

    //########################################
}