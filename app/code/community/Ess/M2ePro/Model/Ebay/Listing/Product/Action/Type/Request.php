<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request
{
    /**
     * @var array
     */
    private $requestsTypes = array(
        'selling',
        'description',
        'categories',
        'variations',
        'shipping',
        'payment',
        'return'
    );

    /**
     * @var array[Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Abstract]
     */
    private $requests = array();

    //########################################

    /**
     * @return array
     */
    public function getData()
    {
        $this->initializeVariations();
        $this->beforeBuildDataEvent();

        $data = $this->getActionData();

        $data = $this->prepareFinalData($data);
        $this->collectRequestsWarningMessages();

        return $data;
    }

    // ---------------------------------------

    abstract protected function getActionData();

    //########################################

    protected function initializeVariations()
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation_Updater $variationUpdater */
        $variationUpdater = Mage::getModel('M2ePro/Ebay_Listing_Product_Variation_Updater');
        $variationUpdater->process($this->getListingProduct());
        $variationUpdater->afterMassProcessEvent();

        $isVariationItem = $this->getEbayListingProduct()->isVariationsReady();

        $this->setIsVariationItem($isVariationItem);

        $validateVariationsKey = Ess_M2ePro_Model_Ebay_Listing_Product_Variation_Updater::VALIDATE_MESSAGE_DATA_KEY;

        if ($this->getListingProduct()->hasData($validateVariationsKey)) {

            $this->addWarningMessage(
                Mage::helper('M2ePro')->__(
                    $this->getListingProduct()->getData($validateVariationsKey)
                )
            );

            $this->getListingProduct()->unsetData($validateVariationsKey);
        }
    }

    protected function beforeBuildDataEvent() {}

    // ---------------------------------------

    protected function prepareFinalData(array $data)
    {
        $data['is_eps_ebay_images_mode'] = $this->getIsEpsImagesMode();
        $data['upload_images_mode'] = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/description/', 'upload_images_mode'
        );

        if (!isset($data['out_of_stock_control'])) {
            $data['out_of_stock_control'] = $this->getOutOfStockControlMode();
        }

        $data = $this->replaceVariationSpecificsNames($data);
        $data = $this->replaceHttpsToHttpOfImagesUrls($data);
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

        $additionalData = $this->getListingProduct()->getAdditionalData();

        if (empty($additionalData['variations_specifics_replacements'])) {
            return $data;
        }

        $data = $this->doReplaceVariationSpecifics($data, $additionalData['variations_specifics_replacements']);
        return $data;
    }

    protected function replaceHttpsToHttpOfImagesUrls(array $data)
    {
        if ($data['is_eps_ebay_images_mode'] === false ||
            (is_null($data['is_eps_ebay_images_mode']) &&
                $data['upload_images_mode'] ==
                    Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Description::UPLOAD_IMAGES_MODE_SELF)) {
            return $data;
        }

        if (isset($data['images']['images'])) {
            foreach ($data['images']['images'] as &$imageUrl) {
                $imageUrl = str_replace('https://', 'http://', $imageUrl);
            }
        }

        if (isset($data['variation_image']['images'])) {
            foreach ($data['variation_image']['images'] as $attribute => &$imagesUrls) {
                foreach ($imagesUrls as &$imageUrl) {
                    $imageUrl = str_replace('https://', 'http://', $imageUrl);
                }
            }
        }

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

    /**
     * In M2e Pro version <= 6.4.1 value MPN - 'Does Not Apply' was sent for variations always
     * (even if Brand was Unbranded). Due to eBay specific we can not stop sending it. So, for "old" items we need
     * set 'Does Not Apply', if real MPN is empty. New items has 'without_mpn_variation_issue' in additional data
     * (set by list response), it means that item was listed after fixing this issue.
     *
     * @param array $data
     * @return array
     */
    protected function resolveVariationMpnIssue(array $data)
    {
        if (!$this->getIsVariationItem()) {
            return $data;
        }

        $additionalData = $this->getListingProduct()->getAdditionalData();
        if (!empty($additionalData['without_mpn_variation_issue'])) {
            $data['without_mpn_variation_issue'] = true;
            return $data;
        }

        foreach ($data['variation'] as &$variationData) {
            if (!empty($variationData['details']['mpn'])) {
                continue;
            }

            if (!isset($additionalData['is_variation_mpn_filled']) ||
                $additionalData['is_variation_mpn_filled'] === true
            ) {
                $variationData['details']['mpn'] = Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Description::
                PRODUCT_DETAILS_DOES_NOT_APPLY;
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

        foreach ($data['variation'] as &$variationItem) {
            foreach ($replacements as $findIt => $replaceBy) {

                if (!isset($variationItem['specifics'][$findIt])) {
                   continue;
                }

                $variationItem['specifics'][$replaceBy] = $variationItem['specifics'][$findIt];
                unset($variationItem['specifics'][$findIt]);
            }
        }

        foreach ($replacements as $findIt => $replaceBy) {

            if (!isset($data['variations_sets'][$findIt])) {
                continue;
            }

            $data['variations_sets'][$replaceBy] = $data['variations_sets'][$findIt];
            unset($data['variations_sets'][$findIt]);

            // M2ePro_TRANSLATIONS
            // The Variational Attribute Label "%replaced_it%" was changed to "%replaced_by%". For Item Specific "%replaced_by%" you select an Attribute by which your Variational Item varies. As it is impossible to send a correct Value for this Item Specific, it’s Label will be used as Variational Attribute Label instead of "%replaced_it%". This replacement cannot be edit in future by Relist/Revise Actions.
            $this->addWarningMessage(
                Mage::helper('M2ePro')->__(
                    'The Variational Attribute Label "%replaced_it%" was changed to "%replaced_by%". For Item Specific
                    "%replaced_by%" you select an Attribute by which your Variational Item varies. As it is impossible
                    to send a correct Value for this Item Specific, it’s Label will be used as Variational Attribute
                    Label instead of "%replaced_it%". This replacement cannot be edit in future by
                    Relist/Revise Actions.',
                    $findIt, $replaceBy
                )
            );
        }

        return $data;
    }

    // ---------------------------------------

    protected function collectRequestsWarningMessages()
    {
        foreach ($this->requestsTypes as $requestType) {

            $messages = $this->getRequest($requestType)->getWarningMessages();

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
            return NULL;
        }

        return $additionalData['is_eps_ebay_images_mode'];
    }

    protected function getOutOfStockControlMode()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();

        if (!isset($additionalData['out_of_stock_control'])) {
            return NULL;
        }

        return $additionalData['out_of_stock_control'];
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Selling
     */
    public function getRequestSelling()
    {
        return $this->getRequest('selling');
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Description
     */
    public function getRequestDescription()
    {
        return $this->getRequest('description');
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Variations
     */
    public function getRequestVariations()
    {
        return $this->getRequest('variations');
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Categories
     */
    public function getRequestCategories()
    {
        return $this->getRequest('categories');
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Payment
     */
    public function getRequestPayment()
    {
        return $this->getRequest('payment');
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Shipping
     */
    public function getRequestShipping()
    {
        return $this->getRequest('shipping');
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Return
     */
    public function getRequestReturn()
    {
        return $this->getRequest('return');
    }

    //########################################

    /**
     * @param $type
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Abstract
     */
    private function getRequest($type)
    {
        if (!isset($this->requests[$type])) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Abstract $request */
            $request = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Request_'.ucfirst($type));

            $request->setParams($this->getParams());
            $request->setListingProduct($this->getListingProduct());
            $request->setIsVariationItem($this->getIsVariationItem());
            $request->setConfigurator($this->getConfigurator());

            $this->requests[$type] = $request;
        }

        return $this->requests[$type];
    }

    //########################################
}