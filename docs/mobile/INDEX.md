# MyImam Mobile App — Role-Based Access Documentation

**Version:** 1.0.0  
**Last Updated:** 29 April 2026  
**Purpose:** Development guide for the MyImam mobile application — defining what each role can access and what fields are present on each screen.

---

## System Overview

MyImam is a mosque financial management platform. It is **multi-tenant**: each mosque (Masjid) is an isolated tenant. Users belong to a mosque and operate within that mosque's data boundary.

---

## Roles Summary

| Role | Level | Scope | Description |
|------|-------|-------|-------------|
| **Superadmin** | 1 | Global | Full system control — manages all mosques, subscriptions, CMS, settings |
| **Admin** | 2 | Tenant | Mosque administrator — manages users, finance, reports, and CMS for their mosque |
| **Manager** | 3 | Tenant | Oversees finance approvals and user management; no data entry for transactions |
| **Bendahari** | 3 | Tenant | Treasurer — full finance entry, all reports, manage reference data |
| **FinanceOfficer** | 3 | Tenant | Finance data entry — record income, expense, and transfers |
| **AJK** | 3 | Tenant | Committee member — view-only access to all finance modules |
| **Auditor** | 3 | Tenant | Read-only access to audit logs and all reports |
| **MasjidOfficer** | 3 | Tenant | Basic officer — can record income and view reports |
| **User** | 3 | Tenant | General user — dashboard and report view only |

---

## Module Glossary

| Module (Malay) | English | Description |
|----------------|---------|-------------|
| Hasil | Income / Revenue | All money received by the mosque |
| Belanja | Expense | All payments made by the mosque |
| Akaun | Account | Bank/cash accounts held by the mosque |
| Pindahan Akaun | Account Transfer | Transfers between mosque's own accounts |
| Sumber Hasil | Revenue Source | Categories of income (e.g., Friday collection, donation) |
| Kategori Belanja | Expense Category | Categories of expense (e.g., utilities, maintenance) |
| Tabung Khas | Special Fund | Dedicated fund pots (e.g., renovation fund) |
| Program Masjid | Mosque Program | Events/programs expenses are linked to |
| Baucar Bayaran | Payment Voucher | Pre-approved payment voucher linked to expense |
| Running No | Document Number | Auto-generated sequential receipt/voucher numbers |
| Laporan | Report | Financial reports in various formats |
| Log Aktiviti | Audit Log | System activity trail |
| Masjid | Mosque | The mosque/tenant entity |

---

## Common Fields on All Screens

Every authenticated screen includes:

| Field | Description |
|-------|-------------|
| `user.name` | Logged-in user's name |
| `user.email` | Logged-in user's email |
| `user.peranan` | User's role label |
| `masjid.nama` | Current mosque name |
| `notifications` | Unread notification count badge |

---

## Document Index

| File | Role |
|------|------|
| [SUPERADMIN.md](SUPERADMIN.md) | Superadmin |
| [ADMIN.md](ADMIN.md) | Admin |
| [MANAGER.md](MANAGER.md) | Manager |
| [BENDAHARI.md](BENDAHARI.md) | Bendahari (Treasurer) |
| [FINANCE_OFFICER.md](FINANCE_OFFICER.md) | FinanceOfficer |
| [AJK.md](AJK.md) | AJK (Committee Member) |
| [AUDITOR.md](AUDITOR.md) | Auditor |
| [MASJID_OFFICER.md](MASJID_OFFICER.md) | MasjidOfficer |
| [USER.md](USER.md) | User (General) |

---

## API Base URL

```
https://{tenant}.padat.net/api
```
or for local development:
```
http://localhost:8000/api
```

All protected endpoints require:
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```
