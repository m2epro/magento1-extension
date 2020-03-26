<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_Listing_TransferringController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    protected $_sessionKey = 'ebay_listing_transferring';

    //########################################

    protected function _initAction()
    {
        $this->loadLayout();

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK . '/listings'
        );
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
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account')->setOrder('title', 'ASC');

        $accounts = array();
        foreach ($collection->getItems() as $account) {
            $accounts[] = array(
                'id'    => $account->getId(),
                'title' => Mage::helper('M2ePro')->escapeHtml($account->getTitle()),
            );
        }

        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($accounts));
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
            ->addFieldToFilter('id', array('neq' => (int)$this->getRequest()->getParam('listing_id')))
            ->addFieldToFilter('account_id', (int)$this->getRequest()->getParam('account_id'))
            ->addFieldToFilter('marketplace_id', (int)$this->getRequest()->getParam('marketplace_id'))
            ->addFieldToFilter('store_id', (int)$this->getRequest()->getParam('store_id'));

        $listings = array();
        foreach ($collection->getItems() as $listing) {
            $listings[] = array(
                'id'    => $listing->getId(),
                'title' => Mage::helper('M2ePro')->escapeHtml($listing->getTitle())
            );
        }

        $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'listings'                     => $listings,
                'is_allowed_migration_service' => '0',
                )
            )
        );
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

        $this->setSessionValue('account_id', (int)$post['account_id']);
        $this->setSessionValue('marketplace_id', (int)$post['marketplace_id']);
        $this->setSessionValue('store_id', (int)$post['store_id']);

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

    public function createListingAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' => 'error')));
        }

        if (empty($post['title'])          ||
            empty($post['account_id'])     ||
            empty($post['marketplace_id']) ||
            !isset($post['store_id'])) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' => 'error')));
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
        $data = array_merge(
            $data,
            $this->getTemplatesDataFromSource($listing->getChildObject(), $isDifferentMarketplace)
        );
        $isDifferentMarketplace && $data = array_merge($data, $this->getTemplatesDataFromPost());
        // ---------------------------------------

        $model = Mage::helper('M2ePro/Component_Ebay')->getModel('Listing');
        $model->addData($data)->save();

        // ---------------------------------------
        $this->setAutoActionData($model, $listing, $isDifferentMarketplace);
        // ---------------------------------------

        $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'result'     => 'success',
                'listing_id' => $model->getId(),
                )
            )
        );
    }

    //########################################

    public function addProductsAction()
    {
        $targetListingId = $this->getRequest()->getParam('target_listing_id');
        /** @var Ess_M2ePro_Model_Listing $targetListing */
        $targetListing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', (int)$targetListingId);

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
        $errorsCount = 0;
        foreach ($collection->getItems() as $sourceListingProduct) {
            $listingProduct = $targetListing->getChildObject()->addProductFromAnotherEbaySite(
                $sourceListingProduct, $this->getSourceListingFromRequest()
            );
            if ($listingProduct instanceof Ess_M2ePro_Model_Listing_Product) {
                $ids[] = $listingProduct->getId();

                $data = $this->getTemplatesDataFromSource(
                    $sourceListingProduct->getChildObject(),
                    $isDifferentMarketplace
                );
                $listingProduct->addData($data);

                if (!$isDifferentMarketplace) {
                    $listingProduct
                        ->setData('template_category_id', $sourceListingProduct->getTemplateCategoryId())
                        ->setData('template_other_category_id', $sourceListingProduct->getTemplateOtherCategoryId());
                } else {
                    $matchingListingProducts = $this->getSessionValue('matching_listing_products');
                    $matchingListingProducts[$listingProduct->getId()] = $sourceListingProduct->getId();
                    $this->setSessionValue('matching_listing_products', $matchingListingProducts);
                }

                $listingProduct->save();
            } else {
                $errorsCount++;
            }
        }

        // ---------------------------------------
        $existingIds = $targetListing->getChildObject()->getAddedListingProductsIds();
        $existingIds = array_values(array_unique(array_merge($existingIds, $ids)));
        $targetListing->setData('product_add_ids', Mage::helper('M2ePro')->jsonEncode($existingIds))->save();
        // ---------------------------------------

        if ($this->getRequest()->getParam('is_last_part')) {
            $errorsCount = $errorsCount + $this->getRequest()->getParam('total_errors_count');

            if ($errorsCount) {
                $logViewUrl = $this->getUrl(
                    '*/adminhtml_ebay_log/listing', array(
                    'id' => $this->getSourceListingFromRequest()->getId()
                    )
                );

                if (count($productsIds) == $errorsCount) {
                    $this->getSession()->addError(
                        Mage::helper('M2ePro')->__(
                            'Products were not added to the selected Listing. Please <a target="_blank" href="%url%">
                        view Log</a> for the details.',
                            $logViewUrl
                        )
                    );

                    return $this->getResponse()->setBody(
                        Mage::helper('M2ePro')->jsonEncode(
                            array(
                            'result' => 'error'
                            )
                        )
                    );
                }

                $this->getSession()->addError(
                    Mage::helper('M2ePro')->__(
                        '%errors_count% product(s) were not added to the selected Listing. Please
                    <a target="_blank" href="%url%">view Log</a> for the details.',
                        $errorsCount, $logViewUrl
                    )
                );
            } else {
                $this->getSession()->addSuccess(
                    Mage::helper('M2ePro')->__(
                        'The Products have been successfully added into Destination Listing'
                    )
                );
            }
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'result' => 'success',
                'success_products' => $ids,
                'errors_count'  => $errorsCount,
                )
            )
        );
    }

    //########################################

    protected function setSessionValue($key, $value)
    {
        $sessionData = $this->getSessionValue();
        $sessionData[$key] = $value;

        Mage::helper('M2ePro/Data_Session')->setValue($this->_sessionKey, $sessionData);

        return $this;
    }

    protected function getSessionValue($key = null)
    {
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->_sessionKey);

        if ($sessionData === null) {
            $sessionData = array();
        }

        if ($key === null) {
            return $sessionData;
        }

        return isset($sessionData[$key]) ? $sessionData[$key] : null;
    }

    //########################################

    protected function clearSession()
    {
        Mage::helper('M2ePro/Data_Session')->setValue($this->_sessionKey, null);
    }

    //########################################

    protected function loadTemplatesDataFromSession()
    {
        // ---------------------------------------
        $listingTitle = $this->getSessionValue('listing_title');
        Mage::helper('M2ePro/Data_Global')->setValue('ebay_custom_template_title', $listingTitle);

        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Template_Switcher_DataLoader $dataLoader */
        $dataLoader = Mage::getBlockSingleton('M2ePro/adminhtml_ebay_listing_template_switcher_dataLoader');
        $dataLoader->load(Mage::helper('M2ePro/Data_Session'), array('session_key' => $this->_sessionKey));
        // ---------------------------------------
    }

    //########################################

    protected function getTemplatesDataFromSource($ownerObject, $isDifferentMarketplace = false)
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
            $templatesNicks[] = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN_POLICY;
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

    protected function getSynchronizationTemplateDataFromSource($ownerObject)
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
                $sourceData['list_mode'] = 0;
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

    protected function getTemplatesDataFromPost()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return array();
        }

        $templatesNicks = array(
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN_POLICY,
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

            $templateData = Mage::helper('M2ePro')->jsonDecode(base64_decode($post["template_{$nick}"]));

            $templateId = $templateData['id'];
            $templateMode = $templateData['mode'];

            $idColumn = $manager->getIdColumnNameByMode($templateMode);
            $modeColumn = $manager->getModeColumnName();

            if ($idColumn !== null) {
                $data[$idColumn] = (int)$templateId;
            }

            $data[$modeColumn] = $templateMode;
        }

        // ---------------------------------------

        return $data;
    }

    #############################################

    protected function setAutoActionData(
        Ess_M2ePro_Model_Listing $targetListing,
        Ess_M2ePro_Model_Listing $sourceListing,
        $isDifferentMarketplace = false
    ) {
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
                $listingData['auto_global_adding_mode'] = Ess_M2ePro_Model_Listing::ADDING_MODE_NONE;
                $listingData['auto_global_adding_template_category_id']       = null;
                $listingData['auto_global_adding_template_other_category_id'] = null;
            }

            if ($sourceEbayListing->isAutoWebsiteAddingModeAddAndAssignCategory()) {
                $listingData['auto_website_adding_mode'] = Ess_M2ePro_Model_Listing::ADDING_MODE_NONE;
                $listingData['auto_website_adding_template_category_id']       = null;
                $listingData['auto_website_adding_template_other_category_id'] = null;
            }
        }

        $targetListing->addData($listingData)->save();

        if ($sourceListing->isAutoModeCategory()) {
            $this->setAutoCategoryData($targetListing->getId(), $sourceListing->getId(), $isDifferentMarketplace);
        }
    }

    protected function setAutoCategoryData($targetListingId, $sourceListingId, $isDifferentMarketplace = false)
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
                $group->setData('adding_mode', Ess_M2ePro_Model_Listing::ADDING_MODE_NONE);
                $group->setData('adding_template_category_id', null);
                $group->setData('adding_template_other_category_id', null);
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
    protected function getSourceListingFromRequest()
    {
        if (!$listingId = $this->getRequest()->getParam('listing_id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        return Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', (int)$listingId);
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
