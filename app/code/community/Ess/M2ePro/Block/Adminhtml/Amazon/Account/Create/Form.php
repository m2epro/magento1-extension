<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Account_Create_Form extends Mage_Adminhtml_Block_Widget
{
    /** @var Ess_M2ePro_Model_Amazon_Marketplace_Repository */
    private $marketplaceRepository;

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonAccountCreateForm');
        $this->setTemplate('M2ePro/amazon/account/create/form.phtml');

        $this->marketplaceRepository = Mage::getModel('M2ePro/Amazon_Marketplace_Repository');
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Marketplace[]
     */
    public function getMarketplaces()
    {
        return $this->marketplaceRepository->getAll();
    }
}
