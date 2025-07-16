<?php

use Ess_M2ePro_Block_Adminhtml_StoreSwitcher as StoreSwitcher;

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Create_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;
    
    protected function _prepareForm()
    {
        $helper = Mage::helper('M2ePro');

        $form = new Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form(
            array(
                'id'      => 'edit_form',
                'class'   => 'form-list',
                'method'  => 'post',
                'action'  => $this->getUrl('*/adminhtml_walmart_listing/save'),
                'enctype' => 'multipart/form-data'
            )
        );

        $formData = $this->getDefaultFieldsValues();

        $fieldset = $form->addFieldset(
            'general_fieldset',
            array(
                'legend'      => $helper->__('General'),
                'collapsable' => false
            )
        );

        $fieldset->addField(
            'title',
            'text',
            array(
                'name'     => 'title',
                'label'    => $helper->__('Title'),
                'value'    => $formData['title'],
                'required' => true,
                'class'    => 'M2ePro-listing-title',
                'tooltip'  => $helper->__('Listing Title for your internal use.')
            )
        );

        $fieldset = $form->addFieldset(
            'walmart_settings_fieldset',
            array(
                'legend'      => $helper->__('Walmart Settings'),
                'collapsable' => false
            )
        );

        /** @var $accountsCollection Ess_M2ePro_Model_Resource_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Account')
            ->setOrder('title', 'ASC');

        $accountsCollection->resetByType(
            Zend_Db_Select::COLUMNS,
            array(
                'value' => 'id',
                'label' => 'title'
            )
        );

        $accountSelectionDisabled = false;

        $accountId = $formData['account_id'];
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
                            'class'   => 'add add-account-drop-down'
                        )
                    )->toHtml(),
                'tooltip'            => $helper->__('Select Account under which you want to manage this Listing.')
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
                'value' => ''
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
                'value'                      => $formData['store_id'],
                'style'                      => 'display: initial;',
                'required'                   => true,
                'has_empty_option'           => true,
                'display_default_store_mode' => StoreSwitcher::DISPLAY_DEFAULT_STORE_MODE_DOWN
            )
        );

        $fieldset = $form->addFieldset(
            'policies_settings',
            array(
                'legend'      => $helper->__('Policies Settings'),
                'collapsable' => false
            )
        );

        $fieldset->addField(
            'template_selling_format_messages',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::CUSTOM_CONTAINER,
            array()
        );

        $sellingFormatTemplates = $this->getSellingFormatTemplates();
        $style = count($sellingFormatTemplates) === 0 ? 'display: none' : '';

        $templateSellingFormat = new Varien_Data_Form_Element_Select(
            array(
                'html_id'  => 'template_selling_format_id',
                'name'     => 'template_selling_format_id',
                'style'    => $style,
                'no_span'  => true,
                'values'   => array_merge(array('' => ''), $sellingFormatTemplates),
                'value'    => $formData['template_selling_format_id'],
                'required' => true
            )
        );
        $templateSellingFormat->setForm($form);

        $style = count($sellingFormatTemplates) === 0 ? '' : 'display: none';
        $fieldset->addField(
            'template_selling_format_container',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::CUSTOM_CONTAINER,
            array(
                'label'              => $helper->__('Selling Policy'),
                'required'           => true,
                'text'               => <<<HTML
    <span id="template_selling_format_label" style="{$style}">
        {$helper->__('No Policies available.')}
    </span>
    {$templateSellingFormat->toHtml()}
HTML
                ,
                'after_element_html' => <<<HTML
&nbsp;
<span style="line-height: 20px;">
    <span id="edit_selling_format_template_link" style="color:#41362f">
        <a href="javascript: void(0);" style="" onclick="WalmartListingSettingsObj.editTemplate(
            M2ePro.url.get('editSellingFormatTemplate'), 
            $('template_selling_format_id').value,
            WalmartListingSettingsObj.newSellingFormatTemplateCallback
        );">
            {$helper->__('View')}&nbsp;/&nbsp;{$helper->__('Edit')}
        </a>
        <span>{$helper->__('or')}</span>
    </span>
    <a id="add_selling_format_template_link" href="javascript: void(0);"
        onclick="WalmartListingSettingsObj.addNewTemplate(
        M2ePro.url.get('addNewSellingFormatTemplate'),
        WalmartListingSettingsObj.newSellingFormatTemplateCallback
    );">{$helper->__('Add New')}</a>
</span>
HTML
            )
        );

        $descriptionTemplates = $this->getDescriptionTemplates();
        $style = count($descriptionTemplates) === 0 ? 'display: none' : '';

        $templateDescription = new Varien_Data_Form_Element_Select(
            array(
                'html_id'  => 'template_description_id',
                'name'     => 'template_description_id',
                'style'    => $style,
                'no_span'  => true,
                'values'   => array_merge(array('' => ''), $descriptionTemplates),
                'value'    => $formData['template_description_id'],
                'required' => true
            )
        );
        $templateDescription->setForm($form);

        $style = count($descriptionTemplates) === 0 ? '' : 'display: none';
        $fieldset->addField(
            'template_description_container',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::CUSTOM_CONTAINER,
            array(
                'label'              => $helper->__('Description Policy'),
                'required'           => true,
                'text'               => <<<HTML
    <span id="template_description_label" style="{$style}">
        {$helper->__('No Policies available.')}
    </span>
    {$templateDescription->toHtml()}
HTML
                ,
                'after_element_html' => <<<HTML
&nbsp;
<span style="line-height: 20px;">
    <span id="edit_description_template_link" style="color:#41362f">
        <a href="javascript: void(0);" onclick="WalmartListingSettingsObj.editTemplate(
            M2ePro.url.get('editDescriptionTemplate'),
            $('template_description_id').value,
            WalmartListingSettingsObj.newDescriptionTemplateCallback
        );">
            {$helper->__('View')}&nbsp;/&nbsp;{$helper->__('Edit')}
        </a>
        <span>{$helper->__('or')}</span>
    </span>
    <a id="add_description_template_link" href="javascript: void(0);"
        onclick="WalmartListingSettingsObj.addNewTemplate(
        M2ePro.url.get('addNewDescriptionTemplate'),
        WalmartListingSettingsObj.newDescriptionTemplateCallback
    );">{$helper->__('Add New')}</a>
</span>
HTML
            )
        );

        $synchronizationTemplates = $this->getSynchronizationTemplates();
        $style = count($synchronizationTemplates) === 0 ? 'display: none' : '';

        $templateSynchronization = new Varien_Data_Form_Element_Select(
            array(
                'html_id'  => 'template_synchronization_id',
                'name'     => 'template_synchronization_id',
                'style'    => $style,
                'no_span'  => true,
                'values'   => array_merge(array('' => ''), $synchronizationTemplates),
                'value'    => $formData['template_synchronization_id'],
                'required' => true
            )
        );
        $templateSynchronization->setForm($form);

        $style = count($synchronizationTemplates) === 0 ? '' : 'display: none';
        $fieldset->addField(
            'template_synchronization_container',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::CUSTOM_CONTAINER,
            array(
                'label'                  => $helper->__('Synchronization Policy'),
                'field_extra_attributes' => 'style="margin-bottom: 5px"',
                'required'               => true,
                'text'                   => <<<HTML
    <span id="template_synchronization_label" style="{$style}">
        {$helper->__('No Policies available.')}
    </span>
    {$templateSynchronization->toHtml()}
HTML
                ,
                'after_element_html'     => <<<HTML
&nbsp;
<span style="line-height: 20px;">
    <span id="edit_synchronization_template_link" style="color:#41362f">
        <a href="javascript: void(0);" onclick="WalmartListingSettingsObj.editTemplate(
            M2ePro.url.get('editSynchronizationTemplate'), 
            $('template_synchronization_id').value,
            WalmartListingSettingsObj.newSynchronizationTemplateCallback
        );">
            {$helper->__('View')}&nbsp;/&nbsp;{$helper->__('Edit')}
        </a>
        <span>{$helper->__('or')}</span>
    </span>
    <a id="add_synchronization_template_link" href="javascript: void(0);"
        onclick="WalmartListingSettingsObj.addNewTemplate(
        M2ePro.url.get('addNewSynchronizationTemplate'),
        WalmartListingSettingsObj.newSynchronizationTemplateCallback
    );">{$helper->__('Add New')}</a>
</span>
HTML
            )
        );

        $this->addConditionFieldset($form, $formData);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return void
     */
    private function addConditionFieldset(Varien_Data_Form $form, array $formData)
    {
        /** @var Ess_M2ePro_Helper_Data $helper */
        $helper = Mage::helper('M2ePro');

        $fieldset = $form->addFieldset(
            'condition_settings_fieldset',
            array(
                'legend' => $helper->__('Condition Settings'),
                'collapsable' => true
            )
        );

        $fieldset->addField(
            'condition_custom_attribute',
            'hidden',
            array(
                'name' => 'condition_custom_attribute',
                'value' => $formData['condition_custom_attribute']
            )
        );

        $fieldset->addField(
            'condition_value',
            'hidden',
            array(
                'name' => 'condition_value',
                'value' => $formData['condition_value']
            )
        );

        $preparedAttributes = array();

        /** @var Ess_M2ePro_Helper_Magento_Attribute $magentoAttributeHelper */
        $magentoAttributeHelper = Mage::helper('M2ePro/Magento_Attribute');
        $magentoSelectTextAttrs =  $magentoAttributeHelper->filterByInputTypes(
            $magentoAttributeHelper->getAll(),
            array('text', 'select')
        );

        foreach ($magentoSelectTextAttrs as $attribute) {
            $attrs = array(
                'attribute_code' => $attribute['code'],
            );
            if (
                $formData['condition_mode'] == Ess_M2ePro_Model_Walmart_Listing::CONDITION_MODE_CUSTOM_ATTRIBUTE
                && $attribute['code'] == $formData['condition_custom_attribute']
            ) {
                $attrs['selected'] = 'selected';
            }

            $preparedAttributes[] = array(
                'attrs' => $attrs,
                'value' => Ess_M2ePro_Model_Walmart_Listing::CONDITION_MODE_CUSTOM_ATTRIBUTE,
                'label' => $attribute['label'],
            );
        }

        $fieldset->addField(
            'condition_mode',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::SELECT,
            array(
                'name' => 'condition_mode',
                'label' => $helper->__('Condition'),
                'values' => array(
                    array(
                        'label' => $helper->__('Recommended Value'),
                        'value' => $this->getRecommendedConditionValues($formData),
                    ),
                    array(
                        'label' => $helper->__('Magento Attributes'),
                        'value' => $preparedAttributes,
                        'attrs' => array(
                            'is_magento_attribute' => true
                        )
                    )
                ),
                'create_magento_attribute' => true,
                'tooltip' => $helper->__(
                    'Specify the condition that best describes the current state of your product.'
                ),
                'allowed_attribute_types' => 'text,select',
            )
        );
    }

    /**
     * @return array
     */
    private function getRecommendedConditionValues(array $formData)
    {
        $values = array();
        foreach (Ess_M2ePro_Model_Walmart_Listing::$conditionRecommendedValues as $condition) {
            $value = array(
                'attrs' => array('attribute_code' => $condition),
                'value' => Ess_M2ePro_Model_Walmart_Listing::CONDITION_MODE_RECOMMENDED,
                'label' => Mage::helper('M2ePro')->__($condition),
            );

            if ($condition === $formData[Ess_M2ePro_Model_Resource_Walmart_Listing::COLUMN_CONDITION_VALUE]) {
                $value['attrs']['selected'] = 'selected';
            }

            $values[] = $value;
        }

        return $values;
    }

    // ---------------------------------------

    protected function _prepareLayout()
    {
        /** @var Mage_Page_Block_Html_Head $headBlock */
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock
                ->addCss('M2ePro/css/Plugin/DropDown.css')
                ->addJs('M2ePro/Plugin/DropDown.js');
        }

        /** @var Ess_M2ePro_Helper_View $viewHelper */
        $viewHelper = Mage::helper('M2ePro/View');

        $viewHelper->getJsPhpRenderer()
                   ->addConstants(
                       array(
                           array(
                               'CONDITION_MODE_RECOMMENDED',
                               Ess_M2ePro_Model_Walmart_Listing::CONDITION_MODE_RECOMMENDED,
                           ),
                       ),
                       'Ess_M2ePro_Model_Walmart_Listing'
                   );

        $viewHelper->getJsUrlsRenderer()->addUrls(array(
            'templateCheckMessages'       => $this->getUrl(
                '*/adminhtml_template/checkMessages', array(
                    'component_mode' => Ess_M2ePro_Helper_Component_Walmart::NICK
                )
            ),
            'addNewSellingFormatTemplate' => $this->getUrl(
                '*/adminhtml_walmart_template_sellingFormat/new',
                array(
                    'wizard'        => $this->getRequest()->getParam('wizard'),
                    'close_on_save' => 1
                )
            ),
            'editSellingFormatTemplate' => $this->getUrl(
                '*/adminhtml_walmart_template_sellingFormat/edit',
                array(
                    'wizard'        => $this->getRequest()->getParam('wizard'),
                    'close_on_save' => 1
                )
            ),
            'getSellingFormatTemplates'   => $this->getUrl(
                '*/adminhtml_general/modelGetAll', array(
                    'model'              => 'Template_SellingFormat',
                    'id_field'           => 'id',
                    'data_field'         => 'title',
                    'sort_field'         => 'title',
                    'sort_dir'           => 'ASC',
                    'component_mode'     => Ess_M2ePro_Helper_Component_Walmart::NICK
                )
            ),
            'addNewDescriptionTemplate' => $this->getUrl(
                '*/adminhtml_walmart_template_description/new',
                array(
                    'wizard'        => $this->getRequest()->getParam('wizard'),
                    'close_on_save' => 1
                )
            ),
            'editDescriptionTemplate' => $this->getUrl(
                '*/adminhtml_walmart_template_description/edit',
                array(
                    'wizard'        => $this->getRequest()->getParam('wizard'),
                    'close_on_save' => 1
                )
            ),
            'getDescriptionTemplates'     => $this->getUrl(
                '*/adminhtml_general/modelGetAll', array(
                    'model'              => 'Template_Description',
                    'id_field'           => 'id',
                    'data_field'         => 'title',
                    'sort_field'         => 'title',
                    'sort_dir'           => 'ASC',
                    'component_mode'     => Ess_M2ePro_Helper_Component_Walmart::NICK
                )
            ),
            'addNewSynchronizationTemplate' => $this->getUrl(
                '*/adminhtml_walmart_template_synchronization/new',
                array(
                    'wizard'        => $this->getRequest()->getParam('wizard'),
                    'close_on_save' => 1
                )
            ),
            'editSynchronizationTemplate' => $this->getUrl(
                '*/adminhtml_walmart_template_synchronization/edit',
                array(
                    'wizard'        => $this->getRequest()->getParam('wizard'),
                    'close_on_save' => 1
                )
            ),
            'getSynchronizationTemplates' => $this->getUrl(
                '*/adminhtml_general/modelGetAll', array(
                    'model'              => 'Template_Synchronization',
                    'id_field'           => 'id',
                    'data_field'         => 'title',
                    'sort_field'         => 'title',
                    'sort_dir'           => 'ASC',
                    'component_mode'     => Ess_M2ePro_Helper_Component_Walmart::NICK
                )
            ),
            'walmart_marketplace_withProductType/runSynchNow' => $this->getUrl(
                '*/adminhtml_walmart_marketplace_withProductType/runSynchNow'
            ),
            'walmart_marketplace_withProductType/synchGetExecutingInfo' => $this->getUrl(
                '*/adminhtml_walmart_marketplace_withProductType/synchGetExecutingInfo'
            ),
        ));

        $marketplacesSyncSettings = \json_encode($this->getMarketplacesSyncSettings());
        Mage::helper('M2ePro/View')->getJsRenderer()->addOnReadyJs(<<<JS
    M2ePro.formData.wizard = {$this->getRequest()->getParam('wizard', 0)};

    TemplateManagerObj = new TemplateManager();

    WalmartListingCreateGeneralObj = new WalmartListingCreateGeneral();
    WalmartListingCreateGeneralObj.setMarketplacesSyncSettings($marketplacesSyncSettings);
    WalmartListingSettingsObj = new WalmartListingSettings();
    
    WalmartListingCreateGeneralObj.initObservers();
    WalmartListingSettingsObj.initObservers();
JS
        );

        return parent::_prepareLayout();
    }

    /**
     * @return array
     */
    private function getMarketplacesSyncSettings()
    {
        /** @var Ess_M2ePro_Model_Walmart_Marketplace_Repository $marketplaceRepository */
        $marketplaceRepository = Mage::getModel('M2ePro/Walmart_Marketplace_Repository');

        $result = array();
        foreach ($marketplaceRepository->findActive() as $marketplace) {
            $result[] = array(
                'marketplace_id' => (int)$marketplace->getId(),
                'is_sync_with_product_type' => $marketplace->getChildObject()
                                                           ->isSupportedProductType(),
            );
        }

        return $result;
    }

    //########################################

    protected function _toHtml()
    {
        $helpBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_helpBlock', '', array(
                'content' => Mage::helper('M2ePro')->__(
                    'On this page, you can configure the basic Listing settings. Specify the meaningful Listing Title
                    for your internal use.<br>
                    Select Account under which you want to manage this Listing. Assign the Policy Templates and
                    Magento Store View.<br/><br/>
                    <p>The detailed information can be found <a href="%url%" target="_blank">here</a></p>',
                    Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(null, null, 'walmart-integration')
                ),
                'title'   => Mage::helper('M2ePro')->__('General')
            )
        );

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

        return $this->getAddAccountButtonHtml()
            . '<div id="account_credentials_form" style="display: none;">'
            . $credentialsForm->toHtml()
            . '</div>'
            . $helpBlock->toHtml()
            . parent::_toHtml();
    }

    //########################################

    protected function getSellingFormatTemplates()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Template_SellingFormat_Collection */
        $collection = Mage::getModel('M2ePro/Template_SellingFormat')->getCollection();
        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Walmart::NICK);
        $collection->setOrder('title', Varien_Data_Collection::SORT_ORDER_ASC);

        $collection->resetByType(
            Zend_Db_Select::COLUMNS, array(
                'value' => 'id',
                'label' => 'title'
            )
        );

        $result = $collection->toArray();
        return $result['items'];
    }

    protected function getDescriptionTemplates()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Template_Description_Collection */
        $collection = Mage::getModel('M2ePro/Template_Description')->getCollection();
        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Walmart::NICK);
        $collection->setOrder('title', Varien_Data_Collection::SORT_ORDER_ASC);

        $collection->resetByType(
            Zend_Db_Select::COLUMNS, array(
                'value' => 'id',
                'label' => 'title'
            )
        );

        $result = $collection->toArray();
        return $result['items'];
    }

    protected function getSynchronizationTemplates()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Template_Synchronization_Collection */
        $collection = Mage::getModel('M2ePro/Template_Synchronization')->getCollection();
        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Walmart::NICK);
        $collection->setOrder('title', Varien_Data_Collection::SORT_ORDER_ASC);

        $collection->resetByType(
            Zend_Db_Select::COLUMNS, array(
                'value' => 'id',
                'label' => 'title'
            )
        );

        $result = $collection->toArray();
        return $result['items'];
    }

    //########################################

    public function getDefaultFieldsValues()
    {
        return array(
            'title'      => Mage::helper('M2ePro/Component_Walmart')
                ->getCollection('Listing')->getSize() == 0 ? 'Default' : '',
            'account_id' => '',
            'store_id'   => '',

            'template_selling_format_id'  => '',
            'template_description_id'     => '',
            'template_synchronization_id' => '',

            Ess_M2ePro_Model_Resource_Walmart_Listing::COLUMN_CONDITION_MODE
            => Ess_M2ePro_Model_Walmart_Listing::CONDITION_MODE_NONE,
            Ess_M2ePro_Model_Resource_Walmart_Listing::COLUMN_CONDITION_VALUE => '',
            Ess_M2ePro_Model_Resource_Walmart_Listing::COLUMN_CONDITION_CUSTOM_ATTRIBUTE => '',
        );
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
        $specificEndUrl = urlencode($this->getUrl('*/*/*', array('_current' => true)));

        $url = $this->getUrl(
            '*/adminhtml_walmart_account_unitedStates_beforeGetToken/beforeGetToken',
            array(
                '_current' => true,
                'marketplace_id' => Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_US,
                'specific_end_url' => $specificEndUrl,
            )
        );

        $items[] = array(
            'url'    => $url,
            'label'  => Mage::helper('M2ePro')->__('United States')
        );

        $addAccount = $this->getUrl(
            '*/adminhtml_walmart_account_canada_accountCreate/addAccount',
            array(
                'marketplace_id' => Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_CA,
                'specific_end_url' => $specificEndUrl,
            )
        );
        $items[] = array(
            'url'     => '#',
            'id'      => 'account-ca',
            'label'   => Mage::helper('M2ePro')->__('Canada'),
            'onclick' => "WalmartListingCreateGeneralObj.openAccessDataPopup('{$addAccount}')"
        );

        return $items;
    }
}
