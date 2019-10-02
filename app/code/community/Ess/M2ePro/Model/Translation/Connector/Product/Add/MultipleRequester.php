<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Translation_Connector_Product_Add_MultipleRequester
    extends Ess_M2ePro_Model_Translation_Connector_Command_Pending_Requester
{
    // ########################################

    /**
     * @var Ess_M2ePro_Model_Marketplace|null
     */
    protected $marketplace = NULL;

    protected $logsActionId = NULL;
    protected $neededRemoveLocks = array();

    protected $status = Ess_M2ePro_Helper_Data::STATUS_SUCCESS;

    protected $listingsProducts = array();
    protected $listingProductRequestsData = array();

    const MAX_LIFE_TIME_INTERVAL = 864000; // 10 days

    // ########################################

    public function __construct(array $params = array())
    {
        $defaultParams = array(
            'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN
        );
        $params = array_merge($defaultParams, $params);

        if (isset($params['logs_action_id'])) {
            $this->logsActionId = (int)$params['logs_action_id'];
            unset($params['logs_action_id']);
        } else {
            $this->logsActionId = Mage::getModel('M2ePro/Listing_Log')->getResource()->getNextActionId();
        }

        parent::__construct($params);
    }

    public function setListingsProducts(array $listingsProducts)
    {
        if (empty($listingsProducts)) {
            throw new Ess_M2ePro_Model_Exception('Product Connector has received empty array');
        }

        foreach ($listingsProducts as $listingProduct) {
            if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
                throw new Ess_M2ePro_Model_Exception('Product Connector has received invalid Product data type');
            }
        }

        $translationData = $listingsProducts[0]->getSetting(
            'additional_data', array('translation_service'), array()
        );
        $tempSourceLanguage = $translationData['from']['language'];
        $tempTargetLanguage = $translationData['to']['language'];
        $tempService = $listingsProducts[0]->getTranslationService();

        $tempListing = $listingsProducts[0]->getListing();
        foreach ($listingsProducts as $listingProduct) {
            if ($tempListing->getId() != $listingProduct->getListing()->getId()) {
                throw new Ess_M2ePro_Model_Exception('Product Connector has received Products from different Listings');
            }

            $translationData = $listingProduct->getSetting('additional_data', array('translation_service'), array());

            if ($tempSourceLanguage != $translationData['from']['language']) {
                throw new Ess_M2ePro_Model_Exception(
                    'Product Connector has received Products from different
                    source languages'
                );
            }

            if ($tempTargetLanguage != $translationData['to']['language']) {
                throw new Ess_M2ePro_Model_Exception(
                    'Product Connector has received Products from different
                    target languages'
                );
            }

            if ($tempService != $listingProduct->getTranslationService()) {
                throw new Ess_M2ePro_Model_Exception(
                    'Product Connector has received Products from different
                    Translation Services'
                );
            }
        }

        $this->_account    = $listingsProducts[0]->getListing()->getAccount();
        $this->marketplace = $listingsProducts[0]->getListing()->getMarketplace();

        $listingsProducts = $this->filterLockedListingsProducts($listingsProducts);
        $listingsProducts = $this->prepareListingsProducts($listingsProducts);

        $this->listingsProducts = array_values($listingsProducts);
    }

    // ########################################

    public function __destruct()
    {
        $this->checkUnlockListings();
    }

    // ########################################

    public function getCommand()
    {
        return array('product','add','entities');
    }

    // ########################################

    public function getStatus()
    {
        return $this->status;
    }

    protected function setStatus($status)
    {
        if (!in_array(
            $status, array(
            Ess_M2ePro_Helper_Data::STATUS_ERROR,
            Ess_M2ePro_Helper_Data::STATUS_WARNING,
            Ess_M2ePro_Helper_Data::STATUS_SUCCESS)
        )) {
            return;
        }

        if ($status == Ess_M2ePro_Helper_Data::STATUS_ERROR) {
            $this->status = Ess_M2ePro_Helper_Data::STATUS_ERROR;
            return;
        }

        if ($this->status == Ess_M2ePro_Helper_Data::STATUS_ERROR) {
            return;
        }

        if ($status == Ess_M2ePro_Helper_Data::STATUS_WARNING) {
            $this->status = Ess_M2ePro_Helper_Data::STATUS_WARNING;
            return;
        }

        if ($this->status == Ess_M2ePro_Helper_Data::STATUS_WARNING) {
            return;
        }

        $this->status = Ess_M2ePro_Helper_Data::STATUS_SUCCESS;
    }

    // ########################################

    public function getRequestData()
    {
         $requestData = array(
             'service'      => $this->_params['service'],
             'source_language' => $this->_params['source_language'],
             'target_language' => $this->_params['target_language'],
             'products' => array()
        );

        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $tempData = $listingProduct->getSetting('additional_data', array('translation_service', 'from'), array());

            $listingProductRequestData = array(
                'title'          => $tempData['description']['title'],
                'subtitle'       => $tempData['description']['subtitle'],
                'description'    => $tempData['description']['description'],
                'sku'            => $tempData['sku'],
                'item_specifics' => $tempData['item_specifics'],
                'category'       => $tempData['category']
            );

            $this->listingProductRequestsData[$listingProduct->getId()] = $listingProductRequestData;
            $requestData['products'][] = $listingProductRequestData;
        }

        return $requestData;
    }

    // ########################################

    public function process()
    {
        $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_SUCCESS);

        if (empty($this->listingsProducts)) {
            return;
        }

        $this->updateOrLockListingProducts();
        parent::process();

        // When all items are failed in response

        $responseData = $this->getResponse()->getData();

        (isset($responseData['data']['messages'])) && $tempMessages = $responseData['data']['messages'];
        if (isset($tempMessages) && is_array($tempMessages) && !empty($tempMessages)) {
            $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_ERROR);
        }

        $this->checkUnlockListings();
    }

    // ########################################

    protected function getProcessingParams()
    {
        $listingProductIds = array();
        foreach ($this->listingsProducts as $listingProduct) {
            $listingProductIds[] = $listingProduct->getId();
        }

        return array_merge(
            parent::getProcessingParams(),
            array(
                'listing_product_ids' => array_unique($listingProductIds),
            )
        );
    }

    protected function getResponserParams()
    {
        $tempProductsData = array();

        foreach ($this->listingsProducts as $listingProduct) {
            $tempProductsData[$listingProduct->getId()] =
                isset($this->listingProductRequestsData[$listingProduct->getId()])
                    ? $this->listingProductRequestsData[$listingProduct->getId()]
                    : array();
        }

        return array(
            'account_id'     => $this->_account->getId(),
            'marketplace_id' => $this->marketplace->getId(),
            'logs_action_id' => $this->logsActionId,
            'status_changer' => $this->_params['status_changer'],
            'params'         => $this->_params,
            'products'       => $tempProductsData
        );
    }

    // ########################################

    protected function updateOrLockListingProducts()
    {
        foreach ($this->listingsProducts as $product) {

            /** @var $product Ess_M2ePro_Model_Listing_Product */

            $lockItemManager = Mage::getModel(
                'M2ePro/Lock_Item_Manager',
                array('nick' => Ess_M2ePro_Helper_Component_Ebay::NICK.'_listing_product_'.$product->getId())
            );

            if (!$lockItemManager->isExist() ||
                $lockItemManager->isInactiveMoreThanSeconds(
                    Ess_M2ePro_Model_Lock_Item_Manager::DEFAULT_MAX_INACTIVE_TIME
                )
            ) {
                $lockItemManager->create();
                $this->neededRemoveLocks[$product->getId()] = $lockItemManager;
            }

            $lockItemManager->activate();
        }
    }

    protected function checkUnlockListings()
    {
        foreach ($this->neededRemoveLocks as $lockItemManager) {
            $lockItemManager->isExist() && $lockItemManager->remove();
        }

        $this->neededRemoveLocks = array();
    }

    // ########################################

    protected function addListingsProductsLogsMessage(
        Ess_M2ePro_Model_Listing_Product $listingProduct,
        $text,
        $type = Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
        $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
    ) {
        $action = Ess_M2ePro_Model_Listing_Log::ACTION_TRANSLATE_PRODUCT;

        $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;
        if ($this->_params['status_changer'] == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN) {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;
        } else if ($this->_params['status_changer'] == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_USER;
        } else {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION;
        }

        switch ($type) {
            case Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR:
                $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_ERROR);
                break;
            case Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING:
                $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_WARNING);
                break;
            case Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS:
            case Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE:
                $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_SUCCESS);
                break;
            default:
                $this->setStatus(Ess_M2ePro_Helper_Data::STATUS_ERROR);
                break;
        }

        $logModel = Mage::getModel('M2ePro/Listing_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

        $logModel->addProductMessage(
            $listingProduct->getListingId(),
            $listingProduct->getProductId(),
            $listingProduct->getId(),
            $initiator,
            $this->logsActionId,
            $action, $text, $type, $priority
        );
    }

    // ########################################

    protected function filterLockedListingsProducts($listingsProducts)
    {
        foreach ($listingsProducts as $key => $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if ($listingProduct->isSetProcessingLock(NULL) ||
                $listingProduct->isSetProcessingLock('in_action') ||
                $listingProduct->isSetProcessingLock('translation_action')) {
                // M2ePro_TRANSLATIONS
                // Another Action is being processed. Try again when the Action is completed.
                $this->addListingsProductsLogsMessage(
                    $listingProduct, 'Another Action is being processed. Try again when the Action is completed.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                unset($listingsProducts[$key]);
                continue;
            }
        }

        return $listingsProducts;
    }

    protected function prepareListingsProducts($listingProducts)
    {
        foreach ($listingProducts as $key => $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if (!$listingProduct->getChildObject()->isTranslatable()) {
                // M2ePro_TRANSLATIONS
                // 'Product is Translated or being Translated'
                $this->addListingsProductsLogsMessage(
                    $listingProduct, 'Product is Translated or being Translated',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );
                unset($listingProducts[$key]);
                continue;
            }

            $listingProduct->getChildObject()->setData(
                'translation_status',
                Ess_M2ePro_Model_Ebay_Listing_Product::TRANSLATION_STATUS_IN_PROGRESS
            )->save();
        }

        return array_values($listingProducts);
    }

    // ########################################
}
