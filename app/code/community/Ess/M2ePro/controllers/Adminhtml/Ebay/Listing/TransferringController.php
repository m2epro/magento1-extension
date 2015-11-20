<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_Listing_TransferringController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    protected $sessionKey = 'ebay_listing_transferring';

    //########################################

    protected function _initAction()
    {
        $this->loadLayout();

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_ebay/listings');
    }

    //########################################

    public function indexAction()
    {
        $this->clearSession();

        $generalBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_transferring_general')
            ->setData('listing_id', (int)$this->getRequest()->getParam('listing_id'))
            ->setData('products_ids', $this->getRequest()->getParam('products_ids'));
        $this->getResponse()->setBody($generalBlock->toHtml());
    }

    //########################################

    public function shownTutorialAction()
    {
        Mage::helper('M2ePro/Module')
            ->getConfig()
            ->setGroupValue('/ebay/sell_on_another_marketplace/', 'tutorial_shown', 1);
    }

    //########################################

    public function getAccountsAction()
    {
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account')->setOrder('title','ASC');

        $accounts = array();
        foreach ($collection->getItems() as $account) {
            $accounts[] = array(
                'id'               => $account->getId(),
                'title'            => Mage::helper('M2ePro')->escapeHtml($account->getTitle()),
                'translation_hash' => (bool)$account->getTranslationHash() ? '1' : '0',
            );
        }

        $this->getResponse()->setBody(json_encode($accounts));
    }

    //########################################

    public function getStoresAction()
    {
        // ---------------------------------------
        $storeSwitcherBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_storeSwitcher')
            ->setData('id', 'transferring_store_id');

        $this->getResponse()->setBody($storeSwitcherBlock->toHtml());
    }

    //########################################

    public function getListingsAction()
    {
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing')
            ->addFieldToFilter('id',             array('neq' => (int)$this->getRequest()->getParam('listing_id')))
            ->addFieldToFilter('account_id',     (int)$this->getRequest()->getParam('account_id'))
            ->addFieldToFilter('marketplace_id', (int)$this->getRequest()->getParam('marketplace_id'))
            ->addFieldToFilter('store_id',       (int)$this->getRequest()->getParam('store_id'));

        $listings = array();
        foreach ($collection->getItems() as $listing) {
            $listings[] = array(
                'id'    => $listing->getId(),
                'title' => Mage::helper('M2ePro')->escapeHtml($listing->getTitle())
            );
        }

        $this->getResponse()->setBody(json_encode(array(
            'listings'                     => $listings,
            'is_allowed_migration_service' => $this->isAllowedMigrationService() ? '1' : '0',
        )));
    }

    //########################################

    public function stepPolicyAction()
    {
        if (!$this->getRequest()->isPost()) {
            return;
        }

        $post = $this->getRequest()->getPost();

        if (!isset($post['account_id']) || !isset($post['marketplace_id']) || !isset($post['store_id'])) {
            return;
        }

        $this->setSessionValue('account_id',     (int)$post['account_id']);
        $this->setSessionValue('marketplace_id', (int)$post['marketplace_id']);
        $this->setSessionValue('store_id',       (int)$post['store_id']);

        // ---------------------------------------
        $this->loadTemplatesDataFromSession();
        // ---------------------------------------

        $params = array(
            'products_ids'        => $this->getRequest()->getParam('products_ids'),
            'policy_localization' => $this->getSourceListingFromRequest()
                                        ->getMarketplace()
                                        ->getChildObject()
                                        ->getLanguageCode(),
            'is_allowed'          => true,
        );

        $listingBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_transferring_step_policy', '', $params);

        $this->getResponse()->setBody($listingBlock->toHtml());
    }

    //########################################

    public function stepTranslationAction()
    {
        $translationAccountBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_transferring_step_translation')
            ->setData('account_id', (int)$this->getRequest()->getParam('account_id'))
            ->setData('is_allowed', true);

        $this->getResponse()->setBody($translationAccountBlock->toHtml());
    }

    //########################################

    public function createTranslationAccountAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->getResponse()->setBody(json_encode(array('result' => 'error')));
        }

        if (empty($post['account_id']) ||
            empty($post['email'])      ||
            empty($post['first_name']) ||
            empty($post['last_name'])  ||
            empty($post['company'])    ||
            empty($post['country'])) {
            return $this->getResponse()->setBody(json_encode(array('result' => 'error')));
        }

        $account = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Account',(int)$post['account_id'])
            ->getChildObject();
        $ebayInfo = json_decode($account['info'], true);

        if (empty($ebayInfo['UserID'])) {
            return $this->getResponse()->setBody(json_encode(array('result' => 'error')));
        }

        $params = array(
            'email'       => $post['email'],
            'first_name'  => $post['first_name'],
            'last_name'   => $post['last_name'],
            'company'     => $post['company'],
            'additional'  => array(
                'country' => $post['country'],
                'ebay'    => array('user_id' => $ebayInfo['UserID'],),
            ),
        );

        try {

            $dispatcherObject = Mage::getModel('M2ePro/Connector_Translation_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector('account', 'add', 'entity', $params);
            $response = $dispatcherObject->process($connectorObj);

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }

        if (!isset($response['hash'])) {
            return $this->getResponse()->setBody(json_encode(array('result' => 'error')));
        }

        $account->addData(array(
            'translation_hash' => $response['hash'],
            'translation_info' => json_encode($response['info']),
        ))->save();

        return $this->getResponse()->setBody(json_encode(array(
            'result' => 'success',
            'hash'   => $response['hash'],
            'info'   => $response['info'],
        )));
    }

    public function refreshTranslationAccountAction()
    {
        $accountId = $this->getRequest()->getParam('account_id');

        $account = !empty($accountId)
            ? Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Account', $accountId)
            : $this->getSourceListingFromRequest()->getAccount();

        try {

            $dispatcherObject = Mage::getModel('M2ePro/Connector_Translation_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector('account', 'get', 'info',
                                                                   array(), NULL, $account);

            $response = $dispatcherObject->process($connectorObj);

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }

        if (count($response) <= 0) {
            return $this->getResponse()->setBody(json_encode(array('result' => 'error')));
        }

        $account->getChildObject()->setData('translation_info', json_encode($response))->save();

        return $this->getResponse()->setBody(json_encode(array(
            'result' => 'success',
            'info'   => $response
        )));
    }

    //########################################

    public function createListingAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->getResponse()->setBody(json_encode(array('result' => 'error')));
        }

        if (empty($post['title'])          ||
            empty($post['account_id'])     ||
            empty($post['marketplace_id']) ||
            !isset($post['store_id'])) {
            return $this->getResponse()->setBody(json_encode(array('result' => 'error')));
        }

        $listing = $this->getSourceListingFromRequest();

        // ---------------------------------------

        $data = array(
            'title'          => strip_tags($post['title']),
            'account_id'     => (int)$post['account_id'],
            'marketplace_id' => (int)$post['marketplace_id'],
            'store_id'       => (int)$post['store_id'],
        );

        // ---------------------------------------

        $isDifferentMarketplace = ($data['marketplace_id'] != $listing->getMarketplace()->getId());

        // ---------------------------------------
        $data = array_merge($data,
                            $this->getTemplatesDataFromSource($listing->getChildObject(),$isDifferentMarketplace));
        $isDifferentMarketplace && $data = array_merge($data, $this->getTemplatesDataFromPost());
        // ---------------------------------------

        $model = Mage::helper('M2ePro/Component_Ebay')->getModel('Listing');
        $model->addData($data)->save();

        // ---------------------------------------
        $this->setAutoActionData($model, $listing, $isDifferentMarketplace);
        // ---------------------------------------

        $this->getResponse()->setBody(json_encode(array(
            'result'     => 'success',
            'listing_id' => $model->getId(),
        )));
    }

    //########################################

    public function addProductsAction()
    {
        $targetListingId = $this->getRequest()->getParam('target_listing_id');
        $targetListing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',(int)$targetListingId);

        $isDifferentMarketplace =
            ($targetListing->getMarketplace()->getId() !=
                $this->getSourceListingFromRequest()->getMarketplace()->getId());

        $productsIds = $this->getRequest()->getParam('products');
        $productsIds = explode(',', $productsIds);
        $productsIds = array_unique($productsIds);
        $productsIds = array_filter($productsIds);

        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product')
            ->addFieldToFilter('id', array('in' => ($productsIds)));

        $ids = array();
        $failedProducts = array();
        foreach ($collection->getItems() as $sourceListingProduct) {
            $listingProduct = $targetListing->addProduct($sourceListingProduct->getProductId());
            if ($listingProduct instanceof Ess_M2ePro_Model_Listing_Product) {
                $ids[] = $listingProduct->getId();

                $data = $this->getTemplatesDataFromSource($sourceListingProduct->getChildObject(),
                                                          $isDifferentMarketplace);
                $listingProduct->addData($data);

                if (!$isDifferentMarketplace) {
                    $listingProduct
                        ->setData('template_category_id',$sourceListingProduct->getTemplateCategoryId())
                        ->setData('template_other_category_id',$sourceListingProduct->getTemplateOtherCategoryId());
                } else {
                    $matchingListingProducts = $this->getSessionValue('matching_listing_products');
                    $matchingListingProducts[$listingProduct->getId()] = $sourceListingProduct->getId();
                    $this->setSessionValue('matching_listing_products', $matchingListingProducts);
                }

                $listingProduct->save();
            } else {
                $failedProducts[] = $sourceListingProduct->getProductId();
            }
        }

        // ---------------------------------------
        if ($this->getRequest()->getParam('is_need_to_set_catalog_policy') == 'true') {
            $existingIds = $targetListing->getChildObject()->getAddedListingProductsIds();
            $existingIds = array_values(array_unique(array_merge($existingIds,$ids)));
            $targetListing->setData('product_add_ids',json_encode($existingIds))->save();
        }
        // ---------------------------------------

        $this->getResponse()->setBody(json_encode(array(
            'result' => 'success',
            'success_products' => $ids,
            'failed_products'  => $failedProducts,
        )));
    }

    //########################################

    public function autoMigrationAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->getResponse()->setBody(json_encode(array('result' => 'error')));
        }

        if (empty($post['target_listing_id']) || empty($post['products'])) {
            return $this->getResponse()->setBody(json_encode(array('result' => 'error')));
        }

        if (!empty($post['translation_service']) &&
            !Mage::helper('M2ePro/Component_Ebay')->isAllowedTranslationService($post['translation_service'])) {
            return $this->getResponse()->setBody(json_encode(array('result' => 'error')));
        }

        $matchingListingProducts = $this->getSessionValue('matching_listing_products');

        // ---------------------------------------
        $targetListing = Mage::helper('M2ePro/Component_Ebay')
            ->getCachedObject('Listing',(int)$post['target_listing_id']);

        $sourceLanguage = $this->getSourceListingFromRequest()->getMarketplace()->getChildObject()->getLanguageCode();
        $targetLanguage = $targetListing->getMarketplace()->getChildObject()->getLanguageCode();
        // ---------------------------------------

        $sourceLanguage = str_replace('_', '-', strtolower($sourceLanguage));
        $targetLanguage = str_replace('_', '-', strtolower($targetLanguage));

        $ebayCategoryHelper = Mage::helper('M2ePro/Component_Ebay_Category_Ebay');

        $translationService = !empty($post['translation_service'])
            ? $post['translation_service']
            : NULL;

        $productsIds = explode(',', $post['products']);
        $productsIds = array_unique($productsIds);
        $productsIds = array_filter($productsIds);

        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product')
            ->addFieldToFilter('id', array('in' => ($productsIds)));

        $ids = array();
        $failedProducts = array();

        /** @var  $logModel Ess_M2ePro_Model_Listing_Log */
        $logModel = Mage::getModel('M2ePro/Listing_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
        $logsActionId = $logModel->getNextActionId();

        foreach ($collection->getItems() as $targetListingProduct) {

            $sourceListingProduct = Mage::helper('M2ePro/Component_Ebay')
                ->getObject('Listing_Product',(int)$matchingListingProducts[$targetListingProduct->getId()]);

            if (!$sourceListingProduct->getChildObject()->isSetCategoryTemplate()) {

                // Set message to log
                // ---------------------------------------
                $logModel->addProductMessage(
                    $post['target_listing_id'],
                    $targetListingProduct->getProductId(),
                    $targetListingProduct->getId(),
                    Ess_M2ePro_Helper_Data::INITIATOR_USER,
                    $logsActionId,
                    Ess_M2ePro_Model_Listing_Log::ACTION_TRANSLATE_PRODUCT,
                    // M2ePro_TRANSLATIONS
                    // Categories Settings are not set.
                    'Categories Settings are not set.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                $tempKey = array_search($targetListingProduct->getId(),$productsIds);
                if ($tempKey !== false) {
                    unset($productsIds[$tempKey]);
                }

                $failedProducts[] = $targetListingProduct->getProductId();
                continue;
            }

            $descriptionTemplateSource = $targetListingProduct->getChildObject()->getDescriptionTemplateSource();
            $descriptionRenderer       = $targetListingProduct->getChildObject()->getDescriptionRenderer();

            $title       = trim($descriptionTemplateSource->getTitle());
            $description = trim($descriptionRenderer->parseTemplate($descriptionTemplateSource->getDescription()));

            if (!$title || !$description) {

                // Set message to log
                // ---------------------------------------
                $logModel->addProductMessage(
                    $post['target_listing_id'],
                    $targetListingProduct->getProductId(),
                    $targetListingProduct->getId(),
                    Ess_M2ePro_Helper_Data::INITIATOR_USER,
                    $logsActionId,
                    Ess_M2ePro_Model_Listing_Log::ACTION_TRANSLATE_PRODUCT,
                    // M2ePro_TRANSLATIONS
                    // Translation cannot be executed because Attributes for Title or Description are empty.
                    'Translation cannot be executed because Attributes for Title or Description are empty.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                $tempKey = array_search($targetListingProduct->getId(),$productsIds);
                if ($tempKey !== false) {
                    unset($productsIds[$tempKey]);
                }

                $failedProducts[] = $targetListingProduct->getProductId();
                continue;
            }

            $categoryTemplate  = $sourceListingProduct->getChildObject()->getCategoryTemplate();
            $primaryCategoryId = $categoryTemplate->getCategoryMainId();
            $marketplaceId     = $categoryTemplate->getMarketplaceId();

            $additionalData = $targetListingProduct->getAdditionalData();
            $additionalData['translation_service'] = array(
                'from' =>  array(
                    'description'    => array(
                        'title'       => $title,
                        'subtitle'    => trim($descriptionTemplateSource->getSubTitle()),
                        'description' => $description,
                    ),
                    'category'       => array(
                        'primary_id'   => $primaryCategoryId,
                        'top_level_id' => $ebayCategoryHelper->getTopLevel($primaryCategoryId, $marketplaceId),
                        'path'         => $categoryTemplate->getCategoryPath($targetListing, false),
                    ),
                    'item_specifics' => $this->getEbayItemSpecificsData($sourceListingProduct),
                    'sku'            => $targetListingProduct->getChildObject()->getSku(),
                    'language'       => $sourceLanguage,
                ),
                'to' => array(
                    'description'    => array(
                        'title'       => '',
                        'subtitle'    => '',
                        'description' => '',
                    ),
                    'category'       => array(
                        'primary_id'   => '',
                        'top_level_id' => '',
                        'path'         => '',
                    ),
                    'item_specifics' => array(),
                    'sku'            => '',
                    'language'       => $targetLanguage,
                ),
                'payment' => array(
                    'amount_due' => '',
                    'currency'   => '',
                ),
            );

            $data = $this->getSynchronizationTemplateDataFromSource($sourceListingProduct->getChildObject());
            $targetListingProduct->addData($data)->save();

            $targetListingProduct->setData('additional_data', json_encode($additionalData))->save();
            $targetListingProduct->getChildObject()->addData(array(
                'translation_status'  => Ess_M2ePro_Model_Ebay_Listing_Product::TRANSLATION_STATUS_PENDING,
                'translation_service' => $translationService
            ))->save();

            $ids[] = $targetListingProduct->getId();
        }

        $params = array('status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER);

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Translation_Product_Add_Dispatcher');
        $result = (int)$dispatcherObject->process($productsIds, $params);

        if ($result == Ess_M2ePro_Helper_Data::STATUS_SUCCESS) {
            return $this->getResponse()->setBody(json_encode(array(
                'result'           => 'success',
                'success_products' => $ids,
                'failed_products'  => $failedProducts,
            )));
        }

        return $this->getResponse()->setBody(json_encode(array('result' => 'error')));
    }

    public function updateTranslationServiceAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->getResponse()->setBody(json_encode(array('result' => 'error')));
        }

        if (empty($post['products_ids'])) {
            return $this->getResponse()->setBody(json_encode(array('result' => 'error')));
        }

        if (empty($post['translation_service']) ||
            !Mage::helper('M2ePro/Component_Ebay')->isAllowedTranslationService($post['translation_service'])) {
            return $this->getResponse()->setBody(json_encode(array('result' => 'success')));
        }

        $productsIds = $post['products_ids'];
        $productsIds = explode(',', $productsIds);
        $productsIds = array_filter($productsIds);

        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product')
            ->addFieldToFilter('id', array('in' => ($productsIds)));

        $allowedTranslationStatuses = array(
            Ess_M2ePro_Model_Ebay_Listing_Product::TRANSLATION_STATUS_PENDING,
            Ess_M2ePro_Model_Ebay_Listing_Product::TRANSLATION_STATUS_PENDING_PAYMENT_REQUIRED
        );
        foreach ($collection->getItems() as $listingProduct) {
            if (in_array($listingProduct->getChildObject()->getTranslationStatus(), $allowedTranslationStatuses)) {
                $listingProduct->getChildObject()
                               ->setData('translation_service', $post['translation_service'])
                               ->save();
            }
        }

        return $this->getResponse()->setBody(json_encode(array('result' => 'success')));
    }

    //########################################

    public function getFailedProductsGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_transferring_failedProducts','',
            array(
                'grid_url' => $this->getUrl('*/adminhtml_listing_moving/failedProductsGrid', array('_current'=>true))
            )
        );
        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function getPaymentUrlAction()
    {
        $accountId = $this->getRequest()->getParam('account_id');

        $account = !empty($accountId)
            ? Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Account', $accountId)
            : $this->getSourceListingFromRequest()->getAccount();

        $params = array(
            'amount'   => number_format($this->getRequest()->getParam('amount'), 2),
            'currency' => $this->getRequest()->getParam('currency'),
        );

        try {

            $dispatcherObject = Mage::getModel('M2ePro/Connector_Translation_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector('account', 'add', 'balance',
                                                                   $params, NULL, $account);

            $response = $dispatcherObject->process($connectorObj);

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }

        if (!isset($response['payment_url'])) {
            return $this->getResponse()->setBody(json_encode(array('result' => 'error')));
        }

        return $this->getResponse()->setBody(json_encode(array(
            'result'      => 'success',
            'payment_url' => $response['payment_url'],
        )));
    }

    //########################################

    protected function setSessionValue($key, $value)
    {
        $sessionData = $this->getSessionValue();
        $sessionData[$key] = $value;

        Mage::helper('M2ePro/Data_Session')->setValue($this->sessionKey, $sessionData);

        return $this;
    }

    protected function getSessionValue($key = NULL)
    {
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->sessionKey);

        if (is_null($sessionData)) {
            $sessionData = array();
        }

        if (is_null($key)) {
            return $sessionData;
        }

        return isset($sessionData[$key]) ? $sessionData[$key] : NULL;
    }

    //########################################

    private function clearSession()
    {
        Mage::helper('M2ePro/Data_Session')->setValue($this->sessionKey, NULL);
    }

    //########################################

    private function loadTemplatesDataFromSession()
    {
        // ---------------------------------------
        $listingTitle = $this->getSessionValue('listing_title');
        Mage::helper('M2ePro/Data_Global')->setValue('ebay_custom_template_title', $listingTitle);

        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Template_Switcher_DataLoader $dataLoader */
        $dataLoader = Mage::getBlockSingleton('M2ePro/adminhtml_ebay_listing_template_switcher_dataLoader');
        $dataLoader->load(Mage::helper('M2ePro/Data_Session'), array('session_key' => $this->sessionKey));
        // ---------------------------------------
    }

    //########################################

    private function isAllowedMigrationService()
    {
        $marketplaceId = (int)$this->getRequest()->getParam('marketplace_id');

        if (empty($marketplaceId)) {
            return false;
        }

        $targetEbayMarketplace = Mage::helper('M2ePro/Component_Ebay')
            ->getCachedObject('Marketplace', $marketplaceId)->getChildObject();

        if ($targetEbayMarketplace->getId()) {

            $sourceEbayMarketplace = $this->getSourceListingFromRequest()->getMarketplace()->getChildObject();

            if ($targetEbayMarketplace->getId() != $sourceEbayMarketplace->getId() &&
                ($targetEbayMarketplace->isTranslationServiceModeTo() ||
                    $targetEbayMarketplace->isTranslationServiceModeBoth() )       &&
                ($sourceEbayMarketplace->isTranslationServiceModeFrom() ||
                    $sourceEbayMarketplace->isTranslationServiceModeBoth())        &&
                $targetEbayMarketplace->getLanguageCode() != $sourceEbayMarketplace->getLanguageCode()) {
                return true;
            }
        }

        return false;
    }

    //########################################

    private function getTemplatesDataFromSource($ownerObject, $isDifferentMarketplace = false)
    {
        if (!($ownerObject instanceof Ess_M2ePro_Model_Ebay_Listing) &&
            !($ownerObject instanceof Ess_M2ePro_Model_Ebay_Listing_Product)) {
            return array();
        }

        $templatesNicks = array(
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION,
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT,
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION,
        );

        if (!$isDifferentMarketplace) {
            $templatesNicks[] = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN;
            $templatesNicks[] = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING;
            $templatesNicks[] = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT;
        }

        // ---------------------------------------
        $data = array();
        foreach ($templatesNicks as $nick) {
            $manager = Mage::getModel('M2ePro/Ebay_Template_Manager')->setTemplate($nick)->setOwnerObject($ownerObject);
            $templateMode = $manager->getModeValue();
            $idColumn     = $manager->getIdColumnNameByMode($templateMode);
            $modeColumn   = $manager->getModeColumnName();

            $data[$idColumn]   = $manager->getIdColumnValue();
            $data[$modeColumn] = $templateMode;
        }
        // ---------------------------------------

        return $data;
    }

    #############################################

    private function getSynchronizationTemplateDataFromSource($ownerObject)
    {
        $manager = Mage::getModel('M2ePro/Ebay_Template_Manager')
            ->setTemplate(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION)
            ->setOwnerObject($ownerObject);

        $templateMode = $manager->getModeValue();
        $idColumn     = $manager->getIdColumnNameByMode($templateMode);
        $modeColumn   = $manager->getModeColumnName();

        $data = array(
            $idColumn   => $manager->getIdColumnValue(),
            $modeColumn => $templateMode,
        );

        if (!$manager->isModeParent()) {
            $ebaySynchronizationTemplate = $ownerObject->getEbaySynchronizationTemplate();
        } else {
            $ebaySynchronizationTemplate = $ownerObject->getListing()
                                                       ->getChildObject()
                                                       ->getEbaySynchronizationTemplate();
        }

        if ($ebaySynchronizationTemplate->isListMode()) {
            $key = 'new_synchronization_template_id_'.$ebaySynchronizationTemplate->getId();

            if (!$this->getSessionValue($key)) {
                $sourceData = $ebaySynchronizationTemplate->getData();
                unset($sourceData['id']);
                $sourceData['list_mode'] = Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_MODE_NONE;
                $sourceData['title'] =
                    $sourceData['title'].Mage::helper('M2ePro')->__(' (Changed because Translation Service applied.)');
                $sourceData['is_custom_template'] = 1;
                $templateModel = $manager->getTemplateModel();
                $templateModel->addData($sourceData)->save();

                $this->setSessionValue($key, $templateModel->getId());
            }

            $idColumn = $manager->getIdColumnNameByMode(Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM);

            $data[$idColumn]   = $this->getSessionValue($key);
            $data[$modeColumn] = Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM;
        }

        return $data;
    }

    #############################################

    private function getTemplatesDataFromPost()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return array();
        }

        $templatesNicks = array(
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN,
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING,
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT
        );

        // ---------------------------------------
        $data = array();
        foreach ($templatesNicks as $nick) {
            $manager = Mage::getModel('M2ePro/Ebay_Template_Manager')->setTemplate($nick);

            if (!isset($post["template_{$nick}"])) {
                continue;
            }

            $templateData = json_decode(base64_decode($post["template_{$nick}"]), true);

            $templateId = $templateData['id'];
            $templateMode = $templateData['mode'];

            $idColumn = $manager->getIdColumnNameByMode($templateMode);
            $modeColumn = $manager->getModeColumnName();

            if (!is_null($idColumn)) {
                $data[$idColumn] = (int)$templateId;
            }

            $data[$modeColumn] = $templateMode;

        }
        // ---------------------------------------

        return $data;
    }

    #############################################

    private function setAutoActionData(Ess_M2ePro_Model_Listing $targetListing,
                                       Ess_M2ePro_Model_Listing $sourceListing,
                                       $isDifferentMarketplace = false)
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing $sourceEbayListing */
        $sourceEbayListing = $sourceListing->getChildObject();

        $listingData = array(
            'auto_mode' =>
                $sourceListing->getAutoMode(),
            'auto_global_adding_mode' =>
                $sourceListing->getAutoGlobalAddingMode(),
            'auto_global_adding_template_category_id' =>
                $sourceEbayListing->getAutoGlobalAddingTemplateCategoryId(),
            'auto_global_adding_template_other_category_id' =>
                $sourceEbayListing->getAutoGlobalAddingTemplateOtherCategoryId(),
            'auto_website_adding_mode' =>
                $sourceListing->getAutoWebsiteAddingMode(),
            'auto_website_adding_template_category_id' =>
                $sourceEbayListing->getAutoWebsiteAddingTemplateCategoryId(),
            'auto_website_adding_template_other_category_id' =>
                $sourceEbayListing->getAutoWebsiteAddingTemplateOtherCategoryId(),
            'auto_website_deleting_mode' =>
                $sourceListing->getAutoWebsiteDeletingMode()
        );

        if ($isDifferentMarketplace) {
            if ($sourceEbayListing->isAutoGlobalAddingModeAddAndAssignCategory()) {
                $listingData['auto_global_adding_mode'] = Ess_M2ePro_Model_Listing::ADDING_MODE_ADD;
                $listingData['auto_global_adding_template_category_id']       = NULL;
                $listingData['auto_global_adding_template_other_category_id'] = NULL;
            }

            if ($sourceEbayListing->isAutoWebsiteAddingModeAddAndAssignCategory()) {
                $listingData['auto_website_adding_mode'] = Ess_M2ePro_Model_Listing::ADDING_MODE_ADD;
                $listingData['auto_website_adding_template_category_id']       = NULL;
                $listingData['auto_website_adding_template_other_category_id'] = NULL;
            }
        }

        $targetListing->addData($listingData)->save();

        if ($sourceListing->isAutoModeCategory()) {
            $this->setAutoCategoryData($targetListing->getId(), $sourceListing->getId(), $isDifferentMarketplace);
        }
    }

    private function setAutoCategoryData($targetListingId, $sourceListingId, $isDifferentMarketplace = false)
    {
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Auto_Category_Group');
        $collection->addFieldToFilter('main_table.listing_id', (int)$sourceListingId);

        foreach ($collection->getItems() as $sourceGroup) {

            /** @var Ess_M2ePro_Model_Listing_Auto_Category_Group $sourceGroup */

            /** @var Ess_M2ePro_Model_Ebay_Listing_Auto_Category_Group $group */
            $group = Mage::helper('M2ePro/Component_Ebay')->getModel('Listing_Auto_Category_Group')
                ->addData($sourceGroup->getData());

            $group->setData('listing_id', $targetListingId);

            /** @var Ess_M2ePro_Model_Ebay_Listing_Auto_Category_Group $ebaySourceGroup */
            $ebaySourceGroup = $sourceGroup->getChildObject();

            if ($isDifferentMarketplace && $ebaySourceGroup->isAddingModeAddAndAssignCategory()) {
                $group->setData('adding_mode', Ess_M2ePro_Model_Listing::ADDING_MODE_ADD);
                $group->setData('adding_template_category_id', NULL);
                $group->setData('adding_template_other_category_id', NULL);
            }

            $group->save();

            $categories = $sourceGroup->getCategories();

            foreach ($categories as $sourceCategory) {
                $category = Mage::getModel('M2ePro/Listing_Auto_Category')->addData($sourceCategory->getData());
                $category->setData('group_id', $group->getId());
                $category->save();
            }
        }
    }

    //########################################

    /** @return Ess_M2ePro_Model_Listing
     * @throws Ess_M2ePro_Model_Exception
     */
    private function getSourceListingFromRequest()
    {
        if (!$listingId = $this->getRequest()->getParam('listing_id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        return Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',(int)$listingId);
    }

    //########################################

    protected function getEbayItemSpecificsData($listingProduct)
    {
        $data = array();

        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product_Cache */
        $magentoProduct = $listingProduct->getMagentoProduct();

        $filter = array('mode' => array('in' => array(
            Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_ITEM_SPECIFICS,
            Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_CUSTOM_ITEM_SPECIFICS
        )));
        $categoryTemplate = $listingProduct->getChildObject()->getCategoryTemplate();

        $specifics = $categoryTemplate->getSpecifics(true, $filter);

        /** @var $specific Ess_M2ePro_Model_Ebay_Template_Category_Specific */
        foreach ($specifics as $specific) {

            $magentoProduct->clearNotFoundAttributes();

            $tempAttributeLabel  = $specific->getSource($magentoProduct)->getLabel();
            $tempAttributeValues = $specific->getSource($magentoProduct)->getValues();

            $attributes = $magentoProduct->getNotFoundAttributes();

            if (!empty($attributes)) {
                continue;
            }

            $values = array();
            foreach ($tempAttributeValues as $tempAttributeValue) {
                if ($tempAttributeValue == '--') {
                    continue;
                }
                $values[] = $tempAttributeValue;
            }

            $data[] = array('name'  => $tempAttributeLabel,
                            'value' => $values);
        }

        return $data;
    }

    //########################################
}