EbayTemplateDescriptionHandler = Class.create();
EbayTemplateDescriptionHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    initialize: function()
    {
        Validation.add('M2ePro-validate-condition-note-length', M2ePro.translator.translate('Seller Notes must be less then 1000 symbols.'), function(value) {

            if ($('condition_note_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_NOTE_MODE_NONE')) {
                return true;
            }

            return value.length <= 1000;
        });
    },

    // ---------------------------------------

    simple_mode_disallowed_hide: function()
    {
        $$('#template_description_data_container .simple_mode_disallowed').invoke('hide');
    },

    duplicate_click: function(headId, chapter_when_duplicate_text, templateNick)
    {
        var watermarkImageContainer = $('watermark_uploaded_image_container');

        if (watermarkImageContainer) {
            watermarkImageContainer.remove();
            $('watermark_image').setAttribute('class','M2ePro-required-when-visible');
            $$('#watermark_image_container td.label label').pop().insert('<span class="required">*</span>');
        }

        EbayTemplateEditHandlerObj.duplicate_click(headId, chapter_when_duplicate_text, templateNick);
    },

    // ---------------------------------------

    title_mode_change: function()
    {
        var self = EbayTemplateDescriptionHandlerObj;
        self.setTextVisibilityMode(this, 'custom_title_tr');
    },

    subtitle_mode_change: function()
    {
        var self = EbayTemplateDescriptionHandlerObj;
        self.setTextVisibilityMode(this, 'custom_subtitle_tr');
    },

    description_mode_change: function()
    {
        var self = EbayTemplateDescriptionHandlerObj;

        var viewEditCustomDescription = $('view_edit_custom_description');

        if (viewEditCustomDescription) {
            viewEditCustomDescription.hide();
        }

        $$('.c-custom_description_tr').invoke('hide');

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Description::DESCRIPTION_MODE_CUSTOM')) {
            if (viewEditCustomDescription) {
                viewEditCustomDescription.show();
                $$('.c-custom_description_tr').invoke('hide');
                return;
            }

            $$('.c-custom_description_tr').invoke('show');
        } else {
            if (viewEditCustomDescription) {
                viewEditCustomDescription.remove();
            }
        }
    },

    view_edit_custom_change: function()
    {
        $$('.c-custom_description_tr').invoke('show');
        $('view_edit_custom_description').hide();
    },

    item_condition_change: function()
    {
        $('condition_note_tr').show();
        $('condition_note_mode').simulate('change');

        var self = EbayTemplateDescriptionHandlerObj,
            isConditionNoteNeeded = true,

            conditionValue = $('condition_value'),
            conditionAttribute = $('condition_attribute');

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_MODE_NONE')) {
            isConditionNoteNeeded = false;
        } else if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_MODE_EBAY')) {
            self.updateHiddenValue(this, conditionValue);
            conditionAttribute.value = '';
            isConditionNoteNeeded = conditionValue.value != M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_NEW');
        } else {
            self.updateHiddenValue(this, conditionAttribute);
            conditionValue.value = '';
        }

        if (!isConditionNoteNeeded) {

            $('condition_note_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_NOTE_MODE_NONE');
            $('condition_note_mode').simulate('change');

            $('condition_note_tr').hide();
            $('custom_condition_note_tr').hide();
        }
    },

    condition_note_mode_change: function()
    {
        var self = EbayTemplateDescriptionHandlerObj;

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_NOTE_MODE_NONE')) {
            $('condition_note_template').update('');
        }

        self.setTextVisibilityMode(this, 'custom_condition_note_tr');
    },

    watermark_mode_change: function()
    {
        var self = EbayTemplateDescriptionHandlerObj;
        self.setTextVisibilityMode(this, 'watermark_image_container');
        if ($('watermark_uploaded_image_container')) {
            self.setTextVisibilityMode(this, 'watermark_uploaded_image_container');
        }

        if ($('watermark_scale').value != M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Description::WATERMARK_SCALE_MODE_STRETCH')) {
            self.setTextVisibilityMode(this, 'watermark_position_container');
        }

        self.setTextVisibilityMode(this, 'watermark_scale_container');
        self.setTextVisibilityMode(this, 'watermark_transparent_container');
    },

    watermark_scale_mode_change: function()
    {
        var self = EbayTemplateDescriptionHandlerObj;

        $('watermark_position_container').show();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Description::WATERMARK_SCALE_MODE_STRETCH')) {
            $('watermark_position_container').hide();
        }
    },

    setTextVisibilityMode: function(obj, elementName)
    {
        var self = EbayTemplateDescriptionHandlerObj;

        $(elementName).hide();

        if (obj.value == 1) {
            $(elementName).show();
        }
    },

    // ---------------------------------------

    image_main_change: function()
    {
        var self = EbayTemplateDescriptionHandlerObj;

        $('gallery_images_mode_tr', 'variation_images_mode_tr', 'use_supersize_images_tr',
          'default_image_url_tr', 'watermark_block').invoke('show');

        if ($$('#variation_configurable_images option').length > 1) {
            $('variation_configurable_images_container').show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Description::IMAGE_MAIN_MODE_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('image_main_attribute'));
        } else {
            $('image_main_attribute').value = '';
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Description::IMAGE_MAIN_MODE_NONE')) {
            $('gallery_images_mode_tr', 'variation_images_mode_tr', 'variation_configurable_images_container',
              'use_supersize_images_tr', 'default_image_url_tr').invoke('hide');

            $('use_supersize_images').value = 0;

            $('gallery_images').value = 0;
            $('gallery_images').simulate('change');

            $('variation_configurable_images').value = '';

            $('variation_images').value = 1;
            $('variation_images_limit').value = 1;
            $('variation_images').simulate('change');

            $('default_image_url').value = '';

            $('watermark_block').hide();

            var watermarkMode = $('watermark_mode');

            if (watermarkMode.selectedIndex != 0) {
                watermarkMode.selectedIndex = 0;
                watermarkMode.simulate('change');
            }
        }
    },

    gallery_images_change: function()
    {
        var self = EbayTemplateDescriptionHandlerObj;

        $('gallery_images_limit').value = '';
        $('gallery_images_attribute').value = '';

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Description::GALLERY_IMAGES_MODE_PRODUCT')) {
            self.updateHiddenValue(this, $('gallery_images_limit'));
        } else if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Description::GALLERY_IMAGES_MODE_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('gallery_images_attribute'));
        }
    },

    variation_images_change: function()
    {
        var self = EbayTemplateDescriptionHandlerObj;

        $('variation_images_limit').value = '';
        $('variation_images_attribute').value = '';

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Description::VARIATION_IMAGES_MODE_PRODUCT')) {
            self.updateHiddenValue(this, $('variation_images_limit'));
        } else if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Description::VARIATION_IMAGES_MODE_ATTRIBUTE')) {
            self.updateHiddenValue(this, $('variation_images_attribute'));
        }
    },

    // ---------------------------------------

    image_width_mode_change: function()
    {
        $('image_width_span')[this.value == 1 ? 'show' : 'hide']();
    },

    image_height_mode_change: function()
    {
        $('image_height_span')[this.value == 1 ? 'show' : 'hide']();
    },

    image_margin_mode_change: function()
    {
        $('image_margin_span')[this.value == 1 ? 'show' : 'hide']();
    },

    select_attributes_image_change: function()
    {
        $$('.all-products-images').invoke(this.value == 'media_gallery' ? 'show' : 'hide');
        $$('.all-products-image').invoke(this.value == 'image' ? 'show' : 'hide');
        if (this.value == 'image') {
            $('display_products_images').value = 'custom_settings';
        }
        $('display_products_images').simulate('change');
    },

    display_products_images_change: function()
    {
        $$('.products-images-custom-settings').invoke('hide');
        $$('.products-images-gallery-view').invoke('hide');

        if (this.value == 'gallery_view') {
            $$('.products-images-gallery-view').invoke('show');
        } else {
            $$('.products-images-custom-settings').invoke('show');
        }
    },

    product_details_specification_visibility_change: function()
    {
        var self = EbayTemplateDescriptionHandlerObj,
            modeNone = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Description::PRODUCT_DETAILS_MODE_NONE'),
            modeDoesNotApply = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Description::PRODUCT_DETAILS_MODE_DOES_NOT_APPLY');

        var isNotAttributeMode = function(element) {
            return element.value == modeNone || element.value == modeDoesNotApply;
        };

        if ($('product_details_ean', 'product_details_upc', 'product_details_isbn', 'product_details_brand')
                .every(isNotAttributeMode) && $('product_details_epid').value == modeNone
        ) {
            $$('.product-details-specification').each(function(element) {
                element.hide();
                element.down('.value').down().selectedIndex = 1;
            });
        } else {
            var hiddenElement = $(this.id+'_attribute');
            if (!hiddenElement) {
                return;
            }
            $$('.product-details-specification').invoke('show');
            self.updateHiddenValue(this, hiddenElement);
        }
    },

    product_details_brand_change: function()
    {
        var self = EbayTemplateDescriptionHandlerObj;

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Description::PRODUCT_DETAILS_MODE_NONE') ||
            this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Description::PRODUCT_DETAILS_MODE_DOES_NOT_APPLY')) {
            $('product_details_mpn_tr').hide();
            $('product_details_mpn').selectedIndex = 0;
        } else {
            $('product_details_mpn_tr').show();
            $$('.product-details-specification').invoke('show');
            self.updateHiddenValue(this, $(this.id+'_attribute'));
        }

        self.product_details_specification_visibility_change();
    },

    product_details_mpn_change: function()
    {
        var self = EbayTemplateDescriptionHandlerObj;

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Description::PRODUCT_DETAILS_MODE_ATTRIBUTE')) {
            self.updateHiddenValue(this, $(this.id+'_attribute'));
        }
    },

    insertGallery: function()
    {
        var template = '#' + $('select_attributes_image').value;

        if ($('image_width_mode').value == '1') {
            template += '[' + $('image_width').value + ',';
        } else {
            template += '[,';
        }

        if ($('image_height_mode').value == '1') {
            template += '' + $('image_height').value + ',';
        } else {
            template += ',';
        }

        if ($('image_margin_mode').value == '1') {
            template += '' + $('image_margin').value + ',';
        } else {
            template += ',';
        }

        if ($('select_attributes_image').value == 'media_gallery' && $('display_products_images').value == 'gallery_view')  {
            template += '2';
        } else if ($('image_linked_mode').value == '1') {
            template += '1';
        } else {
            template += "0";
        }

        if ($('select_attributes_image').value == 'media_gallery') {
            template += ',' + $('select_attributes_image_layout').value + ',' + $('select_attributes_image_count').value;
        }

        if ($('select_attributes_image').value == 'media_gallery' && $('display_products_images').value == 'gallery_view') {
            template += ',"' + $('gallery_hint_text').value + '"';
        } else if ($('select_attributes_image').value == 'media_gallery') {
            // media_gallery with empty gallery hint
            template += ',""';
        }

        if ($('image_insertion_watermark_mode').value == '1') {
            template += ',1';
        } else {
            template += ',';
        }

        if ($('select_attributes_image').value == 'image') {
            template += ',' + $('select_attributes_image_order_position').value;
        }

        template += ']#';

        AttributeHandlerObj.appendToTextarea(template);
        EbayTemplateDescriptionHandlerObj.stopObservingImageAttributes();
    },

    // ---------------------------------------

    observeImageAttributes: function()
    {
        $('image_width_mode').observe('change', EbayTemplateDescriptionHandlerObj.image_width_mode_change);
        $('image_height_mode').observe('change', EbayTemplateDescriptionHandlerObj.image_height_mode_change);
        $('image_margin_mode').observe('change', EbayTemplateDescriptionHandlerObj.image_margin_mode_change);

        $('select_attributes_image')
                .observe('change', EbayTemplateDescriptionHandlerObj.select_attributes_image_change)
                .simulate('change');

        $('display_products_images')
                .observe('change', EbayTemplateDescriptionHandlerObj.display_products_images_change);
    },

    stopObservingImageAttributes: function()
    {
        $('image_width_mode').stopObserving();
        $('image_height_mode').stopObserving();
        $('image_margin_mode').stopObserving();

        $('select_attributes_image').stopObserving();

        dialog_image_window.close();
    },

    openInsertImageWindow: function()
    {
        var self = EbayTemplateDescriptionHandlerObj;

        dialog_image_window = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: 'magento',
            windowClassName: 'popup-window',
            title: M2ePro.translator.translate('Adding Image'),
            top: 150,
            width: 650,
            height: 350,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show,
            id: 'new-image',
            closeCallback: function() {
                $('image_insertion_container').appendChild($('image_insertion'));

                return true;
            }
        });

        $('modal_dialog_message').appendChild($('image_insertion'));

        if($('watermark_mode').value == 1) {
            self.setTextVisibilityMode($('watermark_mode'), 'products_images_watermark_mode');
            $('image_insertion_watermark_mode').selectedIndex = 1;
        } else {
            self.setTextVisibilityMode($('watermark_mode'), 'products_images_watermark_mode');
            $('image_insertion_watermark_mode').selectedIndex = 0;
        }

        $('new-image_close').writeAttribute('onclick', 'EbayTemplateDescriptionHandlerObj.stopObservingImageAttributes()');

        EbayTemplateDescriptionHandlerObj.observeImageAttributes();
    },

    // ---------------------------------------

    saveWatermarkImage: function(callback, params)
    {
        var form  = $('edit_form');

        form.action = M2ePro.url.get('adminhtml_ebay_template_description/saveWatermarkImage');
        form.target = 'watermark_image_frame';

        if ($('watermark_image_frame') === null) {
            document.body.appendChild(new Element('iframe', {
                id: 'watermark_image_frame',
                name: 'watermark_image_frame',
                style: 'display: none'
            }));
        }

        $('watermark_image_frame').observe('load',function() {
            if (typeof callback == 'function') {
                callback(params);
            }
        });

        form.submit();
    },

    // ---------------------------------------

    preview_click: function()
    {
        this.submitForm(
            M2ePro.url.get('adminhtml_ebay_template_description/preview'), true
        );
    }

    // ---------------------------------------
});