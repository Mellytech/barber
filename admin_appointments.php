<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Ensure user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    header('Location: login.php');
    exit;
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $appointmentId = (int)$_POST['appointment_id'];
    $newStatus = $_POST['status'];
    
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare('UPDATE appointments SET status = ? WHERE id = ?');
        $stmt->execute([$newStatus, $appointmentId]);
        
        // Add success message
        $_SESSION['success'] = 'Appointment status updated successfully.';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } catch (PDOException $e) {
        $error = 'Failed to update appointment status: ' . $e->getMessage();
    }
}

// Get all appointments with user and service details
try {
    $pdo = get_pdo();
    $query = "
        SELECT a.*, u.name as user_name, u.email, u.phone, s.name as service_name, s.price
        FROM appointments a
        JOIN users u ON a.user_id = u.id
        JOIN services s ON a.service_id = s.id
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ";
    $appointments = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Failed to fetch appointments: ' . $e->getMessage();
    $appointments = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .appointment-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .appointment-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .appointment-body {
            padding: 20px;
        }
        .appointment-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .detail-item {
            margin-bottom: 8px;
        }
        .detail-label {
            font-weight: 600;
            color: #6c757d;
            display: block;
            margin-bottom: 3px;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-confirmed { background-color: #d4edda; color: #155724; }
        .status-completed { background-color: #d1ecf1; color: #0c5460; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
        }
        .btn-primary { background-color: #007bff; color: white; }
        .btn-success { background-color: #28a745; color: white; }
        .btn-danger { background-color: #dc3545; color: white; }
        .btn-warning { background-color: #ffc107; color: #212529; }
        .btn i { font-size: 12px; }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>
    
    <div class="admin-container">
        <div class="admin-sidebar">
            <?php include 'includes/admin_sidebar.php'; ?>
        </div>
        
        <main class="admin-content">
            <div class="page-header">
                <h1><i class="fas fa-calendar-alt"></i> Manage Appointments</h1>
                <div class="header-actions">
                    <a href="add_appointment.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Appointment
                    </a>
                </div>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo htmlspecialchars($_SESSION['success']); 
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($appointments)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times fa-3x mb-3"></i>
                    <h3>No appointments found</h3>
                    <p>There are no appointments scheduled yet.</p>
                </div>
            <?php else: ?>
                <div class="appointments-list">
                    <?php foreach ($appointments as $appointment): ?>
                        <div class="appointment-card">
                            <div class="appointment-header">
                                <div>
                                    <span class="status-badge status-<?php echo htmlspecialchars($appointment['status']); ?>">
                                        <?php echo ucfirst(htmlspecialchars($appointment['status'])); ?>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted">
                                        <?php 
                                        $appointmentDate = new DateTime($appointment['appointment_date'] . ' ' . $appointment['appointment_time']);
                                        echo $appointmentDate->format('M j, Y \a\t g:i A');
                                        ?>
                                    </small>
                                </div>
                            </div>
                            <div class="appointment-body">
                                <div class="appointment-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Customer</span>
                                        <div><?php echo htmlspecialchars($appointment['user_name']); ?></div>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Contact</span>
                                        <div>
                                            <div><?php echo htmlspecialchars($appointment['email']); ?></div>
                                            <?php if (!empty($appointment['phone'])): ?>
                                                <div><?php echo htmlspecialchars($appointment['phone']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Service</span>
                                        <div>
                                            <div><?php echo htmlspecialchars($appointment['service_name']); ?></div>
                                            <div class="text-muted">$<?php echo number_format($appointment['price'], 2); ?></div>
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Notes</span>
                                        <div><?php echo !empty($appointment['notes']) ? nl2br(htmlspecialchars($appointment['notes'])) : 'No notes'; ?></div>
                                    </div>
                                </div>
                                
                                <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to update this appointment status?');">
                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                    <div class="action-buttons">
                                        <select name="status" class="form-control form-control-sm d-inline-block w-auto" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $appointment['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $appointment['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="completed" <?php echo $appointment['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $appointment['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                        <a href="edit_appointment.php?id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="delete_appointment.php?id=<?php echo $appointment['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this appointment? This action cannot be undone.');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <?php include 'includes/admin_footer.php'; ?>
    
    <script>
        // Auto-submit form when status changes
        document.querySelectorAll('select[name="status"]').forEach(select => {
            select.addEventListener('change', function() {
                if (confirm('Are you sure you want to update the status of this appointment?')) {
                    this.form.submit();
                } else {
                    // Reset to original value if cancelled
                    this.value = this.getAttribute('data-original-value');
                }
            });
            
            // Store original value
            select.setAttribute('data-original-value', select.value);
        });
    </script>
</body>
</html>
