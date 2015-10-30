<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Listing_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('buyListingEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_buy_listing';
        $this->_mode = 'edit';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        if (!Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
            $headerText = Mage::helper('M2ePro')->__(
                'Edit %component_name% Listing Settings "%listing_title%"',
                Mage::helper('M2ePro/Component_Buy')->getTitle(),
                $this->escapeHtml($listingData['title'])
            );
        } else {
            $headerText =Mage::helper('M2ePro')->__(
                'Edit Listing Settings "%listing_title%"',
                $this->escapeHtml($listingData['title'])
            );
        }

        $this->_headerText = $headerText;
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        if (!is_null($this->getRequest()->getParam('back'))) {
            // ---------------------------------------
            $url = Mage::helper('M2ePro')->getBackUrl(
                '*/adminhtml_common_listing/index',
                array(
                    'tab' => Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::TAB_ID_BUY
                )
            );
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonListingSettingsHandlerObj.back_click(\''.$url.'\')',
                'class'     => 'back'
            ));
            // ---------------------------------------
        }

        // ---------------------------------------
        $this->_addButton('auto_action', array(
            'label'     => Mage::helper('M2ePro')->__('Auto Add/Remove Rules'),
            'onclick'   => 'ListingAutoActionHandlerObj.loadAutoActionHtml();'
        ));
        // ---------------------------------------

        $backUrl = Mage::helper('M2ePro')->getBackUrlParam('list');

        // ---------------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_buy_listing/save',
            array(
                'id'    => $listingData['id'],
                'back'  => Mage::helper('M2ePro')->getBackUrlParam('list')
            )
        );
        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'CommonListingSettingsHandlerObj.save_click(\'' . $url . '\')',
            'class'     => 'save'
        ));
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton('save_and_continue', array(
            'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
            'onclick'   => 'CommonListingSettingsHandlerObj.save_and_edit_click(\''.$url.'\', 1)',
            'class'     => 'save'
        ));
        // ---------------------------------------
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------
        $tabs = $this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_listing_edit_tabs');
        $this->setChild('tabs', $tabs);
        // ---------------------------------------

        return $this;
    }

    //########################################

    public function getFormHtml()
    {
        $listing = Mage::helper('M2ePro/Component_Buy')->getCachedObject(
            'Listing', $this->getRequest()->getParam('id')
        );

        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header','',
            array('listing' => $listing)
        );

        $tabs = $this->getChild('tabs');

        $urls = Mage::helper('M2ePro')->getControllerActions(
            'adminhtml_common_listing_autoAction',
            array(
                'listing_id' => $this->getRequest()->getParam('id'),
                'component' => Ess_M2ePro_Helper_Component_Buy::NICK
            )
        );
        $urls = json_encode($urls);

        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');

        $translations = json_encode(array(
            'Auto Add/Remove Rules' => $helper->__('Auto Add/Remove Rules'),
            'Based on Magento Categories' => $helper->__('Based on Magento Categories'),
            'You must select at least 1 Category.' => $helper->__('You must select at least 1 Category.'),
            'Rule with the same Title already exists.' => $helper->__('Rule with the same Title already exists.')
        ));

        $js = <<<HTML
<script type="text/javascript">

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});

    ListingAutoActionHandlerObj = new ListingAutoActionHandler();

</script>
HTML;

        return $viewHeaderBlock->toHtml() . $tabs->toHtml() . parent::getFormHtml() . $js;
    }

    //########################################
}