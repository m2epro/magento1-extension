<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Block_Adminhtml_StoreSwitcher as StoreSwitcher;

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Create_General_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setId('amazonListingCreateGeneralForm');
    }

    //########################################

    protected function _prepareForm()
    {
        $form = new Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form(
            array(
                'id'      => 'edit_form',
                'method'  => 'post',
                'action'  => 'javascript:void(0)',
                'enctype' => 'multipart/form-data',
            )
        );

        $fieldset = $form->addFieldset(
            'general_fieldset',
            array(
                'legend'      => Mage::helper('M2ePro')->__('General'),
                'collapsable' => false
            )
        );

        $title = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing')->getSize() == 0 ? 'Default' : '';
        $accountId = '';
        $storeId = '';

        $sessionKey = 'amazon_listing_create';
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($sessionKey);

        isset($sessionData['title']) && $title = $sessionData['title'];
        isset($sessionData['account_id']) && $accountId = $sessionData['account_id'];
        isset($sessionData['store_id']) && $storeId = $sessionData['store_id'];

        $fieldset->addField(
            'title',
            'text',
            array(
                'name'     => 'title',
                'label'    => Mage::helper('M2ePro')->__('Title'),
                'value'    => $title,
                'required' => true,
                'class'    => 'M2ePro-listing-title',
                'tooltip'  => Mage::helper('M2ePro')->__(
                    'Create a descriptive and meaningful Title for your M2E Pro Listing.
                    <br/>This is used for reference within M2E Pro and will not appear on your Amazon Listings.'
                )
            )
        );

        $fieldset = $form->addFieldset(
            'amazon_settings_fieldset',
            array(
                'legend'      => Mage::helper('M2ePro')->__('Amazon Settings'),
                'collapsable' => false
            )
        );

        /** @var $accountsCollection Ess_M2ePro_Model_Resource_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account')
            ->setOrder('title', 'ASC');

        $accountsCollection->resetByType(
            Zend_Db_Select::COLUMNS,
            array(
                'value' => 'id',
                'label' => 'title'
            )
        );

        $accountSelectionDisabled = false;

        if ($this->getRequest()->getParam('account_id')) {
            $accountId = $this->getRequest()->getParam('account_id');
            $fieldset->addField(
                'account_id_hidden',
                'hidden',
                array(
                    'name' => 'account_id',
                    'value' => $accountId
                )
            );
            $accountSelectionDisabled = true;
        }

        $accounts = $accountsCollection->getConnection()->fetchAssoc($accountsCollection->getSelect());
        $accountSelect = new Varien_Data_Form_Element_Select(
            array(
                'html_id'  => 'account_id',
                'name'     => 'account_id',
                'value'    => $accountId,
                'values'   => $accounts,
                'required' => count($accounts) > 1,
                'disabled' => $accountSelectionDisabled
            )
        );
        $accountSelect->setForm($form);

        $style = $this->getRequest()->getParam('wizard', false) ? ' display: none;' : '';
        $fieldset->addField(
            'account_container',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::CUSTOM_CONTAINER,
            array(
                'label' => Mage::helper('M2ePro')->__('Account'),
                'text'  => <<<HTML
    <span id="account_label"></span>
    {$accountSelect->toHtml()}
HTML
            ,
                'after_element_html'  => Mage::getSingleton('core/layout')->createBlock('adminhtml/widget_button')
                    ->setData(
                        array(
                            'id'      => 'add_account_button',
                            'label'   => Mage::helper('M2ePro')->__('Add Another'),
                            'style'   => 'margin-left: 10px;' . $style,
                            'onclick' => '',
                            'class'   => 'primary'
                        )
                    )->toHtml(),
                'tooltip' => Mage::helper('M2ePro')->__('This is the user name of your Amazon Account.')
            )
        );

        $marketplacesCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Marketplace')
            ->setOrder('sorder', 'ASC')
            ->setOrder('title', 'ASC');

        /** @var $marketplacesCollection Ess_M2ePro_Model_Resource_Collection_Abstract */
        $marketplacesCollection->resetByType(
            Zend_Db_Select::COLUMNS,
            array(
                'value' => 'id',
                'label' => 'title',
                'url'   => 'url'
            )
        );

        $fieldset->addField(
            'marketplace_info',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::CUSTOM_CONTAINER,
            array(
                'label' => Mage::helper('M2ePro')->__('Marketplace'),
                'text'  => '<span id="marketplace_title"></span><p class="note" id="marketplace_url"></p>',
                'field_extra_attributes' => 'id="marketplace_info" style="display: none; margin-top: 0px"'
            )
        );

        $fieldset->addField(
            'marketplace_id',
            'hidden',
            array(
                'value' => ''
            )
        );

        $fieldset = $form->addFieldset(
            'magento_fieldset',
            array(
                'legend'      => Mage::helper('M2ePro')->__('Magento Settings'),
                'collapsable' => false
            )
        );

        $fieldset->addField(
            'store_id',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::STORE_SWITCHER,
            array(
                'name'             => 'store_id',
                'label'            => Mage::helper('M2ePro')->__('Magento Store View'),
                'value'            => $storeId,
                'style'            => 'display: initial;',
                'required'         => true,
                'has_empty_option' => true,
                'display_default_store_mode' => StoreSwitcher::DISPLAY_DEFAULT_STORE_MODE_DOWN
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _toHtml()
    {
        $helpBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_helpBlock', '', array(
                'content' => Mage::helper('M2ePro')->__(
                    '<p>It is necessary to select an Amazon Account (existing or create a new one) as well as choose
                a Marketplace that you are going to sell Magento Products on.</p>
                <p>It is also important to specify a Store View in accordance with which Magento Attribute
                values will be used in the Listing settings.</p><br>
                <p>More detailed information you can find
                <a href="%url%" target="_blank" class="external-link">here</a>.</p>',
                    Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(null, null, 'x/wocVAQ')
                ),
                'title' => Mage::helper('M2ePro')->__('General Settings')
            )
        );

        /** @var Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Create_Breadcrumb $breadcrumb */
        $breadcrumb = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_create_breadcrumb');
        $breadcrumb->setSelectedStep((int)$this->getRequest()->getParam('step', 1));

        $javascript = <<<HTML
<script type="text/javascript">

    M2ePro.formData.wizard = {$this->getRequest()->getParam('wizard', 0)};

    AmazonListingSettingsObj = new AmazonListingSettings();
    AmazonListingCreateGeneralObj = new AmazonListingCreateGeneral();
</script>
HTML;

        return $breadcrumb->_toHtml()
            . $helpBlock->_toHtml()
            . parent::_toHtml()
            . $javascript;
    }

    //########################################
}
