<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_General as BuilderGeneral;

class Ess_M2ePro_Model_Ebay_Listing_Product_Variation_Resolver
{
    //########################################

    const MPN_SPECIFIC_NAME = 'MPN';

    /** @var Ess_M2ePro_Model_Listing_Product */
    protected $listingProduct;
    protected $isAllowedToSave = false;

    protected $moduleVariations  = array();
    protected $channelVariations = array();

    /** @var Ess_M2ePro_Model_Response_Message_Set */
    protected $messagesSet;

    //########################################

    public function process()
    {
        try {

            $this->getMessagesSet()->clearEntities();
            $this->validate();

            $this->moduleVariations  = $this->getModuleVariations();
            $this->channelVariations = $this->getChannelVariations();

            $this->validateModuleVariations();

            $this->processVariationsWhichDoNotExistOnTheChannel();
            $this->processVariationsWhichAreNotExistInTheModule();

            $this->processExistedVariations();

        } catch (\Exception $exception) {

            $message = Mage::getModel('M2ePro/Response_Message');
            $message->initFromException($exception);

            $this->getMessagesSet()->addEntity($message);
        }
    }

    //########################################

    private function validate()
    {
        if (!($this->listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
            throw new Ess_M2ePro_Model_Exception_Logic(sprintf(
                'Listing product is not provided [%s].', get_class($this->listingProduct)
            ));
        }

        if (!$this->listingProduct->getChildObject()->isVariationsReady()) {
            throw new Ess_M2ePro_Model_Exception_Logic('Not a variation product.');
        }

        if (!$this->listingProduct->isRevisable()) {
            throw new Ess_M2ePro_Model_Exception_Logic('Not a reviseble product.');
        }

        return true;
    }

    private function validateModuleVariations()
    {
        $skus = array();
        $options = array();

        $duplicatedSkus = array();
        $duplicatedOptions = array();

        foreach ($this->moduleVariations as $variation) {

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

            throw new Ess_M2ePro_Model_Exception_Logic(sprintf(
                'Duplicated SKUs: %s', implode(',', $duplicatedSkus)
            ));
        }

        if (!empty($duplicatedOptions)) {

            throw new Ess_M2ePro_Model_Exception_Logic(sprintf(
                'Duplicated Options: %s', implode(',', $duplicatedOptions)
            ));
        }
    }

    //########################################

    private function getModuleVariations()
    {
        $variationUpdater = Mage::getModel('M2ePro/Ebay_Listing_Product_Variation_Updater');
        $variationUpdater->process($this->listingProduct);

        //--
        $trimmedSpecificsReplacements = array();
        $specificsReplacements = $this->listingProduct->getSetting(
            'additional_data', 'variations_specifics_replacements', array()
        );

        foreach ($specificsReplacements as $findIt => $replaceBy) {
            $trimmedSpecificsReplacements[trim($findIt)] = trim($replaceBy);
        }
        //--

        $variations = array();
        foreach ($this->listingProduct->getVariations(true) as $variation) {

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
            //--------------------------------

            //-- MPN Specific has been changed
            //--------------------------------
            if (!empty($tempVariation['details']['mpn_previous']) && !empty($tempVariation['details']['mpn']) &&
                $tempVariation['details']['mpn_previous'] != $tempVariation['details']['mpn']) {

                $oneMoreVariation = array(
                    'id'        => NULL,
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
                //--------------------------------

                $variations[] = $oneMoreVariation;
            }
            unset($tempVariation['details']['mpn_previous']);
            //--------------------------------

            $variations[] = $tempVariation;
        }

        //--------------------------------
        $variationsThatCanNoBeDeleted = $this->listingProduct->getSetting(
            'additional_data', 'variations_that_can_not_be_deleted', array()
        );

        foreach ($variationsThatCanNoBeDeleted as $canNoBeDeleted) {

            $variations[] = array(
                'id'            => NULL,
                'sku'           => $canNoBeDeleted['sku'],
                'price'         => $canNoBeDeleted['price'],
                'quantity'      => $canNoBeDeleted['qty'],
                'quantity_sold' => $canNoBeDeleted['qty'],
                'specifics'     => $canNoBeDeleted['specifics'],
                'details'       => $canNoBeDeleted['details']
            );
        }
        //--------------------------------

        return $variations;
    }

    private function insertVariationDetails(Ess_M2ePro_Model_Listing_Product_Variation $variation, &$tempVariation)
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $this->listingProduct->getChildObject();
        $ebayDescriptionTemplate = $ebayListingProduct->getEbayDescriptionTemplate();

        $additionalData = $variation->getAdditionalData();

        foreach (array('isbn','upc','ean','mpn','epid') as $tempType) {

            if ($tempType == 'mpn' && !empty($additionalData['online_product_details']['mpn'])) {

                $isMpnFilled = $variation->getListingProduct()->getSetting(
                    'additional_data', 'is_variation_mpn_filled'
                );

                if ($isMpnFilled === false) {
                    continue;
                }

                $tempVariation['details']['mpn'] = $additionalData['online_product_details']['mpn'];

                $isMpnCanBeChanged = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
                    '/component/ebay/variation/', 'mpn_can_be_changed'
                );

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

            if (!$this->listingProduct->getMagentoProduct()->isConfigurableType() &&
                !$this->listingProduct->getMagentoProduct()->isGroupedType()) {
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

        $this->deleteNotAllowedIdentifier($tempVariation['details']);
    }

    private function deleteNotAllowedIdentifier(array &$data)
    {
        if (empty($data)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $this->listingProduct->getChildObject();

        $categoryId = $ebayListingProduct->getCategoryTemplateSource()->getMainCategory();
        $marketplaceId = $this->listingProduct->getMarketplace()->getId();

        $categoryFeatures = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')
            ->getFeatures($categoryId, $marketplaceId);

        if (empty($categoryFeatures)) {
            return;
        }

        $statusDisabled = Ess_M2ePro_Helper_Component_Ebay_Category_Ebay::PRODUCT_IDENTIFIER_STATUS_DISABLED;

        foreach (array('ean','upc','isbn','epid') as $identifier) {

            $key = $identifier.'_enabled';
            if (!isset($categoryFeatures[$key]) || $categoryFeatures[$key] != $statusDisabled) {
                continue;
            }

            if (isset($data[$identifier])) {
                unset($data[$identifier]);
            }
        }
    }

    private function getChannelVariations()
    {
        /** @var Ess_M2ePro_Model_Connector_Command_RealTime_Virtual $connector */
        $connector = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher')->getVirtualConnector(
            'item', 'get', 'info',
            array(
                'item_id'              => $this->listingProduct->getChildObject()->getEbayItemIdReal(),
                'parser_type'          => 'standard',
                'full_variations_mode' => true
            ),
            'result',
            $this->listingProduct->getMarketplace(), $this->listingProduct->getAccount()
        );

        $connector->process();
        $result = $connector->getResponseData();

        if (empty($result['variations'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Unable to retrieve variations from channel.');
        }

        $variations = array();
        foreach ($result['variations'] as $variation) {

            $tempVariation = array(
                'id'            => NULL,
                'sku'           => $variation['sku'],
                'price'         => $variation['price'],
                'quantity'      => $variation['quantity'],
                'quantity_sold' => $variation['quantity_sold'],
                'specifics'     => $variation['specifics'],
                'details'       => !empty($variation['details']) ? $variation['details'] : array()
            );

            if (isset($tempVariation['specifics'][self::MPN_SPECIFIC_NAME])) {

                $tempVariation['details']['mpn'] = $tempVariation['specifics'][self::MPN_SPECIFIC_NAME];
                unset($tempVariation['specifics'][self::MPN_SPECIFIC_NAME]);
            }

            $variations[] = $tempVariation;
        }

        return $variations;
    }

    //########################################

    private function getVariationsWhichDoNotExistOnChannel()
    {
        $variations = array();

        foreach ($this->moduleVariations as $moduleVariation) {
            foreach ($this->channelVariations as $channelVariation) {
                if ($this->isVariationEqualWithCurrent($channelVariation, $moduleVariation)) {
                    continue 2;
                }
            }
            $variations[] = $moduleVariation;
        }

        return $variations;
    }

    private function getVariationsWhichDoNotExistInModule()
    {
        $variations = array();

        foreach ($this->channelVariations as $channelVariation) {
            foreach ($this->moduleVariations as $moduleVariation) {
                if ($this->isVariationEqualWithCurrent($channelVariation, $moduleVariation)) {
                    continue 2;
                }
            }
            $variations[] = $channelVariation;
        }

        return $variations;
    }

    //########################################

    private function processVariationsWhichDoNotExistOnTheChannel()
    {
        $variations = $this->getVariationsWhichDoNotExistOnChannel();
        if (empty($variations)) {
            return;
        }

        foreach ($variations as $variation) {

            $this->addNotice(sprintf(
                "SKU %s will be added to the Channel. Hash: %s",
                $variation['sku'], $this->getVariationHash($variation)
            ));
        }

        if (!$this->isAllowedToSave) {
            return;
        }
    }

    /**
     * variations_that_can_not_be_deleted will be filled up
     */
    private function processVariationsWhichAreNotExistInTheModule()
    {
        $variations = $this->getVariationsWhichDoNotExistInModule();
        if (empty($variations)) {
            return;
        }

        foreach ($variations as $variation) {

            $this->addWarning(sprintf(
                "SKU %s will be added to the Module. Hash: %s",
                $variation['sku'], $this->getVariationHash($variation)
            ));
        }

        if (!$this->isAllowedToSave) {
            return;
        }

        $variationsThatCanNoBeDeleted = $this->listingProduct->getSetting(
            'additional_data', 'variations_that_can_not_be_deleted', array()
        );

        foreach ($variations as $variation) {

            $variationsThatCanNoBeDeleted[] = array(
                'qty'       => 0,
                'price'     => $variation['price'],
                'sku'       => !empty($variation['sku']) ? 'del-' . sha1(microtime(1).$variation['sku']) : '',
                'add'       => 0,
                'delete'    => 1,
                'specifics' => $variation['specifics'],
                'details'   => $variation['details'],
                'has_sales' => true,
            );
        }

        $this->listingProduct->setSetting(
            'additional_data', 'variations_that_can_not_be_deleted', $variationsThatCanNoBeDeleted
        );
        $this->listingProduct->save();
    }

    private function processExistedVariations()
    {
        foreach ($this->moduleVariations as $moduleVariation) {
            foreach ($this->channelVariations as $channelVariation) {

                if ($this->isVariationEqualWithCurrent($channelVariation, $moduleVariation)) {

                    $this->addNotice(sprintf(
                        "Variation ID %s will be Updated. Hash: %s",
                        $moduleVariation['id'], $this->getVariationHash($moduleVariation)
                    ));

                    if (!$this->isAllowedToSave) {
                        continue;
                    }

                    $availableQty = ($channelVariation['quantity'] - $channelVariation['quantity_sold']);

                    /** @var Ess_M2ePro_Model_Listing_Product_Variation $lpv */
                    $lpv = Mage::helper('M2ePro/Component_Ebay')->getObject(
                        'Listing_Product_Variation', $moduleVariation['id']
                    );

                    $additionalData = $lpv->getAdditionalData();
                    $additionalData['online_product_details'] = $channelVariation['details'];

                    $lpv->addData(array(
                        'online_sku'      => $channelVariation['sku'],
                        'online_qty'      => $channelVariation['quantity'],
                        'online_qty_sold' => $channelVariation['quantity_sold'],
                        'status'          => $availableQty > 0 ? Ess_M2ePro_Model_Listing_Product::STATUS_LISTED
                                                               : Ess_M2ePro_Model_Listing_Product::STATUS_SOLD,
                        'add'             => 0,
                        'detele'          => 0,

                        'additional_data' => json_encode($additionalData)
                    ));
                    $lpv->save();

                    continue 2;
                }
            }
        }
    }

    //########################################

    private function isVariationEqualWithCurrent(array $channelVariation, array $moduleVariation)
    {
        if (count($channelVariation['specifics']) != count($moduleVariation['specifics'])) {
            return false;
        }

        $channelMpn = isset($channelVariation['details']['mpn']) ? $channelVariation['details']['mpn'] : NULL;
        $moduleMpn  = isset($moduleVariation['details']['mpn'])  ? $moduleVariation['details']['mpn']  : NULL;

        if ($channelMpn != $moduleMpn) {
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

    private function getVariationHash($variation)
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
        $this->listingProduct = $lp;
        return $this;
    }

    public function setIsAllowedToSave($value)
    {
        $this->isAllowedToSave = $value;
        return $this;
    }

    //----------------------------------------

    public function getMessagesSet()
    {
        if (is_null($this->messagesSet)) {
            $this->messagesSet = Mage::getModel('M2ePro/Response_Message_Set');
        }

        return $this->messagesSet;
    }

    //########################################

    protected function addError($messageText)
    {
        $message = Mage::getModel('M2ePro/Response_Message');
        $message->initFromPreparedData($messageText, $message::TYPE_ERROR);

        $this->getMessagesSet()->addEntity($message);
    }

    protected function addWarning($messageText)
    {
        $message = Mage::getModel('M2ePro/Response_Message');
        $message->initFromPreparedData($messageText, $message::TYPE_WARNING);

        $this->getMessagesSet()->addEntity($message);
    }

    protected function addNotice($messageText)
    {
        $message = Mage::getModel('M2ePro/Response_Message');
        $message->initFromPreparedData($messageText, $message::TYPE_NOTICE);

        $this->getMessagesSet()->addEntity($message);
    }

    //########################################
}