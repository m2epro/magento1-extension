<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Create_General
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayListingCreateGeneral');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing_create';
        $this->_mode = 'general';

        $this->_headerText = Mage::helper('M2ePro')->__("Creating A New Ebay M2E Pro Listing");

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton(
            'next', array(
                'id'    => 'next',
                'label' => Mage::helper('M2ePro')->__('Next Step'),
                'class' => 'scalable next next_step_button'
            )
        );
    }

    //########################################

    protected function _prepareLayout()
    {
        Mage::helper('M2ePro/View')->getJsPhpRenderer()->addClassConstants('Ess_M2ePro_Helper_Component_Ebay');

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addControllerActions('adminhtml_ebay_account');
        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addControllerActions('adminhtml_ebay_marketplace');

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addUrls(
            Mage::helper('M2ePro')->getControllerActions(
                'adminhtml_general', array('component' => Ess_M2ePro_Helper_Component_Ebay::NICK)
            )
        );

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addUrls(
            Mage::helper('M2ePro')->getControllerActions(
                'adminhtml_ebay_listing_create', array('_current' => true)
            )
        );

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->add(
            $this->getUrl(
                '*/adminhtml_ebay_account/new', array(
                    'close_on_save' => true,
                    'wizard'        => $this->getRequest()->getParam('wizard')
                )
            ),
            'adminhtml_ebay_account/new'
        );

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->add(
            $this->getUrl(
                '*/adminhtml_ebay_log/synchronization', array(
                    'wizard' => $this->getRequest()->getParam('wizard')
                )
            ),
            'logViewUrl'
        );

        Mage::helper('M2ePro/View')->getJsTranslatorRenderer()->addTranslations(
            array(
                'The specified Title is already used for other Listing. Listing Title must be unique.' =>
                    Mage::helper('M2ePro')->__(
                        'The specified Title is already used for other Listing. Listing Title must be unique.'
                    ),
                'Account not found, please create it.' =>
                    Mage::helper('M2ePro')->__('Account not found, please create it.'),
                'Add Another' => Mage::helper('M2ePro')->__('Add Another'),
                'Please wait while Synchronization is finished.' =>
                    Mage::helper('M2ePro')->__('Please wait while Synchronization is finished.')
            )
        );

        return parent::_prepareLayout();
    }

    protected function _toHtml()
    {
        return
            '<div id="progress_bar"></div>' .
            '<div id="content_container">' .
            parent::_toHtml() .
            '</div>';
    }

    //########################################
}
