<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing = null;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonListingEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_listing';
        $this->_mode = 'edit';

        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Amazon')->getTitle();

            $this->_headerText = Mage::helper('M2ePro')->__(
                'Edit %component_name% Listing Settings "%listing_title%"',
                $componentName,
                $this->escapeHtml($this->getListing()->getTitle())
            );
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__(
                'Edit Listing Settings "%listing_title%"',
                $this->escapeHtml($this->getListing()->getTitle())
            );
        }

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if ($this->getRequest()->getParam('back') !== null) {
            $url = Mage::helper('M2ePro')->getBackUrl(
                '*/adminhtml_amazon_listing/index'
            );
            $this->_addButton(
                'back',
                array(
                    'label'   => Mage::helper('M2ePro')->__('Back'),
                    'onclick' => 'AmazonListingSettingsObj.back_click(\'' . $url . '\')',
                    'class'   => 'back'
                )
            );
        }

        $this->_addButton(
            'auto_action',
            array(
                'label'   => Mage::helper('M2ePro')->__('Auto Add/Remove Rules'),
                'onclick' => 'ListingAutoActionObj.loadAutoActionHtml();'
            )
        );

        $backUrl = Mage::helper('M2ePro')->getBackUrlParam('list');

        $url = $this->getUrl(
            '*/adminhtml_amazon_listing/save',
            array(
                'id'   => $this->getListing()->getId(),
                'back' => $backUrl
            )
        );
        $this->_addButton(
            'save',
            array(
                'label'   => Mage::helper('M2ePro')->__('Save'),
                'onclick' => 'AmazonListingSettingsObj.save_click(\'' . $url . '\')',
                'class'   => 'save'
            )
        );

        $this->_addButton(
            'save_and_continue',
            array(
                'label'   => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                'onclick' => 'AmazonListingSettingsObj.save_and_edit_click(\'' . $url . '\', 1)',
                'class'   => 'save'
            )
        );
    }

    //########################################

    protected function _prepareLayout()
    {
        $tabs = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_edit_tabs');
        $this->setChild('tabs', $tabs);

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addUrls(
            Mage::helper('M2ePro')->getControllerActions(
                'adminhtml_amazon_listing_autoAction',
                array(
                    'listing_id' => $this->getListing()->getId(),
                    'component'  => Ess_M2ePro_Helper_Component_Amazon::NICK
                )
            )
        );

        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');

        Mage::helper('M2ePro/View')->getJsTranslatorRenderer()->addTranslations(
            array(
                'Auto Add/Remove Rules'                    => $helper->__('Auto Add/Remove Rules'),
                'Based on Magento Categories'              => $helper->__('Based on Magento Categories'),
                'You must select at least 1 Category.'     => $helper->__('You must select at least 1 Category.'),
                'Rule with the same Title already exists.' => $helper->__('Rule with the same Title already exists.')
            )
        );

        Mage::helper('M2ePro/View')->getJsRenderer()->addOnReadyJs(
            <<<JS
    ListingAutoActionObj = new AmazonListingAutoAction();
JS
        );

        return parent::_prepareLayout();
    }

    //########################################

    public function getFormHtml()
    {
        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header',
            '',
            array('listing' => $this->getListing())
        );

        $tabs = $this->getChild('tabs');

        return $viewHeaderBlock->toHtml() . $tabs->toHtml() . parent::getFormHtml();
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
}
