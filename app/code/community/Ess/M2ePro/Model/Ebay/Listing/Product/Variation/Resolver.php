<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_General as BuilderGeneral;

class Ess_M2ePro_Model_Ebay_Listing_Product_Variation_Resolver
{
    const MPN_SPECIFIC_NAME = 'MPN';

    /** @var Ess_M2ePro_Model_Listing_Product */
    protected $_listingProduct;
    protected $_isAllowedToSave = false;

    protected $_isAllowedToProcessVariationsWhichAreNotExistInTheModule = false;
    protected $_isAllowedToProcessVariationMpnErrors                    = false;
    protected $_isAllowedToProcessExistedVariations                     = false;

    protected $_moduleVariations   = array();
    protected $_channelVariations  = array();
    protected $_variationMpnValues = array();

    /** @var Ess_M2ePro_Model_Response_Message_Set */
    protected $_messagesSet;

    //########################################

    public function resolve()
    {
        try {
            $this->getMessagesSet()->clearEntities();
            $this->validate();

            $this->clearVariationsThatCanNotBeDeleted();

            $this->prepareModuleVariations();
            $this->validateModuleVariations();

            $this->prepareChannelVariations();

            $this->processVariationsWhichAreNotExistInTheModule();
            $this->processVariationMpnErrors();

            $this->processExistedVariations();
        } catch (\Exception $exception) {
            $message = Mage::getModel('M2ePro/Response_Message');
            $message->initFromException($exception);

            $this->getMessagesSet()->addEntity($message);
        }
    }

    //########################################

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function validate()
    {
        if (!($this->_listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                sprintf(
                    'Listing product is not provided [%s].', get_class($this->_listingProduct)
                )
            );
        }

        if (!$this->_listingProduct->getChildObject()->isVariationsReady()) {
            throw new Ess_M2ePro_Model_Exception_Logic('Not a variation product.');
        }

        if (!$this->_listingProduct->isRevisable()) {
            throw new Ess_M2ePro_Model_Exception_Logic('Not a revisable product.');
        }

        return true;
    }

