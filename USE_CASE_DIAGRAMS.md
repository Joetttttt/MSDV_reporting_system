# MSDV Reporting System - Use Case Diagrams

This document describes the main actors and use case interactions in the MSDV Reporting System.

## Actors
- **Admin**
- **Teacher**
- **CSU**
- **JASSU**
- **System** (internal process actor for notifications and session handling)

## Primary Use Cases

### Admin use cases
- Authenticate / login
- View dashboard summary
- Manage students
- Manage users
- View violation reports
- Resolve violations
- Save sanctions
- Update disciplinary status
- View charts and analytics
- Execute backup/export
- View notifications
- Change password
- Logout

### Teacher / CSU / JASSU use cases
- Authenticate / login
- Report student violation
- Upload evidence
- Capture signature / camera evidence
- View submitted reports
- View notifications
- Logout

## Use Case Diagram

```mermaid
usecaseDiagram
    actor Admin
    actor Teacher
    actor CSU
    actor JASSU
    actor System as Sys

    Admin --> (Login)
    Teacher --> (Login)
    CSU --> (Login)
    JASSU --> (Login)

    Admin --> (View dashboard)
    Admin --> (Manage students)
    Admin --> (Manage users)
    Admin --> (View violation reports)
    Admin --> (Resolve reports)
    Admin --> (Save sanctions)
    Admin --> (Update disciplinary status)
    Admin --> (View analytics charts)
    Admin --> (Backup / export data)
    Admin --> (Change password)
    Admin --> (Logout)

    Teacher --> (Report violation)
    Teacher --> (Upload evidence)
    Teacher --> (Capture signature / camera evidence)
    Teacher --> (View my reports)
    Teacher --> (View notifications)
    Teacher --> (Logout)

    CSU --> (Report violation)
    CSU --> (Upload evidence)
    CSU --> (Capture signature / camera evidence)
    CSU --> (View my reports)
    CSU --> (View notifications)
    CSU --> (Logout)

    JASSU --> (Report violation)
    JASSU --> (Upload evidence)
    JASSU --> (Capture signature / camera evidence)
    JASSU --> (View my reports)
    JASSU --> (View notifications)
    JASSU --> (Logout)

    Sys --> (Validate session)
    Sys --> (Insert violation record)
    Sys --> (Create notification)

    (Report violation) .> (Upload evidence) : includes
    (Report violation) .> (Capture signature / camera evidence) : includes
    (Report violation) .> (Create notification) : includes
    (Resolve reports) .> (Save sanctions) : includes
    (View violation reports) .> (View analytics charts) : extends
    (Login) .> (Validate session) : includes
```

## Notes
- The `Login` use case is shared by all human actors.
- The `Report violation` use case applies identically to `Teacher`, `CSU`, and `JASSU`.
- The `System` actor represents backend operations triggered by user actions, such as saving records and generating notifications.
- Admin-only modules are located under `admin/`.
- Reporting interfaces are located under `teacher/`, `csu/`, and `jassu/`.
