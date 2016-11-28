<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Repricing_Action_Product extends Ess_M2ePro_Model_Amazon_Repricing_Abstract
{
    //########################################

    public function sendAddProductsActionData(array $listingsProductsIds, $backUrl)
    {
        return $this->sendData(
            Ess_M2ePro_Helper_Component_Amazon_Repricing::COMMAND_OFFERS_ADD,
            $this->getOffersData($listingsProductsIds, false),
            $backUrl
        );
    }

    public function sendShowProductsDetailsActionData(array $listingsProductsIds, $backUrl)
    {
        return $this->sendData(
            Ess_M2ePro_Helper_Component_Amazon_Repricing::COMMAND_OFFERS_DETAILS,
            $this->getOffersData($listingsProductsIds, true),
            $backUrl
        );
    }

    public function sendEditProductsActionData(array $listingsProductsIds, $backUrl)
    {
        return $this->sendData(
            Ess_M2ePro_Helper_Component_Amazon_Repricing::COMMAND_OFFERS_EDIT,
            $this->getOffersData($listingsProductsIds, true),
            $backUrl
        );
    }

    public function sendRemoveProductsActionData(array $listingsProductsIds, $backUrl)
    {
        return $this->sendData(
            Ess_M2ePro_Helper_Component_Amazon_Repricing::COMMAND_OFFERS_REMOVE,
            $this->getOffersData($listingsProductsIds, true),
            $backUrl
        );
    }

    //########################################

    public function getActionResponseData($responseToken)
    {
        try {
            $result = $this->getHelper()->sendRequest(
                Ess_M2ePro_Helper_Component_Amazon_Repricing::COMMAND_DATA_GET_RESPONSE,
                array(
                    'response_token' => $responseToken
                )
            );
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return false;
        }

        return json_decode($result['response'], true);
    }

    //########################################

    private function sendData($command, array $offersData, $backUrl)
    {
        if (empty($offersData)) {
            return false;
        }

        try {
            $result = $this->getHelper()->sendRequest(
                $command, array(
                    'request' => array(
                        'auth' => array(
                            'account_token' => $this->getAmazonAccountRepricing()->getToken()
                        ),
                        'back_url' => array(
                            'url'    => $backUrl,
                            'params' => array()
                        )
                    ),
                    'data' => json_encode(array(
                        'offers' => $offersData,
                    ))
                )
            );
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return false;
        }

        $response = json_decode($result['response'], true);

        return !empty($response['request_token']) ? $response['request_token'] : false;
    }

    //########################################

    /**
     * @param array $listingProductIds
     * @param bool $alreadyOnRepricing
     * @return array
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    private function getOffersData(array $listingProductIds, $alreadyOnRepricing = false)
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->getSelect()->joinLeft(
            array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
            'l.id = main_table.listing_id',
            array('store_id')
        );

        $storeIdSelect = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
            ->from(
                Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar'),
                new Zend_Db_Expr('MAX(`store_id`)')
            )
            ->where("`entity_id` = `main_table`.`product_id`")
            ->where("`attribute_id` = `ea`.`attribute_id`")
            ->where("`store_id` = 0 OR `store_id` = `l`.`store_id`");

        $listingProductCollection->getSelect()
            ->join(
                array('cpev' => Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar')),
                "cpev.entity_id = main_table.product_id",
                array('product_title' => 'value')
            )
            ->join(
                array('ea'=>Mage::getSingleton('core/resource')->getTableName('eav_attribute')),
                'cpev.attribute_id = ea.attribute_id AND ea.attribute_code = \'name\'',
                array()
            )
            ->where('cpev.store_id = ('.$storeIdSelect->__toString().')');

        if ($alreadyOnRepricing) {
            $listingProductCollection->addFieldToFilter('second_table.is_repricing', 1);
        } else {
            $listingProductCollection->addFieldToFilter('second_table.is_repricing', 0);
        }

        $listingProductCollection->addFieldToFilter('main_table.id', array('in' => $listingProductIds));
        $listingProductCollection->addFieldToFilter('second_table.is_variation_parent', 0);
        $listingProductCollection->addFieldToFilter('second_table.sku', array('notnull' => true));

        if ($listingProductCollection->getSize() <= 0) {
            return array();
        }

        $repricingCollection = Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Repricing_Collection');
        $repricingCollection->addFieldToFilter(
            'listing_product_id', array('in' => $listingProductCollection->getColumnValues('id'))
        );

        /** @var Ess_M2ePro_Model_Listing_Product[] $listingsProducts */
        $listingsProducts = $listingProductCollection->getItems();

        $offersData = array();

        foreach ($listingsProducts as $listingProduct) {
            $listingProductRepricingObject = $repricingCollection->getItemById($listingProduct->getId());

            if (is_null($listingProductRepricingObject)) {
                $listingProductRepricingObject = Mage::getModel('M2ePro/Amazon_Listing_Product_Repricing');
            }

            $listingProductRepricingObject->setListingProduct($listingProduct);

            $regularPrice = $listingProductRepricingObject->getRegularPrice();
            $minPrice     = $listingProductRepricingObject->getMinPrice();
            $maxPrice     = $listingProductRepricingObject->getMaxPrice();

            $isDisabled   = $listingProductRepricingObject->isDisabled();

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            $offersData[] = array(
                'name'  => $listingProduct->getData('product_title'),
                'asin'  => $amazonListingProduct->getGeneralId(),
                'sku'   => $amazonListingProduct->getSku(),
                'price' => $amazonListingProduct->getOnlinePrice(),
                'regular_product_price'   => $regularPrice,
                'minimal_product_price'   => $minPrice,
                'maximal_product_price'   => $maxPrice,
                'is_calculation_disabled' => $isDisabled,
            );
        }

        return $offersData;
    }

    //########################################
}