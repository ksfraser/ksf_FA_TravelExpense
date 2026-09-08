# UT-EXPENSE-001-001-001 - Expense Report Entry

## Unit Test

**Module**: TravelExpense
**BR**: BR-EXPENSE-001
**FR**: FR-EXPENSE-001-001
**Status**: Proposed

### Test Case: Create Expense Report

**Setup:**
```php
$expenseReport = new ExpenseReport([
    'employee_id' => 5,
    'project_id' => 1,
    'currency' => 'USD',
]);
```

**Action:**
```php
$reportId = $expenseReport->save();
```

**Assert:**
```php
$this->assertNotNull($reportId);
$this->assertEquals('draft', $expenseReport->getStatus());
```

### Test Case: Add Expense Line with Project/Activity

**Setup:**
```php
$line = new ExpenseLine([
    'expense_report_id' => $reportId,
    'expense_date' => '2026-09-05',
    'category' => 'hotel',
    'amount' => 200.00,
    'gl_code' => 'EXP-HOTEL',
    'project_id' => 1,
    'project_activity_id' => 5,
    'is_billable' => true,
]);
```

**Assert:**
```php
$this->assertEquals(200.00, $line->getAmount());
$this->assertEquals(1, $line->getProjectId());
```

### Test Case: Apply Cost Plus Billing Rule

**Setup:**
```php
// Mock contract with cost_plus 15%
$project = new Project(['id' => 1]);
$project->setContractId(1);

$line = new ExpenseLine(['amount' => 100.00]);

// Apply billing rule via hook
$result = hook_invoke_first('expense_get_billing_rule', [
    'project_id' => 1,
    'activity_id' => 5,
    'expense_category' => 'hotel',
]);

$line->applyBillingRule($result['rule'], $result['margin']);
```

**Assert:**
```php
$this->assertEquals('cost_plus', $line->getBillingRule());
$this->assertEquals(0.15, $line->getBillingMargin());
$this->assertEquals(115.00, $line->getBillableAmount()); // 100 * 1.15
```

### Test Case: Adjustment Calculation

```php
// +amount
$this->assertEquals(125.00, calculateAdjustment(100.00, '+25.00'));

// -amount
$this->assertEquals(90.00, calculateAdjustment(100.00, '-10.00'));

// +%
$this->assertEquals(115.00, calculateAdjustment(100.00, '+15%'));

// -%
$this->assertEquals(90.00, calculateAdjustment(100.00, '-10%'));

// =amount
$this->assertEquals(150.00, calculateAdjustment(100.00, '=150.00'));
```

### Test Case: Multi-Currency Conversion

**Setup:**
```php
$line = new ExpenseLine([
    'amount' => 100.00,
    'currency' => 'EUR',
    'exchange_rate' => 1.10, // EUR to USD
    'amount_base' => 110.00,
]);
```

**Assert:**
```php
$this->assertEquals(100.00, $line->getAmount());
$this->assertEquals(110.00, $line->getAmountBase());
```
