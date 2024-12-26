<?php


class Ess_M2ePro_Adminhtml_Amazon_ProductTypesController
    extends Ess_M2ePro_Controller_Adminhtml_Amazon_MainController
{
    /**
     * @return $this
     */
    private function initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Configuration'))
            ->_title(Mage::helper('M2ePro')->__('Product Type'));

        $this->getLayout()
            ->getBlock('head')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Amazon/ProductType.css')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addJs('M2ePro/Amazon/Marketplace/Sync.js')
            ->addJs('M2ePro/Amazon/ProductType.js')
            ->addJs('M2ePro/Amazon/ProductType/Content.js')
            ->addJs('M2ePro/Amazon/ProductType/Search.js')
            ->addJs('M2ePro/Amazon/ProductType/Finder.js')
            ->addJs('M2ePro/Amazon/ProductType/Tabs.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "amazon-integration");

        return $this;
    }

    //----------------------------------

    public function indexAction()
    {
        $this->initAction()
            ->_addContent(
                $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_amazon_configuration',
                    '',
                    array(
                        'active_tab' => Ess_M2ePro_Block_Adminhtml_Amazon_Configuration_Tabs::TAB_ID_PRODUCT_TYPE
                    )
                )
            )->renderLayout();
    }

    public function gridAction()
    {
        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_productType_grid')
            ->toHtml();

        $this->getResponse()->setBody($response);
    }

    public function newAction()
    {
        $this->_forward('edit');
    }


    //----------------------------------

    public function editAction()
    {
        /** @var Ess_M2ePro_Model_Amazon_Template_ProductType_Repository $productTypeRepository */
        $productTypeRepository = Mage::getModel('M2ePro/Amazon_Template_ProductType_Repository');
        /** @var Ess_M2ePro_Model_Amazon_Template_ProductType $productTypeModel */
        $productTypeModel = Mage::getModel('M2ePro/Amazon_Template_ProductType');

        $productTypeId = $this->getRequest()->getParam('id');
        $productType = !empty($productTypeId)
            ? $productTypeRepository->get((int)$productTypeId)
            : $productTypeModel;

        $this->setPageHelpLink(null, 'amazon-product-types');

        $this->initAction();

        $this->_addLeft(
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_amazon_productType_edit_tabs',
                '',
                array('productType' => $productType)
            )
        );

        $this->_addContent(
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_amazon_productType_edit',
                '',
                array('productType' => $productType)
            )
        );

        $this->_addContent(
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_amazon_productType_edit_fieldTemplates',
                ''
            )
        );

        $this->renderLayout();
    }

    //----------------------------------

    public function searchProductTypePopupAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');

        if (!$marketplaceId) {
            $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'result' => false,
                        'message' => 'You should provide correct marketplace_id.',
                    )
                )
            );
            return;
        }

        $marketplaceRepository = Mage::getModel('M2ePro/Amazon_Marketplace_Repository');
        $productTypes = $this->getAvailableProductTypes($marketplaceRepository->get((int)$marketplaceId));
        /** @var Ess_M2ePro_Block_Adminhtml_Amazon_ProductType_Edit_SearchPopup $block */
        $block = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_productType_edit_searchPopup',
            ''
        );
        $block->setProductTypes($productTypes);

        $this->_addAjaxContent($block->toHtml());
    }

    /**
     * @param int $marketplaceId
     * @return array
     */
    private function getAvailableProductTypes(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        $dictionaryMarketplaceRepository = Mage::getModel('M2ePro/Amazon_Dictionary_Marketplace_Repository');
        $marketplaceDictionaryItem = $dictionaryMarketplaceRepository->findByMarketplace($marketplace);
        $dictionaryMarketplaceService = Mage::getModel('M2ePro/Amazon_Dictionary_MarketplaceService');
        if ($marketplaceDictionaryItem === null) {
            $marketplaceDictionaryItem = $dictionaryMarketplaceService->update($marketplace);
        }

        $productTypes = $marketplaceDictionaryItem->getProductTypes();
        if (empty($productTypes)) {
            return array();
        }

        $result = array();
        $alreadyUsedProductTypes = array();
        $templateProductTypeRepository = Mage::getModel('M2ePro/Amazon_Template_ProductType_Repository');
        foreach ($templateProductTypeRepository->findByMarketplaceId((int)$marketplace->getId()) as $template) {
            $alreadyUsedProductTypes[$template->getDictionary()->getNick()] = (int)$template->getId();
        }

        foreach ($productTypes as $productType) {
            $productTypeData = array(
                'nick' => $productType['nick'],
                'title' => $productType['title'],
            );

            if (isset($alreadyUsedProductTypes[$productType['nick']])) {
                $productTypeData['exist_product_type_id'] = $alreadyUsedProductTypes[$productType['nick']];
            }
            $result[] = $productTypeData;
        }

        return $result;
    }

    //----------------------------------

    public function getCategoriesAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $criteriaRequest = $this->getRequest()->getParam('criteria');

        if ($marketplaceId === null) {
            $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'result' => false,
                        'message' => 'Invalid input',
                    )
                )
            );

            return;
        }

        $criteria = $criteriaRequest !== null ? Zend_Json::decode($criteriaRequest) : array();
        $categoryFinder = Mage::getModel('M2ePro/Amazon_ProductType_CategoryFinder');
        $categories = $categoryFinder->find((int)$marketplaceId, $criteria);

        $jsonItems = array();
        foreach ($categories as $category) {
            $item = array(
                'name' => $category->getName(),
                'path' => array_merge($criteria, array($category->getName())),
                'isLeaf' => $category->getIsLeaf(),
                'productTypes' => array(),
            );

            foreach ($category->getProductTypes() as $productType) {
                $item['productTypes'][] = array(
                    'title' => $productType->getTitle(),
                    'nick' => $productType->getNick(),
                    'templateId' => $productType->getTemplateId(),
                    'path' => array_merge($item['path'], array($productType->getTitle())),
                );
            }

            $jsonItems[] = $item;
        }

        $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array('categories' => $jsonItems)
            )
        );
    }


    //----------------------------------

    public function getProductTypeInfoAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $isNewProductType = (bool)$this->getRequest()->getParam('is_new_product_type');

        if ($marketplaceId === null) {
            $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'result' => false,
                        'message' => 'You should provide correct marketplace_id.',
                    )
                )
            );

            return;
        }

        $productTypeNick = $this->getRequest()->getParam('product_type');
        if ($productTypeNick === null) {
            $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'result' => false,
                        'message' => 'You should provide correct product_type.',
                    )
                )
            );
            return;
        }

        /** @var Ess_M2ePro_Model_Amazon_Marketplace_Repository $marketplaceRepository */
        $marketplaceRepository = Mage::getModel('M2ePro/Amazon_Marketplace_Repository');
        $marketplace = $marketplaceRepository->get((int)$marketplaceId);

        /** @var Ess_M2ePro_Model_Amazon_Dictionary_ProductTypeService $dictionaryProductTypeService */
        $dictionaryProductTypeService = Mage::getModel('M2ePro/Amazon_Dictionary_ProductTypeService');
        $productType = $dictionaryProductTypeService->retrieve($productTypeNick, $marketplace);

        /** @var Ess_M2ePro_Model_Amazon_Template_ProductType_Repository $templateProductTypeRepository */
        $templateProductTypeRepository = Mage::getModel('M2ePro/Amazon_Template_ProductType_Repository');
        $productTypeTemplates = $templateProductTypeRepository->findByDictionary($productType);

        $template = null;
        if (isset($productTypeTemplates[0])) {
            $template = $productTypeTemplates[0];
        }

        $specificDefaultSettings = array();
        if ($isNewProductType) {
            /** @var Ess_M2ePro_Model_Amazon_ProductType_AttributeMappingService $attributeMappingService */
            $attributeMappingService = Mage::getModel('M2ePro/Amazon_ProductType_AttributeMappingService');
            $specificDefaultSettings = $attributeMappingService->getSuggestedAttributes();
        }

        /** @var Ess_M2ePro_Helper_Component_Amazon_ProductType $productTypeHelper */
        $productTypeHelper = Mage::helper('M2ePro/Component_Amazon_ProductType');
        $contentData = array(
            'scheme' => $productType->getScheme(),
            'settings' => $template !== null ? $template->getSelfSetting() : array(),
            'groups' => $productType->getAttributesGroups(),
            'timezone_shift' => $this->getTimezoneShift(),
            'specifics_default_settings' => $specificDefaultSettings,
            'main_image_specifics' => $productTypeHelper->getMainImageSpecifics(),
            'other_images_specifics' => $productTypeHelper->getOtherImagesSpecifics(),
            'recommended_browse_node_link' => $productTypeHelper->getRecommendedBrowseNodesLink($marketplaceId),
        );

        $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                    'result' => true,
                    'data' => $contentData,
                )
            )
        );
    }

    /**
     * @return int
     * @throws DateMalformedStringException
     */
    private function getTimezoneShift()
    {
        $dateLocal = new DateTime(Mage::helper('M2ePro')->gmtDateToTimezone('2024-01-01'));
        $timestampUTC = Mage::helper('M2ePro')->timezoneDateToGmt($dateLocal->format('Y-m-d H:i:s'), true);

        return $dateLocal->getTimestamp() - $timestampUTC;
    }

    //----------------------------------

    public function saveAction()
    {
        $post = $this->getRequest()->getPost();
        if (empty($post)) {
            if ($this->getRequest()->isXmlHttpRequest()) {
                return $this->getResponse()->setBody(
                    Mage::helper('M2ePro')->jsonEncode(
                        array(
                            'status' => false,
                            'message' => 'Incorrect input',
                        )
                    )
                );
            }

            $this->_forward('index');

            return;
        }

        $id = !empty($post['general']['id']) ? $post['general']['id'] : null;

        if (!$id) {
            $temp = array();
            $keys = array('marketplace_id', 'nick');
            foreach ($keys as $key) {
                if (empty($post['general'][$key])) {
                    $message = "Missing required field for Product Type Settings: $key";
                    if ($this->getRequest()->isXmlHttpRequest()) {

                        return $this->getResponse()->setBody(
                            Mage::helper('M2ePro')->jsonEncode(
                                array(
                                    'status' => false,
                                    'message' => $message,
                                )
                            )
                        );
                    }

                    $this->_getSession()->addError($message);

                    return $this->_redirect('*/adminhtml_amazon_productTypes/edit');
                }

                $temp[$key] = $post['general'][$key];
            }

            if ($this->isTryingOverrideExistingSettings((int)$temp['marketplace_id'], (string)$temp['nick'])) {
                $message = $this->__(
                    'Product Type Settings were not saved: duplication of Product Type Settings'
                    . ' for marketplace is not allowed.'
                );

                if ($this->getRequest()->isXmlHttpRequest()) {
                    return $this->getResponse()->setBody(
                        Mage::helper('M2ePro')->jsonEncode(
                            array(
                                'status' => false,
                                'message' => $message,
                            )
                        )
                    );
                }

                $this->_getSession()->addError($message);

                return $this->_redirect('*/adminhtml_amazon_productType/index');
            }
        }

        /** @var Ess_M2ePro_Model_Amazon_Template_ProductType_Builder $builder */
        $builder = Mage::getModel('M2ePro/Amazon_Template_ProductType_Builder');
        /** @var Ess_M2ePro_Model_Amazon_Template_ProductType $productType */
        $productType = Mage::getModel('M2ePro/Amazon_Template_ProductType');

        $templateProductTypeRepository =  Mage::getModel('M2ePro/Amazon_Template_ProductType_Repository');
        if ($id) {
            $productType = $templateProductTypeRepository->get((int)$id);
        }

        $oldData = array();
        if ($productType->getId()) {
            $oldData = $this->makeSnapshot($productType);
        }

        $builder->build($productType, $post);
        /** @var Ess_M2ePro_Model_Amazon_Template_ProductType $productType */
        $productType = $builder->getModel();
        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Product Type Settings were saved'));

        $newData = $this->makeSnapshot($productType);

        /** @var Ess_M2ePro_Model_Amazon_Template_ProductType_Diff $diff */
        $diff = Mage::getModel('M2ePro/Amazon_Template_ProductType_Diff');
        $diff->setNewSnapshot($newData);
        $diff->setOldSnapshot($oldData);

        /** @var Ess_M2ePro_Model_Amazon_Template_ProductType_AffectedListingsProducts $affectedListingsProducts */
        $affectedListingsProducts = Mage::getModel(
            'Ess_M2ePro_Model_Amazon_Template_ProductType_AffectedListingsProducts'
        );
        $affectedListingsProducts->setModel($productType);

        /** @var Ess_M2ePro_Model_Amazon_Template_ProductType_ChangeProcessor $changeProcessor */
        $changeProcessor = Mage::getModel('Ess_M2ePro_Model_Amazon_Template_ProductType_ChangeProcessor');
        $changeProcessor->process(
            $diff,
            $affectedListingsProducts->getData(array('id', 'status'))
        );

        /** @var Ess_M2ePro_Model_Amazon_ProductType_AttributeMappingService $attributeMappingService */
        $attributeMappingService = Mage::getModel('M2ePro/Amazon_ProductType_AttributeMappingService');
        $attributeMappingService->create($productType);

        $backUrl = Mage::helper('M2ePro')->getBackUrl(
            '*/adminhtml_amazon_productTypes/index',
            array(),
            array('edit' => array('id' => $productType->getId()))
        );

        $editUrl = $this->getUrl(
            '*/*/edit',
            array('id' => $productType->getId())
        );

        if ($this->getRequest()->isXmlHttpRequest()) {
            $responseContent =  array(
                'status' => true,
                'product_type_id' => $productType->getId(),
                'back_url' => $backUrl,
                'edit_url' => $editUrl,
            );

            if ($attributeMappingService->hasChangedMappings($productType)) {
                $responseContent['has_changed_mappings_product_type_id'] = $productType->getId();
            }

            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode($responseContent)
            );
        }

        return $this->_redirect($backUrl);
    }

    /**
     * Product type settings must be unique for pair (marketplace_id, nick).
     * This code prevents attempt to create duplicate when user tries to create new product type settings.
     * Situation like this possible when one user starts to create product type, another user creates the same one,
     * and first user saves settings for same (marketplace_id, nick).
     */
    private function isTryingOverrideExistingSettings(
        $marketplaceId,
        $nick
    ) {
        /** @var Ess_M2ePro_Model_Amazon_Template_ProductType_Repository $productTypeRepository */
        $productTypeRepository = Mage::getModel('M2ePro/Amazon_Template_ProductType_Repository');
        $productType = $productTypeRepository->findByMarketplaceIdAndNick($marketplaceId, $nick);

        return $productType !== null;
    }

    /**
     * @return array
     */
    private function makeSnapshot(Ess_M2ePro_Model_Amazon_Template_ProductType $productType)
    {
        /** @var Ess_M2ePro_Model_Amazon_Template_ProductType_SnapshotBuilder $snapshotBuilder */
        $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Template_ProductType_SnapshotBuilder');
        $snapshotBuilder->setModel($productType);

        return $snapshotBuilder->getSnapshot();
    }

    //----------------------------------

    public function updateAttributeMappingAction()
    {
        $productTypeId = $this->getRequest()
                              ->getParam('product_type_id');
        $productType = null;
        if ($productTypeId !== null) {
            /** @var Ess_M2ePro_Model_Amazon_Template_ProductType_Repository $repository */
            $repository = Mage::getModel('M2ePro/Amazon_Template_ProductType_Repository');
            $productType = $repository->find((int)$productTypeId);
        }

        if ($productType === null) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'status' => false,
                        'message' => $this->__('Incorrect Product Type id'),
                    )
                )
            );
        }

        /** @var Ess_M2ePro_Model_Amazon_ProductType_AttributeMappingService $attributeMappingService */
        $attributeMappingService = Mage::getModel('M2ePro/Amazon_ProductType_AttributeMappingService');
        $attributeMappingService->update($productType);

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array('status' => true)
            )
        );
    }

    //----------------------------------

    public function deleteAction()
    {
        $ids = $this->getRequestIds();
        if (count($ids) == 0) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('Please select Item(s) to remove.')
            );
            $this->_redirect('*/*/index');

            return;
        }

        /** @var Ess_M2ePro_Model_Amazon_Template_ProductType_Repository $productTypeRepository */
        $productTypeRepository = Mage::getModel('M2ePro/Amazon_Template_ProductType_Repository');

        $deleted = $locked = 0;
        foreach ($ids as $id) {
            $template = $productTypeRepository->get((int)$id);
            if (
                $template->isLocked()
                || $productTypeRepository->isUsed($template)
            ) {
                $locked++;
            } else {
                $productTypeRepository->remove($template);
                $deleted++;
            }
        }

        if ($deleted !== 0) {
            $this->_getSession()->addSuccess(
                Mage::helper('M2ePro')->__(
                    '%deleted% record(s) were deleted.',
                    array('deleted' => $deleted)
                )
            );
        }

        if ($locked !== 0) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__(
                    'Unable to delete Product Type: It is currently in use in one or more Listings or Auto Rules.'
                    . ' Please ensure the Product Type is not associated with any active records.'
                )
            );
        }

        $this->_redirect('*/*/index');
    }

    //----------------------------------

    public function isUniqueTitleAction()
    {
        $title = $this->getRequest()->getParam('title');
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $productTypeId = $this->getRequest()->getParam('product_type_id');

        if (empty($title) || empty($marketplaceId)) {
            throw new LogicException('You should provide correct parameters.');
        }

        /** @var Ess_M2ePro_Model_Amazon_Template_ProductType_Repository $productTypeRepository */
        $productTypeRepository = Mage::getModel('M2ePro/Amazon_Template_ProductType_Repository');
        $productType = $productTypeRepository->findByTitleMarketplace(
            $title,
            (int)$marketplaceId,
            !empty($productTypeId) ? (int)$productTypeId : null
        );

        $isIsUniqueTitle = $productType === null;

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array('result' => $isIsUniqueTitle)
            )
        );
    }

}