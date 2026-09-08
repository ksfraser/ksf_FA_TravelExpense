<?php
/**
 * PHPUnit Tests for TravelExpense Module
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../includes/travel_db.inc';

class TravelExpenseDBTest extends TestCase
{
    private $mockDb;
    
    protected function setUp(): void
    {
        global $db;
        $this->mockDb = new MockDB();
        $db = $this->mockDb;
    }
    
    protected function tearDown(): void
    {
        global $db;
        $db = null;
    }
    
    public function testCreateTravelRequestBasic(): void
    {
        $result = create_travel_request(1, 'Client Meeting', '2024-03-01', '2024-03-05', 1500.00);
        
        $this->assertGreaterThan(0, $result);
    }
    
    public function testCreateTravelRequestWithProject(): void
    {
        $result = create_travel_request(1, 'Project Site Visit', '2024-03-01', '2024-03-05', 2000.00, 'PRJ-001', 'TSK-001');
        
        $this->assertGreaterThan(0, $result);
    }
    
    public function testCreateTravelRequestWithPerDiem(): void
    {
        $result = create_travel_request(1, 'Conference', '2024-03-10', '2024-03-15', 3000.00, 'PRJ-002', null, 75.00);
        
        $this->assertGreaterThan(0, $result);
    }
    
    public function testGetTravelRequestsNoFilters(): void
    {
        $result = get_travel_requests([]);
        
        $this->assertNotFalse($result);
    }
    
    public function testGetTravelRequestsByEmployee(): void
    {
        $result = get_travel_requests(['employee_id' => 1]);
        
        $this->assertNotFalse($result);
    }
    
    public function testGetTravelRequestsByProject(): void
    {
        $result = get_travel_requests(['project_id' => 'PRJ-001']);
        
        $this->assertNotFalse($result);
    }
    
    public function testGetTravelRequestsByStatus(): void
    {
        $result = get_travel_requests(['status' => 'Pending']);
        
        $this->assertNotFalse($result);
    }
    
    public function testGetTravelRequest(): void
    {
        $result = get_travel_request(1);
        
        $this->assertNull($result); // No data in mock
    }
    
    public function testApproveTravelRequest(): void
    {
        $result = approve_travel_request(1, 1, 'Approved');
        
        $this->assertTrue($result);
    }
    
    public function testRejectTravelRequest(): void
    {
        $result = reject_travel_request(1, 1, 'Budget constraints');
        
        $this->assertTrue($result);
    }
    
    public function testAddExpenseBasic(): void
    {
        $result = add_expense(1, 'Hotel', 250.00, 'Marriott');
        
        $this->assertGreaterThan(0, $result);
    }
    
    public function testAddExpenseWithProject(): void
    {
        $result = add_expense(1, 'Plane', 500.00, 'United Airlines', [
            'project_id' => 'PRJ-001',
            'task_id' => 'TSK-001',
            'activity_code' => 'BILLABLE',
            'billable' => 1
        ]);
        
        $this->assertGreaterThan(0, $result);
    }
    
    public function testAddExpenseMileage(): void
    {
        $result = add_expense(1, 'Mileage', 0.67, 'Client site visit', [
            'mileage_miles' => 150,
            'mileage_rate' => 0.67,
            'billable' => 1
        ]);
        
        $this->assertGreaterThan(0, $result);
    }
    
    public function testAddExpenseMealsBreakdown(): void
    {
        $result = add_expense(1, 'Meals_Breakfast', 15.00, 'Hotel breakfast');
        $this->assertGreaterThan(0, $result);
        
        $result = add_expense(1, 'Meals_Lunch', 25.00, 'Business lunch');
        $this->assertGreaterThan(0, $result);
        
        $result = add_expense(1, 'Meals_Dinner', 45.00, 'Client dinner');
        $this->assertGreaterThan(0, $result);
    }
    
    public function testGetTravelExpenses(): void
    {
        $result = get_travel_expenses(1);
        
        $this->assertNotFalse($result);
    }
    
    public function testGetTravelExpensesByProject(): void
    {
        $result = get_travel_expenses(1, ['project_id' => 'PRJ-001']);
        
        $this->assertNotFalse($result);
    }
    
    public function testGetTravelExpensesByActivity(): void
    {
        $result = get_travel_expenses(1, ['activity_code' => 'BILLABLE']);
        
        $this->assertNotFalse($result);
    }
    
    public function testGetProjectTravelCosts(): void
    {
        $result = get_project_travel_costs('PRJ-001');
        
        $this->assertIsArray($result);
    }
    
    public function testGetProjectTravelCostsWithTask(): void
    {
        $result = get_project_travel_costs('PRJ-001', 'TSK-001');
        
        $this->assertIsArray($result);
    }
    
    public function testSetExpenseStatus(): void
    {
        $result = set_expense_status(1, 'Approved');
        
        $this->assertTrue($result);
    }
    
    public function testMarkExpenseReimbursed(): void
    {
        $result = mark_expense_reimbursed(1);
        
        $this->assertTrue($result);
    }
    
    public function testGetPerDiemRules(): void
    {
        $result = get_per_diem_rules();
        
        $this->assertNotFalse($result);
    }
    
    public function testGetActivityCodes(): void
    {
        $result = get_activity_codes();
        
        $this->assertNotFalse($result);
    }
    
    public function testGetSuppliers(): void
    {
        $result = get_suppliers();
        
        $this->assertNotFalse($result);
    }
    
    public function testGetSuppliersByType(): void
    {
        $result = get_suppliers('Hotel');
        
        $this->assertNotFalse($result);
    }
}