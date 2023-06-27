<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Walmart_TemplateController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
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

        $this->setPageHelpLink(null, null, "walmart-integration");

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Walmart::MENU_ROOT_NODE_NICK . '/configuration'
        );
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_walmart_configuration', '',
                    array('active_tab' => Ess_M2ePro_Block_Adminhtml_Walmart_Configuration_Tabs::TAB_ID_TEMPLATE)
                )
            )->renderLayout();
    }

    public function gridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_walmart_template_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function newAction()
    {
        $type = $this->getPreparedTemplateType($this->getRequest()->getParam('type'));

        if (!$type) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('You should provide correct parameters.'));
            return $this->_redirect('*/*/index');
        }

        return $this->_redirect("*/adminhtml_walmart_template_{$type}/edit");
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

        return $this->_redirect(
            "*/adminhtml_walmart_template_{$type}/edit", array('id'=>$id)
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
            Ess_M2ePro_Block_Adminhtml_Walmart_Template_Grid::TEMPLATE_CATEGORY        => 'category',
            Ess_M2ePro_Block_Adminhtml_Walmart_Template_Grid::TEMPLATE_DESCRIPTION     => 'description',
            Ess_M2ePro_Block_Adminhtml_Walmart_Template_Grid::TEMPLATE_SELLING_FORMAT  => 'sellingFormat',
            Ess_M2ePro_Block_Adminhtml_Walmart_Template_Grid::TEMPLATE_SYNCHRONIZATION => 'synchronization'
        );

        $type = strtolower($type);
        return isset($templateTypes[$type]) ? $templateTypes[$type] : null;
    }

    protected function getTemplateModel($type, $id)
    {
        if ($type == 'category') {
            return Mage::getModel('M2ePro/Walmart_Template_Category')->load($id);
        }

        return Mage::helper('M2ePro/Component')->getUnknownObject('Template_' . ucfirst($type), $id);
    }

    //########################################
}