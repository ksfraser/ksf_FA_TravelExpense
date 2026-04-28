# Functional Requirements - ksf_TravelExpense

## Overview

This document details the functional requirements for the Travel and Expense Management module (ksf_FA_TravelExpense), which provides supplier management, trip coordination, expense tracking, and per-diem processing functionality.

## Scope

The module handles:
- Preferred supplier management (non-inventory)
- Trip lifecycle (as mini-projects)
- Expense entry and categorization
- Per-diem calculation rules
- Expense report workflow
- GL integration for expense posting
- Receipt management
- Activity audit logging

---

## FR-1: Supplier Management

### FR-1.1: Create Supplier

**Description**: Users shall be able to create new suppliers with all required fields.

**Requirements**:
- FR-1.1.1: System shall accept supplier name and service type
- FR-1.1.2: System shall accept service types: Hotel, Car Rental, Taxi, Transit, Meal, Other
- FR-1.1.3: System shall accept optional: contact name, phone, email, website
- FR-1.1.4: System shall accept rate code for corporate rates
- FR-1.1.5: System shall accept preference order (1, 2, 3, etc.)
- FR-1.1.6: System shall accept corporate rate flag (yes/no)
- FR-1.1.7: System shall accept notes
- FR-1.1.8: System shall validate required fields are not empty
- FR-1.1.9: System shall generate unique supplier_id
- FR-1.1.10: System shall set default status to active

**Acceptance Criteria**:
- [ ] Supplier can be created with required fields
- [ ] Service type dropdown shows valid types
- [ ] Preference order stored correctly
- [ ] Corporate rate toggle works

### FR-1.2: View Suppliers

**Description**: Users shall be able to view supplier list and details.

**Requirements**:
- FR-1.2.1: System shall display supplier list with key fields
- FR-1.2.2: System shall support filtering by service type
- FR-1.2.3: System shall support filtering by preference order
- FR-1.2.4: System shall show preference order badge
- FR-1.2.5: System shall show corporate rate indicator
- FR-1.2.6: System shall support search by name
- FR-1.2.7: System shall sort by preference order by default

**Acceptance Criteria**:
- [ ] All suppliers listed with correct columns
- [ ] Service type filtering works
- [ ] Preference order displayed correctly
- [ ] Corporate rate badge shown

### FR-1.3: Edit Supplier

**Description**: Users shall be able to modify existing supplier details.

**Requirements**:
- FR-1.3.1: System shall pre-populate form with existing values
- FR-1.3.2: System shall validate required fields
- FR-1.3.3: System shall track old values before update
- FR-1.3.4: System shall generate activity log entry with changes
- FR-1.3.5: System shall update timestamp on modification

**Acceptance Criteria**:
- [ ] Form pre-fills with current values
- [ ] Changes saved to database
- [ ] Activity log shows what changed

### FR-1.4: Delete Supplier

**Description**: Users shall be able to delete suppliers.

**Requirements**:
- FR-1.4.1: System shall require confirmation before deletion
- FR-1.4.2: System shall prevent deletion if supplier linked to expenses
- FR-1.4.3: System shall allow soft-delete (inactive) option
- FR-1.4.4: System shall generate activity log entry

**Acceptance Criteria**:
- [ ] Confirmation dialog appears
- [ ] Soft-delete sets inactive flag
- [ ] Activity logged

---

## FR-2: Trip Management

### FR-2.1: Create Trip

**Description**: Users shall be able to create new trip requests.

**Requirements**:
- FR-2.1.1: System shall require trip ID, name, employee
- FR-2.1.2: System shall require destination city (for per-diem lookup)
- FR-2.1.3: System shall require start date and end date
- FR-2.1.4: System shall auto-lookup per-diem rate based on city
- FR-2.1.5: System shall accept description
- FR-2.1.6: System shall default status to "Planned"
- FR-2.1.7: System shall validate end date >= start date
- FR-2.1.8: System shall generate activity log entry
- FR-2.1.9: System shall create pre-approval task

