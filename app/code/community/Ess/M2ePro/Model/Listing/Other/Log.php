<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Listing_Other_Log extends Ess_M2ePro_Model_Log_Abstract
{
    const ACTION_UNKNOWN = 1;
    const _ACTION_UNKNOWN = 'System';

    const ACTION_ADD_LISTING = 4;
    const _ACTION_ADD_LISTING = 'Add new Listing';
    const ACTION_DELETE_LISTING = 5;
    const _ACTION_DELETE_LISTING = 'Delete existing Listing';

    const ACTION_MAP_LISTING = 6;
    const _ACTION_MAP_LISTING = 'Map Listing to Magento Product';

    const ACTION_UNMAP_LISTING = 8;
    const _ACTION_UNMAP_LISTING = 'Unmap Listing from Magento Product';

    const ACTION_MOVE_LISTING = 7;
    const _ACTION_MOVE_LISTING = 'Move to existing M2E Pro Listing';

    const ACTION_CHANNEL_CHANGE = 18;
    const _ACTION_CHANNEL_CHANGE = 'Change Item on Channel';

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Other_Log');
    }

    //########################################

    public function addProductMessage($listingOtherId,
                                      $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
                                      $actionId = NULL,
                                      $action = NULL,
                                      $description = NULL,
                                      $type = NULL,
                                      $priority = NULL)
    {
        $dataForAdd = $this->makeDataForAdd($listingOtherId,
                                            $initiator,
                                            $actionId,
                                            $action,
                                            $description,
                                            $type,
                                            $priority);

        $this->createMessage($dataForAdd);
    }

    //########################################

    public function clearMessages($listingOtherId = NULL)
    {
        $filters = array();

        if (!is_null($listingOtherId)) {
            $filters['listing_other_id'] = $listingOtherId;
        }
        if (!is_null($this->componentMode)) {
            $filters['component_mode'] = $this->componentMode;
        }

        $this->getResource()->clearMessages($filters);
    }

    //########################################

    protected function createMessage($dataForAdd)
    {
        $listingOther = Mage::helper('M2ePro/Component')->getComponentObject(
            $this->componentMode, 'Listing_Other', $dataForAdd['listing_other_id']
        );

        $dataForAdd['title'] = $listingOther->getChildObject()->getTitle();

        if ($this->componentMode == Ess_M2ePro_Helper_Component_Ebay::NICK) {
            $dataForAdd['identifier'] = $listingOther->getChildObject()->getItemId();
        }

        if ($this->componentMode == Ess_M2ePro_Helper_Component_Amazon::NICK) {
            $dataForAdd['identifier'] = $listingOther->getChildObject()->getGeneralId();
        }

        if ($this->componentMode == Ess_M2ePro_Helper_Component_Walmart::NICK) {

            $dataForAdd['identifier'] = $listingOther->getChildObject()->getGtin();

            if (!empty($dataForAdd['additional_data'])) {
                $additionalData = Mage::helper('M2ePro')->jsonDecode($dataForAdd['additional_data']);
                $additionalData['channel_url'] = $listingOther->getChildObject()->getChannelUrl();
                $dataForAdd['additional_data'] = Mage::helper('M2ePro')->jsonEncode($additionalData);
            }
        }

        $dataForAdd['component_mode'] = $this->componentMode;

        Mage::getModel('M2ePro/Listing_Other_Log')
                 ->setData($dataForAdd)
                 ->save()
                 ->getId();
    }

    protected function makeDataForAdd($listingOtherId,
                                      $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
                                      $actionId = NULL,
                                      $action = NULL,
                                      $description = NULL,
                                      $type = NULL,
                                      $priority = NULL,
                                      array $additionalData = array())
    {
        $dataForAdd = array();

        if (!is_null($listingOtherId)) {
            $dataForAdd['listing_other_id'] = (int)$listingOtherId;
        } else {
            $dataForAdd['listing_other_id'] = NULL;
        }

        $dataForAdd['initiator'] = $initiator;

        if (!is_null($actionId)) {
            $dataForAdd['action_id'] = (int)$actionId;
        } else {
            $dataForAdd['action_id'] = NULL;
        }

        if (!is_null($action)) {
            $dataForAdd['action'] = (int)$action;
        } else {
            $dataForAdd['action'] = self::ACTION_UNKNOWN;
        }

        if (!is_null($description)) {
            $dataForAdd['description'] = $description;
        } else {
            $dataForAdd['description'] = NULL;
        }

        if (!is_null($type)) {
            $dataForAdd['type'] = (int)$type;
        } else {
            $dataForAdd['type'] = self::TYPE_NOTICE;
        }

        if (!is_null($priority)) {
            $dataForAdd['priority'] = (int)$priority;
        } else {
            $dataForAdd['priority'] = self::PRIORITY_LOW;
        }

        $dataForAdd['additional_data'] = Mage::helper('M2ePro')->jsonEncode($additionalData);

        return $dataForAdd;
    }

    //########################################
}