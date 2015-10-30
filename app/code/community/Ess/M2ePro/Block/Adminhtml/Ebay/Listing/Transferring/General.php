<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Transferring_General extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingTransferringGeneral');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/listing/transferring/general.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------
        $breadcrumb = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_transferring_breadcrumb')
            ->setData('step', 'none');
        $this->setChild('breadcrumb', $breadcrumb);
        // ---------------------------------------

        // ---------------------------------------
        $tutorial = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_transferring_step_tutorial')
            ->setData('is_allowed', !$this->isShownTutorial());
        $this->setChild('tutorial', $tutorial);
        // ---------------------------------------

        // ---------------------------------------
        $destination = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_transferring_step_destination')
            ->setData('listing_id', $this->getData('listing_id'))
            ->setData('products_ids', $this->getData('products_ids'))
            ->setData('is_allowed', true);
        $this->setChild('destination', $destination);
        // ---------------------------------------

        // ---------------------------------------
        $policy = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_transferring_step_policy')
            ->setData('is_allowed', false);
        $this->setChild('policy', $policy);
        // ---------------------------------------

        // ---------------------------------------
        $translation = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_transferring_step_translation')
            ->setData('is_allowed', false);
        $this->setChild('translation', $translation);
        // ---------------------------------------

        // ---------------------------------------
        $categories = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_Ebay_Listing_Transferring_step_categories')
            ->setData('is_allowed', true);
        $this->setChild('categories', $categories);
        // ---------------------------------------
    }

    //########################################

    public function isShownTutorial()
    {
        return (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/ebay/sell_on_another_marketplace/', 'tutorial_shown');
    }

    //########################################
}