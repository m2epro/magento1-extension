<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Template_Manager as TemplateManager;

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Create_Templates_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

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

        $formData = $this->getListingData();

        $form->addField(
            'marketplace_id',
            'hidden',
            array(
                'value' => $formData['marketplace_id']
            )
        );

        $form->addField(
            'store_id',
            'hidden',
            array(
                'value' => $formData['store_id']
            )
        );

        $fieldset = $form->addFieldset(
            'payment_and_shipping_settings',
            array(
                'legend'      => $helper->__('Payment and Shipping'),
                'collapsable' => false
            )
        );

        $paymentTemplates = $this->getPaymentTemplates($formData['marketplace_id']);
        $style = count($paymentTemplates) === 0 ? 'display: none' : '';

        $templatePayment = new Varien_Data_Form_Element_Select(
            array(
                'html_id'  => 'template_payment_id',
                'name'     => 'template_payment_id',
                'style'    => $style,
                'no_span'  => true,
                'values'   => array_merge(array('' => ''), $paymentTemplates),
                'value'    => $formData['template_payment_id'],
                'required' => true
            )
        );
        $templatePayment->setForm($form);

        $style = count($paymentTemplates) === 0 ? '' : 'display: none';
        $fieldset->addField(
            'template_payment_container',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::CUSTOM_CONTAINER,
            array(
                'label'              => $helper->__('Payment Policy'),
                'required'           => true,
                'text'               => <<<HTML
    <span id="template_payment_label" style="{$style}">
        {$helper->__('No Policies available.')}
    </span>
    {$templatePayment->toHtml()}
HTML
                ,
                'after_element_html' => <<<HTML
&nbsp;
<span style="line-height: 20px;">
    <span id="edit_payment_template_link" style="color:#41362f">
        <a href="javascript: void(0);" onclick="EbayListingSettingsObj.editTemplate(
            '{$this->getEditUrl(TemplateManager::TEMPLATE_PAYMENT)}', 
            $('template_payment_id').value,
            EbayListingSettingsObj.newPaymentTemplateCallback
        );">{$helper->__('View')}&nbsp;/&nbsp;{$helper->__('Edit')}</a>
        <span>{$helper->__('or')}</span>
    </span>
    <a id="add_payment_template_link" href="javascript: void(0);"
        onclick="EbayListingSettingsObj.addNewTemplate(
        '{$this->getAddNewUrl($formData['marketplace_id'], TemplateManager::TEMPLATE_PAYMENT)}',
        EbayListingSettingsObj.newPaymentTemplateCallback
    );">{$helper->__('Add New')}</a>
</span>
HTML
            )
        );

        $shippingTemplates = $this->getShippingTemplates($formData['marketplace_id']);
        $style = count($shippingTemplates) === 0 ? 'display: none' : '';

        $templateShipping = new Varien_Data_Form_Element_Select(
            array(
                'html_id'  => 'template_shipping_id',
                'name'     => 'template_shipping_id',
                'style'    => $style,
                'no_span'  => true,
                'values'   => array_merge(array('' => ''), $shippingTemplates),
                'value'    => $formData['template_shipping_id'],
                'required' => true
            )
        );
        $templateShipping->setForm($form);

        $style = count($shippingTemplates) === 0 ? '' : 'display: none';
        $fieldset->addField(
            'template_shipping_container',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::CUSTOM_CONTAINER,
            array(
                'label'              => $helper->__('Shipping Policy'),
                'required'           => true,
                'text'               => <<<HTML
    <span id="template_shipping_label" style="{$style}">
        {$helper->__('No Policies available.')}
    </span>
    {$templateShipping->toHtml()}
HTML
                ,
                'after_element_html' => <<<HTML
&nbsp;
<span style="line-height: 20px;">
    <span id="edit_shipping_template_link" style="color:#41362f">
        <a href="javascript: void(0);" onclick="EbayListingSettingsObj.editTemplate(
            '{$this->getEditUrl(TemplateManager::TEMPLATE_SHIPPING)}', 
            $('template_shipping_id').value,
            EbayListingSettingsObj.newShippingTemplateCallback
        );">{$helper->__('View')}&nbsp;/&nbsp;{$helper->__('Edit')}</a>
        <span>{$helper->__('or')}</span>
    </span>
    <a id="add_shipping_template_link" href="javascript: void(0);"
        onclick="EbayListingSettingsObj.addNewTemplate(
        '{$this->getAddNewUrl($formData['marketplace_id'], TemplateManager::TEMPLATE_SHIPPING)}',
        EbayListingSettingsObj.newShippingTemplateCallback
    );">{$helper->__('Add New')}</a>
