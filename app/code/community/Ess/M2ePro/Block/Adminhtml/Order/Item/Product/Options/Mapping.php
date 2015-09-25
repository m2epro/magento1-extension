<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Order_Item_Product_Options_Mapping extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
    private $magentoProduct = null;

    // ####################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/order/item/product/options/mapping.phtml');
    }

    /**
     * @return Ess_M2ePro_Model_Order_Item
     */
    public function getOrderItem()
    {
        return Mage::helper('M2ePro/Data_Global')->getValue('order_item');
    }

    public function getProductTypeHeader()
    {
        switch ($this->magentoProduct->getTypeId()) {
            case Ess_M2ePro_Model_Magento_Product::TYPE_BUNDLE:
                $title = Mage::helper('M2ePro')->__('Bundle Items');
                break;
            case Ess_M2ePro_Model_Magento_Product::TYPE_DOWNLOADABLE:
                $title = Mage::helper('M2ePro')->__('Links');
                break;
            case Ess_M2ePro_Model_Magento_Product::TYPE_GROUPED:
            case Ess_M2ePro_Model_Magento_Product::TYPE_CONFIGURABLE:
                $title = Mage::helper('M2ePro')->__('Associated Products');
                break;
            case Ess_M2ePro_Model_Magento_Product::TYPE_SIMPLE:
            default:
                $title = Mage::helper('M2ePro')->__('Custom Options');
                break;
        }

        return $title;
    }

    public function isMagentoOptionSelected(array $magentoOption, array $magentoOptionValue)
    {
        if ($this->magentoProduct->isGroupedType()) {
            $associatedProducts = $this->getOrderItem()->getAssociatedProducts();

            if (count($associatedProducts) == 1
                && count(array_diff($associatedProducts, $magentoOptionValue['product_ids'])) == 0
            ) {
                return true;
            }

            return false;
        }

        $associatedOptions = $this->getOrderItem()->getAssociatedOptions();

        if (isset($associatedOptions[(int)$magentoOption['option_id']])
            && $associatedOptions[(int)$magentoOption['option_id']] == $magentoOptionValue['value_id']
        ) {
            return true;
        }

        return false;
    }

    protected function _beforeToHtml()
    {
        //------------------------------
        $channelOptions = array();

        foreach ($this->getOrderItem()->getChildObject()->getVariationChannelOptions() as $attribute => $value) {
            $channelOptions[] = array('label' => $attribute, 'value' => $value);
        }

        $this->setData('channel_options', $channelOptions);
        //------------------------------

        //------------------------------
        $this->magentoProduct = $this->getOrderItem()->getMagentoProduct();

        $magentoOptions = array();
        $magentoVariations = $this->magentoProduct->getVariationInstance()->getVariationsTypeRaw();

        if ($this->magentoProduct->isGroupedType()) {

            $magentoOptionLabel = Mage::helper('M2ePro')
                ->__(Ess_M2ePro_Model_Magento_Product_Variation::GROUPED_PRODUCT_ATTRIBUTE_LABEL);

            $magentoOption = array(
                'option_id' => 0,
                'label' => $magentoOptionLabel,
                'values' => array()
            );

            foreach ($magentoVariations as $key => $magentoVariation) {
                $magentoOption['values'][] = array(
                    'value_id' => $key,
                    'label' => $magentoVariation->getName(),
                    'product_ids' => array($magentoVariation->getId())
                );
            }

            $magentoOptions[] = $magentoOption;

        } else {
            foreach ($magentoVariations as $magentoVariation) {
                $magentoOptionLabel = array_shift($magentoVariation['labels']);
                if (!$magentoOptionLabel) {
                    $magentoOptionLabel = Mage::helper('M2ePro')->__('N/A');
                }

                $magentoOption = array(
                    'option_id' => $magentoVariation['option_id'],
                    'label' => $magentoOptionLabel,
                    'values' => array()
                );

                foreach ($magentoVariation['values'] as $magentoOptionValue) {
                    $magentoValueLabel = array_shift($magentoOptionValue['labels']);
                    if (!$magentoValueLabel) {
                        $magentoValueLabel = Mage::helper('M2ePro')->__('N/A');
                    }

                    $magentoOption['values'][] = array(
                        'value_id' => $magentoOptionValue['value_id'],
                        'label' => $magentoValueLabel,
                        'product_ids' => $magentoOptionValue['product_ids']
                    );
                }

                $magentoOptions[] = $magentoOption;
            }
        }

        $this->setData('magento_options', $magentoOptions);
        //------------------------------

        //------------------------------
        $data = array(
            'id'      => 'product_options_mapping_submit_button',
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'class'   => 'product_options_mapping_submit_button submit',
            'onclick' => 'OrderEditItemHandlerObj.assignProductDetails();'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('product_options_mapping_submit_button',$buttonBlock);
        //------------------------------

        parent::_beforeToHtml();
    }

    // ####################################
}