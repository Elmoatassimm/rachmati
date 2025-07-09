# Rachmat Multi-Store Platform

A comprehensive multi-store digital platform for selling "Rachmat" (digital embroidery files) built with Laravel 12, Inertia.js, and React.js.

## 🌟 Features

### 🧑‍🎨 Designer Features
- **Subscription Management**: Monthly subscription with manual payment validation
- **Rachma Management**: Upload, edit, and manage digital embroidery files
- **Store Management**: Customize store profile and social media links
- **Earnings Tracking**: Monitor sales and earnings
- **Rating System**: View customer ratings and feedback

### 👥 Client Features
- **Browse & Search**: Filter rachmat by category, size, stitches, etc.
- **Detailed View**: Preview images, specifications, and ratings
- **Manual Checkout**: Upload payment proof for CCP, Baridi Mob, Dahabiya
- **Order Tracking**: Monitor order status from pending to completion
- **Rating & Comments**: Rate and review rachmat and designer stores
- **Telegram Delivery**: Automatic file delivery via Telegram

### 🛠 Admin Features
- **Subscription Management**: Approve/reject designer subscriptions
- **Order Management**: Confirm payments and manage transactions
- **Designer Management**: Activate/deactivate stores and track performance
- **Category Management**: Create and manage categories and subcategories
- **Transaction Flow**: Handle payments and designer earnings

## 🏗 Tech Stack

- **Backend**: Laravel 12
- **Frontend**: React.js with Inertia.js
- **Database**: SQLite (configurable)
- **Authentication**: Laravel Sanctum
- **File Delivery**: Telegram Bot API
- **Styling**: Tailwind CSS
- **Build Tool**: Vite

## 📋 Requirements

- PHP 8.2+
- Node.js 18+
- Composer
- SQLite (or MySQL/PostgreSQL)

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd rachamat
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup
```bash
# Run migrations
php artisan migrate

# Seed initial data (admin user and categories)
php artisan db:seed
```

### 5. Telegram Bot Setup (Optional)
1. Create a Telegram bot via [@BotFather](https://t.me/botfather)
2. Get your bot token
3. Add to `.env`:
```env
TELEGRAM_BOT_TOKEN=your_bot_token_here
```

### 6. Storage Setup
```bash
# Create storage link for file uploads
php artisan storage:link
```

### 7. Start Development Servers
```bash
# Start all services (Laravel, Queue, Logs, Vite)
composer run dev

# Or start individually:
php artisan serve          # Laravel server
npm run dev                # Vite dev server
php artisan queue:work     # Queue worker
```

## 🗄 Database Schema

### Core Tables
- **users**: User accounts (clients, designers, admins)
- **designers**: Designer profiles and subscription info
- **categories**: Rachma categories (multilingual)
- **sub_categories**: Subcategories for detailed classification
- **rachmat**: Digital embroidery files with metadata
- **orders**: Purchase orders and payment tracking
- **ratings**: Rating system for rachmat and stores
- **comments**: User comments and reviews
- **designer_social_media**: Social media links for designers

## 🔐 Default Admin Account

After running the seeders, you can login with:
- **Email**: admin@rachmat.com
- **Password**: password123

## 📱 API Endpoints

### Authentication
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout
- `GET /api/auth/user` - Get authenticated user

### Designers
- `GET /api/designers` - List active designers with filters
  - Query Parameters:
    - `search` (optional) - Search by store name
    - `per_page` (optional) - Items per page (default: 15, max: 50)
    - `paginate` (optional) - Set to 'false' to disable pagination
  - Filters: Only returns designers with `subscription_status = 'active'` and valid `subscription_end_date`

### Rachmat
- `GET /api/rachmat` - List rachmat with filters
- `GET /api/rachmat/{id}` - Get rachma details

### Orders
- `POST /api/orders` - Create new order
- `GET /api/orders/{id}` - Get order details
- `GET /api/my-orders` - Get user's orders

### Ratings
- `POST /api/ratings` - Submit rating/comment
- `GET /api/ratings/{targetType}/{targetId}` - Get ratings for target

## 🌐 Web Routes

### Public Routes
- `/` - Homepage
- `/rachmat` - Browse rachmat
- `/rachmat/{rachma}` - Rachma details
- `/designers` - Browse designers
- `/designers/{designer}` - Designer store

### Admin Routes (Prefix: `/admin`)
- `/dashboard` - Admin dashboard
- `/designers` - Manage designers
- `/orders` - Manage orders
- `/categories` - Manage categories

### Designer Routes (Prefix: `/designer`)
- `/dashboard` - Designer dashboard
- `/rachmat` - Manage rachmat
- `/subscription` - Subscription management

## 🔧 Configuration

### Localization
The platform supports Arabic and French:
```php
// In .env
APP_LOCALE=ar  # or 'fr'
```

### File Storage
Configure storage for rachma files and payment proofs:
```php
// In config/filesystems.php
'default' => 'public',  # or 's3' for production
```

### Queue Configuration
For Telegram file delivery:
```bash
# Start queue worker
php artisan queue:work

# Or use supervisor in production
```

## 🎨 Frontend Structure

```
resources/js/
├── components/          # Reusable React components
├── layouts/            # Page layouts
├── pages/              # Inertia.js pages
│   ├── Admin/          # Admin dashboard pages
│   ├── Designer/       # Designer dashboard pages
│   ├── Rachmat/        # Public rachmat pages
│   └── Auth/           # Authentication pages
├── hooks/              # Custom React hooks
├── lib/                # Utility functions
└── types/              # TypeScript definitions
```

## 🔄 Workflow

### Designer Subscription Flow
1. Designer registers and creates store profile
2. Uploads payment proof for monthly subscription
3. Admin reviews and approves/rejects subscription
4. Designer can upload and manage rachmat when active

### Order Flow
1. Client browses and selects rachma
2. Uploads payment proof during checkout
3. Admin reviews and confirms payment
4. System automatically sends file via Telegram
5. Order marked as completed

### Rating System
1. Clients can rate rachmat and designer stores (1-5 stars)
2. Comments require admin approval before display
3. Average ratings calculated automatically

## 🚀 Deployment

### Production Setup
1. Set `APP_ENV=production` in `.env`
2. Configure proper database (MySQL/PostgreSQL)
3. Set up file storage (S3 recommended)
4. Configure queue worker with supervisor
5. Set up SSL certificate
6. Configure Telegram webhook (optional)

### Performance Optimization
```bash
# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License.

## 🆘 Support

For support and questions:
- Create an issue in the repository
- Contact the development team

## 🔮 Roadmap

- [ ] Mobile app (React Native)
- [ ] Advanced analytics dashboard
- [ ] Automated pricing strategies
- [ ] Multi-language support expansion
- [ ] Payment gateway integration
- [ ] Advanced search with AI
- [ ] Designer collaboration features # rachmati
