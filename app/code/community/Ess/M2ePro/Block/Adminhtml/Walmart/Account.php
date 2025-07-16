<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Account extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('account');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_walmart_account';
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

        // ---------------------------------------
        $this->_addButton(
            'add', array(
            'label'     => Mage::helper('M2ePro')->__('Add Account'),
            'onclick'   => '',
            'class'     => 'add add-account-drop-down',
            'id'        => 'add'
            )
        );
        // ---------------------------------------
    }

    //########################################

    protected function _toHtml()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Walmart_Account_CredentialsForm $credentialsForm */
        $credentialsForm = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_walmart_account_credentialsForm',
                '',
                array(
                    'with_title' => true,
                    'with_button' => true,
                    'form_id' => 'account_credentials'
                )
            );

        $javascript = <<<HTML
<script type="text/javascript">

    WalmartAccountObj = new WalmartAccount();

</script>
HTML;

        return $this->getAddAccountButtonHtml()
            . '<div id="account_credentials_form" style="display: none;">'
            . $credentialsForm->toHtml()
            . '</div>'
            . parent::_toHtml()
            . $javascript;
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
            '*/adminhtml_walmart_account_unitedStates_beforeGetToken/beforeGetToken',
            array(
                '_current' => true,
                'marketplace_id' => Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_US
            )
        );

        $items[] = array(
            'url'    => $url,
            'label'  => Mage::helper('M2ePro')->__('United States')
        );

        $addAccount = $this->getUrl(
            '*/adminhtml_walmart_account_canada_accountCreate/addAccount',
            array(
                'marketplace_id' => Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_CA
            )
        );
        $items[] = array(
            'url'     => '#',
            'id'      => 'account-ca',
            'label'   => Mage::helper('M2ePro')->__('Canada'),
            'onclick' => "WalmartAccountObj.openAccessDataPopup('{$addAccount}')"
        );

        return $items;
    }
}
