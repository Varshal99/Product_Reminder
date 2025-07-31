<?php

declare(strict_types=1);

namespace Product\Reminder\Ui\Component\Listing;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Product\Reminder\Model\ResourceModel\EmailLog\Collection;
use Product\Reminder\Model\ResourceModel\EmailLog\CollectionFactory;

/**
 * Class DataProvider
 *
 * Provides data for the Email Log UI grid component.
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data for UI component
     *
     * @return array
     */
    public function getData(): array
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }

        return $this->getCollection()->toArray();
    }

    /**
     * Get collection instance
     *
     * @return Collection
     */
    public function getCollection(): Collection
    {
        return $this->collection;
    }
}
