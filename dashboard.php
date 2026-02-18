<?php

require_once __DIR__ . '/functions.php';
require_login();

$user = current_user();

// Fetch appointment count and latest appointments
$pdo = get_pdo();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Read any booking flash messages
$bookingErrors = $_SESSION['booking_errors'] ?? [];
$bookingSuccess = $_SESSION['booking_success'] ?? null;
unset($_SESSION['booking_errors'], $_SESSION['booking_success']);

// Debug: Log the session data
error_log("Dashboard - Session data: " . print_r($_SESSION, true));
error_log("Dashboard - Booking success: " . print_r($bookingSuccess, true));

// Load active services for booking
$servicesStmt = $pdo->query('SELECT id, name, default_price FROM services WHERE is_active = 1 ORDER BY name');
$services = $servicesStmt->fetchAll();

$stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM appointments WHERE user_id = :uid');
$stmt->execute([':uid' => $user['id']]);
$appointmentCount = (int)$stmt->fetch()['total'];

$stmt = $pdo->prepare('
    SELECT service, price, appointment_datetime, created_at
    FROM appointments
    WHERE user_id = :uid
    ORDER BY appointment_datetime DESC
    LIMIT 10
');
$stmt->execute([':uid' => $user['id']]);
$appointments = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Barber Shop</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 0; color: #111827; }
        header { background: #111827; color: #fff; padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { margin: 0; font-size: 22px; }
        header .user { font-size: 14px; }
        header a { color: #e5e7eb; margin-left: 15px; text-decoration: none; }
        .container { max-width: 900px; margin: 30px auto; padding: 0 20px; box-sizing: border-box; }
        .grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: 20px; }
        .card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; margin-bottom: 15px; font-size: 20px; }
        label { display: block; margin-top: 10px; font-size: 14px; }
        input, select {
            width: 100%; padding: 10px; margin-top: 5px; border-radius: 4px; border: 1px solid #d1d5db; font-size: 14px;
        }
        button {
            margin-top: 15px; padding: 10px 15px; background: #111827; color: #fff; border: none; border-radius: 4px;
            cursor: pointer; font-size: 15px; width: 100%;
        }
        button:hover { background: #000; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        th, td { padding: 8px 6px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        th { background: #f9fafb; font-weight: 600; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 999px; background: #e0f2fe; color: #0369a1; font-size: 12px; }
        .stat { font-size: 26px; font-weight: 600; }
        .muted { color: #6b7280; font-size: 13px; }

        @media (max-width: 768px) {
            header {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
            }
            header .user {
                font-size: 13px;
            }
            .container {
                margin: 20px auto;
                padding: 0 12px;
            }
            .grid {
                grid-template-columns: 1fr;
            }
            table {
                font-size: 13px;
            }
            th, td {
                padding: 6px 4px;
            }
        }
    </style>
</head>
<body>
<header>
    <div>
        <h1>Barber Shop Dashboard</h1>
    </div>
    <div class="user">
        Welcome, <?php echo htmlspecialchars($user['name']); ?> |
        <a href="home.php">Home</a>
        <?php if (!empty($user['is_admin']) && (int)$user['is_admin'] === 1): ?>
            <a href="admin_services.php">Admin</a>
        <?php endif; ?>
        <a href="logout.php">Logout</a>
    </div>
</header>

<div class="container">
    <div class="grid">
        <div class="card">
            <h2>Book an Appointment</h2>

            <?php if (!empty($bookingErrors)): ?>
                <div style="background:#fee2e2;color:#b91c1c;padding:10px;border-radius:4px;margin-bottom:10px;">
                    <ul style="margin:0;padding-left:18px;">
                        <?php foreach ($bookingErrors as $e): ?>
                            <li><?php echo htmlspecialchars($e); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (is_array($bookingSuccess)): ?>
                <div class="alert alert-success" style="margin: 20px; padding: 15px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px;">
                    <h4 style="margin-top: 0; color: #155724;">✅ <?php echo htmlspecialchars($bookingSuccess['message'] ?? 'Appointment Booked!'); ?></h4>
                    <div style="margin-top: 10px; padding: 10px; background: white; border-radius: 4px;">
                        <p><strong>Appointment #:</strong> <?php echo htmlspecialchars($bookingSuccess['appointment_number'] ?? 'N/A'); ?></p>
                        <p><strong>Service:</strong> <?php echo htmlspecialchars($bookingSuccess['service'] ?? 'N/A'); ?></p>
                        <p><strong>Date & Time:</strong> <?php echo htmlspecialchars($bookingSuccess['datetime'] ?? 'N/A'); ?></p>
                        <p><strong>Amount to Pay:</strong> GHS <?php echo isset($bookingSuccess['price']) ? number_format($bookingSuccess['price'], 2) : '0.00'; ?></p>
                    </div>
                    <p style="margin-top: 10px; font-size: 0.9em; color: #0c5460;">
                        A confirmation email has been sent to <?php echo htmlspecialchars($user['email']); ?>
                    </p>
                </div>
            <?php elseif (is_string($bookingSuccess) && $bookingSuccess !== ''): ?>
                <div class="alert alert-success" style="margin: 20px; padding: 15px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px;">
                    <?php echo htmlspecialchars($bookingSuccess); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="book_appointment.php">
                <label for="service_id">Service</label>
                <select id="service_id" name="service_id" required>
                    <option value="">Select service</option>
                    <?php foreach ($services as $service): ?>
                        <option value="<?php echo (int)$service['id']; ?>"
                                data-price="<?php echo htmlspecialchars($service['default_price']); ?>">
                            <?php echo htmlspecialchars($service['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Price (in GHS)</label>
                <div id="priceDisplay" class="muted">Select a service to see the price.</div>

                <label for="datetime">Appointment Date &amp; Time</label>
                <input type="datetime-local" id="datetime" name="datetime" required>

                <button type="submit">Book Appointment</button>
            </form>
            <p class="muted">
                After booking, you will see the appointment in your history with the exact time and amount to pay.
            </p>
        </div>

        <div class="card">
            <h2>Your Stats</h2>
            <div class="stat"><?php echo $appointmentCount; ?></div>
            <div class="muted">Total appointments you have booked</div>
        </div>
    </div>

    <div class="card" style="margin-top: 20px;">
        <h2>Appointment History</h2>
        <?php if (empty($appointments)): ?>
            <p class="muted">You have not booked any appointments yet.</p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>Service</th>
                    <th>Price (GHS)</th>
                    <th>Appointment Time</th>
                    <th>Booked On</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($appointments as $appt): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($appt['service']); ?></td>
                        <td>GHS <?php echo number_format($appt['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars((new DateTime($appt['appointment_datetime']))->format('M d, Y H:i')); ?></td>
                        <td><?php echo htmlspecialchars((new DateTime($appt['created_at']))->format('M d, Y H:i')); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<script>
    (function () {
        var select = document.getElementById('service_id');
        var priceDisplay = document.getElementById('priceDisplay');
        if (!select || !priceDisplay) return;

        select.addEventListener('change', function () {
            var option = select.options[select.selectedIndex];
            var price = option.getAttribute('data-price');
            if (price) {
                priceDisplay.textContent = 'Price: GHS ' + parseFloat(price).toFixed(2);
            } else {
                priceDisplay.textContent = 'Select a service to see the price.';
            }
        });
    })();
</script>
</body>
</html>
