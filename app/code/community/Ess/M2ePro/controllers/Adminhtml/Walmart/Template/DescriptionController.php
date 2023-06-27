<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Walmart_Template_DescriptionController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Policies'))
             ->_title(Mage::helper('M2ePro')->__('Description Policies'));

        $this->getLayout()->getBlock('head')
                ->addJs('M2ePro/Template/Edit.js')
                ->addJs('M2ePro/Walmart/Template/Edit.js')
                ->addJs('M2ePro/Walmart/Template/Description.js')
                ->addJs('M2ePro/Attribute.js');

        $this->_initPopUp();

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
        return $this->_redirect('*/adminhtml_walmart_template/index');
    }

    public function gridAction()
    {
        $block = $this->loadLayout()->getLayout()
                                    ->createBlock('M2ePro/adminhtml_walmart_template_description_grid');

        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        /** @var Ess_M2ePro_Model_Walmart_Template_Description $templateModel */
        $id = $this->getRequest()->getParam('id');
        $templateModel = Mage::helper('M2ePro/Component_Walmart')->getModel('Template_Description')->load($id);

        if (!$templateModel->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Policy does not exist'));
            return $this->_redirect('*/*/index');
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $templateModel);

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_walmart_template_description_edit'))
            ->renderLayout();
    }

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->_redirect('*/*/index');
        }

        $id = $this->getRequest()->getParam('id');

        /** @var Ess_M2ePro_Model_Template_Description $descriptionTemplate */
        $descriptionTemplate = Mage::helper('M2ePro/Component_Walmart')->getModel('Template_Description')->load($id);

        $oldData = array();
        if ($descriptionTemplate->getId()) {
            $snapshotBuilder = Mage::getModel('M2ePro/Walmart_Template_Description_SnapshotBuilder');
            $snapshotBuilder->setModel($descriptionTemplate);

            $oldData = $snapshotBuilder->getSnapshot();
        }

        Mage::getModel('M2ePro/Walmart_Template_Description_Builder')->build($descriptionTemplate, $post);

        $id = $descriptionTemplate->getId();

        // Is Need Synchronize
        // ---------------------------------------
        $snapshotBuilder = Mage::getModel('M2ePro/Walmart_Template_Description_SnapshotBuilder');
        $snapshotBuilder->setModel($descriptionTemplate);
        $newData = $snapshotBuilder->getSnapshot();

        $diff = Mage::getModel('M2ePro/Walmart_Template_Description_Diff');
        $diff->setNewSnapshot($newData);
        $diff->setOldSnapshot($oldData);

        $affectedListingsProducts = Mage::getModel('M2ePro/Walmart_Template_Description_AffectedListingsProducts');
        $affectedListingsProducts->setModel($descriptionTemplate);

        $changeProcessor = Mage::getModel('M2ePro/Walmart_Template_Description_ChangeProcessor');
        $changeProcessor->process(
            $diff, $affectedListingsProducts->getData(array('id', 'status'))
        );
        // ---------------------------------------

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Policy was saved'));
        return $this->_redirectUrl(
            Mage::helper('M2ePro')->getBackUrl('list', array(), array('edit' => array('id' => $id)))
        );
    }

    // ---------------------------------------

    public function deleteAction()
    {
        $ids = $this->getRequestIds();

        if (empty($ids)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select Item(s) to remove.'));
            $this->_redirect('*/*/index');
            return;
        }

        $deleted = $locked = 0;
        foreach ($ids as $id) {
            $template = Mage::helper('M2ePro/Component')->getUnknownObject('Template_Description', $id);
            if ($template->isLocked()) {
                $locked++;
            } else {
                $template->deleteInstance();
                $deleted++;
            }
        }

        $tempString = Mage::helper('M2ePro')->__('%s record(s) were deleted.', $deleted);
        $deleted && $this->_getSession()->addSuccess($tempString);

        $tempString  = Mage::helper('M2ePro')->__('%s record(s) are used in Listing(s).', $locked) . ' ';
        $tempString .= Mage::helper('M2ePro')->__('Policy must not be in use to be deleted.');
        $locked && $this->_getSession()->addError($tempString);

        $this->_redirect('*/*/index');
    }

    //########################################
}
