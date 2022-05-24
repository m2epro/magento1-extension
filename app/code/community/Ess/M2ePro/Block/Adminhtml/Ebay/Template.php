<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayTemplate');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_template';

        $this->_headerText = '';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton(
            'add',
            array(
                'id'      => 'add_policy',
                'label'   => Mage::helper('M2ePro')->__('Add Policy'),
                'onclick' => '',
                'class'   => 'add add-button-drop-down'
            )
        );
    }

    //########################################

    protected function getAddButtonJavascript()
    {
        $data = array(
            'target_css_class' => 'add-button-drop-down',
            'items'            => array(
                array(
                    'id'    => 'add_policy_payment',
                    'url'   => $this->getUrl(
                        '*/adminhtml_ebay_template/new',
                        array(
                            'nick' => Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT,
                            'back' => true
                        )
                    ),
                    'label' => Mage::helper('M2ePro')->__('Payment')
                ),
                array(
                    'id'    => 'add_policy_shipping',
                    'url'   => $this->getUrl(
                        '*/adminhtml_ebay_template/new',
                        array(
                            'nick' => Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING,
                            'back' => true
                        )
                    ),
                    'label' => Mage::helper('M2ePro')->__('Shipping')
                ),
                array(
                    'id'    => 'add_policy_return',
                    'url'   => $this->getUrl(
                        '*/adminhtml_ebay_template/new',
                        array(
                            'nick' => Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN_POLICY,
                            'back' => true
                        )
                    ),
                    'label' => Mage::helper('M2ePro')->__('Return')
                ),
                array(
                    'id'    => 'add_policy_selling',
                    'url'   => $this->getUrl(
                        '*/adminhtml_ebay_template/new',
                        array(
                            'nick' => Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT,
                            'back' => true
                        )
                    ),
                    'label' => Mage::helper('M2ePro')->__('Selling')
                ),
                array(
                    'id'    => 'add_policy_description',
                    'url'   => $this->getUrl(
                        '*/adminhtml_ebay_template/new',
                        array(
                            'nick' => Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION,
                            'back' => true
                        )
                    ),
                    'label' => Mage::helper('M2ePro')->__('Description')
                ),
                array(
                    'id'    => 'add_policy_synchronization',
                    'url'   => $this->getUrl(
                        '*/adminhtml_ebay_template/new',
                        array(
                            'nick' => Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION,
                            'back' => true
                        )
                    ),
                    'label' => Mage::helper('M2ePro')->__('Synchronization')
                )
            )
        );
        $dropDownBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown');
        $dropDownBlock->setData($data);

        return $dropDownBlock->toHtml();
    }

    protected function _toHtml()
    {
        return $this->getAddButtonJavascript() . parent::_toHtml();
    }

    //########################################
}
