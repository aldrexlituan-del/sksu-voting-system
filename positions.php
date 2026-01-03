<?php
include '../auth.php';
include '../db.php';

if ($_SESSION['role'] != 'osas') die("Access Denied");

// ----------------------
// Add Position
// ----------------------
if (isset($_POST['add'])) {
    $stmt = $conn->prepare("INSERT INTO positions(position_name, category) VALUES (?, ?)");
    $stmt->bind_param("ss", $_POST['position_name'], $_POST['category']);
    $stmt->execute();
    header("Location: positions.php");
    exit();
}

// ----------------------
// Edit Position
// ----------------------
if (isset($_POST['edit'])) {
    $stmt = $conn->prepare("UPDATE positions SET position_name=?, category=? WHERE id=?");
    $stmt->bind_param("ssi", $_POST['position_name'], $_POST['category'], $_POST['id']);
    $stmt->execute();
    header("Location: positions.php");
    exit();
}

// ----------------------
// Delete Position
// ----------------------
if (isset($_GET['delete'])) {
    // Check if candidates exist under this position
    $check = $conn->query("SELECT 1 FROM candidates WHERE position_id=" . intval($_GET['delete']));
    if ($check->num_rows > 0) {
        $_SESSION['error'] = "Cannot delete position: Candidates exist under this position!";
        header("Location: positions.php");
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM positions WHERE id=?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    $_SESSION['success'] = "Position deleted successfully!";
    header("Location: positions.php");
    exit();
}

// Fetch all positions
$positions = $conn->query("SELECT * FROM positions");

// Get current position data for editing
$current_position = null;
if (isset($_GET['edit'])) {
    $edit_result = $conn->query("SELECT * FROM positions WHERE id=" . intval($_GET['edit']));
    $current_position = $edit_result->fetch_assoc();
}

// Display messages
if (isset($_SESSION['error'])) {
    $error_msg = $_SESSION['error'];
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    $success_msg = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Positions</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
        }
        .table-responsive {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
        }
        .category-badge {
            font-size: 0.85em;
            padding: 0.35em 0.8em;
        }
        .category-osas {
            background-color: #4e54c8;
            color: white;
        }
        .category-sbo {
            background-color: #f093fb;
            color: #333;
        }
        .action-btns .btn {
            min-width: 80px;
            margin: 0 3px;
        }
        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .page-title {
            color: #2c3e50;
            border-left: 5px solid #4e54c8;
            padding-left: 15px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="page-title h3 mb-0">
                            <i class="fas fa-briefcase me-2"></i>Manage Positions
                        </h2>
                        <p class="text-muted mb-0">Add, edit, or delete election positions</p>
                    </div>
                    <a href="dashboard.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>

                <!-- Alert Messages -->
                <?php if(isset($error_msg)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($error_msg) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($success_msg)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= htmlspecialchars($success_msg) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Add/Edit Position Card -->
                <div class="card form-container mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas <?= isset($_GET['edit']) ? 'fa-edit' : 'fa-plus-circle' ?> me-2"></i>
                            <?= isset($_GET['edit']) ? 'Edit Position' : 'Create New Position' ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <input type="hidden" name="id" value="<?= isset($_GET['edit']) ? $_GET['edit'] : '' ?>">
                            
                            <div class="col-md-6">
                                <label for="position_name" class="form-label">
                                    <i class="fas fa-tag me-1"></i>Position Name
                                </label>
                                <input type="text" class="form-control" id="position_name" name="position_name" 
                                    placeholder="e.g., President, VicePresident, Senator" required 
                                    value="<?= isset($_GET['edit']) && $current_position ? htmlspecialchars($current_position['position_name']) : '' ?>">
                                <div class="form-text">Enter the official title for this position</div>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="category" class="form-label">
                                    <i class="fas fa-layer-group me-1"></i>Category
                                </label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="OSAS" 
                                        <?= (isset($_GET['edit']) && $current_position && $current_position['category'] == 'OSAS') ? 'selected' : '' ?>>
                                        OSAS
                                    </option>
                                    <option value="SBO"
                                        <?= (isset($_GET['edit']) && $current_position && $current_position['category'] == 'SBO') ? 'selected' : '' ?>>
                                        SBO
                                    </option>
                                </select>
                                <div class="form-text">Select the organization category</div>
                            </div>
                            
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="d-grid w-100">
                                    <?php if(isset($_GET['edit'])): ?>
                                        <a href="positions.php" class="btn btn-secondary me-2 mb-2">
                                            <i class="fas fa-times me-1"></i> Cancel
                                        </a>
                                    <?php endif; ?>
                                    <button type="submit" name="<?= isset($_GET['edit']) ? 'edit' : 'add' ?>" 
                                        class="btn <?= isset($_GET['edit']) ? 'btn-warning' : 'btn-success' ?>">
                                        <i class="fas <?= isset($_GET['edit']) ? 'fa-save' : 'fa-plus' ?> me-1"></i>
                                        <?= isset($_GET['edit']) ? 'Update' : 'Add' ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Positions List Card -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list-ol me-2"></i>All Positions
                        </h5>
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-layer-group me-1"></i>
                            <?= $positions->num_rows ?> Position(s)
                        </span>
                    </div>
                    <div class="card-body">
                        <?php if($positions->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="60">#</th>
                                            <th>Position Name</th>
                                            <th>Category</th>
                                            <th width="180" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $counter = 1;
                                        $positions->data_seek(0); // Reset pointer
                                        while($p = $positions->fetch_assoc()): 
                                        ?>
                                        <tr>
                                            <td class="text-muted"><?= $counter++ ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-briefcase text-primary me-3"></i>
                                                    <div>
                                                        <strong><?= htmlspecialchars($p['position_name']) ?></strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge category-badge category-<?= strtolower($p['category']) ?>">
                                                    <i class="fas fa-<?= strtolower($p['category']) == 'osas' ? 'university' : 'users' ?> me-1"></i>
                                                    <?= htmlspecialchars($p['category']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center action-btns">
                                                <a href="?edit=<?= $p['id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="Edit Position">
                                                    <i class="fas fa-edit me-1"></i> Edit
                                                </a>
                                                <a href="?delete=<?= $p['id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Are you sure you want to delete this position?\n\nNote: Positions with candidates cannot be deleted.');"
                                                   title="Delete Position">
                                                    <i class="fas fa-trash me-1"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="fas fa-briefcase fa-4x text-muted opacity-50"></i>
                                </div>
                                <h5 class="text-muted mb-3">No Positions Found</h5>
                                <p class="text-muted mb-4">Start by adding your first position using the form above</p>
                                <a href="#positionForm" class="btn btn-primary">
                                    <i class="fas fa-plus-circle me-1"></i> Add First Position
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card border-info">
                            <div class="card-body">
                                <h6 class="card-title text-info">
                                    <i class="fas fa-info-circle me-2"></i>About Categories
                                </h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <span class="badge category-osas me-2">OSAS</span>
                                        <small>Office of Student Affairs and Services positions</small>
                                    </li>
                                    <li>
                                        <span class="badge category-sbo me-2">SBO</span>
                                        <small>Student Body Organization positions</small>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-warning">
                            <div class="card-body">
                                <h6 class="card-title text-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Important Notes
                                </h6>
                                <small class="text-muted">
                                    <i class="fas fa-ban text-danger me-1"></i>
                                    Positions with existing candidates cannot be deleted.<br>
                                    <i class="fas fa-user-plus text-success me-1"></i>
                                    Add candidates to positions from the Candidates page.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            }

            // Auto-focus on first input when editing
            <?php if(isset($_GET['edit'])): ?>
                document.getElementById('position_name').focus();
            <?php endif; ?>
        });
    </script>
</body>
</html>