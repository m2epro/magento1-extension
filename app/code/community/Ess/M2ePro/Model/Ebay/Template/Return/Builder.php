<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Return_Builder
    extends Ess_M2ePro_Model_Ebay_Template_Builder_Abstract
{
    //########################################

    public function build(array $data)
    {
        if (empty($data)) {
            return NULL;
        }

        $this->validate($data);

        $data = $this->prepareData($data);

        $marketplace = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Marketplace', $data['marketplace_id']
        );

        $template = Mage::getModel('M2ePro/Ebay_Template_Return');

        if (isset($data['id'])) {
            $template->load($data['id']);
        }

        $template->addData($data);
        $template->save();
        $template->setMarketplace($marketplace);

        return $template;
    }

    //########################################

    protected function validate(array $data)
    {
        // ---------------------------------------
        if (empty($data['marketplace_id'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('eBay Site ID is empty.');
        }
        // ---------------------------------------

        parent::validate($data);
    }

    protected function prepareData(array &$data)
    {
        $prepared = parent::prepareData($data);

        $prepared['marketplace_id'] = (int)$data['marketplace_id'];

        if (isset($data['accepted'])) {
            $prepared['accepted'] = $data['accepted'];
        }

        if (isset($data['option'])) {
            $prepared['option'] = $data['option'];
        }

        if (isset($data['within'])) {
            $prepared['within'] = $data['within'];
        }

        if (isset($data['holiday_mode'])) {
            $prepared['holiday_mode'] = $data['holiday_mode'];
        }

        if (isset($data['shipping_cost'])) {
            $prepared['shipping_cost'] = $data['shipping_cost'];
        }

        if (isset($data['restocking_fee'])) {
            $prepared['restocking_fee'] = $data['restocking_fee'];
        }

        if (isset($data['description'])) {
            $prepared['description'] = $data['description'];
        }

        if ($prepared['accepted'] != 'ReturnsAccepted') {
            $prepared['holiday_mode'] = 0;
        }

        return $prepared;
    }

    //########################################
}