<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_Sku_Existence
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Validator
{
    private $existenceResult = array();

    //########################################

    /**
     * @param array $result
     * @return $this
     */
    public function setExistenceResult(array $result)
    {
        $this->existenceResult = $result;
        return $this;
    }

    //########################################

    /**
     * @return bool
     */
    public function validate()
    {
        if (empty($this->existenceResult['asin'])) {
            return true;
        }

        if (empty($this->existenceResult['info'])) {
            // M2ePro_TRANSLATIONS
            // There is an unexpected error appeared during the process of linking Magento Product to Amazon Product. The data was not sent back from Amazon.
            $this->addMessage(
                'There is an unexpected error appeared during the process of linking Magento Product
                 to Amazon Product. The data was not sent back from Amazon.'
            );
        }

        if (!$this->getVariationManager()->isRelationMode()) {
            $this->processSimpleOrIndividualProduct();
        }

        if ($this->getVariationManager()->isRelationParentType()) {
            $this->processParentProduct();
        }

        if ($this->getVariationManager()->isRelationChildType()) {
            $this->processChildProduct();
        }

        return false;
    }

    //########################################

    private function processSimpleOrIndividualProduct()
    {
        $asin = $this->existenceResult['asin'];
        $info = $this->existenceResult['info'];

        if (!empty($info['type']) && $info['type'] == 'parent') {
// M2ePro_TRANSLATIONS
// Product cannot be Listed because in your Inventory the provided SKU "%sku%" is assigned to the Parent Product (ASIN: "%asin%") while you are trying to List a Child or Simple Product. Please check the Settings and try again.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'Product cannot be Listed because in your Inventory the provided SKU %sku%
                     is assigned to the Parent Product (ASIN/ISBN: "%asin%") while you are trying to List a Child or
                     Simple Product. Please check the Settings and try again.',
                    array('!sku' => $this->data['sku'], '!asin' => $asin)
                )
            );

            return;
        }

        if ($this->getAmazonListingProduct()->getGeneralId() &&
            $this->getAmazonListingProduct()->getGeneralId() != $asin
        ) {
// M2ePro_TRANSLATIONS
// Product cannot be Listed because in your Inventory the provided SKU "%sku%" is assigned to the Product with different ASIN/ISBN (%asin%). Please check the Settings and try again.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'Product cannot be Listed because in your Inventory the provided SKU "%sku%" is assigned
                     to the Product with different ASIN/ISBN (%asin%). Please check the Settings and try again.',
                    array('!sku' => $this->data['sku'], '!asin' => $asin)
                )
            );

            return;
        }

        $this->link($asin, $this->data['sku']);
    }

    private function processParentProduct()
    {
        $asin = $this->existenceResult['asin'];
        $info = $this->existenceResult['info'];

        if (empty($info['type']) || $info['type'] != 'parent') {
// M2ePro_TRANSLATIONS
// Product cannot be Listed because in your Inventory the provided SKU "%sku%" is assigned to the Child or Simple Product (ASIN/ISBN: "%asin%") while you want to list Parent Product. Please check the Settings and try again.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'Product cannot be Listed because in your Inventory the provided SKU "%sku%" is assigned
                     to the Child or Simple Product (ASIN/ISBN: "%asin%") while you want to list Parent Product.
                     Please check the Settings and try again.',
                    array('!sku' => $this->data['sku'], '!asin' => $asin)
                )
            );

            return;
        }

        if (!empty($info['bad_parent'])) {
// M2ePro_TRANSLATIONS
// Product cannot be Listed because working with Amazon Parent Product (ASIN/ISBN: "%asin%") found by SKU "%sku%" is limited due to Amazon API restrictions.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'Product cannot be Listed because working with Amazon Parent Product (ASIN/ISBN: "%asin%")
                     found by SKU "%sku%" is limited due to Amazon API restrictions.',
                    array('!sku' => $this->data['sku'], '!asin' => $asin)
                )
            );

            return;
        }

        $magentoAttributes = $this->getVariationManager()->getTypeModel()->getProductAttributes();

        if (count($magentoAttributes) != count($info['variation_attributes'])) {
// M2ePro_TRANSLATIONS
// Product cannot be Listed because the number of Variation Attributes of the Amazon Parent Product (ASIN/ISBN: "%asin%") found by SKU "%sku%" does not match the number of Variation Attributes of the Magento Parent Product.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'Product cannot be Listed because the number of Variation Attributes of
                     the Amazon Parent Product (ASIN/ISBN: "%asin%") found by SKU "%sku%" does not match the number of
                     Variation Attributes of the Magento Parent Product.',
                    array('!sku' => $this->data['sku'], '!asin' => $asin)
                )
            );

            return;
        }

        $this->link($this->existenceResult['asin'], $this->data['sku']);
    }

    private function processChildProduct()
    {
        $asin = $this->existenceResult['asin'];
        $info = $this->existenceResult['info'];

        if (empty($info['type']) || $info['type'] != 'child') {
// M2ePro_TRANSLATIONS
// Product cannot be Listed because Product found on Amazon (ASIN/ISBN: "%asin%") by SKU "%sku%" is not a Child Product.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'Product cannot be Listed because Product found on Amazon (ASIN/ISBN: "%asin%") by SKU "%sku%"
                     is not a Child Product.',
                    array('!sku' => $this->data['sku'], '!asin' => $asin)
                )
            );

            return;
        }

        if (!empty($info['bad_parent'])) {
// M2ePro_TRANSLATIONS
// Product cannot be Listed because Product found on Amazon (ASIN/ISBN: "%asin%") by SKU "%sku%" is a Child Product of the Parent Product (ASIN/ISBN: "%parent_asin%") access to which limited by Amazon API restriction.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'Product cannot be Listed because Item found on Amazon (ASIN/ISBN: "%asin%") by SKU "%sku%"
                     is a Child Product of the Parent Product (ASIN/ISBN: "%parent_asin%")
                     access to which limited by Amazon API restriction.',
                    array('!sku' => $this->data['sku'], '!asin' => $asin, '!parent_asin' => $info['parent_asin'])
                )
            );

            return;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $parentAmazonListingProduct */
        $parentAmazonListingProduct = $this->getVariationManager()
            ->getTypeModel()
            ->getParentListingProduct()
            ->getChildObject();

        if ($parentAmazonListingProduct->getGeneralId() != $info['parent_asin']) {
// M2ePro_TRANSLATIONS
// Product cannot be Listed because in your Inventory the provided SKU "%sku%" is assigned to the Amazon Child Product (ASIN/ISBN: "%asin%") related to the Amazon Parent Product (ASIN/ISBN: "%parent_asin%") with different ASIN. Please check the Settings and try again.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'Product cannot be Listed because in your Inventory the provided SKU "%sku%" is assigned
                     to the Amazon Child Product (ASIN/ISBN: "%asin%") related to the Amazon Parent Product
                     (ASIN/ISBN: "%parent_asin%") with different ASIN/ISBN. Please check the Settings and try again.',
                    array('!sku' => $this->data['sku'], '!asin' => $asin, '!parent_asin' => $info['parent_asin'])
                )
            );

            return;
        }

        $generalId = $this->getAmazonListingProduct()->getGeneralId();

        if (!empty($generalId) && $generalId != $asin) {
// M2ePro_TRANSLATIONS
// Product cannot be Listed because in your Inventory the provided SKU "%sku%" is assigned to the Amazon Product (ASIN/ISBN: "%asin%") with different ASIN/ISBN. Please check the Settings and try again.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'Product cannot be Listed because in your Inventory the provided SKU "%sku%" is
                    assigned to the Amazon Product (ASIN/ISBN: "%asin%") with different ASIN/ISBN.
                    Please check the Settings and try again.',
                    array('!sku' => $this->data['sku'], '!asin' => $asin)
                )
            );

            return;
        }

        $parentChannelVariations = $parentAmazonListingProduct->getVariationManager()
            ->getTypeModel()
            ->getChannelVariations();

        if (!isset($parentChannelVariations[$asin])) {
// M2ePro_TRANSLATIONS
// Product cannot be Listed because the respective Parent has no Child Product with required combination of the Variation Attributes values.
            $this->addMessage(
                'Product cannot be Listed because the respective Parent has no Child Product
                 with required combination of the Variation Attributes values.'
            );

            return;
        }

        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $childProductCollection */
        $childProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $childProductCollection->addFieldToFilter('variation_parent_id', $parentAmazonListingProduct->getId());
        if (!empty($generalId)) {
            $childProductCollection->addFieldToFilter('general_id', array('neq' => $generalId));
        }

        $childGeneralIds = $childProductCollection->getColumnValues('general_id');

        if (in_array($asin, $childGeneralIds)) {
// M2ePro_TRANSLATIONS
// Product cannot be Listed because ASIN/ISBN "%asin%" found on Amazon by SKU "%sku%" has already been used by you to link another Magento Product to Amazon Product.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'Product cannot be Listed because ASIN/ISBN "%asin%" found on Amazon by SKU "%sku%" has already been
                     used by you to link another Magento Product to Amazon Product.',
                    array('!sku' => $this->data['sku'], '!asin' => $asin)
                )
            );

            return;
        }

        $this->link($this->existenceResult['asin'], $this->data['sku']);
    }

    //########################################

    private function link($generalId, $sku)
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Linking $linkingObject */
        $linkingObject = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Type_List_Linking');
        $linkingObject->setListingProduct($this->getListingProduct());
        $linkingObject->setGeneralId($generalId);
        $linkingObject->setSku($sku);

        if ($linkingObject->link()) {
// M2ePro_TRANSLATIONS
// Product has been found by SKU "%sku%" in your Inventory and successfully linked.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'Product has been found by SKU "%sku%" in your Inventory and successfully linked.',
                    array('!sku' => $sku)
                ),
                Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS
            );

            return;
        }

// M2ePro_TRANSLATIONS
// Unexpected error during process of linking by SKU "%sku%". The required SKU has been found but the data is not sent back. Please try again.
        $this->addMessage(
            Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                'Unexpected error during process of linking by SKU "%sku%".
                 The required SKU has been found but the data is not sent back. Please try again.',
                array('!sku' => $sku)
            )
        );
    }

    //########################################
}