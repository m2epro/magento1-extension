<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request
{
    /**
     * @var array
     */
    protected $_dataTypes = array(
        'general',
        'qty',
        'price',
        'title',
        'subtitle',
        'description',
        'images',
        'variations',
        'categories',
        'parts',
        'shipping',
        'payment',
        'return',
        'other',
    );

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Abstract[]
     */
    protected $_dataBuilders = array();

    //########################################

    /**
     * @return array
     */
    public function getData()
    {
        $this->initializeVariations();
        $this->beforeBuildDataEvent();

        $data = $this->getActionData();
        $this->collectMetadata();

        $data = $this->prepareFinalData($data);

        $this->afterBuildDataEvent($data);
        $this->collectDataBuildersWarningMessages();

        return $data;
    }

    protected function collectMetadata()
    {
        foreach ($this->_dataBuilders as $dataBuilder) {
            $this->_metaData = array_merge($this->_metaData, $dataBuilder->getMetaData());
        }
    }

    // ---------------------------------------

    abstract protected function getActionData();

    //########################################

    protected function initializeVariations()
    {
        $this->setIsVariationItem($this->getEbayListingProduct()->isVariationsReady());
    }

    protected function beforeBuildDataEvent()
    {
        return null;
    }

    protected function afterBuildDataEvent(array $data)
    {
        if ($this->getIsVariationItem() || isset($data['price_fixed'])) {
            $isListingTypeFixed = true;
        } elseif (isset($data['price_start'])) {
            $isListingTypeFixed = false;
        } elseif ($this->getEbayListingProduct()->isOnlineAuctionType() !== null) {
            $isListingTypeFixed = !$this->getEbayListingProduct()->isOnlineAuctionType();
        } else {
            $isListingTypeFixed = $this->getEbayListingProduct()->isListingTypeFixed();
        }

        $this->addMetaData('is_listing_type_fixed', $isListingTypeFixed);
    }

    // ---------------------------------------

    protected function prepareFinalData(array $data)
    {
        $data['is_eps_ebay_images_mode'] = $this->getIsEpsImagesMode();
        $data['upload_images_mode'] = Mage::helper('M2ePro/Component_Ebay_Configuration')->getUploadImagesMode();
        $data = $this->replaceVariationSpecificsNames($data);
        $data = $this->resolveVariationAndItemSpecificsConflict($data);
        $data = $this->removeVariationsInstances($data);
        $data = $this->resolveVariationMpnIssue($data);

        return $data;
    }

    protected function replaceVariationSpecificsNames(array $data)
    {
        if (!$this->getIsVariationItem() || !$this->getMagentoProduct()->isConfigurableType() ||
            empty($data['variations_sets']) || !is_array($data['variations_sets'])) {
            return $data;
        }

        $specificsReplacements = $this->getEbayListingProduct()->getVariationSpecificsReplacements();

        if (empty($specificsReplacements)) {
            return $data;
        }

        $data = $this->doReplaceVariationSpecifics($data, $specificsReplacements);
        $this->addMetaData('variations_specifics_replacements', $specificsReplacements);

        return $data;
    }

    protected function resolveVariationAndItemSpecificsConflict(array $data)
    {
        if (!$this->getIsVariationItem() ||
            empty($data['item_specifics']) || !is_array($data['item_specifics']) ||
            empty($data['variations_sets']) || !is_array($data['variations_sets'])) {
            return $data;
        }

        $variationAttributes = array_keys($data['variations_sets']);
        $variationAttributes = array_map('strtolower', $variationAttributes);

        foreach ($data['item_specifics'] as $key => $itemSpecific) {
            if (!in_array(strtolower($itemSpecific['name']), $variationAttributes)) {
                continue;
            }

            unset($data['item_specifics'][$key]);

            $this->addWarningMessage(
                Mage::helper('M2ePro')->__(
                    'Attribute "%specific_name%" will be shown as Variation Specific instead of Item Specific.',
                    $itemSpecific['name']
                )
            );
        }

        return $data;
    }

    protected function removeVariationsInstances(array $data)
    {
        if (isset($data['variation']) && is_array($data['variation'])) {
            foreach ($data['variation'] as &$variation) {
                unset($variation['_instance_']);
            }
        }

        return $data;
    }

    protected function removePriceFromVariationsIfNotAllowed(array $data)
    {
        if ($this->getConfigurator()->isPriceAllowed()) {
            return $data;
        }

        if (isset($data['variation']) && is_array($data['variation'])) {
            foreach ($data['variation'] as &$variation) {
                /** @var $ebayVariation Ess_M2ePro_Model_Ebay_Listing_Product_Variation */
                $ebayVariation = $variation['_instance_']->getChildObject();

                if ($ebayVariation->isAdd()) {
                    continue;
                }

                if (!$ebayVariation->getOnlineQtySold() &&
                    ($ebayVariation->isInactive() || $ebayVariation->isHidden())
                ) {
                    continue;
                }

                unset($variation['price']);
                unset($variation['price_discount_stp']);
                unset($variation['price_discount_map']);
            }
        }

        return $data;
    }

    /**
     * In M2e Pro version <= 6.4.1 value MPN - 'Does Not Apply' was sent for variations always
     * (even if Brand was Unbranded). Due to eBay specific we can not stop sending it. So, for "old" items we need
     * set 'Does Not Apply', if real MPN is empty. New items has 'without_mpn_variation_issue' in additional data
     * (set by list response), it means that item was listed after fixing this issue.
     *
     * 1) form variation MPN value (from additional only for list action)
     *
     *       TRY TO RETRIEVE FROM ADDITIONAL DATA OF EACH VARIATION
     *       IF EMPTY: TRY TO RETRIEVE FROM DESCRIPTION POLICY SETTINGS
     *
     * 2) prepare variation MPN value (skip this for list action)
     *
     *   - item variations MPN flag == unknown (variation MPN value only from settings)
     *     [-> item variations MPN flag == without MPN, item variations MPN flag == with MPN]
     *     - without_mpn_variation_issue == NULL
     *        empty variation MPN value -> set "Does Not Apply"
     *        filled variation MPN value -> do nothing
     *     - without_mpn_variation_issue == true
     *        empty variation MPN value -> do nothing
     *        filled variation MPN value -> do nothing
     *
     *   - item variations MPN flag == without MPN (variation MPN value only from settings)
     *      [-> item variations MPN flag == with MPN]
     *      - without_mpn_variation_issue == NULL / without_mpn_variation_issue == true
     *         empty variation MPN value -> do nothing
     *         filled variation MPNvalue  -> do nothing
     *
     *   - item variations MPN flag == with MPN (variation MPN value from additional or settings) [->]
     *     - without_mpn_variation_issue == NULL
     *        empty variation MPN value -> set "Does Not Apply"
     *        filled variation MPN value -> do nothing
     *   - without_mpn_variation_issue == true
     *        empty variation MPN value -> do nothing
     *        filled variation MPN value -> do nothing
     *
     * 3) after revise/relist error use getItem (skip this for list action)
     *
     *       CONDITIONS:
     *       VARIATIONAL PRODUCT == true
     *       VARIATIONS WERE SENT == true
     *       ANY ERROR FROM LIST [in_array]
     *       item variations MPN flag == unknown
     *
     *       ACTIONS:
     *       set item variations MPN flag according to the request
     *       set variations additional MPN values if need
     *
     * @param array $data
     * @return array
     */
    // todo PHPdoc should be changed
    protected function resolveVariationMpnIssue(array $data)
    {
        if (!$this->getIsVariationItem() || !$this->getConfigurator()->isVariationsAllowed()) {
            return $data;
        }

        $withoutMpnIssue = $this->getListingProduct()->getSetting('additional_data', 'without_mpn_variation_issue');
        $isMpnOnChannel  = $this->getListingProduct()->getSetting('additional_data', 'is_variation_mpn_filled');

        if ($withoutMpnIssue === true) {
            $data['without_mpn_variation_issue'] = true;
        }

        if (isset($data['variation']) && is_array($data['variation'])) {
            foreach ($data['variation'] as &$variationData) {

                /**
                 * Item was listed without MPN, but then the Description Policy setting was changed and
                 * MPN values are being send to eBay
                 */
                if (isset($variationData['details']['mpn']) && $isMpnOnChannel === false) {
                    unset($variationData['details']['mpn']);
                }

                if (!isset($variationData['details']['mpn']) &&
                    ($isMpnOnChannel === true || ($isMpnOnChannel === null && !$withoutMpnIssue))
                ) {
                    $variationData['details']['mpn']
                    = Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_General::PRODUCT_DETAILS_DOES_NOT_APPLY;
                }
            }
        }

        return $data;
    }

    protected function doReplaceVariationSpecifics(array $data, array $replacements)
    {
        if (isset($data['variation_image']['specific'])) {
            foreach ($replacements as $findIt => $replaceBy) {
                if ($data['variation_image']['specific'] == $findIt) {
                    $data['variation_image']['specific'] = $replaceBy;
                }
            }
        }

        if (isset($data['variation']) && is_array($data['variation'])) {
            foreach ($data['variation'] as &$variationItem) {
                foreach ($replacements as $findIt => $replaceBy) {
                    if (!isset($variationItem['specifics'][$findIt])) {
                        continue;
                    }

                    $variationItem['specifics'][$replaceBy] = $variationItem['specifics'][$findIt];
                    unset($variationItem['specifics'][$findIt]);
                }
            }

            unset($variationItem);

            foreach ($replacements as $findIt => $replaceBy) {
                if (!isset($data['variations_sets'][$findIt])) {
                    continue;
                }

                $data['variations_sets'][$replaceBy] = $data['variations_sets'][$findIt];
                unset($data['variations_sets'][$findIt]);

                $this->addWarningMessage(
                    Mage::helper('M2ePro')->__(
                        'The Variational Attribute Label "%replaced_it%" was changed to "%replaced_by%". For Item
                        Specific "%replaced_by%" you select an Attribute by which your Variational Item varies.
                        As it is impossible to send a correct Value for this Item Specific, it’s Label will be used
                        as Variational Attribute Label instead of "%replaced_it%".
                        This replacement cannot be edit in future by Relist/Revise Actions.',
                        $findIt, $replaceBy
                    )
                );
            }
        }

        return $data;
    }

    // ---------------------------------------

    protected function collectDataBuildersWarningMessages()
    {
        foreach ($this->_dataTypes as $requestType) {
            $messages = $this->getDataBuilder($requestType)->getWarningMessages();

            foreach ($messages as $message) {
                $this->addWarningMessage($message);
            }
        }
    }

    // ---------------------------------------

    protected function getIsEpsImagesMode()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();

        if (!isset($additionalData['is_eps_ebay_images_mode'])) {
            return null;
        }

        return $additionalData['is_eps_ebay_images_mode'];
    }

    //########################################

    /**
     * @return string
     */
    public function getSku()
    {
        $sku = $this->getEbayListingProduct()->getSku();

        if (strlen($sku) > Ess_M2ePro_Helper_Component_Ebay::ITEM_SKU_MAX_LENGTH) {
            $sku = Mage::helper('M2ePro')->hashString($sku, 'sha1', 'RANDOM_');
        }

        return $sku;
    }

    /**
     * @return array
     */
    public function getGeneralData()
    {
        if (!$this->getConfigurator()->isGeneralAllowed()) {
            return array();
        }

        $dataBuilder = $this->getDataBuilder('general');
        return $dataBuilder->getData();
    }

    /**
     * @return array
     */
    public function getQtyData()
    {
        if (!$this->getConfigurator()->isQtyAllowed()) {
            return array();
        }

        $dataBuilder = $this->getDataBuilder('qty');
        return $dataBuilder->getData();
    }

    /**
     * @return array
     */
    public function getPriceData()
    {
        if (!$this->getConfigurator()->isPriceAllowed()) {
            return array();
        }

        $dataBuilder = $this->getDataBuilder('price');
        return $dataBuilder->getData();
    }

    /**
     * @return array
     */
    public function getTitleData()
    {
        if (!$this->getConfigurator()->isTitleAllowed()) {
            return array();
        }

        $dataBuilder = $this->getDataBuilder('title');
        return $dataBuilder->getData();
    }

    /**
     * @return array
     */
    public function getSubtitleData()
    {
        if (!$this->getConfigurator()->isSubtitleAllowed()) {
            return array();
        }

        $dataBuilder = $this->getDataBuilder('subtitle');
        return $dataBuilder->getData();
    }

    /**
     * @return array
     */
    public function getDescriptionData()
    {
        if (!$this->getConfigurator()->isDescriptionAllowed()) {
            return array();
        }

        $dataBuilder = $this->getDataBuilder('description');
        return $dataBuilder->getData();
    }

    /**
     * @return array
     */
    public function getImagesData()
    {
        if (!$this->getConfigurator()->isImagesAllowed()) {
            return array();
        }

        $dataBuilder = $this->getDataBuilder('images');
        $data = $dataBuilder->getData();

        $this->addMetaData('images_data', $data);

        return $data;
    }

    /**
     * @return array
     */
    public function getCategoriesData()
    {
        if (!$this->getConfigurator()->isCategoriesAllowed()) {
            return array();
        }

        $dataBuilder = $this->getDataBuilder('categories');
        $data = $dataBuilder->getData();

        $this->addMetaData('categories_data', $data);

        return $data;
    }

    /**
     * @return array
     */
    public function getPartsData()
    {
        if (!$this->getConfigurator()->isPartsAllowed()) {
            return array();
        }

        $dataBuilder = $this->getDataBuilder('parts');
        $data = $dataBuilder->getData();
        $hash = $dataBuilder->getHash();

        $this->addMetaData('parts_data_hash', $hash);

        return $data;
    }

    /**
     * @return array
     */
    public function getPaymentData()
    {
        if (!$this->getConfigurator()->isPaymentAllowed()) {
            return array();
        }

        $dataBuilder = $this->getDataBuilder('payment');
        $data = $dataBuilder->getData();

        $this->addMetaData('payment_data', $data);

        return $data;
    }

    /**
     * @return array
     */
    public function getShippingData()
    {
        if (!$this->getConfigurator()->isShippingAllowed()) {
            return array();
        }

        $dataBuilder = $this->getDataBuilder('shipping');
        $data = $dataBuilder->getData();

        $this->addMetaData('shipping_data', $data);

        return $data;
    }

    /**
     * @return array
     */
    public function getReturnData()
    {
        if (!$this->getConfigurator()->isReturnAllowed()) {
            return array();
        }

        $dataBuilder = $this->getDataBuilder('return');
        $data = $dataBuilder->getData();

        $this->addMetaData('return_data', $data);

        return $data;
    }

    /**
     * @return array
     */
    public function getVariationsData()
    {
        if (!$this->getConfigurator()->isVariationsAllowed()) {
            return array();
        }

        $dataBuilder = $this->getDataBuilder('variations');
        return $dataBuilder->getData();
    }

    /**
     * @return array
     */
    public function getOtherData()
    {
        if (!$this->getConfigurator()->isOtherAllowed()) {
            return array();
        }

        $dataBuilder = $this->getDataBuilder('other');
        $data = $dataBuilder->getData();

        $this->addMetaData('other_data', $data);

        return $data;
    }

    //########################################

    /**
     * @param $type
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Abstract
     */
    protected function getDataBuilder($type)
    {
        if (!isset($this->_dataBuilders[$type])) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Abstract $dataBuilder */
            $dataBuilder = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_DataBuilder_'.ucfirst($type));

            $dataBuilder->setParams($this->getParams());
            $dataBuilder->setListingProduct($this->getListingProduct());
            $dataBuilder->setCachedData($this->getCachedData());

            $dataBuilder->setIsVariationItem($this->getIsVariationItem());

            $this->_dataBuilders[$type] = $dataBuilder;
        }

        return $this->_dataBuilders[$type];
    }

    //########################################
}
