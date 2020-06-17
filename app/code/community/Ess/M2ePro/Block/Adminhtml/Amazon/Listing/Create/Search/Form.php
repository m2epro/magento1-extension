<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Listing as AmazonListing;

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Create_Search_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_sessionKey = 'amazon_listing_create';
    protected $_useFormContainer = true;

    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setId('amazonListingCreateSearchForm');
    }

    protected function _prepareForm()
    {
        $form = new Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form(
            array(
                'id'      => 'edit_form',
                'method'  => 'post',
                'action'  => 'javascript:void(0)',
                'enctype' => 'multipart/form-data',
            )
        );
        $form->addCustomAttribute('allowed_attribute_types');

        /** @var Ess_M2ePro_Helper_Magento_Attribute $magentoAttributeHelper */
        $magentoAttributeHelper = Mage::helper('M2ePro/Magento_Attribute');

        $attributesByTypes = array(
            'text' => $magentoAttributeHelper->filterByInputTypes($this->getData('all_attributes'), array('text'))
        );
        $formData = $this->getListingData();

        // Identifiers Settings
        $fieldset = $form->addFieldset(
            'identifiers_settings_fieldset',
            array(
                'legend'      => Mage::helper('M2ePro')->__('Identifiers Settings'),
                'collapsable' => false
            )
        );

        $fieldset->addField(
            'general_id_custom_attribute',
            'hidden',
            array(
                'name'  => 'general_id_custom_attribute',
                'value' => $formData['general_id_custom_attribute']
            )
        );

        $preparedAttributes = array();

        if ($formData['general_id_mode'] == AmazonListing::GENERAL_ID_MODE_CUSTOM_ATTRIBUTE &&
            !$magentoAttributeHelper->isExistInAttributesArray(
                $formData['general_id_custom_attribute'],
                $attributesByTypes['text']
            ) && $formData['general_id_custom_attribute'] != '') {
            $attrs = array(
                'attribute_code' => $formData['general_id_custom_attribute'],
                'selected'       => 'selected'
            );

            $preparedAttributes[] = array(
                'attrs' => $attrs,
                'value' => AmazonListing::GENERAL_ID_MODE_CUSTOM_ATTRIBUTE,
                'label' => $magentoAttributeHelper->getAttributeLabel($formData['general_id_custom_attribute']),
            );
        }

        foreach ($attributesByTypes['text'] as $attribute) {
            $attrs = array('attribute_code' => $attribute['code']);
            if ($formData['general_id_mode'] == AmazonListing::GENERAL_ID_MODE_CUSTOM_ATTRIBUTE
                && $attribute['code'] == $formData['general_id_custom_attribute']) {
                $attrs['selected'] = 'selected';
            }

            $preparedAttributes[] = array(
                'attrs' => $attrs,
                'value' => AmazonListing::GENERAL_ID_MODE_CUSTOM_ATTRIBUTE,
                'label' => $attribute['label'],
            );
        }

        $fieldset->addField(
            'general_id_mode',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::SELECT,
            array(
                'name'   => 'general_id_mode',
                'label'  => Mage::helper('M2ePro')->__('ASIN / ISBN'),
                'values' => array(
                    AmazonListing::GENERAL_ID_MODE_NOT_SET => Mage::helper('M2ePro')->__('Not Set'),
                    array(
                        'label' => Mage::helper('M2ePro')->__('Magento Attributes'),
                        'value' => $preparedAttributes,
                        'attrs' => array('is_magento_attribute' => true)
                    )
                ),
                'value' => $formData['general_id_mode'] != AmazonListing::GENERAL_ID_MODE_CUSTOM_ATTRIBUTE
                    ? $formData['general_id_mode'] : '',
                'create_magento_attribute' => true,
                'after_element_html' => $this->getTooltipHtml(
                    Mage::helper('M2ePro')->__(
                        'This setting is a source for ASIN/ISBN value which will be used
                    at the time of Automatic Search of Amazon Products.'
                    )
                ),
                'allowed_attribute_types'  => 'text'
            )
        );

        $fieldset->addField(
            'worldwide_id_custom_attribute',
            'hidden',
            array(
                'name'  => 'worldwide_id_custom_attribute',
                'value' => $formData['worldwide_id_custom_attribute']
            )
        );

        $preparedAttributes = array();

        if ($formData['worldwide_id_mode'] == AmazonListing::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE &&
            !$magentoAttributeHelper->isExistInAttributesArray(
                $formData['worldwide_id_custom_attribute'],
                $attributesByTypes['text']
            ) && $formData['worldwide_id_custom_attribute'] != '') {
            $attrs = array(
                'attribute_code' => $formData['worldwide_id_custom_attribute'],
                'selected'       => 'selected'
            );

            $preparedAttributes[] = array(
                'attrs' => $attrs,
                'value' => AmazonListing::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE,
                'label' => $magentoAttributeHelper->getAttributeLabel($formData['worldwide_id_custom_attribute']),
            );
        }

        foreach ($attributesByTypes['text'] as $attribute) {
            $attrs = array('attribute_code' => $attribute['code']);
            if ($formData['worldwide_id_mode'] == AmazonListing::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE
                && $attribute['code'] == $formData['worldwide_id_custom_attribute']) {
                $attrs['selected'] = 'selected';
            }

            $preparedAttributes[] = array(
                'attrs' => $attrs,
                'value' => AmazonListing::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE,
                'label' => $attribute['label'],
            );
        }

        $fieldset->addField(
            'worldwide_id_mode',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::SELECT,
            array(
                'name'   => 'worldwide_id_mode',
                'label'  => Mage::helper('M2ePro')->__('UPC / EAN'),
                'values' => array(
                    AmazonListing::WORLDWIDE_ID_MODE_NOT_SET => Mage::helper('M2ePro')->__('Not Set'),
                    array(
                        'label' => Mage::helper('M2ePro')->__('Magento Attributes'),
                        'value' => $preparedAttributes,
                        'attrs' => array('is_magento_attribute' => true)
                    )
                ),
                'value' => $formData['worldwide_id_mode'] != AmazonListing::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE
                    ? $formData['worldwide_id_mode'] : '',
                'create_magento_attribute' => true,
                'after_element_html' => $this->getTooltipHtml(
                    Mage::helper('M2ePro')->__(
                        'This setting is a source for UPC/EAN value which will be used
                    at the time of Automatic Search of Amazon Products.'
                    )
                ),
                'allowed_attribute_types'  => 'text'
            )
        );

        // Additional Settings
        $fieldset = $form->addFieldset(
            'additional_settings_fieldset',
            array(
                'legend'      => Mage::helper('M2ePro')->__('Additional Settings'),
                'collapsable' => false
            )
        );

        $fieldset->addField(
            'search_by_magento_title_mode',
            'select',
            array(
                'name'   => 'search_by_magento_title_mode',
                'label'  => Mage::helper('M2ePro')->__('Search by Product Name'),
                'values' => array(
                    AmazonListing::SEARCH_BY_MAGENTO_TITLE_MODE_NONE => Mage::helper('M2ePro')->__('Disable'),
                    AmazonListing::SEARCH_BY_MAGENTO_TITLE_MODE_YES  => Mage::helper('M2ePro')->__('Enable')
                ),
                'value'   => $formData['search_by_magento_title_mode'],
                'tooltip' => Mage::helper('M2ePro')->__(
                    '<p>Enable this additional Setting if you want M2E Pro to perform the search for Amazon
                    Products based on Magento Product Name.</p><br>
                    <p><strong>Please note</strong> that this setting is not applied to search for the available
                    Amazon Products during the List action.</p>'
                )
            )
        );

        $form->setUseContainer($this->_useFormContainer);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    //########################################

    protected function _beforeToHtml()
    {
        $data = $this->getListingData();

        $this->setData(
            'all_attributes',
            Mage::helper('M2ePro/Magento_Attribute')->getAll()
        );

        foreach ($data as $key => $value) {
            $this->setData($key, $value);
        }

        return parent::_beforeToHtml();
    }

    //########################################

    protected function _toHtml()
    {

        $helpBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_helpBlock', '', array(
                'content' => Mage::helper('M2ePro')->__(
                    'In this Section you can specify the sources from which the values for ASIN/ISBN and
                UPC/EAN will be taken in case you have those for your Items. <br/><br/>
                These Settings will be used in two cases:

                <ul class="list">
                    <li>at the time of using Automatic ASIN/ISBN Search;</li>
                    <li>at the time of using “List” Action.</li>
                </ul>

                Using these Settings means the Search of existing Amazon Products and the process of
                linking Magento Product with found Amazon Product. <br/><br/>

                During the process of Search, Settings values are used according to the following logic:

                <ul class="list">
                    <li>the Product is searched by ASIN/ISBN parameter. (if specified);</li>
                    <li>if no result by ASIN/ISBN parameter, then UPC/EAN search is performed. (if specified);</li>
                    <li>if no result by UPC/EAN parameter, then additional search by Magento Product Name is performed.
                    (if enabled).</li>
                </ul>
                <br/>
                More detailed information you can find
                <a href="%url%" target="_blank" class="external-link">here</a>.',
                    Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(null, null, 'x/FYgVAQ')
                ),
                'title' => Mage::helper('M2ePro')->__('Search Settings')
            )
        );

        /** @var Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Create_Breadcrumb $breadcrumb */
        $breadcrumb = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_create_breadcrumb');
        $breadcrumb->setSelectedStep((int)$this->getRequest()->getParam('step', 1));

        $javascript = <<<HTML
