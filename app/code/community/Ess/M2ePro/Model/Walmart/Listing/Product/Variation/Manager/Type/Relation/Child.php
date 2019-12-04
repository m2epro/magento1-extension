<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Child
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_PhysicalUnit
{
    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    protected $_parentListingProduct;

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getParentListingProduct()
    {
        if ($this->_parentListingProduct === null) {
            $parentListingProductId      = $this->getVariationManager()->getVariationParentId();
            $this->_parentListingProduct = Mage::helper('M2ePro/Component_Walmart')
                                               ->getObject('Listing_Product', $parentListingProductId);
        }

        return $this->_parentListingProduct;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Product
     */
    public function getWalmartParentListingProduct()
    {
        return $this->getParentListingProduct()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent
     */
    public function getParentTypeModel()
    {
        return $this->getWalmartParentListingProduct()->getVariationManager()->getTypeModel();
    }

    //########################################

    /**
     * @return array|mixed|null
     */
    public function getRealProductOptions()
    {
        $productOptions = $this->getProductOptions();

        $virtualProductAttributes = $this->getParentTypeModel()->getVirtualProductAttributes();
        if (empty($virtualProductAttributes)) {
            return $productOptions;
        }

        $realProductOptions = array();
        foreach ($productOptions as $attribute => $value) {
            if (isset($virtualProductAttributes[$attribute])) {
                continue;
            }

            $realProductOptions[$attribute] = $value;
        }

        return $realProductOptions;
    }

    //########################################

    /**
     * @param array $options
     */
    public function setChannelVariation(array $options)
    {
        $this->unsetChannelVariation();

        $this->setChannelOptions($options, false);

        $this->getListingProduct()->save();
    }

    public function unsetChannelVariation()
    {
        $this->setChannelOptions(array(), false);
        $this->getListingProduct()->save();
    }

    //########################################

    /**
     * @return mixed|null
     */
    public function getChannelOptions()
    {
        return $this->getListingProduct()->getSetting('additional_data', 'variation_channel_options', array());
    }

    /**
     * @return array|mixed|null
     */
    public function getRealChannelOptions()
    {
        $channelOptions = $this->getChannelOptions();

        $virtualChannelAttributes = $this->getParentTypeModel()->getVirtualChannelAttributes();
        if (empty($virtualChannelAttributes)) {
            return $channelOptions;
        }

        $realChannelOptions = array();
        foreach ($channelOptions as $attribute => $value) {
            if (isset($virtualChannelAttributes[$attribute])) {
                continue;
            }

            $realChannelOptions[$attribute] = $value;
        }

        return $realChannelOptions;
    }

    // ---------------------------------------

    protected function setChannelOptions(array $options, $save = true)
    {
        $this->getListingProduct()->setSetting('additional_data', 'variation_channel_options', $options);
        $save && $this->getListingProduct()->save();
    }

    //########################################

    /**
     * @param array $matchedAttributes
     * @param bool
     */
    public function setCorrectMatchedAttributes(array $matchedAttributes, $save = true)
    {
        $this->getListingProduct()->setSetting(
            'additional_data', 'variation_correct_matched_attributes', $matchedAttributes
        );
        $save && $this->getListingProduct()->save();
    }

    /**
     * @return mixed
     */
    public function getCorrectMatchedAttributes()
    {
        return $this->getListingProduct()->getSetting(
            'additional_data', 'variation_correct_matched_attributes', array()
        );
    }

    // ---------------------------------------

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception
     */
    public function isActualMatchedAttributes()
    {
        $correctMatchedAttributes = $this->getCorrectMatchedAttributes();
        if (empty($correctMatchedAttributes)) {
            return true;
        }

        $parentTypeModel = $this->getWalmartParentListingProduct()->getVariationManager()->getTypeModel();
        $currentMatchedAttributes = $parentTypeModel->getMatchedAttributes();
        if (empty($currentMatchedAttributes)) {
            return false;
        }

        $diff = array_diff_assoc($correctMatchedAttributes, $currentMatchedAttributes);
        return empty($diff);
    }

    //########################################

    public function clearTypeData()
    {
        parent::clearTypeData();

        $this->unsetChannelVariation();

        $additionalData = $this->getListingProduct()->getAdditionalData();
        unset($additionalData['variation_channel_options']);
        unset($additionalData['variation_correct_matched_attributes']);
        $this->getListingProduct()->setSettings('additional_data', $additionalData);

        $this->getListingProduct()->save();
    }

    //########################################
}
