<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Grid_Column_Renderer_Status
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{
    protected $_lockedDataCache = array();

    protected $_parentAndChildReviseScheduledCache = array();

    //########################################

    public function render(Varien_Object $row)
    {
        $listingProductId  = (int)$row->getData('id');
        $isVariationParent = (bool)(int)$row->getData('is_variation_parent');
        $additionalData    = (array)Mage::helper('M2ePro')->jsonDecode($row->getData('additional_data'));

        $viewLogIcon = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_grid_column_renderer_viewLogIcon_listing'
        );

        $html = $viewLogIcon->render($row);

        if (!empty($additionalData['synch_template_list_rules_note'])) {
            $synchNote = Mage::helper('M2ePro/View')->getModifiedLogMessage(
                $additionalData['synch_template_list_rules_note']
            );

            if (empty($html)) {
                $html = <<<HTML
<span style="float:right;">
    <img id="map_link_error_icon_{$row->getId()}"
         class="tool-tip-image"
         style="vertical-align: middle;"
         src="{$this->getSkinUrl('M2ePro/images/warning.png')}">
    <span class="tool-tip-message tool-tip-warning tip-left" style="display:none;">
        <img src="{$this->getSkinUrl('M2ePro/images/i_notice.gif')}">
        <span>{$synchNote}</span>
    </span>
</span>
HTML;
            } else {
                $html .= <<<HTML
<div id="synch_template_list_rules_note_{$listingProductId}" style="display: none">{$synchNote}</div>
HTML;
            }
        }

        $resetHtml = '';
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED &&
            !$row->getData('is_online_price_invalid')
        ) {
            $resetHtml = <<<HTML
<br/>
<span style="color: gray">[Can be fixed]</span>
HTML;
        }

        if (!$isVariationParent) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct = Mage::helper('M2ePro/Component_Walmart')
                ->getObject('Listing_Product', $listingProductId);

            $statusChangeReasons = $listingProduct->getChildObject()->getStatusChangeReasons();

            return $html
                . $this->getProductStatus($row, $row->getData('status'), $statusChangeReasons)
                . $resetHtml
                . $this->getScheduledTag($row)
                . $this->getLockedTag($row);
        } else {
            $statusNotListed = Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;
            $statusListed    = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
            $statusInactive  = Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE;
            $statusBlocked   = Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED;

            $variationChildStatuses = $row->getData('variation_child_statuses');
            if (empty($variationChildStatuses)) {
                return $html
                    . $this->getProductStatus($row, $statusNotListed)
                    . $this->getScheduledTag($row)
                    . $this->getLockedTag($row);
            }

            $variationChildStatuses = Mage::helper('M2ePro')->jsonDecode($variationChildStatuses);

            $sortedStatuses = array();

            if (isset($variationChildStatuses[$statusNotListed])) {
                $sortedStatuses[$statusNotListed] = $variationChildStatuses[$statusNotListed];
            }

            if (isset($variationChildStatuses[$statusListed])) {
                $sortedStatuses[$statusListed] = $variationChildStatuses[$statusListed];
            }

            if (isset($variationChildStatuses[$statusInactive])) {
                $sortedStatuses[$statusInactive] = $variationChildStatuses[$statusInactive];
            }

            if (isset($variationChildStatuses[$statusBlocked])) {
                $sortedStatuses[$statusBlocked] = $variationChildStatuses[$statusBlocked];
            }

            $linkTitle = Mage::helper('M2ePro')->__('Show all Child Products with such Status');

            foreach ($sortedStatuses as $status => $productsCount) {
                if (empty($productsCount)) {
                    continue;
                }

                $filter = base64_encode('status=' . $status);

                $productTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('name'));
                $vpmt = Mage::helper('M2ePro')->__('Manage Variations of &quot;%s&quot; ', $productTitle);
                $vpmt = addslashes($vpmt);

                $generalId = $row->getData('general_id');
                if (!empty($generalId)) {
                    $vpmt .= '('. $generalId .')';
                }

                $productsCount = <<<HTML
<a onclick="ListingGridObj.variationProductManageHandler.openPopUp({$listingProductId}, '{$vpmt}', '{$filter}')"
   class="hover-underline"
   title="{$linkTitle}"
   href="javascript:void(0)">[{$productsCount}]</a>
HTML;

                $html .= $this->getProductStatus($row, $status) . '&nbsp;'. $productsCount . '<br/>';
            }

            $html .= $this->getScheduledTag($row) . $this->getLockedTag($row);
        }

        return $html;
    }

    protected function getProductStatus($row, $status, $statusChangeReasons = array())
    {
        $html = '';
        switch ($status) {
            case Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED:
                $html = '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
                break;
            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                $html = '<span style="color: green;">' . Mage::helper('M2ePro')->__('Active') . '</span>';
                break;
            case Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE:
                $html ='<span style="color: red;">' . Mage::helper('M2ePro')->__('Inactive') . '</span>';
                break;
            case Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED:
                $html ='<span style="color: orange; font-weight: bold;">'
                    . Mage::helper('M2ePro')->__('Incomplete') . '</span>';
                break;
        }

        return $html .
            $this->getStatusChangeReasons($statusChangeReasons);
    }

    protected function getStatusChangeReasons($statusChangeReasons)
    {
        if (empty($statusChangeReasons)) {
            return '';
        }

        $html = '<li style="margin-bottom: 5px;">'
            . implode('</li><li style="margin-bottom: 5px;">', $statusChangeReasons)
            . '</li>';

        return <<<HTML
        <div style="display: inline-block; width: 16px; margin-left: 3px; margin-right: 4px;">
            <img class="tool-tip-image"
                 style="vertical-align: middle;"
                 src="{$this->getSkinUrl('M2ePro/images/i_icon.png')}">
            <span class="tool-tip-message tool-tip-message tip-left" style="display:none;">
                <img src="{$this->getSkinUrl('M2ePro/images/i_logo.png')}">
                <ul>
                    {$html}
                </ul>
            </span>
        </div>
HTML;
    }

    protected function getScheduledTag($row)
    {
        $html = '';

        $scheduledActionsCollection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $scheduledActionsCollection->addFieldToFilter('listing_product_id', $row->getData('id'));

        /** @var Ess_M2ePro_Model_Listing_Product_ScheduledAction $scheduledAction */
        $scheduledAction = $scheduledActionsCollection->getFirstItem();
        if (!$scheduledAction->getId()) {
            return $html;
        }

        switch ($scheduledAction->getActionType()) {
            case Ess_M2ePro_Model_Listing_Product::ACTION_LIST:
                $html .= '<br/><span style="color: #605fff">[List is Scheduled...]</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_RELIST:
                $html .= '<br/><span style="color: #605fff">[Relist is Scheduled...]</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_REVISE:

                $reviseParts = array();

                $additionalData = $scheduledAction->getAdditionalData();
                if (!empty($additionalData['configurator']) &&
                    !isset($this->_parentAndChildReviseScheduledCache[$row->getData('id')])) {
                    $configurator = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Configurator');
                    $configurator->setData($additionalData['configurator']);

                    if ($configurator->isIncludingMode()) {
                        if ($configurator->isQtyAllowed()) {
                            $reviseParts[] = 'QTY';
                        }

                        if ($configurator->isPriceAllowed()) {
                            $reviseParts[] = 'Price';
                        }

                        if ($configurator->isPromotionsAllowed()) {
                            $reviseParts[] = 'Promotions';
                        }

                        if ($configurator->isDetailsAllowed()) {
                            $params = $additionalData['params'];

                            if (isset($params['changed_sku'])) {
                                $reviseParts[] = 'SKU';
                            }

                            if (isset($params['changed_identifier'])) {
                                $reviseParts[] = strtoupper($params['changed_identifier']['type']);
                            }

                            $reviseParts[] = 'Details';
                        }
                    }
                }

                if (!empty($reviseParts)) {
                    $html .= '<br/><span style="color: #605fff">[Revise of '.
                        implode(', ', $reviseParts).' is Scheduled...]</span>';
                } else {
                    $html .= '<br/><span style="color: #605fff">[Revise is Scheduled...]</span>';
                }
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_STOP:
                $html .= '<br/><span style="color: #605fff">[Stop is Scheduled...]</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_DELETE:
                $html .= '<br/><span style="color: #605fff">[Retire is Scheduled...]</span>';
                break;

            default:
                break;
        }

        return $html;
    }

    protected function getLockedTag($row)
    {
        $html = '';

        $tempLocks = $this->getLockedData($row);
        $childCount = 0;

        foreach ($tempLocks['object_locks'] as $lock) {
            switch ($lock->getTag()) {
                case 'list_action':
                    $html .= '<br/><span style="color: #605fff">[List in Progress...]</span>';
                    break;

                case 'relist_action':
                    $html .= '<br/><span style="color: #605fff">[Relist in Progress...]</span>';
                    break;

                case 'revise_action':
                    $html .= '<br/><span style="color: #605fff">[Revise in Progress...]</span>';
                    break;

                case 'stop_action':
                    $html .= '<br/><span style="color: #605fff">[Stop in Progress...]</span>';
                    break;

                case 'stop_and_remove_action':
                    $html .= '<br/><span style="color: #605fff">[Stop And Remove in Progress...]</span>';
                    break;

                case 'delete_and_remove_action':
                    $html .= '<br/><span style="color: #605fff">[Remove in Progress...]</span>';
                    break;

                case 'child_products_in_action':
                    $childCount++;
                    break;

                default:
                    break;
            }
        }

        if ($childCount > 0) {
            $html .= '<br/><span style="color: #605fff">[Child(s) in Action...]</span>';
        }

        return $html;
    }

    protected function getLockedData($row)
    {
        $listingProductId = $row->getData('id');
        if (!isset($this->_lockedDataCache[$listingProductId])) {
            $objectLocks = Mage::getModel('M2ePro/Listing_Product')->load(
                $listingProductId
            )->getProcessingLocks();
            $tempArray = array(
                'object_locks' => $objectLocks,
                'in_action'    => !empty($objectLocks),
            );
            $this->_lockedDataCache[$listingProductId] = $tempArray;
        }

        return $this->_lockedDataCache[$listingProductId];
    }

    //########################################

    public function setParentAndChildReviseScheduledCache(array $data)
    {
        $this->_parentAndChildReviseScheduledCache = $data;
    }

    //########################################
}
