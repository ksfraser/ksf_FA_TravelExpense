# FR-EXPENSE-007-001 — Event-close expense attachment: NEW or EXISTING report

@BABOK Related: BR-007; FR-CAL-007-002 (close broadcast + DTO); FR-CAL-007-003
(membership — does NOT gate expense).
@UML : TravelExpense/subscriber -> on `ksf_event_closed` -> NEW header
       (create_travel_request) OR append lines (add_expense)
Status: Approved — BABOK; implementation parks next stage.
Module: ksf_FA_TravelExpense (owner of 0_travel_requests / 0_travel_expenses writes).

## Need (BABOK What-not-How)
Anyone who attended a closed work event may have SPENT money on it — members
AND supporting-role outsiders/contractors. Membership scopes TIME
(FR-TIME-007-002/003) but not spend. The attendee must be able to attach the
event's spend to a brand-new expense report, or append to an already-OPEN
report they are mid-filing — never double-asked, never forced to start fresh.

## Requirement
On receiving `ksf_event_closed`:
1. EVERY attendee in `attendee_emails[]` is offered expense attachment —
   membership classification is ignored for this flow.
2. The user chooses NEW or EXISTING when prompted:
   - NEW -> a fresh `0_travel_requests` header is created from the DTO
     metadata (`project_id`, `task_id`, purpose=event title, dates from
     `started_at/closed_at`, destination=location); subsequent event spend
     lines append to it.
   - EXISTING -> the user supplies their open report (status Pending); the
     event's spend lines append via `add_expense()` — hotel/flights already on
     it stay; parking/coffee from this event are added.
3. Expense lines carry `project_id`, `task_id`, `activity_code` from the DTO so
   cost rolls up to the right project.
4. Duplicate delivery of the same close creates NO duplicate header/line
   (idempotent by `(event_id, attendee_email)` guard on the request header or
   the attached line).
5. Writes live ONLY in the module's own tables; emitters
   (`travel_request_*`/expense events) fire after commit.

## Acceptance
- ARI: event attendee picks NEW -> header created once; parking/coffee lines
  append; second broadcast -> no duplicate header.
- AZZ: attendee with an open report picks EXISTING -> existing lines retained,
  event lines appended, total = old + new.
- BON: EXTERNAL attendee (contractor, not on task) is still offered expense
  attachment (membership does not gate spend).
- CAN: 0 attendees -> no expense prompts, no error.