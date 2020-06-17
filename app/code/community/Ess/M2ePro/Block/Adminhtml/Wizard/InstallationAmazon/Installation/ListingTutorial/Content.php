<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationAmazon_Installation_ListingTutorial_Content
    extends Mage_Adminhtml_Block_Template
{
    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setId('wizardInstallationAmazonListingTutorial');
    }

    //########################################

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('wizard.help.block')->setContent(
            Mage::helper('M2ePro')->__(
                <<<HTML
<div class="wizard_block">
    <h2>
        Congratulations! You have successfully registered your M2E Pro Extension and linked your Amazon Account to it.
    </h2>

    <p>
        The next step involves creating your first M2E Pro Listing.
    </p>

    <br/>
    <p>
        M2E Pro Listing is a group of Magento Products combined under specific sets of rules. 
        <br/> 
        These are the Products which will be offered as Amazon Items by a Seller on a particular Marketplace.
    </p>

    <br/>
    <p>
        Click on <strong>Create First Listing</strong> button to proceed.
    </p>
</div>
HTML
            )
        );

        return parent::_prepareLayout();
    }

    //########################################
}
