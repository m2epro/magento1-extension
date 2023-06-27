<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Amazon_Template_SellingFormatController
    extends Ess_M2ePro_Controller_Adminhtml_Amazon_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Policies'))
             ->_title(Mage::helper('M2ePro')->__('Selling Policies'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Template/Edit.js')
            ->addJs('M2ePro/Amazon/Template/Edit.js')
            ->addJs('M2ePro/Amazon/Template/SellingFormat.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "selling-policies");

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
        return $this->_redirect('*/adminhtml_amazon_template/index');
    }

    //########################################

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id    = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Template_SellingFormat')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Policy does not exist'));
            return $this->_redirect('*/adminhtml_amazon_template/index');
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_template_sellingFormat_edit')
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

        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Template_SellingFormat')->load($id);

        $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Template_SellingFormat_SnapshotBuilder');
        $snapshotBuilder->setModel($model);
        $oldData = $snapshotBuilder->getSnapshot();

        Mage::getModel('M2ePro/Amazon_Template_SellingFormat_Builder')->build($model, $post);
        if (Mage::helper('M2ePro/Component_Amazon_Configuration')->isEnabledBusinessMode()) {
            $this->saveDiscounts($model->getId(), $post);
        }

        $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Template_SellingFormat_SnapshotBuilder');
        $snapshotBuilder->setModel($model);
        $newData = $snapshotBuilder->getSnapshot();

        $diff = Mage::getModel('M2ePro/Amazon_Template_SellingFormat_Diff');
        $diff->setNewSnapshot($newData);
        $diff->setOldSnapshot($oldData);

        $affectedListingsProducts = Mage::getModel('M2ePro/Amazon_Template_SellingFormat_AffectedListingsProducts');
        $affectedListingsProducts->setModel($model);

        $changeProcessor = Mage::getModel('M2ePro/Amazon_Template_SellingFormat_ChangeProcessor');
        $changeProcessor->process(
            $diff, $affectedListingsProducts->getData(array('id', 'status'), array('only_physical_units' => true))
        );

        $id = $model->getId();

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Policy was saved'));
        $this->_redirectUrl(
            Mage::helper('M2ePro')->getBackUrl(
                '*/adminhtml_amazon_template/index', array(), array(
                'edit' => array('id'=>$id)
                )
            )
        );
    }

    //########################################

    protected function saveDiscounts($templateId, $post)
    {
        $coreRes = Mage::getSingleton('core/resource');
        $connWrite = $coreRes->getConnection('core_write');

        $connWrite->delete(
            Mage::getResourceModel('M2ePro/Amazon_Template_SellingFormat_BusinessDiscount')->getMainTable(),
            array(
                'template_selling_format_id = ?' => (int)$templateId
            )
        );

        if (empty($post['is_business_customer_allowed']) ||
            empty($post['business_discount']) || empty($post['business_discount']['qty'])
        ) {
            return;
        }

        $discounts = array();
        foreach ($post['business_discount']['qty'] as $i => $qty) {
            $attribute = empty($post['business_discount']['attribute']) ?
                '' : $post['business_discount']['attribute'][$i];

            $mode = empty($post['business_discount']['mode'][$i]) ?
                '' : $post['business_discount']['mode'][$i];

            $coefficient = empty($post['business_discount']['coefficient'][$i]) ?
                '' : $post['business_discount']['coefficient'][$i];

            $discounts[] = array(
                'template_selling_format_id' => $templateId,
                'qty' => $qty,
                'mode' => $mode,
                'attribute' => $attribute,
                'coefficient' => $coefficient
            );
        }

        if (empty($discounts)) {
            return;
        }

        usort(
            $discounts, function($a, $b)
            {
            return $a["qty"] > $b["qty"];
            }
        );

        $connWrite->insertMultiple(
            Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('M2ePro/Amazon_Template_SellingFormat_BusinessDiscount'),
            $discounts
        );

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

        $this->_redirect('*/adminhtml_amazon_template/index');
    }

    //########################################
}
