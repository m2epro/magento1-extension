<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_NonexistentTemplates
    extends Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection
    implements Ess_M2ePro_Model_ControlPanel_Inspection_InspectorInterface,
    Ess_M2ePro_Model_ControlPanel_Inspection_FixerInterface
{
    const FIX_ACTION_SET_NULL     = 'set_null';
    const FIX_ACTION_SET_PARENT   = 'set_parent';
    const FIX_ACTION_SET_TEMPLATE = 'set_template';

    /**@var array */
    protected $_simpleTemplates = array(
        'template_category_id'                 => 'category',
        'template_category_secondary_id'       => 'category',
        'template_store_category_id'           => 'store_category',
        'template_store_category_secondary_id' => 'store_category'
    );

    /** @var array */
    protected $_difficultTemplates = array(
        Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT,
        Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION,
        Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION,
        Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING,
        Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT,
        Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN_POLICY,
    );

    //########################################

    public function getTitle()
    {
        return 'Nonexistent template';
    }

    public function getGroup()
    {
        return Ess_M2ePro_Model_ControlPanel_Inspection_Manager::GROUP_STRUCTURE;
    }

    public function getExecutionSpeed()
    {
        return Ess_M2ePro_Model_ControlPanel_Inspection_Manager::EXECUTION_SPEED_FAST;
    }

    //########################################

    public function process()
    {
        $nonexistentTemplates = array();
        $issues = array();

        foreach ($this->_simpleTemplates as $templateIdField => $templateName) {
            $tempResult = $this->getNonexistentTemplatesBySimpleLogic($templateName, $templateIdField);
            !empty($tempResult) && $nonexistentTemplates[$templateName] = $tempResult;
        }

        foreach ($this->_difficultTemplates as $templateName) {
            $tempResult = $this->getNonexistentTemplatesByDifficultLogic($templateName);
            !empty($tempResult) && $nonexistentTemplates[$templateName] = $tempResult;
        }

        if (!empty($nonexistentTemplates)) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createError(
                $this,
                'Has nonexistent templates',
                $this->renderMetadata($nonexistentTemplates)
            );
        }

        return $issues;
    }

    protected function renderMetadata($data)
    {
        $tableContent = <<<HTML
<tr>
    <th>Listing ID</th>
    <th>Listing Product ID</th>
    <th>Policy ID</th>
    <th>Policy ID Field</th>
    <th>My Mode</th>
    <th>Parent Mode</th>
    <th>Actions</th>
</tr>
HTML;

        $alreadyRendered = array();
        foreach ($data as $templateName => $items) {
            $tableContent .= <<<HTML
<tr>
    <td colspan="15" align="center">{$templateName}</td>
</tr>
HTML;

            foreach ($items as $index => $itemInfo) {
                $myModeWord = '--';
                $parentModeWord = '--';
                $actionsHtml = '';
                $params = array(
                    'template' => $templateName,
                    'field_value' => $itemInfo['my_needed_id'],
                    'field' => $itemInfo['my_needed_id_field'],
                );

                if (!isset($itemInfo['my_mode']) && !isset($itemInfo['parent_mode'])) {
                    $params['action'] = self::FIX_ACTION_SET_NULL;
                    $url = Mage::helper('adminhtml')->getUrl(
                        '*/adminhtml_controlPanel_module_integration_ebay/repairNonexistentTemplates',
                        $params
                    );

                    $actionsHtml .= <<<HTML
<a href="{$url}">set null</a><br>
HTML;
                }

                if (isset($itemInfo['my_mode']) && $itemInfo['my_mode'] == 0) {
                    $myModeWord = 'parent';
                }

                if (isset($itemInfo['my_mode']) && $itemInfo['my_mode'] == 1) {
                    $myModeWord = 'custom';
                    $params['action'] = self::FIX_ACTION_SET_PARENT;
                    $url = Mage::helper('adminhtml')->getUrl(
                        '*/adminhtml_controlPanel_module_integration_ebay/repairNonexistentTemplates',
                        $params
                    );

                    $actionsHtml .= <<<HTML
<a href="{$url}">set parent</a><br>
HTML;
                }

                if (isset($itemInfo['my_mode']) && $itemInfo['my_mode'] == 2) {
                    $myModeWord = 'template';
                    $params['action'] = self::FIX_ACTION_SET_PARENT;
                    $url = Mage::helper('adminhtml')->getUrl(
                        '*/adminhtml_controlPanel_module_integration_ebay/repairNonexistentTemplates',
                        $params
                    );

                    $actionsHtml .= <<<HTML
<a href="{$url}">set parent</a><br>
HTML;
                }

                if (isset($itemInfo['parent_mode']) && $itemInfo['parent_mode'] == 1) {
                    $parentModeWord = 'custom';
                    $params['action'] = self::FIX_ACTION_SET_TEMPLATE;
                    $url = Mage::helper('adminhtml')->getUrl(
                        '*/adminhtml_controlPanel_module_integration_ebay/repairNonexistentTemplates',
                        $params
                    );
                    $onClick = <<<JS
var elem   = $(this),
    result = prompt('Enter Template ID');

if (result) {
    elem.up('tr').remove();
    new Ajax.Request( '{$url}'+ '?template_id=' + result , {
    method: 'get',
    asynchronous : true,
});
}
return false;
JS;
                    $actionsHtml .= <<<HTML
<a href="javascript:void();" onclick="{$onClick}">set template</a><br>
HTML;
                }

                if (isset($itemInfo['parent_mode']) && $itemInfo['parent_mode'] == 2) {
                    $parentModeWord = 'template';
                    $params['action'] = self::FIX_ACTION_SET_TEMPLATE;
                    $url = Mage::helper('adminhtml')->getUrl(
                        '*/adminhtml_controlPanel_module_integration_ebay/repairNonexistentTemplates',
                        $params
                    );
                    $onClick = <<<JS
var elem   = $(this),
    result = prompt('Enter Template ID');

if (result) {
    elem.up('tr').remove();
    new Ajax.Request( '{$url}'+ '?template_id=' + result , {
    method: 'get',
    asynchronous : true,
});
}
return false;
JS;
                    $actionsHtml .= <<<HTML
<a href="javascript:void();" onclick="{$onClick}">set template</a><br>
HTML;
                }

                $key = $templateName . '##' . $myModeWord . '##' . $itemInfo['listing_id'];
                if ($myModeWord === 'parent' && in_array($key, $alreadyRendered)) {
                    continue;
                }

                $alreadyRendered[] = $key;
                $tableContent .= <<<HTML
<tr>
    <td>{$itemInfo['listing_id']}</td>
    <td>{$itemInfo['my_id']}</td>
    <td>{$itemInfo['my_needed_id']}</td>
    <td>{$itemInfo['my_needed_id_field']}</td>
    <td>{$myModeWord}</td>
    <td>{$parentModeWord}</td>
    <td>
        {$actionsHtml}
    </td>
</tr>
HTML;
            }
        }

        $html = <<<HTML
        <table width="100%">
            {$tableContent}
        </table>
HTML;
        return $html;
    }

    public function fix($data)
    {
        if ($data['action'] === self::FIX_ACTION_SET_NULL) {
            $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
            $collection->addFieldToFilter($data['field'], $data['field_value']);

            foreach ($collection->getItems() as $listingProduct) {
                $listingProduct->setData($data['field'], null);
                $listingProduct->save();
            }
        }

        if ($data['action'] === self::FIX_ACTION_SET_PARENT) {
            $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
            $collection->addFieldToFilter($data['field'], $data['field_value']);

            foreach ($collection->getItems() as $listingProduct) {
                $listingProduct->setData(
                    "template_{$data['template']}_mode", Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT
                );
                $listingProduct->setData($data['field'], null);
                $listingProduct->save();
            }
        }

        if ($data['action'] === self::FIX_ACTION_SET_TEMPLATE &&
            $data['template_id']
        ) {
            $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing');
            $collection->addFieldToFilter($data['field'], $data['field_value']);

            foreach ($collection->getItems() as $listing) {
                $listing->setData(
                    "template_{$data['template']}_mode", Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE
                );
                $listing->setData($data['field'], null);
                $listing->setData("template_{$data['template']}_id", $data['template_id']);
                $listing->save();
            }
        }
    }

    //########################################

    protected function getNonexistentTemplatesByDifficultLogic($templateCode)
    {
        $databaseHelper = Mage::helper('M2ePro/Module_Database_Structure');

        $subSelect = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from(
                array(
                    'melp' => $databaseHelper->getTableNameWithPrefix('m2epro_ebay_listing_product')
                ),
                array(
                    'my_id' => 'listing_product_id',
                    'my_mode' => "template_{$templateCode}_mode",
                    'my_template_id' => "template_{$templateCode}_id",
                    'my_custom_id' => "template_{$templateCode}_custom_id",

                    'my_needed_id' => new Zend_Db_Expr(
                        "CASE
                        WHEN melp.template_{$templateCode}_mode = 2 THEN melp.template_{$templateCode}_id
                        WHEN melp.template_{$templateCode}_mode = 1 THEN melp.template_{$templateCode}_custom_id
                        WHEN melp.template_{$templateCode}_mode = 0 THEN IF(mel.template_{$templateCode}_mode = 1,
                                                                            mel.template_{$templateCode}_custom_id,
                                                                            mel.template_{$templateCode}_id)
                        END"
                    ),
                    'my_needed_id_field' => new Zend_Db_Expr(
                        "CASE
                        WHEN melp.template_{$templateCode}_mode = 2 THEN 'template_{$templateCode}_id'
                        WHEN melp.template_{$templateCode}_mode = 1 THEN 'template_{$templateCode}_custom_id'
                        WHEN melp.template_{$templateCode}_mode = 0 THEN IF(mel.template_{$templateCode}_mode = 1,
                                                                            'template_{$templateCode}_custom_id',
                                                                            'template_{$templateCode}_id')
                        END"
                    )
                )
            )
            ->joinLeft(
                array(
                    'mlp' => $databaseHelper->getTableNameWithPrefix('m2epro_listing_product')
                ),
                'melp.listing_product_id = mlp.id',
                array('listing_id' => 'listing_id')
            )
            ->joinLeft(
                array(
                    'mel' => $databaseHelper->getTableNameWithPrefix('m2epro_ebay_listing')
                ),
                'mlp.listing_id = mel.listing_id',
                array(
                    'parent_mode' => "template_{$templateCode}_mode",
                    'parent_template_id' => "template_{$templateCode}_id",
                    'parent_custom_id' => "template_{$templateCode}_custom_id"
                )
            );

        $templateIdName = 'id';
        if (in_array($templateCode, Mage::getModel('M2ePro/Ebay_Template_Manager')->getHorizontalTemplates())) {
            $templateIdName = "template_{$templateCode}_id";
        }

        $result = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from(
                array(
                    'subselect' => new Zend_Db_Expr(
                        '(' . $subSelect->__toString() . ')'
                    )
                ),
                array(
                    'subselect.my_id',
                    'subselect.listing_id',
                    'subselect.my_mode',
                    'subselect.parent_mode',
                    'subselect.my_needed_id',
                    'subselect.my_needed_id_field',
                )
            )
            ->joinLeft(
                array(
                    'template' => $databaseHelper->getTableNameWithPrefix("m2epro_ebay_template_{$templateCode}")
                ),
                "subselect.my_needed_id = template.{$templateIdName}",
                array()
            )
            ->where("template.{$templateIdName} IS NULL")
            ->query()
            ->fetchAll();

        return $result;
    }

    protected function getNonexistentTemplatesBySimpleLogic($templateCode, $templateIdField)
    {
        $databaseHelper = Mage::helper('M2ePro/Module_Database_Structure');

        $select = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from(
                array(
                    'melp' => $databaseHelper->getTableNameWithPrefix('m2epro_ebay_listing_product')
                ),
                array(
                    'my_id' => 'listing_product_id',
                    'my_needed_id' => $templateIdField,
                    'my_needed_id_field' => new Zend_Db_Expr("'{$templateIdField}'")
                )
            )
            ->joinLeft(
                array(
                    'mlp' => $databaseHelper->getTableNameWithPrefix('m2epro_listing_product')
                ),
                'melp.listing_product_id = mlp.id',
                array('listing_id' => 'listing_id')
            )
            ->joinLeft(
                array(
                    'template' => $databaseHelper->getTableNameWithPrefix("m2epro_ebay_template_{$templateCode}")
                ),
                "melp.{$templateIdField} = template.id",
                array()
            )
            ->where("melp.{$templateIdField} IS NOT NULL")
            ->where("template.id IS NULL");

        return $select->query()->fetchAll();
    }

    //########################################
}