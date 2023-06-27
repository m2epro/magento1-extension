<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Walmart_Template_SellingFormatController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Policies'))
             ->_title(Mage::helper('M2ePro')->__('Selling Policies'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Template/Edit.js')
            ->addJs('M2ePro/Walmart/Template/Edit.js')
            ->addJs('M2ePro/Walmart/Template/SellingFormat.js')
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

    //########################################

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id    = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Walmart')->getModel('Template_SellingFormat')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Policy does not exist'));
            return $this->_redirect('*/adminhtml_walmart_template/index');
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_template_sellingFormat_edit')
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

        $model = Mage::helper('M2ePro/Component_Walmart')->getModel('Template_SellingFormat')->load($id);

        $snapshotBuilder = Mage::getModel('M2ePro/Walmart_Template_SellingFormat_SnapshotBuilder');
        $snapshotBuilder->setModel($model);
        $oldData = $snapshotBuilder->getSnapshot();

        Mage::getModel('M2ePro/Walmart_Template_SellingFormat_Builder')->build($model, $post);

        $this->updateServices($post, $model->getId());
        $this->updatePromotions($post, $model->getId());

        $snapshotBuilder = Mage::getModel('M2ePro/Walmart_Template_SellingFormat_SnapshotBuilder');
        $snapshotBuilder->setModel($model);
        $newData = $snapshotBuilder->getSnapshot();

        $diff = Mage::getModel('M2ePro/Walmart_Template_SellingFormat_Diff');
        $diff->setNewSnapshot($newData);
        $diff->setOldSnapshot($oldData);

        $affectedListingsProducts = Mage::getModel('M2ePro/Walmart_Template_SellingFormat_AffectedListingsProducts');
        $affectedListingsProducts->setModel($model);

        $changeProcessor = Mage::getModel('M2ePro/Walmart_Template_SellingFormat_ChangeProcessor');
        $changeProcessor->process(
            $diff,
            $affectedListingsProducts->getData(
                array('id', 'status'), array('only_physical_units' => true)
            )
        );

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Policy was saved'));

        $params = array('id' => $model->getId());
        if ($this->getRequest()->getParam('marketplace_locked')) {
            $params['marketplace_id'] = $model->getData('marketplace_id');
        }

        $url = Mage::helper('M2ePro')->getBackUrl(
            '*/adminhtml_walmart_template/index', array(), array(
            'edit' => $params
            )
        );

        return $this->_redirectUrl($url);
    }

    protected function updateServices($data, $templateId)
    {
        $collection = Mage::getModel('M2ePro/Walmart_Template_SellingFormat_ShippingOverride')
            ->getCollection()
            ->addFieldToFilter('template_selling_format_id', (int)$templateId);

        foreach ($collection as $item) {
            $item->delete();
        }

        if (empty($data['shipping_override_rule'])) {
            return;
        }

        /** @var Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverride_Builder $shippingOverrideBuilder */
        $shippingOverrideBuilder = Mage::getModel('M2ePro/Walmart_Template_SellingFormat_ShippingOverride_Builder');

        foreach ($data['shipping_override_rule'] as $serviceData) {
            /** @var Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverride $shippingOverrideInstance */
            $shippingOverrideInstance = Mage::getModel('M2ePro/Walmart_Template_SellingFormat_ShippingOverride');
            $shippingOverrideBuilder->setTemplateSellingFormatId($templateId);
            $shippingOverrideBuilder->build($shippingOverrideInstance, $serviceData);
        }
    }

    protected function updatePromotions($data, $templateId)
    {
        $collection = Mage::getModel('M2ePro/Walmart_Template_SellingFormat_Promotion')->getCollection()
                            ->addFieldToFilter('template_selling_format_id', (int)$templateId);

        foreach ($collection as $item) {
            $item->delete();
        }

        if (empty($data['promotions'])) {
            return;
        }

        /** @var Ess_M2ePro_Model_Walmart_Template_SellingFormat_Promotion_Builder $promotionBuilder */
        $promotionBuilder = Mage::getModel('M2ePro/Walmart_Template_SellingFormat_Promotion_Builder');

        foreach ($data['promotions'] as $promotionData) {
            /** @var Ess_M2ePro_Model_Walmart_Template_SellingFormat_Promotion $promotionInstance */
            $promotionInstance = Mage::getModel('M2ePro/Walmart_Template_SellingFormat_Promotion');
            $promotionBuilder->setTemplateSellingFormatId($templateId);
            $promotionBuilder->build($promotionInstance, $promotionData);
        }
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
            $template = Mage::helper('M2ePro/Component')->getUnknownObject('Template_SellingFormat', $id);
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
