<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Template extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonTemplate');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_template';

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

    public function getHeaderHtml()
    {
        $data = array(
            'target_css_class' => 'add-button-drop-down',
            'items'            => array(
                array(
                    'id'    => 'add_policy_selling',
                    'url'   => $this->getUrl(
                        '*/adminhtml_amazon_template/new',
                        array(
                            'type' => Ess_M2ePro_Block_Adminhtml_Amazon_Template_Grid::TEMPLATE_SELLING_FORMAT,
                        )
                    ),
                    'label' => Mage::helper('M2ePro')->__('Selling')
                ),
                array(
                    'id'    => 'add_policy_synchronization',
                    'url'   => $this->getUrl(
                        '*/adminhtml_amazon_template/new',
                        array(
                            'type' => Ess_M2ePro_Block_Adminhtml_Amazon_Template_Grid::TEMPLATE_SYNCHRONIZATION,
                        )
                    ),
                    'label' => Mage::helper('M2ePro')->__('Synchronization')
                ),
                array(
                    'id'    => 'add_policy_shipping',
                    'url'   => $this->getUrl(
                        '*/adminhtml_amazon_template/new',
                        array(
                            'type' => Ess_M2ePro_Block_Adminhtml_Amazon_Template_Grid::TEMPLATE_SHIPPING
                        )
                    ),
                    'label' => Mage::helper('M2ePro')->__('Shipping')
                ),
            )
        );

        $data['items'][] = array(
            'id'    => 'add_policy_tax_code',
            'url'   => $this->getUrl(
                '*/adminhtml_amazon_template/new',
                array(
                    'type' => Ess_M2ePro_Block_Adminhtml_Amazon_Template_Grid::TEMPLATE_PRODUCT_TAX_CODE
                )
            ),
            'label' => Mage::helper('M2ePro')->__('Product Tax Code')
        );

        $dropDownBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown');
        $dropDownBlock->setData($data);

        return parent::getHeaderHtml() . $dropDownBlock->toHtml();
    }

    //########################################
}