</span>
HTML
            )
        );

        $returnPolicyTemplates = $this->getReturnPolicyTemplates($formData['marketplace_id']);
        $style = count($returnPolicyTemplates) === 0 ? 'display: none' : '';

        $templateReturnPolicy = new Varien_Data_Form_Element_Select(
            array(
                'html_id'  => 'template_return_policy_id',
                'name'     => 'template_return_policy_id',
                'style'    => $style,
                'no_span'  => true,
                'values'   => array_merge(array('' => ''), $returnPolicyTemplates),
                'value'    => $formData['template_return_policy_id'],
                'required' => true
            )
        );
        $templateReturnPolicy->setForm($form);

        $style = count($returnPolicyTemplates) === 0 ? '' : 'display: none';
        $fieldset->addField(
            'template_return_policy_container',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::CUSTOM_CONTAINER,
            array(
                'label'              => $helper->__('Return Policy'),
                'required'           => true,
                'text'               => <<<HTML
    <span id="template_return_policy_label" style="{$style}">
        {$helper->__('No Policies available.')}
    </span>
    {$templateReturnPolicy->toHtml()}
HTML
                ,
                'after_element_html' => <<<HTML
&nbsp;
<span style="line-height: 20px;">
    <span id="edit_return_policy_template_link" style="color:#41362f">
        <a href="javascript: void(0);" onclick="EbayListingSettingsObj.editTemplate(
            '{$this->getEditUrl(TemplateManager::TEMPLATE_RETURN_POLICY)}', 
            $('template_return_policy_id').value,
            EbayListingSettingsObj.newReturnPolicyTemplateCallback
        );">
            {$helper->__('View')}&nbsp;/&nbsp;{$helper->__('Edit')}
        </a>
        <span>{$helper->__('or')}</span>
    </span>
    <a id="add_return_policy_template_link" href="javascript: void(0);"
        onclick="EbayListingSettingsObj.addNewTemplate(
        '{$this->getAddNewUrl($formData['marketplace_id'], TemplateManager::TEMPLATE_RETURN_POLICY)}',
        EbayListingSettingsObj.newReturnPolicyTemplateCallback
    );">{$helper->__('Add New')}</a>
</span>
HTML
            )
        );

        $fieldset = $form->addFieldset(
            'selling_settings',
            array(
                'legend'      => $helper->__('Selling'),
                'collapsable' => false
            )
        );

        $fieldset->addField(
            'template_selling_format_messages',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::CUSTOM_CONTAINER,
            array(
                'style' => 'display: none',
            )
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
        <a href="javascript: void(0);" style="" onclick="EbayListingSettingsObj.editTemplate(
            '{$this->getEditUrl(TemplateManager::TEMPLATE_SELLING_FORMAT)}', 
            $('template_selling_format_id').value,
            EbayListingSettingsObj.newSellingFormatTemplateCallback
        );">
            {$helper->__('View')}&nbsp;/&nbsp;{$helper->__('Edit')}
        </a>
        <span>{$helper->__('or')}</span>
    </span>
    <a id="add_selling_format_template_link" href="javascript: void(0);"
        onclick="EbayListingSettingsObj.addNewTemplate(
        '{$this->getAddNewUrl($formData['marketplace_id'], TemplateManager::TEMPLATE_SELLING_FORMAT)}',
        EbayListingSettingsObj.newSellingFormatTemplateCallback
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
        <a href="javascript: void(0);" onclick="EbayListingSettingsObj.editTemplate(
            '{$this->getEditUrl(TemplateManager::TEMPLATE_DESCRIPTION)}', 
            $('template_description_id').value,
            EbayListingSettingsObj.newDescriptionTemplateCallback
        );">
            {$helper->__('View')}&nbsp;/&nbsp;{$helper->__('Edit')}
        </a>
        <span>{$helper->__('or')}</span>
    </span>
    <a id="add_description_template_link" href="javascript: void(0);"
        onclick="EbayListingSettingsObj.addNewTemplate(
        '{$this->getAddNewUrl($formData['marketplace_id'], TemplateManager::TEMPLATE_DESCRIPTION)}',
        EbayListingSettingsObj.newDescriptionTemplateCallback
    );">{$helper->__('Add New')}</a>