**Acceptance Criteria**:
- [ ] Trip can be created with all required fields
- [ ] Per-diem rate auto-populated from city
- [ ] Activity logged

### FR-2.2: View Trips

**Description**: Users shall be able to view trip list and details.

**Requirements**:
- FR-2.2.1: System shall display trip list with key fields
- FR-2.2.2: System shall support filtering by status
- FR-2.2.3: System shall support filtering by employee
- FR-2.2.4: System shall display trip dates
- FR-2.2.5: System shall display total estimated vs actual
- FR-2.2.6: System shall show status with color coding
- FR-2.2.7: System shall support search by name

**Acceptance Criteria**:
- [ ] All trips listed with correct columns
- [ ] Status filter works
- [ ] Color coding reflects status

### FR-2.3: Edit Trip

**Description**: Users shall be able to modify existing trips.

**Requirements**:
- FR-2.3.1: System shall pre-populate form with existing values
- FR-2.3.2: System shall allow status changes through workflow
- FR-2.3.3: System shall validate date changes
- FR-2.3.4: System shall regenerate per-diem if city changes
- FR-2.3.5: System shall generate activity log entry

**Acceptance Criteria**:
- [ ] Form pre-fills with current values
- [ ] Per-diem recalculates on city change

### FR-2.4: Trip Workflow

**Description**: System shall support trip state transitions.

**Requirements**:
- FR-2.4.1: System shall allow Planned -> Approved transition
- FR-2.4.2: System shall allow Approved -> In Progress transition
- FR-2.4.3: System shall allow In Progress -> Complete transition
- FR-2.4.4: System shall allow Rejected from any state
- FR-2.4.5: System shall allow Cancelled from Planned/Approved
- FR-2.4.6: System shall create manager approval task

**Acceptance Criteria**:
- [ ] Status transitions work correctly
- [ ] Task created for manager approval

### FR-2.5: Delete Trip

**Description**: Users shall be able to delete trips.

**Requirements**:
- FR-2.5.1: System shall require confirmation before deletion
- FR-2.5.2: System shall prevent deletion of trips with submitted expenses
- FR-2.5.3: System shall cascade delete related expenses (if Draft)
- FR-2.5.4: System shall generate activity log entry

**Acceptance Criteria**:
- [ ] Confirmation dialog appears
- [ ] Deletion prevented if expenses exist

---

## FR-3: Expense Entry

### FR-3.1: Create Expense

**Description**: Users shall be able to create expense line items.

**Requirements**:
- FR-3.1.1: System shall require trip_id (linked to trip)
- FR-3.1.2: System shall require expense date
- FR-3.1.3: System shall require category
- FR-3.1.4: System shall require amount
- FR-3.1.5: System shall require GL code (or default based on category)
- FR-3.1.6: System shall accept optional project_id (billable to)
- FR-3.1.7: System shall accept optional task_id (billable to)
- FR-3.1.8: System shall accept optional vendor (supplier)
- FR-3.1.9: System shall accept optional description
- FR-3.1.10: System shall accept receipt upload
- FR-3.1.11: System shall default status to "Pending"

**Acceptance Criteria**:
- [ ] Expense can be created with all required fields
- [ ] Category selection populates GL code
- [ ] Receipt upload works

### FR-3.2: View Expenses

**Description**: Users shall be able to view expense list.

**Requirements**:
- FR-3.2.1: System shall display expense list for a trip
- FR-3.2.2: System shall show date, category, amount
- FR-3.2.3: System shall show status with color coding
- FR-3.2.4: System shall show linked receipt indicator
- FR-3.2.5: System shall calculate subtotals by category
- FR-3.2.6: System shall support filtering by status
- FR-3.2.7: System shall support sorting by date, category, amount

