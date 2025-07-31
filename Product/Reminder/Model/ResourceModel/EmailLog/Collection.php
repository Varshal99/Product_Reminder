<?php

declare(strict_types=1);

namespace Product\Reminder\Model\ResourceModel\EmailLog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Product\Reminder\Model\EmailLog::class,
            \Product\Reminder\Model\ResourceModel\EmailLog::class
        );
    }
}
