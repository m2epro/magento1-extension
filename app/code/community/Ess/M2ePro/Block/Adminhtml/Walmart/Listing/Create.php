<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Create extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('walmartListingCreate');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_walmart_listing';
        $this->_mode = 'create';

        $this->_headerText = Mage::helper('M2ePro')->__("Creating A New Walmart M2E Pro Listing");

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton(
            'save_and_next', array(
                'id'      => 'save_and_next',
                'label'   => Mage::helper('M2ePro')->__('Next Step'),
                'onclick' => 'WalmartListingCreateGeneralObj.save_and_next()',
                'class'   => 'next'
            )
        );
        // ---------------------------------------
    }

    //########################################

    protected function _prepareLayout()
    {
        Mage::helper('M2ePro/View')->getJsPhpRenderer()->addConstants(
            Mage::helper('M2ePro')->getClassConstants('Ess_M2ePro_Helper_Component_Walmart'),
            'Ess_M2ePro_Helper_Component'
        );

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addControllerActions('adminhtml_walmart_account');

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addControllerActions('adminhtml_walmart_marketplace');

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addUrls(
            Mage::helper('M2ePro')->getControllerActions(
                'adminhtml_general', array('component' => Ess_M2ePro_Helper_Component_Walmart::NICK)
            )
        );

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addUrls(
            Mage::helper('M2ePro')->getControllerActions(
                'adminhtml_walmart_listing_create', array('_current' => true)
            )
        );

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->add(
            $this->getUrl(
                '*/adminhtml_walmart_log/synchronization', array(
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
        return '<div id="progress_bar"></div>'
            . '<div id="content_container">'
            . parent::_toHtml()
            . '</div>';
    }

    //########################################
}
