<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Listing_Other_Log extends Ess_M2ePro_Model_Log_Abstract
{
    const ACTION_UNKNOWN = 1;
    const _ACTION_UNKNOWN = 'System';

    const ACTION_REVISE_PRODUCT = 9;
    const _ACTION_REVISE_PRODUCT = 'Revise Product';
    const ACTION_RELIST_PRODUCT = 2;
    const _ACTION_RELIST_PRODUCT = 'Relist Product';
    const ACTION_STOP_PRODUCT = 3;
    const _ACTION_STOP_PRODUCT = 'Stop Product';

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

    const ACTION_CHANGE_PRODUCT_PRICE = 10;
    const _ACTION_CHANGE_PRODUCT_PRICE = 'Change of Product Price in Magento Store';
    const ACTION_CHANGE_PRODUCT_SPECIAL_PRICE = 11;
    const _ACTION_CHANGE_PRODUCT_SPECIAL_PRICE = 'Change of Product Special Price in Magento Store';
    const ACTION_CHANGE_PRODUCT_QTY = 12;
    const _ACTION_CHANGE_PRODUCT_QTY = 'Change of Product QTY in Magento Store';
    const ACTION_CHANGE_PRODUCT_STOCK_AVAILABILITY = 13;
    const _ACTION_CHANGE_PRODUCT_STOCK_AVAILABILITY = 'Change of Product Stock availability in Magento Store';
    const ACTION_CHANGE_PRODUCT_STATUS = 14;
    const _ACTION_CHANGE_PRODUCT_STATUS = 'Change of Product status in Magento Store';

    const ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_FROM_DATE = 15;
    const _ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_FROM_DATE = 'Change of Product Special Price from date in Magento Store';

    const ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_TO_DATE = 16;
    const _ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_TO_DATE = 'Change of Product Special Price to date in Magento Store';

    const ACTION_CHANGE_CUSTOM_ATTRIBUTE = 17;
    const _ACTION_CHANGE_CUSTOM_ATTRIBUTE = 'Change of Product Custom Attribute in Magento Store';

    const ACTION_CHANNEL_CHANGE = 18;
    const _ACTION_CHANNEL_CHANGE = 'Change Item on Channel';

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Other_Log');
    }

    //########################################

    public function addGlobalMessage($initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
                                     $actionId = NULL,
                                     $action = NULL,
                                     $description = NULL,
                                     $type = NULL,
                                     $priority = NULL,
                                     array $additionalData = array())
    {
        $dataForAdd = $this->makeDataForAdd(NULL,
                                            $initiator,
                                            $actionId,
                                            $action,
                                            $description,
                                            $type,
                                            $priority,
                                            $additionalData);

        $this->createMessage($dataForAdd);
    }

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

    public function getActionTitle($type)
    {
        return $this->getActionTitleByClass(__CLASS__,$type);
    }

    public function getActionsTitles()
    {
        return $this->getActionsTitlesByClass(__CLASS__,'ACTION_');
    }

    // ---------------------------------------

    public function clearMessages($listingOtherId = NULL)
    {
        $columnName = !is_null($listingOtherId) ? 'listing_other_id' : NULL;
        $this->clearMessagesByTable('M2ePro/Listing_Other_Log',$columnName,$listingOtherId);
    }

    public function getLastActionIdConfigKey()
    {
        return 'other_listings';
    }

    //########################################

    protected function createMessage($dataForAdd)
    {
        if (!is_null($dataForAdd['listing_other_id'])) {

            $listingOther = Mage::helper('M2ePro/Component')->getComponentObject(
                $this->componentMode,'Listing_Other',$dataForAdd['listing_other_id']
            );

            $dataForAdd['title'] = $listingOther->getChildObject()->getTitle();

            if ($this->componentMode == Ess_M2ePro_Helper_Component_Ebay::NICK) {
                $dataForAdd['identifier'] = $listingOther->getChildObject()->getItemId();
            }

            if ($this->componentMode == Ess_M2ePro_Helper_Component_Amazon::NICK ||
                $this->componentMode == Ess_M2ePro_Helper_Component_Buy::NICK) {
                $dataForAdd['identifier'] = $listingOther->getChildObject()->getGeneralId();
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

        $dataForAdd['additional_data'] = json_encode($additionalData);

        return $dataForAdd;
    }

    //########################################
}