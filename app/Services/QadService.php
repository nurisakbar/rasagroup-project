<?php

namespace App\Services;

class QadService extends QidApiService
{
    // =========================================================================
    // Bill of Material (BOM)
    // =========================================================================

    public function saveBomFormulaCode(array $payload)
    {
        return $this->post('/api/master/bom/formula-code-save', $payload);
    }

    public function saveBomFormulaDetail(array $payload)
    {
        return $this->post('/api/master/bom/formula-detail-save', $payload);
    }

    public function getBom(string $formulaCode)
    {
        return $this->get('/api/master/bom/get', ['FormulaCode' => $formulaCode]);
    }

    public function getAllBom(string $search = '')
    {
        return $this->get('/api/master/bom/get-all', ['search' => $search]);
    }

    // =========================================================================
    // Business Relation
    // =========================================================================

    public function getBusinessRelation(string $code)
    {
        return $this->get('/api/master/business-relation/get', ['businessRelationCode' => $code]);
    }

    public function listBusinessRelation(array $query = [])
    {
        return $this->get('/api/master/business-relation/list', $query);
    }

    public function createBusinessRelation(array $payload)
    {
        return $this->post('/api/master/business-relation/create', $payload);
    }

    public function updateBusinessRelation(array $payload)
    {
        return $this->patch('/api/master/business-relation/update', $payload);
    }

    // =========================================================================
    // Customer
    // =========================================================================

    public function getCustomer(string $customerCode, string $sharedSetCode = '')
    {
        return $this->get('/api/master/customer/get', [
            'CustomerCode' => $customerCode,
            'SharedSetCode' => $sharedSetCode
        ]);
    }

    public function listCustomer(array $query = [])
    {
        return $this->get('/api/master/customer/list', $query);
    }

    public function createCustomer(array $payload)
    {
        return $this->post('/api/master/customer/create', $payload);
    }

    public function createCustomerData(array $payload)
    {
        return $this->post('/api/master/customer/create-data', $payload);
    }

    public function updateCustomer(array $payload)
    {
        return $this->patch('/api/master/customer/update', $payload);
    }

    // =========================================================================
    // Inventory
    // =========================================================================

    public function getInventoryLocation(array $query)
    {
        return $this->get('/api/master/inventory/location', $query);
    }

    public function getAllInventory(array $payload)
    {
        return $this->post('/api/master/inventory/all', $payload);
    }

    // =========================================================================
    // Item Master
    // =========================================================================

    public function listItem(array $payload)
    {
        return $this->post('/api/master/item/list', $payload);
    }

    public function getItem(string $itemCode)
    {
        return $this->get('/api/master/item/get', ['ItemCode' => $itemCode]);
    }

    public function pagingItem(array $payload)
    {
        return $this->post('/api/master/item/paging', $payload);
    }

    public function saveItem(array $payload)
    {
        return $this->post('/api/master/item/save', $payload);
    }

    public function updateItem(array $payload)
    {
        return $this->patch('/api/master/item/update', $payload);
    }

    // =========================================================================
    // QAD Master (WSA Endpoints)
    // =========================================================================

    public function getQadTrailer(array $query = [])
    {
        return $this->get('/api/master/trailer', $query);
    }

    public function getQadCustomer(array $query = [])
    {
        return $this->get('/api/master/customer', $query);
    }

    public function getQadGeneralLedger(array $query = [])
    {
        return $this->get('/api/master/general-ledger', $query);
    }

    public function getQadCostCentre(array $query = [])
    {
        return $this->get('/api/master/cost-centre', $query);
    }

    public function getQadTax(array $query = [])
    {
        return $this->get('/api/master/tax', $query);
    }

    // =========================================================================
    // Sales Orders
    // =========================================================================

    public function getSalesOrder(string $code)
    {
        return $this->get('/api/transaction/sales-orders/get', ['SalesOrderCode' => $code]);
    }

    public function listSalesOrder(array $query = [])
    {
        return $this->get('/api/transaction/sales-orders/list', $query);
    }

    public function createSalesOrder(array $payload)
    {
        return $this->post('/api/transaction/sales-orders/create', $payload, true);
    }

    public function updateSalesOrder(array $payload)
    {
        return $this->patch('/api/transaction/sales-orders/update', $payload);
    }

    // =========================================================================
    // System / Notification
    // =========================================================================

    /**
     * Send WhatsApp text message.
     */
    public function sendWhatsAppText(string $phone, string $message)
    {
        // We reuse the QadWhatsAppService logic here for convenience
        return app(QadWhatsAppService::class)->sendText($phone, $message);
    }

    /**
     * Re-authenticate and return new token.
     */
    public function refreshAuthToken()
    {
        $this->logout();
        return $this->getToken();
    }

    /**
     * Internal patch method.
     */
    public function patch(string $endpoint, array $payload = []): ?array
    {
        return $this->request('PATCH', $endpoint, $payload);
    }
}
