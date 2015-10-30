<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Synchronization_Templates_Inspector
    extends Ess_M2ePro_Model_Synchronization_Templates_Inspector
{
    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     * @throws Exception
     */
    public function isMeetListRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$listingProduct->isNotListed()) {
            return false;
        }

        if (!$listingProduct->isListable()) {
            return false;
        }

        if ($listingProduct->isLockedObject('in_action')) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Buy_Listing_Product $buyListingProduct */
        $buyListingProduct = $listingProduct->getChildObject();

        if (!$buyListingProduct->getGeneralId()) {
            $searchGeneralId = $buyListingProduct->getListingSource()->getSearchGeneralId();
            if (empty($searchGeneralId)) {
                return false;
            }
        }

        $buySynchronizationTemplate = $buyListingProduct->getBuySynchronizationTemplate();

        if (!$buySynchronizationTemplate->isListMode()) {
            return false;
        }

        $variationManager = $buyListingProduct->getVariationManager();

        if ($variationManager->isVariationProduct() && !$variationManager->isVariationProductMatched()) {
            return false;
        }

        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        $additionalData = $listingProduct->getAdditionalData();

        if ($buySynchronizationTemplate->isListStatusEnabled()) {

            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                // M2ePro_TRANSLATIONS
                // Product was not automatically Listed according to the List Rules in Synchronization Policy. Status of Magento Product is Disabled (%date%) though in Synchronization Rules “Product Status” is set to Enabled.
                $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                     Status of Magento Product is Disabled (%date%) though in Synchronization Rules “Product Status”
                     is set to Enabled.',
                    array('date' => Mage::helper('M2ePro')->getCurrentGmtDate())
                );
                $additionalData['synch_template_list_rules_note'] = $note;

                $listingProduct->setSettings('additional_data', $additionalData)->save();

                return false;
            } else if ($variationManager->isVariationProduct() && $variationManager->isVariationProductMatched()) {

                $temp = $variationResource->isAllStatusesDisabled(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    // M2ePro_TRANSLATIONS
                    // Product was not automatically Listed according to the List Rules in Synchronization Policy. Status of Magento Product Variation is Disabled though in Synchronization Rules “Product Status“ is set to Enabled.
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Status of Magento Product Variation is Disabled though in Synchronization Rules
                         “Product Status“ is set to Enabled.',
                        array('date' => Mage::helper('M2ePro')->getCurrentGmtDate())
                    );
                    $additionalData['synch_template_list_rules_note'] = $note;

                    $listingProduct->setSettings('additional_data', $additionalData)->save();

                    return false;
                }
            }
        }

        if ($buySynchronizationTemplate->isListIsInStock()) {

            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                // M2ePro_TRANSLATIONS
                // Product was not automatically Listed according to the List Rules in Synchronization Policy. Stock Availability of Magento Product is Out of Stock though in Synchronization Rules “Stock Availability” is set to In Stock.
                $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                     Stock Availability of Magento Product is Out of Stock though in
                     Synchronization Rules “Stock Availability” is set to In Stock.',
                    array('date' => Mage::helper('M2ePro')->getCurrentGmtDate())
                );
                $additionalData['synch_template_list_rules_note'] = $note;

                $listingProduct->setSettings('additional_data', $additionalData)->save();

                return false;
            } else if ($variationManager->isVariationProduct() && $variationManager->isVariationProductMatched()) {

                $temp = $variationResource->isAllDoNotHaveStockAvailabilities(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    // M2ePro_TRANSLATIONS
                    // Product was not automatically Listed according to the List Rules in Synchronization Policy. Stock Availability of Magento Product Variation is Out of Stock though in Synchronization Rules “Stock Availability” is set to In Stock.
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Stock Availability of Magento Product Variation is Out of Stock though
                         in Synchronization Rules “Stock Availability” is set to In Stock.',
                        array('date' => Mage::helper('M2ePro')->getCurrentGmtDate())
                    );
                    $additionalData['synch_template_list_rules_note'] = $note;

                    $listingProduct->setSettings('additional_data', $additionalData)->save();

                    return false;
                }
            }
        }

        if ($buySynchronizationTemplate->isListWhenQtyMagentoHasValue()) {

            $result = false;
            $productQty = (int)$buyListingProduct->getQty(true);

            $typeQty = (int)$buySynchronizationTemplate->getListWhenQtyMagentoHasValueType();
            $minQty = (int)$buySynchronizationTemplate->getListWhenQtyMagentoHasValueMin();
            $maxQty = (int)$buySynchronizationTemplate->getListWhenQtyMagentoHasValueMax();

            $note = '';

            if ($typeQty == Ess_M2ePro_Model_Buy_Template_Synchronization::LIST_QTY_LESS) {
                if ($productQty <= $minQty) {
                    $result = true;
                } else {
                    // M2ePro_TRANSLATIONS
                    // Product was not automatically Listed according to the List Rules in Synchronization Policy. Quantity of Magento Product is %product_qty% though in Synchronization Rules “Magento Quantity“ is set to less then  %template_min_qty%.
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Quantity of Magento Product is %product_qty% though in Synchronization Rules
                         “Magento Quantity“ is set to less then  %template_min_qty%.',
                        array(
                            '!template_min_qty' => $minQty,
                            '!product_qty' => $productQty,
                            '!date' => Mage::helper('M2ePro')->getCurrentGmtDate()
                        )
                    );
                }
            }

            if ($typeQty == Ess_M2ePro_Model_Buy_Template_Synchronization::LIST_QTY_MORE) {
                if ($productQty >= $minQty) {
                    $result = true;
                } else {
                    // M2ePro_TRANSLATIONS
                    // Product was not automatically Listed according to the List Rules in Synchronization Policy. Quantity of Magento Product is %product_qty% though in Synchronization Rules “Magento Quantity” is set to more then  %template_min_qty%.
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Quantity of Magento Product is %product_qty% though in Synchronization Rules
                         “Magento Quantity” is set to more then  %template_min_qty%.',
                        array(
                            '!template_min_qty' => $minQty,
                            '!product_qty' => $productQty,
                            '!date' => Mage::helper('M2ePro')->getCurrentGmtDate()
                        )
                    );
                }
            }

            if ($typeQty == Ess_M2ePro_Model_Buy_Template_Synchronization::LIST_QTY_BETWEEN) {
                if ($productQty >= $minQty && $productQty <= $maxQty) {
                    $result = true;
                } else {
                    // M2ePro_TRANSLATIONS
                    // Product was not automatically Listed according to the List Rules in Synchronization Policy. Quantity of Magento Product is %product_qty% though in Synchronization Rules “Magento Quantity” is set between  %template_min_qty% and %template_max_qty%.
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Quantity of Magento Product is %product_qty% though in Synchronization Rules
                         “Magento Quantity” is set between  %template_min_qty% and %template_max_qty%.',
                        array(
                            '!template_min_qty' => $minQty,
                            '!template_max_qty' => $maxQty,
                            '!product_qty' => $productQty,
                            '!date' => Mage::helper('M2ePro')->getCurrentGmtDate()
                        )
                    );
                }
            }

            if (!$result) {
                if (!empty($note)) {
                    $additionalData['synch_template_list_rules_note'] = $note;
                    $listingProduct->setSettings('additional_data', $additionalData)->save();
                }

                return false;
            }
        }

        if ($buySynchronizationTemplate->isListWhenQtyCalculatedHasValue()) {

            $result = false;
            $productQty = (int)$buyListingProduct->getQty(false);

            $typeQty = (int)$buySynchronizationTemplate->getListWhenQtyCalculatedHasValueType();
            $minQty = (int)$buySynchronizationTemplate->getListWhenQtyCalculatedHasValueMin();
            $maxQty = (int)$buySynchronizationTemplate->getListWhenQtyCalculatedHasValueMax();

            $note = '';

            if ($typeQty == Ess_M2ePro_Model_Buy_Template_Synchronization::LIST_QTY_LESS) {
                if ($productQty <= $minQty) {
                    $result = true;
                } else {
                    // M2ePro_TRANSLATIONS
                    // Product was not automatically Listed according to the List Rules in Synchronization Policy. Quantity of Magento Product is %product_qty% though in Synchronization Rules “Calculated Quantity” is set to less then %template_min_qty%.
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Quantity of Magento Product is %product_qty% though in Synchronization Rules
                         “Calculated Quantity” is set to less then %template_min_qty%.',
                        array(
                            '!template_min_qty' => $minQty,
                            '!product_qty' => $productQty,
                            '!date' => Mage::helper('M2ePro')->getCurrentGmtDate()
                        )
                    );
                }
            }

            if ($typeQty == Ess_M2ePro_Model_Buy_Template_Synchronization::LIST_QTY_MORE) {
                if ($productQty >= $minQty) {
                    $result = true;
                } else {
                    // M2ePro_TRANSLATIONS
                    // Product was not automatically Listed according to the List Rules in Synchronization Policy. Quantity of Magento Product is %product_qty% though in Synchronization Rules “Calculated Quantity” is set to more then  %template_min_qty%.
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Quantity of Magento Product is %product_qty% though in Synchronization Rules
                         “Calculated Quantity” is set to more then  %template_min_qty%.',
                        array(
                            '!template_min_qty' => $minQty,
                            '!product_qty' => $productQty,
                            '!date' => Mage::helper('M2ePro')->getCurrentGmtDate()
                        )
                    );
                }
            }

            if ($typeQty == Ess_M2ePro_Model_Buy_Template_Synchronization::LIST_QTY_BETWEEN) {
                if ($productQty >= $minQty && $productQty <= $maxQty) {
                    $result = true;
                } else {
                    // M2ePro_TRANSLATIONS
                    // Product was not automatically Listed according to the List Rules in Synchronization Policy. Quantity of Magento Product is %product_qty% though in Synchronization Rules “Calculated Quantity” is set between  %template_min_qty% and %template_max_qty%.
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Quantity of Magento Product is %product_qty% though in Synchronization Rules
                         “Calculated Quantity” is set between  %template_min_qty% and %template_max_qty%.',
                        array(
                            '!template_min_qty' => $minQty,
                            '!template_max_qty' => $maxQty,
                            '!product_qty' => $productQty,
                            '!date' => Mage::helper('M2ePro')->getCurrentGmtDate()
                        )
                    );
                }
            }

            if (!$result) {
                if (!empty($note)) {
                    $additionalData['synch_template_list_rules_note'] = $note;
                    $listingProduct->setSettings('additional_data', $additionalData)->save();
                }

                return false;
            }
        }

        if ($listingProduct->getSynchStatus() != Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_NEED &&
            $this->isTriedToList($listingProduct) &&
            $this->isChangeInitiatorOnlyInspector($listingProduct)
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isMeetRelistRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$listingProduct->isStopped()) {
            return false;
        }

        if (!$listingProduct->isRelistable()) {
            return false;
        }

        if ($listingProduct->isLockedObject('in_action')) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Buy_Listing_Product $buyListingProduct */
        $buyListingProduct = $listingProduct->getChildObject();

        $buySynchronizationTemplate = $buyListingProduct->getBuySynchronizationTemplate();

        if (!$buySynchronizationTemplate->isRelistMode()) {
            return false;
        }

        if ($buySynchronizationTemplate->isRelistFilterUserLock() &&
            $listingProduct->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER
        ) {
            return false;
        }

        $variationManager = $buyListingProduct->getVariationManager();

        if ($variationManager->isVariationProduct() && !$variationManager->isVariationProductMatched()) {
            return false;
        }

        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        if ($buySynchronizationTemplate->isRelistStatusEnabled()) {

            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                return false;
            } else if ($variationManager->isVariationProduct() && $variationManager->isVariationProductMatched()) {

                $temp = $variationResource->isAllStatusesDisabled(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    return false;
                }
            }
        }

        if ($buySynchronizationTemplate->isRelistIsInStock()) {

            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                return false;
            } else if ($variationManager->isVariationProduct() && $variationManager->isVariationProductMatched()) {

                $temp = $variationResource->isAllDoNotHaveStockAvailabilities(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    return false;
                }
            }
        }

        if ($buySynchronizationTemplate->isRelistWhenQtyMagentoHasValue()) {

            $result = false;
            $productQty = (int)$buyListingProduct->getQty(true);

            $typeQty = (int)$buySynchronizationTemplate->getRelistWhenQtyMagentoHasValueType();
            $minQty = (int)$buySynchronizationTemplate->getRelistWhenQtyMagentoHasValueMin();
            $maxQty = (int)$buySynchronizationTemplate->getRelistWhenQtyMagentoHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Buy_Template_Synchronization::RELIST_QTY_LESS &&
                $productQty <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Buy_Template_Synchronization::RELIST_QTY_MORE &&
                $productQty >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Buy_Template_Synchronization::RELIST_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                $result = true;
            }

            if (!$result) {
                return false;
            }
        }

        if ($buySynchronizationTemplate->isRelistWhenQtyCalculatedHasValue()) {

            $result = false;
            $productQty = (int)$buyListingProduct->getQty(false);

            $typeQty = (int)$buySynchronizationTemplate->getRelistWhenQtyCalculatedHasValueType();
            $minQty = (int)$buySynchronizationTemplate->getRelistWhenQtyCalculatedHasValueMin();
            $maxQty = (int)$buySynchronizationTemplate->getRelistWhenQtyCalculatedHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Buy_Template_Synchronization::RELIST_QTY_LESS &&
                $productQty <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Buy_Template_Synchronization::RELIST_QTY_MORE &&
                $productQty >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Buy_Template_Synchronization::RELIST_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                $result = true;
            }

            if (!$result) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isMeetStopRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$listingProduct->isListed()) {
            return false;
        }

        if (!$listingProduct->isStoppable()) {
            return false;
        }

        if ($listingProduct->isLockedObject('in_action')) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Buy_Listing_Product $buyListingProduct */
        $buyListingProduct = $listingProduct->getChildObject();
        $buySynchronizationTemplate = $buyListingProduct->getBuySynchronizationTemplate();
        $variationManager = $buyListingProduct->getVariationManager();
        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        if ($buySynchronizationTemplate->isStopStatusDisabled()) {

            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                return true;
            } else if ($variationManager->isVariationProduct() && $variationManager->isVariationProductMatched()) {

                $temp = $variationResource->isAllStatusesDisabled(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    return true;
                }
            }
        }

        if ($buySynchronizationTemplate->isStopOutOfStock()) {

            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                return true;
            } else if ($variationManager->isVariationProduct() && $variationManager->isVariationProductMatched()) {

                $temp = $variationResource->isAllDoNotHaveStockAvailabilities(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    return true;
                }
            }
        }

        if ($buySynchronizationTemplate->isStopWhenQtyMagentoHasValue()) {

            $productQty = (int)$buyListingProduct->getQty(true);

            $typeQty = (int)$buySynchronizationTemplate->getStopWhenQtyMagentoHasValueType();
            $minQty = (int)$buySynchronizationTemplate->getStopWhenQtyMagentoHasValueMin();
            $maxQty = (int)$buySynchronizationTemplate->getStopWhenQtyMagentoHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Buy_Template_Synchronization::STOP_QTY_LESS &&
                $productQty <= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Buy_Template_Synchronization::STOP_QTY_MORE &&
                $productQty >= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Buy_Template_Synchronization::STOP_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                return true;
            }
        }

        if ($buySynchronizationTemplate->isStopWhenQtyCalculatedHasValue()) {

            $productQty = (int)$buyListingProduct->getQty(false);

            $typeQty = (int)$buySynchronizationTemplate->getStopWhenQtyCalculatedHasValueType();
            $minQty = (int)$buySynchronizationTemplate->getStopWhenQtyCalculatedHasValueMin();
            $maxQty = (int)$buySynchronizationTemplate->getStopWhenQtyCalculatedHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Buy_Template_Synchronization::STOP_QTY_LESS &&
                $productQty <= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Buy_Template_Synchronization::STOP_QTY_MORE &&
                $productQty >= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Buy_Template_Synchronization::STOP_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                return true;
            }
        }

        return false;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isMeetReviseGeneralRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$listingProduct->isListed()) {
            return false;
        }

        if (!$listingProduct->isRevisable()) {
            return false;
        }

        if ($listingProduct->isLockedObject('in_action')) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Buy_Listing_Product $buyListingProduct */
        $buyListingProduct = $listingProduct->getChildObject();
        $variationManager = $buyListingProduct->getVariationManager();

        if ($variationManager->isVariationProduct() && !$variationManager->isVariationProductMatched()) {
            return false;
        }

        return true;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isMeetReviseQtyRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$this->isMeetReviseGeneralRequirements($listingProduct)) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Buy_Listing_Product $buyListingProduct */
        $buyListingProduct = $listingProduct->getChildObject();

        $buySynchronizationTemplate = $buyListingProduct->getBuySynchronizationTemplate();

        if (!$buySynchronizationTemplate->isReviseWhenChangeQty()) {
            return false;
        }

        $isMaxAppliedValueModeOn = $buySynchronizationTemplate->isReviseUpdateQtyMaxAppliedValueModeOn();
        $maxAppliedValue = $buySynchronizationTemplate->getReviseUpdateQtyMaxAppliedValue();

        $productQty = $buyListingProduct->getQty();
        $channelQty = $buyListingProduct->getOnlineQty();

        // Check ReviseUpdateQtyMaxAppliedValue
        if ($isMaxAppliedValueModeOn && $productQty > $maxAppliedValue && $channelQty > $maxAppliedValue) {
            return false;
        }

        if ($productQty > 0 && $productQty != $channelQty) {
            return true;
        }

        return false;
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isMeetRevisePriceRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$this->isMeetReviseGeneralRequirements($listingProduct)) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Buy_Listing_Product $buyListingProduct */
        $buyListingProduct = $listingProduct->getChildObject();

        $buySynchronizationTemplate = $buyListingProduct->getBuySynchronizationTemplate();

        if (!$buySynchronizationTemplate->isReviseWhenChangePrice()) {
            return false;
        }

        return $this->checkRevisePricesRequirements(
            $buySynchronizationTemplate, $buyListingProduct->getOnlinePrice(), $buyListingProduct->getPrice()
        );
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isMeetReviseSynchReasonsRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $reasons = $listingProduct->getSynchReasons();
        if (empty($reasons)) {
            return false;
        }

        if (!$this->isMeetReviseGeneralRequirements($listingProduct)) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Buy_Listing_Product $buyListingProduct */
        $buyListingProduct = $listingProduct->getChildObject();

        $synchronizationTemplate = $buyListingProduct->getSynchronizationTemplate();
        $buySynchronizationTemplate = $buyListingProduct->getBuySynchronizationTemplate();

        foreach ($reasons as $reason) {

            $method = 'isRevise'.ucfirst($reason);

            if (method_exists($synchronizationTemplate, $method)) {
                if ($synchronizationTemplate->$method()) {
                    return true;
                }

                continue;
            }

            if (method_exists($buySynchronizationTemplate, $method)) {
                if ($buySynchronizationTemplate->$method()) {
                    return true;
                }

                continue;
            }
        }

        return false;
    }

    //########################################

    private function checkRevisePricesRequirements(
        Ess_M2ePro_Model_Buy_Template_Synchronization $buySynchronizationTemplate,
        $onlinePrice, $productPrice
    ) {
        if ((float)$onlinePrice == (float)$productPrice) {
            return false;
        }

        if ((float)$onlinePrice <= 0) {
            return true;
        }

        if ($buySynchronizationTemplate->isReviseUpdatePriceMaxAllowedDeviationModeOff()) {
            return true;
        }

        $deviation = round(abs($onlinePrice - $productPrice) / $onlinePrice * 100, 2);

        return $deviation > $buySynchronizationTemplate->getReviseUpdatePriceMaxAllowedDeviation();
    }

    //########################################
}