<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Account_Create_Form extends Mage_Adminhtml_Block_Widget
{
    /** @var array */
    protected $marketplaces;

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonAccountCreateForm');
        $this->setTemplate('M2ePro/amazon/account/create/form.phtml');
    }

    protected function _prepareLayout()
    {
        $marketplaces = Mage::helper('M2ePro/Component_Amazon')->getMarketplacesAvailableForApiCreation();
        $marketplaces = $marketplaces->toArray();
        $this->marketplaces = $marketplaces['items'];

        return parent::_prepareLayout();
    }
}
