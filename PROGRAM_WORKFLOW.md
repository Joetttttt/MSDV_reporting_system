# MSDV Reporting System - Program Workflow

## System Overview
The MSDV (Mindanao Doctrine Strengthening Values) Reporting System is a comprehensive disciplinary management platform designed to track, report, and manage student violations across an educational institution.

---

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    MSDV REPORTING SYSTEM                         │
├─────────────────────────────────────────────────────────────────┤
│  Frontend (Bootstrap 5.3.3)  │  Backend (PHP 8.2.12)             │
│  - Responsive UI             │  - Session Management             │
│  - Data Input Forms          │  - Database Operations            │
│  - Report Dashboards         │  - Business Logic                 │
│                              │                                   │
│                   Database (MySQL 10.4.32)                       │
│         - Users - Violations - Students - Appeals                │
│         - Notifications - Disciplinary Actions                   │
└─────────────────────────────────────────────────────────────────┘
```

---

## User Roles & Access Levels

### 1. **Admin (System Administrator)**
- **Access Level**: Full system control
- **Primary Functions**:
  - User management (create, update, delete accounts)
  - Report review and case management
  - Disciplinary action assignment
  - Sanction application
  - Analytics and reporting
  - System configuration

### 2. **Teacher**
- **Access Level**: Violation reporting
- **Primary Functions**:
  - Submit violation reports
  - Provide evidence and observations
  - Track reported violations
  - View violation history
  - Generate student reports

### 3. **CSU (Conduct and Student Services Unit)**
- **Access Level**: Case management
- **Primary Functions**:
  - Review violation reports
  - Investigate cases
  - Recommend sanctions
  - Schedule hearings
  - Track case progress

### 4. **JASSU (Junior Achievement Student Services Unit)**
- **Access Level**: Case management
- **Primary Functions**:
  - Review and process violations
  - Coordinate disciplinary actions
  - Manage student counseling
  - Track remedial actions
  - Monitor compliance

### 5. **Student (New)**
- **Access Level**: Self-service viewing
- **Primary Functions**:
  - View personal violations
  - Submit appeals
  - Track appeal status
  - View disciplinary actions
  - Access system notifications

---

## Core Workflows

### WORKFLOW 1: Student Violation Reporting

```
START
  │
  ├─→ Teacher Submits Violation Report
  │   ├─ Select Student
  │   ├─ Classify Violation (Minor/Major)
  │   ├─ Describe Incident
  │   ├─ Attach Evidence
  │   ├─ Capture Camera Evidence (Optional)
  │   └─ E-Sign Report
  │
  ├─→ System Creates Violation Record
  │   ├─ Assigns Unique ID
  │   ├─ Sets Status: "Pending"
  │   ├─ Creates Timestamp Entry
  │   └─ Saves Evidence Files
  │
  ├─→ Student Notification Generated
  │   └─ Alert sent to Student Account
  │
  └─→ END (Violation Pending Review)
```

### WORKFLOW 2: Case Review & Action (Admin/CSU/JASSU)

```
START (Pending Violation)
  │
  ├─→ Reviewer Accesses Violation
  │   ├─ Review Student Profile
  │   ├─ Examine Violation Details
  │   ├─ Review Evidence
  │   ├─ Check Violation History
  │   └─ Assess Violation Category
  │
  ├─→ Decision Point: Valid Violation?
  │   │
  │   ├─ YES → Apply Appropriate Sanction
  │   │   ├─ Select Disciplinary Level
  │   │   ├─ Assign Sanction Type:
  │   │   │  ├─ Verbal Warning
  │   │   │  ├─ Written Warning
  │   │   │  ├─ Community Service
  │   │   │  ├─ Suspension
  │   │   │  ├─ Counseling Session
  │   │   │  └─ Detention
  │   │   ├─ Set Action Duration
  │   │   ├─ Create Notification
  │   │   └─ Update Case Status: "Completed"
  │   │
  │   └─ NO → Dismiss Case
  │       ├─ Document Reason
  │       ├─ Set Status: "Dismissed"
  │       └─ Notify Student
  │
  └─→ END (Case Resolved)