**Acceptance Criteria**:
- [ ] Expenses displayed in table format
- [ ] Subtotals calculated correctly
- [ ] Receipt link shown

### FR-3.3: Edit Expense

**Description**: Users shall be able to modify expenses.

**Requirements**:
- FR-3.3.1: System shall pre-populate form with existing values
- FR-3.3.2: System shall allow editing only in Draft/Pending status
- FR-3.3.3: System shall allow receipt replacement
- FR-3.3.4: System shall update trip totals on change
- FR-3.3.5: System shall generate activity log entry

**Acceptance Criteria**:
- [ ] Form pre-fills correctly
- [ ] Edit blocked if approved

### FR-3.4: Delete Expense

**Description**: Users shall be able to delete expenses.

**Requirements**:
- FR-3.4.1: System shall require confirmation before deletion
- FR-3.4.2: System shall prevent deletion of approved expenses
- FR-3.4.3: System shall delete linked receipt file
- FR-3.4.4: System shall update trip totals

**Acceptance Criteria**:
- [ ] Confirmation appears
- [ ] Approved expenses protected

---

## FR-4: Per Diem

### FR-4.1: Per Diem Calculation

**Description**: System shall calculate per-diem allowances.

**Requirements**:
- FR-4.1.1: System shall look up daily rate by city/country
- FR-4.1.2: System shall apply first-day percentage rule
- FR-4.1.3: System shall apply last-day percentage rule
- FR-4.1.4: System shall calculate total days
- FR-4.1.5: System shall calculate total allowance amount
- FR-4.1.6: System shall create expense entry for per-diem
- FR-4.1.7: System shall handle single-day trips

**Acceptance Criteria**:
- [ ] Daily rate retrieved correctly
- [ ] Percentages applied correctly
- [ ] Total calculation accurate

### FR-4.2: Per Diem Rules

**Description**: System shall manage per-diem rules.

**Requirements**:
- FR-4.2.1: System shall allow CRUD on per-diem rules
- FR-4.2.2: System shall require city, country, daily rate
- FR-4.2.3: System shall accept first-day percentage (default 75%)
- FR-4.2.4: System shall accept last-day percentage (default 75%)
- FR-4.2.5: System shall accept meal deduction percentages

**Acceptance Criteria**:
- [ ] Rules can be created/edited
- [ ] Default percentages applied

### FR-4.3: Per Diem Excess

**Description**: System shall track per-diem excess.

**Requirements**:
- FR-4.3.1: System shall compare per-diem to actual meal expenses
- FR-4.3.2: System shall calculate excess amount
- FR-4.3.3: System shall flag excess to be returned
- FR-4.3.4: System shall show excess in expense report

**Acceptance Criteria**:
- [ ] Excess calculated correctly
- [ ] Flag displayed in report

---

## FR-5: Expense Report

### FR-5.1: Create Expense Report

**Description**: System shall create expense report from trip expenses.

**Requirements**:
- FR-5.1.1: System shall create report header from trip
- FR-5.1.2: System shall include all expenses for trip
- FR-5.1.3: System shall calculate total expenses
- FR-5.1.4: System shall calculate total per-diem
- FR-5.1.5: System shall include per-diem excess info
- FR-5.1.6: System shall default status to "Draft"

**Acceptance Criteria**:
- [ ] Report created with correct totals
- [ ] All expenses included

### FR-5.2: Submit Expense Report

**Description**: Users shall be able to submit expense reports.

**Requirements**:
- FR-5.2.1: System shall require all expenses have receipts (configurable)
- FR-5.2.2: System shall validate required fields on expenses
- FR-5.2.3: System shall change status to "Submitted"
- FR-5.2.4: System shall set submitted_by and submitted_at
- FR-5.2.5: System shall update all expense statuses to "Submitted"
- FR-5.2.6: System shall notify manager

**Acceptance Criteria**:
- [ ] Report status changes to Submitted
- [ ] Manager notification sent

