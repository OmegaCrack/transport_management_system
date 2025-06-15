# Transport Management System (TMS)

A comprehensive Transport Management System built with Laravel, designed to manage transportation operations, bookings, and customer communications efficiently.

## 🚀 Features

- **Booking Management**: Create, view, update, and cancel transport bookings
- **Real-time Notifications**: Email and SMS notifications for booking confirmations and updates
- **User Management**: Role-based access control for administrators and customers
- **Route Management**: Manage transportation routes and schedules
- **API-First Approach**: RESTful API for seamless integration with other systems
- **Responsive Design**: Mobile-friendly interface for on-the-go access

## 📋 Requirements

- PHP 8.1 or higher
- Composer
- Postgres 
- Node.js & NPM
- Redis (for queue and caching)
- Twilio Account (for SMS notifications)

## 🛠 Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/transport_management_system.git
   cd transport_management_system
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install JavaScript dependencies:
   ```bash
   npm install
   npm run build
   ```

4. Copy the environment file:
   ```bash
   cp .env.example .env
   ```

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Configure your `.env` file with database and other settings.

7. Run database migrations and seeders:
   ```bash
   php artisan migrate --seed
   ```

## ⚙️ Configuration

### Environment Variables

Update these variables in your `.env` file:

```env
APP_NAME="Transport Management System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=transport_management
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

TWILIO_SID=your_twilio_sid
TWILIO_AUTH_TOKEN=your_twilio_auth_token
TWILIO_FROM=your_twilio_phone_number
```

## 📱 Notifications

The system sends notifications via both email and SMS for important events like booking confirmations and updates.

### SMS Notifications

To enable SMS notifications:

1. Sign up for a [Twilio](https://www.twilio.com/) account
2. Get your Account SID and Auth Token
3. Update your `.env` file with Twilio credentials
4. Ensure users have a valid phone number in their profile

### Email Notifications

Email notifications are sent using Laravel's mail system. Configure your mail settings in the `.env` file.

## 🚀 Usage

### Running the Application

Start the development server:

```bash
php artisan serve
```

Run queue worker for processing notifications:
```bash
php artisan queue:work
```

### Accessing the Application

- **Web Interface**: Visit `http://localhost:8000` in your browser
- **API Documentation**: Available at `/api/documentation` after installing and configuring [Laravel API Documentation](https://github.com/knuckleswtf/scribe) package

## 🧪 Testing

Run the test suite:

```bash
php artisan test
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [Laravel](https://laravel.com/)
- [Twilio](https://www.twilio.com/)
- All contributors who have helped shape this project
