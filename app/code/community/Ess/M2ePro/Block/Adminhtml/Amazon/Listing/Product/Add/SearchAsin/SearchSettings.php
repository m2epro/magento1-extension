<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Product_Add_SearchAsin_SearchSettings
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_component;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('searchSettings');
        $this->setTemplate('M2ePro/amazon/listing/product/add/search_asin/search_settings.phtml');
    }

    protected function _prepareForm()
    {
        $action = $this->getUrl(
            '*/adminhtml_amazon_listing_productAdd/saveSearchSettings', array(
                'id' => (int)$this->getRequest()->getParam('id')
            )
        );

        $form = new Varien_Data_Form(
            array(
                'id'     => 'search_settings_form',
                'action' => $action,
                'method' => 'post'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        $helpBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_helpBlock', '', array(
                'content' => Mage::helper('M2ePro')->__(<<<HTML
<p>In this section, you can specify the settings according to which M2E Pro will perform <a href="%url1%" 
target="_blank" class="external-link">automatic search of ASIN/ISBN</a> for your products. Also, these settings are 
used when you list products to Amazon via the Module.</p>
<p>Click <a href="%url2%" target="_blank" class="external-link">here</a> for more detailed information.</p>
HTML
                    ,
                    Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(null, null, 'asin-isbn-management#271e3b536b4045cf847a54ec26564ff3'),
                    Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(null, null, 'step-3-specify-search-settings')
                ),
                'title'   => Mage::helper('M2ePro')->__('Search Settings')
            )
        );
        $this->setChild('help_block', $helpBlock);

        $child = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_create_search_form')
            ->setUseFormContainer(false);
        $this->setChild('content', $child);

        Mage::helper('M2ePro/View')->getJsPhpRenderer()->addClassConstants('Ess_M2ePro_Model_Amazon_Listing');

        Mage::helper('M2ePro/View')->getJsRenderer()->add(<<<JS

    AmazonListingCreateSearchObj = new AmazonListingCreateSearch();

    $('general_id_mode').observe('change', AmazonListingCreateSearchObj.general_id_mode_change);
    $('worldwide_id_mode').observe('change', AmazonListingCreateSearchObj.worldwide_id_mode_change);
JS
        );

        return parent::_prepareLayout();
    }

    //########################################

    protected function _toHtml()
    {
        $buttonSave = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(
            array(
                'id'      => 'save_search_settings',
                'label'   => Mage::helper('M2ePro')->__('Confirm'),
                'style'   => 'float: right; margin: 0 0 7px 5px;',
                'onclick' => 'ListingGridObj.saveSearchSettings()'
            )
        );

        $this->setChild('save_search_settings', $buttonSave);

        return parent::_toHtml();
    }

    //########################################
}
