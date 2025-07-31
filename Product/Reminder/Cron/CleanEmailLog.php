<?php

declare(strict_types=1);

namespace Product\Reminder\Cron;

use Psr\Log\LoggerInterface;
use Product\Reminder\Model\ResourceModel\EmailLog\CollectionFactory as EmailLogCollectionFactory;

/**
 * Class CleanEmailLog
 *
 * Cron job to remove old email log entries.
 */
class CleanEmailLog
{
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var EmailLogCollectionFactory
     */
    protected EmailLogCollectionFactory $emailLogCollectionFactory;

    /**
     * @param LoggerInterface $logger
     * @param EmailLogCollectionFactory $emailLogCollectionFactory
     */
    public function __construct(
        LoggerInterface $logger,
        EmailLogCollectionFactory $emailLogCollectionFactory
    ) {
        $this->logger = $logger;
        $this->emailLogCollectionFactory = $emailLogCollectionFactory;
    }

    /**
     * Executes the log cleanup cron job.
     *
     * @return void
     */
    public function execute(): void
    {
        try {
            $this->logger->info('[Reminder Log Cleanup] Starting cleanup cron.');

            // Calculate the date 7 days ago
            $cleanupDate = (new \DateTime('-7 days'))->format('Y-m-d H:i:s');

            $collection = $this->emailLogCollectionFactory->create();
            $collection->addFieldToFilter('sent_at', ['lt' => $cleanupDate]);

            $logsDeleted = $collection->getSize();

            if ($logsDeleted > 0) {
                // Delete all items in the filtered collection
                $collection->walk('delete');
                $this->logger->info(sprintf('[Reminder Log Cleanup] Successfully deleted %d log entries older than %s.', $logsDeleted, $cleanupDate));
            } else {
                $this->logger->info('[Reminder Log Cleanup] No old log entries to delete.');
            }
        } catch (\Exception $e) {
            $this->logger->error('[Reminder Log Cleanup] Error: ' . $e->getMessage(), ['exception' => $e]);
        }
    }
}
