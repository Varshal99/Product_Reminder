<?php

declare(strict_types=1);

namespace Product\Reminder\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class AddRepurchaseReminderAttribute
 *
 * Adds the "repurchase_reminder" product attribute to enable/disable repurchase reminder functionality.
 */
class AddRepurchaseReminderAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * AddRepurchaseReminderAttribute constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Apply the data patch to add the product attribute.
     *
     * @return void
     */
    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'repurchase_reminder',
            [
                'type' => 'int',
                'label' => 'Enable Product Reminder',
                'input' => 'boolean',
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'required' => false,
                'default' => 0,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
                'user_defined' => true,
                'group' => 'General',
                'backend' => '',
                'visible_on_front' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
            ]
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Get patch dependencies
     *
     * @return array
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Get patch aliases
     *
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }
}
