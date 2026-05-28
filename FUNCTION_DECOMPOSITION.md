# Function Decomposition — MSDV Reporting System

This document decomposes the MSDV Reporting System into top-level functions and sub-functions to clarify responsibilities and guide implementation.

## System-Level Function
- MSDV Reporting System (overall)

```mermaid
flowchart TD
  SYS[MSDV Reporting System]
  SYS --> AUTH[Authentication & Authorization]
  SYS --> REP[Violation Reporting]
  SYS --> CM[Case Management]
  SYS --> AP[Appeals]
  SYS --> NOT[Notifications]
  SYS --> STU[Student Dashboard]
  SYS --> UMG[User Management]
  SYS --> ANL[Analytics & Reporting]
  SYS --> FILE[Evidence & File Management]
  SYS --> DB[Database Operations]

  %% Decompose Authentication
  AUTH --> A1[Login / Session]
  AUTH --> A2[Password Hashing]
  AUTH --> A3[Role-based Redirects]

  %% Reporting
  REP --> R1[Create Violation]
  REP --> R2[Attach Evidence]
  REP --> R3[Capture Camera Image]
  REP --> R4[Validate Input]
  REP --> R5[Notify Student]

  %% Case Management
  CM --> C1[Assign Reviewer]
  CM --> C2[Review Evidence]
  CM --> C3[Apply Sanction]
  CM --> C4[Update Case Status]
  CM --> C5[Schedule Hearings]

  %% Appeals
  AP --> P1[Submit Appeal]
  AP --> P2[Admin Review Appeal]
  AP --> P3[Respond to Appeal]
  AP --> P4[Update Appeal Status]

  %% Notifications
  NOT --> N1[Create Notification]
  NOT --> N2[Mark Read/Unread]
  NOT --> N3[Push / Email (future)]

  %% Student Dashboard
  STU --> S1[View Violations]
  STU --> S2[Submit Appeal Form]
  STU --> S3[View Appeal Status]
  STU --> S4[View Sanctions]

  %% User Management
  UMG --> U1[Create/Update/Delete Users]
  UMG --> U2[Manage Roles]
  UMG --> U3[Password Reset]

  %% Analytics
  ANL --> AN1[Aggregate Violations]
  ANL --> AN2[Generate Charts]
  ANL --> AN3[Export Reports]

  %% File/Evidence
  FILE --> F1[Upload Files]
  FILE --> F2[Store/Serve Files]
  FILE --> F3[Sanitize File Inputs]

  %% Database Operations
  DB --> D1[CRUD Violations]
  DB --> D2[CRUD Appeals]
  DB --> D3[CRUD Users/Students]
  DB --> D4[Transactions & Migrations]
```

---

## Numbered Function Breakdown

1. Authentication & Authorization
   - 1.1 Login (verify credentials, start session)
   - 1.2 Password hashing & verification (bcrypt)
   - 1.3 Role-based redirect and access checks
   - 1.4 Logout and session expiration

2. Violation Reporting
   - 2.1 Report creation (form validation)
   - 2.2 Evidence attachment (file uploads, camera capture)
   - 2.3 Auto-generate notification to student(s)
   - 2.4 Store report metadata in `violations` table

3. Case Management
   - 3.1 Case assignment to Admin/CSU/JASSU
   - 3.2 Evidence review & notes
   - 3.3 Apply or modify sanctions (update `sanction`, `case_status`)
   - 3.4 Scheduling and logging actions (hearing dates)

4. Appeals
   - 4.1 Appeal submission (link to `violation_id`)
   - 4.2 Appeal queue for admin review
   - 4.3 Admin response and resolution (approve/deny/modify)
   - 4.4 Persist appeal decisions and notify student

5. Notifications
   - 5.1 Create notification records (type/title/message)
   - 5.2 Deliver notifications in-app (and optional email)
   - 5.3 Mark read/unread and history view

6. Student Dashboard
   - 6.1 Aggregate student-specific violations and appeals
   - 6.2 Render lists, detail views, and summary cards
   - 6.3 Provide forms for appeal submission

7. User Management
   - 7.1 Admin CRUD for users
   - 7.2 Role lifecycle (add student role handled)
   - 7.3 Password resets and account auditing

8. Analytics & Reporting
   - 8.1 Query aggregation (counts by category/department)
   - 8.2 Render charts (monthly trends, department charts)
   - 8.3 Export (CSV/JSON)

9. Evidence & File Management
   - 9.1 Secure file upload and storage
   - 9.2 Serve media for display
   - 9.3 Cleanup and retention policy

10. Database Operations
    - 10.1 Schema migrations (SQL dumps / ALTERs)
    - 10.2 Transactional writes where needed
    - 10.3 Indexing and performance (queries used by dashboards)

---

## Implementation Mapping (example files)
- Authentication: `auth/login.php`, `auth/logout.php`
- Violation reporting: `teacher/report_violation.php`, `teacher/save_violation.php`
- Appeals: `student/save_appeal.php`, `student/dashboard.php`
- Student view: `student/dashboard.php`
- Admin actions: `admin/*` (dashboard, reports, resolve_report.php)
- DB schema: `mcc_discipline_system.sql`

---

## How to use this diagram
- Use the mermaid flowchart at the top to visualize parent→child function relationships.
- Use the numbered breakdown for planning implementation sprints or writing unit/integration tests.
- Map each sub-function to files or controllers to create task-level tickets.

---

**Document Version**: 1.0  
**Last Updated**: May 28, 2026
