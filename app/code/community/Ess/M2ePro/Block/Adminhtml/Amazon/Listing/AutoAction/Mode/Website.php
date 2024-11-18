<?php

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_AutoAction_Mode_Website
    extends Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode_WebsiteAbstract
{
    /** @var Ess_M2ePro_Model_Amazon_Template_ProductType_Repository*/
    private $templateProductTypeRepository;

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonListingAutoActionModeWebsite');
        $this->setTemplate('M2ePro/amazon/listing/auto_action/mode/website.phtml');

        $this->templateProductTypeRepository = Mage::getModel('M2ePro/Amazon_Template_ProductType_Repository');
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_ProductType[]
     */
    public function getProductTypesTemplates()
    {
        return $this->templateProductTypeRepository->findByMarketplaceId(
            $this->getListing()->getMarketplaceId()
        );
    }
}
