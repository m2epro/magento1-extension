<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Block_Adminhtml_StoreSwitcher as StoreSwitcher;

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Create_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setId('walmartListingCreateForm');
    }

    //########################################

    protected function _prepareForm()
    {
        $formData = $this->getListingData();
        $helper   = Mage::helper('M2ePro');

        $form = new Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form(
            array(
                'id'      => 'edit_form',
                'method'  => 'post',
                'action'  => $this->getUrl('*/adminhtml_walmart_listing/save'),
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

        $fieldset->addField(
            'account_container',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::CUSTOM_CONTAINER,
            array(
                'label' => $helper->__('Account'),
                'text'  => <<<HTML
    <span id="account_label"></span>
    {$accountSelect->toHtml()}
HTML
            ,
                'after_element_html' => Mage::getSingleton('core/layout')->createBlock('adminhtml/widget_button')
                    ->setData(
                        array(
                            'id'      => 'add_account_button',
                            'label'   => $helper->__('Add Another'),
                            'style'   => 'margin-left: 10px;',
                            'onclick' => '',
                            'class'   => 'primary'
                        )
                    )->toHtml(),
                'tooltip' => $helper->__('Select Account under which you want to manage this Listing.')
            )
        );

        $marketplacesCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Marketplace')
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
                'label' => $helper->__('Marketplace'),
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
                'legend'      => $helper->__('Magento Settings'),
                'collapsable' => false
            )
        );

        $fieldset->addField(
            'store_id',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::STORE_SWITCHER,
            array(
                'name'             => 'store_id',
                'label'            => $helper->__('Magento Store View'),
                'value'            => $formData['store_id'],
                'style'            => 'display: initial;',
                'required'         => true,
                'has_empty_option' => true,
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
                'label'    => $helper->__('Selling Policy'),
                'required' => true,
                'text'     => <<<HTML
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
        <a href="javascript: void(0);" style="" onclick="WalmartListingSettingsObj.openWindow(
            M2ePro.url.editSellingFormatTemplate + 'id/' + $('template_selling_format_id').value
        );">
            {$helper->__('View')}&nbsp;/&nbsp;{$helper->__('Edit')}
        </a>
        <span>{$helper->__('or')}</span>
    </span>
    <a href="javascript: void(0);" onclick="WalmartListingSettingsObj.addNewTemplate(
        M2ePro.url.addNewSellingFormatTemplate,
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
                'label' => $helper->__('Description Policy'),
                'required' => true,
                'text' => <<<HTML
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
        <a href="javascript: void(0);" onclick="WalmartListingSettingsObj.openWindow(
            M2ePro.url.editDescriptionTemplate + 'id/' + $('template_description_id').value
        );">
            {$helper->__('View')}&nbsp;/&nbsp;{$helper->__('Edit')}
        </a>
        <span>{$helper->__('or')}</span>
    </span>
    <a href="javascript: void(0);" onclick="WalmartListingSettingsObj.addNewTemplate(
        M2ePro.url.addNewDescriptionTemplate,
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
                'label' => $helper->__('Synchronization Policy'),
                'field_extra_attributes' => 'style="margin-bottom: 5px"',
                'required' => true,
                'text' => <<<HTML
    <span id="template_synchronization_label" style="{$style}">
        {$helper->__('No Policies available.')}
    </span>
    {$templateSynchronization->toHtml()}
HTML
                ,
                'after_element_html' => <<<HTML
&nbsp;
<span style="line-height: 20px;">
    <span id="edit_synchronization_template_link" style="color:#41362f">
        <a href="javascript: void(0);" onclick="WalmartListingSettingsObj.openWindow(
            M2ePro.url.editSynchronizationTemplate + 'id/' + $('template_synchronization_id').value
        );">
            {$helper->__('View')}&nbsp;/&nbsp;{$helper->__('Edit')}
        </a>
        <span>{$helper->__('or')}</span>
    </span>
    <a href="javascript: void(0);" onclick="WalmartListingSettingsObj.addNewTemplate(
        M2ePro.url.addNewSynchronizationTemplate,
        WalmartListingSettingsObj.newSynchronizationTemplateCallback
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
                    Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(null, null, 'x/L4taAQ')
                ),
                'title' => Mage::helper('M2ePro')->__('General')
            )
        );

        $javascript = <<<HTML
