<?php

declare(strict_types=1);

namespace Product\Reminder\Cron;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Product\Reminder\Model\EmailLogFactory;

/**
 * Class SendReminderEmail
 *
 * Cron job to send reminder emails to customers for repurchasing low-stock products.
 */
class SendReminderEmail
{
    /** Config path to enable/disable reminder emails */
    private const XML_PATH_ENABLE_REMINDER = 'product_reminder/settings/enable';

    /** Config path to set stock quantity threshold */
    private const XML_PATH_STOCK_THRESHOLD = 'product_reminder/settings/stock_threshold';

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var TransportBuilder
     */
    protected TransportBuilder $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var ProductCollectionFactory
     */
    protected ProductCollectionFactory $productCollectionFactory;

    /**
     * @var StockRegistryInterface
     */
    protected StockRegistryInterface $stockRegistry;

    /**
     * @var OrderCollectionFactory
     */
    protected OrderCollectionFactory $orderCollectionFactory;

    /**
     * @var Escaper
     */
    protected Escaper $escaper;

    /**
     * @var EmailLogFactory
     */
    protected EmailLogFactory $emailLogFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param ProductCollectionFactory $productCollectionFactory
     * @param StockRegistryInterface $stockRegistry
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param Escaper $escaper
     * @param EmailLogFactory $emailLogFactory
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        ProductCollectionFactory $productCollectionFactory,
        StockRegistryInterface $stockRegistry,
        OrderCollectionFactory $orderCollectionFactory,
        Escaper $escaper,
        EmailLogFactory $emailLogFactory,
        ProductRepositoryInterface $productRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->stockRegistry = $stockRegistry;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->escaper = $escaper;
        $this->emailLogFactory = $emailLogFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * Executes the reminder cron job.
     *
     * Sends reminder emails to customers for products they purchased in the last 180 days,
     * if the product has low stock and repurchase reminders are enabled.
     *
     * @return void
     */
    public function execute(): void
    {
        try {
            $isEnabled = $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLE_REMINDER, ScopeInterface::SCOPE_STORE);
            if (!$isEnabled) {
                $this->logger->info('[Reminder Cron] Disabled via configuration.');
                return;
            }

            $stockThreshold = (int) $this->scopeConfig->getValue(self::XML_PATH_STOCK_THRESHOLD, ScopeInterface::SCOPE_STORE);
            $store = $this->storeManager->getStore();
            $storeId = $store->getId();
            
            $orderCollection = $this->orderCollectionFactory->create()
                ->addFieldToFilter('created_at', ['gteq' => (new \DateTime('-180 days'))->format('Y-m-d H:i:s')]);

            $sentReminders = [];

            foreach ($orderCollection as $order) {
                $customerEmail = $order->getCustomerEmail();
                $customerName = $order->getCustomerName();

                if (!$customerEmail) {
                    continue;
                }

                foreach ($order->getAllVisibleItems() as $item) {
                    $product = $item->getProduct();
                    $productId = $product->getId();

                    try {
                        $fullProduct = $this->productRepository->getById($productId, false, $storeId);
                    } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                        $this->logger->error(sprintf('[Reminder Cron] Product with ID %s not found.', $productId));
                        continue;
                    }

                    if (!$fullProduct->getRepurchaseReminder()) {
                        continue;
                    }

                    $reminderKey = $productId . '-' . $customerEmail;
                    if (isset($sentReminders[$reminderKey])) {
                        continue;
                    }

                    $stockItem = $this->stockRegistry->getStockItem($productId);

                    if ($stockItem && $stockItem->getIsInStock() && $stockItem->getQty() <= $stockThreshold) {
                        $productUrl = $fullProduct->getProductUrl();
                        $imageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                            . 'catalog/product' . $fullProduct->getSmallImage();

                        $templateVars = [
                            'customer_name' => $this->escaper->escapeHtml($customerName),
                            'product_name' => $this->escaper->escapeHtml($fullProduct->getName()),
                            'product_price' => number_format($fullProduct->getFinalPrice(), 2),
                            'product_url' => $productUrl,
                            'product_image' => $imageUrl
                        ];

                        $sender = [
                            'name' => $this->escaper->escapeHtml($this->scopeConfig->getValue('trans_email/ident_support/name', ScopeInterface::SCOPE_STORE)),
                            'email' => $this->escaper->escapeHtml($this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE))
                        ];

                        $transport = $this->transportBuilder
                            ->setTemplateIdentifier('product_reminder_email_template')
                            ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId])
                            ->setTemplateVars($templateVars)
                            ->setFromByScope($sender)
                            ->addTo($customerEmail, $customerName)
                            ->getTransport();

                        $transport->sendMessage();

                        $emailLog = $this->emailLogFactory->create();
                        $emailLog->setData([
                            'customer_email' => $customerEmail,
                            'customer_name' => $customerName,
                            'product_name' => $fullProduct->getName(),
                            'email_status' => 'sent',
                            'sent_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                        ]);
                        $emailLog->save();

                        $sentReminders[$reminderKey] = true;

                        $this->logger->info(sprintf(
                            '[Reminder Cron] Email sent for Product ID %s to %s',
                            $productId,
                            $customerEmail
                        ));
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('[Reminder Cron] Error: ' . $e->getMessage(), ['exception' => $e]);
        }
    }
}
