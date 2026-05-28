# Use Case Diagrams — MSDV Reporting System

This document lists and numbers all primary use cases by actor. Each section includes a short description and a Mermaid use-case diagram.

**Legend**: (UC#) = Use Case number

---

## 1. Admin (System Administrator)
1. (UC1) Manage Users — create/update/delete accounts and change roles
2. (UC2) Review Violations — view, validate, and reopen cases
3. (UC3) Apply Sanctions — assign disciplinary actions and timelines
4. (UC4) Review & Respond to Appeals — approve/deny/modify appeals
5. (UC5) View Analytics & Export Reports — generate charts and exports

```mermaid
usecaseDiagram
actor Admin
Admin --> (Manage Users)
Admin --> (Review Violations)
Admin --> (Apply Sanctions)
Admin --> (Review & Respond to Appeals)
Admin --> (View Analytics & Export Reports)
```

---

## 2. Teacher
1. (UC6) Submit Violation Report — report student incidents with evidence
2. (UC7) View Submitted Reports — track status of reported cases
3. (UC8) Receive Notifications — alerts about case developments

```mermaid
usecaseDiagram
actor Teacher
Teacher --> (Submit Violation Report)
Teacher --> (View Submitted Reports)
Teacher --> (Receive Notifications)
```

---

## 3. CSU (Conduct & Student Services Unit)
1. (UC9) Review Assigned Cases — investigate and recommend actions
2. (UC10) Coordinate Hearings — schedule interviews/counseling
3. (UC11) Update Case Progress — set case status and notes

```mermaid
usecaseDiagram
actor CSU
CSU --> (Review Assigned Cases)
CSU --> (Coordinate Hearings)
CSU --> (Update Case Progress)
```

---

## 4. JASSU (Junior/Local Student Services Unit)
1. (UC12) Review Violations — assist processing and mitigation
2. (UC13) Monitor Remedial Actions — track completion of sanctions
3. (UC14) Notify Students — send case updates and reminders

```mermaid
usecaseDiagram
actor JASSU
JASSU --> (Review Violations)
JASSU --> (Monitor Remedial Actions)
JASSU --> (Notify Students)
```

---

## 5. Student
1. (UC15) View My Violations — list and inspect personal violations
2. (UC16) Submit Appeal — file an appeal with justification
3. (UC17) Track Appeal Status — monitor appeal resolution and admin response
4. (UC18) Receive Notifications — receive alerts for new violations and appeal outcomes

```mermaid
usecaseDiagram
actor Student
Student --> (View My Violations)
Student --> (Submit Appeal)
Student --> (Track Appeal Status)
Student --> (Receive Notifications)
```

---

## 6. System (Automated Processes)
1. (UC19) Create Notifications — send alerts on new violations/appeals/decisions
2. (UC20) Maintain Audit Logs — record key actions for auditability
3. (UC21) Enforce Role-Based Redirects — route users after login

```mermaid
usecaseDiagram
actor System
System --> (Create Notifications)
System --> (Maintain Audit Logs)
System --> (Enforce Role-Based Redirects)
```

---

## Cross-Actor Use Cases (Shared)
1. (UC22) Authenticate Users — login and session management
2. (UC23) Upload Evidence — attach files/images to violations
3. (UC24) Search & Filter Records — query violations, appeals, and users

```mermaid
usecaseDiagram
actor Admin
actor Teacher
actor CSU
actor JASSU
actor Student

(Admin, Teacher, CSU, JASSU, Student) --> (Authenticate Users)
(Admin, Teacher, CSU, JASSU, Student) --> (Upload Evidence)
(Admin, Teacher, CSU, JASSU, Student) --> (Search & Filter Records)
```

---

## Notes
- Use case numbering (UC#) is referenced across the system documentation and can be used for test cases and API endpoint mapping.
- Diagrams use Mermaid `usecaseDiagram` syntax — renderable in many Markdown viewers (VS Code, GitHub with extensions, and Mermaid-enabled renderers).

**Document Version**: 1.0
**Last Updated**: May 28, 2026
