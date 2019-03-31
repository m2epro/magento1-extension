<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Order_Edit_ShippingAddress_Form extends Mage_Adminhtml_Block_Widget_Form
{
    private $order;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartOrderEditShippingAddressForm');
        // ---------------------------------------

        $this->setTemplate('M2ePro/walmart/order/edit/shipping_address.phtml');
        $this->order = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        try {
            $regionCode = $this->order->getShippingAddress()->getRegionCode();
        } catch (Exception $e) {
            $regionCode = null;
        }

        $state = $this->order->getShippingAddress()->getState();

        if (empty($regionCode) && !empty($state)) {
            $regionCode = $this->order->getShippingAddress()->getState();
        }

        $this->setData('countries', Mage::helper('M2ePro/Magento')->getCountries());
        $this->setData('buyer_name', $this->order->getData('buyer_name'));
        $this->setData('buyer_email', $this->order->getData('buyer_email'));
        $this->setData('address', $this->order->getShippingAddress()->getData());
        $this->setData('region_code', $regionCode);

        return parent::_beforeToHtml();
    }

    //########################################
}