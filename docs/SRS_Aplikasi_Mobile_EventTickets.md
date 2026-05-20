# 1. Judul

SRS — Sistem Informasi Event Tickets (Aplikasi Mobile)

# 2. Revision History

| Revision | Tanggal | Penulis | Catatan |
|---|---:|---|---|
| 0.1 | 2026-04-28 | Tim Pengembang | Draft awal SRS; cakupan fitur mobile dasar dan LBS |

# 3. Table of Contents

1. Judul
2. Revision History
3. Table of Contents
4. Introduction
   - Purpose
   - Scope
   - Definitions, Acronyms, and Abbreviations
   - References
   - Overview
5. Overall Description
   - Problem Identification
   - User Characteristics
   - User Requirement
   - Functional Requirement
   - Non-Functional Requirement
   - Data Requirement
   - Constraints
   - Assumptions and Dependencies
6. Specific Requirements
7. Lampiran

# 4. Introduction

## Purpose
Dokumen ini mendeskripsikan kebutuhan sistem (Software Requirements Specification, SRS) untuk Aplikasi Mobile "Event Tickets" — platform untuk mencari, melihat, dan membeli tiket event serta fitur bagi penyelenggara untuk membuat event.

## Scope
Aplikasi mobile menyediakan:
- Pendaftaran dan otentikasi pengguna (user dan organizer)
- Browsing event, pencarian lokasi (LBS) untuk event dalam radius 10 km
- Lihat detail event, gambar, deskripsi, tanggal, lokasi
- List dan pembelian tiket (checkout sederhana)
- Panel penyelenggara: tambah/edit/delete event dan tiket
- Integrasi backend API (Laravel) sebagai sumber data

Aplikasi tidak mencakup: pembayaran gateway kompleks (hanya simulasi/placeholder), manajemen inventori fisik, atau fitur analitik lanjutan.

## Definitions, Acronyms, and Abbreviations
<!-- Extended SRS for Event Tickets mobile app -->

# 1. Judul

Software Requirements Specification (SRS)
Event Tickets — Aplikasi Mobile (Android / iOS)

# 2. Revision History

| Revision | Tanggal | Penulis | Catatan |
|---|---:|---|---|
| 0.1 | 2026-04-28 | Tim Pengembang | Draft awal SRS (ringkas) |
| 0.2 | 2026-04-28 | Tim Pengembang | Perluasan SRS: use-cases, API contract, data model, fitur lanjutan |

# 3. Table of Contents

1. Judul
2. Revision History
3. Table of Contents
4. Introduction
5. Overall Description
6. Specific Requirements
7. System Models and Diagrams
8. API Contract (endpoints, request/response)
9. Data Model (ERD / schema)
10. Use Cases & User Stories
11. Non-functional Requirements (detailed)
12. Security & Privacy
13. Testing and Acceptance Criteria
14. Deployment & Maintenance
15. Risks, Constraints, Assumptions
16. Appendices / Lampiran

# 4. Introduction

## Purpose
Dokumen ini menjabarkan kebutuhan fungsional dan non-fungsional untuk aplikasi mobile "Event Tickets". Tujuan SRS ini adalah:
- Menyediakan acuan untuk tim pengembang frontend (Flutter) dan backend (Laravel).
- Menggambarkan API contract dan format data untuk integrasi.
- Menetapkan acceptance criteria untuk QA dan testing.

## Scope
Versi mobile mencakup fitur end-user dan organizer:
- Otentikasi (register/login) dan manajemen sesi (JWT).
- Browsing event, pencarian LBS (10 km), tampilan daftar dan detail event.
- Pembelian tiket sederhana, ringkasan order.
- Organizer CRUD event & ticket types, draft & preview.

Fitur yang direncanakan sebagai peningkatan:
- Integrasi pembayaran (payment gateway), notifikasi push, rekomendasi event, scan QR untuk check-in.

## Definitions, Acronyms, and Abbreviations
- SRS: Software Requirements Specification
- LBS: Location-Based Service
- API: Application Programming Interface
- UX: User Experience
- JWT: JSON Web Token
- ERD: Entity Relationship Diagram

## References
- Backend API source: `backend/` Laravel project
- Flutter docs: https://flutter.dev/docs
- Haversine formula: https://en.wikipedia.org/wiki/Haversine_formula

# 5. Overall Description

## Problem Identification
Pengguna membutuhkan cara cepat untuk menemukan event lokal, melihat detail, dan membeli tiket di perangkat mobile. Penyelenggara membutuhkan cara mudah mempublikasikan acara dan mengelola tiket.

