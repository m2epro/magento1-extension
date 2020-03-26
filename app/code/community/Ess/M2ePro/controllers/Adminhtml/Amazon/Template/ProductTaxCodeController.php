<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Amazon_Template_ProductTaxCodeController
    extends Ess_M2ePro_Controller_Adminhtml_Amazon_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Policies'))
             ->_title(Mage::helper('M2ePro')->__('Product Tax Code Policies'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Template/EditHandler.js')
            ->addJs('M2ePro/Amazon/Template/EditHandler.js')
            ->addJs('M2ePro/Amazon/Template/ProductTaxCodeHandler.js');

        $this->_initPopUp();

        // todo must be added when develop branch becomes "public"
//        $this->pageHelpLink = Mage::helper('M2ePro/Module_Support')
//                                    ->getDocumentationUrl(null, null, 'TODO LINK');

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
        $model = Mage::getModel('M2ePro/Amazon_Template_ProductTaxCode')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Policy does not exist'));
            return $this->_redirect('*/adminhtml_amazon_template/index');
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_template_productTaxCode_edit')
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

        $keys = array(
            'title',

            'product_tax_code_mode',
            'product_tax_code_value',
            'product_tax_code_attribute',
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $model = Mage::getModel('M2ePro/Amazon_Template_ProductTaxCode')->load($id);

        $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Template_ProductTaxCode_SnapshotBuilder');
        $snapshotBuilder->setModel($model);
        $oldData = $snapshotBuilder->getSnapshot();

        $model->addData($data)->save();

        $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Template_ProductTaxCode_SnapshotBuilder');
        $snapshotBuilder->setModel($model);
        $newData = $snapshotBuilder->getSnapshot();

        $diff = Mage::getModel('M2ePro/Amazon_Template_ProductTaxCode_Diff');
        $diff->setNewSnapshot($newData);
        $diff->setOldSnapshot($oldData);

        $affectedListingsProducts = Mage::getModel('M2ePro/Amazon_Template_ProductTaxCode_AffectedListingsProducts');
        $affectedListingsProducts->setModel($model);

        $changeProcessor = Mage::getModel('M2ePro/Amazon_Template_ProductTaxCode_ChangeProcessor');
        $changeProcessor->process(
            $diff, $affectedListingsProducts->getData(array('id', 'status'), array('only_physical_units' => true))
        );

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Policy was successfully saved'));
        return $this->_redirectUrl(
            Mage::helper('M2ePro')->getBackUrl(
                '*/adminhtml_amazon_template/index', array(),
                array(
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
            $template = Mage::getModel('M2ePro/Amazon_Template_ProductTaxCode')->load($id);
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

        $tempString = Mage::helper('M2ePro')->__('%amount% record(s) were successfully deleted.', $deleted);
        $deleted && $this->_getSession()->addSuccess($tempString);

        $tempString  = Mage::helper('M2ePro')->__('%amount% record(s) are used in Listing(s).', $locked) . ' ';
        $tempString .= Mage::helper('M2ePro')->__('Policy must not be in use to be deleted.');
        $locked && $this->_getSession()->addError($tempString);

        return $this->_redirect('*/adminhtml_amazon_template/index');
    }

    //########################################

    public function viewPopupAction()
    {
        $productsIds  = $this->getRequest()->getParam('products_ids');

        if (empty($productsIds)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $messages = array();
        $productsIdsLocked = $this->filterLockedProducts($productsIds);

        if (count($productsIdsLocked) < count($productsIds)) {
            $messages[] = array(
                'type' => 'warning',
                'text' => '<p>' . Mage::helper('M2ePro')->__(
                    'The Product Tax Code Policy was not assigned because the Products have In Action Status.'
                ). '</p>'
            );
        }

        if (empty($productsIdsLocked)) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                    'messages' => $messages
                    )
                )
            );
        }

        $mainBlock = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_listing_template_productTaxCode');

        if (!empty($messages)) {
            $mainBlock->setMessages($messages);
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'data' => $mainBlock->toHtml(),
                'messages' => $messages,
                'products_ids' => implode(',', $productsIdsLocked)
                )
            )
        );
    }

    public function viewGridAction()
    {
        $productsIds = $this->getRequest()->getParam('products_ids');

        if (empty($productsIds)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $grid = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_listing_template_productTaxCode_grid');
        $grid->setProductsIds($productsIds);

        return $this->getResponse()->setBody($grid->toHtml());
    }

    // ---------------------------------------

    public function assignAction()
    {
        $productsIds  = $this->getRequest()->getParam('products_ids');
        $templateId   = $this->getRequest()->getParam('template_id');

        if (empty($productsIds) || empty($templateId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $messages = array();
        $productsIdsLocked = $this->filterLockedProducts($productsIds);

        if (count($productsIdsLocked) < count($productsIds)) {
            $messages[] = array(
                'type' => 'warning',
                'text' => '<p>' . Mage::helper('M2ePro')->__(
                    'Product Tax Code Policy cannot be assigned to some Products
                         because the Products are in Action'
                ). '</p>'
            );
        }

        if (!empty($productsIdsLocked)) {
            $messages[] = array(
                'type' => 'success',
                'text' => Mage::helper('M2ePro')->__('Product Tax Code Policy was successfully assigned.')
            );

            $this->setProductTaxCodeTemplateForProducts($productsIdsLocked, $templateId);
            $this->runProcessorForParents($productsIdsLocked);
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'messages' => $messages
                )
            )
        );
    }

    public function unassignAction()
    {
        $productsIds  = $this->getRequest()->getParam('products_ids');

        if (empty($productsIds)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $messages = array();
        $productsIdsLocked = $this->filterLockedProducts($productsIds);

        if (count($productsIdsLocked) < count($productsIds)) {
            $messages[] = array(
                'type' => 'warning',
                'text' => '<p>' . Mage::helper('M2ePro')->__(
                    'Product Tax Code Policy cannot be unassigned from some Products
                         because the Products are in Action'
                ). '</p>'
            );
        }

        if (!empty($productsIdsLocked)) {
            $messages[] = array(
                'type' => 'success',
                'text' => Mage::helper('M2ePro')->__('Product Tax Code Policy was successfully unassigned.')
            );

            $this->setProductTaxCodeTemplateForProducts($productsIdsLocked, null);
            $this->runProcessorForParents($productsIdsLocked);
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'messages' => $messages
                )
            )
        );
    }

    //########################################

    public function filterLockedProducts($productsIdsParam)
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_processing_lock');

        $productsIds = array();
        $productsIdsParam = array_chunk($productsIdsParam, 1000);
        foreach ($productsIdsParam as $productsIdsParamChunk) {
            $select = $connRead->select();
            $select->from(array('lo' => $table), array('object_id'))
                ->where('model_name = "M2ePro/Listing_Product"')
                ->where('object_id IN (?)', $productsIdsParamChunk)
                ->where('tag IS NOT NULL');

            $lockedProducts = Mage::getResourceModel('core/config')->getReadConnection()->fetchCol($select);

            foreach ($lockedProducts as $id) {
                $key = array_search($id, $productsIdsParamChunk);
                if ($key !== false) {
                    unset($productsIdsParamChunk[$key]);
                }
            }

            $productsIds = array_merge($productsIds, $productsIdsParamChunk);
        }

        return $productsIds;
    }

    protected function setProductTaxCodeTemplateForProducts($productsIds, $templateId)
    {
        if (empty($productsIds)) {
            return;
        }

        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->addFieldToFilter('id', array('in' => $productsIds));
        // ---------------------------------------

        if ($collection->getSize() == 0) {
            return;
        }

        $transaction = Mage::getModel('core/resource_transaction');
        $oldTemplateIds = array();

        try {
            foreach ($collection->getItems() as $listingProduct) {
                /**@var Ess_M2ePro_Model_Listing_Product $listingProduct */

                $oldTemplateIds[$listingProduct->getId()] = $listingProduct->getData('template_product_tax_code_id');

                $listingProduct->setData('template_product_tax_code_id', $templateId);
                $transaction->addObject($listingProduct);
            }

            $transaction->save();
        } catch (Exception $e) {
            $oldTemplateIds = false;
            $transaction->rollback();
        }

        if (!$oldTemplateIds) {
            return;
        }

        $newTemplate = Mage::getModel('M2ePro/Amazon_Template_ProductTaxCode')->load($templateId);

        if ($newTemplate->getId()) {
            $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Template_ProductTaxCode_SnapshotBuilder');
            $snapshotBuilder->setModel($newTemplate);
            $newSnapshot = $snapshotBuilder->getSnapshot();
        } else {
            $newSnapshot = array();
        }

        foreach ($collection->getItems() as $listingProduct) {
            /**@var Ess_M2ePro_Model_Listing_Product $listingProduct */

            $oldTemplate = Mage::getModel('M2ePro/Amazon_Template_ProductTaxCode')->load(
                $oldTemplateIds[$listingProduct->getId()]
            );

            if ($oldTemplate->getId()) {
                $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Template_ProductTaxCode_SnapshotBuilder');
                $snapshotBuilder->setModel($oldTemplate);
                $oldSnapshot = $snapshotBuilder->getSnapshot();
            } else {
                $oldSnapshot = array();
            }

            if (empty($newSnapshot) && empty($oldSnapshot)) {
                continue;
            }

            $diff = Mage::getModel('M2ePro/Amazon_Template_ProductTaxCode_Diff');
            $diff->setOldSnapshot($oldSnapshot);
            $diff->setNewSnapshot($newSnapshot);

            $changeProcessor = Mage::getModel('M2ePro/Amazon_Template_ProductTaxCode_ChangeProcessor');
            $changeProcessor->process(
                $diff, array(array('id' => $listingProduct->getId(), 'status' => $listingProduct->getStatus()))
            );
        }
    }

    protected function runProcessorForParents($productsIds)
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableAmazonListingProduct = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_amazon_listing_product');

        $select = $connRead->select();
        $select->from(array('alp' => $tableAmazonListingProduct), array('listing_product_id'))
            ->where('listing_product_id IN (?)', $productsIds)
            ->where('is_variation_parent = ?', 1);

        $productsIds = Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($select);

        foreach ($productsIds as $productId) {
            $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);
            $listingProduct->getChildObject()->getVariationManager()->getTypeModel()->getProcessor()->process();
        }
    }

    //########################################
}
