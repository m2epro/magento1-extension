<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        /** @var Ess_M2ePro_Model_Listing $listing */
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing',
            $this->getRequest()->getParam('listing_id')
        );

        $this->setId('ebayListingProduct');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing_product_source';
        $this->_controller .= ucfirst($listing->getSetting('additional_data', 'source'));

        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $this->_headerText = Mage::helper('M2ePro')->__(
                "%component_name% / Select Products",
                Mage::helper('M2ePro/Component_Ebay')->getTitle()
            );
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__("Select Products");
        }

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $url = $this->getUrl('*/*/index', array('_current' => true, 'clear' => true));
        if ($backParam = $this->getRequest()->getParam('back')) {
            $url = Mage::helper('M2ePro')->getBackUrl();
        }

        $this->_addButton(
            'back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'class'     => 'back',
                'onclick'   => 'setLocation(\''.$url.'\')'
            )
        );

        $this->_addButton(
            'auto_action', array(
                'label'     => Mage::helper('M2ePro')->__('Auto Add/Remove Rules'),
                'onclick'   => 'ListingAutoActionObj.loadAutoActionHtml();'
            )
        );

        if ($this->getRequest()->getParam('listing_creation')) {
            $url = $this->getUrl(
                '*/adminhtml_ebay_listing_categorySettings/exitToListing',
                array('listing_id' => $this->getRequest()->getParam('listing_id'))
            );
            $confirm =
                $this->__('Are you sure?') . '\n\n'
                . $this->__('All unsaved changes will be lost and you will be returned to the Listings grid.');
            $this->_addButton(
                'exit_to_listing',
                array(
                    'id' => 'exit_to_listing',
                    'label' => Mage::helper('M2ePro')->__('Cancel'),
                    'onclick' => "confirmSetLocation('$confirm', '$url');",
                    'class' => 'scalable'
                )
            );
        }

        $this->_addButton(
            'continue', array(
                'id'        => 'continue',
                'label'     => Mage::helper('M2ePro')->__('Continue'),
                'class'     => 'scalable next',
                'onclick'   => 'ListingProductAddObj.continue();'
            )
        );
    }

    //########################################

    public function getGridHtml()
    {
        /** @var Ess_M2ePro_Model_Listing $listing */
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing',
            $this->getRequest()->getParam('listing_id')
        );

        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header',
            '',
            array('listing' => $listing)
        );

        return $viewHeaderBlock->toHtml() . parent::getGridHtml();
    }

    protected function _toHtml()
    {
        return '<div id="add_products_progress_bar"></div>' .
               '<div id="add_products_container">' .
               parent::_toHtml() .
               '</div>' .
               $this->getVideoTutorialHtml() .
               $this->getAutoactionPopupHtml();
    }

    //########################################

    protected function getVideoTutorialHtml()
    {
        $videoId = 'iBEiQ8Ilya8';
        return <<<HTML
<div id="video_tutorial_pop_up" style="display: none;">
    <div class="player_container" style="margin: 20px 5px; ">
    <object width="853" height="480">
        <param name="movie" value="http://www.youtube.com/v/{$videoId}?version=3&amp;hl=ru_RU&amp;rel=0&amp;vq=hd720"/>
        <param name="allowFullScreen" value="true"/>
        <param name="allowscriptaccess" value="always"/>
        <embed src="http://www.youtube.com/v/{$videoId}?version=3&amp;hl=ru_RU&amp;rel=0&amp;vq=hd720"
               type="application/x-shockwave-flash" width="853" height="480"
               allowscriptaccess="always" allowfullscreen="true">
        </embed>
    </object>
    </div>
</div>
HTML;
    }

    protected function getAutoactionPopupHtml()
    {
        $helper = Mage::helper('M2ePro');

        $onclick = <<<JS
ListingProductAddObj.autoactionPopup.close();
ListingAutoActionObj.loadAutoActionHtml();
JS;
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Start Configure'),
            'onclick' => $onclick
        );
        $startConfigureButton = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);

        return <<<HTML
<div id="autoaction_popup_content" style="display: none">
    <div style="margin: 10px; height: 153px">
        {$helper->__(
'<b>
 Do you want to set up a Rule by which Products will be automatically Added or Deleted from the current M2E Pro Listing?
</b>
<br/><br/>
Click Start Configure to create a Rule<br/> or Cancel if you do not want to do it now.
<br/><br/>
<b>Note:</b> You can always return to it by clicking Auto Add/Remove Rules Button on this Page.'
        )}
    </div>

    <div style="text-align: right">
        <a href="javascript:"
            onclick="ListingProductAddObj.cancelAutoActionPopup();">{$helper->__('Cancel')}</a>
        &nbsp;&nbsp;&nbsp;&nbsp;
        {$startConfigureButton->toHtml()}
    </div>
</div>
HTML;
    }

    //########################################
}
