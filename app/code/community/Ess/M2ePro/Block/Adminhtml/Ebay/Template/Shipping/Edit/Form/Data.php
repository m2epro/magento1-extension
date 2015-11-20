<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Shipping_Edit_Form_Data extends Mage_Adminhtml_Block_Widget
{
    public $attributes = array();
    private $formData = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayTemplateShippingEditFormData');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/template/shipping/form/data.phtml');

        $this->attributes = Mage::helper('M2ePro/Data_Global')->getValue('ebay_attributes');
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getMarketplace()
    {
        $marketplace = Mage::helper('M2ePro/Data_Global')->getValue('ebay_marketplace');

        if (!$marketplace instanceof Ess_M2ePro_Model_Marketplace) {
            throw new Ess_M2ePro_Model_Exception_Logic('Marketplace is required for editing Shipping Policy.');
        }

        return $marketplace;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        $account = Mage::helper('M2ePro/Data_Global')->getValue('ebay_account');

        if (!$account instanceof Ess_M2ePro_Model_Account) {
            return NULL;
        }

        return $account;
    }

    public function getAccountId()
    {
        return $this->getAccount() ? $this->getAccount()->getId() : NULL;
    }

    //########################################

    public function getDiscountProfiles()
    {
        $template = Mage::helper('M2ePro/Data_Global')->getValue('ebay_template_shipping');

        $localDiscount = $template->getData('local_shipping_discount_profile_id');
        $internationalDiscount = $template->getData('international_shipping_discount_profile_id');

        !is_null($localDiscount) && $localDiscount = json_decode($localDiscount, true);
        !is_null($internationalDiscount) && $internationalDiscount = json_decode($internationalDiscount, true);

        $accountCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');

        $profiles = array();

        foreach ($accountCollection as $account) {
            $accountId = $account->getId();

            $temp = array();
            $temp['account_name'] = $account->getTitle();
            $temp['selected']['local'] = isset($localDiscount[$accountId]) ? $localDiscount[$accountId] : '';
            $temp['selected']['international'] = isset($internationalDiscount[$accountId]) ?
                $internationalDiscount[$accountId] : '';

            $accountProfiles = $account->getChildObject()->getData('ebay_shipping_discount_profiles');
            $temp['profiles'] = array();

            if (is_null($accountProfiles)) {
                $profiles[$accountId] = $temp;
                continue;
            }

            $accountProfiles = json_decode($accountProfiles, true);
            $marketplaceId = $this->getMarketplace()->getId();

            if (is_array($accountProfiles) && isset($accountProfiles[$marketplaceId]['profiles'])) {
                foreach ($accountProfiles[$marketplaceId]['profiles'] as $profile) {
                    $temp['profiles'][] = array(
                        'type' => Mage::helper('M2ePro')->escapeHtml($profile['type']),
                        'profile_id' => Mage::helper('M2ePro')->escapeHtml($profile['profile_id']),
                        'profile_name' => Mage::helper('M2ePro')->escapeHtml($profile['profile_name'])
                    );
                }
            }

            $profiles[$accountId] = $temp;
        }

        return $profiles;
    }

    //########################################

    public function isCustom()
    {
        if (isset($this->_data['is_custom'])) {
            return (bool)$this->_data['is_custom'];
        }

        return false;
    }

    public function getTitle()
    {
        if ($this->isCustom()) {
            return isset($this->_data['custom_title']) ? $this->_data['custom_title'] : '';
        }

        $template = Mage::helper('M2ePro/Data_Global')->getValue('ebay_template_shipping');

        if (is_null($template)) {
            return '';
        }

        return $template->getTitle();
    }

    public function getFormData()
    {
        if (!empty($this->formData)) {
            return $this->formData;
        }

        /** @var Ess_M2ePro_Model_Ebay_Template_Shipping $template */
        $template = Mage::helper('M2ePro/Data_Global')->getValue('ebay_template_shipping');

        if (is_null($template) || is_null($template->getId())) {
            return array();
        }

        $this->formData = $template->getData();
        $this->formData['services'] = $template->getServices();

        $calculated = $template->getCalculatedShipping();

        if (!is_null($calculated)) {
            $this->formData = array_merge($this->formData, $calculated->getData());
        }

        if (is_string($this->formData['excluded_locations'])) {
            $excludedLocations = json_decode($this->formData['excluded_locations'],true);
            $this->formData['excluded_locations'] = is_array($excludedLocations) ? $excludedLocations : array();
        } else {
            unset($this->formData['excluded_locations']);
        }

        return $this->formData;
    }

    public function getDefault()
    {
        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            $default = Mage::getModel('M2ePro/Ebay_Template_Shipping')->getDefaultSettingsSimpleMode();
        } else {
            $default = Mage::getModel('M2ePro/Ebay_Template_Shipping')->getDefaultSettingsAdvancedMode();
        }

        $default['excluded_locations'] = json_decode($default['excluded_locations'],true);

        // populate address fields with the data from magento configuration
        // ---------------------------------------
        $store = Mage::helper('M2ePro/Data_Global')->getValue('ebay_store');

        $city = $store->getConfig('shipping/origin/city');
        $regionId = $store->getConfig('shipping/origin/region_id');
        $countryId = $store->getConfig('shipping/origin/country_id');
        $postalCode = $store->getConfig('shipping/origin/postcode');

        $address = array(trim($city));

        if ($regionId) {
            $region = Mage::getModel('directory/region')->load($regionId);

            if ($region->getId()) {
                $address[] = trim($region->getName());
            }
        }

        $address = implode(', ', array_filter($address));

        if ($countryId) {
            $default['country_mode'] = Ess_M2ePro_Model_Ebay_Template_Shipping::ADDRESS_MODE_CUSTOM_VALUE;
            $default['country_custom_value'] = $countryId;
        }

        if ($postalCode) {
            $default['postal_code_mode'] = Ess_M2ePro_Model_Ebay_Template_Shipping::POSTAL_CODE_MODE_CUSTOM_VALUE;
            $default['postal_code_custom_value'] = $postalCode;
        }

        if ($address) {
            $default['address_mode'] = Ess_M2ePro_Model_Ebay_Template_Shipping::ADDRESS_MODE_CUSTOM_VALUE;
            $default['address_custom_value'] = $address;
        }

        // ---------------------------------------

        return $default;
    }

    public function getMarketplaceData()
    {
        $data = array(
            'id' => $this->getMarketplace()->getId(),
            'currency' => $this->getMarketplace()->getChildObject()->getCurrency(),
            'services' => $this->getMarketplace()->getChildObject()->getShippingInfo(),
            'packages' => $this->getMarketplace()->getChildObject()->getPackageInfo(),
            'dispatch' => $this->getSortedDispatchInfo(),
            'locations' => $this->getMarketplace()->getChildObject()->getShippingLocationInfo(),
            'locations_exclude' => $this->getSortedLocationExcludeInfo(),
            'origin_country' => $this->getMarketplace()->getChildObject()->getOriginCountry(),
        );

        $data['services'] = $this->modifyNonUniqueShippingServicesTitles($data['services']);

        $policyLocalization = $this->getData('policy_localization');

        if (!empty($policyLocalization)) {
            /** @var Ess_M2ePro_Model_Magento_Translate $translator */
            $translator = Mage::getModel('M2ePro/Magento_Translate');
            $translator->setLocale($policyLocalization);
            $translator->init();

            foreach ($data['services'] as $serviceKey => $service) {
                $data['services'][$serviceKey]['title'] = $translator->__($service['title']);
                foreach ($service['methods'] as $methodKey => $method) {
                    $data['services'][$serviceKey]['methods'][$methodKey]['title'] = $translator->__($method['title']);
                }
            }

            foreach ($data['locations'] as $key => $item) {
                $data['locations'][$key]['title'] =  $translator->__($item['title']);
            }

            foreach ($data['locations_exclude'] as $regionKey => $region) {
                foreach ($region as $locationKey => $location) {
                    $data['locations_exclude'][$regionKey][$locationKey] = $translator->__($location);
                }
            }
        }

        return $data;
    }

    // ---------------------------------------

    private function getSortedDispatchInfo()
    {
        $dispatchInfo = $this->getMarketplace()->getChildObject()->getDispatchInfo();

        $ebayIds = array();
        foreach ($dispatchInfo as $dispatchRecord) {
            $ebayIds[] = $dispatchRecord['ebay_id'];
        }
        array_multisort($ebayIds, SORT_ASC, $dispatchInfo);

        return $dispatchInfo;
    }

    private function getSortedLocationExcludeInfo()
    {
        $sortedInfo = array(
            'international' => array(),
            'domestic' => array(),
            'additional' => array()
        );

        foreach ($this->getMarketplace()->getChildObject()->getShippingLocationExcludeInfo() as $item) {

            $region = $item['region'];

            strpos(strtolower($item['region']), 'worldwide') !== false && $region = 'international';
            strpos(strtolower($item['region']), 'domestic') !== false && $region = 'domestic';
            strpos(strtolower($item['region']), 'additional') !== false && $region = 'additional';

            $sortedInfo[$region][$item['ebay_id']] = $item['title'];
        }

        foreach ($sortedInfo as $code => $info) {

            if ($code == 'domestic' || $code == 'international' || $code == 'additional') {
                continue;
            }

            $isInternational = array_key_exists($code, $sortedInfo['international']);
            $isDomestic = array_key_exists($code, $sortedInfo['domestic']);
            $isAdditional = array_key_exists($code, $sortedInfo['additional']);

            if (!$isInternational && !$isDomestic && !$isAdditional) {

                $foundedItem = array();
                foreach ($this->getMarketplace()->getChildObject()->getShippingLocationExcludeInfo() as $item) {
                    $item['ebay_id'] == $code && $foundedItem = $item;
                }

                if (empty($foundedItem)) {
                    continue;
                }

                unset($sortedInfo[$foundedItem['region']][$code]);
                $sortedInfo['international'][$code] = $foundedItem['title'];
            }
        }

        return $sortedInfo;
    }

    //########################################

    private function modifyNonUniqueShippingServicesTitles($services)
    {
        foreach ($services as &$category) {

            $nonUniqueTitles = array();
            foreach ($category['methods'] as $key => $method) {
                $nonUniqueTitles[$method['title']][] = $key;
            }

            foreach ($nonUniqueTitles as $methodsKeys) {
                if (count($methodsKeys) > 1) {

                    foreach ($methodsKeys as $key) {
                        $ebayId = $category['methods'][$key]['ebay_id'];
                        $title = $category['methods'][$key]['title'];

                        $uniqPart = preg_replace('/\w*'.str_replace(' ', '', $title).'/i', '', $ebayId);
                        $uniqPart = preg_replace('/([A-Z]+[a-z]*)/', '${1} ', $uniqPart);

                        $category['methods'][$key]['title'] = trim($title) . ' ' . $uniqPart;
                    }
                }
            }
        }

        return $services;
    }

    //########################################

    public function getAttributesJsHtml()
    {
        $html = '';

        $attributes = Mage::helper('M2ePro/Magento_Attribute')->filterByInputTypes(
            $this->attributes, array('text', 'price', 'select')
        );

        foreach ($attributes as $attribute) {
            $code = Mage::helper('M2ePro')->escapeHtml($attribute['code']);
            $html .= sprintf('<option value="%s">%s</option>', $code, $attribute['label']);
        }

        return Mage::helper('M2ePro')->escapeJs($html);
    }

    public function getMissingAttributes()
    {
        $formData = $this->getFormData();

        if (empty($formData)) {
            return array();
        }

        $attributes = array();

        // m2epro_ebay_template_shipping_service
        // ---------------------------------------
        $attributes['services'] = array();

        foreach ($formData['services'] as $i => $service) {
            $mode = 'cost_mode';
            $code = 'cost_value';

            if ($service[$mode] == Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_CUSTOM_ATTRIBUTE) {
                if (!$this->isExistInAttributesArray($service[$code])) {
                    $label = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($service[$code]);
                    $attributes['services'][$i][$code] = $label;
                }
            }

            $mode = 'cost_mode';
            $code = 'cost_additional_value';

            if ($service[$mode] == Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_CUSTOM_ATTRIBUTE) {
                if (!$this->isExistInAttributesArray($service[$code])) {
                    $label = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($service[$code]);
                    $attributes['services'][$i][$code] = $label;
                }
            }

            $mode = 'cost_mode';
            $code = 'cost_surcharge_value';

            if ($service[$mode] == Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_CUSTOM_ATTRIBUTE) {
                if (!$this->isExistInAttributesArray($service[$code])) {
                    $label = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($service[$code]);
                    $attributes['services'][$i][$code] = $label;
                }
            }
        }
        // ---------------------------------------

        // m2epro_ebay_template_shipping_calculated
        // ---------------------------------------
        if (!empty($formData['calculated'])) {
            $code = 'package_size_attribute';
            if (!$this->isExistInAttributesArray($formData['calculated'][$code])) {
                $label = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($formData['calculated'][$code]);
                $attributes['calculated'][$code] = $label;
            }

            $code = 'dimension_width_attribute';
            if (!$this->isExistInAttributesArray($formData['calculated'][$code])) {
                $label = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($formData['calculated'][$code]);
                $attributes['calculated'][$code] = $label;
            }

            $code = 'dimension_length_attribute';
            if (!$this->isExistInAttributesArray($formData['calculated'][$code])) {
                $label = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($formData['calculated'][$code]);
                $attributes['calculated'][$code] = $label;
            }

            $code = 'dimension_depth_attribute';
            if (!$this->isExistInAttributesArray($formData['calculated'][$code])) {
                $label = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($formData['calculated'][$code]);
                $attributes['calculated'][$code] = $label;
            }

            $code = 'weight_attribute';
            if (!$this->isExistInAttributesArray($formData['calculated'][$code])) {
                $label = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($formData['calculated'][$code]);
                $attributes['calculated'][$code] = $label;
            }
        }
        // ---------------------------------------

        return $attributes;
    }

    //########################################

    public function isExistInAttributesArray($code)
    {
        if (!$code) {
            return true;
        }

        return Mage::helper('M2ePro/Magento_Attribute')->isExistInAttributesArray($code, $this->attributes);
    }

    //########################################

    public function canDisplayLocalShippingRateTable()
    {
        return $this->getMarketplace()->getChildObject()->isLocalShippingRateTableEnabled();
    }

    public function canDisplayClickAndCollectOption()
    {
        return $this->getMarketplace()->getChildObject()->isClickAndCollectEnabled();
    }

    public function canDisplayFreightShippingType()
    {
        return $this->getMarketplace()->getChildObject()->isFreightShippingEnabled();
    }

    public function canDisplayCalculatedShippingType()
    {
        return $this->getMarketplace()->getChildObject()->isCalculatedShippingEnabled();
    }

    public function canDisplayLocalCalculatedShippingType()
    {
        if (!$this->canDisplayCalculatedShippingType()) {
            return false;
        }

        return true;
    }

    public function canDisplayInternationalCalculatedShippingType()
    {
        if (!$this->canDisplayCalculatedShippingType()) {
            return false;
        }

        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            return false;
        }

        return true;
    }

    public function canDisplayInternationalShippingRateTable()
    {
        return $this->getMarketplace()->getChildObject()->isInternationalShippingRateTableEnabled();
    }

    public function canDisplayCashOnDeliveryCost()
    {
        return $this->getMarketplace()->getChildObject()->isCashOnDeliveryEnabled();
    }

    public function canDisplayNorthAmericaCrossBorderTradeOption()
    {
        $marketplace = $this->getMarketplace();

        return $marketplace->getId() == 3   // UK
            || $marketplace->getId() == 17; // Ireland
    }

    public function canDisplayUnitedKingdomCrossBorderTradeOption()
    {
        $marketplace = $this->getMarketplace();

        return $marketplace->getId() == 1   // US
            || $marketplace->getId() == 2;  // Canada
    }

    public function canDisplayEnglishMeasurementSystemOption()
    {
        return $this->getMarketplace()->getChildObject()->isEnglishMeasurementSystemEnabled();
    }

    public function canDisplayMetricMeasurementSystemOption()
    {
        return $this->getMarketplace()->getChildObject()->isMetricMeasurementSystemEnabled();
    }

    public function canDisplayGlobalShippingProgram()
    {
        return $this->getMarketplace()->getChildObject()->isGlobalShippingProgramEnabled();
    }

    //########################################

    public function isLocalShippingModeCalculated()
    {
        $formData = $this->getFormData();

        if (!isset($formData['local_shipping_mode'])) {
            return false;
        }

        $mode = $formData['local_shipping_mode'];

        return $mode == Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_CALCULATED;
    }

    public function isInternationalShippingModeCalculated()
    {
        $formData = $this->getFormData();

        if (!isset($formData['international_shipping_mode'])) {
            return false;
        }

        $mode = $formData['international_shipping_mode'];

        return $mode == Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_CALCULATED;
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData(array(
                                'onclick' => 'EbayTemplateShippingHandlerObj.addRow(\'local\');',
                                'class' => 'add add_local_shipping_method_button'
                            ));
        $this->setChild('add_local_shipping_method_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData(array(
                                'onclick' => 'EbayTemplateShippingHandlerObj.addRow(\'international\');',
                                'class' => 'add add_international_shipping_method_button'
                            ));
        $this->setChild('add_international_shipping_method_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData(array(
                                'label'   => Mage::helper('M2ePro')->__('Remove'),
                                'onclick' => 'EbayTemplateShippingHandlerObj.removeRow.call(this, \'%type%\');',
                                'class' => 'delete icon-btn remove_shipping_method_button'
                            ));
        $this->setChild('remove_shipping_method_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'id'      => 'save_popup_button',
            'label'   => Mage::helper('M2ePro')->__('Save'),
            'onclick' => 'EbayTemplateShippingHandlerObj.saveExcludeLocationsList()',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('save_popup_button',$buttonBlock);
        // ---------------------------------------
    }

    //########################################
}