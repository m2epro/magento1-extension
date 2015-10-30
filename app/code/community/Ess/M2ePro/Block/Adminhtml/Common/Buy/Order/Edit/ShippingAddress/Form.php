<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Order_Edit_ShippingAddress_Form extends Mage_Adminhtml_Block_Widget_Form
{
    private $order;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('buyOrderEditShippingAddressForm');
        // ---------------------------------------

        $this->setTemplate('M2ePro/common/buy/order/edit/shipping_address.phtml');
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

        $this->setData('buyer_name', $this->order->getData('buyer_name'));
        $this->setData('buyer_email', $this->order->getData('buyer_email'));
        $this->setData('address', $this->order->getShippingAddress()->getData());
        $this->setData('region_code', $regionCode);
        $this->setData('company', $this->order->getChildObject()->getBillingAddress()->getData('company'));
        $this->setData('phone', $this->order->getChildObject()->getBillingAddress()->getData('phone'));

        return parent::_beforeToHtml();
    }

    //########################################
}