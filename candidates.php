<?php
include '../auth.php';
include '../db.php';

if ($_SESSION['role'] != 'osas') die("Access Denied");

// ----------------------
// Add Candidate
// ----------------------
if (isset($_POST['add'])) {
    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
        $imagePath = $targetDir . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
    }

    $stmt = $conn->prepare("INSERT INTO candidates(fullname, position_id, image) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $_POST['fullname'], $_POST['position_id'], $imagePath);
    $stmt->execute();
    header("Location: candidates.php");
    exit();
}

// ----------------------
// Edit Candidate
// ----------------------
if (isset($_POST['edit'])) {
    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
        $imagePath = $targetDir . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
    }

    if ($imagePath) {
        $stmt = $conn->prepare("UPDATE candidates SET fullname=?, position_id=?, image=? WHERE id=?");
        $stmt->bind_param("sisi", $_POST['fullname'], $_POST['position_id'], $imagePath, $_POST['id']);
    } else {
        $stmt = $conn->prepare("UPDATE candidates SET fullname=?, position_id=? WHERE id=?");
        $stmt->bind_param("sii", $_POST['fullname'], $_POST['position_id'], $_POST['id']);
    }
    $stmt->execute();
    header("Location: candidates.php");
    exit();
}

// ----------------------
// Delete Candidate
// ----------------------
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM votes WHERE candidate_id=?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM candidates WHERE id=?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    header("Location: candidates.php");
    exit();
}

// Fetch all candidates
$candidates = $conn->query("
    SELECT c.id, c.fullname, c.image, p.position_name, p.id as position_id
    FROM candidates c
    JOIN positions p ON c.position_id = p.id
");

// Fetch positions for dropdown
$positions = $conn->query("SELECT * FROM positions");

// Store positions for reuse
$positions_data = [];
while($p = $positions->fetch_assoc()) {
    $positions_data[] = $p;
}

// Get current candidate data for editing
$current_candidate = null;
if (isset($_GET['edit'])) {
    $edit_result = $conn->query("SELECT * FROM candidates WHERE id=" . intval($_GET['edit']));
    $current_candidate = $edit_result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .candidate-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }
        .table-responsive {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .action-btns .btn {
            margin: 0 3px;
        }
        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h3 text-primary">
                        <i class="fas fa-users me-2"></i>Manage Candidates
                    </h2>
                    <a href="dashboard.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>

                <!-- Candidate Form Card -->
                <div class="card form-container mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas <?= isset($_GET['edit']) ? 'fa-edit' : 'fa-plus' ?> me-2"></i>
                            <?= isset($_GET['edit']) ? 'Edit Candidate' : 'Add New Candidate' ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" class="row g-3">
                            <input type="hidden" name="id" value="<?= isset($_GET['edit']) ? $_GET['edit'] : '' ?>">
                            
                            <div class="col-md-5">
                                <label for="fullname" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" 
                                    placeholder="Enter candidate's full name" required 
                                    value="<?= isset($_GET['edit']) && $current_candidate ? htmlspecialchars($current_candidate['fullname']) : '' ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="position_id" class="form-label">Position</label>
                                <select class="form-select" id="position_id" name="position_id" required>
                                    <option value="">Select Position</option>
                                    <?php foreach($positions_data as $p): ?>
                                        <option value="<?= $p['id'] ?>" 
                                            <?= (isset($_GET['edit']) && $current_candidate && $current_candidate['position_id'] == $p['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['position_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="image" class="form-label">Candidate Photo</label>
                                <input type="file" class="form-control" id="image" name="image" 
                                    <?= isset($_GET['edit']) ? '' : 'required' ?>>
                                <?php if(isset($_GET['edit']) && $current_candidate && $current_candidate['image']): ?>
                                    <small class="text-muted">Leave empty to keep current image</small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <?php if(isset($_GET['edit'])): ?>
                                        <a href="candidates.php" class="btn btn-secondary me-2">
                                            <i class="fas fa-times me-1"></i> Cancel
                                        </a>
                                    <?php endif; ?>
                                    <button type="submit" name="<?= isset($_GET['edit']) ? 'edit' : 'add' ?>" 
                                        class="btn <?= isset($_GET['edit']) ? 'btn-warning' : 'btn-success' ?>">
                                        <i class="fas <?= isset($_GET['edit']) ? 'fa-save' : 'fa-plus' ?> me-1"></i>
                                        <?= isset($_GET['edit']) ? 'Update Candidate' : 'Add Candidate' ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Candidates List Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Candidate List
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="100">Photo</th>
                                        <th>Name</th>
                                        <th>Position</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($c = $candidates->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php if ($c['image']): ?>
                                                <img src="<?= htmlspecialchars($c['image']) ?>" 
                                                    class="candidate-img" 
                                                    alt="<?= htmlspecialchars($c['fullname']) ?>">
                                            <?php else: ?>
                                                <div class="candidate-img bg-secondary d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle">
                                            <strong><?= htmlspecialchars($c['fullname']) ?></strong>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge bg-info">
                                                <?= htmlspecialchars($c['position_name']) ?>
                                            </span>
                                        </td>
                                        <td class="align-middle action-btns">
                                            <a href="?edit=<?= $c['id'] ?>" 
                                                class="btn btn-sm btn-outline-primary"
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?= $c['id'] ?>" 
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Are you sure you want to delete this candidate? This will also remove all associated votes.');"
                                                title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    <?php if($candidates->num_rows == 0): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <i class="fas fa-user-slash fa-2x text-muted mb-3"></i>
                                            <p class="text-muted">No candidates found. Add your first candidate using the form above.</p>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        });
    </script>
</body>
</html>