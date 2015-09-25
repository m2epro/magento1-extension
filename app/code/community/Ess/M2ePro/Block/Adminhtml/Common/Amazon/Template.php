<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template extends Ess_M2ePro_Block_Adminhtml_Common_Template
{
    protected $nick = Ess_M2ePro_Helper_Component_Amazon::NICK;

    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('commonAmazonTemplate');
        //------------------------------

    }

    protected function getAddButtonJavascript()
    {
        $data = array(
            'target_css_class' => 'add-button-drop-down',
            'items'            => array(
                array(
                    'url'   => $this->getUrl(
                        '*/adminhtml_common_template/new',
                        array(
                            'channel' => $this->nick,
                            'type' => Ess_M2ePro_Block_Adminhtml_Common_Template_Grid::TEMPLATE_SELLING_FORMAT,
                        )
                    ),
                    'label' => Mage::helper('M2ePro')->__('Selling Format')
                ),
                array(
                    'url'   => $this->getUrl(
                        '*/adminhtml_common_template/new',
                        array(
                            'channel' => $this->nick,
                            'type' => Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_Grid::TEMPLATE_SHIPPING_OVERRIDE
                        )
                    ),
                    'label' => Mage::helper('M2ePro')->__('Shipping Override')
                ),
                array(
                    'url'   => $this->getUrl(
                        '*/adminhtml_common_template/new',
                        array(
                            'channel' => $this->nick,
                            'type' => Ess_M2ePro_Block_Adminhtml_Common_Template_Grid::TEMPLATE_SYNCHRONIZATION,
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

    // ########################################
}