<script type="text/javascript">

    AmazonListingCreateSearchObj = new AmazonListingCreateSearch();

    $('general_id_mode').observe('change', AmazonListingCreateSearchObj.general_id_mode_change);
    $('worldwide_id_mode').observe('change', AmazonListingCreateSearchObj.worldwide_id_mode_change);

</script>
HTML;

        return $breadcrumb->_toHtml()
            . $helpBlock->_toHtml()
            . parent::_toHtml()
            . $javascript;
    }

    //########################################

    public function getDefaultFieldsValues()
    {
        return array(
            'general_id_mode' => AmazonListing::GENERAL_ID_MODE_NOT_SET,
            'general_id_custom_attribute' => '',

            'worldwide_id_mode' => AmazonListing::WORLDWIDE_ID_MODE_NOT_SET,
            'worldwide_id_custom_attribute' => '',

            'search_by_magento_title_mode' => AmazonListing::SEARCH_BY_MAGENTO_TITLE_MODE_NONE
        );
    }

    //########################################

    protected function getListingData()
    {
        if ($this->getRequest()->getParam('id') !== null) {
            $data = $this->getListing()->getData();
        } else {
            $data = Mage::helper('M2ePro/Data_Session')->getValue($this->_sessionKey);
            $data = array_merge($this->getDefaultFieldsValues(), $data);
        }

        return $data;
    }

    protected function getListing()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if ($this->_listing === null) {
            $this->_listing = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
                'Listing', $listingId
            );
        }

        return $this->_listing;
    }

    protected function getTooltipHtml($content)
    {
        $toolTipIconSrc = $this->getSkinUrl('M2ePro/images/tool-tip-icon.png');
        $helpIconSrc = $this->getSkinUrl('M2ePro/images/help.png');

        return <<<HTML
<span>
    <img class="tool-tip-image" style="vertical-align: middle;" src="{$toolTipIconSrc}" />
    <span class="tool-tip-message" style="display:none; text-align: left; width: 120px; background: #E3E3E3;">
        <img src="{$helpIconSrc}" />
        <span style="color:gray;">
           {$content}
        </span>
    </span>
</span>
HTML;
    }

    //########################################

    /**
     * @param boolean $useFormContainer
     */
    public function setUseFormContainer($useFormContainer)
    {
        $this->_useFormContainer = $useFormContainer;
    }

    //########################################
}
