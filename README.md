# EventRentals

A full-stack event rental marketplace built with Laravel, PHP, MySQL and JavaScript.

EventRentals is designed to connect customers with vendors offering event-related rental services and products. The platform handles rental listings, bookings, payments, vendor operations and customer interactions through a centralized web application.

## Live Application

https://eventrenthub.com.ng

## Portfolio

https://classik7.github.io/Awelewa-portfolio/

---

## Overview

EventRentals was developed as a full-stack application to solve the challenges involved in discovering, booking and managing event rental services.

The system includes customer-facing marketplace features, vendor management, booking workflows, payment integration and administrative functionality.

## Key Features

### Marketplace
- Browse event rental products and services
- Rental listing management
- Categories and product organization
- Search and marketplace discovery
- Trending and featured listings
- Wishlist functionality
- Customer reviews

### Booking & Rental Management
- Rental booking workflow
- Availability management
- Rental summaries
- Booking status management
- Vendor-side rental management

### Payments & Financial Workflows
- Paystack payment integration
- Payment verification
- Wallet functionality
- Platform commission handling
- Escrow-based payment workflow
- Vendor withdrawal workflow

### Vendor & Trust System
- Vendor management
- KYC workflow
- Trust levels
- Vendor wallet management
- Administrative vendor controls

### Authentication & Security
- User authentication
- Role-based access control
- Protected application areas
- Secure backend workflows
- API authentication

### AI Event Planner
The platform includes an AI-assisted event planning feature that helps users organize event requirements and identify suitable rental categories based on their planning needs.

### Mobile Application
A Flutter mobile client was developed to interact with the EventRentals backend through REST APIs.

---

## Technology Stack

### Backend
- Laravel 12
- PHP
- MySQL
- Eloquent ORM
- Laravel REST APIs

### Frontend
- Blade
- JavaScript
- HTML5
- CSS3
- Tailwind CSS
- Vite
- AJAX

### Payments & Integrations
- Paystack
- REST API integrations

### Mobile
- Flutter
- Dart

### Development Tools
- Git
- GitHub
- XAMPP
- Composer
- npm

---

## Architecture

The application follows a Laravel MVC architecture with:

- Controllers for application logic
- Models and Eloquent ORM for database interaction
- Blade views for server-rendered interfaces
- REST API endpoints for mobile/client communication
- Middleware for authentication and access control
- Services for reusable business logic
- MySQL for persistent application data

---

## Engineering Highlights

Some of the engineering work involved in the project includes:

- Designing relational database structures for marketplace operations
- Implementing rental booking and availability logic
- Integrating and verifying Paystack payments
- Building wallet and escrow workflows
- Implementing vendor KYC and trust-level logic
- Developing REST APIs for the mobile application
- Connecting a Flutter client to the Laravel backend
- Implementing role-based application access
- Building an AI-assisted event planning workflow
- Developing responsive marketplace interfaces
- Using Git and GitHub for source-code management

---

## Project Structure

```text
app/
├── Http/
├── Models/
├── Services/
└── ...

database/
├── migrations/
├── seeders/
└── factories/

resources/
├── views/
├── css/
└── js/

routes/
├── web.php
├── api.php
└── auth.php

tests/
├── Feature/
└── Unit/