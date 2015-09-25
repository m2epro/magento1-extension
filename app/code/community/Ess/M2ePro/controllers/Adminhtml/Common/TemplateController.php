<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Common_TemplateController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Policies'))
            ->_title(Mage::helper('M2ePro')->__('Selling Format Policies'));

        $this->getLayout()->getBlock('head')->addJs('M2ePro/Plugin/DropDown.js')
            ->addCss('M2ePro/css/Plugin/DropDown.css');

        $this->setComponentPageHelpLink('Policies');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/configuration');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_common_configuration', '',
                    array('active_tab' => Ess_M2ePro_Block_Adminhtml_Common_Configuration_Tabs::TAB_ID_TEMPLATE)
                )
            )->renderLayout();
    }

    public function gridAction()
    {
        $channel = $this->getRequest()->getParam('channel');

        if (empty($channel)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_common_'.$channel.'_template_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //#############################################

    public function newAction()
    {
        $type = $this->getRequest()->getParam('type');
        $channel = $this->getRequest()->getParam('channel');

        if (empty($type)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('You should provide correct parameters.'));
            return $this->_redirect('*/*/index', array(
                'channel' => $this->getRequest()->getParam('channel')
            ));
        }

        if ($type == Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_Grid::TEMPLATE_SHIPPING_OVERRIDE) {
            return $this->_redirect(
                "*/adminhtml_common_amazon_template_shippingOverride/edit"
            );
        }

        $type = $this->prepareTemplateType($type);

        return $this->_redirect("*/adminhtml_common_{$channel}_template_{$type}/edit");
    }

    //#############################################

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $type = $this->getRequest()->getParam('type');

        if (is_null($id) || empty($type)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('You should provide correct parameters.'));
            return $this->_redirect('*/*/index', array(
                'channel' => $this->getRequest()->getParam('channel')
            ));
        }

        if ($type == Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_Grid::TEMPLATE_SHIPPING_OVERRIDE) {
            return $this->_redirect(
                "*/adminhtml_common_amazon_template_shippingOverride/edit", array('id'=>$id)
            );
        }

        $type = $this->prepareTemplateType($type);

        $template = Mage::helper('M2ePro/Component')->getUnknownObject('Template_' . $type, $id);
        return $this->_redirect(
            "*/adminhtml_common_{$template->getComponentMode()}_template_{$type}/edit", array('id'=>$id)
        );
    }

    public function deleteAction()
    {
        $ids = $this->getRequestIds();
        $type = $this->getRequest()->getParam('type');

        if (count($ids) == 0) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select Item(s) to remove.'));
            return $this->_redirect('*/*/index', array(
                'channel' => $this->getRequest()->getParam('channel')
            ));
        }

        if (empty($type)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('You should provide correct parameters.'));
            return $this->_redirect('*/*/index', array(
                'channel' => $this->getRequest()->getParam('channel')
            ));
        }

        $type = $this->prepareTemplateType($type);

        $deleted = $locked = 0;

        foreach ($ids as $id) {
            if (strtolower($type)==Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_Grid::TEMPLATE_SHIPPING_OVERRIDE) {
                $template = Mage::getModel('M2ePro/Amazon_Template_ShippingOverride')->load($id);
            } else {
                $template = Mage::helper('M2ePro/Component')->getUnknownObject('Template_' . $type, $id);
            }

            if ($template->isLocked()) {
                $locked++;
            } else {
                $template->deleteInstance();
                $deleted++;
            }
        }

        $tempString = Mage::helper('M2ePro')->__('%amount% record(s) were successfully deleted.', $deleted);
        $deleted && $this->_getSession()->addSuccess($tempString);

        $tempString  = Mage::helper('M2ePro')->__('%amount% record(s) are used in Listing(s).', $locked) . ' ';
        $tempString .= Mage::helper('M2ePro')->__('Policy must not be in use to be deleted.');
        $locked && $this->_getSession()->addError($tempString);

        $this->_redirect('*/*/index', array(
            'channel' => $this->getRequest()->getParam('channel')
        ));
    }

    //#############################################

    private function prepareTemplateType($type)
    {
        return $type == Ess_M2ePro_Block_Adminhtml_Common_Template_Grid::TEMPLATE_SELLING_FORMAT ?
            'SellingFormat' : ucfirst($type);
    }

    //#############################################
}