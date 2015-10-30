<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Translation_Product_Add_MultipleResponser
    extends Ess_M2ePro_Model_Connector_Translation_Responser
{
    protected $listingsProducts = array();

    protected $failedListingsProducts = array();
    protected $succeededListingsProducts = array();

    private $descriptionTemplatesIds = array();

    //########################################

    public function __construct(array $params = array())
    {
        parent::__construct($params);

        foreach ($this->params['products'] as $listingProductId => $listingProductData) {
            try {
                $this->listingsProducts[] = Mage::helper('M2ePro/Component_Ebay')
                    ->getObject('Listing_Product',(int)$listingProductId);
            } catch (Exception $exception) {}
        }
    }

    /**
     * @param Ess_M2ePro_Model_Processing_Request $processingRequest
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function unsetProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::unsetProcessingLocks($processingRequest);

        $alreadyUnlockedListings = array();
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $listingProduct->deleteObjectLocks(NULL, $processingRequest->getHash());
            $listingProduct->deleteObjectLocks('in_action', $processingRequest->getHash());
            $listingProduct->deleteObjectLocks('translation_action', $processingRequest->getHash());

            if (isset($alreadyUnlockedListings[$listingProduct->getListingId()])) {
                continue;
            }

            $listingProduct->getListing()->deleteObjectLocks(NULL, $processingRequest->getHash());

            $alreadyUnlockedListings[$listingProduct->getListingId()] = true;
        }
    }

    public function eventFailedExecuting($message)
    {
        parent::eventFailedExecuting($message);

        $alreadyLoggedListings = array();
        foreach ($this->listingsProducts as $listingProduct) {

            $listingProduct->getChildObject()->setData(
                'translation_status', Ess_M2ePro_Model_Ebay_Listing_Product::TRANSLATION_STATUS_PENDING
            )->save();

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            if (isset($alreadyLoggedListings[$listingProduct->getListingId()])) {
                continue;
            }

            $this->addListingsProductsLogsMessage(
                $listingProduct,
                $message,
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );

            $alreadyLoggedListings[$listingProduct->getListingId()] = true;
        }
    }

    //########################################

    protected function addListingsProductsLogsMessage(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                                      $text, $type = Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                                      $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM)
    {
        $action = Ess_M2ePro_Model_Listing_Log::ACTION_TRANSLATE_PRODUCT;

        if ($this->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN) {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;
        } else if ($this->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_USER;
        } else {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION;
        }

        /** @var  $logModel Ess_M2ePro_Model_Listing_Log */
        $logModel = Mage::getModel('M2ePro/Listing_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

        $logModel->addProductMessage($listingProduct->getListingId() ,
                                     $listingProduct->getProductId() ,
                                     $listingProduct->getId() ,
                                     $initiator ,
                                     $this->getLogsActionId() ,
                                     $action , $text, $type , $priority);
    }

    //########################################

    protected function validateResponseData($response)
    {
        return true;
    }

    protected function processResponseData($response)
    {
        $failedListingsProductsIds = array();

        // Check global messages
        // ---------------------------------------
        $globalMessages = $this->messages;

        foreach ($this->listingsProducts as $listingProduct) {

            $hasError = false;
            foreach ($globalMessages as $message) {

                $type = $this->getTypeByServerMessage($message);
                $priority = $this->getPriorityByServerMessage($message);
                $text = $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY];

                !$hasError && $hasError = $this->getHasErrorByServerMessage($message);

                $this->addListingsProductsLogsMessage($listingProduct,$text,$type,$priority);

                if (strpos($text, 'code:64') !== false) {

                    preg_match("/amount_due\:(.*?)\s*,\s*currency\:(.*?)\s*\)/i", $text, $matches);

                    $additionalData = $listingProduct->getAdditionalData();
                    $additionalData['translation_service']['payment'] = array(
                        'amount_due' => $matches[1],
                        'currency'   => $matches[2],
                    );

                    $listingProduct->setData('additional_data', json_encode($additionalData))->save();
                    $listingProduct->getChildObject()->setData(
                        'translation_status',
                        Ess_M2ePro_Model_Ebay_Listing_Product::TRANSLATION_STATUS_PENDING_PAYMENT_REQUIRED
                    )->save();
                }
            }

            if ($hasError && !in_array($listingProduct->getId(),$failedListingsProductsIds)) {
                $this->failedListingsProducts[] = $listingProduct;
                $failedListingsProductsIds[] = $listingProduct->getId();
            }
        }

        // ---------------------------------------

        foreach ($this->listingsProducts as $listingProduct) {

            if (in_array($listingProduct->getId(),$failedListingsProductsIds)) {
                continue;
            }

            $this->succeededListingsProducts[] = $listingProduct;

            foreach ($response['products'] as $responseProduct) {
               if ($responseProduct['sku'] == $this->params['products'][$listingProduct->getId()]['sku']) {
                    $this->updateProduct($listingProduct, $responseProduct);
                    break;
                }
            }

            // M2ePro_TRANSLATIONS
            // Product has been successfully Translated.
            $this->addListingsProductsLogsMessage($listingProduct, 'Product has been successfully Translated.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
        }
    }

    //########################################

    protected function updateProduct(Ess_M2ePro_Model_Listing_Product $listingProduct, array $response)
    {
        $productData = array();
        $descriptionTemplate = $listingProduct->getChildObject()->getDescriptionTemplate();
        $oldDescriptionTemplateId = $descriptionTemplate->getId();

        if (!isset($this->descriptionTemplatesIds[$oldDescriptionTemplateId]) && (
            trim($descriptionTemplate->getData('title_template'))       != '#ebay_translated_title#'    ||
            trim($descriptionTemplate->getData('subtitle_template'))    != '#ebay_translated_subtitle#' ||
            trim($descriptionTemplate->getData('description_template')) != '#ebay_translated_description#')) {

            $data = $descriptionTemplate->getDataSnapshot();
            unset($data['id'], $data['update_date'], $data['create_date']);

            $data['title']                = $data['title']
                .Mage::helper('M2ePro')->__(' (Changed because Translation Service applied.)');
            $data['title_mode']           = Ess_M2ePro_Model_Ebay_Template_Description::TITLE_MODE_CUSTOM;
            $data['title_template']       = '#ebay_translated_title#';
            $data['subtitle_mode']        = Ess_M2ePro_Model_Ebay_Template_Description::SUBTITLE_MODE_CUSTOM;
            $data['subtitle_template']    = '#ebay_translated_subtitle#';
            $data['description_mode']     = Ess_M2ePro_Model_Ebay_Template_Description::DESCRIPTION_MODE_CUSTOM;
            $data['description_template'] = '#ebay_translated_description#';
            $data['is_custom_template']   = 1;

            $newDescriptionTemplate = Mage::getModel('M2ePro/Ebay_Template_Manager')
                ->setTemplate(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION)
                ->getTemplateBuilder()
                ->build($data);
            $this->descriptionTemplatesIds[$oldDescriptionTemplateId] = $newDescriptionTemplate->getId();
        }

        if (isset($this->descriptionTemplatesIds[$oldDescriptionTemplateId])) {
            $productData['template_description_custom_id'] = $this->descriptionTemplatesIds[$oldDescriptionTemplateId];
            $productData['template_description_mode']      = Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM;
        }

        $attributes = array(
            'ebay_translated_title'       => array('label' => 'Ebay Translated Title', 'type' => 'text'),
            'ebay_translated_subtitle'    => array('label' => 'Ebay Translated Subtitle', 'type' => 'text'),
            'ebay_translated_description' => array('label' => 'Ebay Translated Description', 'type' => 'textarea')
        );
        $this->checkAndCreateMagentoAttributes($listingProduct->getMagentoProduct(), $attributes);

        $listingProduct->getMagentoProduct()
                       ->setAttributeValue('ebay_translated_title',       $response['title'])
                       ->setAttributeValue('ebay_translated_subtitle',    $response['subtitle'])
                       ->setAttributeValue('ebay_translated_description', $response['description']);
        // ---------------------------------------

        $categoryPath = !is_null($response['category']['primary_id'])
            ? Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getPath((int)$response['category']['primary_id'],
                                                                            $this->params['marketplace_id'])
            : '';

        $response['category']['path'] = $categoryPath;

        if ($categoryPath) {
            $data = Mage::getModel('M2ePro/Ebay_Template_Category')->getDefaultSettings();
            $data['category_main_id']   = (int)$response['category']['primary_id'];
            $data['category_main_path'] = $categoryPath;
            $data['marketplace_id']     = $this->params['marketplace_id'];
            $data['specifics']          = $this->getSpecificsData($response['item_specifics']);

            $productData['template_category_id'] =
                Mage::getModel('M2ePro/Ebay_Template_Category_Builder')->build($data)->getId();
        } else {
            $response['category']['primary_id'] = null;
        }

        $additionalData = $listingProduct->getAdditionalData();
        $additionalData['translation_service']['to'] = array_merge(
            $additionalData['translation_service']['to'], $response
        );
        $productData['additional_data'] = json_encode($additionalData);

        $listingProduct->addData($productData)->save();
        $listingProduct->getChildObject()->addData(array(
            'translation_status' => Ess_M2ePro_Model_Ebay_Listing_Product::TRANSLATION_STATUS_TRANSLATED,
            'translated_date'    => Mage::helper('M2ePro')->getCurrentGmtDate()
        ))->save();
    }

    //########################################

    protected function getHasErrorByServerMessage($message)
    {
        switch ($message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_KEY]) {
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_WARNING:
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_SUCCESS:
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_NOTICE:
                return false;
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_ERROR:
            default:
                return true;
        }
    }

    protected function getTypeByServerMessage($message)
    {
        switch ($message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_KEY]) {

            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_WARNING:
                return Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;
                break;
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_SUCCESS:
                return Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS;
                break;
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_NOTICE:
                return Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE;
                break;
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_ERROR:
            default:
                return Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR;
                break;
        }
    }

    protected function getPriorityByServerMessage($message)
    {
        switch ($message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_KEY]) {

            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_WARNING:
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_SUCCESS:
                return Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM;
                break;
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_NOTICE:
                return Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW;
                break;
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_ERROR:
            default:
                return Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH;
                break;
        }
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account','account_id');
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getObjectByParam('Marketplace','marketplace_id');
    }

    // ---------------------------------------

    protected function getStatusChanger()
    {
        return (int)$this->params['status_changer'];
    }

    protected function getLogsActionId()
    {
        return (int)$this->params['logs_action_id'];
    }

    //########################################

    private function checkAndCreateMagentoAttributes($magentoProduct, array $attributes)
    {
        $attributeHelper = Mage::helper('M2ePro/Magento_Attribute');

        $attributeSetId  = $magentoProduct->getProduct()->getAttributeSetId();
        $attributesInSet = $attributeHelper->getByAttributeSet($attributeSetId);

        /** @var Ess_M2ePro_Model_Magento_AttributeSet_Group $model */
        $model = Mage::getModel('M2ePro/Magento_AttributeSet_Group');
        $model->setGroupName('Ebay')
              ->setAttributeSetId($attributeSetId)
              ->save();

        foreach ($attributes as $attributeCode => $attributeProp) {

            if (!$attributeHelper->getByCode($attributeCode)) {

                /** @var Ess_M2ePro_Model_Magento_Attribute_Builder $model */
                $model = Mage::getModel('M2ePro/Magento_Attribute_Builder');
                $model->setCode($attributeCode)
                      ->setLabel($attributeProp['label'])
                      ->setInputType($attributeProp['type'])
                      ->setScope($model::SCOPE_STORE);

                $model->save();
            }

            if (!$attributeHelper->isExistInAttributesArray($attributeCode, $attributesInSet)) {

                /** @var Ess_M2ePro_Model_Magento_Attribute_Relation $model */
                $model = Mage::getModel('M2ePro/Magento_Attribute_Relation');
                $model->setCode($attributeCode)
                      ->setAttributeSetId($attributeSetId)
                      ->setGroupName('Ebay');

                $model->save();
            }
        }

        return true;
    }

    // ---------------------------------------

    private function getSpecificsData($responseSpecifics)
    {
        $data = array();
        foreach ($responseSpecifics as $responseSpecific) {
            $data[] = array(
                'mode'                  => Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_CUSTOM_ITEM_SPECIFICS,
                'attribute_title'       => $responseSpecific['name'],
                'value_mode'            => Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE,
                'value_ebay_recommended'=> json_encode(array()),
                'value_custom_value'    => join(",", $responseSpecific['value']),
                'value_custom_attribute'=> ''
            );
        }

        return $data;
    }

    //########################################
}