## User Characteristics
- Guest / Visitor: browsing tanpa login, dapat melihat event.
- Registered User: dapat membeli tiket, menyimpan favorit, melihat pembelian.
- Organizer: dapat membuat/mengelola event dan ticket types.
- Admin: manajemen pengguna, moderation (backend web).

## Key User Journeys
- Penemuan Event: buka aplikasi → lihat daftar default → gunakan lokasi → lihat detail → beli tiket.
- Organizer Flow: register as organizer → create event → upload cover → add ticket types → save draft → publish.

## Major System Interfaces
- Mobile app ↔ Backend REST API (JSON over HTTPS)
- Mobile app ↔ Geolocation services (device)
- Mobile app ↔ Secure storage (local token)

# 6. Specific Requirements (Detailed)

Setiap requirement diberi ID, prioritas, dan acceptance criteria.

6.1 Authentication & Account
- SR-AUTH-001 (P0): User dapat mendaftar dengan `name`, `email`, `password`, `role`.
  - Acceptance: server mengembalikan HTTP 201 dan objek user + token.
- SR-AUTH-002 (P0): User dapat login dan menyimpan JWT di secure storage.
  - Acceptance: setelah login, endpoint `/auth/login` mengembalikan `access_token` dan `user`.
- SR-AUTH-003 (P1): Terdapat opsi `forgot password` (email) — backend stub.

6.2 Event Listing & Search
- SR-EVT-001 (P0): GET `/api/events` mengembalikan daftar event dengan pagination.
  - Params: `page`, `per_page`, optional `lat`, `lng`, `radius`.
  - Acceptance: jika `lat,lng` diberikan dan `radius<=30`, balikkan `distance_km` pada response.
- SR-EVT-002 (P1): Filtering by category, date range, price range.

6.3 Event Detail
- SR-EVT-010 (P0): GET `/api/events/{id}` mengembalikan lengkap field event dan `ticket_types`.
  - Acceptance: jika field penting null, UI harus tampilkan pesan 'Event data incomplete'.

6.4 Purchase
- SR-PUR-001 (P0): POST `/api/purchase` dengan body `{event_id, ticket_type_id, quantity}`.
  - Acceptance: return 201 with `order` and `tickets` array and `order_code`.

6.5 Organizer Management
- SR-ORG-001 (P0): Organizer CRUD endpoints protected by role-based auth.

6.6 Additional Features (Enhancements)
- SR-ENH-001 (P2): Add favorites/wishlist for users.
- SR-ENH-002 (P2): Calendar export (.ics) or add to device calendar.
- SR-ENH-003 (P1): Social login (Google / Facebook) support.
- SR-ENH-004 (P1): Notifications (push) for organizer updates and reminders.

# 7. System Models and Diagrams (Textual)

7.1 Use-case summary (high-level)
- UC1: Browse Events (Guest)
- UC2: Search Nearby Events (User)
- UC3: View Event Detail (Guest/User)
- UC4: Purchase Ticket (User)
- UC5: Manage Event (Organizer)

7.2 Sequence (browse -> detail -> purchase)
- User opens app -> App requests `/api/events` -> User taps event -> App requests `/api/events/{id}` -> User selects ticket and quantity -> App posts to `/api/purchase` -> Server returns `tickets` -> App shows `OrderResultScreen`.

# 8. API Contract (selected endpoints)

8.1 POST /auth/register
Request JSON:
```
{
  "name": "String",
  "email": "email@example.com",
  "password": "string",
  "role": "user|organizer"
}
```
Response 201:
```
{
  "user": {"id": 1, "name": "...", "email": "...", "role":"organizer"},
  "access_token": "<jwt>",
  "token_type": "bearer"
}
```

8.2 GET /api/events?lat={lat}&lng={lng}&radius=10&page=1
Response 200 (partial):
```
{
  "data": [
    {"id":123, "title":"Concert", "cover_path":"https://...", "date":"2026-05-10", "distance_km":3.5}
  ],
  "meta": {"page":1, "per_page":10, "total":200}
}
```

8.3 GET /api/events/{id}
Response 200:
```
{
  "id":123,
  "title":"Concert",
  "description":"...",
  "cover_path":"https://...",
  "date":"2026-05-10T19:00:00Z",
  "location_name":"Stadium",
  "latitude":-6.2,
  "longitude":106.8,
  "ticket_types":[{"id":1, "name":"VIP","price":150000, "quota":100, "sold":2}]
}
```

