# UAT Plan - ksf_TravelExpense

## Overview

This document defines the User Acceptance Test (UAT) cases for the Travel and Expense Management module. UAT validates that the system meets business requirements and is ready for production deployment.

---

## 1. UAT Objectives

### 1.1 Goals

- Validate business workflows function correctly
- Confirm user requirements are met
- Ensure integration with FA works seamlessly
- Verify data accuracy and integrity
- Obtain sign-off for production deployment

### 1.2 Success Criteria

- All critical test cases pass
- No high-severity defects open
- User acceptance obtained
- Sign-off documented

---

## 2. UAT Scope

### 2.1 In Scope

- Supplier CRUD operations
- Trip lifecycle management
- Expense entry and categorization
- Per-diem calculations
- Expense report workflow
- GL integration
- Receipt management
- Dashboard and reporting
- FA integrations (Employee, GL, Project Management)

### 2.2 Out of Scope

- Performance stress testing
- Security penetration testing
- Browser compatibility (covered in QA)
- Data migration from legacy systems

---

## 3. UAT User Roles

| Role | Description | Tests Executed |
|------|-------------|----------------|
| Employee | Creates trips, enters expenses, submits reports | SUP, TRP, EXP, RPT |
| Manager | Approves trips and expense reports | APPR |
| Finance | Processes GL entries, reconciles | GL |
| Administrator | System configuration | ADMIN |

---

## 4. UAT Test Cases

### 4.1 Supplier Management (SUP)

#### UAT-SUP-001: Create Supplier

| Field | Value |
|-------|-------|
| Test Case ID | UAT-SUP-001 |
| Scenario | Add new travel supplier |
| Preconditions | User has TE_MANAGE_SUPPLIERS |
| Test Steps | 1. Navigate to Suppliers |
| | 2. Click "New Supplier" |
| | 3. Enter: Name="Enterprise Rent-A-Car", Type="Car Rental", Preference=1 |
| | 4. Save |
| Expected Result | Success message, supplier in list |
| Acceptance Criteria | [ ] Supplier saved to database |
| | [ ] Visible in list |
| | [ ] Activity logged |
| Result | PASS/FAIL |
| Notes |

#### UAT-SUP-002: Filter Suppliers by Type

| Field | Value |
|-------|-------|
| Test Case ID | UAT-SUP-002 |
| Scenario | Filter to view only hotels |
| Preconditions | Suppliers of multiple types exist |
| Test Steps | 1. Navigate to Suppliers |
| | 2. Filter by "Hotel" |
| Expected Result | Only hotels displayed |
| Acceptance Criteria | [ ] Only Hotel type shown |
| | [ ] Count correct |
| Result | PASS/FAIL |
| Notes |

#### UAT-SUP-003: Set Corporate Rate Flag

| Field | Value |
|-------|-------|
| Test Case ID | UAT-SUP-003 |
| Scenario | Mark supplier as having corporate rates |
| Preconditions | Supplier exists |
| Test Steps | 1. Edit supplier |
| | 2. Enable "Corporate Rate" |
| | 3. Enter rate code |
| | 4. Save |
| Expected Result | Corporate rate flag set |
| Acceptance Criteria | [ ] Flag displayed |
| | [ ] Rate code stored |
| Result | PASS/FAIL |
| Notes |

---

### 4.2 Trip Management (TRP)

#### UAT-TRP-001: Create Business Trip

| Field | Value |
|-------|-------|
| Test Case ID | UAT-TRP-001 |
| Scenario | Create trip request for business travel |
| Preconditions | User has TE_MANAGE_TRIPS |
| Test Steps | 1. Navigate to Trips |
| | 2. Click "New Trip" |
| | 3. Enter: Trip ID="TRIP-001", Name="NYC Sales Conference" |
| | 4. Select Employee |
| | 5. Enter Destination="New York", Country="USA" |
| | 6. Enter Start Date, End Date |
| | 7. Save |
| Expected Result | Trip created |
| Acceptance Criteria | [ ] Trip saved |
| | [ ] Per diem rate auto-populated |
| | [ ] Status = Planned |
| Result | PASS/FAIL |
| Notes |

#### UAT-TRP-002: Trip Approval Workflow

| Field | Value |
|-------|-------|
| Test Case ID | UAT-TRP-002 |
| Scenario | Manager approves trip |
| Preconditions | Trip exists with status "Planned" |
| Test Steps | 1. Manager views trip |
| | 2. Click Approve |
| Expected Result | Status changes to Approved |
| Acceptance Criteria | [ ] Status = Approved |
| | [ ] Approval logged |
| Result | PASS/FAIL |
| Notes |

#### UAT-TRP-003: Start Trip

