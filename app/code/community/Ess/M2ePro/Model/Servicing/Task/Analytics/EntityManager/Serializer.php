<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Servicing_Task_Analytics_EntityManager as EntityManager;

class Ess_M2ePro_Model_Servicing_Task_Analytics_EntityManager_Serializer
{
    //########################################

    public function serialize(Ess_M2ePro_Model_Abstract $item, EntityManager $manager)
    {
        return array(
            'component' => $manager->getComponent(),
            'entity'    => $manager->getEntityType(),
            'id'        => $item->getId(),
            'data'      => $this->prepareEntityData($item, $manager)
        );
    }

    //########################################

    protected function prepareEntityData(Ess_M2ePro_Model_Abstract $item, EntityManager $manager)
    {
        $data = $item->getData();

        unset(
            $data['id'], $data['component_mode'], $data['server_hash'],
            $data[strtolower($manager->getEntityType()).'_id']
        );

        switch ($manager->getComponent() .'::'. $manager->getEntityType()) {
            case Ess_M2ePro_Helper_Component_Amazon::NICK . '::Account':
                unset($data['server_hash'], $data['token']);
                break;

            case Ess_M2ePro_Helper_Component_Amazon::NICK . '::Listing':
                unset($data['account_id'], $data['additional_data']);
                break;

            case Ess_M2ePro_Helper_Component_Amazon::NICK . '::Template_SellingFormat':
                /**@var $item Ess_M2ePro_Model_Template_SellingFormat */
                $data['business_discounts'] = $this->unsetDataInRelatedItems(
                    $item->getChildObject()->getBusinessDiscounts(), 'template_selling_format_id'
                );
                break;

            case Ess_M2ePro_Helper_Component_Amazon::NICK . '::Template_Description':
                /**@var $item Ess_M2ePro_Model_Template_Description */
                $data['specifics'] = $this->unsetDataInRelatedItems(
                    $item->getChildObject()->getSpecifics(), 'template_description_id'
                );
                break;

            // ---------------------------------------

            case Ess_M2ePro_Helper_Component_Ebay::NICK . '::Account':
                unset(
                    $data['server_hash'], $data['info'], $data['user_preferences'], $data['job_token']
                );
                break;

            case Ess_M2ePro_Helper_Component_Ebay::NICK . '::Listing':
                unset($data['account_id'], $data['additional_data'], $data['product_add_ids']);
                break;

            case Ess_M2ePro_Helper_Component_Ebay::NICK . '::Ebay_Template_Payment':
                /**@var $item Ess_M2ePro_Model_Ebay_Template_Payment */
                $data['services'] = $this->unsetDataInRelatedItems($item->getServices(), 'template_payment_id');
                break;

            case Ess_M2ePro_Helper_Component_Ebay::NICK . '::Ebay_Template_Shipping':
                /**@var $item Ess_M2ePro_Model_Ebay_Template_Shipping */
                if ($calculated = $item->getCalculatedShipping()) {
                    $data['calculated'] = $calculated->getData();
                }

                $data['services'] = $this->unsetDataInRelatedItems($item->getServices(), 'template_shipping_id');
                break;

            case Ess_M2ePro_Helper_Component_Ebay::NICK . '::Template_Description':
                unset($data['watermark_image'], $data['description_template']);
                break;

            case Ess_M2ePro_Helper_Component_Ebay::NICK . '::Ebay_Template_Category':
                /**@var $item Ess_M2ePro_Model_Ebay_Template_Category */
                $data['specifics'] = $this->unsetDataInRelatedItems($item->getSpecifics(), 'template_category_id');
                break;

            // ---------------------------------------

            case Ess_M2ePro_Helper_Component_Walmart::NICK . '::Account':
                unset($data['server_hash'], $data['client_id'], $data['client_secret'], $data['private_key']);
                break;

            case Ess_M2ePro_Helper_Component_Walmart::NICK . '::Template_SellingFormat':
                /**@var $item Ess_M2ePro_Model_Template_SellingFormat */
                $data['shipping_overrides'] = $this->unsetDataInRelatedItems(
                    $item->getChildObject()->getShippingOverrides(), 'template_selling_format_id'
                );
                $data['promotions'] = $this->unsetDataInRelatedItems(
                    $item->getChildObject()->getPromotions(), 'template_selling_format_id'
                );
                break;

            case Ess_M2ePro_Helper_Component_Walmart::NICK . '::Template_Description':
                unset($data['description_template']);
                break;

            case Ess_M2ePro_Helper_Component_Walmart::NICK . '::Template_Category':
                /**@var $item Ess_M2ePro_Model_Walmart_Template_Category */
                $data['specifics'] = $this->unsetDataInRelatedItems($item->getSpecifics(), 'template_category_id');
                break;
        }

        return Mage::helper('M2ePro')->jsonEncode($data);
    }

    //########################################

    protected function unsetDataInRelatedItems(array $items, $dataKey)
    {
        return array_map(
            function($el) use ($dataKey) {
            unset($el[$dataKey]);
            return $el;
            }, $items
        );
    }

    //########################################
}
