<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode_CategoryAbstract
    extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setId('listingAutoActionModeCategory');
        $this->setTemplate('M2ePro/listing/auto_action/mode/category.phtml');
    }

    //########################################

    /** @return Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode_Category_Group_GridAbstract */
    abstract protected function prepareGroupsGrid();

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $groupGrid = $this->prepareGroupsGrid();

        $data = array(
            'id'      => 'confirm_button',
            'class'   => 'confirm_button',
            'label'   => Mage::helper('M2ePro')->__('Save'),
            'onclick' => 'ListingAutoActionObj.confirm();',
            'style'   => 'display: none;'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('confirm_button', $buttonBlock);

        $data = array(
            'id'      => 'close_button',
            'class'   => 'close_button',
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'onclick' => 'Windows.getFocusedWindow().close();'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('close_button', $buttonBlock);

        $data = array(
            'id'      => 'continue_button',
            'class'   => 'continue_button next',
            'label'   => Mage::helper('M2ePro')->__('Continue'),
            'style'   => 'display: none;',
            'onclick' => ''
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('continue_button', $buttonBlock);

        $data = array(
            'id'      => 'add_button',
            'class'   => 'add_button add',
            'label'   => Mage::helper('M2ePro')->__('Add New Rule'),
            'onclick' => 'ListingAutoActionObj.categoryStepOne();',
            'style'   => $groupGrid->getCollection()->getSize() == 0 ? 'display: none;' : ''
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('add_button', $buttonBlock);

        $data = array(
            'id'      => 'add_first_button',
            'class'   => 'add_first_button add',
            'label'   => Mage::helper('M2ePro')->__('Add New Rule'),
            'onclick' => 'ListingAutoActionObj.categoryStepOne();'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('add_first_button', $buttonBlock);
    }

    //########################################

    /**
     * @param string $link
     */
    protected function createHelpBlock($link)
    {
        $helpBlock = Mage::app()->getLayout()->createBlock('M2ePro/adminhtml_helpBlock')->setData(
            array(
                'id' => 'block_notice_listing_auto_action_mode',
                'title' => Mage::helper('M2ePro')->__('Auto Add/Remove Rules'),
                'content' => Mage::helper('M2ePro')->__(
                    'These Rules of automatic product adding and removal come into action when a Magento Product is 
                    added to the Magento Category with regard to the Store View selected for the M2E Pro Listing. 
                    In other words, after a Magento Product is added to the selected Magento Category, it can be 
                    automatically added to M2E Pro Listing if the settings are enabled.<br><br>
                    Please note if a product is already presented in another M2E Pro Listing with the related Channel 
                    account and marketplace, the Item won’t be added to the Listing to prevent listing duplicates on 
                    the Channel.<br><br>
                    Accordingly, if a Magento Product presented in the M2E Pro Listing is removed from the Magento 
                    Category, the Item will be removed from the Listing and its sale will be stopped on Channel.<br><br>
                    You should combine Magento Categories into groups to apply the Auto Add/Remove Rules. You can 
                    create as many groups as you need, but one Magento Category can be used only in one Rule.<br><br>
                    More detailed information you can find <a href="%url%" target="_blank">here</a>.',
                    $link
                )
            )
        );
        $this->setChild('help_block', $helpBlock);
    }

    //########################################

    /**
     * @return string
     */
    protected function getLink($path)
    {
        return Mage::getBlockSingleton($path)->getHelpPageUrl();
    }

    //########################################
}