| Field | Value |
|-------|-------|
| Test Case ID | UAT-TRP-003 |
| Scenario | Employee starts business trip |
| Preconditions | Trip approved |
| Test Steps | 1. Employee edits trip |
| | 2. Changes status to "In Progress" |
| | 3. Save |
| Expected Result | Trip in progress |
| Acceptance Criteria | [ ] Status = In Progress |
| Result | PASS/FAIL |
| Notes |

#### UAT-TRP-004: Complete Trip

| Field | Value |
|-------|-------|
| Test Case ID | UAT-TRP-004 |
| Scenario | Complete business trip |
| Preconditions | Trip in progress |
| Test Steps | 1. Edit trip |
| | 2. Change status to "Complete" |
| | 3. Save |
| Expected Result | Trip completed |
| Acceptance Criteria | [ ] Status = Complete |
| | [ ] Trip totals accurate |
| Result | PASS/FAIL |
| Notes |

#### UAT-TRP-005: Reject Trip

| Field | Value |
|-------|-------|
| Test Case ID | UAT-TRP-005 |
| Scenario | Manager rejects不合规trip request |
| Preconditions | Trip exists |
| Test Steps | 1. Manager views trip |
| | 2. Click Reject with reason |
| Expected Result | Trip rejected |
| Acceptance Criteria | [ ] Status = Rejected |
| | [ ] Reason stored |
| Result | PASS/FAIL |
| Notes |

---

### 4.3 Expense Entry (EXP)

#### UAT-EXP-001: Add Expense to Trip

| Field | Value |
|-------|-------|
| Test Case ID | UAT-EXP-001 |
| Scenario | Enter hotel expense |
| Preconditions | Trip exists |
| Test Steps | 1. Navigate to Trip |
| | 2. Click "Add Expense" |
| | 3. Enter: Date, Category="Hotel", Amount=150.00 |
| | 4. GL code auto-selected |
| | 5. Save |
| Expected Result | Expense added |
| Acceptance Criteria | [ ] Expense saved |
| | [ ] Linked to trip |
| | [ ] GL code correct |
| Result | PASS/FAIL |
| Notes |

#### UAT-EXP-002: Add Meal Expense

| Field | Value |
|-------|-------|
| Test Case ID | UAT-EXP-002 |
| Scenario | Enter dinner expense |
| Preconditions | Trip exists |
| Test Steps | 1. Add expense |
| | 2. Category = Meals - Dinner |
| | 3. Amount = 75.00 |
| | 4. Save |
| Expected Result | Meal expense added |
| Acceptance Criteria | [ ] GL code defaults to MEAL-DINNER |
| | [ ] Amount correct |
| Result | PASS/FAIL |
| Notes |

#### UAT-EXP-003: Upload Receipt

| Field | Value |
|-------|-------|
| Test Case ID | UAT-EXP-003 |
| Description | Upload receipt image for expense |
| Preconditions | Trip exists, image file ready |
| Test Steps | 1. Add expense |
| | 2. Attach receipt file |
| | 3. Save |
| Expected Result | Receipt uploaded |
| Acceptance Criteria | [ ] File stored |
| | [ ] Link to expense |
| Result | PASS/FAIL |
| Notes |

#### UAT-EXP-004: Link Expense to Project

| Field | Value |
|-------|-------|
| Test Case ID | UAT-EXP-004 |
| Scenario | Bill expense to project |
| Preconditions | Project exists |
| Test Steps | 1. Add expense |
| | 2. Select Project from dropdown |
| | 3. Save |
| Expected Result | Expense linked |
| Acceptance Criteria | [ ] Project stored |
| | [ ] Shows in details |
| Result | PASS/FAIL |
| Notes |

#### UAT-EXP-005: Edit Pending Expense

| Field | Value |
|-------|-------|
| Test Case ID | UAT-EXP-005 |
| Scenario | Modify pending expense |
| Preconditions | Pending expense exists |
| Test Steps | 1. Edit expense |
| | 2. Change amount |
| | 3. Save |
| Expected Result | Expense updated |
| Acceptance Criteria | [ ] New amount saved |
| | [ ] Activity logged |
| Result | PASS/FAIL |
| Notes |

---

### 4.4 Per Diem (PD)

#### UAT-PD-001: Calculate Per Diem

| Field | Value |
|-------|-------|
| Test Case ID | UAT-PD-001 |
| Scenario | Calculate per diem for trip |
| Preconditions | Per diem rule exists for destination |
| Test Steps | 1. View trip |
| | 2. Click "Calculate Per Diem" |
| Expected Result | Per diem calculated |
| Acceptance Criteria | [ ] Amount correct |
| | [ ] Days counted correctly |
| Result | PASS/FAIL |
| Notes |

#### UAT-PD-002: First Day Percentage