<script type="text/javascript">

    M2ePro.url.templateCheckMessages = '{$this->getUrl(
        '*/adminhtml_template/checkMessages',
        array('component_mode' => Ess_M2ePro_Helper_Component_Walmart::NICK)
    )}';

    M2ePro.url.addNewSellingFormatTemplate = '{$this->getUrl(
        '*/adminhtml_walmart_template_sellingFormat/new',
        array(
            'wizard'        => $this->getRequest()->getParam('wizard'),
            'close_on_save' => 1
        )
    )}';

    M2ePro.url.addNewDescriptionTemplate = '{$this->getUrl(
        '*/adminhtml_walmart_template_description/new',
        array(
            'wizard'        => $this->getRequest()->getParam('wizard'),
            'close_on_save' => 1
        )
    )}';

    M2ePro.url.addNewSynchronizationTemplate = '{$this->getUrl(
        '*/adminhtml_walmart_template_synchronization/new',
        array(
            'wizard'        => $this->getRequest()->getParam('wizard'),
            'close_on_save' => 1
        )
    )}';

    M2ePro.url.editSellingFormatTemplate = '{$this->getUrl(
        '*/adminhtml_walmart_template_sellingFormat/edit',
        array(
            'wizard'        => $this->getRequest()->getParam('wizard'),
            'close_on_save' => 1
        )
    )}';

    M2ePro.url.editDescriptionTemplate = '{$this->getUrl(
        '*/adminhtml_walmart_template_description/edit',
        array(
            'wizard'        => $this->getRequest()->getParam('wizard'),
            'close_on_save' => 1
        )
    )}';

    M2ePro.url.editSynchronizationTemplate = '{$this->getUrl(
        '*/adminhtml_walmart_template_synchronization/edit',
        array(
            'wizard'        => $this->getRequest()->getParam('wizard'),
            'close_on_save' => 1
        )
    )}';

    M2ePro.url.getSellingFormatTemplates = '{$this->getUrl(
        '*/adminhtml_general/modelGetAll', array(
            'model'          => 'Template_SellingFormat',
            'id_field'       => 'id',
            'data_field'     => 'title',
            'sort_field'     => 'title',
            'sort_dir'       => 'ASC',
            'component_mode' => Ess_M2ePro_Helper_Component_Walmart::NICK
        )
    )}';

    M2ePro.url.getDescriptionTemplates = '{$this->getUrl(
        '*/adminhtml_general/modelGetAll', array(
            'model'          => 'Template_Description',
            'id_field'       => 'id',
            'data_field'     => 'title',
            'sort_field'     => 'title',
            'sort_dir'       => 'ASC',
            'component_mode' => Ess_M2ePro_Helper_Component_Walmart::NICK
        )
    )}';

    M2ePro.url.getSynchronizationTemplates = '{$this->getUrl(
        '*/adminhtml_general/modelGetAll', array(
            'model'          => 'Template_Synchronization',
            'id_field'       => 'id',
            'data_field'     => 'title',
            'sort_field'     => 'title',
            'sort_dir'       => 'ASC',
            'component_mode' => Ess_M2ePro_Helper_Component_Walmart::NICK
        )
    )}';

    M2ePro.formData.wizard = {$this->getRequest()->getParam('wizard', 0)};

    TemplateManagerObj = new TemplateManager();

    WalmartListingSettingsObj = new WalmartListingSettings();
    WalmartListingCreateGeneralObj = new WalmartListingCreateGeneral();

    $('store_id').observe('change', WalmartListingCreateGeneralObj.store_id_change);
    $('store_id').simulate('change');

    $('account_id').observe('change', WalmartListingSettingsObj.reloadSellingFormatTemplates)
    if ($('account_id').value) {
        $('account_id').simulate('change');
    }

    $('template_selling_format_id').observe('change', function() {
        if ($('template_selling_format_id').value) {
            $('edit_selling_format_template_link').show();
        } else {
            $('edit_selling_format_template_link').hide();
        }
    });
    $('template_selling_format_id').simulate('change');

    $('template_selling_format_id').observe('change', WalmartListingSettingsObj.selling_format_template_id_change)
    if ($('template_selling_format_id').value) {
        $('template_selling_format_id').simulate('change');
    }

    $('template_description_id').observe('change', function() {
        if ($('template_description_id').value) {
            $('edit_description_template_link').show();
        } else {
            $('edit_description_template_link').hide();
        }
    });
    $('template_description_id').simulate('change');

    $('template_description_id').observe('change', WalmartListingSettingsObj.description_template_id_change)
    if ($('template_description_id').value) {
        $('template_description_id').simulate('change');
    }

    $('template_synchronization_id').observe('change', function() {
        if ($('template_synchronization_id').value) {
            $('edit_synchronization_template_link').show();
        } else {
            $('edit_synchronization_template_link').hide();
        }
    });
    $('template_synchronization_id').simulate('change');

    $('template_synchronization_id').observe('change', WalmartListingSettingsObj.synchronization_template_id_change)
    if ($('template_synchronization_id').value) {
        $('template_synchronization_id').simulate('change');
    }

</script>
HTML;

        return $helpBlock->_toHtml() . parent::_toHtml() . $javascript;
    }

    //########################################

    protected function getSellingFormatTemplates()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Template_SellingFormat')->getCollection();
        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Walmart::NICK);
        $collection->setOrder('title', Varien_Data_Collection::SORT_ORDER_ASC);

        $collection->resetByType(
            Zend_Db_Select::COLUMNS, array(
                'value' => 'id',
                'label' => 'title'
            )
        );

        return $collection->getConnection()->fetchAssoc($collection->getSelect());
    }

    protected function getDescriptionTemplates()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Template_Description')->getCollection();
        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Walmart::NICK);
        $collection->setOrder('title', Varien_Data_Collection::SORT_ORDER_ASC);

        $collection->resetByType(
            Zend_Db_Select::COLUMNS, array(
                'value' => 'id',
                'label' => 'title'
            )
        );

        return $collection->getConnection()->fetchAssoc($collection->getSelect());
    }

    protected function getSynchronizationTemplates()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Template_Synchronization')->getCollection();
        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Walmart::NICK);
        $collection->setOrder('title', Varien_Data_Collection::SORT_ORDER_ASC);

        $collection->resetByType(
            Zend_Db_Select::COLUMNS, array(
                'value' => 'id',
                'label' => 'title'
            )
        );

        return $collection->getConnection()->fetchAssoc($collection->getSelect());
    }

    //########################################

    public function getDefaultFieldsValues()
    {
        return array(
            'title' => Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing')
                ->getSize() == 0 ? 'Default' : '',
            'account_id'                  => '',
            'store_id'                    => '',
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
            $data = $this->getDefaultFieldsValues();
        }

        return $data;
    }

    //########################################

    protected function getListing()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if ($this->_listing === null) {
            $this->_listing = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
                'Listing', $listingId
            );
        }

        return $this->_listing;
    }

    //########################################
}
