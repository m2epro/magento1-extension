<?php

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_AutoAction_Mode_Category_Form
    extends Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode_Category_FormAbstract
{
    /** @var Ess_M2ePro_Model_Amazon_Template_ProductType_Repository*/
    private $templateProductTypeRepository;

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/amazon/listing/auto_action/mode/category/form.phtml');

        $this->templateProductTypeRepository = Mage::getModel('M2ePro/Amazon_Template_ProductType_Repository');
    }

    public function getDefault()
    {
        return array_merge(
            parent::getDefault(),
            array(
                'adding_product_type_template_id' => null
            )
        );
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
