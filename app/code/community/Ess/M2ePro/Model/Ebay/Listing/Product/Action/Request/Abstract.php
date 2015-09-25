<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Abstract
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request
{
    // ########################################

    protected function searchNotFoundAttributes()
    {
        $this->getMagentoProduct()->clearNotFoundAttributes();
    }

    protected function processNotFoundAttributes($title)
    {
        $attributes = $this->getMagentoProduct()->getNotFoundAttributes();

        if (empty($attributes)) {
            return true;
        }

        $this->addNotFoundAttributesMessages($title, $attributes);

        return false;
    }

    // -----------------------------------------

    protected function addNotFoundAttributesMessages($title, array $attributes)
    {
        $attributesTitles = array();

        foreach ($attributes as $attribute) {
            $attributesTitles[] = Mage::helper('M2ePro/Magento_Attribute')
                                       ->getAttributeLabel($attribute,
                                                           $this->getListing()->getStoreId());
        }
    // M2ePro_TRANSLATIONS
    // %attribute_title%: Attribute(s) %attributes% were not found in this Product and its value was not sent.
        $this->addWarningMessage(
            Mage::helper('M2ePro')->__(
                '%attribute_title%: Attribute(s) %attributes% were not found'.
                ' in this Product and its value was not sent.',
                Mage::helper('M2ePro')->__($title), implode(',',$attributesTitles)
            )
        );
    }

    // ########################################
}