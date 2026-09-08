# Test Plan - ksf_TravelExpense

## Overview

This document outlines the test strategy, test types, test cases, and acceptance criteria for the Travel and Expense Management module.

---

## 1. Test Strategy

### 1.1 Test Objectives

- Verify all functional requirements are met
- Ensure data integrity and consistency
- Validate integration with FA core
- Confirm security controls work correctly
- Achieve code quality standards

### 1.2 Test Levels

| Level | Description | Coverage Target |
|-------|-------------|-----------------|
| Unit Testing | Individual function/method testing | Core business logic |
| Integration Testing | Module integration with FA | All integrations |
| System Testing | End-to-end workflows | Critical paths |
| User Acceptance Testing | Business user validation | All use cases |

### 1.3 Test Types

| Type | Description |
|------|-------------|
| Functional Testing | Feature verification |
| Regression Testing | Existing functionality |
| Security Testing | Permission and access |
| Performance Testing | Response times |
| UI/UX Testing | User interface |

---

## 2. Test Environment

### 2.1 Environment Requirements

- FrontAccounting 2.4.0+ installed
- PHP 8.0+
- MySQL 5.7+
- Web browser (Chrome/Firefox/Edge)
- Sample data loaded

### 2.2 Test Data

**Required Test Data**:
- At least 5 sample suppliers (different service types)
- At least 3 sample employees
- At least 5 sample trips (different statuses)
- At least 10 sample expenses (different categories)
- Per diem rules for at least 3 cities
- Sample GL codes configured

---

## 3. Test Cases

### 3.1 Supplier Management Tests

#### TC-SUP-001: Create Supplier

| Field | Value |
|-------|-------|
| Test ID | TC-SUP-001 |
| Description | Create a new supplier with all fields |
| Preconditions | User has TE_MANAGE_SUPPLIERS permission |
| Steps | 1. Navigate to Suppliers page |
| | 2. Click "New Supplier" |
| | 3. Fill required fields |
| | 4. Save |
| Expected Result | Supplier saved to database |
| Pass Criteria | Supplier visible in list |

#### TC-SUP-002: View Supplier List

| Field | Value |
|-------|-------|
| Test ID | TC-SUP-002 |
| Description | View list of all suppliers |
| Preconditions | User has TE_VIEW_SUPPLIERS permission |
| Steps | 1. Navigate to Suppliers page |
| Expected Result | Suppliers displayed in table |
| Pass Criteria | All columns display correctly |

#### TC-SUP-003: Filter Suppliers by Type

| Field | Value |
|-------|-------|
| Test ID | TC-SUP-003 |
| Description | Filter suppliers by service type |
| Preconditions | Suppliers exist with different types |
| Steps | 1. Navigate to Suppliers page |
| | 2. Click service type filter |
| Expected Result | Only suppliers of selected type shown |
| Pass Criteria | Correct filtering |

#### TC-SUP-004: Edit Supplier

| Field | Value |
|-------|-------|
| Test ID | TC-SUP-004 |
| Description | Modify existing supplier |
| Preconditions | Supplier exists |
| Steps | 1. Edit supplier |
| | 2. Modify fields |
| | 3. Save |
| Expected Result | Supplier updated |
| Pass Criteria | Changes reflected in list |

#### TC-SUP-005: Delete Supplier (Soft Delete)

| Field | Value |
|-------|-------|
| Test ID | TC-SUP-005 |
| Description | Soft-delete supplier |
| Preconditions | Supplier exists |
| Steps | 1. Navigate to supplier edit |
| | 2. Click Delete/Inactivate |
| Expected Result | Supplier marked inactive |
| Pass Criteria | Supplier no longer active in list |

---

### 3.2 Trip Management Tests

#### TC-TRP-001: Create Trip

| Field | Value |
|-------|-------|
| Test ID | TC-TRP-001 |
| Description | Create a new trip request |
| Preconditions | User has TE_MANAGE_TRIPS permission |
| Steps | 1. Navigate to Trips page |
| | 2. Click "New Trip" |
| | 3. Fill required fields |
| | 4. Save |
| Expected Result | Trip saved to database |
| Pass Criteria | Trip visible in list |