### FR-5.3: Approve Expense Report

**Description**: Managers shall be able to approve expense reports.

**Requirements**:
- FR-5.3.1: System shall allow manager to review all expenses
- FR-5.3.2: System shall allow approve action
- FR-5.3.3: System shall allow reject action with reason
- FR-5.3.4: System shall set approved_by and approved_at
- FR-5.3.5: System shall update expense statuses
- FR-5.3.6: System shall generate approval task

**Acceptance Criteria**:
- [ ] Approval changes status
- [ ] Rejection includes reason

### FR-5.4: Process GL Entries

**Description**: System shall post expenses to GL.

**Requirements**:
- FR-5.4.1: System shall create GL entries for approved expenses
- FR-5.4.2: System shall map category to GL code
- FR-5.4.3: System shall include project/task for billable expenses
- FR-5.4.4: System shall prevent double-posting
- FR-5.4.5: System shall mark expenses as "Posted"

**Acceptance Criteria**:
- [ ] GL entries created correctly
- [ ] No duplicates

---

## FR-6: Dashboard & Reporting

### FR-6.1: Dashboard Statistics

**Description**: System shall display travel expense dashboard.

**Requirements**:
- FR-6.1.1: System shall display pending trip count
- FR-6.1.2: System shall display pending expense report count
- FR-6.1.3: System shall display total pending approval amount
- FR-6.1.4: System shall display recent trips (last 5)
- FR-6.1.5: System shall display recent expenses (last 5)

**Acceptance Criteria**:
- [ ] All statistics display correctly
- [ ] Recent items show latest

### FR-6.2: Expense Summary Report

**Description**: System shall generate expense summary reports.

**Requirements**:
- FR-6.2.1: System shall summarize by category
- FR-6.2.2: System shall summarize by employee
- FR-6.2.3: System shall summarize by period
- FR-6.2.4: System shall support date range filtering

**Acceptance Criteria**:
- [ ] Category totals accurate
- [ ] Employee totals accurate

### FR-6.3: Per Diem Report

**Description**: System shall generate per-diem reports.

**Requirements**:
- FR-6.3.1: System shall list trips with per-diem
- FR-6.3.2: System shall compare per-diem to actual
- FR-6.3.3: System shall calculate excess amounts
- FR-6.3.4: System shall sum total excess

**Acceptance Criteria**:
- [ ] Calculations accurate
- [ ] Excess amounts correct

---

## FR-7: Receipt Management

### FR-7.1: Upload Receipt

**Description**: Users shall be able to upload receipt images.

**Requirements**:
- FR-7.1.1: System shall accept image/pdf files
- FR-7.1.2: System shall limit file size (configurable)
- FR-7.1.3: System shall generate unique filename
- FR-7.1.4: System shall store file path
- FR-7.1.5: System shall link to expense

**Acceptance Criteria**:
- [ ] Upload completes successfully
- [ ] File stored correctly

### FR-7.2: View Receipt

**Description**: Users shall be able to view uploaded receipts.

**Requirements**:
- FR-7.2.1: System shall display receipt in viewer
- FR-7.2.2: System shall support PDF viewing
- FR-7.2.3: System shall download original file

**Acceptance Criteria**:
- [ ] Receipt displays in browser
- [ ] Download works

### FR-7.3: Delete Receipt

**Description**: Users shall be able to delete receipts.

**Requirements**:
- FR-7.3.1: System shall delete file from storage
- FR-7.3.2: System shall remove link from expense
- FR-7.3.3: System shall allow only before expense approval

**Acceptance Criteria**:
- [ ] File deleted
- [ ] Link removed

---

## FR-8: Activity Logging

### FR-8.1: Track Activities

**Description**: System shall log all travel expense activities.

