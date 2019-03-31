<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Order_MerchantFulfillment_Configuration
    extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();
        // Initialization block
        // ---------------------------------------
        $this->setId('amazonOrderMerchantFulfillmentConfiguration');
        // ---------------------------------------

        $this->setTemplate('M2ePro/amazon/order/merchant_fulfillment/configuration.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------
        $breadcrumb = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_order_merchantFulfillment_breadcrumb');
        $breadcrumb->setData('step', 1);
        $this->setChild('breadcrumb', $breadcrumb);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'class'   => 'next',
            'label'   => Mage::helper('M2ePro')->__('Continue'),
            'onclick' => "OrderMerchantFulfillmentHandlerObj.getShippingServicesAction()",
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('continue_button', $buttonBlock);
        // ---------------------------------------
    }

    //########################################

    public function getCountries()
    {
        $preparedCountries = array();
        $countries = Mage::helper('M2ePro/Magento')->getCountries();

        foreach ($countries as $country) {
            $preparedCountries[$country['iso2_code']] = $country['name'];
        }

        return $preparedCountries;
    }

    public function getOrderItems()
    {
        $data = array();
        $totalWeight = 0;

        foreach ($this->getData('order_items') as $parentOrderItem) {
            /**
             * @var $parentOrderItem Ess_M2ePro_Model_Order_Item
             */
            $parentOrderItem->getMagentoProduct();

            $orderItem = $parentOrderItem->getChildObject();

            $orderItemProduct = $parentOrderItem->getProduct();
            if (!is_null($orderItemProduct)) {
                $weight = $orderItemProduct->getTypeInstance()->getWeight();
                if (!is_null($weight)) {
                    $totalWeight += $weight;
                }
            }

            $data[] = array(
                'title'    => $orderItem->getTitle(),
                'sku'      => $orderItem->getSku(),
                'asin'     => $orderItem->getGeneralId(),
                'qty'      => $orderItem->getQtyPurchased(),
                'price'    => $orderItem->getPrice(),
                'currency' => $orderItem->getCurrency(),
            );
        }

        $this->setData('total_weight', $totalWeight);

        return $data;
    }

    public function getShippingOriginData()
    {
        return array(
            'country_id'   => Mage::getStoreConfig('shipping/origin/country_id'),
            'region_id'    => Mage::getStoreConfig('shipping/origin/region_id'),
            'postal_code'  => Mage::getStoreConfig('shipping/origin/postcode'),
            'city'         => Mage::getStoreConfig('shipping/origin/city'),
            'street_line1' => Mage::getStoreConfig('shipping/origin/street_line1'),
            'street_line2' => Mage::getStoreConfig('shipping/origin/street_line2'),
        );
    }

    public function getUserData()
    {
        $licenseFormDataRegistry = Mage::getModel('M2ePro/Registry')->load('/wizard/license_form_data/', 'key');
        return $licenseFormDataRegistry->getValueFromJson();
    }

    //########################################
}