8.4 POST /api/purchase
Request:
```
{
  "event_id":123,
  "ticket_type_id":1,
  "quantity":2
}
```
Response 201:
```
{
  "order": {"id": 999, "order_code":"ORD-999", "total":300000},
  "tickets": [{"id":111, "code":"TCK-111", "qr":"<base64>"}]
}
```

# 9. Data Model (ERD summary)

Entities:
- Users(id, name, email, password_hash, role, created_at)
- Events(id, organizer_id, title, short_description, description, cover_path, starts_at, ends_at, location_name, latitude, longitude, status, created_at)
- TicketTypes(id, event_id, name, price, quota, sold)
- Purchases(id, user_id, event_id, total, created_at)
- PurchaseItems(id, purchase_id, ticket_type_id, quantity, price)

Indexes & Relationships:
- Users 1..* Events (organizer)
- Events 1..* TicketTypes
- Purchases 1..* PurchaseItems

# 10. Use Cases & User Stories

10.1 Use Case: Search Nearby Events
- Actor: User
- Precondition: App has location permission
- Flow: User allows location -> App requests `/api/events?lat&lng` -> Server returns events with `distance_km` sorted ascending.
- Postcondition: User sees list of nearby events.

10.2 User Story (As a user, I want to save favorite events)
- Acceptance: tapping heart icon adds to `/api/users/{id}/favorites` and persists locally for offline view.

# 11. Non-functional Requirements (Detailed)

- Performance: 95% of list requests return within 1.5s on 4G.
- Scalability: backend supports pagination and query limits; mobile caches results for 5 minutes.
- Reliability: app recovers from network failures with retry/backoff.
- Accessibility: support for text scaling, semantic labels, and high-contrast theme.
- Internationalization: support ID and EN locales.

# 12. Security & Privacy

- Tokens: Use JWT with short expiry; refresh token pattern optional.
- Storage: store tokens in `flutter_secure_storage`.
- Transport: enforce HTTPS, HSTS on server.
- Sensitive data: do not persist credit card or password in plaintext.
- Privacy: request location with rationale; allow opt-out and manual input.

# 13. Testing and Acceptance Criteria

13.1 Unit & Widget Tests (Flutter)
- Coverage: core screens (EventList, EventDetail, Login, Register, Purchase flow) with mock API responses.

13.2 Integration / API Tests (PHPUnit / HTTP)
- Tests for registration, login, LBS results, purchase, organizer CRUD.

13.3 Manual QA Checklist
- Permission flows (allow/deny), network failures, empty states, invalid data handling (e.g., missing `id`).

# 14. Deployment & Maintenance

- Mobile: Release on Play Store (Android) and App Store (iOS) with appropriate app signing.
- Backend: Deploy Laravel app with queue workers for email/notifications; use env secrets for DB and JWT.
- Monitoring: add error tracking (Sentry) and analytics (optional).

# 15. Risks, Constraints, Assumptions

- Risk: Incomplete data from backend causing UI crashes — mitigate by defensive parsing and schema validation.
- Constraint: Payment gateway integration requires additional PCI compliance.
- Assumption: Cover images are accessible via HTTPS.

# 16. Appendices / Lampiran

Appendix A — Example JSON responses (see API Contract above)

Appendix B — Suggested Enhancements (roadmap):
- Payment gateway (Midtrans/Stripe) integration
- Push notifications for reminders and organizer messages
- QR-based check-in + organizer scan app or web
- Event recommendation engine (simple collaborative filtering)
- Offline mode: cache last viewed events and purchased tickets

Appendix C — Acceptance Test Matrix (summary)
- SR-AUTH-001: test register returns 201 and token
- SR-EVT-001: test events listing with lat/lng returns distance_km
- SR-PUR-001: test purchase returns 201 and tickets

---

Dokumen ini adalah versi diperluas SRS untuk aplikasi mobile "Event Tickets". Jika Anda ingin saya tambahkan diagram (use-case, ERD) saya bisa:
- Menghasilkan ERD PNG/ASCII dan menempatkannya di `docs/diagrams/`.
- Membuat sequence diagrams untuk purchase flow.
- Mengonversi SRS ke PDF.

Pilihan selanjutnya: `tambah diagram`, `buat API contract file (OpenAPI/Swagger)`, atau `buat acceptance test cases` — sebutkan pilihan Anda, saya lanjutkan.