#### TC-TRP-002: Auto-Per Diem Lookup

| Field | Value |
|-------|-------|
| Test ID | TC-TRP-002 |
| Description | Verify per-diem rate auto-populates |
| Preconditions | Per diem rule exists for city |
| Steps | 1. Create new trip |
| | 2. Enter destination city |
| Expected Result | Per diem rate populated |
| Pass Criteria | Rate from per diem rules |

#### TC-TRP-003: View Trip List

| Field | Value |
|-------|-------|
| Test ID | TC-TRP-003 |
| Description | View list of trips |
| Preconditions | User has TE_VIEW_TRIPS permission |
| Steps | 1. Navigate to Trips page |
| Expected Result | Trips displayed |
| Pass Criteria | All columns correct |

#### TC-TRP-004: Filter Trips by Status

| Field | Value |
|-------|-------|
| Test ID | TC-TRP-004 |
| Description | Filter trips by status |
| Preconditions | Trips exist with different statuses |
| Steps | 1. Navigate to Trips page |
| | 2. Click status filter |
| Expected Result | Only trips with selected status shown |
| Pass Criteria | Correct filtering |

#### TC-TRP-005: Trip Status Transition

| Field | Value |
|-------|-------|
| Test ID | TC-TRP-005 |
| Description | Transition trip status |
| Preconditions | Trip exists with status "Planned" |
| Steps | 1. Edit trip |
| | 2. Change status to "Approved" |
| | 3. Save |
| Expected Result | Status changed |
| Pass Criteria | New status displayed |

#### TC-TRP-006: Calculate Trip Days

| Field | Value |
|-------|-------|
| Test ID | TC-TRP-006 |
| Description | Verify trip duration calculation |
| Preconditions | Trip with start/end dates |
| Steps | 1. View trip details |
| Expected Result | Duration calculated correctly |
| Pass Criteria | Days = end_date - start_date + 1 |

---

### 3.3 Expense Entry Tests

#### TC-EXP-001: Create Expense

| Field | Value |
|-------|-------|
| Test ID | TC-EXP-001 |
| Description | Create expense line item |
| Preconditions | Trip exists, user has TE_MANAGE_EXPENSES |
| Steps | 1. Navigate to trip |
| | 2. Click "Add Expense" |
| | 3. Fill required fields |
| | 4. Save |
| Expected Result | Expense saved |
| Pass Criteria | Expense in list |

#### TC-EXP-002: GL Code Auto-Populate

| Field | Value |
|-------|-------|
| Test ID | TC-EXP-002 |
| Description | Verify GL code defaults based on category |
| Preconditions | Default GL codes configured |
| Steps | 1. Create expense |
| | 2. Select category "Hotel" |
| Expected Result | GL code defaults to "HOTEL" |
| Pass Criteria | GL code populated |

#### TC-EXP-003: View Expense List

| Field | Value |
|-------|-------|
| Test ID | TC-EXP-003 |
| Description | View expenses for a trip |
| Preconditions | Expenses exist for trip |
| Steps | 1. Navigate to trip |
| | 2. View expenses |
| Expected Result | Expenses displayed in table |
| Pass Criteria | All columns correct |

#### TC-EXP-004: Expense Category Totals

| Field | Value |
|-------|-------|
| Test ID | TC-EXP-004 |
| Description | Verify category subtotals |
| Preconditions | Multiple expenses in different categories |
| Steps | 1. View expense list |
| Expected Result | Subtotals calculated by category |
| Pass Criteria | Totals match sum |

#### TC-EXP-005: Receipt Upload

| Field | Value |
|-------|-------|
| Test ID | TC-EXP-005 |
| Description | Upload receipt for expense |
| Preconditions | Image file available |
| Steps | 1. Create expense |
| | 2. Upload receipt file |
| | 3. Save |
| Expected Result | Receipt uploaded and linked |
| Pass Criteria | File stored, link displayed |

#### TC-EXP-006: Edit Expense

| Field | Value |
|-------|-------|
| Test ID | TC-EXP-006 |
| Description | Modify expense |
| Preconditions | Expense exists |
| Steps | 1. Edit expense |
| | 2. Modify amount |
| | 3. Save |
| Expected Result | Expense updated |
| Pass Criteria | Changes saved |

