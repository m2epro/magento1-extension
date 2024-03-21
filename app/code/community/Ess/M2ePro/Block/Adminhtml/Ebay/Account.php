<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayAccounts');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_account';
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
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton(
            'add-account',
            array(
                'label'   => Mage::helper('M2ePro')->__('Add Account'),
                'onclick' => '',
                'class'   => 'add add-account-drop-down',
            )
        );

    }

    protected function _toHtml()
    {
        return  $this->getAddAccountButtonHtml() . parent::_toHtml();
    }

    public function getAddAccountButtonHtml()
    {
        $data = array(
            'target_css_class' => 'add-account-drop-down',
            'items'            => $this->getAddAccountButtonDropDownItems()
        );

        $addAccountDropDownBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown');
        $addAccountDropDownBlock->setData($data);

        return $addAccountDropDownBlock->toHtml();
    }
    private function getAddAccountButtonDropDownItems()
    {
        $items = array();

        $url = $this->getUrl(
            '*/adminhtml_ebay_account/beforeGetSellApiToken',
            array(
                'mode' => Ess_M2ePro_Model_Ebay_Account::MODE_PRODUCTION
            )
        );

        $items[] = array(
            'url'    => $url,
            'target' => '_blank',
            'label'  => Mage::helper('M2ePro')->__('Live Account')
        );

        $url = $this->getUrl(
            '*/adminhtml_ebay_account/beforeGetSellApiToken',
            array(
                'mode' => Ess_M2ePro_Model_Ebay_Account::MODE_SANDBOX
            )
        );

        $items[] = array(
            'url'    => $url,
            'target' => '_blank',
            'label'  => Mage::helper('M2ePro')->__('Sandbox Account')
        );

        return $items;
    }
}
