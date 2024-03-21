<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Block_Adminhtml_StoreSwitcher as StoreSwitcher;

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Create_General_Form extends Mage_Adminhtml_Block_Widget_Form
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
                'action'  => $this->getUrl('*/adminhtml_ebay_listing/save'),
                'enctype' => 'multipart/form-data'
            )
        );

        $fieldset = $form->addFieldset(
            'general_fieldset',
            array(
                'legend'      => $helper->__('General'),
                'collapsable' => false
            )
        );

        $title = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing')->getSize() == 0 ? 'Default' : '';
        $accountId = '';

        $account = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account')->getLastItem();
        $marketplaceId = $this->getRequest()->getParam('marketplace_id', '');
        $marketplaceSelectionDisabled = true;
        if (!$marketplaceId && $account->getId()) {
            $accountId = $account->getId();
            $info = Mage::helper('M2ePro')->jsonDecode($account->getChildObject()->getInfo());
            $marketplaceId = Mage::getModel('M2ePro/Marketplace')->getIdByCode($info['Site']);
            $marketplaceSelectionDisabled = false;
        }

        $storeId = '';

        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue(
            Ess_M2ePro_Model_Ebay_Listing::CREATE_LISTING_SESSION_DATA
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
                    This is used for reference within M2E Pro and will not appear on your eBay Listings.'
                )
            )
        );

        $fieldset = $form->addFieldset(
            'ebay_settings_fieldset',
            array(
                'legend'      => $helper->__('eBay Settings'),
                'collapsable' => false
            )
        );

        /** @var Ess_M2ePro_Model_Resource_Ebay_Account_Collection $accountsCollection */
        $accountsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account')
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

        $isAddAccountButtonHidden = $this->getRequest()->getParam('wizard', false) || $accountSelectionDisabled;

        $addAnotherButton = Mage::getSingleton('core/layout')->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                    'id'      => 'add_account_button',
                    'label'   => $helper->__('Add Another'),
                    'style'   => 'margin-left: 10px;' .
                        ($isAddAccountButtonHidden ? 'display: none;' : ''),
                    'onclick' => '',
                    'class'   => 'add add-account-drop-down',
                )
            )->toHtml();

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
                'after_element_html' => $addAnotherButton
            )
        );

        if ($this->getRequest()->getParam('marketplace_id', false) !== false) {
            $fieldset->addField(
                'marketplace_id_hidden',
                'hidden',
                array(
                    'name'  => 'marketplace_id',
                    'value' => $marketplaceId
                )
            );
        }

        $fieldset->addField(
            'marketplace_id',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::SELECT,
            array(
                'name'                   => 'marketplace_id',
                'label'                  => $helper->__('Marketplace'),
                'value'                  => $marketplaceId,
                'values'                 => $this->getMarketplaces(),
                'field_extra_attributes' => 'style="margin-bottom: 0px"',
                'disabled'               => $marketplaceSelectionDisabled,
                'note'                   => '<p class="note note-no-tool-tip"><span id="marketplace_url"></span></p>',
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
        $marketplaces = Mage::helper('M2ePro')->jsonEncode($this->getMarketplaces());

        Mage::helper('M2ePro/View')->getJsRenderer()->addOnReadyJs(<<<JS
    M2ePro.formData.wizard = {$this->getRequest()->getParam('wizard', 0)};

    EbayListingCreateGeneralObj = new EbayListingCreateGeneral({$marketplaces});
JS
        );

        return parent::_prepareLayout();
    }

    //########################################

    protected function _toHtml()
    {
        $breadcrumb = $this->getLayout()->createBlock('M2ePro/adminhtml_Ebay_Listing_Create_Breadcrumb');
        $breadcrumb->setSelectedStep(1);

        $helpBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_helpBlock',
            '',
            array(
                'content' => Mage::helper('M2ePro')->__(<<<HTML
This Page sets the eBay details and Magento Store View you want to use for this M2E Pro Listing.<br/><br/>
More detailed information about ability to work with this Page you can find <a href="%url%" target="_blank">here</a>.
HTML
                    ,
                    Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(null, null, 'step-1-general-settings')
                ),
                'title'   => Mage::helper('M2ePro')->__('General Settings')
            )
        );

        return $this->getAddAccountButtonHtml() .
            $breadcrumb->toHtml() .
            $helpBlock->toHtml() .
            parent::_toHtml();
    }

    //########################################

    protected function getMarketplaces()
    {
        if ($this->_marketplaces === null) {
            $marketplacesCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Marketplace')
                ->setOrder('sorder', 'ASC')
                ->setOrder('title', 'ASC');

            $this->_marketplaces = array(
                array('label' => '', 'value' => '', 'attrs' => array('style' => 'display: none;'))
            );

            foreach ($marketplacesCollection->getItems() as $marketplace) {
                $this->_marketplaces[$marketplace['id']] = array(
                    'label' => $marketplace['title'],
                    'value' => $marketplace['id'],
                    'url' => $marketplace['url']
                );
            }
        }

        return $this->_marketplaces;
    }

    public function getAddAccountButtonHtml()
    {
        $data = array(
            'target_css_class' => 'add-account-drop-down',
            'items'            => $this->getAddAccountButtonDropDownItems()
        );

        $addAccountDropDownBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown');
        $addAccountDropDownBlock->setData($data);

        return  $addAccountDropDownBlock->toHtml();
    }
    private function getAddAccountButtonDropDownItems()
    {
        $items = array();

        $url = $this->getUrl(
            '*/adminhtml_ebay_account/beforeGetSellApiToken',
            array(
                'mode' => Ess_M2ePro_Model_Ebay_Account::MODE_PRODUCTION,
                'close_on_save' => true,
                'wizard'        => $this->getRequest()->getParam('wizard')
            )
        );

        $items[] = array(
            'onclick' => 'EbayListingCreateGeneralObj.addAccount(this, event);',
            'url'    => $url,
            'target' => '_blank',
            'label'  => Mage::helper('M2ePro')->__('Live Account')
        );

        $url = $this->getUrl(
            '*/adminhtml_ebay_account/beforeGetSellApiToken',
            array(
                'mode' => Ess_M2ePro_Model_Ebay_Account::MODE_SANDBOX,
                'close_on_save' => true,
                'wizard'        => $this->getRequest()->getParam('wizard')
            )
        );

        $items[] = array(
            'onclick' => 'EbayListingCreateGeneralObj.addAccount(this, event);',
            'url'    => $url,
            'target' => '_blank',
            'label'  => Mage::helper('M2ePro')->__('Sandbox Account')
        );

        return $items;
    }
}
