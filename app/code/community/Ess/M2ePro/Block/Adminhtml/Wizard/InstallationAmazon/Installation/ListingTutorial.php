<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationAmazon_Installation_ListingTutorial
    extends Ess_M2ePro_Block_Adminhtml_Wizard_InstallationAmazon_Installation
{
    //########################################

    protected function getStep()
    {
        return 'listingTutorial';
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $this->updateButton('continue', 'label', Mage::helper('M2ePro')->__('Create First Listing'));
        $this->updateButton('continue', 'class', 'primary');

        $url = $this->getUrl('M2ePro/adminhtml_wizard_installationAmazon/skip');
        $this->addButton(
            'skip',
            array(
                'label'   => Mage::helper('M2ePro')->__('Skip'),
                'class'   => 'primary forward',
                'id'      => 'skip',
                'onclick' => "WizardObj.skip('{$url}');"
            ),
            1,
            1
        );
    }

    protected function _prepareLayout()
    {
        $newListingsUrl = $this->getUrl(
            '*/adminhtml_amazon_listing_create/index',
            array(
                'step'  => '1',
                'clear' => 'yes',
            )
        );

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->add($newListingsUrl, 'amazon_listing_create');

        return parent::_prepareLayout();
    }

    //########################################
}