#### TC-EXP-007: Delete Expense

| Field | Value |
|-------|-------|
| Test ID | TC-EXP-007 |
| Description | Delete expense |
| Preconditions | Expense exists |
| Steps | 1. Delete expense |
| | 2. Confirm |
| Expected Result | Expense removed |
| Pass Criteria | Expense not in list |

---

### 3.4 Per Diem Tests

#### TC-PD-001: Per Diem Calculation

| Field | Value |
|-------|-------|
| Test ID | TC-PD-001 |
| Description | Calculate per diem amount |
| Preconditions | Trip with per diem rule |
| Steps | 1. Calculate per diem for trip |
| Expected Result | Amount calculated correctly |
| Pass Criteria | Total matches rate × days × percentages |

#### TC-PD-002: First/Last Day Percentages

| Field | Value |
|-------|-------|
| Test ID | TC-PD-002 |
| Description | Verify first/last day percentages |
| Preconditions | Per diem rule with custom percentages |
| Steps | 1. Calculate per diem |
| Expected Result | First/last days use specified percentages |
| Pass Criteria | Applied correctly |

#### TC-PD-003: Per Diem Excess

| Field | Value |
|-------|-------|
| Test ID | TC-PD-003 |
| Description | Calculate per diem excess |
| Preconditions | Trip with per diem and meal expenses |
| Steps | 1. Calculate excess |
| Expected Result | Excess calculated |
| Pass Criteria | excess = per_diem - meal_expenses |

---

### 3.5 Expense Report Tests

#### TC-RPT-001: Create Expense Report

| Field | Value |
|-------|-------|
| Test ID | TC-RPT-001 |
| Description | Create expense report from trip |
| Preconditions | Trip with expenses |
| Steps | 1. Create report for trip |
| Expected Result | Report created with all expenses |
| Pass Criteria | All expenses included |

#### TC-RPT-002: Submit Expense Report

| Field | Value |
|-------|-------|
| Test ID | TC-RPT-002 |
| Description | Submit report for approval |
| Preconditions | Report exists |
| Steps | 1. Submit report |
| Expected Result | Status changes to Submitted |
| Pass Criteria | Status updated |

#### TC-RPT-003: Approve Expense Report

| Field | Value |
|-------|-------|
| Test ID | TC-RPT-003 |
| Description | Manager approves report |
| Preconditions | Report submitted |
| Steps | 1. Manager reviews |
| | 2. Click Approve |
| Expected Result | Report approved |
| Pass Criteria | Status = Approved |

#### TC-RPT-004: Reject Expense Report

| Field | Value |
|-------|-------|
| Test ID | TC-RPT-004 |
| Description | Manager rejects report |
| Preconditions | Report submitted |
| Steps | 1. Manager reviews |
| | 2. Click Reject with reason |
| Expected Result | Report rejected |
| Pass Criteria | Status = Rejected, reason stored |

---

### 3.6 Dashboard Tests

#### TC-DB-001: Dashboard Statistics

| Field | Value |
|-------|-------|
| Test ID | TC-DB-001 |
| Description | Verify dashboard shows correct statistics |
| Preconditions | Data exists |
| Steps | 1. Navigate to Dashboard |
| Expected Result | Statistics displayed |
| Pass Criteria | Counts accurate |

#### TC-DB-002: Recent Trips

| Field | Value |
|-------|-------|
| Test ID | TC-DB-002 |
| Description | Verify recent trips displayed |
| Preconditions | Trips exist |
| Steps | 1. Navigate to Dashboard |
| Expected Result | Recent trips listed |
| Pass Criteria | Last 5 trips shown |

---

### 3.7 Security Tests

#### TC-SC-001: View Suppliers Permission

| Field | Value |
|-------|-------|
| Test ID | TC-SC-001 |
| Description | User without permission cannot view suppliers |
| Preconditions | User lacks TE_VIEW_SUPPLIERS |
| Steps | 1. User attempts to access Suppliers page |
| Expected Result | Access denied |
| Pass Criteria | Error message shown |

