 <?php

require_once __DIR__ . '/functions.php';

// Admin-only: send non-logged-in users to the admin login page
if (!is_logged_in()) {
    header('Location: admin_login.php');
    exit;
}
$current = current_user();
if (!$current || (int)($current['is_admin'] ?? 0) !== 1) {
    http_response_code(403);
    echo 'Access denied.';
    exit;
}

$pdo = get_pdo();
$errors = [];
$success = '';

// Handle add / update services
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $price = $_POST['default_price'] ?? '';

        if ($name === '') {
            $errors[] = 'Service name is required.';
        }
        if ($price === '' || !is_numeric($price) || (float)$price < 0) {
            $errors[] = 'Valid default price is required.';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare('INSERT INTO services (name, default_price) VALUES (:name, :price)');
            $stmt->execute([
                ':name' => $name,
                ':price' => (float)$price,
            ]);
            $success = 'Service added successfully.';
        }
    } elseif ($action === 'toggle') {
        $id = $_POST['id'] ?? '';
        if ($id !== '' && ctype_digit((string)$id)) {
            $stmt = $pdo->prepare('UPDATE services SET is_active = 1 - is_active WHERE id = :id');
            $stmt->execute([':id' => (int)$id]);
            $success = 'Service status updated.';
        }
    } elseif ($action === 'update_price') {
        $id = $_POST['id'] ?? '';
        $price = $_POST['default_price'] ?? '';
        if ($id === '' || !ctype_digit((string)$id)) {
            $errors[] = 'Invalid service.';
        } elseif ($price === '' || !is_numeric($price) || (float)$price < 0) {
            $errors[] = 'Valid default price is required.';
        } else {
            $stmt = $pdo->prepare('UPDATE services SET default_price = :price WHERE id = :id');
            $stmt->execute([
                ':price' => (float)$price,
                ':id' => (int)$id,
            ]);
            $success = 'Service price updated.';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'];
        if (is_numeric($id)) {
            try {
                $stmt = $pdo->prepare('DELETE FROM services WHERE id = ?');
                $stmt->execute([$id]);
                $success = 'Service deleted successfully.';
            } catch (PDOException $e) {
                $errors[] = 'Error deleting service: ' . $e->getMessage();
            }
        } else {
            $errors[] = 'Invalid service ID.';
        }
    }
}

// Load all services
$stmt = $pdo->query('SELECT id, name, default_price, is_active FROM services ORDER BY name');
$services = $stmt->fetchAll();

// Get search query for appointments
$search = trim($_GET['search'] ?? '');