</span>
HTML
            )
        );

        $fieldset = $form->addFieldset(
            'synchronization_settings',
            array(
                'legend'      => $helper->__('Synchronization'),
                'collapsable' => false
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
        <a href="javascript: void(0);" onclick="EbayListingSettingsObj.editTemplate(
            '{$this->getEditUrl(TemplateManager::TEMPLATE_SYNCHRONIZATION)}', 
            $('template_synchronization_id').value,
            EbayListingSettingsObj.newSynchronizationTemplateCallback
        );">
            {$helper->__('View')}&nbsp;/&nbsp;{$helper->__('Edit')}
        </a>
        <span>{$helper->__('or')}</span>
    </span>
    <a id="add_synchronization_template_link" href="javascript: void(0);"
        onclick="EbayListingSettingsObj.addNewTemplate(
        '{$this->getAddNewUrl($formData['marketplace_id'], TemplateManager::TEMPLATE_SYNCHRONIZATION)}',
        EbayListingSettingsObj.newSynchronizationTemplateCallback
    );">{$helper->__('Add New')}</a>
</span>
HTML
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    //########################################

    protected function _prepareLayout()
    {
        $formData = $this->getListingData();

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addUrls(array(
            'templateCheckMessages'       => $this->getUrl(
                '*/adminhtml_template/checkMessages', array(
                    'component_mode' => Ess_M2ePro_Helper_Component_Ebay::NICK
                )
            ),
            'getPaymentTemplates'         => $this->getUrl(
                '*/adminhtml_general/modelGetAll', array(
                    'model'              => 'Ebay_Template_Payment',
                    'id_field'           => 'id',
                    'data_field'         => 'title',
                    'sort_field'         => 'title',
                    'sort_dir'           => 'ASC',
                    'marketplace_id'     => $formData['marketplace_id'],
                    'is_custom_template' => 0
                )
            ),
            'getShippingTemplates'        => $this->getUrl(
                '*/adminhtml_general/modelGetAll', array(
                    'model'              => 'Ebay_Template_Shipping',
                    'id_field'           => 'id',
                    'data_field'         => 'title',
                    'sort_field'         => 'title',
                    'sort_dir'           => 'ASC',
                    'marketplace_id'     => $formData['marketplace_id'],
                    'is_custom_template' => 0
                )
            ),
            'getReturnPolicyTemplates'    => $this->getUrl(
                '*/adminhtml_general/modelGetAll', array(
                    'model'              => 'Ebay_Template_ReturnPolicy',
                    'id_field'           => 'id',
                    'data_field'         => 'title',
                    'sort_field'         => 'title',
                    'sort_dir'           => 'ASC',
                    'marketplace_id'     => $formData['marketplace_id'],
                    'is_custom_template' => 0
                )
            ),
            'getSellingFormatTemplates'   => $this->getUrl(
                '*/adminhtml_general/modelGetAll', array(
                    'model'              => 'Template_SellingFormat',
                    'id_field'           => 'id',
                    'data_field'         => 'title',
                    'sort_field'         => 'title',
                    'sort_dir'           => 'ASC',
                    'component_mode'     => Ess_M2ePro_Helper_Component_Ebay::NICK,
                    'is_custom_template' => 0
                )
            ),
            'getDescriptionTemplates'     => $this->getUrl(
                '*/adminhtml_general/modelGetAll', array(
                    'model'              => 'Template_Description',
                    'id_field'           => 'id',
                    'data_field'         => 'title',
                    'sort_field'         => 'title',
                    'sort_dir'           => 'ASC',
                    'component_mode'     => Ess_M2ePro_Helper_Component_Ebay::NICK,
                    'is_custom_template' => 0
                )
            ),
            'getSynchronizationTemplates' => $this->getUrl(
                '*/adminhtml_general/modelGetAll', array(
                    'model'              => 'Template_Synchronization',
                    'id_field'           => 'id',
                    'data_field'         => 'title',
                    'sort_field'         => 'title',
                    'sort_dir'           => 'ASC',
                    'component_mode'     => Ess_M2ePro_Helper_Component_Ebay::NICK,
                    'is_custom_template' => 0
                )
            )
        ));

        Mage::helper('M2ePro/View')->getJsRenderer()->addOnReadyJs(<<<JS
    TemplateManagerObj = new TemplateManager();
    EbayListingSettingsObj = new EbayListingSettings();
    EbayListingSettingsObj.initObservers();
JS
        );

        return parent::_prepareLayout();
    }

    //########################################

    protected function _toHtml()
    {
        if ($this->getListing()) {
            return parent::_toHtml();
        }

        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Create_Breadcrumb $breadcrumb */
        $breadcrumb = $this->getLayout()->createBlock('M2ePro/adminhtml_Ebay_Listing_Create_Breadcrumb');
        $breadcrumb->setSelectedStep(2);

        $helpBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_helpBlock',
            '',
            array(
                'content' => Mage::helper('M2ePro')->__(<<<HTML
<p>In this Section, you set the payment methods you accept, the shipping methods you offer, and whether you accept 
returns. For that, select <b>Payment</b>, <b>Shipping</b>, and <b>Return</b> Policies for the Listing.</p>
<p>Also, you can choose the right listing format, provide a competitive price for your Items, set the detailed 
description for products to attract more buyers. For that, select <b>Selling</b> and <b>Description</b> 
Policies for the Listing.</p>
<p>You can set the preferences on how to synchronize your Items with Magento Catalog data. The rules can be defined in 
<b>Synchronization</b> policy.</p>
<p>More details in <a href="%url%" target="_blank">our documentation</a>.</p>
HTML
                    ,
                    Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(null, null, 'step-2-policies')
                ),
                'title'   => Mage::helper('M2ePro')->__('Policies')
            )
        );

        return $breadcrumb->toHtml() .
            $helpBlock->toHtml() .
            parent::_toHtml();
    }

    //########################################

    public function getDefaultFieldsValues()
    {
        return array(
            'template_payment_id'         => '',
            'template_shipping_id'        => '',
            'template_return_policy_id'   => '',
            'template_selling_format_id'  => '',
            'template_description_id'     => '',
            'template_synchronization_id' => '',
        );
    }

    //########################################

    protected function getListingData()
    {
        if ($this->getRequest()->getParam('id') !== null) {
            $data = $this->getListing()->getData();
        } else {
            $data = Mage::helper('M2ePro/Data_Session')->getValue(
                Ess_M2ePro_Model_Ebay_Listing::CREATE_LISTING_SESSION_DATA
            );
            $data = array_merge($this->getDefaultFieldsValues(), $data);
        }

        return $data;
    }

    //########################################

    protected function getListing()
    {
        if ($this->_listing === null && $this->getRequest()->getParam('id')) {
            $this->_listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Listing', $this->getRequest()->getParam('id')
            );
        }

        return $this->_listing;
    }

    //########################################

    protected function getPaymentTemplates($marketplaceId)
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Ebay_Template_Payment_Collection */
        $collection = Mage::getModel('M2ePro/Ebay_Template_Payment')->getCollection();
        $collection->addFieldToFilter('marketplace_id', $marketplaceId);
        $collection->addFieldToFilter('is_custom_template', 0);
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

    protected function getShippingTemplates($marketplaceId)
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Ebay_Template_Shipping_Collection */
        $collection = Mage::getModel('M2ePro/Ebay_Template_Shipping')->getCollection();
        $collection->addFieldToFilter('marketplace_id', $marketplaceId);
        $collection->addFieldToFilter('is_custom_template', 0);
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

    protected function getReturnPolicyTemplates($marketplaceId)
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Ebay_Template_ReturnPolicy_Collection */
        $collection = Mage::getModel('M2ePro/Ebay_Template_ReturnPolicy')->getCollection();
        $collection->addFieldToFilter('marketplace_id', $marketplaceId);
        $collection->addFieldToFilter('is_custom_template', 0);
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

    protected function getSellingFormatTemplates()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Template_SellingFormat_Collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Template_SellingFormat');
        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK);
        $collection->addFieldToFilter('is_custom_template', 0);
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
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Template_Description');
        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK);
        $collection->addFieldToFilter('is_custom_template', 0);
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
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Template_Synchronization');
        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK);
        $collection->addFieldToFilter('is_custom_template', 0);
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

    protected function getAddNewUrl($marketplaceId, $nick)
    {
        return $this->getUrl(
            '*/adminhtml_ebay_template/new',
            array(
                'marketplace_id' => $marketplaceId,
                'wizard'         => $this->getRequest()->getParam('wizard'),
                'nick'           => $nick,
                'close_on_save'  => 1
            )
        );

    }

    protected function getEditUrl($nick)
    {
        return $this->getUrl(
            '*/adminhtml_ebay_template/edit',
            array(
                'wizard'        => $this->getRequest()->getParam('wizard'),
                'nick'          => $nick,
                'close_on_save' => 1
            )
        );
    }

    //########################################
}
