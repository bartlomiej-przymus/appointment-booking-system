# Appointment Booking System

**Example implementation of online appointment booking system using Laravel, Livewire, Filament and Stripe.**

### Description

I was looking at different scheduling and online appointment booking systems and I did not like them, so I decided to build one myself.

### Be Warned

This software is intended for my personal use only. Its purpose is to provide me with the opportunity to build apps, that interest me, in my own way, while using technologies I love, learning and having fun. Besides I need a portfolio so there is that as well I guess :).<br> It may contain bugs and other critters it may contain unfinished features - use it at your own risk!

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
- run `npm run dev` to start vite<br>
Project is using Filament database notification so it requires a worker to be running in the background in order to process them:
- run 'php artisan queue:work' to receive db notifications

### Testing

Project uses Pest testing library you can run test by this command:<br>
- run `php artisan test`


### Access
Please note that user seeder is set to create admin user email that will have same domain as one set in application .env file.<br>

For example if `APP_URL` is set like this:<br>
`APP_URL=https://myapp.test`<br>
Seeded admin user email credentials will look like this:<br>
`admin@myapp.test`

You can access admin panel on `your-configured-domain-name-here/admin`<br>

Email Address: as explained above<br>
Password: `password`

### Features roadmap

- [X] Livewire calendar component with available days and time slots that users can choose and book.
(calendar component implemented booking functionality remains to be done)
- [ ] User account section to where users can manage their upcoming appontments providing them with window to reschedule / cancel appointments.
- [X] Admin Panel that gives admin users ability to manage Time schedules, availability slots and see upcoming appointments.
- [ ] Email notification composer for various stages of Appointment Booking.