    private function clearVariationsThatCanNotBeDeleted()
    {
        $this->_listingProduct
            ->setSetting('additional_data', 'variations_that_can_not_be_deleted', array())
            ->save();
    }

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function validateModuleVariations()
    {
        $skus = array();
        $options = array();

        $duplicatedSkus = array();
        $duplicatedOptions = array();

        foreach ($this->_moduleVariations as $variation) {
            $sku = $variation['sku'];
            $option = $this->getVariationHash($variation);

            if (empty($sku)) {
                continue;
            }

            if (in_array($sku, $skus)) {
                $duplicatedSkus[] = $sku;
            } else {
                $skus[] = $sku;
            }

            if (in_array($option, $options)) {
                $duplicatedOptions[] = $option;
            } else {
                $options[] = $option;
            }
        }

        if (!empty($duplicatedSkus)) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                sprintf(
                    'Duplicated SKUs: %s', implode(',', $duplicatedSkus)
                )
            );
        }

        if (!empty($duplicatedOptions)) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                sprintf(
                    'Duplicated Options: %s', implode(',', $duplicatedOptions)
                )
            );
        }
    }

    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function prepareModuleVariations()
    {
        $variationUpdater = Mage::getModel('M2ePro/Ebay_Listing_Product_Variation_Updater');
        $variationUpdater->process($this->_listingProduct);

        //--
        $trimmedSpecificsReplacements = array();
        $specificsReplacements = $this->_listingProduct->getSetting(
            'additional_data', 'variations_specifics_replacements', array()
        );

        foreach ($specificsReplacements as $findIt => $replaceBy) {
            $trimmedSpecificsReplacements[trim($findIt)] = trim($replaceBy);
        }

        $this->_moduleVariations = array();
        foreach ($this->_listingProduct->getVariations(true) as $variation) {

            /**@var Ess_M2ePro_Model_Ebay_Listing_Product_Variation $ebayVariation */
            $ebayVariation = $variation->getChildObject();

            $tempVariation = array(
                'id'            => $variation->getId(),
                'sku'           => $ebayVariation->getOnlineSku(),
                'price'         => $ebayVariation->getOnlinePrice(),
                'quantity'      => $ebayVariation->getOnlineQty(),
                'quantity_sold' => $ebayVariation->getOnlineQtySold(),
                'specifics'     => array(),
                'details'       => array()
            );

            //--------------------------------
            foreach ($variation->getOptions(true) as $option) {
                /**@var Ess_M2ePro_Model_Listing_Product_Variation_Option $option */

                $optionName  = trim($option->getAttribute());
                $optionValue = trim($option->getOption());

                if (array_key_exists($optionName, $trimmedSpecificsReplacements)) {
                    $optionName = $trimmedSpecificsReplacements[$optionName];
                }

                $tempVariation['specifics'][$optionName] = $optionValue;
            }

            $this->insertVariationDetails($variation, $tempVariation);

            //-- MPN Specific has been changed
            if (!empty($tempVariation['details']['mpn_previous']) && !empty($tempVariation['details']['mpn']) &&
                $tempVariation['details']['mpn_previous'] != $tempVariation['details']['mpn']) {
                $oneMoreVariation = array(
                    'id'        => null,
                    'qty'       => 0,
                    'price'     => $tempVariation['price'],
                    'sku'       => 'del-' . sha1(microtime(1) . $tempVariation['sku']),
                    'add'       => 0,
                    'delete'    => 1,
                    'specifics' => $tempVariation['specifics'],
                    'has_sales' => true,
                    'details'   => $tempVariation['details']
                );
                $oneMoreVariation['details']['mpn'] = $tempVariation['details']['mpn_previous'];

                if (!empty($trimmedSpecificsReplacements)) {
                    $oneMoreVariation['variations_specifics_replacements'] = $trimmedSpecificsReplacements;
                }

                $this->_moduleVariations[] = $oneMoreVariation;
            }

            unset($tempVariation['details']['mpn_previous']);

            $this->_moduleVariations[] = $tempVariation;
        }

        $variationsThatCanNoBeDeleted = $this->_listingProduct->getSetting(
            'additional_data', 'variations_that_can_not_be_deleted', array()
        );

        foreach ($variationsThatCanNoBeDeleted as $canNoBeDeleted) {
            $this->_moduleVariations[] = array(
                'id'            => null,
                'sku'           => $canNoBeDeleted['sku'],
                'price'         => $canNoBeDeleted['price'],
                'quantity'      => $canNoBeDeleted['qty'],
                'quantity_sold' => $canNoBeDeleted['qty'],
                'specifics'     => $canNoBeDeleted['specifics'],
                'details'       => $canNoBeDeleted['details']
            );
        }
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product_Variation $variation
     * @param $tempVariation
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function insertVariationDetails(Ess_M2ePro_Model_Listing_Product_Variation $variation, &$tempVariation)
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $this->_listingProduct->getChildObject();
        $ebayDescriptionTemplate = $ebayListingProduct->getEbayDescriptionTemplate();

        $additionalData = $variation->getAdditionalData();

        foreach (array('isbn', 'upc', 'ean', 'mpn', 'epid') as $tempType) {
            if ($tempType == 'mpn' && !empty($additionalData['online_product_details']['mpn'])) {
                $isMpnFilled = $variation->getListingProduct()->getSetting(
                    'additional_data', 'is_variation_mpn_filled'
                );

                if ($isMpnFilled === false) {
                    continue;
                }

                $tempVariation['details']['mpn'] = $additionalData['online_product_details']['mpn'];

                $isMpnCanBeChanged = Mage::helper('M2ePro/Component_Ebay_Configuration')
                    ->getVariationMpnCanBeChanged();

                if (!$isMpnCanBeChanged) {
                    continue;
                }

                $tempVariation['details']['mpn_previous'] = $additionalData['online_product_details']['mpn'];
            }

            if (isset($additionalData['product_details'][$tempType])) {
                $tempVariation['details'][$tempType] = $additionalData['product_details'][$tempType];
                continue;
            }

            if ($tempType == 'mpn') {
                if ($ebayDescriptionTemplate->isProductDetailsModeNone('brand')) {
                    continue;
                }

                if ($ebayDescriptionTemplate->isProductDetailsModeDoesNotApply('brand')) {
                    $tempVariation['details'][$tempType] = BuilderGeneral::PRODUCT_DETAILS_DOES_NOT_APPLY;
                    continue;
                }
            }

            if ($ebayDescriptionTemplate->isProductDetailsModeNone($tempType)) {
                continue;
            }

            if ($ebayDescriptionTemplate->isProductDetailsModeDoesNotApply($tempType)) {
                $tempVariation['details'][$tempType] = BuilderGeneral::PRODUCT_DETAILS_DOES_NOT_APPLY;
                continue;
            }

            if (!$this->_listingProduct->getMagentoProduct()->isConfigurableType() &&
                !$this->_listingProduct->getMagentoProduct()->isGroupedType()) {
                continue;
            }

            $attribute = $ebayDescriptionTemplate->getProductDetailAttribute($tempType);
            if (!$attribute) {
                continue;
            }

            /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
            $options = $variation->getOptions(true);
            $option = reset($options);

            $tempValue = $option->getMagentoProduct()->getAttributeValue($attribute);
            if (!$tempValue) {
                continue;
            }

            $tempVariation['details'][$tempType] = $tempValue;
        }

        $this->deleteNotAllowedIdentifiers($tempVariation['details']);
    }

    /**
     * @param array $data
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function deleteNotAllowedIdentifiers(array &$data)
    {
        if (empty($data)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $this->_listingProduct->getChildObject();

        $categoryId = $ebayListingProduct->getCategoryTemplateSource()->getCategoryId();
        $marketplaceId = $this->_listingProduct->getMarketplace()->getId();

        $categoryFeatures = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')
            ->getFeatures($categoryId, $marketplaceId);

        if (empty($categoryFeatures)) {
            return;
        }

        $statusDisabled = Ess_M2ePro_Helper_Component_Ebay_Category_Ebay::PRODUCT_IDENTIFIER_STATUS_DISABLED;

        foreach (array('ean', 'upc', 'isbn', 'epid') as $identifier) {
            $key = $identifier.'_enabled';
            if (!isset($categoryFeatures[$key]) || $categoryFeatures[$key] != $statusDisabled) {
                continue;
            }

            if (isset($data[$identifier])) {
                unset($data[$identifier]);
            }
        }
    }

    /**
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    private function prepareChannelVariations()
    {
        $this->_channelVariations  = array();
        $this->_variationMpnValues = array();

        foreach ($this->getVariationsDataFromEbay() as $variation) {
            $tempVariation = array(
                'id'            => null,
                'sku'           => $variation['sku'],
                'price'         => $variation['price'],
                'quantity'      => $variation['quantity'],
                'quantity_sold' => $variation['quantity_sold'],
                'specifics'     => $variation['specifics'],
                'details'       => !empty($variation['details']) ? $variation['details'] : array()
            );

            if (isset($tempVariation['specifics'][self::MPN_SPECIFIC_NAME])) {
                $tempVariation['details']['mpn'] = $tempVariation['specifics'][self::MPN_SPECIFIC_NAME];

                $this->_variationMpnValues[] = array(
                    'mpn'       => $tempVariation['specifics'][self::MPN_SPECIFIC_NAME],
                    'sku'       => $variation['sku'],
                    'specifics' => $variation['specifics'],
                );

                unset($tempVariation['specifics'][self::MPN_SPECIFIC_NAME]);
            }

            $this->_channelVariations[] = $tempVariation;
        }
    }

    /**
     * @return mixed
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getVariationsDataFromEbay()
    {
        /** @var Ess_M2ePro_Model_Connector_Command_RealTime_Virtual $connector */
        $connector = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher')->getVirtualConnector(
            'item', 'get', 'info',
            array(
                'item_id'              => $this->_listingProduct->getChildObject()->getEbayItemIdReal(),
                'parser_type'          => 'standard',
                'full_variations_mode' => true
            ),
            'result',
            $this->_listingProduct->getMarketplace(), $this->_listingProduct->getAccount()
        );

        $connector->process();
        $result = $connector->getResponseData();

        if (empty($result['variations'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Unable to retrieve variations from channel.');
        }

        return $result['variations'];
    }

    //########################################

    protected function getVariationsWhichDoNotExistInModule()
    {
        $variations = array();

        foreach ($this->_channelVariations as $channelVariation) {
            foreach ($this->_moduleVariations as $moduleVariation) {
                if ($this->isVariationEqualWithCurrent($channelVariation, $moduleVariation)) {
                    continue 2;
                }
            }

            $variations[] = $channelVariation;
        }

        return $variations;
    }

    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function processExistedVariations()
    {
        if (!$this->_isAllowedToProcessExistedVariations) {
            return;
        }

        foreach ($this->_moduleVariations as $moduleVariation) {
            foreach ($this->_channelVariations as $channelVariation) {
                if ($this->isVariationEqualWithCurrent($channelVariation, $moduleVariation)) {
                    $this->addNotice(
                        sprintf(
                            "Variation ID %s will be Updated. Hash: %s",
                            $moduleVariation['id'], $this->getVariationHash($moduleVariation)
                        )
                    );

                    if (!$this->_isAllowedToSave) {
                        continue;
                    }

                    $availableQty = ($channelVariation['quantity'] - $channelVariation['quantity_sold']);

                    /** @var Ess_M2ePro_Model_Listing_Product_Variation $lpv */
                    $lpv = Mage::helper('M2ePro/Component_Ebay')->getObject(
                        'Listing_Product_Variation', $moduleVariation['id']
                    );

                    $additionalData = $lpv->getAdditionalData();
                    $additionalData['online_product_details'] = $channelVariation['details'];

                    $lpv->addData(
                        array(
                            'online_sku'      => $channelVariation['sku'],
                            'online_qty'      => $channelVariation['quantity'],
                            'online_qty_sold' => $channelVariation['quantity_sold'],
                            'status'          => $availableQty > 0 ? Ess_M2ePro_Model_Listing_Product::STATUS_LISTED
                                : Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE,
                            'add'             => 0,
                            'detele'          => 0,

                            'additional_data' => json_encode($additionalData)
                        )
                    );
                    $lpv->save();

                    continue 2;
                }
            }
        }
    }

    /**
     * variations_that_can_not_be_deleted will be filled up
     */
    protected function processVariationsWhichAreNotExistInTheModule()
    {
        if (!$this->_isAllowedToProcessVariationsWhichAreNotExistInTheModule) {
            return;
        }

        $variations = $this->getVariationsWhichDoNotExistInModule();
        if (empty($variations)) {
            return;
        }

        foreach ($variations as $variation) {
            $this->addWarning(
                sprintf(
                    "SKU %s will be added to the Module. Hash: %s",
                    $variation['sku'], $this->getVariationHash($variation)
                )
            );
        }

        if (!$this->_isAllowedToSave) {
            return;
        }

        $variationsThatCanNoBeDeleted = $this->_listingProduct->getSetting(
            'additional_data', 'variations_that_can_not_be_deleted', array()
        );

        foreach ($variations as $variation) {
            $variationsThatCanNoBeDeleted[] = array(
                'qty'       => 0,
                'price'     => $variation['price'],
                'sku'       => !empty($variation['sku']) ? $variation['sku'] : '',
                'add'       => 0,
                'delete'    => 1,
                'specifics' => $variation['specifics'],
                'details'   => $variation['details'],
                'has_sales' => false,
                'from_resolver' => true,
            );
        }

        $this->_listingProduct->setSetting(
            'additional_data', 'variations_that_can_not_be_deleted', $variationsThatCanNoBeDeleted
        );
        $this->_listingProduct->save();
    }

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function processVariationMpnErrors()
    {
        if (!$this->_isAllowedToProcessVariationMpnErrors) {
            return;
        }

        $isVariationMpnFilled = !empty($this->_variationMpnValues);

        $isVariationMpnFilled && $this->fillVariationMpnValues();

        if ($this->_isAllowedToSave) {
            $this->_listingProduct->setSetting('additional_data', 'is_variation_mpn_filled', $isVariationMpnFilled);

            if (!$isVariationMpnFilled) {
                $this->_listingProduct->setSetting('additional_data', 'without_mpn_variation_issue', true);
            }

            $this->_listingProduct->save();
        }
    }

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function fillVariationMpnValues()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Variation_Collection $variationCollection */
        $variationCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product_Variation');
        $variationCollection->addFieldToFilter('listing_product_id', $this->_listingProduct->getId());

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Variation_Option_Collection $variationOptionCollection */
        $variationOptionCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection(
            'Listing_Product_Variation_Option'
        );
        $variationOptionCollection->addFieldToFilter(
            'listing_product_variation_id', $variationCollection->getColumnValues('id')
        );

        /** @var Ess_M2ePro_Model_Listing_Product_Variation[] $variations */
        $variations = $variationCollection->getItems();

        /** @var Ess_M2ePro_Model_Listing_Product_Variation_Option[] $variationOptions */
        $variationOptions = $variationOptionCollection->getItems();

        foreach ($variations as $variation) {
            $specifics = array();

            foreach ($variationOptions as $id => $variationOption) {
                if ($variationOption->getListingProductVariationId() != $variation->getId()) {
                    continue;
                }

                $specifics[$variationOption->getAttribute()] = $variationOption->getOption();
                unset($variationOptions[$id]);
            }

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation $ebayVariation */
            $ebayVariation = $variation->getChildObject();

            foreach ($this->_variationMpnValues as $id => $variationMpnValue) {
                if ($ebayVariation->getOnlineSku() != $variationMpnValue['sku'] &&
                    $specifics != $variationMpnValue['specifics']
                ) {
                    continue;
                }

                $additionalData = $variation->getAdditionalData();

                if (!isset($additionalData['online_product_details']['mpn']) ||
                    $additionalData['online_product_details']['mpn'] != $variationMpnValue['mpn']
                ) {
                    $this->addWarning(
                        sprintf(
                            "MPN for SKU %s has been added to the Module. Hash: %s",
                            $variationMpnValue['sku'],
                            $this->getVariationHash($variation)
                        )
                    );

                    if (!$this->_isAllowedToSave) {
                        continue;
                    }

                    $additionalData['online_product_details']['mpn'] = $variationMpnValue['mpn'];

                    $variation->setSettings('additional_data', $additionalData);
                    $variation->save();
                }

                unset($this->_variationMpnValues[$id]);

                break;
            }
        }
    }

    //########################################

    protected function isVariationEqualWithCurrent(array $channelVariation, array $moduleVariation)
    {
        if (count($channelVariation['specifics']) != count($moduleVariation['specifics'])) {
            return false;
        }

        $channelMpn = isset($channelVariation['details']['mpn']) ? $channelVariation['details']['mpn'] : null;
        $moduleMpn  = isset($moduleVariation['details']['mpn'])  ? $moduleVariation['details']['mpn']  : null;

        /** @var Ess_M2ePro_Helper_Component_Ebay_Configuration $ebayConfiguration */
        $ebayConfiguration = Mage::helper('M2ePro/Component_Ebay_Configuration');
        if ($channelMpn != $moduleMpn
            && $ebayConfiguration->getIgnoreVariationMpnInResolver() === false
        ) {
            return false;
        }

        foreach ($moduleVariation['specifics'] as $moduleVariationOptionName => $moduleVariationOptionValue) {
            $haveOption = false;
            foreach ($channelVariation['specifics'] as $channelVariationOptionName => $channelVariationOptionValue) {
                if (trim($moduleVariationOptionName)  == trim($channelVariationOptionName) &&
                    trim($moduleVariationOptionValue) == trim($channelVariationOptionValue))
                {
                    $haveOption = true;
                    break;
                }
            }

            if ($haveOption === false) {
                return false;
            }
        }

        return true;
    }

    protected function getVariationHash($variation)
    {
        $hash = array();

        foreach ($variation['specifics'] as $name => $value) {
            $hash[] = trim($name) .'-'.trim($value);
        }

        if (!empty($variation['details']['mpn'])) {
            $hash[] = 'MPN' .'-'. $variation['details']['mpn'];
        }

        return implode('##', $hash);
    }

    //########################################

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $lp)
    {
        $this->_listingProduct = $lp;
        return $this;
    }

    public function setIsAllowedToSave($value)
    {
        $this->_isAllowedToSave = $value;
        return $this;
    }

    public function setIsAllowedToProcessVariationsWhichAreNotExistInTheModule($value)
    {
        $this->_isAllowedToProcessVariationsWhichAreNotExistInTheModule = $value;

        return $this;
    }

    public function setIsAllowedToProcessVariationMpnErrors($value)
    {
        $this->_isAllowedToProcessVariationMpnErrors = $value;

        return $this;
    }

    public function setIsAllowedToProcessExistedVariations($value)
    {
        $this->_isAllowedToProcessExistedVariations = $value;

        return $this;
    }

    public function getMessagesSet()
    {
        if ($this->_messagesSet === null) {
            $this->_messagesSet = Mage::getModel('M2ePro/Response_Message_Set');
        }

        return $this->_messagesSet;
    }

    //########################################

    protected function addError($messageText)
    {
        $message = Mage::getModel('M2ePro/Response_Message');
        $message->initFromPreparedData($messageText, Ess_M2ePro_Model_Response_Message::TYPE_ERROR);

        $this->getMessagesSet()->addEntity($message);
    }

    protected function addWarning($messageText)
    {
        $message = Mage::getModel('M2ePro/Response_Message');
        $message->initFromPreparedData($messageText, Ess_M2ePro_Model_Response_Message::TYPE_WARNING);

        $this->getMessagesSet()->addEntity($message);
    }

    protected function addNotice($messageText)
    {
        $message = Mage::getModel('M2ePro/Response_Message');
        $message->initFromPreparedData($messageText, Ess_M2ePro_Model_Response_Message::TYPE_NOTICE);

        $this->getMessagesSet()->addEntity($message);
    }

    //########################################
}