```

### WORKFLOW 3: Student Appeal Process

```
START
  │
  ├─→ Student Views Their Violations
  │   └─ Dashboard displays all violations
  │
  ├─→ Student Selects Violation to Appeal
  │   ├─ Choose Violation ID
  │   └─ Select "Submit Appeal" Option
  │
  ├─→ Student Files Appeal
  │   ├─ Write Appeal Reason/Justification
  │   ├─ Submit Form
  │   └─ System Records Appeal
  │       ├─ Creates Appeal Record
  │       ├─ Sets Status: "Pending"
  │       └─ Notifies Admin
  │
  ├─→ Admin Reviews Appeal
  │   ├─ Read Appeal Reason
  │   ├─ Review Original Violation
  │   ├─ Make Decision: (Approve/Deny/Modify)
  │   │   ├─ APPROVE: Reverse sanction
  │   │   ├─ DENY: Keep original sanction
  │   │   └─ MODIFY: Adjust sanction
  │   └─ Provide Response
  │
  ├─→ Student Notified of Decision
  │   └─ View Appeal Result
  │
  └─→ END (Appeal Closed)
```

### WORKFLOW 4: Student Dashboard Access

```
START (Student Login)
  │
  ├─→ University System Routes Student
  │   └─ Redirects to: /student/dashboard.php
  │
  ├─→ Dashboard Loads Student Data
  │   ├─ Query violations for student ID
  │   ├─ Query appeals for student ID
  │   ├─ Generate Statistics Cards:
  │   │  ├─ Total Violations
  │   │  ├─ Pending Cases
  │   │  ├─ Completed Cases
  │   │  └─ Appeals Filed
  │   └─ Display Student Profile
  │
  ├─→ Display Violations Table
  │   ├─ Date of Incident
  │   ├─ Violation Category
  │   ├─ Violation Type
  │   ├─ Status
  │   └─ Case Status
  │
  ├─→ Display Appeals Table
  │   ├─ Appeal ID
  │   ├─ Violation ID
  │   ├─ Appeal Reason Preview
  │   ├─ Status (Pending/Approved/Denied)
  │   └─ Submission Date
  │
  ├─→ Appeal Submission Form
  │   ├─ Dropdown: Select Violation
  │   ├─ TextArea: Enter Appeal Reason
  │   └─ Button: Submit Appeal
  │       └─ Calls: save_appeal.php
  │
  └─→ END (Dashboard Ready)
```

### WORKFLOW 5: Admin Analytics & Reporting

```
START (Admin Access Reports)
  │
  ├─→ Generate Violation Statistics
  │   ├─ Total Violations by Category
  │   ├─ Violations by Severity
  │   ├─ Violations by Department
  │   ├─ Monthly Trend Analysis
  │   └─ Risk Level Indicators
  │
  ├─→ Generate Disciplinary Statistics
  │   ├─ Cases by Status
  │   ├─ Sanction Distribution
  │   ├─ Average Resolution Time
  │   └─ Repeat Offenders
  │
  ├─→ Visualization Charts
  │   ├─ Department Violation Chart
  │   ├─ Monthly Minor/Major Breakdown
  │   ├─ Specific Violation Type Chart
  │   ├─ Course Distribution Chart
  │   └─ Year Level Analysis
  │
  └─→ Export/Download Reports
      └─ JSON, CSV, or Print Format
