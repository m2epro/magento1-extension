<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_ListType
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Validator
{
    protected $_childGeneralIdsForParent = array();

    protected $_cachedData = array();

    //########################################

    /**
     * @param array $generalIds
     * @return $this
     */
    public function setChildGeneralIdsForParent(array $generalIds)
    {
        $this->_childGeneralIdsForParent = $generalIds;
        return $this;
    }

    //########################################

    /**
     * @return bool
     */
    public function validate()
    {
        $generalId = $this->recognizeByListingProductGeneralId();
        if (!empty($generalId)) {
            $this->setGeneralId($generalId);
            $this->setListType(Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Request::LIST_TYPE_EXIST);

            return true;
        }

        if ($this->getVariationManager()->isIndividualType() && !$this->validateComplexMagentoProductTypes()) {
            $this->addMessage(
                'You cannot list this Product because for selling Bundle, Simple
                With Custom Options or Downloadable With Separated Links Magento Products
                on Amazon the ASIN/ISBN has to be found manually.
                Please use Manual Search to find the required ASIN/ISBN and try again.'
            );
            return false;
        }

        $generalId = $this->recognizeBySearchGeneralId();
        if ($generalId === false) {
            return false;
        }

        if ($generalId !== null) {
            if ($this->getVariationManager()->isRelationParentType()) {
                /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Linking $linkingObject */
                $linkingObject = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Type_List_Linking');
                $linkingObject->setListingProduct($this->getListingProduct());
                $linkingObject->setGeneralId($generalId);
                $linkingObject->setSku($this->_data['sku']);
                $linkingObject->setAdditionalData(reset($this->_cachedData['amazon_data'][$generalId]));

                $generalIdType = Mage::helper('M2ePro')->isISBN($generalId) ? 'ISBN' : 'ASIN';

                if ($linkingObject->link()) {
                    $this->addMessage(
                        Mage::helper('M2ePro/Module_Log')->encodeDescription(
                            'Magento Parent Product was linked
                             to Amazon Parent Product by %general_id_type% "%general_id%" via Product Identifiers.',
                            array('!general_id_type' => $generalIdType, '!general_id' => $generalId)
                        ),
                        Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_SUCCESS
                    );
                } else {
                    $this->addMessage(
                        Mage::helper('M2ePro/Module_Log')->encodeDescription(
                            'Unexpected error has occurred while trying to link Magento Parent Product,
                             although the %general_id_type% "%general_id%" was found on Amazon.',
                            array('general_id' => $generalId, 'general_id_type' => $generalIdType)
                        )
                    );
                }

                return false;
            }

            $this->setGeneralId($generalId);
            $this->setListType(Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Request::LIST_TYPE_EXIST);

            return true;
        }

        $generalId = $this->recognizeBySearchWorldwideId();
        if ($generalId === false) {
            return false;
        }

        if ($generalId !== null) {
            $this->setGeneralId($generalId);
            $this->setListType(Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Request::LIST_TYPE_EXIST);

            return true;
        }

        if ($this->validateNewProduct()) {
            $this->setListType(Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Request::LIST_TYPE_NEW);
            return true;
        }

        return false;
    }

    //########################################

    protected function recognizeByListingProductGeneralId()
    {
        $generalId = $this->getAmazonListingProduct()->getGeneralId();
        if (empty($generalId)) {
            return null;
        }

        return $generalId;
    }

    protected function recognizeBySearchGeneralId()
    {
        if ($this->getVariationManager()->isRelationChildType()) {
            return null;
        }

        $generalId = $this->getAmazonListingProduct()
                          ->getIdentifiers()
                          ->getGeneralId();

        if ($generalId === null) {
            return null;
        }

        if ($this->getAmazonListingProduct()->isGeneralIdOwner()) {
            $this->addMessage(
                Mage::helper('M2ePro/Module_Log')->encodeDescription(
                    'M2E Pro did not use New ASIN/ISBN Creation feature assigned because settings
                    for ASIN/ISBN Search were specified in Listing Product Identifiers and a value
                    %general_id% were set in Magento Attribute for that Product.',
                    array('!general_id' => $generalId->getIdentifier())
                ),
                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_WARNING
            );
        }

        if ($generalId->hasUnresolvedType()) {
            $this->addMessage(
                Mage::helper('M2ePro/Module_Log')->encodeDescription(
                    'The value "%general_id%" provided for ASIN/ISBN in Listing Product Identifiers is invalid.
                     Please set the correct value and try again.',
                    array('!general_id' => $generalId->getIdentifier())
                )
            );

            return false;
        }

        $generalIdType = $generalId->isISBN() ? 'ISBN' : 'ASIN';

        $amazonData = $this->getDataFromAmazon($generalId->getIdentifier());
        if (empty($amazonData)) {
            $this->addMessage(
                Mage::helper('M2ePro/Module_Log')->encodeDescription(
                    '%general_id_type% %general_id% provided in Listing Product Identifiers
                     is not found on Amazon.
                     Please set the correct value and try again.
                     Note: Due to Amazon API restrictions M2E Pro
                     might not see all the existing Products on Amazon.',
                    array('!general_id_type' => $generalIdType, '!general_id' => $generalId->getIdentifier())
                )
            );

            return false;
        }

        if (count($amazonData) > 1) {
            $this->addMessage(
                Mage::helper('M2ePro/Module_Log')->encodeDescription(
                    'There is more than one Product found on Amazon using Search
                     by %general_id_type% %general_id%.
                     First, you should select certain one using manual search.',
                    array('!general_id_type' => $generalIdType, '!general_id' => $generalId->getIdentifier())
                )
            );

            return false;
        }

        $amazonData = reset($amazonData);

        if (!empty($amazonData['parentage']) && $amazonData['parentage'] == 'parent') {
            if (!$this->getVariationManager()->isRelationParentType()) {
                $this->addMessage(
                    Mage::helper('M2ePro/Module_Log')->encodeDescription(
                        'Amazon Parent Product was found using Search by %general_id_type% %general_id%
                         while Simple or Child Product ASIN/ISBN is required.',
                        array('!general_id_type' => $generalIdType, '!general_id' => $generalId->getIdentifier())
                    )
                );

                return false;
            }

            if (!empty($amazonData['bad_parent'])) {
                $this->addMessage(
                    Mage::helper('M2ePro/Module_Log')->encodeDescription(
                        'Working with Amazon Parent Product found using Search by %general_id_type% %general_id%
                         is limited due to Amazon API restrictions.',
                        array('!general_id_type' => $generalIdType, '!general_id' => $generalId->getIdentifier())
                    )
                );

                return false;
            }

            $magentoAttributes = $this->getVariationManager()->getTypeModel()->getProductAttributes();
            $amazonDataAttributes = array_keys($amazonData['variations']['set']);

            if (count($magentoAttributes) != count($amazonDataAttributes)) {
                $this->addMessage(
                    Mage::helper('M2ePro/Module_Log')->encodeDescription(
                        'The number of Variation Attributes of the Amazon Parent Product found
                         using Search by %general_id_type% %general_id% does not match the number
                         of Variation Attributes of the Magento Parent Product.',
                        array('!general_id_type' => $generalIdType, '!general_id' => $generalId->getIdentifier())
                    )
                );

                return false;
            }

            return $generalId->getIdentifier();
        }

        if ($this->getVariationManager()->isRelationParentType()) {
            $this->addMessage(
                Mage::helper('M2ePro/Module_Log')->encodeDescription(
                    'Amazon Simple or Child Product was found using Search by %general_id_type% %general_id%
                     while Parent Product ASIN/ISBN is required.',
                    array('!general_id_type' => $generalIdType, '!general_id' => $generalId->getIdentifier())
                )
            );

            return false;
        }

        return $generalId->getIdentifier();
    }

    protected function recognizeBySearchWorldwideId()
    {
        if ($this->getVariationManager()->isRelationMode()) {
            return null;
        }

        $worldwideId = $this->getAmazonListingProduct()
                            ->getIdentifiers()
                            ->getWorldwideId();

        if ($worldwideId === null) {
            return null;
        }

        $changingListTypeMessage = Mage::helper('M2ePro/Module_Log')->encodeDescription(
            'New ASIN/ISBN was not created because UPC/EAN of the Product is already present in Amazon catalog'
        );

        if ($worldwideId->hasUnresolvedType()) {
            if ($this->getAmazonListingProduct()->isGeneralIdOwner()) {
                $this->addMessage(
                    $changingListTypeMessage, Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_WARNING
                );
            }

            $this->addMessage(
                Mage::helper('M2ePro/Module_Log')->encodeDescription(
                    'The value "%worldwide_id%" provided for UPC/EAN in Listing Product Identifiers is invalid.
                     Please set the correct value and try again.',
                    array('!worldwide_id' => $worldwideId->getIdentifier())
                )
            );

            return false;
        }

        $worldwideIdType = $worldwideId->isUPC() ? 'UPC' : 'EAN';

        $amazonData = $this->getDataFromAmazon($worldwideId->getIdentifier());
        if (empty($amazonData)) {
            if ($this->getAmazonListingProduct()->isGeneralIdOwner()) {
                return null;
            }

            $this->addMessage(
                Mage::helper('M2ePro/Module_Log')->encodeDescription(
                    '%worldwide_id_type% %worldwide_id% provided in Product Identifiers
                     is not found on Amazon. Please set Product Type to create New ASIN/ISBN.',
                    array('!worldwide_id_type' => $worldwideIdType, '!worldwide_id' => $worldwideId->getIdentifier())
                )
            );

            return false;
        }

        if ($this->getAmazonListingProduct()->isGeneralIdOwner()) {
            $this->addMessage(
                $changingListTypeMessage, Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_WARNING
            );
        }

        if (count($amazonData) > 1) {
            $this->addMessage(
                Mage::helper('M2ePro/Module_Log')->encodeDescription(
                    'There is more than one Product found on Amazon using Search by %worldwide_id_type% %worldwide_id%.
                     First, you should select certain one using manual search.',
                    array('!worldwide_id_type' => $worldwideIdType, '!worldwide_id' => $worldwideId->getIdentifier())
                )
            );

            return false;
        }

        $amazonData = reset($amazonData);

        if (!empty($amazonData['parentage']) &&
            $amazonData['parentage'] == 'parent' &&
            empty($amazonData['requested_child_id'])
        ) {
            $this->addMessage(
                Mage::helper('M2ePro/Module_Log')->encodeDescription(
                    'Amazon Parent Product was found using Search by %worldwide_id_type% %worldwide_id%
                     while Simple or Child Product ASIN/ISBN is required.',
                    array('!worldwide_id_type' => $worldwideIdType, '!worldwide_id' => $worldwideId->getIdentifier())
                )
            );

            return false;
        }

        if (!empty($amazonData['requested_child_id'])) {
            return $amazonData['requested_child_id'];
        } else {
            return $amazonData['product_id'];
        }
    }

    // ---------------------------------------

    protected function validateNewProduct()
    {
        if (!$this->getAmazonListingProduct()->isGeneralIdOwner()) {
            $this->addMessage(
                'Product cannot be Listed because ASIN/ISBN is not assigned, UPC/EAN value
                 is not provided and the Product Identifiers are invalid. Please set the required
                 Settings and try again.'
            );

            return false;
        }

        $productTypeTemplate = $this->getAmazonListingProduct()->getProductTypeTemplate();
        if (empty($productTypeTemplate)) {
            $this->addMessage(
                'Product cannot be Listed because the process of new ASIN/ISBN creation has started
                 but Product Type is missing. Please assign the Product Type and try again.'
            );

            return false;
        }

        if ($this->getVariationManager()->isRelationMode()) {
            $channelTheme = $this->getChannelTheme();

            if (empty($channelTheme)) {
                $this->addMessage(
                    'Product is not Listed. The process of New ASIN/ISBN creation has been started,
                     but the Variation Theme was not set.
                     Please, set the Variation Theme to list this Product.'
                );

                return false;
            }
        }

        if ($this->getVariationManager()->isRelationParentType()) {
            return true;
        }

        $worldwideId = $this->getAmazonListingProduct()
                                   ->getIdentifiers()
                                   ->getWorldwideId();

        if ($worldwideId === null) {
            return true;
        }

        if ($worldwideId->hasUnresolvedType()) {
            $this->addMessage(
                'The Product cannot be Listed because the value specified for UPC/EAN under
                 Amazon > Configuration > Main has an invalid format.
                 Please provide the correct value and try again.'
            );

            return false;
        }

        $worldwideIdType = $worldwideId->isUPC() ? 'UPC' : 'EAN';

        $amazonData = $this->getDataFromAmazon($worldwideId->getIdentifier());
        if (!empty($amazonData)) {
            $this->addMessage(
                Mage::helper('M2ePro/Module_Log')->encodeDescription(
                    'New ASIN/ISBN cannot be created because %worldwide_id_type% %worldwide_id% specified under
                     Amazon > Configuration > Main have been found on Amazon.
                     Please provide the correct value and try again.',
                    array('!worldwide_id_type' => $worldwideIdType, '!worldwide_id' => $worldwideId->getIdentifier())
                )
            );

            return false;
        }

        return true;
    }

    //########################################

    protected function validateComplexMagentoProductTypes()
    {
        if ($this->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {
            return false;
        }

        if ($this->getMagentoProduct()->isBundleType()) {
            return false;
        }

        if ($this->getMagentoProduct()->isDownloadableTypeWithSeparatedLinks()) {
            return false;
        }

        return true;
    }

    //########################################

    protected function getDataFromAmazon($identifier)
    {
        if (isset($this->_cachedData['amazon_data'][$identifier])) {
            return $this->_cachedData['amazon_data'][$identifier];
        }

        $validation = Mage::helper('M2ePro');

        $idType = (Mage::helper('M2ePro/Component_Amazon')->isASIN($identifier) ? 'ASIN' :
                  ($validation->isISBN($identifier)                             ? 'ISBN' :
                  ($validation->isUPC($identifier)                              ? 'UPC'  :
                  ($validation->isEAN($identifier)                              ? 'EAN'  : false))));

        if (empty($idType)) {
            return array();
        }

        $params = array(
            'item'    => $identifier,
            'id_type' => $idType,
            'variation_child_modification' => 'parent',
        );

        $searchMethod = 'byIdentifier';
        if ($idType == 'ASIN') {
            $searchMethod = 'byAsin';
            unset($params['id_type']);
        }

        $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector(
            'product', 'search', $searchMethod,
            $params, null,
            $this->getListingProduct()->getListing()->getAccount()
        );

        $dispatcherObject->process($connectorObj);
        $result = $connectorObj->getResponseData();

        foreach ($connectorObj->getResponse()->getMessages()->getEntities() as $message) {
            /** @var Ess_M2ePro_Model_Connector_Connection_Response_Message $message */

            if ($message->isError()) {
                $this->addMessage($message->getText());
            }
        }

        if ($searchMethod == 'byAsin') {
            return $this->_cachedData['amazon_data'][$identifier] = isset($result['item']) ? array($result['item'])
                                                                                          : array();
        }

        return $this->_cachedData['amazon_data'][$identifier] = isset($result['items']) ? $result['items'] : array();
    }

    //########################################

    protected function getChannelTheme()
    {
        $variationManager = $this->getAmazonListingProduct()->getVariationManager();
        if (!$variationManager->isRelationMode()) {
            return null;
        }

        $typeModel = $variationManager->getTypeModel();

        if ($variationManager->isRelationChildType()) {
            $typeModel = $variationManager->getTypeModel()
                ->getParentListingProduct()
                ->getChildObject()
                ->getVariationManager()
                ->getTypeModel();
        }

        return $typeModel->getChannelTheme();
    }

    //########################################

    protected function setListType($listType)
    {
        $this->_data['list_type'] = $listType;
    }

    protected function setGeneralId($generalId)
    {
        $this->_data['general_id'] = $generalId;
    }

    // ---------------------------------------

    protected function isExistInChildGeneralIdsForParent($childGeneralId)
    {
        return in_array($childGeneralId, $this->_childGeneralIdsForParent);
    }

    //########################################
}