**Requirements**:
- FR-8.1.1: System shall log supplier CRUD operations
- FR-8.1.2: System shall log trip CRUD operations
- FR-8.1.3: System shall log expense CRUD operations
- FR-8.1.4: System shall log approval workflow changes
- FR-8.1.5: System shall capture user_id, action, details
- FR-8.1.6: System shall capture IP address
- FR-8.1.7: System shall capture timestamp

**Acceptance Criteria**:
- [ ] All major operations logged
- [ ] Audit trail complete

---

## FR-9: Settings & Configuration

### FR-9.1: Module Settings

**Description**: System shall provide module configuration.

**Requirements**:
- FR-9.1.1: System shall configure default GL codes
- FR-9.1.2: System shall configure per-diem rules
- FR-9.1.3: System shall configure require receipts setting
- FR-9.1.4: System shall configure approval workflow

**Acceptance Criteria**:
- [ ] Settings page accessible to admins
- [ ] Settings persist correctly

---

## FR-10: Integration

### FR-10.1: FA CRM Integration

**Description**: System shall integrate with FA CRM.

**Requirements**:
- FR-10.1.1: System shall link suppliers separately from stock_master
- FR-10.1.2: System shall not use debtors for suppliers

**Acceptance Criteria**:
- [ ] Separate supplier table maintained

### FR-10.2: Employee Management Integration

**Description**: System shall integrate with FA Employee Management.

**Requirements**:
- FR-10.2.1: System shall link employees from employees table
- FR-10.2.2: System shall display employee name
- FR-10.2.3: System shall validate employee exists

**Acceptance Criteria**:
- [ ] Employee dropdown populated
- [ ] Valid employee checks work

### FR-10.3: Project Management Integration

**Description**: System shall integrate with ksf_FA_ProjectManagement.

**Requirements**:
- FR-10.3.1: System shall reference projects for billing
- FR-10.3.2: System shall reference tasks for billing
- FR-10.3.3: System shall show project/task names

**Acceptance Criteria**:
- [ ] Project dropdown populated
- [ ] Billing links work

### FR-10.4: GL Integration

**Description**: System shall integrate with FA GL.

**Requirements**:
- FR-10.4.1: System shall use GL codes from chart of accounts
- FR-10.4.2: System shall create GL journal entries
- FR-10.4.3: System shall validate GL codes exist
- FR-10.4.4: System shall post to appropriate GL accounts

**Acceptance Criteria**:
- [ ] GL codes from FA used
- [ ] Journal entries created

---

## Appendix: Requirement ID Index

| ID | Description |
|----|-------------|
| FR-1.1 | Create Supplier |
| FR-1.2 | View Suppliers |
| FR-1.3 | Edit Supplier |
| FR-1.4 | Delete Supplier |
| FR-2.1 | Create Trip |
| FR-2.2 | View Trips |
| FR-2.3 | Edit Trip |
| FR-2.4 | Trip Workflow |
| FR-2.5 | Delete Trip |
| FR-3.1 | Create Expense |
| FR-3.2 | View Expenses |
| FR-3.3 | Edit Expense |
| FR-3.4 | Delete Expense |
| FR-4.1 | Per Diem Calculation |
| FR-4.2 | Per Diem Rules |
| FR-4.3 | Per Diem Excess |
| FR-5.1 | Create Expense Report |
| FR-5.2 | Submit Expense Report |
| FR-5.3 | Approve Expense Report |
| FR-5.4 | Process GL Entries |
| FR-6.1 | Dashboard Statistics |
| FR-6.2 | Expense Summary Report |
| FR-6.3 | Per Diem Report |
| FR-7.1 | Upload Receipt |
| FR-7.2 | View Receipt |
| FR-7.3 | Delete Receipt |
| FR-8.1 | Track Activities |
| FR-9.1 | Module Settings |
| FR-10.1 | FA CRM Integration |
| FR-10.2 | Employee Management Integration |
| FR-10.3 | Project Management Integration |
| FR-10.4 | GL Integration |
