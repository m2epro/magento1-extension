<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Manager
{
    private $ownerObject = NULL;
    private $templateNick = NULL;
    private $resultObject = NULL;

    const MODE_PARENT   = 0;
    const MODE_CUSTOM   = 1;
    const MODE_TEMPLATE = 2;

    const COLUMN_PREFIX = 'template';

    const OWNER_LISTING = 'listing';
    const OWNER_LISTING_PRODUCT = 'listing_product';

    const TEMPLATE_RETURN = 'return';
    const TEMPLATE_PAYMENT = 'payment';
    const TEMPLATE_SHIPPING = 'shipping';
    const TEMPLATE_DESCRIPTION = 'description';
    const TEMPLATE_SELLING_FORMAT = 'selling_format';
    const TEMPLATE_SYNCHRONIZATION = 'synchronization';

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing|Ess_M2ePro_Model_Ebay_Listing_Product
     */
    public function getOwnerObject()
    {
        return $this->ownerObject;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Listing|Ess_M2ePro_Model_Ebay_Listing_Product $object
     * @return $this
     * @throws Ess_M2ePro_Model_Exception
     */
    public function setOwnerObject($object)
    {
        if (!($object instanceof Ess_M2ePro_Model_Ebay_Listing) &&
            !($object instanceof Ess_M2ePro_Model_Ebay_Listing_Product)) {
            throw new Ess_M2ePro_Model_Exception('Owner object is out of knowledge range.');
        }
        $this->ownerObject = $object;
        return $this;
    }

    //########################################

    /**
     * @return bool
     */
    public function isListingOwner()
    {
        return $this->getOwnerObject() instanceof Ess_M2ePro_Model_Ebay_Listing;
    }

    /**
     * @return bool
     */
    public function isListingProductOwner()
    {
        return $this->getOwnerObject() instanceof Ess_M2ePro_Model_Ebay_Listing_Product;
    }

    //########################################

    /**
     * @return null|string
     */
    public function getTemplate()
    {
        return $this->templateNick;
    }

    /**
     * @param string $nick
     * @return $this
     * @throws Ess_M2ePro_Model_Exception
     */
    public function setTemplate($nick)
    {
        if (!in_array(strtolower($nick),$this->getAllTemplates())) {
            throw new Ess_M2ePro_Model_Exception('Policy nick is out of knowledge range.');
        }
        $this->templateNick = strtolower($nick);
        return $this;
    }

    //########################################

    /**
     * @return array
     */
    public function getAllTemplates()
    {
        return array(
            self::TEMPLATE_RETURN,
            self::TEMPLATE_SHIPPING,
            self::TEMPLATE_PAYMENT,
            self::TEMPLATE_DESCRIPTION,
            self::TEMPLATE_SELLING_FORMAT,
            self::TEMPLATE_SYNCHRONIZATION
        );
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isFlatTemplate()
    {
        return in_array($this->getTemplate(),$this->getFlatTemplates());
    }

    /**
     * @return array
     */
    public function getFlatTemplates()
    {
        return array(
            self::TEMPLATE_RETURN,
            self::TEMPLATE_SHIPPING,
            self::TEMPLATE_PAYMENT,
        );
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isHorizontalTemplate()
    {
        return in_array($this->getTemplate(),$this->getHorizontalTemplates());
    }

    /**
     * @return array
     */
    public function getHorizontalTemplates()
    {
        return array(
            self::TEMPLATE_SELLING_FORMAT,
            self::TEMPLATE_SYNCHRONIZATION,
            self::TEMPLATE_DESCRIPTION
        );
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isMarketplaceDependentTemplate()
    {
        return in_array($this->getTemplate(), $this->getMarketplaceDependentTemplates());
    }

    /**
     * @return array
     */
    public function getMarketplaceDependentTemplates()
    {
        return array(
            self::TEMPLATE_PAYMENT,
            self::TEMPLATE_SHIPPING,
            self::TEMPLATE_RETURN,
        );
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isTrackingAttributesTemplate()
    {
        return in_array($this->getTemplate(),$this->getTrackingAttributesTemplates());
    }

    /**
     * @return array
     */
    public function getTrackingAttributesTemplates()
    {
        return array(
            self::TEMPLATE_RETURN,
            self::TEMPLATE_SHIPPING,
            self::TEMPLATE_PAYMENT,
            self::TEMPLATE_DESCRIPTION,
            self::TEMPLATE_SELLING_FORMAT
        );
    }

    //########################################

    /**
     * @return string
     */
    public function getModeColumnName()
    {
        return self::COLUMN_PREFIX.'_'.$this->getTemplate().'_mode';
    }

    /**
     * @return string
     */
    public function getCustomIdColumnName()
    {
        return self::COLUMN_PREFIX.'_'.$this->getTemplate().'_custom_id';
    }

    /**
     * @return string
     */
    public function getTemplateIdColumnName()
    {
        return self::COLUMN_PREFIX.'_'.$this->getTemplate().'_id';
    }

    //########################################

    /**
     * @param int $mode
     * @return null|string
     */
    public function getIdColumnNameByMode($mode)
    {
        $name = NULL;

        switch ($mode) {
            case self::MODE_TEMPLATE:
                $name = $this->getTemplateIdColumnName();
                break;
            case self::MODE_CUSTOM:
                $name = $this->getCustomIdColumnName();
                break;
        }

        return $name;
    }

    public function getIdColumnValue()
    {
        $idColumnName = $this->getIdColumnNameByMode($this->getModeValue());

        if (is_null($idColumnName)) {
            return NULL;
        }

        return $this->getOwnerObject()->getData($idColumnName);
    }

    //########################################

    public function getModeValue()
    {
        return $this->getOwnerObject()->getData($this->getModeColumnName());
    }

    public function getCustomIdValue()
    {
        return $this->getOwnerObject()->getData($this->getCustomIdColumnName());
    }

    public function getTemplateIdValue()
    {
        return $this->getOwnerObject()->getData($this->getTemplateIdColumnName());
    }

    //########################################

    public function getParentResultObject()
    {
        if ($this->isListingOwner()) {
            return NULL;
        }

        /** @var Ess_M2ePro_Model_Ebay_Template_Manager $manager */
        $manager = Mage::getModel('M2ePro/Ebay_Template_Manager');
        $manager->setTemplate($this->getTemplate());
        $manager->setOwnerObject($this->getOwnerObject()->getEbayListing());

        return $manager->getResultObject();
    }

    public function getCustomResultObject()
    {
        $id = $this->getCustomIdValue();

        if (is_null($id)) {
            return NULL;
        }

        return $this->makeResultObject($id);
    }

    public function getTemplateResultObject()
    {
        $id = $this->getTemplateIdValue();

        if (is_null($id)) {
            return NULL;
        }

        return $this->makeResultObject($id);
    }

    // ---------------------------------------

    private function makeResultObject($id)
    {
        $modelName = 'Template_';
        $modelName .= $this->getTemplate() == self::TEMPLATE_SELLING_FORMAT ?
                    'SellingFormat' : ucfirst($this->getTemplate());

        if ($this->isHorizontalTemplate()) {
            $object = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                $modelName, $id, NULL, array('template')
            );
        } else {
            $modelName = 'Ebay_'.$modelName;
            $object = Mage::helper('M2ePro')->getCachedObject(
                $modelName, $id, NULL, array('template')
            );
        }

        return $object;
    }

    //########################################

    /**
     * @return bool
     */
    public function isModeParent()
    {
        return $this->getModeValue() == self::MODE_PARENT;
    }

    /**
     * @return bool
     */
    public function isModeCustom()
    {
        return $this->getModeValue() == self::MODE_CUSTOM;
    }

    /**
     * @return bool
     */
    public function isModeTemplate()
    {
        return $this->getModeValue() == self::MODE_TEMPLATE;
    }

    //########################################

    public function getResultObject()
    {
        if (!is_null($this->resultObject)) {
            return $this->resultObject;
        }

        if ($this->isModeParent()) {
            $this->resultObject = $this->getParentResultObject();
        }

        if ($this->isModeCustom()) {
            $this->resultObject = $this->getCustomResultObject();
        }

        if ($this->isModeTemplate()) {
            $this->resultObject = $this->getTemplateResultObject();
        }

        if (is_null($this->resultObject)) {
            throw new Ess_M2ePro_Model_Exception('Unable to get result object.');
        }

        return $this->resultObject;
    }

    //########################################

    /**
     * @return null|string
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getTemplateModelName()
    {
        $name = NULL;

        switch ($this->getTemplate()) {
            case self::TEMPLATE_PAYMENT:
                $name = 'Ebay_Template_Payment';
                break;
            case self::TEMPLATE_SHIPPING:
                $name = 'Ebay_Template_Shipping';
                break;
            case self::TEMPLATE_RETURN:
                $name = 'Ebay_Template_Return';
                break;
            case self::TEMPLATE_SELLING_FORMAT:
                $name = 'Template_SellingFormat';
                break;
            case self::TEMPLATE_DESCRIPTION:
                $name = 'Template_Description';
                break;
            case self::TEMPLATE_SYNCHRONIZATION:
                $name = 'Template_Synchronization';
                break;
        }

        if (is_null($name)) {
            throw new Ess_M2ePro_Model_Exception_Logic(sprintf('Template nick "%s" is unknown.', $this->getTemplate()));
        }

        return $name;
    }

    public function getTemplateModel($returnChildModel = false)
    {
        $model = NULL;

        switch ($this->getTemplate()) {
            case self::TEMPLATE_PAYMENT:
            case self::TEMPLATE_SHIPPING:
            case self::TEMPLATE_RETURN:
                $model = Mage::getModel('M2ePro/'.$this->getTemplateModelName());
                break;

            case self::TEMPLATE_SELLING_FORMAT:
            case self::TEMPLATE_SYNCHRONIZATION:
            case self::TEMPLATE_DESCRIPTION:
                if ($returnChildModel) {
                    $modelPath = ucfirst(Ess_M2ePro_Helper_Component_Ebay::NICK).'_'.$this->getTemplateModelName();
                    $model = Mage::getModel('M2ePro/'.$modelPath);
                } else {
                    $model = Mage::helper('M2ePro/Component')->getComponentModel(
                        Ess_M2ePro_Helper_Component_Ebay::NICK,
                        $this->getTemplateModelName()
                    );
                }
                break;
        }

        if (is_null($model)) {
            throw new Ess_M2ePro_Model_Exception_Logic(sprintf('Template nick "%s" is unknown.', $this->getTemplate()));
        }

        return $model;
    }

    public function getTemplateCollection()
    {
        $collection = NULL;

        switch ($this->getTemplate()) {
            case self::TEMPLATE_PAYMENT:
            case self::TEMPLATE_SHIPPING:
            case self::TEMPLATE_RETURN:
                $collection = $this->getTemplateModel()->getCollection();
                break;

            case self::TEMPLATE_SELLING_FORMAT:
            case self::TEMPLATE_SYNCHRONIZATION:
            case self::TEMPLATE_DESCRIPTION:
                $collection = Mage::helper('M2ePro/Component')->getComponentCollection(
                    Ess_M2ePro_Helper_Component_Ebay::NICK,
                    $this->getTemplateModelName()
                );
                break;
        }

        if (is_null($collection)) {
            throw new Ess_M2ePro_Model_Exception_Logic(sprintf('Template nick "%s" is unknown.', $this->getTemplate()));
        }

        return $collection;
    }

    public function getTemplateBuilder()
    {
        $model = NULL;

        switch ($this->getTemplate()) {
            case self::TEMPLATE_PAYMENT:
                $model = Mage::getModel('M2ePro/Ebay_Template_Payment_Builder');
                break;
            case self::TEMPLATE_SHIPPING:
                $model = Mage::getModel('M2ePro/Ebay_Template_Shipping_Builder');
                break;
            case self::TEMPLATE_RETURN:
                $model = Mage::getModel('M2ePro/Ebay_Template_Return_Builder');
                break;
            case self::TEMPLATE_SELLING_FORMAT:
                $model = Mage::getModel('M2ePro/Ebay_Template_SellingFormat_Builder');
                break;
            case self::TEMPLATE_DESCRIPTION:
                $model = Mage::getModel('M2ePro/Ebay_Template_Description_Builder');
                break;
            case self::TEMPLATE_SYNCHRONIZATION:
                $model = Mage::getModel('M2ePro/Ebay_Template_Synchronization_Builder');
                break;
        }

        if (is_null($model)) {
            throw new Ess_M2ePro_Model_Exception_Logic(sprintf('Template nick "%s" is unknown.', $this->getTemplate()));
        }

        return $model;
    }

    //########################################

    /**
     * @param string $ownerObjectModel
     * @param int $templateId
     * @param bool $asArrays
     * @param string|array $columns
     * @return array
     */
    public function getAffectedOwnerObjects($ownerObjectModel, $templateId, $asArrays = true, $columns = '*')
    {
        /* @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection($ownerObjectModel);

        $where = "({$this->getModeColumnName()} = " . Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM;
        $where .= " AND {$this->getCustomIdColumnName()} = " . (int)$templateId . ")";

        $where .= ' OR ';

        $where .= "({$this->getModeColumnName()} = " . Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE;
        $where .= " AND {$this->getTemplateIdColumnName()} = " . (int)$templateId . ")";

        $collection->getSelect()->where($where);

        if (is_array($columns) && !empty($columns)) {
            $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $collection->getSelect()->columns($columns);
        }

        return $asArrays ? (array)$collection->getData() : (array)$collection->getItems();
    }

    public function getTemplatesFromData($data)
    {
        $resultTemplates = array();

        foreach ($this->getAllTemplates() as $template) {

            $this->setTemplate($template);

            $templateMode = $data[$this->getModeColumnName()];

            if ($templateMode == self::MODE_PARENT) {
                $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$data['listing_id']);
                $templateMode = $listing->getData($this->getModeColumnName());
                $templateId   = $listing->getData($this->getIdColumnNameByMode($templateMode));
            } else {
                $templateId = $data[$this->getIdColumnNameByMode($templateMode)];
            }

            $templateModelName = $this->getTemplateModelName();

            if ($this->isHorizontalTemplate()) {
                $templateModel = Mage::helper('M2ePro/Component_Ebay')
                    ->getCachedObject($templateModelName, $templateId, NULL, array('template'))
                    ->getChildObject();
            } else {
                $templateModel = Mage::helper('M2ePro')->getCachedObject(
                    $templateModelName, $templateId, NULL, array('template')
                );
            }

            $resultTemplates[$template] = $templateModel;
        }

        return $resultTemplates;
    }

    //########################################
}