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

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonTemplate');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_template';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = '';
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton('add', array(
            'label'     => Mage::helper('M2ePro')->__('Add Policy'),
            'onclick'   => '',
            'class'     => 'add add-button-drop-down'
        ));
        // -------------------------------------
    }

    public function getHeaderHtml()
    {
        $data = array(
            'target_css_class' => 'add-button-drop-down',
            'items'            => array(
                array(
                    'url'   => $this->getUrl(
                        '*/adminhtml_amazon_template/new',
                        array(
                            'type' => Ess_M2ePro_Block_Adminhtml_Amazon_Template_Grid::TEMPLATE_SELLING_FORMAT,
                        )
                    ),
                    'label' => Mage::helper('M2ePro')->__('Selling')
                ),
                array(
                    'url'   => $this->getUrl(
                        '*/adminhtml_amazon_template/new',
                        array(
                            'type' => Ess_M2ePro_Block_Adminhtml_Amazon_Template_Grid::TEMPLATE_DESCRIPTION
                        )
                    ),
                    'label' => Mage::helper('M2ePro')->__('Description')
                ),
                array(
                    'url'   => $this->getUrl(
                        '*/adminhtml_amazon_template/new',
                        array(
                            'type' => Ess_M2ePro_Block_Adminhtml_Amazon_Template_Grid::TEMPLATE_SYNCHRONIZATION,
                        )
                    ),
                    'label' => Mage::helper('M2ePro')->__('Synchronization')
                ),
                array(
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