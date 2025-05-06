<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setId('walmartListingEditForm');
    }

    //########################################

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

        $formData = $this->getListing()->getData();

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

    //########################################

    protected function _prepareLayout()
    {
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

        $viewHelper->getJsUrlsRenderer()->addUrls(
            array(
                'templateCheckMessages'         => $this->getUrl(
                    '*/adminhtml_template/checkMessages',
                    array(
                        'component_mode' => Ess_M2ePro_Helper_Component_Walmart::NICK
                    )
                ),
                'addNewSellingFormatTemplate'   => $this->getUrl(
                    '*/adminhtml_walmart_template_sellingFormat/new',
                    array(
                        'close_on_save' => 1
                    )
                ),
                'editSellingFormatTemplate'     => $this->getUrl(
                    '*/adminhtml_walmart_template_sellingFormat/edit',
                    array(
                        'close_on_save' => 1
                    )
                ),
                'getSellingFormatTemplates'     => $this->getUrl(
                    '*/adminhtml_general/modelGetAll',
                    array(
                        'model'          => 'Template_SellingFormat',
                        'id_field'       => 'id',
                        'data_field'     => 'title',
                        'sort_field'     => 'title',
                        'sort_dir'       => 'ASC',
                        'component_mode' => Ess_M2ePro_Helper_Component_Walmart::NICK
                    )
                ),
                'addNewDescriptionTemplate'     => $this->getUrl(
                    '*/adminhtml_walmart_template_description/new',
                    array(
                        'close_on_save' => 1
                    )
                ),
                'editDescriptionTemplate'       => $this->getUrl(
                    '*/adminhtml_walmart_template_description/edit',
                    array(
                        'close_on_save' => 1
                    )
                ),
                'getDescriptionTemplates'       => $this->getUrl(
                    '*/adminhtml_general/modelGetAll',
                    array(
                        'model'          => 'Template_Description',
                        'id_field'       => 'id',
                        'data_field'     => 'title',
                        'sort_field'     => 'title',
                        'sort_dir'       => 'ASC',
                        'component_mode' => Ess_M2ePro_Helper_Component_Walmart::NICK
                    )
                ),
                'addNewSynchronizationTemplate' => $this->getUrl(
                    '*/adminhtml_walmart_template_synchronization/new',
                    array(
                        'close_on_save' => 1
                    )
                ),
                'editSynchronizationTemplate'   => $this->getUrl(
                    '*/adminhtml_walmart_template_synchronization/edit',
                    array(
                        'close_on_save' => 1
                    )
                ),
                'getSynchronizationTemplates'   => $this->getUrl(
                    '*/adminhtml_general/modelGetAll',
                    array(
                        'model'          => 'Template_Synchronization',
                        'id_field'       => 'id',
                        'data_field'     => 'title',
                        'sort_field'     => 'title',
                        'sort_dir'       => 'ASC',
                        'component_mode' => Ess_M2ePro_Helper_Component_Walmart::NICK
                    )
                )
            )
        );

        Mage::helper('M2ePro/View')->getJsRenderer()->addOnReadyJs(
            <<<JS
    TemplateManagerObj = new TemplateManager();

    WalmartListingSettingsObj = new WalmartListingSettings();
    WalmartListingSettingsObj.initObservers();

JS
        );

        return parent::_prepareLayout();
    }


    //########################################

    protected function getSellingFormatTemplates()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Template_SellingFormat_Collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Template_SellingFormat');
        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Walmart::NICK);
        $collection->addFieldToFilter('marketplace_id', $this->getListing()->getMarketplaceId());
        $collection->setOrder('title', Varien_Data_Collection::SORT_ORDER_ASC);

        $collection->resetByType(
            Zend_Db_Select::COLUMNS,
            array(
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
            Zend_Db_Select::COLUMNS,
            array(
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
            Zend_Db_Select::COLUMNS,
            array(
                'value' => 'id',
                'label' => 'title'
            )
        );

        $result = $collection->toArray();

        return $result['items'];
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

    //########################################

    protected function getListing()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if ($this->_listing === null) {
            $this->_listing = Mage::helper('M2ePro/Component_Walmart')->getCachedObject('Listing', $listingId);
        }

        return $this->_listing;
    }

    //########################################
}
