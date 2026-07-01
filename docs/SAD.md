# Software Architecture Document (SAD)

## 1. Judul
**Aplikasi Mobile EventTickets**

## 2. Revision History
| Versi | Tanggal | Penulis | Deskripsi |
| --- | --- | --- | --- |
| 1.0 | 2026-07-01 | Tim Pengembang | Dokumen awal SAD untuk aplikasi EventTickets |

## 3. Table of Contents
1. [Judul](#1-judul)
2. [Revision History](#2-revision-history)
3. [Table of Contents](#3-table-of-contents)
4. [Introduction](#4-introduction)
5. [Architectural Design](#5-architectural-design)
   - [System Architecture](#system-architecture)
   - [Use Case Diagram](#use-case-diagram)
   - [Activity Diagram](#activity-diagram)
   - [Class Diagram](#class-diagram)
   - [Sequence Diagram](#sequence-diagram)
6. [User Interface Design](#6-user-interface-design)
   - [Wireframe](#wireframe)
   - [Mockup](#mockup)
7. [Database Design](#7-database-design)
8. [Deployment Diagram](#8-deployment-diagram)
9. [Security and Authorization](#9-security-and-authorization)

## 4. Introduction
### 4.1 Purpose
Dokumen ini menjelaskan arsitektur sistem, desain teknis, dan desain antarmuka untuk aplikasi mobile EventTickets.

### 4.2 Scope
Sistem mencakup:
- Mobile App Flutter untuk customer
- Backend Laravel REST API untuk manajemen event, pembelian, pembayaran, tiket, dan refund
- Relational Database untuk data event, order, tiket
- File storage untuk QR pembayaran dan QR tiket
- Mekanisme otentikasi, otorisasi, serta pemulihan kegagalan

### 4.3 References
- SRS: `docs/SRS_Aplikasi_Mobile_EventTickets.md`
- OpenAPI spec: `docs/openapi/purchase_payment_openapi.yaml`
- Diagram PlantUML: `docs/diagrams/*.puml`

## 5. Architectural Design
### System Architecture
Arsitektur sistem:
- **Mobile App (Flutter)**: bertindak sebagai client yang memanggil REST API.
- **Backend (Laravel)**: bertanggung jawab atas logika bisnis, pembayaran, dan manajemen tiket.
- **Database**: menyimpan pengguna, event, ticket type, orders, order items, tickets, refunds.
- **Storage**: menyimpan QR pembayaran dan QR tiket.
- **Payment Gateway**: layanan eksternal yang mengirim webhook ketika pembayaran diterima.

Keputusan desain penting:
- `purchase` melakukan reservasi stock tiket (`sold` increment) di dalam transaksi.
- `generate payment QR` memproduksi QRIS dan signed URL.
- `confirm payment` membuat tiket hanya setelah pembayaran diverifikasi.

### Use Case Diagram
Gunakan file PlantUML: `docs/diagrams/use_case.puml`

### Activity Diagram
Gunakan file PlantUML: `docs/diagrams/activity.puml`

### Class Diagram
Gunakan file PlantUML: `docs/diagrams/class_diagram.puml`

### Sequence Diagram
Gunakan file PlantUML: `docs/diagrams/sequence_diagram.puml`

### ERD (Entity Relationship Diagram)
Gunakan file PlantUML: `docs/diagrams/erd.puml`

## 6. User Interface Design
### Wireframe
Gunakan file PlantUML: `docs/diagrams/ui_wireframe.puml`

Komponen UI utama:
- Event List: daftar event dengan poster, nama, tanggal, lokasi.
- Event Detail: detail event dan pilihan ticket type.
- Purchase Screen: form quantity, ringkasan harga, tombol checkout.
- Payment Screen: total harga, QR pembayaran, timer, tombol retry.
- Ticket Screen: tampilan tiket dan QR tiket.

### Mockup
Desain mockup sebaiknya menggunakan palet gelap dengan aksen hijau untuk nilai sukses, merah untuk error, dan putih/abu-abu untuk teks. Mockup dapat dibuat di Figma atau tools desain lainnya; detail ini dapat dilampirkan terpisah.

## 7. Database Design
### Tabel dan kolom utama
- `users`: id, name, email, email_verified_at, password, role, phone, profile_photo_path, bio, location_lat, location_lng, is_active, remember_token, created_at, updated_at
- `events`: id, organizer_id, title, description, location_name, location_address, location_lat, location_lng, starts_at, ends_at, capacity, is_active, created_at, updated_at
- `ticket_types`: id, event_id, name, description, price, quota, sold, is_active, created_at, updated_at
- `event_images`: id, event_id, path, label, created_at, updated_at
- `orders`: id, user_id, event_id, total_price, status, payment_qr_data, payment_deadline, paid_at, created_at, updated_at
- `order_items`: id, order_id, ticket_type_id, quantity, unit_price, line_total, created_at, updated_at
- `tickets`: id, order_id, order_item_id, ticket_type_id, code, qr_path, used, used_at, created_at, updated_at
- `refunds`: id, order_id, processed_by, amount, reason, status, requested_by, processed_at, ticket_ids, created_at, updated_at

### Relasi utama
- `users` 1..* `events` (organizer)
- `events` 1..* `ticket_types`
- `orders` 1..* `order_items`
- `order_items` 1..* `tickets`
- `orders` 1..* `tickets`
- `orders` 1..* `refunds`
- `users` 1..* `refunds` (requested_by / processed_by)
- `events` 1..* `event_images`
- `tickets`: id, order_id, order_item_id, ticket_type_id, code, qr_path, created_at, updated_at
- `refunds`: id, order_id, amount, status, reason, created_at, updated_at

### Relasi
- User 1..* Order
- Event 1..* TicketType
- Order 1..* OrderItem
- OrderItem 1..* Ticket

### Indeks dan constraint
- Index: `orders.user_id`, `orders.status`, `ticket_types.event_id`
- Unique: `tickets.code`
- Foreign key: `orders.user_id -> users.id`, `orders.event_id -> events.id`, `order_items.order_id -> orders.id`, `order_items.ticket_type_id -> ticket_types.id`, `tickets.order_id -> orders.id`, `tickets.order_item_id -> order_items.id`

## 8. Deployment Diagram
Gunakan file PlantUML: `docs/diagrams/deployment.puml`

## 9. Security and Authorization
### Authentication
- Mobile menggunakan JWT untuk semua endpoint yang memerlukan otorisasi.
- Token disimpan di secure storage dan dikirim melalui header `Authorization: Bearer <token>`.

### Authorization
- Customer hanya dapat mengakses order/ticket miliknya.
- Admin dapat mengelola event dan melakukan verifikasi pembayaran.

### Data Security
- QR pembayaran diakses melalui signed temporary URL.
- Validasi input di backend untuk semua request.
- Jangan log data sensitif seperti token atau string QR sepenuhnya.

### Webhook & Payment
- Webhook payment gateway harus diverifikasi menggunakan signature.
- Endpoint konfirmasi pembayaran harus idempotent.

## Lampiran
- OpenAPI: `docs/openapi/purchase_payment_openapi.yaml`
- Diagram PU ML: `docs/diagrams/use_case.puml`, `docs/diagrams/activity.puml`, `docs/diagrams/class_diagram.puml`, `docs/diagrams/sequence_diagram.puml`, `docs/diagrams/deployment.puml`, `docs/diagrams/ui_wireframe.puml`
- Konversi: `docs/convert_sad.ps1`
