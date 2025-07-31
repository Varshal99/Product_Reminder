<?php

declare(strict_types=1);

namespace Product\Reminder\Model;

use Magento\Framework\Model\AbstractModel;

class EmailLog extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Product\Reminder\Model\ResourceModel\EmailLog::class);
    }
}
