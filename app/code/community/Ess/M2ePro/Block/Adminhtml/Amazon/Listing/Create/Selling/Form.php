<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Listing as AmazonListing;

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Create_Selling_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_useFormContainer = true;

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
                'action'  => 'javascript:void(0)',
                'enctype' => 'multipart/form-data',
            )
        );
        $form->addCustomAttribute('allowed_attribute_types');

        /** @var Ess_M2ePro_Helper_Magento_Attribute $magentoAttributeHelper */
        $magentoAttributeHelper = Mage::helper('M2ePro/Magento_Attribute');

        $attributes = Mage::helper('M2ePro/Magento_Attribute')->getAll();

        $attributesByTypes = array(
            'boolean'       => $magentoAttributeHelper->filterByInputTypes(
                $attributes,
                array('boolean')
            ),
            'text'          => $magentoAttributeHelper->filterByInputTypes(
                $attributes,
                array('text')
            ),
            'text_textarea' => $magentoAttributeHelper->filterByInputTypes(
                $attributes,
                array('text', 'textarea')
            ),
            'text_date'     => $magentoAttributeHelper->filterByInputTypes(
                $attributes,
                array('text', 'date', 'datetime')
            ),
            'text_select'   => $magentoAttributeHelper->filterByInputTypes(
                $attributes,
                array('text', 'select')
            ),
            'text_images'   => $magentoAttributeHelper->filterByInputTypes(
                $attributes,
                array('text', 'image', 'media_image', 'gallery', 'multiline', 'textarea', 'select', 'multiselect')
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

        // SKU Settings
        $fieldset = $form->addFieldset(
            'sku_settings_fieldset',
            array(
                'legend'      => $helper->__('SKU Settings'),
                'collapsable' => true
            )
        );

        $fieldset->addField(
            'sku_custom_attribute',
            'hidden',
            array(
                'name'  => 'sku_custom_attribute',
                'value' => $formData['sku_custom_attribute']
            )
        );

        $preparedAttributes = array();
        foreach ($attributesByTypes['text'] as $attribute) {
            $attrs = array('attribute_code' => $attribute['code']);
            if ($formData['sku_mode'] == AmazonListing::SKU_MODE_CUSTOM_ATTRIBUTE
                && $attribute['code'] == $formData['sku_custom_attribute']) {
                $attrs['selected'] = 'selected';
            }

            $preparedAttributes[] = array(
                'attrs' => $attrs,
                'value' => AmazonListing::SKU_MODE_CUSTOM_ATTRIBUTE,
                'label' => $attribute['label'],
            );
        }

        $fieldset->addField(
            'sku_mode',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::SELECT,
            array(
                'name'                     => 'sku_mode',
                'label'                    => $helper->__('Source'),
                'values'                   => array(
                    AmazonListing::SKU_MODE_PRODUCT_ID => $helper->__('Product ID'),
                    AmazonListing::SKU_MODE_DEFAULT    => $helper->__('Product SKU'),
                    array(
                        'label' => $helper->__('Magento Attributes'),
                        'value' => $preparedAttributes,
                        'attrs' => array(
                            'is_magento_attribute' => true
                        )
                    )
                ),
                'value'                    => $formData['sku_mode'] != AmazonListing::SKU_MODE_CUSTOM_ATTRIBUTE
                    ? $formData['sku_mode'] : '',
                'create_magento_attribute' => true,
                'tooltip'                  => $helper->__(
                    'Is used to identify Amazon Items, which you list, in Amazon Seller Central Inventory.
                    <br/>
                    <br/>
                    <b>Note:</b> If you list a Magento Product and M2E Pro find an Amazon Item with the same
                    <i>Merchant SKU</i> in Amazon Inventory, they will be Mapped.'
                ),
                'allowed_attribute_types'  => 'text'
            )
        );

        $fieldset->addField(
            'sku_modification_mode',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::SELECT,
            array(
                'label'   => $helper->__('Modification'),
                'name'    => 'sku_modification_mode',
                'values'  => array(
                    AmazonListing::SKU_MODIFICATION_MODE_NONE     => $helper->__('None'),
                    AmazonListing::SKU_MODIFICATION_MODE_PREFIX   => $helper->__('Prefix'),
                    AmazonListing::SKU_MODIFICATION_MODE_POSTFIX  => $helper->__('Postfix'),
                    AmazonListing::SKU_MODIFICATION_MODE_TEMPLATE => $helper->__('Template'),
                ),
                'value'   => $formData['sku_modification_mode'],
                'tooltip' => $helper->__(
                    'Select one of the available variants to modify Amazon Item SKU
                    that was formed based on the Source you provided.'
                )
            )
        );

        $fieldStyle = '';
        if ($formData['sku_modification_mode'] == AmazonListing::SKU_MODIFICATION_MODE_NONE) {
            $fieldStyle = 'style="display: none"';
        }

        $fieldset->addField(
            'sku_modification_custom_value',
            'text',
            array(
                'container_id'           => 'sku_modification_custom_value_tr',
                'label'                  => $helper->__('Modification Value'),
                'name'                   => 'sku_modification_custom_value',
                'required'               => true,
                'value'                  => $formData['sku_modification_custom_value'],
                'class'                  => 'M2ePro-validate-sku-modification-custom-value 
                M2ePro-validate-sku-modification-custom-value-max-length',
                'field_extra_attributes' => $fieldStyle,
            )
        );

        $fieldset->addField(
            'generate_sku_mode',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::SELECT,
            array(
                'label'   => $helper->__('Generate'),
                'name'    => 'generate_sku_mode',
                'values'  => array(
                    AmazonListing::GENERATE_SKU_MODE_NO  => $helper->__('No'),
                    AmazonListing::GENERATE_SKU_MODE_YES => $helper->__('Yes')
                ),
                'value'   => $formData['generate_sku_mode'],
                'tooltip' => $helper->__(
                    'If <strong>Yes</strong>, then if Merchant SKU of the Amazon Item you list is found in the
                    Unmanaged Listings,
                    M2E Pro Listings or among the Amazon Items that are currently in process of Listing,
                    another SKU will be automatically created and the Amazon Item will be Listed.<br/><br/>
                    Has to be set to <strong>Yes</strong> if you are going to use the same
                    Magento Product under different ASIN(s)/ISBN(s)'
                )
            )
        );

        // Policies
        $fieldset = $form->addFieldset(
            'policies_fieldset',
            array(
                'legend'      => $helper->__('Policies'),
                'collapsable' => true
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
    <span id="template_selling_format_label" style="padding-right: 25px; {$style}">
        {$helper->__('No Policies available.')}
    </span>
    {$templateSellingFormat->toHtml()}
HTML
                ,
                'after_element_html' => <<<HTML
&nbsp;
<span style="line-height: 20px;">
    <span id="edit_selling_format_template_link" style="color:#41362f">
        <a href="javascript: void(0);" style="" onclick="AmazonListingSettingsObj.editTemplate(
            M2ePro.url.get('editSellingFormatTemplate'), 
            $('template_selling_format_id').value,
            AmazonListingSettingsObj.newSellingFormatTemplateCallback
        );">
            {$helper->__('View')}&nbsp;/&nbsp;{$helper->__('Edit')}
        </a>
        <span>{$helper->__('or')}</span>
    </span>
    <a id="add_selling_format_template_link" href="javascript: void(0);"
        onclick="AmazonListingSettingsObj.addNewTemplate(
        M2ePro.url.get('addNewSellingFormatTemplate'),
        AmazonListingSettingsObj.newSellingFormatTemplateCallback
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
    <span id="template_synchronization_label" style="padding-right: 25px; {$style}">
        {$helper->__('No Policies available.')}
    </span>
    {$templateSynchronization->toHtml()}
HTML
                ,
                'after_element_html'     => <<<HTML
&nbsp;
<span style="line-height: 20px;">
    <span id="edit_synchronization_template_link" style="color:#41362f">
        <a href="javascript: void(0);" onclick="AmazonListingSettingsObj.editTemplate(
            M2ePro.url.get('editSynchronizationTemplate'),
            $('template_synchronization_id').value,
            AmazonListingSettingsObj.newSynchronizationTemplateCallback
        );">
            {$helper->__('View')}&nbsp;/&nbsp;{$helper->__('Edit')}
        </a>
        <span>{$helper->__('or')}</span>
    </span>
    <a id="add_synchronization_template_link" href="javascript: void(0);"
        onclick="AmazonListingSettingsObj.addNewTemplate(
        M2ePro.url.get('addNewSynchronizationTemplate'),
        AmazonListingSettingsObj.newSynchronizationTemplateCallback
    );">{$helper->__('Add New')}</a>
</span>
HTML
            )
        );

        $shippingTemplates = $this->getShippingTemplates();
        $style = count($shippingTemplates) === 0 ? 'display: none' : '';

        $templateShipping = new Varien_Data_Form_Element_Select(
            array(
                'html_id'  => 'template_shipping_id',
                'name'     => 'template_shipping_id',
                'style'    => $style,
                'no_span'  => true,
                'values'   => array_merge(array('' => ''), $shippingTemplates),
                'value'    => $formData['template_shipping_id'],
                'required' => false
            )
        );
        $templateShipping->setForm($form);

        $style = count($shippingTemplates) === 0 ? '' : 'display: none';
        $fieldset->addField(
            'template_shipping_container',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::CUSTOM_CONTAINER,
            array(
                'label'                  => $helper->__('Shipping Policy'),
                'field_extra_attributes' => 'style="margin-bottom: 5px"',
                'required'               => false,
                'text'                   => <<<HTML
    <span id="template_shipping_label" style="padding-right: 25px; {$style}">
        {$helper->__('No Policies available.')}
    </span>
    {$templateShipping->toHtml()}
HTML
                ,
                'after_element_html'     => <<<HTML
&nbsp;
<span style="line-height: 20px;">
    <span id="edit_shipping_template_link" style="color:#41362f">
        <a href="javascript: void(0);" onclick="AmazonListingSettingsObj.editTemplate(
            M2ePro.url.get('editShippingTemplate'), 
            $('template_shipping_id').value,
            AmazonListingSettingsObj.newShippingTemplateCallback
        );">
            {$helper->__('View')}&nbsp;/&nbsp;{$helper->__('Edit')}
        </a>
        <span>{$helper->__('or')}</span>
    </span>
    <a id="add_shipping_template_link" href="javascript: void(0);"  
        onclick="AmazonListingSettingsObj.addNewTemplate(
        M2ePro.url.get('addNewShippingTemplate'),
        AmazonListingSettingsObj.newShippingTemplateCallback
    );">{$helper->__('Add New')}</a>