| Field | Value |
|-------|-------|
| Test Case ID | UAT-PD-002 |
| Scenario | Verify first day percentage applied |
| Preconditions | Per diem rule with 75% first day |
| Test Steps | 1. Create 3-day trip |
| | 2. Calculate per diem |
| Expected Result | First day at 75% |
| Acceptance Criteria | [ ] First day = 75% × rate |
| Result | PASS/FAIL |
| Notes |

#### UAT-PD-003: Per Diem Excess

| Field | Value |
|-------|-------|
| Test Case ID | UAT-PD-003 |
| Scenario | Calculate excess to return |
| Preconditions | Trip with per diem, meal expenses > per diem |
| Test Steps | 1. Add meal expenses totaling $300 |
| | 2. Per diem is $225 (75×3 days) |
| | 3. Calculate excess |
| Expected Result | Excess = $75 |
| Acceptance Criteria | [ ] Excess = Meal expenses - Per diem |
| Result | PASS/FAIL |
| Notes |

---

### 4.5 Expense Report (RPT)

#### UAT-RPT-001: Create Expense Report

| Field | Value |
|-------|-------|
| Test Case ID | UAT-RPT-001 |
| Scenario | Create expense report |
| Preconditions | Trip with expenses |
| Test Steps | 1. Navigate to Trip |
| | 2. Click "Create Expense Report" |
| Expected Result | Report created |
| Acceptance Criteria | [ ] Report with all expenses |
| | [ ] Totals calculated |
| Result | PASS/FAIL |
| Notes |

#### UAT-RPT-002: Submit Expense Report

| Field | Value |
|-------|-------|
| Test Case ID | UAT-RPT-002 |
| Scenario | Submit report for approval |
| Preconditions | Report exists |
| Test Steps | 1. View report |
| | 2. Click Submit |
| Expected Result | Report submitted |
| Acceptance Criteria | [ ] Status = Submitted |
| | [ ] All expenses status = Submitted |
| | [ ] Manager notified |
| Result | PASS/FAIL |
| Notes |

#### UAT-RPT-003: Approve Expense Report

| Field | Value |
|-------|-------|
| Test Case ID | UAT-RPT-003 |
| Scenario | Manager approves report |
| Preconditions | Report submitted |
| Test Steps | 1. Manager views report |
| | 2. Reviews all expenses |
| | 3. Click Approve |
| Expected Result | Report approved |
| Acceptance Criteria | [ ] Status = Approved |
| | [ ] Approval logged |
| Result | PASS/FAIL |
| Notes |

#### UAT-RPT-004: Reject Expense Report

| Field | Value |
|-------|-------|
| Test Case ID | UAT-RPT-004 |
| Scenario | Manager rejects report |
| Preconditions | Report submitted |
| Test Steps | 1. Manager reviews |
| | 2. Click Reject |
| | 3. Enter reason: "Missing receipt for $50 expense" |
| Expected Result | Report rejected |
| Acceptance Criteria | [ ] Status = Rejected |
| | [ ] Reason stored |
| Result | PASS/FAIL |
| Notes |

#### UAT-RPT-005: Process GL Entries

| Field | Value |
|-------|-------|
| Test Case ID | UAT-RPT-005 |
| Scenario | Post approved expenses to GL |
| Preconditions | Report approved |
| Test Steps | 1. Finance views report |
| | 2. Click "Post to GL" |
| Expected Result | GL entries created |
| Acceptance Criteria | [ ] GL entries in FA |
| | [ ] Expenses marked Posted |
| Result | PASS/FAIL |
| Notes |

---

### 4.6 Dashboard (DB)

#### UAT-DB-001: View Travel Dashboard

| Field | Value |
|-------|-------|
| Test Case ID | UAT-DB-001 |
| Scenario | View main dashboard |
| Preconditions | Test data exists |
| Test Steps | 1. Navigate to Dashboard |
| Expected Result | Statistics displayed |
| Acceptance Criteria | [ ] Pending trips count |
| | [ ] Pending reports count |
| | [ ] Total amounts |
| Result | PASS/FAIL |
| Notes |

#### UAT-DB-002: View Recent Activity

| Field | Value |
|-------|-------|
| Test Case ID | UAT-DB-002 |
| Scenario | Verify activity log |
| Preconditions | Activities performed |
| Test Steps | 1. Navigate to Dashboard |
| | 2. View Recent Activities |
| Expected Result | Activities listed |
| Acceptance Criteria | [ ] Last 5-10 activities |
| | [ ] Chronological order |
| Result | PASS/FAIL |
| Notes |

---

### 4.7 Reports (RP)

#### UAT-RP-001: Expense Summary Report

