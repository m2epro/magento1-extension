<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Repricing
{
    const COMMAND_ACCOUNT_LINK      = 'account/link';
    const COMMAND_ACCOUNT_UNLINK    = 'account/unlink';
    const COMMAND_SYNCHRONIZE       = 'synchronize';
    const COMMAND_GOTO_SERVICE      = 'goto_service';
    const COMMAND_OFFERS_ADD        = 'offers/add';
    const COMMAND_OFFERS_DETAILS    = 'offers/details';
    const COMMAND_OFFERS_EDIT       = 'offers/edit';
    const COMMAND_OFFERS_REMOVE     = 'offers/remove';
    const COMMAND_DATA_SET_REQUEST  = 'data/setRequest';
    const COMMAND_DATA_GET_RESPONSE = 'data/getResponse';

    const TIMEOUT = 300;

    private $account;

    //########################################

    public function __construct(Ess_M2ePro_Model_Account $account)
    {
        if (!$account->isComponentModeAmazon()) {
            throw new Ess_M2ePro_Model_Exception_Logic('Required Amazon Account.');
        }

        $this->account = $account;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Account
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    private function getAmazonAccount()
    {
        return $this->account->getChildObject();
    }

    //########################################

    public function getLinkUrl()
    {
        $backUrl = Mage::helper('adminhtml')->getUrl('*/adminhtml_common_amazon_account_repricing/link');

        // -----------1----------------------------
        $defaultStoreId = Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();

        $userId = Mage::getSingleton('admin/session')->getUser()->getId();
        $userInfo = Mage::getModel('admin/user')->load($userId)->getData();

        $tempPath = defined('Mage_Shipping_Model_Config::XML_PATH_ORIGIN_CITY')
            ? Mage_Shipping_Model_Config::XML_PATH_ORIGIN_CITY : 'shipping/origin/city';
        $userInfo['city'] = Mage::getStoreConfig($tempPath, $defaultStoreId);

        $tempPath = defined('Mage_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE')
            ? Mage_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE : 'shipping/origin/postcode';
        $userInfo['postal_code'] = Mage::getStoreConfig($tempPath, $defaultStoreId);

        $userInfo['country'] = Mage::getStoreConfig('general/country/default', $defaultStoreId);

        $requiredKeys = array(
            'email',
            'firstname',
            'lastname',
            'country',
            'city',
            'postal_code',
        );

        foreach ($userInfo as $key => $value) {
            if (!in_array($key, $requiredKeys)) {
                unset($userInfo[$key]);
            }
        }
        // ---------------------------------------

        $requestToken = $this->sendData(self::COMMAND_ACCOUNT_LINK, array(
            'request' => array(
                'back_url' => array(
                    'url' => $backUrl,
                    'params' => array(
                        'id' => $this->account->getId()
                    )
                )
            ),
            'data' => array(
                'account' => array(
                    'merchant_id' => $this->getAmazonAccount()->getMerchantId(),
                    'marketplace_code' => $this->getAmazonAccount()->getMarketplace()->getCode(),
                    'additional_data' => $userInfo
                )
            )
        ));

        return $this->getBaseUrl() .
            self::COMMAND_ACCOUNT_LINK .
            '?' . http_build_query(array('request_token' => $requestToken));
    }

    public function getUnLinkUrl()
    {
        $backUrl = Mage::helper('adminhtml')->getUrl('*/adminhtml_common_amazon_account_repricing/unlink');

        $collection = $this->prepareListingProductCollection();

        $collection->getSelect()->where("`l`.`account_id` = ?", $this->account->getId());
        $collection->getSelect()->where('second_table.is_repricing = ?',
            Ess_M2ePro_Model_Amazon_Listing_Product::IS_REPRICING_YES);

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(array(
            'name'  => 'cpev.value',
            'asin'  => 'second_table.general_id',
            'sku'   => 'second_table.sku',
            'price' => 'second_table.online_price'
        ));

        $requestToken = $this->sendData(self::COMMAND_ACCOUNT_UNLINK, array(
            'request' => array(
                'auth' => array(
                    'account_token' => $this->getAmazonAccount()->getRepricingToken()
                ),
                'back_url' => array(
                    'url' => $backUrl,
                    'params' => array(
                        'id' => $this->account->getId()
                    )
                )
            ),
            'data' => array(
                'offers' => $collection->getData()
            )
        ));

        return $this->getBaseUrl() .
            self::COMMAND_ACCOUNT_UNLINK .
            '?' . http_build_query(array('request_token' => $requestToken));
    }

    //----------------------------------------

    public function getManagementUrl()
    {
        return $this->getBaseUrl() . self::COMMAND_GOTO_SERVICE . '?' . http_build_query(array(
            'account_token' => $this->getAmazonAccount()->getRepricingToken()
        ));
    }

    //----------------------------------------

    public function getAddProductsUrl($listingId, $productsIds)
    {
        $backUrl = Mage::helper('adminhtml')->getUrl('*/adminhtml_common_amazon_listing_repricing/addProducts');

        $collection = $this->prepareListingProductCollection();

        $collection->getSelect()->where('main_table.id IN (?)', $productsIds);
        $collection->getSelect()->where('second_table.is_repricing = ?',
            Ess_M2ePro_Model_Amazon_Listing_Product::IS_REPRICING_NO);

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            array(
                'name'  => 'cpev.value',
                'asin'  => 'second_table.general_id',
                'sku'   => 'second_table.sku',
                'price' => 'second_table.online_price'
            )
        );

        $productsData = $collection->getData();

        if (empty($productsData)) {
            return false;
        }

        $requestToken = $this->sendData(self::COMMAND_OFFERS_ADD, array(
            'request' => array(
                'auth' => array(
                    'account_token' => $this->getAmazonAccount()->getRepricingToken()
                ),
                'back_url' => array(
                    'url' => $backUrl,
                    'params' => array(
                        'id' => $listingId,
                        'account_id' => $this->account->getId()
                    )
                )
            ),
            'data' => array(
                'offers' => $productsData
            )
        ));

        return $this->getBaseUrl() .
            self::COMMAND_OFFERS_ADD .
            '?' . http_build_query(array('request_token' => $requestToken));
    }

    public function getShowDetailsUrl($listingId, $productsIds)
    {
        $backUrl = Mage::helper('adminhtml')->getUrl('*/adminhtml_common_amazon_listing_repricing/showDetails');

        $collection = $this->prepareListingProductCollection();

        $collection->getSelect()->where('main_table.id IN (?)', $productsIds);
        $collection->getSelect()->where('second_table.is_repricing = ?',
            Ess_M2ePro_Model_Amazon_Listing_Product::IS_REPRICING_YES);

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            array(
                'name'  => 'cpev.value',
                'asin'  => 'second_table.general_id',
                'sku'   => 'second_table.sku',
                'price' => 'second_table.online_price'
            )
        );

        $productsData = $collection->getData();

        if (empty($productsData)) {
            return false;
        }

        $requestToken = $this->sendData(self::COMMAND_OFFERS_DETAILS, array(
            'request' => array(
                'auth' => array(
                    'account_token' => $this->getAmazonAccount()->getRepricingToken()
                ),
                'back_url' => array(
                    'url' => $backUrl,
                    'params' => array(
                        'id' => $listingId,
                        'account_id' => $this->account->getId()
                    )
                )
            ),
            'data' => array(
                'offers' => $productsData
            )
        ));

        return $this->getBaseUrl() .
            self::COMMAND_OFFERS_DETAILS .
            '?' . http_build_query(array('request_token' => $requestToken));
    }

    public function getEditProductsUrl($listingId, $productsIds)
    {
        $backUrl = Mage::helper('adminhtml')->getUrl('*/adminhtml_common_amazon_listing_repricing/editProducts');

        $collection = $this->prepareListingProductCollection();

        $collection->getSelect()->where('main_table.id IN (?)', $productsIds);
        $collection->getSelect()->where('second_table.is_repricing = ?',
            Ess_M2ePro_Model_Amazon_Listing_Product::IS_REPRICING_YES);

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            array(
                'name'  => 'cpev.value',
                'asin'  => 'second_table.general_id',
                'sku'   => 'second_table.sku',
                'price' => 'second_table.online_price'
            )
        );

        $productsData = $collection->getData();

        if (empty($productsData)) {
            return false;
        }

        $requestToken = $this->sendData(self::COMMAND_OFFERS_EDIT, array(
            'request' => array(
                'auth' => array(
                    'account_token' => $this->getAmazonAccount()->getRepricingToken()
                ),
                'back_url' => array(
                    'url' => $backUrl,
                    'params' => array(
                        'id' => $listingId,
                        'account_id' => $this->account->getId()
                    )
                )
            ),
            'data' => array(
                'offers' => $productsData
            )
        ));

        return $this->getBaseUrl() .
            self::COMMAND_OFFERS_EDIT .
            '?' . http_build_query(array('request_token' => $requestToken));
    }

    public function getRemoveProductsUrl($listingId, $productsIds)
    {
        $backUrl = Mage::helper('adminhtml')->getUrl('*/adminhtml_common_amazon_listing_repricing/removeProducts');

        $collection = $this->prepareListingProductCollection();

        $collection->getSelect()->where('main_table.id IN (?)', $productsIds);
        $collection->getSelect()->where('second_table.is_repricing = ?',
            Ess_M2ePro_Model_Amazon_Listing_Product::IS_REPRICING_YES);

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            array(
                'name'  => 'cpev.value',
                'asin'  => 'second_table.general_id',
                'sku'   => 'second_table.sku',
                'price' => 'second_table.online_price'
            )
        );

        $productsData = $collection->getData();

        if (empty($productsData)) {
            return false;
        }

        $requestToken = $this->sendData(self::COMMAND_OFFERS_REMOVE, array(
            'request' => array(
                'auth' => array(
                    'account_token' => $this->getAmazonAccount()->getRepricingToken()
                ),
                'back_url' => array(
                    'url' => $backUrl,
                    'params' => array(
                        'id' => $listingId,
                        'account_id' => $this->account->getId()
                    )
                )
            ),
            'data' => array(
                'offers' => $productsData
            )
        ));

        return $this->getBaseUrl() .
            self::COMMAND_OFFERS_REMOVE .
            '?' . http_build_query(array('request_token' => $requestToken));
    }

    //########################################

    public function synchronize()
    {
        $result = $this->sendRequest(
            $this->getBaseUrl() . 'synchronize',
            array(
                'account_token' => $this->getAmazonAccount()->getRepricingToken()
            )
        );

        if (empty($result['response'])) {
            return array(
                array(
                    'type' => 'error',
                    'text' => Mage::helper('M2ePro')->__('Synchronization with Amazon Repricing Tool is failed.')
                )
            );
        }

        $response = json_decode($result['response'], true);

        if ($response['status'] == '0') {
            return $response['messages'];
        }

        if (empty($response['offers'])) {

            $this->account->setSetting('repricing', array('info', 'total_products'), 0);
            $this->account->save();

            return array(
                array(
                    'type' => 'notice',
                    'text' => Mage::helper('M2ePro')->__(
                        'There are no Amazon Products which are managed by Amazon Repricing Tool.'
                    )
                )
            );
        }

        $skus = array();
        foreach ($response['offers'] as $offer) {
            $skus[] = $offer['sku'];
        }

        $this->resetProductRepricingStatus();

        $this->setProductRepricingStatusBySku(
            $skus,
            Ess_M2ePro_Model_Amazon_Listing_Product::IS_REPRICING_YES
        );

        $this->account->setSetting('repricing', array('info', 'total_products'), count($skus));
        $this->account->save();

        return true;
    }

    //########################################

    private function getBaseUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/amazon/repricing/', 'base_url');
    }

    // ---------------------------------------

    public function getResponseData($responseToken)
    {
        $result = $this->sendRequest(
            $this->getBaseUrl() . self::COMMAND_DATA_GET_RESPONSE,
            array(
                'response_token' => $responseToken
            )
        );

        return json_decode($result['response'], true);
    }

    // ---------------------------------------

    private function sendData($command, $data)
    {
        if (!empty($data['data'])) {
            $data['data'] = json_encode($data['data']);
        }

        $result = $this->sendRequest(
            $this->getBaseUrl() . $command,
            $data
        );

        $response = json_decode($result['response'], true);

        if (!empty($response['request_token'])) {
            return $response['request_token'];
        }

        return false;
    }

    private function sendRequest($url, array $postData)
    {
        $curlObject = curl_init();

        //set the url
        curl_setopt($curlObject, CURLOPT_URL, $url);

        // stop CURL from verifying the peer's certificate
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYHOST, false);

        // set the data body of the request
        curl_setopt($curlObject, CURLOPT_POST, true);
        curl_setopt($curlObject, CURLOPT_POSTFIELDS, http_build_query($postData,'','&'));

        // set it to return the transfer as a string from curl_exec
        curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlObject, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($curlObject, CURLOPT_TIMEOUT, self::TIMEOUT);

        $response = curl_exec($curlObject);

        $curlInfo    = curl_getinfo($curlObject);
        $errorNumber = curl_errno($curlObject);

        curl_close($curlObject);

        if ($response === false) {

            throw new Ess_M2ePro_Model_Exception_Connection(
                'The Action was not completed because connection with M2E Pro Server was not set.
                 There are several possible reasons: temporary connection problem – please wait and try again later;
                 block of outgoing connection by firewall',
                array('curl_error_number' => $errorNumber,
                      'curl_info' => $curlInfo)
            );
        }

        return array(
            'curl_error_number' => $errorNumber,
            'curl_info'         => $curlInfo,
            'response'          => $response
        );
    }

    //########################################

    public function getRepricingListingProductsData()
    {
        $collection = $this->prepareListingProductCollection();

        $collection->getSelect()->where("`l`.`account_id` = ?", $this->account->getId());
        $collection->getSelect()->where('second_table.is_repricing = ?',
            Ess_M2ePro_Model_Amazon_Listing_Product::IS_REPRICING_YES);

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(array(
            'sku' => 'second_table.sku'
        ));

        return $collection->getData();
    }

    //----------------------------------------

    public function setProductRepricingStatusBySku($skus, $status)
    {
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableAmazonListingProduct = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_listing_product');
        $tableAmazonListingOther = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_listing_other');

        $connWrite->update($tableAmazonListingProduct, array(
                'is_repricing' => $status
            ), '`sku` IN (\''.implode('\',\'', $skus).'\')'
        );

        $connWrite->update($tableAmazonListingOther, array(
                'is_repricing' => $status
            ), '`sku` IN (\''.implode('\',\'', $skus).'\')'
        );
    }

    public function resetProductRepricingStatus()
    {
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $tableAmazonListingProduct = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_listing_product');
        $tableAmazonListingOther = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_listing_other');

        /** @var Ess_M2ePro_Model_Mysql4_Amazon_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');

        $collection->getSelect()
            ->join(array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
                '(`l`.`id` = `main_table`.`listing_id`)', array());

        $collection->getSelect()->where(
            "`second_table`.`is_repricing` = ?",
            Ess_M2ePro_Model_Amazon_Listing_Product::IS_REPRICING_YES
        );
        $collection->getSelect()->where("`l`.`account_id` = ?", $this->account->getId());

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(array(
            'id' => 'main_table.id'
        ));

        $productsIds = $collection->getColumnValues('id');

        if (!empty($productsIds)) {
            $connWrite->update($tableAmazonListingProduct, array(
                    'is_repricing' => Ess_M2ePro_Model_Amazon_Listing_Product::IS_REPRICING_NO
                ), '`listing_product_id` IN ('.implode(',', $productsIds).')'
            );
        }

        /** @var Ess_M2ePro_Model_Mysql4_Amazon_Listing_Other_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');

        $collection->getSelect()->where("`main_table`.`account_id` = ?", $this->account->getId());
        $collection->getSelect()->where(
            "`second_table`.`is_repricing` = ?",
            Ess_M2ePro_Model_Amazon_Listing_Product::IS_REPRICING_YES
        );

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(array(
            'id' => 'main_table.id'
        ));

        $productsIds = $collection->getColumnValues('id');

        if (!empty($productsIds)) {
            $connWrite->update($tableAmazonListingOther, array(
                    'is_repricing' => Ess_M2ePro_Model_Amazon_Listing_Product::IS_REPRICING_NO
                ), '`listing_other_id` IN ('.implode(',', $productsIds).')'
            );
        }
    }

    //----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Mysql4_Amazon_Listing_Product_Collection
     */
    private function prepareListingProductCollection()
    {
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->getSelect()
            ->join(array('l'=>Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
                '(`l`.`id` = `main_table`.`listing_id`)',
                array('listing_title'=>'title','store_id','marketplace_id'));

        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
            ->from(Mage::getSingleton('core/resource')
                    ->getTableName('catalog_product_entity_varchar'),
                new Zend_Db_Expr('MAX(`store_id`)'))
            ->where("`entity_id` = `main_table`.`product_id`")
            ->where("`attribute_id` = `ea`.`attribute_id`")
            ->where("`store_id` = 0 OR `store_id` = `l`.`store_id`");

        $collection->getSelect()
            ->join(array('cpev'=>Mage::getSingleton('core/resource')
                    ->getTableName('catalog_product_entity_varchar')),
                "(`cpev`.`entity_id` = `main_table`.product_id)",
                array('value'))
            ->join(array('ea'=>Mage::getSingleton('core/resource')->getTableName('eav_attribute')),
                '(`cpev`.`attribute_id` = `ea`.`attribute_id` AND `ea`.`attribute_code` = \'name\')',
                array())
            ->where('`cpev`.`store_id` = ('.$dbSelect->__toString().')');

        $collection->getSelect()->where('second_table.is_variation_parent = 0');
        $collection->getSelect()->where('second_table.sku IS NOT NULL');

        return $collection;
    }

    //########################################
}
