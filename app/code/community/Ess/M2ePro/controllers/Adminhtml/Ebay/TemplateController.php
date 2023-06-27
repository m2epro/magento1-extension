<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_TemplateController extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Policies'));

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addJs('mage/adminhtml/rules.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addJs('M2ePro/Plugin/ActionColumn.js')
            ->addCss('M2ePro/css/Plugin/DropDown.css')
            ->addJs('M2ePro/TemplateManager.js')
            ->addJs('M2ePro/Attribute.js')
            ->addJs('M2ePro/Ebay/Listing/Template/Switcher.js')
            ->addJs('M2ePro/Template/Edit.js')
            ->addJs('M2ePro/Ebay/Template/Edit.js')
            ->addJs('M2ePro/Ebay/Template/Return.js')
            ->addJs('M2ePro/Ebay/Template/Shipping.js')
            ->addJs('M2ePro/Ebay/Template/Shipping/ExcludedLocations.js')
            ->addJs('M2ePro/Ebay/Template/SellingFormat.js')
            ->addJs('M2ePro/Ebay/Template/Description.js')
            ->addJs('M2ePro/Ebay/Template/Synchronization.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "configuration");

        if (Mage::helper('M2ePro/Magento')->isTinyMceAvailable()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK . '/configuration'
        );
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_ebay_configuration',
                    '',
                    array('active_tab' => Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Tabs::TAB_ID_TEMPLATE)
                )
            )->renderLayout();
    }

    public function templateGridAction()
    {
        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_template_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //########################################

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        // ---------------------------------------
        $id = $this->getRequest()->getParam('id');
        $nick = $this->getRequest()->getParam('nick');
        // ---------------------------------------

        // ---------------------------------------
        $manager = Mage::getSingleton('M2ePro/Ebay_Template_Manager')->setTemplate($nick);
        $template = $manager
            ->getTemplateModel()
            ->getCollection()
            ->addFieldToFilter('id', $id)
            ->addFieldToFilter('is_custom_template', 0)
            ->getFirstItem();
        // ---------------------------------------

        // ---------------------------------------
        if (!$template->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Policy does not exist.'));

            return $this->_redirect('*/adminhtml_ebay_template/index');
        }

        // ---------------------------------------

        // ---------------------------------------
        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Template_Switcher_DataLoader $dataLoader */
        $dataLoader = Mage::getBlockSingleton('M2ePro/adminhtml_ebay_listing_template_switcher_dataLoader');
        $dataLoader->load($template);
        // ---------------------------------------

        $data = array(
            'template_nick' => $nick
        );
        $content = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_template_edit', '', $data);

        $this->_initAction();

        switch ($nick) {
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN_POLICY:
                $this->setPageHelpLink(null, null, "set-up-return-policy");
                break;

            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT:
                $this->setPageHelpLink(null, null, "set-up-payment-policy");
                break;

            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING:
                $this->setPageHelpLink(null, null, "set-up-shipping-policy#6e8b3db9007740e1a87f1d2a26209a10");
                break;

            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION:
                $this->setPageHelpLink(null, null, "set-up-description-policy");
                break;

            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT:
                $this->setPageHelpLink(null, null, "set-up-selling-policy");
                break;

            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION:
                $this->setPageHelpLink(null, null, "set-up-synchronization-policy");
                break;
        }

        $this->_addContent($content);
        $this->renderLayout();
    }

    //########################################

    public function saveAction()
    {
        $templates = array();
        $templateNicks = Mage::getSingleton('M2ePro/Ebay_Template_Manager')->getAllTemplates();

        // ---------------------------------------
        foreach ($templateNicks as $nick) {
            if ($this->isSaveAllowed($nick)) {
                $template = $this->saveTemplate($nick);

                if ($template) {
                    $templates[] = array(
                        'nick' => $nick,
                        'id' => (int)$template->getId(),
                        'title' => Mage::helper('M2ePro')->escapeJs(
                            Mage::helper('M2ePro')->escapeHtml($template->getTitle())
                        )
                    );
                }
            }
        }

        // ---------------------------------------

        // ---------------------------------------
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->loadLayout();
            $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($templates));

            return;
        }

        // ---------------------------------------

        if (empty($templates)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Policy was not saved.'));
            $this->_redirect('*/*/index');

            return;
        }

        if ((bool)$this->getRequest()->getParam('close_on_save', false)) {
            return $this->getResponse()->setBody("<script>window.close();</script>");
        }

        $template = array_shift($templates);

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Policy was saved.'));

        $extendedRoutersParams = array('edit' => array('id' => $template['id'], 'nick' => $template['nick']));

        if ((bool)$this->getRequest()->getParam('wizard', false)) {
            $extendedRoutersParams['edit']['wizard'] = true;
        } else {
            $extendedRoutersParams['edit']['back'] = true;
        }

        $this->_redirectUrl(
            Mage::helper('M2ePro')->getBackUrl(
                'list',
                array(),
                $extendedRoutersParams
            )
        );
    }

    //########################################

    protected function isSaveAllowed($templateNick)
    {
        if (!$this->getRequest()->isPost()) {
            return false;
        }

        $requestedTemplateNick = $this->getRequest()->getPost('nick');

        if ($requestedTemplateNick === null) {
            return true;
        }

        if ($requestedTemplateNick == $templateNick) {
            return true;
        }

        return false;
    }

    //########################################

    protected function saveTemplate($nick)
    {
        $data = $this->getRequest()->getPost($nick);

        if ($data === null) {
            return null;
        }

        /** @var Ess_M2ePro_Model_Ebay_Template_Manager $templateManager */
        $templateManager = Mage::getSingleton('M2ePro/Ebay_Template_Manager')->setTemplate($nick);
        $templateModel = $templateManager->getTemplateModel();

        if (empty($data['id'])) {
            $oldData = array();
        } else {
            $templateModel->load($data['id']);

            /** @var Ess_M2ePro_Model_ActiveRecord_SnapshotBuilder $snapshotBuilder */
            if ($templateManager->isHorizontalTemplate()) {
                $snapshotBuilder = Mage::getModel(
                    'M2ePro/Ebay_' . $templateManager->getTemplateModelName() . '_SnapshotBuilder'
                );
            } else {
                $snapshotBuilder = Mage::getModel(
                    'M2ePro/' . $templateManager->getTemplateModelName() . '_SnapshotBuilder'
                );
            }

            $snapshotBuilder->setModel(
                $templateManager->isHorizontalTemplate() ? $templateModel->getChildObject() : $templateModel
            );

            $oldData = $snapshotBuilder->getSnapshot();
        }

        $template = $templateManager->getTemplateBuilder()->build($templateModel, $data);

        /** @var Ess_M2ePro_Model_ActiveRecord_SnapshotBuilder $snapshotBuilder */
        if ($templateManager->isHorizontalTemplate()) {
            $snapshotBuilder = Mage::getModel(
                'M2ePro/Ebay_' . $templateManager->getTemplateModelName() . '_SnapshotBuilder'
            );
        } else {
            $snapshotBuilder = Mage::getModel(
                'M2ePro/' . $templateManager->getTemplateModelName() . '_SnapshotBuilder'
            );
        }

        $snapshotBuilder->setModel($template);

        $newData = $snapshotBuilder->getSnapshot();

        /** @var Ess_M2ePro_Model_ActiveRecord_Diff $diff */
        if ($templateManager->isHorizontalTemplate()) {
            $diff = Mage::getModel('M2ePro/Ebay_' . $templateManager->getTemplateModelName() . '_Diff');
        } else {
            $diff = Mage::getModel('M2ePro/' . $templateManager->getTemplateModelName() . '_Diff');
        }

        $diff->setNewSnapshot($newData);
        $diff->setOldSnapshot($oldData);

        /** @var Ess_M2ePro_Model_Template_AffectedListingsProductsAbstract $affectedListingsProducts */
        if ($templateManager->isHorizontalTemplate()) {
            $affectedListingsProducts = Mage::getModel(
                'M2ePro/Ebay_' . $templateManager->getTemplateModelName() . '_AffectedListingsProducts'
            );
        } else {
            $affectedListingsProducts = Mage::getModel(
                'M2ePro/' . $templateManager->getTemplateModelName() . '_AffectedListingsProducts'
            );
        }

        $affectedListingsProducts->setModel($template);

        /** @var Ess_M2ePro_Model_Template_ChangeProcessorAbstract $changeProcessor */
        if ($templateManager->isHorizontalTemplate()) {
            $changeProcessor = Mage::getModel(
                'M2ePro/Ebay_' . $templateManager->getTemplateModelName() . '_ChangeProcessor'
            );
        } else {
            $changeProcessor = Mage::getModel(
                'M2ePro/' . $templateManager->getTemplateModelName() . '_ChangeProcessor'
            );
        }

        $changeProcessor->process($diff, $affectedListingsProducts->getData(array('id', 'status')));

        return $template;
    }

    //########################################

    public function deleteAction()
    {
        // ---------------------------------------
        $id = $this->getRequest()->getParam('id');
        $nick = $this->getRequest()->getParam('nick');
        // ---------------------------------------

        // ---------------------------------------
        $manager = Mage::getSingleton('M2ePro/Ebay_Template_Manager')->setTemplate($nick);
        $template = $manager
            ->getTemplateModel()
            ->getCollection()
            ->addFieldToFilter('id', $id)
            ->addFieldToFilter('is_custom_template', 0)
            ->getFirstItem();
        // ---------------------------------------

        if (!$template->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Policy does not exist.'));
            $this->_redirect('*/*/index');

            return;
        }

        if (!$template->isLocked()) {
            $template->deleteInstance();

            $this->_getSession()->addSuccess(
                Mage::helper('M2ePro')->__('Policy was deleted.')
            );
        } else {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('Policy cannot be deleted as it is used in Listing Settings.')
            );
        }

        $this->_redirect('*/*/index');
    }

    //########################################

    public function editListingProductAction()
    {
        $ids = $this->getRequestIds();
        $tab = $this->getRequest()->getParam('tab');

        if (empty($ids)) {
            $this->getResponse()->setBody('');

            return;
        }

        // ---------------------------------------
        $collection = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Listing_Product')
            ->addFieldToFilter('id', array('in' => $ids));
        // ---------------------------------------

        if ($collection->getSize() == 0) {
            $this->getResponse()->setBody('');

            return;
        }

        // ---------------------------------------
        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Template_Switcher_DataLoader $dataLoader */
        $dataLoader = Mage::getBlockSingleton('M2ePro/adminhtml_ebay_listing_template_switcher_dataLoader');
        $dataLoader->load($collection);
        // ---------------------------------------

        $data = array();
        if ($tab) {
            $data['allowed_tabs'] = array($tab);
        }

        $content = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_product_template_edit');
        $content->addData($data);

        $this->getResponse()->setBody($content->toHtml());
    }

    public function saveListingProductAction()
    {
        $ids = $this->getRequestIds();

        if (!$post = $this->getRequest()->getPost() || empty($ids)) {
            $this->getResponse()->setBody('');

            return;
        }

        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('id', array('in' => $ids));

        if ($collection->getSize() == 0) {
            $this->getResponse()->setBody('');

            return;
        }

        $data = $this->getPostedTemplatesData();

        $snapshots = array();
        $transaction = Mage::getModel('core/resource_transaction');

        try {
            foreach ($collection->getItems() as $listingProduct) {
                $snapshotBuilder = Mage::getModel('M2ePro/Ebay_Listing_Product_SnapshotBuilder');
                $snapshotBuilder->setModel($listingProduct);

                $snapshots[$listingProduct->getId()] = $snapshotBuilder->getSnapshot();

                $listingProduct->addData($data);
                $transaction->addObject($listingProduct);
            }

            $transaction->save();
        } catch (Exception $e) {
            $snapshots = false;
            $transaction->rollback();
        }

        $this->getResponse()->setBody('');

        if (!$snapshots) {
            return;
        }

        /** @var Ess_M2ePro_Model_Ebay_Template_AffectedListingsProducts_Processor $changesProcessor */
        $changesProcessor = Mage::getModel('M2ePro/Ebay_Template_AffectedListingsProducts_Processor');

        foreach ($collection->getItems() as $listingProduct) {
            $snapshotBuilder = Mage::getModel('M2ePro/Ebay_Listing_Product_SnapshotBuilder');
            $snapshotBuilder->setModel($listingProduct);

            $newSnapshot = $snapshotBuilder->getSnapshot();
            $oldSnapshot = $snapshots[$listingProduct->getId()];

            $changesProcessor->setListingProduct($listingProduct);
            $changesProcessor->processChanges($newSnapshot, $oldSnapshot);
        }
    }

    //########################################

    public function getTemplateHtmlAction()
    {
        $this->loadLayout();

        try {
            // ---------------------------------------
            /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Template_Switcher_DataLoader $dataLoader */
            $dataLoader = Mage::getBlockSingleton('M2ePro/adminhtml_ebay_listing_template_switcher_dataLoader');
            $dataLoader->load($this->getRequest());
            // ---------------------------------------

            // ---------------------------------------
            $templateNick = $this->getRequest()->getParam('nick');
            $templateDataForce = (bool)$this->getRequest()->getParam('data_force', false);

            $data = array(
                'template_nick' => $templateNick,
            );
            /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Template_Switcher $switcherBlock */
            $switcherBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_template_switcher');
            $switcherBlock->setData($data);
            // ---------------------------------------

            $this->getResponse()->setBody($switcherBlock->getFormDataBlockHtml($templateDataForce));
        } catch (Exception $e) {
            $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('error' => $e->getMessage())));
        }
    }

    //########################################

    public function isTitleUniqueAction()
    {
        $id = $this->getRequest()->getParam('id_value');
        $nick = $this->getRequest()->getParam('nick');
        $title = $this->getRequest()->getParam('title');

        if ($title == '') {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('unique' => false)));
        }

        $manager = Mage::getSingleton('M2ePro/Ebay_Template_Manager');
        $manager->setTemplate($nick);

        $collection = $manager
            ->getTemplateModel()
            ->getCollection()
            ->addFieldToFilter('is_custom_template', 0)
            ->addFieldToFilter('title', $title);

        if ($id) {
            $collection->addFieldToFilter('id', array('neq' => $id));
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array('unique' => !(bool)$collection->getSize())
            )
        );
    }

    //########################################

    protected function getPostedTemplatesData()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return array();
        }

        // ---------------------------------------
        $data = array();
        foreach (Mage::getSingleton('M2ePro/Ebay_Template_Manager')->getAllTemplates() as $nick) {
            /** @var Ess_M2ePro_Model_Ebay_Template_Manager $manager */
            $manager = Mage::getModel('M2ePro/Ebay_Template_Manager')
                ->setTemplate($nick);

            if (!isset($post["template_{$nick}"])) {
                continue;
            }

            $templateData = Mage::helper('M2ePro')->jsonDecode(base64_decode($post["template_{$nick}"]));

            if ($templateData['mode'] !== Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT) {
                $data[$manager->getTemplateIdColumnName()] = (int)$templateData['id'];
            }

            $data[$manager->getModeColumnName()] = $templateData['mode'];
        }

        // ---------------------------------------

        return $data;
    }

    //########################################
}
