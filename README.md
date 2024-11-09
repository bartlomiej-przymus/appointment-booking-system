# Appointment Booking System

**Example implementation of online appointment booking system using Laravel, Livewire, Filament and Stripe.**

### Description

This project is part of bigger effort to build up my portfolio and provide me with the opportunity to experiment and have fun with code.<br>
As it turns out I need appointment booking system for future project that I'm planning, so I decided to build one.

### Technologies Used

- Laravel (v.11)
- Livewire (v.3)
- Filament (v.3)
- Tailwind
- Pint (code style formatter)

### State

 - project is in active development

### Requirements

- PHP: 8.3
- MySql Database

### Installation

- clone it to local directory
- create local domain for project - I use `abs.test`
- set up project `.env` file 
- run `composer install` to install php dependencies.
- run `npm install` to install js dependencies.
- run `php artisan key:generate` to set application key.
- run `php artisan migrate` to setup database tables.
- run `php artisan db:seed` to seed necessary tables.
- run `npm run dev` to start vite

### Access
You can access admin panel on `abs.test/admin`<br>
User: `admin`<br>
Pass: `password`

### Features to be implemented
Online appointment booking system consisting of:

- **Frontend Interface** Livewire calendar element with available days and time slots that users can choose and book.
- **User Account Section** containing details of booked appointments, past orders. Its purpose is to provide user the opportunity to cancel or reschedule appointments.
- **Admin Panel** Implemented using Filament Laravel library. It gives Admin user ability to:
1. Create, View, Edit and Delete Time Schedules.
2. View, Cancel and Reschedule Booked Appointments
3. View Past Orders
4. Create, View, Edit and Delete Email Notifications sent for various stages of Appointment Booking.
