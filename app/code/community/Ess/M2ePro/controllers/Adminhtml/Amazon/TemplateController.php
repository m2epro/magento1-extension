<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Amazon_TemplateController
    extends Ess_M2ePro_Controller_Adminhtml_Amazon_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Policies'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addJs('M2ePro/Plugin/ActionColumn.js')
            ->addCss('M2ePro/css/Plugin/DropDown.css');

        $this->setPageHelpLink(null, null, "configurations");

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Amazon::MENU_ROOT_NODE_NICK . '/configuration'
        );
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_amazon_configuration', '',
                    array('active_tab' => Ess_M2ePro_Block_Adminhtml_Amazon_Configuration_Tabs::TAB_ID_TEMPLATE)
                )
            )->renderLayout();
    }

    public function gridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_amazon_template_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function newAction()
    {
        $type    = $this->getPreparedTemplateType($this->getRequest()->getParam('type'));

        if (!$type) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('You should provide correct parameters.'));
            return $this->_redirect('*/*/index');
        }

        return $this->_redirect("*/adminhtml_amazon_template_{$type}/edit");
    }

    //########################################

    public function editAction()
    {
        $id   = $this->getRequest()->getParam('id');
        $type = $this->getPreparedTemplateType($this->getRequest()->getParam('type'));

        if ($id === null || empty($type)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('You should provide correct parameters.'));
            return $this->_redirect('*/*/index');
        }

        if ($type == 'shipping') {
            return $this->_redirect(
                "*/adminhtml_amazon_template_shipping/edit", array('id'=>$id)
            );
        }

        if ($type == 'productTaxCode') {
            return $this->_redirect(
                "*/adminhtml_amazon_template_productTaxCode/edit", array('id'=>$id)
            );
        }

        return $this->_redirect(
            "*/adminhtml_amazon_template_{$type}/edit", array('id'=>$id)
        );
    }

    public function deleteAction()
    {
        $ids  = $this->getRequestIds();
        $type = $this->getPreparedTemplateType($this->getRequest()->getParam('type'));

        if (empty($ids)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select Item(s) to remove.'));
            return $this->_redirect('*/*/index');
        }

        if (empty($type)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('You should provide correct parameters.'));
            return $this->_redirect('*/*/index');
        }

        $deleted = $locked = 0;
        foreach ($ids as $id) {

            /** @var Ess_M2ePro_Model_Component_Parent_Abstract $templateModel */
            $templateModel = $this->getTemplateModel($type, $id);

            if ($templateModel->isLocked()) {
                $locked++;
            } else {
                $templateModel->deleteInstance();
                $deleted++;
            }
        }

        $tempString = Mage::helper('M2ePro')->__('%amount% record(s) were deleted.', $deleted);
        $deleted && $this->_getSession()->addSuccess($tempString);

        $tempString  = Mage::helper('M2ePro')->__('%amount% record(s) are used in Listing(s).', $locked) . ' ';
        $tempString .= Mage::helper('M2ePro')->__('Policy must not be in use to be deleted.');
        $locked && $this->_getSession()->addError($tempString);

        $this->_redirect('*/*/index');
    }

    //########################################

    protected function getPreparedTemplateType($type)
    {
        $templateTypes = array(
            Ess_M2ePro_Block_Adminhtml_Amazon_Template_Grid::TEMPLATE_SELLING_FORMAT,
            Ess_M2ePro_Block_Adminhtml_Amazon_Template_Grid::TEMPLATE_SYNCHRONIZATION,

            Ess_M2ePro_Block_Adminhtml_Amazon_Template_Grid::TEMPLATE_DESCRIPTION,
            Ess_M2ePro_Block_Adminhtml_Amazon_Template_Grid::TEMPLATE_SHIPPING,
            Ess_M2ePro_Block_Adminhtml_Amazon_Template_Grid::TEMPLATE_PRODUCT_TAX_CODE
        );

        if (!in_array(strtolower($type), $templateTypes)) {
            return null;
        }

        if (strtolower($type) == Ess_M2ePro_Block_Adminhtml_Amazon_Template_Grid::TEMPLATE_SELLING_FORMAT) {
            return 'sellingFormat';
        }

        if (strtolower($type) == Ess_M2ePro_Block_Adminhtml_Amazon_Template_Grid::TEMPLATE_SHIPPING) {
            return 'shipping';
        }

        if (strtolower($type) == Ess_M2ePro_Block_Adminhtml_Amazon_Template_Grid::TEMPLATE_PRODUCT_TAX_CODE) {
            return 'productTaxCode';
        }

        return $type;
    }

    protected function getTemplateModel($type, $id)
    {
        if ($type == 'shipping') {
            return Mage::getModel('M2ePro/Amazon_Template_Shipping')->load($id);
        }

        if ($type == 'productTaxCode') {
            return Mage::getModel('M2ePro/Amazon_Template_ProductTaxCode')->load($id);
        }

        return Mage::helper('M2ePro/Component')->getUnknownObject('Template_' . ucfirst($type), $id);
    }

    //########################################
}