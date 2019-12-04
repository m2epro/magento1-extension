<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Walmart_Template_ChangeProcessor_Abstract
    extends Ess_M2ePro_Model_Template_ChangeProcessor_Abstract
{
    //########################################

    const INSTRUCTION_TYPE_QTY_DATA_CHANGED        = 'template_qty_data_changed';
    const INSTRUCTION_TYPE_LAG_TIME_DATA_CHANGED   = 'template_lag_time_data_changed';
    const INSTRUCTION_TYPE_PRICE_DATA_CHANGED      = 'template_price_data_changed';
    const INSTRUCTION_TYPE_PROMOTIONS_DATA_CHANGED = 'template_promotions_data_changed';
    const INSTRUCTION_TYPE_DETAILS_DATA_CHANGED    = 'template_details_data_changed';

    //########################################
}
