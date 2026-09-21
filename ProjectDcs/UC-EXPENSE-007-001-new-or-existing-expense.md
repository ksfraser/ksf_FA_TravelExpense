# UC-EXPENSE-007-001 — Attendee attaches event spend to NEW or EXISTING report

@BABOK Related: BR-007; FR-EXPENSE-007-001 (new-or-existing); UC-CAL-007-001
(close flow that triggers this).
Status: Approved — BABOK; implementation parks next stage.
Module: ksf_FA_TravelExpense (subscriber VIA `ksf_event_closed`).

## Preconditions
- A work event closed; the broadcast reached TravelExpense. Actor = an attendee
  (member OR supporting-role outsider) landing on the expense prompt. They may
  already have an OPEN travel request (status Pending).

## Main flow
1. Prompt offers NEW or EXISTING (lists open reports if any).
2a. Actor = NEW. Header from DTO metadata (`purpose`=event title, dates,
    destination=location, project/task), status Pending. Then lines: add
    parking + coffee amounts -> saved against the new header.
2b. Actor = EXISTING, picks open report R. The event's lines append to R via
    `add_expense()`; R's prior hotel/flight lines untouched. Total rolls the
    event's spend onto R.
3. A second delivery of the same event prompt finds the header/line already
   present → no duplicate (guard by `(event_id, attendee_email)`).

## Alternate flows
- **2c. No open report + user picks EXISTING:** list is empty; they fall back to
  NEW (no double-ask).
- **2d. External attendee:** same prompt appears — membership does not gate.

## Postconditions
- Event spend attached once — new header or appended onto an open report;
  project/task rollup correct; idempotent.

## Acceptance
- ARI: NEW -> exactly one header + the event's lines; redelivery -> no change.
- BON: EXISTING -> prior lines + event lines both present on R.