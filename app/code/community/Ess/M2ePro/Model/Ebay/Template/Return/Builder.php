<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Return_Builder
    extends Ess_M2ePro_Model_Ebay_Template_Builder_Abstract
{
    //########################################

    public function build(array $data)
    {
        if (empty($data)) {
            return null;
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

        $domesticKeys = array(
            'accepted', 'option', 'within', 'shipping_cost'
        );
        foreach ($domesticKeys as $keyName) {
            isset($data[$keyName]) && $prepared[$keyName] = $data[$keyName];
        }

        $internationalKeys = array(
            'international_accepted', 'international_option', 'international_within', 'international_shipping_cost'
        );
        foreach ($internationalKeys as $keyName) {
            isset($data[$keyName]) && $prepared[$keyName] = $data[$keyName];
        }

        isset($data['description']) && $prepared['description'] = $data['description'];

        return $prepared;
    }

    //########################################
}