</span>
HTML
            )
        );

        // Condition Settings
        $fieldset = $form->addFieldset(
            'condition_settings_fieldset',
            array(
                'legend'      => $helper->__('Condition Settings'),
                'collapsable' => true
            )
        );

        $fieldset->addField(
            'condition_custom_attribute',
            'hidden',
            array(
                'name'  => 'condition_custom_attribute',
                'value' => $formData['condition_custom_attribute']
            )
        );

        $fieldset->addField(
            'condition_value',
            'hidden',
            array(
                'name'  => 'condition_value',
                'value' => $formData['condition_value']
            )
        );

        $preparedAttributes = array();
        foreach ($attributesByTypes['text_select'] as $attribute) {
            $attrs = array('attribute_code' => $attribute['code']);
            if ($formData['condition_mode'] == AmazonListing::SKU_MODE_CUSTOM_ATTRIBUTE
                && $attribute['code'] == $formData['condition_custom_attribute']) {
                $attrs['selected'] = 'selected';
            }

            $preparedAttributes[] = array(
                'attrs' => $attrs,
                'value' => AmazonListing::SKU_MODE_CUSTOM_ATTRIBUTE,
                'label' => $attribute['label'],
            );
        }

        $fieldset->addField(
            'condition_mode',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::SELECT,
            array(
                'name'                     => 'condition_mode',
                'label'                    => $helper->__('Condition'),
                'values'                   => array(
                    array(
                        'label' => $helper->__('Recommended Value'),
                        'value' => $this->getRecommendedConditionValues()
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
                'tooltip'                  => $helper->__(
                    <<<HTML
                    <p>The Condition settings will be used not only to create new Amazon Products, but
                    also during a Full Revise of the Product on the channel. However, it is not recommended
                    to change the Condition settings of the already existing Amazon Products as the ability to
                    edit this kind of information in Seller Central is not available.</p><br>

                    <p>On the other hand, Amazon MWS API allows changing the Condition of the existing Amazon
                    Product following the list of technical limitations. It is required to provide the Condition
                    value when the Condition Note should be updated.</p><br>

                    <p><strong>For example</strong>, you are listing a New Product on Amazon with the Condition Note
                    ‘totally new’.
                    Then, you are changing the Condition to Used and Condition Note to ‘a bit used’.
                    The modified values will be updated during the next Revise action. As a result, the Condition
                    will be set to Used and the Condition Note will be ‘a bit used’ for the Product on Amazon.</p>
HTML
                ),
                'allowed_attribute_types'  => 'text,select'
            )
        );

        $fieldset->addField(
            'condition_note_mode',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::SELECT,
            array(
                'container_id' => 'condition_note_mode_tr',
                'label'        => $helper->__('Condition Note'),
                'name'         => 'condition_note_mode',
                'values'       => array(
                    AmazonListing::CONDITION_NOTE_MODE_NONE         => $helper->__('None'),
                    AmazonListing::CONDITION_NOTE_MODE_CUSTOM_VALUE => $helper->__('Custom Value')
                ),
                'value'        => $formData['condition_note_mode'],
                'tooltip'      => $helper->__('Short Description of Item(s) Condition.')
            )
        );

        $preparedAttributes = array();
        foreach ($attributesByTypes['text_textarea'] as $attribute) {
            $option['value'] = $attribute['code'];
            $option['label'] = $attribute['label'];
            $preparedAttributes[] = $option;
        }

        $style = 'width: 148px !important; margin: 0 5px 0 5px !important; vertical-align: top !important;';
        $attributesSelect = new Varien_Data_Form_Element_Select(
            array(
                'html_id'  => 'condition_note_custom_attribute',
                'name'     => 'condition_note_custom_attribute',
                'no_span'  => true,
                'style'    => $style,
                'values'   => $preparedAttributes,
                'value'    => $preparedAttributes[0]['value'],
                'required' => false
            )
        );
        $attributesSelect->setForm($form);

        $attributesButton = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                    'label'   => Mage::helper('M2ePro')->__('Insert Attribute'),
                    'onclick' => "AmazonListingCreateSellingObj.appendToText"
                        . "('condition_note_custom_attribute', 'condition_note_value');",
                    'class'   => 'condition_note_value_insert_button',
                    'style'   => 'vertical-align: top !important;'
                )
            );

        $fieldset->addField(
            'condition_note_value',
            'textarea',
            array(
                'container_id'       => 'condition_note_value_tr',
                'name'               => 'condition_note_value',
                'label'              => $helper->__('Condition Note Value'),
                'style'              => 'height: 200px;',
                'class'              => 'textarea M2ePro-required-when-visible',
                'required'           => true,
                'after_element_html' => $attributesSelect->toHtml() . $attributesButton->toHtml(),
                'value'              => $formData['condition_note_value']
            )
        );

        // Listing Photos
        $fieldset = $form->addFieldset(
            'magento_block_amazon_listing_add_images',
            array(
                'legend'      => $helper->__('Listing Photos'),
                'collapsable' => true
            )
        );

        $fieldset->addField(
            'image_main_attribute',
            'hidden',
            array(
                'name'  => 'image_main_attribute',
                'value' => $formData['image_main_attribute']
            )
        );

        $preparedAttributes = array();
        foreach ($attributesByTypes['text_images'] as $attribute) {
            $attrs = array('attribute_code' => $attribute['code']);
            if ($formData['image_main_mode'] == AmazonListing::IMAGE_MAIN_MODE_ATTRIBUTE
                && $attribute['code'] == $formData['image_main_attribute']) {
                $attrs['selected'] = 'selected';
            }

            $preparedAttributes[] = array(
                'attrs' => $attrs,
                'value' => AmazonListing::IMAGE_MAIN_MODE_ATTRIBUTE,
                'label' => $attribute['label'],
            );
        }

        $fieldset->addField(
            'image_main_mode',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::SELECT,
            array(
                'name'                     => 'image_main_mode',
                'label'                    => $helper->__('Main Image'),
                'required'                 => true,
                'values'                   => array(
                    AmazonListing::IMAGE_MAIN_MODE_NONE    => $helper->__('None'),
                    AmazonListing::IMAGE_MAIN_MODE_PRODUCT => $helper->__('Product Base Image'),
                    array(
                        'label' => $helper->__('Magento Attributes'),
                        'value' => $preparedAttributes,
                        'attrs' => array('is_magento_attribute' => true)
                    )
                ),
                'value'                    => $formData['image_main_mode'] != AmazonListing::IMAGE_MAIN_MODE_ATTRIBUTE
                    ? $formData['image_main_mode'] : '',
                'create_magento_attribute' => true,
                'tooltip'                  => $helper->__(
                    'You have an ability to add Photos for your Items to be displayed on the More Buying Choices Page.
                    <br/>It is available only for Items with Used or Collectible Condition.'
                ),
                'allowed_attribute_types'  => 'text,textarea,select,multiselect'
            )
        );

        $fieldset->addField(
            'gallery_images_limit',
            'hidden',
            array(
                'name'  => 'gallery_images_limit',
                'value' => $formData['gallery_images_limit']
            )
        );

        $fieldset->addField(
            'gallery_images_attribute',
            'hidden',
            array(
                'name'  => 'gallery_images_attribute',
                'value' => $formData['gallery_images_attribute']
            )
        );

        $preparedLimitOptions[] = array(
            'attrs' => array('attribute_code' => 1),
            'value' => 1,
            'label' => 1,
        );
        if ($formData['gallery_images_limit'] == 1 &&
            $formData['gallery_images_mode'] != AmazonListing::GALLERY_IMAGES_MODE_NONE) {
            $preparedLimitOptions[0]['attrs']['selected'] = 'selected';
        }

        for ($i = 2; $i <= AmazonListing::GALLERY_IMAGES_COUNT_MAX; $i++) {
            $option = array(
                'attrs' => array('attribute_code' => $i),
                'value' => AmazonListing::GALLERY_IMAGES_MODE_PRODUCT,
                'label' => $helper->__('Up to') . ' ' . $i,
            );

            if ($formData['gallery_images_limit'] == $i) {
                $option['attrs']['selected'] = 'selected';
            }

            $preparedLimitOptions[] = $option;
        }

        $preparedAttributes = array();
        foreach ($attributesByTypes['text_images'] as $attribute) {
            $attrs = array('attribute_code' => $attribute['code']);
            if ($formData['gallery_images_mode'] == AmazonListing::GALLERY_IMAGES_MODE_ATTRIBUTE
                && $attribute['code'] == $formData['gallery_images_attribute']) {
                $attrs['selected'] = 'selected';
            }

            $preparedAttributes[] = array(
                'attrs' => $attrs,
                'value' => AmazonListing::GALLERY_IMAGES_MODE_ATTRIBUTE,
                'label' => $attribute['label'],
            );
        }

        $fieldConfig = array(
            'container_id'             => 'gallery_images_mode_tr',
            'name'                     => 'gallery_images_mode',
            'label'                    => $helper->__('Additional Images'),
            'values'                   => array(
                AmazonListing::GALLERY_IMAGES_MODE_NONE => $helper->__('None'),
                array(
                    'label' => $helper->__('Product Images Quantity'),
                    'value' => $preparedLimitOptions
                ),
                array(
                    'label' => $helper->__('Magento Attribute'),
                    'value' => $preparedAttributes,
                    'attrs' => array('is_magento_attribute' => true)
                )
            ),
            'create_magento_attribute' => true,
            'allowed_attribute_types'  => 'text,textarea,select,multiselect',
        );

        if ($formData['gallery_images_mode'] == AmazonListing::GALLERY_IMAGES_MODE_NONE) {
            $fieldConfig['value'] = $formData['gallery_images_mode'];
        }

        $fieldset->addField(
            'gallery_images_mode',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::SELECT,
            $fieldConfig
        );

        // Gift Wrap
        $fieldset = $form->addFieldset(
            'magento_block_amazon_listing_gift_settings',
            array(
                'legend'      => $helper->__('Gift Settings'),
                'collapsable' => true
            )
        );

        $fieldset->addField(
            'gift_wrap_attribute',
            'hidden',
            array(
                'name'  => 'gift_wrap_attribute',
                'value' => $formData['gift_wrap_attribute']
            )
        );

        $preparedAttributes = array();
        foreach ($attributesByTypes['boolean'] as $attribute) {
            $attrs = array('attribute_code' => $attribute['code']);
            if ($formData['gift_wrap_mode'] == AmazonListing::GIFT_WRAP_MODE_ATTRIBUTE
                && $attribute['code'] == $formData['gift_wrap_attribute']) {
                $attrs['selected'] = 'selected';
            }

            $preparedAttributes[] = array(
                'attrs' => $attrs,
                'value' => AmazonListing::GIFT_WRAP_MODE_ATTRIBUTE,
                'label' => $attribute['label'],
            );
        }

        $fieldset->addField(
            'gift_wrap_mode',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::SELECT,
            array(
                'name'                     => 'gift_wrap_mode',
                'label'                    => $helper->__('Gift Wrap'),
                'values'                   => array(
                    AmazonListing::GIFT_WRAP_MODE_NO  => $helper->__('No'),
                    AmazonListing::GIFT_WRAP_MODE_YES => $helper->__('Yes'),
                    array(
                        'label' => $helper->__('Magento Attributes'),
                        'value' => $preparedAttributes,
                        'attrs' => array(
                            'is_magento_attribute' => true,
                            'new_option_value'     => AmazonListing::GIFT_WRAP_MODE_ATTRIBUTE
                        )
                    )
                ),
                'value'                    => $formData['gift_wrap_mode'] != AmazonListing::GIFT_WRAP_MODE_ATTRIBUTE
                    ? $formData['gift_wrap_mode'] : '',
                'create_magento_attribute' => true,
                'tooltip'                  => $helper->__(
                    'Enable this Option in case you want Gift Wrapped Option be applied to the
                    Products you are going to sell.'
                ),
                'allowed_attribute_types'  => 'boolean'
            )
        );

        $fieldset->addField(
            'gift_message_attribute',
            'hidden',
            array(
                'name'  => 'gift_message_attribute',
                'value' => $formData['gift_message_attribute']
            )
        );

        $preparedAttributes = array();
        foreach ($attributesByTypes['boolean'] as $attribute) {
            $attrs = array('attribute_code' => $attribute['code']);
            if ($formData['gift_message_mode'] == AmazonListing::GIFT_MESSAGE_MODE_ATTRIBUTE
                && $attribute['code'] == $formData['gift_message_attribute']) {
                $attrs['selected'] = 'selected';
            }

            $preparedAttributes[] = array(
                'attrs' => $attrs,
                'value' => AmazonListing::GIFT_MESSAGE_MODE_ATTRIBUTE,
                'label' => $attribute['label'],
            );
        }

        $fieldset->addField(
            'gift_message_mode',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::SELECT,
            array(
                'name'                     => 'gift_message_mode',
                'label'                    => $helper->__('Gift Message'),
                'values'                   => array(
                    AmazonListing::GIFT_MESSAGE_MODE_NO  => $helper->__('No'),
                    AmazonListing::GIFT_MESSAGE_MODE_YES => $helper->__('Yes'),
                    array(
                        'label' => $helper->__('Magento Attributes'),
                        'value' => $preparedAttributes,
                        'attrs' => array(
                            'is_magento_attribute' => true,
                            'new_option_value'     => AmazonListing::GIFT_MESSAGE_MODE_ATTRIBUTE
                        )
                    )
                ),
                'value'                    => $formData['gift_message_mode'] != AmazonListing::GIFT_MESSAGE_MODE_ATTRIBUTE
                    ? $formData['gift_message_mode'] : '',
                'create_magento_attribute' => true,
                'tooltip'                  => $helper->__(
                    'Enable this Option in case you want Gift Message Option be applied to the
                    Products you are going to sell.'
                ),
                'allowed_attribute_types'  => 'boolean'
            )
        );

        // Gift Wrap
        $fieldset = $form->addFieldset(
            'magento_block_amazon_listing_add_additional',
            array(
                'legend'      => $helper->__('Additional Settings'),
                'collapsable' => true
            )
        );

        $fieldset->addField(
            'handling_time_custom_attribute',
            'hidden',
            array(
                'name'  => 'handling_time_custom_attribute',
                'value' => $formData['handling_time_custom_attribute']
            )
        );

        $fieldset->addField(
            'handling_time_value',
            'hidden',
            array(
                'name'  => 'handling_time_value',
                'value' => $formData['handling_time_value']
            )
        );

        $recommendedValuesOptions = array();
        for ($i = 1; $i <= 30; $i++) {
            $option = array(
                'attrs' => array('attribute_code' => $i),
                'value' => AmazonListing::HANDLING_TIME_MODE_RECOMMENDED,
                'label' => $i . ' ' . $helper->__('day(s)'),
            );

            if ($formData['handling_time_value'] == $i) {
                $option['attrs']['selected'] = 'selected';
            }

            $recommendedValuesOptions[] = $option;
        }

        $preparedAttributes = array();
        foreach ($attributesByTypes['text_select'] as $attribute) {
            $attrs = array('attribute_code' => $attribute['code']);
            if ($formData['handling_time_mode'] == AmazonListing::HANDLING_TIME_MODE_CUSTOM_ATTRIBUTE
                && $attribute['code'] == $formData['handling_time_custom_attribute']) {
                $attrs['selected'] = 'selected';
            }

            $preparedAttributes[] = array(
                'attrs' => $attrs,
                'value' => AmazonListing::HANDLING_TIME_MODE_CUSTOM_ATTRIBUTE,
                'label' => $attribute['label'],
            );
        }

        $fieldConfig = array(
            'name'                     => 'handling_time_mode',
            'label'                    => $helper->__('Production Time'),
            'values'                   => array(
                AmazonListing::HANDLING_TIME_MODE_NONE => $helper->__('None'),
                array(
                    'label' => $helper->__('Recommended Value'),
                    'value' => $recommendedValuesOptions
                ),
                array(
                    'label' => $helper->__('Magento Attribute'),
                    'value' => $preparedAttributes,
                    'attrs' => array(
                        'is_magento_attribute' => true
                    )
                )
            ),
            'create_magento_attribute' => true,
            'tooltip'                  => $helper->__('Time that is needed to prepare an Item to be shipped.'),
            'allowed_attribute_types'  => 'text,select',
        );

        if ($formData['handling_time_mode'] == AmazonListing::HANDLING_TIME_MODE_NONE) {
            $fieldConfig['value'] = $formData['handling_time_mode'];
        }

        $fieldset->addField(
            'handling_time_mode',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::SELECT,
            $fieldConfig
        );

        $fieldset->addField(
            'restock_date_custom_attribute',
            'hidden',
            array(
                'name'  => 'restock_date_custom_attribute',
                'value' => $formData['restock_date_custom_attribute']
            )
        );

        $preparedAttributes = array();
        foreach ($attributesByTypes['text_date'] as $attribute) {
            $attrs = array('attribute_code' => $attribute['code']);
            if ($formData['restock_date_mode'] == AmazonListing::RESTOCK_DATE_MODE_CUSTOM_ATTRIBUTE
                && $attribute['code'] == $formData['restock_date_custom_attribute']) {
                $attrs['selected'] = 'selected';
            }

            $preparedAttributes[] = array(
                'attrs' => $attrs,
                'value' => AmazonListing::RESTOCK_DATE_MODE_CUSTOM_ATTRIBUTE,
                'label' => $attribute['label'],
            );
        }

        $fieldset->addField(
            'restock_date_mode',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::SELECT,
            array(
                'name'                     => 'restock_date_mode',
                'label'                    => $helper->__('Restock Date'),
                'values'                   => array(
                    AmazonListing::RESTOCK_DATE_MODE_NONE         => $helper->__('None'),
                    AmazonListing::RESTOCK_DATE_MODE_CUSTOM_VALUE => $helper->__('Custom Value'),
                    array(
                        'label' => $helper->__('Magento Attributes'),
                        'value' => $preparedAttributes,
                        'attrs' => array('is_magento_attribute' => true)
                    )
                ),
                'create_magento_attribute' => true,
                'value'                    => $formData['restock_date_mode'] != AmazonListing::RESTOCK_DATE_MODE_CUSTOM_ATTRIBUTE
                    ? $formData['restock_date_mode'] : '',
                'tooltip'                  => $helper->__(
                    'The date you will be able to ship any back-ordered Items to a Customer.
                     Enter the date in the format YYYY-MM-DD.'
                ),
                'allowed_attribute_types'  => 'text,date'
            )
        );

        $fieldset->addField(
            'restock_date_value',
            'text',
            array(
                'container_id' => 'restock_date_value_tr',
                'name'         => 'restock_date_value',
                'label'        => $helper->__('Restock Date'),
                'required'     => true,
                'value'        => $formData['restock_date_value']
            )
        );

        $form->setUseContainer($this->_useFormContainer);
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
        Mage::helper('M2ePro/View')->getJsPhpRenderer()->addClassConstants('Ess_M2ePro_Model_Amazon_Listing');

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addUrls(
            array(
                'templateCheckMessages'         => $this->getUrl(
                    '*/adminhtml_template/checkMessages',
                    array(
                        'component_mode' => Ess_M2ePro_Helper_Component_Amazon::NICK
                    )
                ),
                'addNewSellingFormatTemplate'   => $this->getUrl(
                    '*/adminhtml_amazon_template_sellingFormat/new',
                    array(
                        'wizard'        => $this->getRequest()->getParam('wizard'),
                        'close_on_save' => 1
                    )
                ),
                'editSellingFormatTemplate'     => $this->getUrl(
                    '*/adminhtml_amazon_template_sellingFormat/edit',
                    array(
                        'wizard'        => $this->getRequest()->getParam('wizard'),
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
                        'component_mode' => Ess_M2ePro_Helper_Component_Amazon::NICK
                    )
                ),
                'addNewSynchronizationTemplate' => $this->getUrl(
                    '*/adminhtml_amazon_template_synchronization/new',
                    array(
                        'wizard'        => $this->getRequest()->getParam('wizard'),
                        'close_on_save' => 1
                    )
                ),
                'editSynchronizationTemplate'   => $this->getUrl(
                    '*/adminhtml_amazon_template_synchronization/edit',
                    array(
                        'wizard'        => $this->getRequest()->getParam('wizard'),
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
                        'component_mode' => Ess_M2ePro_Helper_Component_Amazon::NICK
                    )
                ),
                'addNewShippingTemplate'        => $this->getUrl(
                    '*/adminhtml_amazon_template_shipping/new',
                    array(
                        'wizard'        => $this->getRequest()->getParam('wizard'),
                        'close_on_save' => 1
                    )
                ),
                'editShippingTemplate'          => $this->getUrl(
                    '*/adminhtml_amazon_template_shipping/edit',
                    array(
                        'wizard'        => $this->getRequest()->getParam('wizard'),
                        'close_on_save' => 1
                    )
                ),
                'getShippingTemplates'          => $this->getUrl(
                    '*/adminhtml_general/modelGetAll',
                    array(
                        'model'      => 'Amazon_Template_Shipping',
                        'id_field'   => 'id',
                        'data_field' => 'title',
                        'sort_field' => 'title',
                        'sort_dir'   => 'ASC'
                    )
                )
            )
        );

        Mage::helper('M2ePro/View')->getJsTranslatorRenderer()->addTranslations(
            array(
                'condition_note_length_error'                    => Mage::helper('M2ePro')->__(
                    'Must be not more than 2000 characters long.'
                ),
                'sku_modification_custom_value_error'            => Mage::helper('M2ePro')->__(
                    '%value% placeholder should be specified'
                ),
                'sku_modification_custom_value_max_length_error' => Mage::helper('M2ePro')->__(
                    'The SKU length must be less than %value%.',
                    Ess_M2ePro_Helper_Component_Amazon::SKU_MAX_LENGTH
                )
            )
        );

        Mage::helper('M2ePro/View')->getJsRenderer()->addOnReadyJs(
            <<<JS
    M2ePro.formData.wizard = {$this->getRequest()->getParam('wizard', 0)};

    TemplateManagerObj = new TemplateManager();

    AmazonListingSettingsObj = new AmazonListingSettings();
    AmazonListingCreateSellingObj = new AmazonListingCreateSelling();
    
    AmazonListingSettingsObj.initObservers();
JS
        );

        return parent::_prepareLayout();
    }

    //########################################

    protected function getRecommendedConditionValues()
    {
        $formData = $this->getListingData();

        $recommendedValues = array(
            array(
                'attrs' => array('attribute_code' => AmazonListing::CONDITION_NEW),
                'value' => AmazonListing::CONDITION_MODE_DEFAULT,
                'label' => Mage::helper('M2ePro')->__('New'),
            ),
            array(
                'attrs' => array('attribute_code' => AmazonListing::CONDITION_USED_LIKE_NEW),
                'value' => AmazonListing::CONDITION_MODE_DEFAULT,
                'label' => Mage::helper('M2ePro')->__('Used - Like New'),
            ),
            array(
                'attrs' => array('attribute_code' => AmazonListing::CONDITION_USED_VERY_GOOD),
                'value' => AmazonListing::CONDITION_MODE_DEFAULT,
                'label' => Mage::helper('M2ePro')->__('Used - Very Good'),
            ),
            array(
                'attrs' => array('attribute_code' => AmazonListing::CONDITION_USED_GOOD),
                'value' => AmazonListing::CONDITION_MODE_DEFAULT,
                'label' => Mage::helper('M2ePro')->__('Used - Good'),
            ),
            array(
                'attrs' => array('attribute_code' => AmazonListing::CONDITION_USED_ACCEPTABLE),
                'value' => AmazonListing::CONDITION_MODE_DEFAULT,
                'label' => Mage::helper('M2ePro')->__('Used - Acceptable'),
            ),
            array(
                'attrs' => array('attribute_code' => AmazonListing::CONDITION_COLLECTIBLE_LIKE_NEW),
                'value' => AmazonListing::CONDITION_MODE_DEFAULT,
                'label' => Mage::helper('M2ePro')->__('Collectible - Like New'),
            ),
            array(
                'attrs' => array('attribute_code' => AmazonListing::CONDITION_COLLECTIBLE_VERY_GOOD),
                'value' => AmazonListing::CONDITION_MODE_DEFAULT,
                'label' => Mage::helper('M2ePro')->__('Collectible - Very Good'),
            ),
            array(
                'attrs' => array('attribute_code' => AmazonListing::CONDITION_COLLECTIBLE_GOOD),
                'value' => AmazonListing::CONDITION_MODE_DEFAULT,
                'label' => Mage::helper('M2ePro')->__('Collectible - Good'),
            ),
            array(
                'attrs' => array('attribute_code' => AmazonListing::CONDITION_COLLECTIBLE_ACCEPTABLE),
                'value' => AmazonListing::CONDITION_MODE_DEFAULT,
                'label' => Mage::helper('M2ePro')->__('Collectible - Acceptable'),
            ),
            array(
                'attrs' => array('attribute_code' => AmazonListing::CONDITION_REFURBISHED),
                'value' => AmazonListing::CONDITION_MODE_DEFAULT,
                'label' => Mage::helper('M2ePro')->__('Refurbished'),
            ),
            array(
                'attrs' => array('attribute_code' => AmazonListing::CONDITION_CLUB),
                'value' => AmazonListing::CONDITION_MODE_DEFAULT,
                'label' => Mage::helper('M2ePro')->__('Club'),
            )
        );

        foreach ($recommendedValues as &$value) {
            if ($value['attrs']['attribute_code'] == $formData['condition_value']) {
                $value['attrs']['selected'] = 'selected';
            }
        }

        return $recommendedValues;
    }

    //########################################

    protected function _toHtml()
    {
        if ($this->getListing()) {
            return parent::_toHtml();
        }

        /** @var Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Create_Breadcrumb $breadcrumb */
        $breadcrumb = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_create_breadcrumb');
        $breadcrumb->setSelectedStep(2);

        $helpBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_helpBlock',
            '',
            array(
                'content' => Mage::helper('M2ePro')->__(
                    <<<HTML
<p>On this Page you can specify main <strong>Selling Settings</strong> for Amazon Items you are going to sell using 
this M2E Pro Listing.</p>
<p>You can provide settings for SKU formating, appropriate Condition, Condition Note, Gift Wrap, Gift Message and 
also specify Additional Settings - Production Time and Restock Date.</p>
<p>In addition to, in this Section you can select Selling Policy that contains Settings connected with forming of 
Price, Quantity etc. and Synchronization Policy that describes Rules of Automatic Synchronization of Magento Product 
and Amazon Item.</p>
<p>More detailed information you can find <a href="%url%" target="_blank" class="external-link">here</a>.</p>
HTML
                    ,
                    Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(null, null, 'step-2-specify-selling-settings')
                ),
                'title'   => Mage::helper('M2ePro')->__('Selling Settings')
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
            'sku_mode'                      => AmazonListing::SKU_MODE_DEFAULT,
            'sku_custom_attribute'          => '',
            'sku_modification_mode'         => AmazonListing::SKU_MODIFICATION_MODE_NONE,
            'sku_modification_custom_value' => '',
            'generate_sku_mode'             => AmazonListing::GENERATE_SKU_MODE_NO,

            'template_selling_format_id'  => '',
            'template_synchronization_id' => '',
            'template_shipping_id'        => '',

            'condition_mode'             => AmazonListing::CONDITION_MODE_DEFAULT,
            'condition_value'            => AmazonListing::CONDITION_NEW,
            'condition_custom_attribute' => '',
            'condition_note_mode'        => AmazonListing::CONDITION_NOTE_MODE_NONE,
            'condition_note_value'       => '',

            'image_main_mode'          => AmazonListing::IMAGE_MAIN_MODE_NONE,
            'image_main_attribute'     => '',
            'gallery_images_mode'      => AmazonListing::GALLERY_IMAGES_MODE_NONE,
            'gallery_images_limit'     => '',
            'gallery_images_attribute' => '',

            'gift_wrap_mode'      => AmazonListing::GIFT_WRAP_MODE_NO,
            'gift_wrap_attribute' => '',

            'gift_message_mode'      => AmazonListing::GIFT_MESSAGE_MODE_NO,
            'gift_message_attribute' => '',

            'handling_time_mode'             => AmazonListing::HANDLING_TIME_MODE_NONE,
            'handling_time_value'            => '',
            'handling_time_custom_attribute' => '',

            'restock_date_mode'             => AmazonListing::RESTOCK_DATE_MODE_NONE,
            'restock_date_value'            => Mage::helper('M2ePro')->getCurrentGmtDate(),
            'restock_date_custom_attribute' => ''
        );
    }

    //########################################

    protected function getListingData()
    {
        if ($this->getRequest()->getParam('id') !== null) {
            $data = $this->getListing()->getData();
        } else {
            $data = Mage::helper('M2ePro/Data_Session')->getValue(
                AmazonListing::CREATE_LISTING_SESSION_DATA
            );
            $data = array_merge($this->getDefaultFieldsValues(), $data);
        }

        if ($this->getRequest()->getParam('id') !== null && !empty($data['restock_date_value']) && strtotime($data['restock_date_value'])) {
            $data['restock_date_value'] = Mage::helper('M2ePro')->gmtDateToTimezone($data['restock_date_value']);
        }

        return $data;
    }

    //########################################

    protected function getListing()
    {
        if ($this->_listing === null && $this->getRequest()->getParam('id')) {
            $this->_listing = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
                'Listing',
                $this->getRequest()->getParam('id')
            );
        }

        return $this->_listing;
    }

    //########################################

    protected function getSellingFormatTemplates()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Template_SellingFormat')->getCollection();
        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Amazon::NICK);
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
        /** @var $collection Ess_M2ePro_Model_Resource_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Template_Synchronization')->getCollection();
        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Amazon::NICK);
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

    protected function getShippingTemplates()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Amazon_Template_Shipping')->getCollection();
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

    //########################################

    /**
     * @param boolean $useFormContainer
     */
    public function setUseFormContainer($useFormContainer)
    {
        $this->_useFormContainer = $useFormContainer;

        return $this;
    }

    //########################################
}