// Load all appointments with customer info for the barber
if ($search !== '') {
    $appointmentsStmt = $pdo->prepare('
        SELECT a.id,
               a.appointment_number,
               a.service,
               a.price,
               a.appointment_datetime,
               a.created_at,
               u.name AS customer_name,
               u.email AS customer_email
        FROM appointments a
        JOIN users u ON u.id = a.user_id
        WHERE a.appointment_number LIKE :search
        ORDER BY a.appointment_datetime DESC
    ');
    $appointmentsStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    $appointmentsStmt->execute();
} else {
    $appointmentsStmt = $pdo->query('
        SELECT a.id,
               a.appointment_number,
               a.service,
               a.price,
               a.appointment_datetime,
               a.created_at,
               u.name AS customer_name,
               u.email AS customer_email
        FROM appointments a
        JOIN users u ON u.id = a.user_id
        ORDER BY a.appointment_datetime DESC
    ');
}
$appointments = $appointmentsStmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Services | Barber Shop</title>
    <style>
        /* Base styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', sans-serif;
            background: #f3f4f6; 
            color: #111827; 
            line-height: 1.5;
        }

        /* Header */
        header { 
            background: #111827; 
            color: #fff; 
            padding: 12px 16px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        header a { 
            color: #e5e7eb; 
            text-decoration: none; 
            font-size: 0.875rem;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        header a:hover {
            background: rgba(255,255,255,0.1);
        }

        header h1 { 
            margin: 0; 
            font-size: 1.25rem;
            font-weight: 600;
        }

        /* Header and Navigation */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 8px;
            margin-right: 8px;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            position: relative;
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-links {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            transition: all 0.3s ease;
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }

            .header-links {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: #1f2937;
                flex-direction: column;
                padding: 12px 16px;
                gap: 8px;
                max-height: 0;
                overflow: hidden;
                opacity: 0;
                border-radius: 0 0 8px 8px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }

            .header-links.active {
                max-height: 500px;
                opacity: 1;
                padding: 12px 16px;
            }

            header a {
                display: block;
                text-align: left;
                padding: 8px 12px;
                background: rgba(255,255,255,0.1);
                border-radius: 4px;
            }
        }

        /* Main container */
        .container { 
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 16px;
            width: 100%;
        }

        /* Cards */
        .card { 
            background: #fff; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.05); 
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
        }

        h2 { 
            margin: 0 0 16px 0; 
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
        }

        /* Forms */
        .form-group {
            margin-bottom: 16px;
        }

        label { 
            display: block; 
            margin-bottom: 6px; 
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }

        input[type="text"], 
        input[type="number"],
        input[type="email"],
        input[type="password"],
        select,
        textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.9375rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            line-height: 1.5;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .btn-primary {
            background: #111827;
            color: #fff;
        }

        .btn-primary:hover {
            background: #1f2937;
        }

        .btn-sm {
            padding: 4px 12px;
            font-size: 0.8125rem;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        /* Tables */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 0 -16px;
            padding: 0 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
            min-width: 800px;
        }

        th, td { 
            padding: 12px 8px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        tr:hover {
            background-color: #f9fafb;
        }

        /* Status badges */
        .badge { 
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            line-height: 1;
        }

        .badge.active { 
            background: #dcfce7; 
            color: #15803d; 
        }

        .badge.inactive { 
            background: #fee2e2; 
            color: #b91c1c; 
        }

        /* Alerts */
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 0.9375rem;
            line-height: 1.5;
        }

        .alert-error { 
            background: #fef2f2; 
            color: #b91c1c;
            border-left: 4px solid #dc2626;
        }

        .alert-success { 
            background: #f0fdf4;
            color: #166534;
            border-left: 4px solid #16a34a;
        }

        /* Form layouts */
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 16px;
        }

        .form-group {
            flex: 1;
            min-width: 200px;
        }

        /* Search form */
        .search-form {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .search-form input[type="text"] {
            flex: 1;
            min-width: 200px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 0 12px;
            }
            
            .card {
                padding: 16px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 12px;
            }
            
            .form-group {
                width: 100%;
            }
            
            .header-container {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }
            
            .header-links {
                flex-direction: column;
                gap: 8px;
            }
            
            header a {
                display: block;
                text-align: center;
                padding: 8px;
                background: rgba(255,255,255,0.1);
            }
        }

        @media (max-width: 480px) {
            .container {
                margin: 12px auto;
            }
            
            .btn {
                width: 100%;
                padding: 10px 16px;
            }
            
            .search-form button {
                width: 100%;
            }
            
            table {
                font-size: 0.8125rem;
            }
            
            th, td {
                padding: 8px 6px;
            }
        }
        
        /* Print styles */
        @media print {
            header, .no-print {
                display: none !important;
            }
            
            .container {
                margin: 0;
                padding: 0;
                max-width: 100%;
            }
            
            .card {
                box-shadow: none;
                border: none;
                padding: 0;
                margin: 0 0 20px 0;
            }
            
            table {
                width: 100%;
                font-size: 12px;
            }
        }
        
        .inline-form {
            display: inline-block;
            margin: 0;
        }

        .actions {
            white-space: nowrap;
        }
    </style>
</head>
<body>
<header>
    <div class="header-container">
        <div class="header-logo">
            <button class="menu-toggle" aria-label="Toggle menu">
                <span class="menu-icon">☰</span>
            </button>
            <h1>Barber Panel</h1>
        </div>
        <nav class="header-links" id="mainMenu">
            <a href="admin_services.php" class="active">Services</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout (<?= htmlspecialchars($current['name'] ?? 'Admin') ?>)</a>
        </nav>
    </div>
</header>

<div class="container">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>Add New Service</h2>
        <form method="post" action="">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Service Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="default_price">Price (GH₵)</label>
                    <input type="number" id="default_price" name="default_price" min="0" step="0.01" required>
                </div>
            </div>
            <button type="submit" name="action" value="add" class="btn btn-primary">Add Service</button>
        </form>
    </div>

    <div class="card">
        <h2>Manage Services</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Service Name</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td><?= htmlspecialchars($service['name']) ?></td>
                            <td>GH₵<?= number_format($service['default_price'], 2) ?></td>
                            <td>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="id" value="<?= $service['id'] ?>">
                                    <input type="hidden" name="action" value="toggle">
                                    <button type="submit" class="status-toggle <?= $service['is_active'] ? 'active' : 'inactive' ?>">
                                        <?= $service['is_active'] ? 'Active' : 'Inactive' ?>
                                    </button>
                                </form>
                            </td>
                            <td class="actions">
                                <div style="display: flex; gap: 5px;">
                                    <form method="post" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this service? This action cannot be undone.');">
                                        <input type="hidden" name="id" value="<?= $service['id'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-sm btn-danger" style="padding: 4px 8px;">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h2>Appointments</h2>
        
        <form method="get" action="admin_services.php" class="search-form">
            <input type="text" name="search" placeholder="Search by appointment number" 
                   value="<?= htmlspecialchars($search) ?>" style="flex: 1; min-width: 200px;">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search): ?>
                <a href="admin_services.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Appointment #</th>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Date & Time</th>
                        <th>Price</th>
                        <th>Booked On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($appointments)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px;">
                                No appointments found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($appointments as $appt): ?>
                            <tr>
                                <td><?= htmlspecialchars($appt['appointment_number']) ?></td>
                                <td>
                                    <div><?= htmlspecialchars($appt['customer_name']) ?></div>
                                    <div class="muted" style="font-size: 0.8125rem;"><?= htmlspecialchars($appt['customer_email']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($appt['service']) ?></td>
                                <td><?= date('M j, Y g:i A', strtotime($appt['appointment_datetime'])) ?></td>
                                <td>GH₵<?= number_format($appt['price'], 2) ?></td>
                                <td><?= date('M j, Y', strtotime($appt['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.menu-toggle');
        const menu = document.getElementById('mainMenu');
        
        if (menuToggle && menu) {
            // Initialize aria-expanded
            menuToggle.setAttribute('aria-expanded', 'false');
            
            menuToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                this.setAttribute('aria-expanded', !isExpanded);
                menu.classList.toggle('active');
            });

            // Close when clicking outside
            document.addEventListener('click', function(e) {
                if (!menu.contains(e.target) && !menuToggle.contains(e.target)) {
                    menu.classList.remove('active');
                    menuToggle.setAttribute('aria-expanded', 'false');
                }
            });

            // Close when clicking a link
            menu.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    menu.classList.remove('active');
                    menuToggle.setAttribute('aria-expanded', 'false');
                });
            });
        }
    });
</script>
</body>
</html>
