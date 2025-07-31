<?php

declare(strict_types=1);

namespace Product\Reminder\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class EmailLog Resource Model
 *
 * @package Product\Reminder\Model\ResourceModel
 */
class EmailLog extends AbstractDb
{
    /**
     * Define main table and primary key
     */
    protected function _construct(): void
    {
        $this->_init('product_reminder_email_log', 'entity_id');
    }
}
