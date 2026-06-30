# PROJECT RULES

Version: 1.0

---

# Project Identity

Project Name

Official Music Band Application

Project Type

Refactoring Existing Software

Architecture

Flutter Mobile

↓

REST API

↓

Laravel Backend

↓

MySQL Database

---

# Project Philosophy

This project is NOT a rewrite.

This project is a business transformation.

Existing code is an asset.

Always reuse before replacing.

Never destroy working modules.

Think like maintaining a production system.

---

# Business Domain

This application belongs to ONE music band.

There is NO organizer.

There is NO multi-tenant system.

The application represents the official digital platform of the band.

Core business:

• Concert Ticket Sales

Supporting business:

• Band Profile

• Members

• Albums

• Songs

• Gallery

• News

• Fan Management

---

# Development Principles

Always perform:

Analyze

↓

Explain

↓

Plan

↓

Refactor

↓

Test

↓

Continue

Never skip analysis.

Never modify many modules simultaneously.

---

# Refactoring Rules

Always:

Reuse existing code.

Refactor before replacing.

Delete only unnecessary modules.

Keep project stable.

Avoid introducing bugs.

---

# Architecture Rules

Architecture must remain exactly:

Flutter

↓

REST API

↓

Laravel

↓

Database

Never merge Flutter into Laravel.

Never replace Flutter.

Never replace Laravel.

---

# Backend Rules

Controllers should remain thin.

Business logic belongs to Services.

Database logic belongs to Repository.

Validation belongs to Form Request.

Responses use API Resources.

Authorization uses Middleware.

Avoid duplicated logic.

---

# Flutter Rules

UI must not contain business logic.

Use Services.

Use Repository Pattern.

Keep Widgets reusable.

Avoid duplicated screens.

---

# API Rules

REST API only.

Response format:

{
    "success": true,
    "message": "...",
    "data": {}
}

Use consistent endpoint naming.

Avoid breaking existing APIs.

---

# Database Rules

Database must remain normalized.

Avoid duplicate tables.

Avoid duplicate columns.

Avoid unnecessary relationships.

Remove Organizer tables only.

---

# Security Rules

Keep JWT Authentication.

Protect Admin APIs.

Validate every request.

Sanitize uploads.

Protect QR Ticket validation.

Never expose sensitive information.

---

# Performance Rules

Optimize database queries.

Use eager loading.

Implement pagination.

Reduce duplicated API requests.

Lazy load large datasets.

---

# UI Rules

Theme

Modern

Minimal

Premium

Dark

Music-oriented

Avoid clutter.

Keep navigation simple.

---

# Documentation Rules

Every modification must include:

Purpose

Reason

Files changed

Database changes

API changes

Flutter changes

Possible risks

---

# Before Every Change

Cursor must answer:

Can I reuse this?

Can I refactor this?

Can I keep compatibility?

Can I avoid deleting code?

If YES

Reuse it.

If NO

Explain why.

---

# Code Quality

Follow SOLID Principles.

Follow Clean Architecture.

Follow DRY.

Follow KISS.

Write readable code.

Avoid unnecessary abstractions.

---

# Testing

After every module:

Verify backend.

Verify Flutter.

Verify API.

Verify Database.

Ensure no existing feature is broken.

---

# Final Objective

The final software must appear as if it was originally developed as an Official Music Band Application.

The application should be production-ready, scalable, maintainable, and suitable as a capstone project.