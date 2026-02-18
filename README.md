## Barber Shop Booking (PHP)

### 1. Create the database

1. Open phpMyAdmin or your MySQL client.
2. Create a database named `barber_db` (or another name, but then also update `config.php`).
3. Import `schema.sql` into that database.

### 2. Configure database connection

Open `config.php` and adjust:

- `DB_HOST` (usually `localhost`)
- `DB_NAME` (the database you created)
- `DB_USER` / `DB_PASS` (your MySQL username and password)
- `BASE_URL` (URL where you will access the app, e.g. `http://localhost/barber`)

### 3. Configure email sending

This project uses PHP`s `mail()` function to send the 6-digit verification codes.

- On Windows, you must configure SMTP in your `php.ini` file:
  - Set `SMTP`, `smtp_port`, and `sendmail_from`.
- Alternatively, replace the logic in `send_verification_email()` (in `functions.php`) with an SMTP library such as PHPMailer.

When email is configured correctly, the app will:

- Send a 6-digit code when you register (for email verification).
- Send a 6-digit code every time you log in (for extra security).

### 4. Run the app

1. Place the `barber` folder inside your local web server root:
   - For XAMPP: `C:\xampp\htdocs\barber`
2. Start Apache and MySQL from XAMPP.
3. Visit `http://localhost/barber` in your browser.

### 5. Features

- Create account with name, email, and password.
- Email verification with 6-digit code after registration.
- Login with password + 6-digit email code.
- Admin-only services management:
  - Mark any user as admin by setting `is_admin = 1` for that user in the `users` table (via phpMyAdmin).
  - Admin page at `admin_services.php` to add services, set default prices, enable/disable them.
- Book appointments with:
  - Service selected from the admin-defined list.
  - Price automatically loaded from the service (so the user clearly sees how much to pay).
  - Exact appointment date and time.
- Dashboard shows:
  - Total number of appointments you have booked.
  - History of your last bookings (service, price, time, created date).

### 6. Load default services and prices (Ghana)

To quickly add basic barbering services with prices in Ghana cedis:

1. In phpMyAdmin, open the `barber_db` database.
2. Go to the **Import** tab.
3. Select the file `seed_services.sql` from the `barber` folder.
4. Run the import.

This will create these services (you can later change them in `admin_services.php`):

- Haircut – 50.00
- Beard Trim – 30.00
- Haircut & Beard – 70.00
- Kids Cut – 35.00


