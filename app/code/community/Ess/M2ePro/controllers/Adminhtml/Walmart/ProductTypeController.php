<?php

class Ess_M2ePro_Adminhtml_Walmart_ProductTypeController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
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
            ->addCss('M2ePro/css/Walmart/ProductType.css')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addJs('M2ePro/SynchProgress.js')
            ->addJs('M2ePro/Synchronization.js')
            ->addJs('M2ePro/Walmart/Marketplace/WithProductType/Sync.js')
            ->addJs('M2ePro/Walmart/Marketplace/WithProductType/SyncProgress.js')
            ->addJs('M2ePro/Walmart/ProductType.js')
            ->addJs('M2ePro/Walmart/ProductType/Content.js')
            ->addJs('M2ePro/Walmart/ProductType/Search.js')
            ->addJs('M2ePro/Walmart/ProductType/Finder.js')
            ->addJs('M2ePro/Walmart/ProductType/Tabs.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "walmart-integration");

        return $this;
    }

    //----------------------------------

    public function indexAction()
    {
        $this->initAction()
            ->_addContent(
                $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_walmart_configuration',
                    '',
                    array(
                        'active_tab' => Ess_M2ePro_Block_Adminhtml_Walmart_Configuration_Tabs::TAB_ID_PRODUCT_TYPE
                    )
                )
            )->renderLayout();
    }

    public function gridAction()
    {
        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_walmart_productType_grid')
            ->toHtml();

        $this->getResponse()->setBody($response);
    }

    //----------------------------------

    public function editAction()
    {
        /** @var Ess_M2ePro_Model_Walmart_ProductType_Repository $productTypeRepository */
        $productTypeRepository = Mage::getModel('M2ePro/Walmart_ProductType_Repository');
        /** @var Ess_M2ePro_Model_Walmart_ProductTypeFactory $productTypeFactory */
        $productTypeFactory = Mage::getModel('M2ePro/Walmart_ProductTypeFactory');

        $productTypeId = $this->getRequest()->getParam('id');
        $productType = !empty($productTypeId)
            ? $productTypeRepository->get((int)$productTypeId)
            : $productTypeFactory->createEmpty();

        $this->setPageHelpLink(null, 'walmart-product-types');

        $this->initAction();

        $this->_addLeft(
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_walmart_productType_edit_tabs',
                '',
                array('productType' => $productType)
            )
        );

        $this->_addContent(
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_walmart_productType_edit',
                '',
                array('productType' => $productType)
            )
        );

        $this->_addContent(
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_walmart_productType_edit_fieldTemplates',
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

        $productTypes = $this->getAvailableProductTypes((int)$marketplaceId);
        /** @var Ess_M2ePro_Block_Adminhtml_Walmart_ProductType_Edit_SearchPopup $block */
        $block = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_productType_edit_searchPopup',
            ''
        );
        $block->setProductTypes($productTypes);

        $this->_addAjaxContent($block->toHtml());
    }

    /**
     * @param int $marketplaceId
     * @return array
     */
    private function getAvailableProductTypes($marketplaceId)
    {
        /** @var Ess_M2ePro_Model_Walmart_Dictionary_Marketplace_Repository $marketplaceDictionaryRepository */
        $marketplaceDictionaryRepository = Mage::getModel('M2ePro/Walmart_Dictionary_Marketplace_Repository');
        $marketplaceDictionary = $marketplaceDictionaryRepository->findByMarketplaceId($marketplaceId);
        if ($marketplaceDictionary === null) {
            return array();
        }

        $marketplaceDictionaryProductTypes = $marketplaceDictionary->getProductTypes();
        if (empty($marketplaceDictionaryProductTypes)) {
            return array();
        }
        $marketplaceDictionaryProductTypes = $this->sortMarketplaceDictionaryProductTypes(
            $marketplaceDictionaryProductTypes
        );

        /** @var Ess_M2ePro_Model_Walmart_ProductType_Repository $productTypeRepository */
        $productTypeRepository = Mage::getModel('M2ePro/Walmart_ProductType_Repository');
        $productTypes = $productTypeRepository->retrieveListWithKeyNick($marketplaceId);

        $result = array();
        foreach ($marketplaceDictionaryProductTypes as $marketplaceDictionaryProductType) {
            $productTypeData = array(
                'nick' => $marketplaceDictionaryProductType['nick'],
                'title' => $marketplaceDictionaryProductType['title'],
            );

            $existsProductType = isset($productTypes[$marketplaceDictionaryProductType['nick']])
                ? $productTypes[$marketplaceDictionaryProductType['nick']]
                : null;

            if ($existsProductType !== null) {
                $productTypeData['exist_product_type_id'] = $existsProductType->getId();
            }
            $result[] = $productTypeData;
        }

        return $result;
    }

    /**
     * @param array $productTypes
     * @return array
     */
    private function sortMarketplaceDictionaryProductTypes($productTypes)
    {
        $byTitle = array();
        foreach ($productTypes as $productType) {
            $byTitle[$productType['title']] = $productType;
        }

        ksort($byTitle);

        return array_values($byTitle);
    }

    //----------------------------------

    public function getCategoriesAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $parentCategoryId = $this->getRequest()->getParam('parent_category_id');

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

        $categories = $this->getCategories(
            (int)$marketplaceId,
            $parentCategoryId !== null ? (int)$parentCategoryId : null
        );

        $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array('categories' => $categories)
            )
        );
    }

    /**
     * @param int $marketplaceId
     * @param int|null $parentCategoryId
     * @return array
     */
    private function getCategories($marketplaceId, $parentCategoryId)
    {
        /** @var Ess_M2ePro_Model_Walmart_Dictionary_Category_Repository $categoryDictionaryRepository */
        $categoryDictionaryRepository = Mage::getModel('M2ePro/Walmart_Dictionary_Category_Repository');
        $categories = $parentCategoryId === null
            ? $categoryDictionaryRepository->findRoots($marketplaceId)
            : $categoryDictionaryRepository->findChildren($parentCategoryId);

        /** @var Ess_M2ePro_Model_Walmart_ProductType_Repository $productTypeRepository */
        $productTypeRepository = Mage::getModel('M2ePro/Walmart_ProductType_Repository');
        $productTypes = $productTypeRepository->retrieveListWithKeyNick($marketplaceId);

        $resultItems = array();
        foreach ($categories as $category) {
            $item = array(
                'id' => $category->getCategoryId(),
                'name' => $category->getTitle(),
                'is_leaf' => $category->isLeaf(),
                'product_types' => array(),
            );

            if ($category->isLeaf()) {
                $item['product_types'][] = array(
                    'title' => $category->getProductTypeTitle(),
                    'nick' => $category->getProductTypeNick(),
                    'template_id' => isset($productTypes[$category->getProductTypeNick()])
                        ? $productTypes[$category->getProductTypeNick()]->getId()
                        : null,
                );
            }

            $resultItems[] = $item;
        }

        return $resultItems;
    }

    //----------------------------------

    public function getProductTypeInfoAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
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

        /** @var Ess_M2ePro_Model_Walmart_Marketplace_Repository $marketplaceRepository */
        $marketplaceRepository = Mage::getModel('M2ePro/Walmart_Marketplace_Repository');
        $marketplace = $marketplaceRepository->get((int)$marketplaceId);
        $contentData = $this->buildProductTypeInfoContentData($productTypeNick, $marketplace);

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
     * @param string $productTypeNick
     * @param Ess_M2ePro_Model_Marketplace $marketplace
     * @return array
     */
    private function buildProductTypeInfoContentData($productTypeNick, $marketplace)
    {
        /** @var Ess_M2ePro_Model_Walmart_Dictionary_ProductTypeService $dictionaryProductTypeService */
        $dictionaryProductTypeService = Mage::getModel('M2ePro/Walmart_Dictionary_ProductTypeService');
        $productTypeDictionary = $dictionaryProductTypeService->retrieve(
            $productTypeNick,
            $marketplace
        );

        /** @var Ess_M2ePro_Model_Walmart_ProductType_Repository $productTypeRepository */
        $productTypeRepository = Mage::getModel('M2ePro/Walmart_ProductType_Repository');
        $productType = $productTypeRepository->findByDictionary($productTypeDictionary);
        if ($productType === null) {
            return $this->getProductTypeInfoContentData(
                $productTypeDictionary->getAttributes()
            );
        }

        return $this->getProductTypeInfoContentData(
            $productTypeDictionary->getAttributes(),
            $productType->getRawAttributesSettings()
        );
    }

    /**
     * @param array $dictionaryProductTypeAttributes
     * @param array $productTypeAttributesSettings
     * @return array
     */
    private function getProductTypeInfoContentData(
        array $dictionaryProductTypeAttributes,
        array $productTypeAttributesSettings = array()
    ) {
        $dateLocal = new DateTime(Mage::helper('M2ePro')->gmtDateToTimezone('2024-01-01'));
        $timestampUTC = Mage::helper('M2ePro')->timezoneDateToGmt($dateLocal->format('Y-m-d H:i:s'), true);
        $timezoneShift = $timestampUTC - $dateLocal->getTimestamp();

        return array(
            'groups' => array(
                array('title' => Mage::helper('M2ePro')->__('Attributes'), 'nick' => 'attributes',),
            ),
            'scheme' => $dictionaryProductTypeAttributes,
            'settings' => $productTypeAttributesSettings,
            'timezone_shift' => $timezoneShift,
            'specifics_default_settings' => array(),
        );
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

                    return $this->_redirect('*/adminhtml_walmart_productType/edit');
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

                return $this->_redirect('*/adminhtml_walmart_productType/index');
            }
        }

        /** @var Ess_M2ePro_Model_Walmart_ProductType_Builder $builder */
        $builder = Mage::getModel('M2ePro/Walmart_ProductType_Builder');
        /** @var Ess_M2ePro_Model_Walmart_ProductTypeFactory $productTypeFactory */
        $productTypeFactory = Mage::getModel('M2ePro/Walmart_ProductTypeFactory');
        $productType = $productTypeFactory->createEmpty();

        if ($id) {
            $productType->load($id);
        }

        $oldData = array();
        if ($productType->getId()) {
            $oldData = $this->makeSnapshot($productType);
        }

        $builder->build($productType, $post);
        /** @var Ess_M2ePro_Model_Walmart_ProductType $productType */
        $productType = $builder->getModel();
        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Product Type Settings were saved'));

        $newData = $this->makeSnapshot($productType);

        /** @var Ess_M2ePro_Model_Walmart_ProductType_Builder_Diff $diff */
        $diff = Mage::getModel('M2ePro/Walmart_ProductType_Builder_Diff');
        $diff->setNewSnapshot($newData);
        $diff->setOldSnapshot($oldData);

        /** @var Ess_M2ePro_Model_Walmart_ProductType_Builder_AffectedListingsProducts $affectedListingsProducts */
        $affectedListingsProducts = Mage::getModel('M2ePro/Walmart_ProductType_Builder_AffectedListingsProducts');
        $affectedListingsProducts->setModel($productType);

        /** @var Ess_M2ePro_Model_Walmart_ProductType_Builder_ChangeProcessor $changeProcessor */
        $changeProcessor = Mage::getModel('M2ePro/Walmart_ProductType_Builder_ChangeProcessor');
        $changeProcessor->process(
            $diff,
            $affectedListingsProducts->getData(array('id', 'status'))
        );

        $backUrl = Mage::helper('M2ePro')->getBackUrl(
            '*/adminhtml_walmart_productType/index',
            array(),
            array('edit' => array('id' => $productType->getId()))
        );

        $editUrl = $this->getUrl(
            '*/*/edit',
            array('id' => $productType->getId())
        );

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'status' => true,
                        'product_type_id' => $productType->getId(),
                        'back_url' => $backUrl,
                        'edit_url' => $editUrl,
                    )
                )
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
    private function isTryingOverrideExistingSettings($marketplaceId, $nick)
    {
        /** @var Ess_M2ePro_Model_Walmart_ProductType_Repository $productTypeRepository */
        $productTypeRepository = Mage::getModel('M2ePro/Walmart_ProductType_Repository');
        $productType = $productTypeRepository->findByMarketplaceIdAndNick($marketplaceId, $nick);

        return $productType !== null;
    }

    /**
     * @return array
     */
    private function makeSnapshot(Ess_M2ePro_Model_Walmart_ProductType $productType)
    {
        /** @var Ess_M2ePro_Model_Walmart_ProductType_Builder_SnapshotBuilder $snapshotBuilder */
        $snapshotBuilder = Mage::getModel('M2ePro/Walmart_ProductType_Builder_SnapshotBuilder');
        $snapshotBuilder->setModel($productType);

        return $snapshotBuilder->getSnapshot();
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

        /** @var Ess_M2ePro_Model_Walmart_ProductType_Service $productTypeService */
        $productTypeService = Mage::getModel('M2ePro/Walmart_ProductType_Service');
        $result = $productTypeService->deleteByIds($ids);

        if ($result->getCountDeleted() !== 0) {
            $this->_getSession()->addSuccess(
                Mage::helper('M2ePro')->__('Product Type deleted.')
            );
        }

        if ($result->getCountLocked() !== 0) {
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

        /** @var Ess_M2ePro_Model_Walmart_ProductType_Repository $productTypeRepository */
        $productTypeRepository = Mage::getModel('M2ePro/Walmart_ProductType_Repository');
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

    //----------------------------------

    public function getListingProductIdsByProductTypeAction()
    {
        $productTypeId = $this->getRequest()->getParam('product_type_id');
        if ($productTypeId === null) {
            throw new \LogicException('Parameter product_type_id is required');
        }

        /** @var Ess_M2ePro_Model_Resource_Walmart_Listing_Product $walmartListingProductResource */
        $walmartListingProductResource = Mage::getResourceModel('M2ePro/Walmart_Listing_Product');
        $select = $walmartListingProductResource->getReadConnection()->select();
        $select->from($walmartListingProductResource->getMainTable(), array('listing_product_id'));
        $select->where(
            Ess_M2ePro_Model_Resource_Walmart_Listing_Product::COLUMN_PRODUCT_TYPE_ID . ' = ?',
            $productTypeId
        );
        $ids = $walmartListingProductResource->getReadConnection()->fetchCol($select);

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode($ids)
        );
    }
}