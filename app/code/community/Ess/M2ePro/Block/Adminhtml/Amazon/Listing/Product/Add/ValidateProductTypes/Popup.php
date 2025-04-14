<?php

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Product_Add_ValidateProductTypes_Popup
    extends Mage_Adminhtml_Block_Widget
{
    protected function _toHtml()
    {
        $url = Mage::helper('M2ePro')->jsonEncode(
            array(
                'product_type_validation_view_result' =>  Mage::helper('adminhtml')
                    ->getUrl(
                        'M2ePro/adminhtml_amazon_productTypes_validation/viewProductTypeValidationDataResult'
                    ),
                'product_type_validation_url' =>  Mage::helper('adminhtml')
                    ->getUrl('M2ePro/adminhtml_amazon_productTypes_validation/validate'),
            )
        );

        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
                'validation_popup_modal_title' => Mage::helper('M2ePro')->escapeJs(
                    Mage::helper('M2ePro')->__(
                        'Product Data Validation'
                    )
                ),
            )
        );

        $validateProductTypeFunction = '';

        if ($this->getData('validate_product_type_function')) {
            $validateProductTypeFunction = $this->getData('validate_product_type_function');
        }

        $javascript = <<<HTML
<script type="text/javascript">
    if (typeof M2ePro == 'undefined') {
        M2ePro = {};
        M2ePro.url = {};
        M2ePro.formData = {};
        M2ePro.customData = {};
        M2ePro.text = {};
    }

    M2ePro.translator.add({$translations});

    M2ePro.url.add({$url});
    
    var validatorGridObject = new ProductTypeValidatorPopupClass($validateProductTypeFunction);
    window['productTypeValidatorPopupObjectName'] = validatorGridObject
</script>
HTML;

        return $javascript .  parent::_toHtml();
    }
}
