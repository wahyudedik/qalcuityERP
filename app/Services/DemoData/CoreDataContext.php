<?php

namespace App\Services\DemoData;

class CoreDataContext
{
    public int $tenantId;

    public int $warehouseId;

    public int $periodId;

    public array $coaMap;       // ['1101' => id, ...]

    public array $productIds;

    public array $customerIds;

    public array $supplierIds;

    public array $employeeIds;

    public int $recordsCreated;

    public function __construct(
        int $tenantId = 0,
        int $warehouseId = 0,
        int $periodId = 0,
        array $coaMap = [],
        array $productIds = [],
        array $customerIds = [],
        array $supplierIds = [],
        array $employeeIds = [],
        int $recordsCreated = 0,
    ) {
        $this->tenantId = $tenantId;
        $this->warehouseId = $warehouseId;
        $this->periodId = $periodId;
        $this->coaMap = $coaMap;
        $this->productIds = $productIds;
        $this->customerIds = $customerIds;
        $this->supplierIds = $supplierIds;
        $this->employeeIds = $employeeIds;
        $this->recordsCreated = $recordsCreated;
    }

    public function addRecords(int $count): void
    {
        $this->recordsCreated += $count;
    }
}
