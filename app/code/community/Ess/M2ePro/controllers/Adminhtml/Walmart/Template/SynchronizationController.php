<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Walmart_Template_SynchronizationController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Policies'))
             ->_title(Mage::helper('M2ePro')->__('Synchronization Policies'));

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addJs('mage/adminhtml/rules.js')
            ->addJs('M2ePro/Template/Edit.js')
            ->addJs('M2ePro/Walmart/Template/Edit.js')
            ->addJs('M2ePro/Walmart/Template/Synchronization.js');

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

    //########################################

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id    = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Walmart')->getModel('Template_Synchronization')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Policy does not exist'));
            return $this->_redirect('*/adminhtml_walmart_template/index');
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

        return $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_template_synchronization_edit')
            )
            ->renderLayout();
    }

    //########################################

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->indexAction();
        }

        $id = $this->getRequest()->getParam('id');

        $model = Mage::helper('M2ePro/Component_Walmart')->getModel('Template_Synchronization');
        $model->load($id);

        $snapshotBuilder = Mage::getModel('M2ePro/Walmart_Template_Synchronization_SnapshotBuilder');
        $snapshotBuilder->setModel($model);
        $oldData = $snapshotBuilder->getSnapshot();

        Mage::getModel('M2ePro/Walmart_Template_Synchronization_Builder')->build($model, $post);

        $snapshotBuilder = Mage::getModel('M2ePro/Walmart_Template_Synchronization_SnapshotBuilder');
        $snapshotBuilder->setModel($model);
        $newData = $snapshotBuilder->getSnapshot();

        $diff = Mage::getModel('M2ePro/Walmart_Template_Synchronization_Diff');
        $diff->setNewSnapshot($newData);
        $diff->setOldSnapshot($oldData);

        $affectedListingsProducts = Mage::getModel('M2ePro/Walmart_Template_Synchronization_AffectedListingsProducts');
        $affectedListingsProducts->setModel($model);

        $changeProcessor = Mage::getModel('M2ePro/Walmart_Template_Synchronization_ChangeProcessor');
        $changeProcessor->process(
            $diff, $affectedListingsProducts->getData(array('id', 'status'))
        );

        $id = $model->getId();
        // ---------------------------------------

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Policy was saved'));
        return $this->_redirectUrl(
            Mage::helper('M2ePro')->getBackUrl(
                '*/adminhtml_walmart_template/index', array(), array('edit' => array('id'=>$id))
            )
        );
    }

    protected function getRuleData($rulePrefix, $post)
    {
        if (empty($post['rule'][$rulePrefix])) {
            return null;
        }

        $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setData(
            array('prefix' => $rulePrefix)
        );

        return $ruleModel->getSerializedFromPost($post);
    }

    //########################################

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
            $template = Mage::helper('M2ePro/Component')->getUnknownObject('Template_Synchronization', $id);
            if ($template->isLocked()) {
                $locked++;
            } else {
                $template->deleteInstance();
                $deleted++;
            }
        }

        $tempString = Mage::helper('M2ePro')->__('%amount% record(s) were deleted.', $deleted);
        $deleted && $this->_getSession()->addSuccess($tempString);

        $tempString  = Mage::helper('M2ePro')->__('%amount% record(s) are used in Listing(s).', $locked) . ' ';
        $tempString .= Mage::helper('M2ePro')->__('Policy must not be in use to be deleted.');
        $locked && $this->_getSession()->addError($tempString);

        $this->_redirect('*/adminhtml_walmart_template/index');
    }

    //########################################
}
