window.EbayTemplateSellingFormat = Class.create(Common, {

    // ---------------------------------------

    tempOrganizationEl: {},

    // ---------------------------------------

    initialize: function()
    {
        var self = this;
        Validation.add('M2ePro-validate-price-coefficient', M2ePro.translator.translate('Price Change is not valid.'), function(value, el) {

            var tempEl = el;

            if (self.isElementHiddenFromPage(el)) {
                return true;
            }

            var coefficient = el.up().next().down('input');

            coefficient.removeClassName('price_unvalidated');

            if (!coefficient.up('div').visible()) {
                return true;
            }

            if (coefficient.value == '') {
                return false;
            }

            var floatValidator = Validation.get('M2ePro-validation-float');
            if (floatValidator.test($F(coefficient), coefficient) && parseFloat(coefficient.value) <= 0) {
                coefficient.addClassName('price_unvalidated');
                return false;
            }

            return true;
        });

        Validation.add('validate-qty', M2ePro.translator.translate('Wrong value. Only integer numbers.'), function(value, el) {

            if (self.isElementHiddenFromPage(el)) {
                return true;
            }

            if (value.match(/[^\d]+/g) || value <= 0) {
                return false;
            }

            return true;
        });

        Validation.add('M2ePro-validate-vat', M2ePro.translator.translate('Wrong value. Must be no more than 30. Max applicable length is 6 characters, including the decimal (e.g., 12.345).'), function(value) {

            if (!value) {
                return true;
            }

            if (value.length > 6) {
                return false;
            }

            if (value < 0) {
                return false;
            }

            value = Math.ceil(value);

            return value >= 0 && value <= 30;
        });

        Validation.add('M2ePro-lot-size', M2ePro.translator.translate('Wrong value. Lot Size must be from 2 to 100000 Items.'), function(value, element) {

            if (Validation.get('IsEmpty').test(value)) {
                return true;
            }

            var numValue = parseNumber(value);
            if (isNaN(numValue)) {
                return false;
            }

            return numValue >= 2 && numValue <= 100000;
        });
    },

    // ---------------------------------------

    isStpAvailable: function()
    {
        return M2ePro.formData.isStpEnabled;
    },

    isMapAvailable: function()
    {
        return M2ePro.formData.isMapEnabled;
    },

    isStpAdvancedAvailable: function()
    {
        return M2ePro.formData.isStpAdvancedEnabled;
    },

    // ---------------------------------------

    listing_type_change: function(event)
    {
        var self             = EbayTemplateSellingFormatObj,

            bestOfferBlock   = $('magento_block_ebay_template_selling_format_edit_form_best_offer'),
            bestOfferMode    = $('best_offer_mode'),
            attributeElement = $('listing_type_attribute');

        $('fixed_price_tr', 'start_price_tr', 'reserve_price_tr', 'buyitnow_price_tr').invoke('show');
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_FIXED')) {
            $('start_price_tr','reserve_price_tr', 'buyitnow_price_tr').invoke('hide');
            $$('#variation_price_tr .value').invoke('show');
        }

        attributeElement.innerHTML = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_ATTRIBUTE')) {
            self.selectMagentoAttribute(this, attributeElement);
        }

        bestOfferBlock.show();
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION')) {
            $('fixed_price_tr').hide();
            bestOfferBlock.hide();
            bestOfferMode.value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::BEST_OFFER_MODE_NO');
            bestOfferMode.simulate('change');
        }

        self.updateQtyMode();
        self.updateQtyPercentage();
        self.updateIgnoreVariations();
        self.updateLotSize();
        self.updateListingDuration();
        self.updateFixedPrice();
        self.updatePriceDiscountStpVisibility();
        self.updatePriceDiscountMapVisibility();
        self.updateVariationPriceTrVisibility();
    },

    duration_attribute_change: function()
    {
        EbayTemplateSellingFormatObj.updateHiddenValue(this, $('listing_duration_attribute_value'));
    },

    updateQtyMode: function()
    {
        var qtyMode        = $('qty_mode'),
            qtyModeTr      = $('qty_mode_tr'),
            qtyCustomValue = $('qty_custom_value'),
            customValueTr  = $('qty_mode_cv_tr');

        qtyModeTr.show();
        qtyMode.simulate('change');
        if ($('listing_type').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION')) {
            qtyMode.value = M2ePro.php.constant('Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_NUMBER');
            qtyCustomValue.value = 1;
            qtyMode.simulate('change');
            qtyModeTr.hide();
            customValueTr.hide();
        }
    },

    updateQtyPercentage: function()
    {
        var qtyPercentageTr = $('qty_percentage_tr');

        qtyPercentageTr.hide();

        if ($('listing_type').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION')) {
            return;
        }

        var qtyMode = $('qty_mode').value;

        if (qtyMode == M2ePro.php.constant('Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_NUMBER')) {
            return;
        }

        qtyPercentageTr.show();
    },

    updateIgnoreVariations: function()
    {
        var ignoreVariationsValueTr = $('ignore_variations_value_tr'),
            ignoreVariationsValue = $('ignore_variations_value');

        ignoreVariationsValueTr.hide();

        if ($('listing_type').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION')) {
            ignoreVariationsValue.value = 0;
        } else {
            ignoreVariationsValueTr.show();
        }
    },

    updateLotSize: function()
    {
        var lotSizeCustomValueTr = $('lot_size_cv_tr');

        if ($('lot_size_mode').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LOT_SIZE_MODE_CUSTOM_VALUE')) {
            lotSizeCustomValueTr.show();
        } else {
            lotSizeCustomValueTr.hide();
            $('lot_size_custom_value').value = '';
        }
    },

    updateListingDuration: function()
    {
        var durationMode      = $('duration_mode'),
            durationAttribute = $('duration_attribute');

        $$('.durationId').invoke('show');
        $$('.duration_note').invoke('hide');
        durationMode.removeClassName('disabled');

        durationMode.show();
        durationAttribute.hide();

        if ($('listing_type').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_FIXED')) {

            $$('.durationId').invoke('hide');
            $('durationId100').show();

            durationMode.addClassName('disabled');
            durationMode.value = 100;

            $$('.duration_fixed_note').invoke('show');
        }

        if ($('listing_type').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION')) {

            durationMode.value = 3;

            $('durationId30', 'durationId100').invoke('hide');
            if (M2ePro.formData.duration_mode && M2ePro.formData.duration_mode != 30 && M2ePro.formData.duration_mode != 100) {
                durationMode.value = M2ePro.formData.duration_mode;
            }

            $$('.duration_auction_note').invoke('show');
        }

        if ($('listing_type').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_ATTRIBUTE')) {
            durationMode.hide();
            durationAttribute.show();
            $$('.duration_attribute_note').invoke('show');
        }
    },

    updateVariationPriceTrVisibility: function()
    {
        var removeBottomBorderTds = $$('#fixed_price_tr td.remove_bottom_border'),
            addRowspanTds         = $$('#fixed_price_tr td.add_rowspan'),
            priceModeSelect       = $('fixed_price_mode'),
            variationPriceTr      = $('variation_price_tr');

        variationPriceTr.hide();
        removeBottomBorderTds.invoke('removeClassName','bottom_border_disabled');
        addRowspanTds.invoke('removeAttribute','rowspan');

        if ($('listing_type').value != M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION')) {
            variationPriceTr.show();
            addRowspanTds.invoke('setAttribute','rowspan','2');
            if(priceModeSelect.value != M2ePro.php.constant('Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_NONE')) {
                removeBottomBorderTds.invoke('addClassName','bottom_border_disabled');
            }
        }
    },

    updateFixedPrice: function()
    {
        var priceLabel                      = $('fixed_price_label'),
            bestOfferAcceptPercentageOption = $('best_offer_accept_percentage_option'),
            bestOfferRejectPercentageOption = $('best_offer_reject_percentage_option');

        priceLabel.innerHTML = M2ePro.translator.translate('Fixed Price') + ': ';
        bestOfferAcceptPercentageOption.innerHTML = M2ePro.translator.translate('% of Fixed Price');
        bestOfferRejectPercentageOption.innerHTML = M2ePro.translator.translate('% of Fixed Price');

        if ($('listing_type').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_FIXED')) {
            priceLabel.innerHTML = M2ePro.translator.translate('Price') + ': ';
            bestOfferAcceptPercentageOption.innerHTML = M2ePro.translator.translate('% of Price');
            bestOfferRejectPercentageOption.innerHTML = M2ePro.translator.translate('% of Price');
        }
    },

    updatePriceDiscountStpVisibility: function()
    {
        var priceDiscTrStp = $('price_discount_stp_tr'),
            priceDiscStpMode = $('price_discount_stp_mode');

        priceDiscTrStp.hide();
        if (EbayTemplateSellingFormatObj.isStpAvailable()) {
            priceDiscTrStp.show();
        }

        if ($('listing_type').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION')) {
            priceDiscTrStp.hide();
            priceDiscStpMode.value = M2ePro.php.constant('Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_NONE');
            priceDiscStpMode.simulate('change');
        }
    },

    updatePriceDiscountMapVisibility: function()
    {
        var priceDiscTrMap = $('price_discount_map_tr'),
            priceDiscMapMode = $('price_discount_map_mode');

        priceDiscTrMap.hide();
        if (EbayTemplateSellingFormatObj.isMapAvailable()) {
            priceDiscTrMap.show();
        }

        if ($('listing_type').value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION')) {
            priceDiscTrMap.hide();
            priceDiscMapMode.value = M2ePro.php.constant('Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_NONE');
            priceDiscMapMode.simulate('change');
        }
    },

    // ---------------------------------------

    qty_mode_change: function()
    {
        var self               = EbayTemplateSellingFormatObj,

            customValueTr      = $('qty_mode_cv_tr'),
            attributeElement   = $('qty_custom_attribute'),

            maxPostedValueTr   = $('qty_modification_mode_tr'),
            maxPostedValueMode = $('qty_modification_mode');

        customValueTr.hide();
        attributeElement.value = '';

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_NUMBER')) {
            customValueTr.show();
        } else if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_ATTRIBUTE')) {
            self.selectMagentoAttribute(this, attributeElement);
        }

        maxPostedValueTr.hide();
        maxPostedValueMode.value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODIFICATION_MODE_OFF');

        if (self.isMaxPostedQtyAvailable(this.value)) {

            maxPostedValueTr.show();
            maxPostedValueMode.value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODIFICATION_MODE_ON');

            if (self.isMaxPostedQtyAvailable(M2ePro.formData.qty_mode)) {
                maxPostedValueMode.value = M2ePro.formData.qty_modification_mode;
            }
        }

        maxPostedValueMode.simulate('change');

        self.updateQtyPercentage();
    },

    isMaxPostedQtyAvailable: function(qtyMode)
    {
        return qtyMode == M2ePro.php.constant('Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT') ||
               qtyMode == M2ePro.php.constant('Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_ATTRIBUTE') ||
               qtyMode == M2ePro.php.constant('Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED');
    },

    qtyPostedMode_change: function()
    {
        var minPosterValueTr = $('qty_min_posted_value_tr'),
            maxPosterValueTr = $('qty_max_posted_value_tr');

        minPosterValueTr.hide();
        maxPosterValueTr.hide();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::QTY_MODIFICATION_MODE_ON')) {
            minPosterValueTr.show();
            maxPosterValueTr.show();
        }
    },

    lotSizeMode_change: function()
    {
        var self = EbayTemplateSellingFormatObj,

            lotSizeCustomValueTr = $('lot_size_cv_tr'),
            attributeElement   = $('lot_size_attribute');

        lotSizeCustomValueTr.hide();
        attributeElement.value = '';

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LOT_SIZE_MODE_CUSTOM_VALUE')) {
            lotSizeCustomValueTr.show();
        } else if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LOT_SIZE_MODE_ATTRIBUTE')) {
            self.selectMagentoAttribute(this, attributeElement);
        }

        if (this.value != M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LOT_SIZE_MODE_CUSTOM_VALUE')) {
            $('lot_size_custom_value').value = '';
        }
    },

    // ---------------------------------------

    vatModeChange: function ()
    {
        var vatPercentTr = $('vat_percent_tr');

        vatPercentTr.hide();

        if (this.value != M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::VAT_MODE_NO')) {
            vatPercentTr.show();
        }
    },

    // ---------------------------------------

    taxCategoryChange: function()
    {
        var self = EbayTemplateSellingFormatObj,
            valueEl     = $('tax_category_value'),
            attributeEl = $('tax_category_attribute');

        valueEl.value     = '';
        attributeEl.value = '';

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::TAX_CATEGORY_MODE_VALUE')) {
            self.updateHiddenValue(this, valueEl);
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::TAX_CATEGORY_MODE_ATTRIBUTE')) {
            self.updateHiddenValue(this, attributeEl);
        }
    },

    // ---------------------------------------

    fixed_price_mode_change: function()
    {
        var self             = EbayTemplateSellingFormatObj,
            listingType             = $('listing_type'),
            currencyTd              = $('fixed_price_currency_td'),
            attributeElement        = $('fixed_price_custom_attribute'),
            priceChangeTd           = $('fixed_price_change_td'),
            priceChangeTds          = $$('#fixed_price_tr td.remove_bottom_border'),
            variationPriceSelect    = $$('#variation_price_tr .value');

        variationPriceSelect.invoke('hide');
        priceChangeTds.invoke('removeClassName','bottom_border_disabled');
        priceChangeTd.hide();
        currencyTd && currencyTd.hide();

        if (this.value != M2ePro.php.constant('Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_NONE')) {
            priceChangeTd.show();
            currencyTd && currencyTd.show();
            variationPriceSelect.invoke('show');
            if(listingType.value != M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION')) {
                priceChangeTds.invoke('addClassName','bottom_border_disabled');
            }
        }

        attributeElement.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE')) {
            self.selectMagentoAttribute(this, attributeElement);
        }
    },

    start_price_mode_change: function()
    {
        var self             = EbayTemplateSellingFormatObj,
            attributeElement = $('start_price_custom_attribute');

        attributeElement.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE')) {
            self.selectMagentoAttribute(this, attributeElement);
        }
    },

    reserve_price_mode_change: function()
    {
        var self             = EbayTemplateSellingFormatObj,
            attributeElement = $('reserve_price_custom_attribute'),
            priceChangeTd    = $('reserve_price_change_td'),
            currencyTd       = $('reserve_price_currency_td');

        priceChangeTd.hide();
        currencyTd && currencyTd.hide();

        if (this.value != M2ePro.php.constant('Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_NONE')) {
            priceChangeTd.show();
            currencyTd && currencyTd.show();
        }

        attributeElement.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE')) {
            self.selectMagentoAttribute(this, attributeElement);
        }
    },

    buyitnow_price_mode_change: function()
    {
        var self = EbayTemplateSellingFormatObj,
            attributeElement        = $('buyitnow_price_custom_attribute'),
            priceChangeTd           = $('buyitnow_price_change_td'),
            currencyTd              = $('buyitnow_price_currency_td');

        priceChangeTd.hide();
        currencyTd && currencyTd.hide();

        if (this.value != M2ePro.php.constant('Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_NONE')) {
            priceChangeTd.show();
            currencyTd && currencyTd.show();
        }

        attributeElement.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE')) {
            self.selectMagentoAttribute(this, attributeElement);
        }
    },

    price_coefficient_mode_change: function()
    {
        var coefficientInputDiv = $(this.id.replace('mode','') + 'input_div'),
            signSpan            = $(this.id.replace('mode','') + 'sign_span'),
            percentSpan         = $(this.id.replace('mode','') + 'percent_span'),
            examplesContainer   = $(this.id.replace('coefficient_mode','') + 'example_container');

        // ---------------------------------------

        coefficientInputDiv.show();
        examplesContainer.show();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_NONE')) {
            coefficientInputDiv.hide();
            examplesContainer.hide();
        }
        // ---------------------------------------

        // ---------------------------------------
        signSpan.innerHTML    = '';
        percentSpan.innerHTML = '';
        $$('.' + this.id.replace('coefficient_mode','') + 'example').invoke('hide');

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_ABSOLUTE_INCREASE')) {
            signSpan.innerHTML = '+';

            if (typeof M2ePro.formData.currency != 'undefined') {
                percentSpan.innerHTML = M2ePro.formData.currency;
            }

            $(this.id.replace('coefficient_mode','') + 'example_absolute_increase').show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_ABSOLUTE_DECREASE')) {
            signSpan.innerHTML = '-';

            if (typeof M2ePro.formData.currency != 'undefined') {
                percentSpan.innerHTML = M2ePro.formData.currency;
            }

            $(this.id.replace('coefficient_mode','') + 'example_absolute_decrease').show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_PERCENTAGE_INCREASE')) {
            signSpan.innerHTML = '+';
            percentSpan.innerHTML = '%';

            $(this.id.replace('coefficient_mode','') + 'example_percentage_increase').show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_PERCENTAGE_DECREASE')) {
            signSpan.innerHTML = '-';
            percentSpan.innerHTML = '%';

            $(this.id.replace('coefficient_mode','') + 'example_percentage_decrease').show();
        }
        // ---------------------------------------
    },

    price_discount_stp_mode_change: function()
    {
        var attributeElement         = $('price_discount_stp_attribute'),
            priceDiscountStpTds      = $$('#price_discount_stp_tr td.remove_bottom_border'),
            priceDiscountStpReasonTr = $('price_discount_stp_reason_tr'),
            currencyTd               = $('price_discount_stp_currency_td');

        priceDiscountStpReasonTr.hide();
        currencyTd && currencyTd.hide();
        priceDiscountStpTds.invoke('removeClassName','bottom_border_disabled');

        if (this.value != M2ePro.php.constant('Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_NONE')) {
            currencyTd && currencyTd.show();

            if (EbayTemplateSellingFormatObj.isStpAdvancedAvailable()) {
                priceDiscountStpReasonTr.show();
                priceDiscountStpTds.invoke('addClassName','bottom_border_disabled');
            }
        }

        attributeElement.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE')) {
            EbayTemplateSellingFormatObj.selectMagentoAttribute(this, attributeElement);
        }
    },

    price_discount_map_mode_change: function()
    {
        var attributeElement           = $('price_discount_map_attribute'),
            priceDiscountMapExposureTr = $('price_discount_map_exposure_tr'),
            currencyTd                 = $('price_discount_map_currency_td');

        priceDiscountMapExposureTr.hide();
        currencyTd && currencyTd.hide();

        if (this.value != M2ePro.php.constant('Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_NONE')) {
            currencyTd && currencyTd.show();

            if (EbayTemplateSellingFormatObj.isMapAvailable()) {
                priceDiscountMapExposureTr.show();
            }
        }

        attributeElement.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE')) {
            EbayTemplateSellingFormatObj.selectMagentoAttribute(this, attributeElement);
        }
    },

    // ---------------------------------------

    best_offer_mode_change: function()
    {
        var bestOfferRespondTable = $('best_offer_respond_table');

        bestOfferRespondTable.hide();
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::BEST_OFFER_MODE_YES')) {
            bestOfferRespondTable.show();
            $('best_offer_reject_mode','best_offer_accept_mode').invoke('simulate','change');
        } else {
            $('template_selling_format_messages-best_offer').innerHTML = '';
        }
    },

    best_offer_accept_mode_change: function()
    {
        var self                   = EbayTemplateSellingFormatObj,

            bestOfferAcceptValueTr = $('best_offer_accept_value_tr'),
            attributeElement       = $('best_offer_accept_custom_attribute');

        bestOfferAcceptValueTr.hide();
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::BEST_OFFER_ACCEPT_MODE_PERCENTAGE')) {
            bestOfferAcceptValueTr.show();
        }

        attributeElement.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::BEST_OFFER_ACCEPT_MODE_ATTRIBUTE')) {
            self.selectMagentoAttribute(this, attributeElement);
        }
    },

    best_offer_reject_mode_change: function()
    {
        var self                   = EbayTemplateSellingFormatObj,
            bestOfferRejectValueTr = $('best_offer_reject_value_tr'),
            attributeElement       = $('best_offer_reject_custom_attribute');

        bestOfferRejectValueTr.hide();
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::BEST_OFFER_REJECT_MODE_PERCENTAGE')) {
            bestOfferRejectValueTr.show();
        }

        attributeElement.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_SellingFormat::BEST_OFFER_REJECT_MODE_ATTRIBUTE')) {
            self.selectMagentoAttribute(this, attributeElement);
        }
    },

    // ---------------------------------------

    selectMagentoAttribute: function(elementSelect, elementAttribute)
    {
        var attributeCode = elementSelect.options[elementSelect.selectedIndex].getAttribute('attribute_code');
        elementAttribute.value = attributeCode;
    },

    // ---------------------------------------

    checkBestOfferMessages: function ()
    {
        var formElements = $(
            "best_offer_mode",
            "best_offer_accept_mode",
            "best_offer_accept_custom_attribute",
            "best_offer_reject_mode",
            "best_offer_reject_custom_attribute"
        );

        var isVisible = $('best_offer_respond_table').visible();

        if (!isVisible) {
            return false;
        }

        this.checkMessages(Form.serializeElements(formElements), 'template_selling_format_messages-best_offer')
    },

    checkPriceMessages: function () {

        var formElements = Form.getElements('template_selling_format_data_container'),
            excludedElements = $(
                "best_offer_mode",
                "best_offer_accept_mode",
                "best_offer_accept_custom_attribute",
                "best_offer_reject_mode",
                "best_offer_reject_custom_attribute"
            );

        formElements = formElements.filter(function (element) {
            return excludedElements.indexOf(element) < 0;
        });

        if (formElements.length === 0) {
            return false;
        }

        this.checkMessages(Form.serializeElements(formElements), 'template_selling_format_messages');
    },

    checkMessages: function(data, container)
    {
        if (typeof EbayListingTemplateSwitcherObj == 'undefined') {
            // not inside template switcher
            return;
        }

        var id = '',
            nick = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT'),
            storeId = EbayListingTemplateSwitcherObj.storeId,
            marketplaceId = EbayListingTemplateSwitcherObj.marketplaceId,
            callback = function() {
                var refresh = $(container).down('a.refresh-messages');
                if (refresh) {
                    refresh.observe('click', function() {
                        this.checkMessages(data, container);
                    }.bind(this))
                }
            }.bind(this);

        TemplateManagerObj.checkMessages(
            id,
            nick,
            data,
            storeId,
            marketplaceId,
            container,
            callback
        );
    },
});
