<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Grid_Column_Renderer_Status
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{
    /** @var Ess_M2ePro_Block_Adminhtml_Grid_Column_Renderer_ViewLogIcon_Listing */
    protected $_logRenderer;

    //########################################

    public function render(Varien_Object $row)
    {
        $html = '';
        $listingProductId = (int)$row->getData('listing_product_id');

        if ($this->getColumn()->getData('showLogIcon')) {
            $html .= $this->getLogRenderer()->render($row);

            $additionalData = (array)Mage::helper('M2ePro')->jsonDecode($row->getData('additional_data'));
            $synchNote = (isset($additionalData['synch_template_list_rules_note']))
                          ? $additionalData['synch_template_list_rules_note']
                          : array();

            if (!empty($synchNote)) {
                $synchNote = Mage::helper('M2ePro/View')->getModifiedLogMessage($synchNote);

                if (empty($html)) {
                    $html = <<<HTML
<span style="float:right;">
    <img id="map_link_error_icon_{$row->getId()}"
         class="tool-tip-image"
         style="vertical-align: middle;"
         src="{$this->getSkinUrl('M2ePro/images/warning.png')}"><span
         class="tool-tip-message tool-tip-warning tip-left" style="display:none;">
        <img src="{$this->getSkinUrl('M2ePro/images/i_notice.gif')}">
        <span>{$synchNote}</span>
    </span>
</span>
HTML;
                } else {
                    $html .= <<<HTML
&nbsp;<div id="synch_template_list_rules_note_{$listingProductId}" style="display: none">{$synchNote}</div>
HTML;
                }
            }
        }

        $html .= $this->getCurrentStatus($row);

        if ($row->getData('is_duplicate') && isset($additionalData['item_duplicate_action_required'])) {

            $linkContent = Mage::helper('M2ePro')->__('duplicate');
            if ($this->getColumn()->getData('showLogIcon')) {
                $duplicateContent = '<a href="javascript:" 
                            onclick="EbayListingEbayGridObj.openItemDuplicatePopUp(' . $listingProductId . ');"
                          >' . $linkContent . '</a>';
            } else {
                $duplicateContent = '<span style="color: #ea7601;">' . $linkContent . '</span>';
            }

            $html .= <<<HTML
<div style="float: right; clear: both;">
   {$duplicateContent}
    &nbsp;
    <img style="vertical-align: middle;" src="{$this->getSkinUrl('M2ePro/images/warning.png')}">
</div>
<br>
HTML;
        }

        $html .= $this->getScheduledTag($row) . $this->getLockedTag($row);

        return $html;
    }

    //########################################

    protected function getLogRenderer()
    {
        if ($this->_logRenderer === null) {
            $this->_logRenderer = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_grid_column_renderer_viewLogIcon_listing', '', array(
                    'jsHandler' => 'EbayListingEbayGridObj'
                )
            );
        }

        return $this->_logRenderer;
    }

    //########################################

    protected function getCurrentStatus($row)
    {
        $html = '';

        switch ($row->getData('status')) {
            case Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED:
                $html .= '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                $html .= '<span style="color: green;">' . Mage::helper('M2ePro')->__('Listed') . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN:
                $html .= '<span style="color: red;">' . Mage::helper('M2ePro')->__('Listed (Hidden)') . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE:
                $html .= '<span style="color: red;">' . Mage::helper('M2ePro')->__('Inactive') . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED:
                $html .= '<span style="color: orange;">' . Mage::helper('M2ePro')->__('Inactive (Blocked)') . '</span>';
                break;

            default:
                break;
        }

        return $html;
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
                if (!empty($additionalData['configurator'])) {
                    $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
                    $configurator->setData($additionalData['configurator']);

                    if ($configurator->isIncludingMode()) {
                        if ($configurator->isQtyAllowed()) {
                            $reviseParts[] = 'QTY';
                        }

                        if ($configurator->isPriceAllowed()) {
                            $reviseParts[] = 'Price';
                        }

                        if ($configurator->isTitleAllowed()) {
                            $reviseParts[] = 'Title';
                        }

                        if ($configurator->isSubtitleAllowed()) {
                            $reviseParts[] = 'Subtitle';
                        }

                        if ($configurator->isDescriptionAllowed()) {
                            $reviseParts[] = 'Description';
                        }

                        if ($configurator->isImagesAllowed()) {
                            $reviseParts[] = 'Images';
                        }

                        if ($configurator->isCategoriesAllowed()) {
                            $reviseParts[] = 'Categories / Specifics';
                        }

                        if ($configurator->isShippingAllowed()) {
                            $reviseParts[] = 'Shipping';
                        }

                        if ($configurator->isPaymentAllowed()) {
                            $reviseParts[] = 'Payment';
                        }

                        if ($configurator->isReturnAllowed()) {
                            $reviseParts[] = 'Return';
                        }

                        if ($configurator->isOtherAllowed()) {
                            $reviseParts[] = 'Other';
                        }
                    }
                }

                if (!empty($reviseParts)) {
                    $html .= '<br/><span style="color: #605fff">[Revise of '.implode(', ', $reviseParts)
                        .' is Scheduled...]</span>';
                } else {
                    $html .= '<br/><span style="color: #605fff">[Revise is Scheduled...]</span>';
                }
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_STOP:
                $html .= '<br/><span style="color: #605fff">[Stop is Scheduled...]</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_DELETE:
                $html .= '<br/><span style="color: #605fff">[Delete is Scheduled...]</span>';
                break;

            default:
                break;
        }

        return $html;
    }

    protected function getLockedTag($row)
    {
        if ($row instanceof Ess_M2ePro_Model_Listing_Other) {
            $processingLocks = $row->getProcessingLocks();
        } else {
            $object = Mage::helper('M2ePro/Component_Ebay')->getObject(
                'Listing_Product',
                $row->getData('listing_product_id')
            );

            $processingLocks = $object->getProcessingLocks();
        }

        $html = '';

        foreach ($processingLocks as $processingLock) {
            switch ($processingLock->getTag()) {
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

                default:
                    break;
            }
        }

        return $html;
    }

    //########################################
}
