<?php

use Ess_M2ePro_Model_Resource_Amazon_Template_Shipping as Resource;

class Ess_M2ePro_Model_Amazon_Template_Shipping_Builder
    extends Ess_M2ePro_Model_ActiveRecord_AbstractBuilder
{
    /** @var Ess_M2ePro_Model_Amazon_Account_Repository */
    private $amazonAccountRepository;

    public function __construct()
    {
        $this->amazonAccountRepository = Mage::getModel('M2ePro/Amazon_Account_Repository');
    }

    protected function prepareData()
    {
        $data = array();

        $keys = array_keys($this->getDefaultData());

        foreach ($keys as $key) {
            if (isset($this->_rawData[$key])) {
                $data[$key] = $this->_rawData[$key];
            }
        }

        if (isset($data['account_id'])) {
            $amazonAccount = $this->amazonAccountRepository->get(($data['account_id']));
            $data[Resource::COLUMN_MARKETPLACE_ID] = $amazonAccount->getMarketplaceId();
        }

        return $data;
    }

    public function getDefaultData()
    {
        return array(
            Resource::COLUMN_TITLE => '',
            Resource::COLUMN_ACCOUNT_ID => '',
            Resource::COLUMN_MARKETPLACE_ID => '',
            Resource::COLUMN_TEMPLATE_ID => '',
        );
    }
}
