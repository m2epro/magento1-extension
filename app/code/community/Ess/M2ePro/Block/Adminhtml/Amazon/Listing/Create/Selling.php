<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Create_Selling
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonListingCreateSelling');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_listing_create';
        $this->_mode = 'selling';

        $this->_headerText = Mage::helper('M2ePro')->__("Creating A New Amazon M2E Pro Listing");

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        $url = $this->getUrl(
            '*/adminhtml_amazon_listing_create/index', array(
                '_current' => true,
                'step'     => '1'
            )
        );
        $this->_addButton(
            'back', array(
                'label'   => Mage::helper('M2ePro')->__('Previous Step'),
                'onclick' => 'AmazonListingSettingsObj.back_click(\'' . $url . '\')',
                'class'   => 'back'
            )
        );

        $url = $this->getUrl(
            '*/adminhtml_amazon_listing_create/index', array(
                '_current' => true
            )
        );
        $this->_addButton(
            'save_and_next', array(
                'label'   => Mage::helper('M2ePro')->__('Next Step'),
                'onclick' => 'AmazonListingSettingsObj.save_click(\'' . $url . '\')',
                'class'   => 'next'
            )
        );
    }

    //########################################

    protected function _prepareLayout()
    {
        Mage::helper('M2ePro/View')->getJsPhpRenderer()->addConstants(
            Mage::helper('M2ePro')->getClassConstants('Ess_M2ePro_Helper_Component_Amazon'),
            'Ess_M2ePro_Helper_Component'
        );

        Mage::helper('M2ePro/View')->getJsPhpRenderer()->addClassConstants('Ess_M2ePro_Model_Listing');
        Mage::helper('M2ePro/View')->getJsPhpRenderer()->addClassConstants('Ess_M2ePro_Model_Amazon_Listing');

        Mage::helper('M2ePro/View')->getJsTranslatorRenderer()->addTranslations(
            array(
                'condition_note_length_error' => Mage::helper('M2ePro')->__(
                    'Must be not more than 2000 characters long.'
                ),
                'sku_modification_custom_value_error' => Mage::helper('M2ePro')->__(
                    '%value% placeholder should be specified'
                ),
                'sku_modification_custom_value_max_length_error' => Mage::helper('M2ePro')->__(
                    'The SKU length must be less than %value%.',
                    Ess_M2ePro_Helper_Component_Amazon::SKU_MAX_LENGTH
                )
            )
        );

        return parent::_prepareLayout();
    }

    //########################################
}