```

---

## Database Schema Overview

### Users Table
```sql
- id (Primary Key)
- fullname
- username
- email
- password (hashed)
- role (enum: admin, teacher, csu, jassu, student)
- created_at
```

### Students Table
```sql
- id (Primary Key)
- student_id (Unique)
- fullname
- course
- year_level
- department
- created_at
```

### Violations Table
```sql
- id (Primary Key)
- student_id (Foreign Key)
- student_name
- course
- year_level
- department
- violation_category (Minor/Major)
- violation_type
- description
- evidence (file path)
- camera_capture (image data)
- e_signature
- reported_by
- reporter_role
- status (Pending/Approved)
- created_at
- sanction (action assigned)
- action_start
- action_end
- case_status (Pending/Completed)
- disciplinary_level
- disciplinary_start
- disciplinary_end
```

### Appeals Table
```sql
- id (Primary Key)
- violation_id (Foreign Key)
- student_id
- appeal_reason (text)
- appeal_date
- appeal_status (Pending/Approved/Denied)
- admin_response
- resolved_date
```

### Notifications Table
```sql
- id (Primary Key)
- type (new_violation, appeal_submitted, etc.)
- title
- message
- student_id
- is_read (boolean)
- created_at
```

---

## Authentication & Authorization Flow

```
LOGIN PROCESS:
  │
  ├─→ User Submits Credentials
  │   ├─ Username
  │   └─ Password
  │
  ├─→ System Validates
  │   ├─ Query users table
  │   ├─ Verify password hash
  │   └─ Check user exists
  │
  ├─→ Role-Based Redirect
  │   ├─ admin → /admin/dashboard.php
  │   ├─ teacher → /teacher/report_violation.php
  │   ├─ csu → /csu/report_violation.php
  │   ├─ jassu → /jassu/report_violation.php
  │   └─ student → /student/dashboard.php
  │
  └─→ Session Created
      ├─ user_id
      ├─ fullname
      ├─ username
      └─ role
```

---

## Key Features by Module

### Reporting Module (Teacher)
- Submit violation reports with detailed information
- Attach multiple evidence types (files, images, signatures)
- Category-based violation classification
- Automatic student notification

### Case Management Module (Admin/CSU/JASSU)
- Review and validate violations
- Apply disciplinary sanctions
- Track case progression
- Assign action timelines
- Generate case reports

### Student Dashboard Module
- View personal violation history
- Submit appeals with justification
- Track appeal status in real-time
- Access disciplinary action details
- Receive system notifications

### Admin Analytics Module
- Generate comprehensive reports
- Visualize violation trends
- Analyze disciplinary patterns
- Export data in multiple formats
- Risk assessment indicators

### Notification System
- Real-time student alerts
- Case status updates
- Appeal decision notifications
- System-wide messaging

---

## Data Flow Diagram

```
VIOLATION SUBMISSION:
Teacher → Report Form → save_violation.php → Violations DB → Notification System → Student

APPEAL SUBMISSION:
Student → Appeal Form → save_appeal.php → Appeals DB → Admin Review → Notification → Student

CASE REVIEW:
Admin Dashboard → Violations DB → Apply Sanction → Update DB → Notify Student

ANALYTICS:
Admin Dashboard → Query DB → Generate Charts → Display Reports
```

---

## Error Handling & Validation

- **Input Validation**: All user inputs sanitized and validated
- **Database Integrity**: Foreign key constraints, data type validation
- **Access Control**: Session-based role verification
- **Error Logging**: System errors recorded and displayed appropriately

---

## Security Measures

- **Password Security**: Bcrypt hashing (cost factor 10)
- **Session Management**: Server-side session storage
- **Input Sanitization**: mysqli_real_escape_string() for SQL injection prevention
- **Access Control**: Role-based authorization checks
- **Data Protection**: Sensitive data not exposed in logs

---

## Future Enhancement Opportunities

1. Parent/Guardian portal access
2. Email notification integration
3. Appeal mediation workflow
4. Behavioral improvement tracking
5. Predictive risk analysis
6. Mobile application
7. Advanced reporting with custom filters
8. Integration with student information system

---

**Document Version**: 1.0  
**Last Updated**: May 28, 2026  
**System Version**: MSDV Reporting System v1.0
