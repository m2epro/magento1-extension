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
            ->addJs('M2ePro/Template/EditHandler.js')
            ->addJs('M2ePro/Walmart/Template/EditHandler.js')
            ->addJs('M2ePro/Walmart/Template/SynchronizationHandler.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "x/L4taAQ");

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

        // Base prepare
        // ---------------------------------------
        $data = array();
        // ---------------------------------------

        // tab: list
        // ---------------------------------------
        $keys = array(
            'title',
            'list_mode',
            'list_status_enabled',
            'list_is_in_stock',
            'list_qty_magento',
            'list_qty_magento_value',
            'list_qty_magento_value_max',
            'list_qty_calculated',
            'list_qty_calculated_value',
            'list_qty_calculated_value_max',
            'list_advanced_rules_mode'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $data['list_advanced_rules_filters'] = $this->getRuleData(
            Ess_M2ePro_Model_Walmart_Template_Synchronization::LIST_ADVANCED_RULES_PREFIX,
            $post
        );

        $data['title'] = strip_tags($data['title']);
        // ---------------------------------------

        // tab: revise
        // ---------------------------------------
        $keys = array(
            'revise_update_qty_max_applied_value_mode',
            'revise_update_qty_max_applied_value',
            'revise_update_price',
            'revise_update_price_max_allowed_deviation_mode',
            'revise_update_price_max_allowed_deviation',
            'revise_update_promotions',
            'revise_update_details',
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $data['revise_update_qty'] = 1;
        // ---------------------------------------

        // tab: relist
        // ---------------------------------------
        $keys = array(
            'relist_mode',
            'relist_filter_user_lock',
            'relist_status_enabled',
            'relist_is_in_stock',
            'relist_qty_magento',
            'relist_qty_magento_value',
            'relist_qty_magento_value_max',
            'relist_qty_calculated',
            'relist_qty_calculated_value',
            'relist_qty_calculated_value_max',
            'relist_advanced_rules_mode'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $data['relist_advanced_rules_filters'] = $this->getRuleData(
            Ess_M2ePro_Model_Walmart_Template_Synchronization::RELIST_ADVANCED_RULES_PREFIX,
            $post
        );

        // ---------------------------------------

        // tab: stop
        // ---------------------------------------
        $keys = array(
            'stop_mode',
            'stop_status_disabled',
            'stop_out_off_stock',
            'stop_qty_magento',
            'stop_qty_magento_value',
            'stop_qty_magento_value_max',
            'stop_qty_calculated',
            'stop_qty_calculated_value',
            'stop_qty_calculated_value_max',
            'stop_advanced_rules_mode'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $data['stop_advanced_rules_filters'] = $this->getRuleData(
            Ess_M2ePro_Model_Walmart_Template_Synchronization::STOP_ADVANCED_RULES_PREFIX,
            $post
        );

        // ---------------------------------------

        // Add or update model
        // ---------------------------------------
        $model = Mage::helper('M2ePro/Component_Walmart')->getModel('Template_Synchronization');
        $model->load($id);

        $snapshotBuilder = Mage::getModel('M2ePro/Walmart_Template_Synchronization_SnapshotBuilder');
        $snapshotBuilder->setModel($model);
        $oldData = $snapshotBuilder->getSnapshot();

        $model->addData($data)->save();

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

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Policy was successfully saved'));
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

        $tempString = Mage::helper('M2ePro')->__('%amount% record(s) were successfully deleted.', $deleted);
        $deleted && $this->_getSession()->addSuccess($tempString);

        $tempString  = Mage::helper('M2ePro')->__('%amount% record(s) are used in Listing(s).', $locked) . ' ';
        $tempString .= Mage::helper('M2ePro')->__('Policy must not be in use to be deleted.');
        $locked && $this->_getSession()->addError($tempString);

        $this->_redirect('*/adminhtml_walmart_template/index');
    }

    //########################################
}