#### TC-SC-002: Edit Expenses Permission

| Field | Value |
|-------|-------|
| Test ID | TC-SC-002 |
| Description | User without permission cannot edit expenses |
| Preconditions | User lacks TE_MANAGE_EXPENSES |
| Steps | 1. User attempts to create expense |
| Expected Result | Access denied |
| Pass Criteria | Error message shown |

---

### 3.8 Integration Tests

#### TC-INT-001: Employee Integration

| Field | Value |
|-------|-------|
| Test ID | TC-INT-001 |
| Description | Employee dropdown from FA employees |
| Preconditions | Employees exist in FA |
| Steps | 1. Create trip |
| | 2. View employee dropdown |
| Expected Result | Employees from FA displayed |
| Pass Criteria | Names populated |

#### TC-INT-002: GL Code Integration

| Field | Value |
|-------|-------|
| Test ID | TC-INT-002 |
| Description | GL codes from chart of accounts |
| Preconditions | GL codes configured in FA |
| Steps | 1. View GL code dropdown |
| Expected Result | GL codes from FA |
| Pass Codes | Codes from chart_master |

#### TC-INT-003: Project Integration

| Field | Value |
|-------|-------|
| Test ID | TC-INT-003 |
| Description | Projects from ksf_FA_ProjectManagement |
| Preconditions | Projects exist |
| Steps | 1. View project dropdown |
| Expected Result | Projects populated |
| Pass Criteria | Can select project |

---

## 4. Test Execution

### 4.1 Execution Order

1. Unit tests (via phpunit)
2. Integration tests
3. System tests
4. UAT

### 4.2 Test Results Template

| Test ID | Test Name | Status | Notes |
|---------|-----------|--------|-------|
| TC-SUP-001 | Create Supplier | PASS/FAIL | |
| TC-TRP-001 | Create Trip | PASS/FAIL | |

### 4.3 Defect Reporting

| Field | Description |
|----|-------------|
| Defect ID | Unique identifier |
| Test ID | Related test case |
| Severity | Critical/Major/Minor |
| Description | Detailed description |
| Steps to Reproduce | Reproduction steps |
| Expected Result | What should happen |
| Actual Result | What actually happened |

---

## 5. Acceptance Criteria

### 5.1 Functional Acceptance

| Requirement ID | Description | Test Coverage |
|----------------|-------------|---------------|
| FR-1.1 | Create Supplier | TC-SUP-001 |
| FR-1.2 | View Suppliers | TC-SUP-002 |
| FR-2.1 | Create Trip | TC-TRP-001 |
| FR-2.2 | View Trips | TC-TRP-003 |
| FR-3.1 | Create Expense | TC-EXP-001 |
| FR-3.2 | View Expenses | TC-EXP-003 |
| FR-4.1 | Per Diem Calculation | TC-PD-001 |
| FR-5.1 | Create Expense Report | TC-RPT-001 |
| FR-6.1 | Dashboard Statistics | TC-DB-001 |

### 5.2 Non-Functional Acceptance

| Criteria | Target |
|----------|--------|
| Page Load Time | < 3 seconds |
| Database Queries | < 10 per page |
| Browser Compatibility | Chrome, Firefox, Edge |
| Access Control | All permissions enforced |
| Data Validation | All inputs validated |

---

## 6. Test Deliverables

| Deliverable | Description |
|-------------|-------------|
| Test Cases | This document |
| Test Data | Sample data for testing |
| Test Results | Execution results log |
| Defect Log | Issues found during testing |
| Test Summary | Final pass/fail report |

---

## 7. Test Schedule

| Phase | Duration | Activities |
|-------|----------|-----------|
| Unit Testing | 1 day | phpunit execution |
| Integration Testing | 2 days | Integration tests |
| System Testing | 3 days | End-to-end workflows |
| UAT | 5 days | User acceptance |
| Bug Fixing | Ongoing | Fix and retest |

---

## 8. Risk Management

### 8.1 Test Risks

| Risk | Mitigation |
|------|-------------|
| Test data not available | Create sample data first |
| Environment issues | Use isolated test environment |
| Scope creep | Track changes to requirements |

---
