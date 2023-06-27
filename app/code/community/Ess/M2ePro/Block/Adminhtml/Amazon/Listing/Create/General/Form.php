<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Block_Adminhtml_StoreSwitcher as StoreSwitcher;

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Create_General_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    protected $_marketplaces;

    //########################################

    protected function _prepareForm()
    {
        $helper = Mage::helper('M2ePro');

        $form = new Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form(
            array(
                'id'      => 'edit_form',
                'class'   => 'form-list',
                'method'  => 'post',
                'action'  => 'javascript:void(0)',
                'enctype' => 'multipart/form-data',
            )
        );

        $fieldset = $form->addFieldset(
            'general_fieldset',
            array(
                'legend'      => $helper->__('General'),
                'collapsable' => false
            )
        );

        $title = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing')->getSize() == 0 ? 'Default' : '';
        $accountId = '';
        $marketplaceId = '';
        $storeId = '';

        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue(
            Ess_M2ePro_Model_Amazon_Listing::CREATE_LISTING_SESSION_DATA
        );

        isset($sessionData['title']) && $title = $sessionData['title'];
        isset($sessionData['account_id']) && $accountId = $sessionData['account_id'];
        isset($sessionData['marketplace_id']) && $marketplaceId = $sessionData['marketplace_id'];
        isset($sessionData['store_id']) && $storeId = $sessionData['store_id'];

        $fieldset->addField(
            'title',
            'text',
            array(
                'name'     => 'title',
                'label'    => $helper->__('Title'),
                'value'    => $title,
                'required' => true,
                'class'    => 'M2ePro-listing-title',
                'tooltip'  => $helper->__(
                    'Create a descriptive and meaningful Title for your M2E Pro Listing.<br/>
                    This is used for reference within M2E Pro and will not appear on your Amazon Listings.'
                )
            )
        );

        $fieldset = $form->addFieldset(
            'amazon_settings_fieldset',
            array(
                'legend'      => $helper->__('Amazon Settings'),
                'collapsable' => false
            )
        );

        /** @var $accountsCollection Ess_M2ePro_Model_Resource_Amazon_Account_Collection */
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
                    'name'  => 'account_id',
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

        $isAddAccountButtonHidden = $this->getRequest()->getParam('wizard', false) ? ' display: none;' : '';

        $fieldset->addField(
            'account_container',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::CUSTOM_CONTAINER,
            array(
                'label'              => $helper->__('Account'),
                'required'           => count($accounts) > 1,
                'text'               => <<<HTML
    <span id="account_label"></span>
    {$accountSelect->toHtml()}
HTML
                ,
                'after_element_html' => Mage::getSingleton('core/layout')->createBlock('adminhtml/widget_button')
                    ->setData(
                        array(
                            'id'      => 'add_account_button',
                            'label'   => $helper->__('Add Another'),
                            'style'   => 'margin-left: 10px;' . $isAddAccountButtonHidden,
                            'onclick' => '',
                            'class'   => 'primary'
                        )
                    )->toHtml(),
                'tooltip'            => $helper->__('This is the user name of your Amazon Account.')
            )
        );

        $fieldset->addField(
            'marketplace_info',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::CUSTOM_CONTAINER,
            array(
                'label'                  => $helper->__('Marketplace'),
                'text'                   => <<<HTML
<span id="marketplace_title" style="display: block;"></span><p class="note" id="marketplace_url"></p>
HTML
                ,
                'field_extra_attributes' => 'id="marketplace_info" style="display: none; margin-top: 0px"'
            )
        );

        $fieldset->addField(
            'marketplace_id',
            'hidden',
            array(
                'name'  => 'marketplace_id',
                'value' => $marketplaceId
            )
        );

        $fieldset = $form->addFieldset(
            'magento_fieldset',
            array(
                'legend'      => $helper->__('Magento Settings'),
                'collapsable' => false
            )
        );

        $fieldset->addField(
            'store_id',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::STORE_SWITCHER,
            array(
                'name'                       => 'store_id',
                'label'                      => $helper->__('Magento Store View'),
                'value'                      => $storeId,
                'style'                      => 'display: initial;',
                'required'                   => true,
                'has_empty_option'           => true,
                'display_default_store_mode' => StoreSwitcher::DISPLAY_DEFAULT_STORE_MODE_DOWN
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    //########################################

    protected function _prepareLayout()
    {
        Mage::helper('M2ePro/View')->getJsPhpRenderer()->addConstants(
            Mage::helper('M2ePro')->getClassConstants('Ess_M2ePro_Helper_Component_Amazon'),
            'Ess_M2ePro_Helper_Component'
        );

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addControllerActions('adminhtml_amazon_account');
        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addControllerActions('adminhtml_amazon_marketplace');

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addUrls(
            Mage::helper('M2ePro')->getControllerActions(
                'adminhtml_general',
                array('component' => Ess_M2ePro_Helper_Component_Amazon::NICK)
            )
        );

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addUrls(
            Mage::helper('M2ePro')->getControllerActions(
                'adminhtml_amazon_listing_create',
                array('_current' => true)
            )
        );

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->add(
            $this->getUrl(
                '*/adminhtml_amazon_account/new',
                array(
                    'close_on_save' => true,
                    'wizard'        => $this->getRequest()->getParam('wizard')
                )
            ),
            'adminhtml_amazon_account/new'
        );

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->add(
            $this->getUrl(
                '*/adminhtml_amazon_log/synchronization',
                array(
                    'wizard' => $this->getRequest()->getParam('wizard')
                )
            ),
            'logViewUrl'
        );

        Mage::helper('M2ePro/View')->getJsTranslatorRenderer()->addTranslations(
            array(
                'The specified Title is already used for other Listing. Listing Title must be unique.' =>
                    Mage::helper('M2ePro')->__(
                        'The specified Title is already used for other Listing. Listing Title must be unique.'
                    ),
                'Account not found, please create it.'                                                 =>
                    Mage::helper('M2ePro')->__('Account not found, please create it.'),
                'Add Another'                                                                          => Mage::helper(
                    'M2ePro'
                )->__('Add Another'),
                'Please wait while Synchronization is finished.'                                       =>
                    Mage::helper('M2ePro')->__('Please wait while Synchronization is finished.')
            )
        );

        Mage::helper('M2ePro/View')->getJsRenderer()->addOnReadyJs(
            <<<JS
    M2ePro.formData.wizard = {$this->getRequest()->getParam('wizard', 0)};

    AmazonListingCreateGeneralObj = new AmazonListingCreateGeneral();
JS
        );

        return parent::_prepareLayout();
    }

    //########################################

    protected function _toHtml()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Create_Breadcrumb $breadcrumb */
        $breadcrumb = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_create_breadcrumb');
        $breadcrumb->setSelectedStep(1);

        $helpBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_helpBlock',
            '',
            array(
                'content' => Mage::helper('M2ePro')->__(
                    '<p>It is necessary to select an Amazon Account (existing or create a new one) as well as choose
                a Marketplace that you are going to sell Magento Products on.</p>
                <p>It is also important to specify a Store View in accordance with which Magento Attribute
                values will be used in the Listing settings.</p><br>
                <p>More detailed information you can find
                <a href="%url%" target="_blank" class="external-link">here</a>.</p>',
                    Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(null, null, 'step-1-specify-general-settings')
                ),
                'title'   => Mage::helper('M2ePro')->__('General Settings')
            )
        );

        return $breadcrumb->toHtml() .
            $helpBlock->toHtml() .
            parent::_toHtml();
    }

    //########################################
}
