<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Ebay_Item_MultipleAbstract
    extends Ess_M2ePro_Model_Connector_Ebay_Item_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Listing_Product[]
     */
    protected $listingsProducts = array();

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request[]
     */
    protected $requestsObjects = array();

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response[]
     */
    protected $responsesObjects = array();

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData[]
     */
    protected $requestsDataObjects = array();

    //########################################

    /**
     * @param array $params
     * @param Ess_M2ePro_Model_Listing_Product[] $listingsProducts
     * @throws Ess_M2ePro_Model_Exception
     */
    public function __construct(array $params = array(), array $listingsProducts)
    {
        if (empty($listingsProducts)) {
            throw new Ess_M2ePro_Model_Exception('Multiple Item Connector has received empty array');
        }

        /** @var Ess_M2ePro_Model_Account $account */
        $account = reset($listingsProducts)->getAccount();
        /** @var Ess_M2ePro_Model_Marketplace $marketplace */
        $marketplace = reset($listingsProducts)->getMarketplace();

        $listingProductIds   = array();
        $actionConfigurators = array();

        foreach ($listingsProducts as $listingProduct) {

            if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
                throw new Ess_M2ePro_Model_Exception('Multiple Item Connector has received invalid Product data type');
            }

            if ($account->getId() != $listingProduct->getListing()->getAccountId()) {
                throw new Ess_M2ePro_Model_Exception('Multiple Item Connector has received Products from
                    different Accounts');
            }

            if ($marketplace->getId() != $listingProduct->getListing()->getMarketplaceId()) {
                throw new Ess_M2ePro_Model_Exception('Multiple Item Connector has received Products from
                    different Marketplaces');
            }

            $listingProductIds[] = $listingProduct->getId();

            if (!is_null($listingProduct->getActionConfigurator())) {
                $actionConfigurators[$listingProduct->getId()] = $listingProduct->getActionConfigurator();
            } else {
                $actionConfigurators[$listingProduct->getId()] = Mage::getModel(
                    'M2ePro/Ebay_Listing_Product_Action_Configurator'
                );
            }
        }

        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('id', array('in' => array_unique($listingProductIds)));

        /** @var Ess_M2ePro_Model_Listing_Product[] $actualListingsProducts */
        $actualListingsProducts = $listingProductCollection->getItems();

        if (empty($actualListingsProducts)) {
            throw new Ess_M2ePro_Model_Exception('All products were removed before connector processing');
        }

        foreach ($actualListingsProducts as $actualListingProduct) {
            $actualListingProduct->setActionConfigurator($actionConfigurators[$actualListingProduct->getId()]);
            $this->listingsProducts[$actualListingProduct->getId()] = $actualListingProduct;
        }

        parent::__construct($params,$marketplace,$account);
    }

    //########################################

    public function process()
    {
        $result = parent::process();

        foreach ($this->messages as $message) {

            $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM;

            if ($message[parent::MESSAGE_TYPE_KEY] == parent::MESSAGE_TYPE_ERROR) {
                $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH;
            }

            foreach ($this->listingsProducts as $product) {
                $this->getLogger()->logListingProductMessage($product, $message, $priority);
            }
        }

        if (!isset($result['result'])) {
            return $result;
        }

        foreach ($result['result'] as $listingProductId => $listingsProductResult) {

            if (!isset($listingsProductResult['messages'])) {
                continue;
            }

            $listingProduct = $this->getListingProduct($listingProductId);

            foreach ($listingsProductResult['messages'] as $message) {

                $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM;

                if ($message[parent::MESSAGE_TYPE_KEY] == parent::MESSAGE_TYPE_ERROR) {
                    $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH;
                }

                $this->getLogger()->logListingProductMessage($listingProduct, $message, $priority);
            }
        }

        return $result;
    }

    // ---------------------------------------

    protected function eventAfterProcess()
    {
        $this->unlockListingsProducts();
    }

    //########################################

    protected function isNeedSendRequest()
    {
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $lockItem = Mage::getModel('M2ePro/LockItem');
            $lockItem->setNick(Ess_M2ePro_Helper_Component_Ebay::NICK . '_listing_product_' . $listingProduct->getId());

            if (!$lockItem->isExist()) {
                continue;
            }

            $message = array(
                // M2ePro_TRANSLATIONS
                // Another Action is being processed. Try again when the Action is completed.
                parent::MESSAGE_TEXT_KEY => 'Another Action is being processed. '
                    . 'Try again when the Action is completed.',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

            $this->getLogger()->logListingProductMessage(
                $listingProduct, $message, Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            unset($this->listingsProducts[$listingProduct->getId()]);
        }

        $this->lockListingsProducts();
        $this->filterManualListingsProducts();

        return !empty($this->listingsProducts);
    }

    protected function getRequestTimeout()
    {
        $imagesTimeout = 0;

        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $requestDataObject = $this->getRequestDataObject($listingProduct);
            $requestData = $requestDataObject->getData();

            if ($requestData['is_eps_ebay_images_mode'] === false ||
                (is_null($requestData['is_eps_ebay_images_mode']) &&
                    $requestData['upload_images_mode'] ==
                        Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Description::UPLOAD_IMAGES_MODE_SELF)) {
                continue;
            }

            $imagesTimeout += self::TIMEOUT_INCREMENT_FOR_ONE_IMAGE * $requestDataObject->getTotalImagesCount();
        }

        return parent::getRequestTimeout() + $imagesTimeout;
    }

    // ---------------------------------------

    protected function lockListingsProducts()
    {
        foreach ($this->listingsProducts as $listingProduct) {
            $lockItem = Mage::getModel('M2ePro/LockItem');
            $lockItem->setNick(Ess_M2ePro_Helper_Component_Ebay::NICK . '_listing_product_' . $listingProduct->getId());

            $lockItem->create();
            $lockItem->makeShutdownFunction();
        }
    }

    protected function unlockListingsProducts()
    {
        foreach ($this->listingsProducts as $listingProduct) {
            $lockItem = Mage::getModel('M2ePro/LockItem');
            $lockItem->setNick(Ess_M2ePro_Helper_Component_Ebay::NICK . '_listing_product_' . $listingProduct->getId());
            $lockItem->remove();
        }
    }

    // ---------------------------------------

    abstract protected function filterManualListingsProducts();

    protected function removeAndUnlockListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(Ess_M2ePro_Helper_Component_Ebay::NICK.'_listing_product_'.$listingProduct->getId());
        $lockItem->remove();

        unset($this->listingsProducts[$listingProduct->getId()]);
    }

    //########################################

    protected function logRequestMessages(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        foreach ($this->getRequestObject($listingProduct)->getWarningMessages() as $message) {

            $message = array(
                parent::MESSAGE_TEXT_KEY => $message,
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_WARNING
            );

            $this->getLogger()->logListingProductMessage(
                $listingProduct, $message, Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );
        }
    }

    // ---------------------------------------

    /**
     * @param $id
     * @return Ess_M2ePro_Model_Listing_Product
     * @throws Exception
     */
    protected function getListingProduct($id)
    {
        if (!isset($this->listingsProducts[$id])) {
            throw new Ess_M2ePro_Model_Exception('Listing Product was not found');
        }

        return $this->listingsProducts[$id];
    }

    /**
     * @param array $result
     * @return bool
     */
    protected function isResultSuccess(array $result)
    {
        $messages = isset($result['messages']) ? $result['messages'] : array();

        foreach ($messages as $message) {
            if ($message[parent::MESSAGE_TYPE_KEY] == parent::MESSAGE_TYPE_ERROR) {
                return false;
            }
        }

        return true;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
     */
    protected function getRequestObject(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!isset($this->requestsObjects[$listingProduct->getId()])) {
            $this->requestsObjects[$listingProduct->getId()] = $this->makeRequestObject($listingProduct);
        }
        return $this->requestsObjects[$listingProduct->getId()];
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response
     */
    protected function getResponseObject(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!isset($this->responsesObjects[$listingProduct->getId()])) {
            $this->responsesObjects[$listingProduct->getId()] = $this->makeResponseObject(
                $listingProduct, $this->getRequestDataObject($listingProduct)
            );
        }
        return $this->responsesObjects[$listingProduct->getId()];
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @param array $data
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected function buildRequestDataObject(Ess_M2ePro_Model_Listing_Product $listingProduct, array $data)
    {
        if (!isset($this->requestsDataObjects[$listingProduct->getId()])) {
            $this->requestsDataObjects[$listingProduct->getId()] = parent::makeRequestDataObject($listingProduct,$data);
        }
        return $this->requestsDataObjects[$listingProduct->getId()];
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected function getRequestDataObject(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        return $this->requestsDataObjects[$listingProduct->getId()];
    }

    //########################################
}