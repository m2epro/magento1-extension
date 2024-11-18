<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Amazon_Template_ShippingController
    extends Ess_M2ePro_Controller_Adminhtml_Amazon_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Policies'))
             ->_title(Mage::helper('M2ePro')->__('Shipping Policies'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Template/Edit.js')
            ->addJs('M2ePro/Amazon/Template/Edit.js')
            ->addJs('M2ePro/Amazon/Template/Shipping.js');

        $this->_initPopUp();

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
        $model = Mage::getModel('M2ePro/Amazon_Template_Shipping')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Policy does not exist'));
            return $this->_redirect('*/adminhtml_amazon_template/index');
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_template_shipping_edit')
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

        $model = Mage::getModel('M2ePro/Amazon_Template_Shipping')->load($id);

        $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Template_Shipping_SnapshotBuilder');
        $snapshotBuilder->setModel($model);
        $oldData = $snapshotBuilder->getSnapshot();

        Mage::getModel('M2ePro/Amazon_Template_Shipping_Builder')->build($model, $post);

        $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Template_Shipping_SnapshotBuilder');
        $snapshotBuilder->setModel($model);
        $newData = $snapshotBuilder->getSnapshot();

        $diff = Mage::getModel('M2ePro/Amazon_Template_Shipping_Diff');
        $diff->setNewSnapshot($newData);
        $diff->setOldSnapshot($oldData);

        $affectedListingsProducts = Mage::getModel('M2ePro/Amazon_Template_Shipping_AffectedListingsProducts');
        $affectedListingsProducts->setModel($model);

        $changeProcessor = Mage::getModel('M2ePro/Amazon_Template_Shipping_ChangeProcessor');
        $changeProcessor->process(
            $diff, $affectedListingsProducts->getData(array('id', 'status'), array('only_physical_units' => true))
        );

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Policy was saved'));
        if (Mage::app()->getRequest()->isAjax()) {
            $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'status' => true,
                        'url' =>  Mage::helper('M2ePro')->getBackUrl(
                            '*/adminhtml_amazon_template/index', array(), array(
                                'edit' => array('id' => $model->getId())
                            )
                        )
                    )
                )
            );

            return;
        }

        $this->_redirectUrl(
            Mage::helper('M2ePro')->getBackUrl(
                '*/adminhtml_amazon_template/index', array(), array(
                'edit' => array('id' => $model->getId())
                )
            )
        );
    }

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
            $template = Mage::getModel('M2ePro/Amazon_Template_Shipping')->load($id);
            if (!$template->getId()) {
                continue;
            }

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

        return $this->_redirect('*/adminhtml_amazon_template/index');
    }

    public function refreshAction()
    {
        $accountId = $this->getRequest()->getParam('account_id');
        $account = $this->findAccount($accountId);
        if ($account === null) {
            throw new RuntimeException('Account not found by ID ' . $accountId);
        }

        /** @var Ess_M2ePro_Model_Amazon_Template_Shipping_Update $templateShippingUpdate */
        $templateShippingUpdate = Mage::getModel('M2ePro/Amazon_Template_Shipping_Update');
        $templateShippingUpdate->process($account);
    }

    public function getTemplatesAction()
    {
        $accountId = $this->getRequest()->getParam('account_id');
        $account = $this->findAccount($accountId);
        if ($account === null) {
            throw new RuntimeException('Account not found by ID ' . $accountId);
        }

        /** @var Ess_M2ePro_Model_Amazon_Dictionary_TemplateShipping_Repository $dictionaryRepository */
        $dictionaryRepository = Mage::getModel('M2ePro/Amazon_Dictionary_TemplateShipping_Repository');
        $dictionaries = $dictionaryRepository->retrieveByAccountId($accountId);

        $arrayDictionaries = array();
        foreach ($dictionaries as $dictionary) {
            $arrayDictionaries[] = $dictionary->toArray();
        }

        $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode($arrayDictionaries)
        );
    }

    public function getOptionsOfShippingTemplatesAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');

        /** @var $collection Ess_M2ePro_Model_Resource_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Amazon_Template_Shipping')->getCollection();
        if ($marketplaceId !== null) {
            $collection->addFieldToFilter('marketplace_id', $marketplaceId);
        }
        $collection->setOrder('title', Varien_Data_Collection::SORT_ORDER_ASC);
        /** @var Ess_M2ePro_Model_Amazon_Template_Shipping[] $templates */
        $templates = $collection->getItems();

        $result = array();
        foreach ($templates as $template) {
            $result[] = array(
                'id' => $template->getId(),
                'title' => $template->getTitle(),
            );
        }

        $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode($result)
        );
    }

    /**
     * @return Ess_M2ePro_Model_Account|null
     */
    private function findAccount($accountId)
    {
        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::getModel('M2ePro/Account')->load($accountId);

        if ($account->isObjectNew()) {
            return null;
        }

        return $account;
    }
}