| Field | Value |
|-------|-------|
| Test Case ID | UAT-RP-001 |
| Scenario | Generate expense summary |
| Preconditions | Expenses exist |
| Test Steps | 1. Navigate to Reports |
| | 2. Select Expense Summary |
| | 3. Set date range |
| Expected Result | Report generated |
| Acceptance Criteria | [ ] By category |
| | [ ] By employee |
| | [ ] Totals correct |
| Result | PASS/FAIL |
| Notes |

#### UAT-RP-002: Per Diem Report

| Field | Value |
|-------|-------|
| Test Case ID | UAT-RP-002 |
| Scenario | Per diem summary |
| Preconditions | Per diem expenses exist |
| Test Steps | 1. Navigate to Reports |
| | 2. Select Per Diem Report |
| Expected Result | Report generated |
| Acceptance Criteria | [ ] Per diem by trip |
| | [ ] Excess shown |
| Result | PASS/FAIL |
| Notes |

---

### 4.8 Security (SC)

#### UAT-SC-001: View Permission

| Field | Value |
|-------|-------|
| Test Case ID | UAT-SC-001 |
| Scenario | Deny access without permission |
| Preconditions | User lacks TE_VIEW_TRIPS |
| Test Steps | 1. User accesses Trips page |
| Expected Result | Access denied |
| Acceptance Criteria | [ ] Error message |
| | [ ] No data shown |
| Result | PASS/FAIL |
| Notes |

#### UAT-SC-002: Edit Permission

| Field | Value |
|-------|-------|
| Test Case ID | UAT-SC-002 |
| Scenario | Deny edit without permission |
| Preconditions | User lacks TE_MANAGE_EXPENSES |
| Test Steps | 1. User attempts to create expense |
| Expected Result | Access denied |
| Acceptance Criteria | [ ] Error message |
| | [ ] Expense not created |
| Result | PASS/FAIL |
| Notes |

---

### 4.9 Integration (INT)

#### UAT-INT-001: Employee Dropdown

| Field | Value |
|-------|-------|
| Test Case ID | UAT-INT-001 |
| Scenario | Verify employees from FA |
| Preconditions | Employees exist in FA |
| Test Steps | 1. Create trip |
| | 2. View employee dropdown |
| Expected Result | Employees from FA displayed |
| Acceptance Criteria | [ ] Names shown |
| | [ ] Can select employee |
| Result | PASS/FAIL |
| Notes |

#### UAT-INT-002: GL Codes

| Field | Value |
|-------|-------|
| Test Case ID | UAT-INT-002 |
| Scenario | GL codes from chart of accounts |
| Preconditions | GL codes exist |
| Test Steps | 1. Create expense |
| | 2. View GL code dropdown |
| Expected Result | GL codes from FA |
| Acceptance Criteria | [ ] Codes from chart_master |
| | [ ] Valid codes |
| Result | PASS/FAIL |
| Notes |

#### UAT-INT-003: Project Dropdown

| Field | Value |
|-------|-------|
| Test Case ID | UAT-INT-003 |
| Scenario | Projects from ksf_FA_ProjectManagement |
| Preconditions | Projects exist |
| Test Steps | 1. Create expense |
| | 2. View project dropdown |
| Expected Result | Projects populated |
| Acceptance Criteria | [ ] Can select project |
| | [ ] Project name displays |
| Result | PASS/FAIL |
| Notes |

---

## 5. UAT Execution

### 5.1 Execution Checklist

- [ ] All test cases reviewed
- [ ] Test environment ready
- [ ] Test data loaded
- [ ] Test users configured
- [ ] Test cases executed
- [ ] Results documented
- [ ] Defects logged

### 5.2 Sign-off

| Role | Name | Date | Signature |
|------|------|------|----------|
| Project Manager | | | |
| QA Lead | | | |
| Development Lead | | | |

---

## 6. Test Results Summary

### 6.1 Results Summary

| Category | Total | Passed | Failed | Pass Rate |
|----------|-------|--------|--------|----------|
| Supplier Management | 3 | | | |
| Trip Management | 5 | | | |
| Expense Entry | 5 | | | |
| Per Diem | 3 | | | |
| Expense Report | 5 | | | |
| Dashboard | 2 | | | |
| Reports | 2 | | | |
| Security | 2 | | | |
| Integration | 3 | | | |
| **TOTAL** | **30** | | | |

### 6.2 Defects Found

| Defect ID | Test Case | Severity | Description | Status |
|-----------|----------|----------|-------------|--------|
| | | | | |

---

## 7. UAT Completion

### 7.1 Completion Criteria

- [ ] All critical test cases pass
- [ ] No high-severity defects open
- [ ] All test data cleaned up
- [ ] Sign-off obtained

### 7.2 Final Sign-off

This module is approved for production deployment.

| Role | Name | Date | Signature |
|------|------|------|----------|
| Business Owner | | | |
| Project Manager | | | |
| QA Lead | | |
