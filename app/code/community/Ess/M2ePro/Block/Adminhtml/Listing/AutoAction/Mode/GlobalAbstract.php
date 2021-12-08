<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode_GlobalAbstract
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_listing;

    //########################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'      => 'edit_form',
                'action'  => $this->getUrl('*/*/save'),
                'method'  => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    //########################################

    public function hasFormData()
    {
        return $this->getListing()->getData('auto_mode') == Ess_M2ePro_Model_Listing::AUTO_MODE_GLOBAL;
    }

    public function getFormData()
    {
        return $this->getListing()->getData();
    }

    public function getDefault()
    {
        return array(
            'auto_global_adding_mode'            => Ess_M2ePro_Model_Listing::ADDING_MODE_NONE,
            'auto_global_adding_add_not_visible' => Ess_M2ePro_Model_Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
            'auto_global_deleting_mode'          => Ess_M2ePro_Model_Listing::DELETING_MODE_STOP_REMOVE,
        );
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     * @throws Exception
     */
    public function getListing()
    {
        if ($this->_listing === null) {
            $this->_listing = Mage::helper('M2ePro/Component')->getCachedUnknownObject(
                'Listing',
                $this->getRequest()->getParam('listing_id')
            );
        }

        return $this->_listing;
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $data = array(
            'id'      => 'confirm_button',
            'class'   => 'confirm_button',
            'label'   => Mage::helper('M2ePro')->__('Save'),
            'onclick' => 'ListingAutoActionObj.confirm();',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('confirm_button', $buttonBlock);

        $data = array(
            'id'      => 'continue_button',
            'class'   => 'continue_button next',
            'label'   => Mage::helper('M2ePro')->__('Continue'),
            'style'   => 'display: none;',
            'onclick' => '',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('continue_button', $buttonBlock);
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
                    'These Rules of the automatic product adding and removal act globally for all Magento Catalog. 
                    When a new Magento Product is added to Magento Catalog, it will be automatically added to the 
                    current M2E Pro Listing if the settings are enabled.<br><br>
                    Please note if a product is already presented in another M2E Pro Listing with the related Channel 
                    account and marketplace, the Item won’t be added to the Listing to prevent listing duplicates on 
                    the Channel.<br><br>
                    Accordingly, if a Magento Product presented in the M2E Pro Listing is removed from Magento Catalog, 
                    the Item will be removed from the Listing and its sale will be stopped on Channel.<br><br>
                    More detailed information you can find <a href="%url%" target="_blank">here</a>',
                    $link
                )
            )
        );
        $this->setChild('help_block', $helpBlock);
    }

    //########################################
}
