<?php
include '../auth.php';
include '../db.php';

// Only OSAS allowed
if ($_SESSION['role'] !== 'osas') {
    die("Access Denied");
}

// Check student_id
if (!isset($_GET['student_id'])) {
    die("Student not specified.");
}

$student_id = intval($_GET['student_id']);

// Fetch student info
$studentStmt = $conn->prepare("SELECT fullname FROM users WHERE id = ? AND role='student'");
$studentStmt->bind_param("i", $student_id);
$studentStmt->execute();
$studentResult = $studentStmt->get_result();

if ($studentResult->num_rows === 0) {
    die("Student not found.");
}

$student = $studentResult->fetch_assoc();

// Fetch votes of the student
$votesStmt = $conn->prepare("
    SELECT 
        p.position_name,
        p.category,
        c.fullname AS candidate_name
    FROM votes v
    JOIN positions p ON v.position_id = p.id
    JOIN candidates c ON v.candidate_id = c.id
    WHERE v.student_id = ?
    ORDER BY p.position_name
");
$votesStmt->bind_param("i", $student_id);
$votesStmt->execute();
$votesResult = $votesStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Votes - OSAS Panel</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            padding-top: 20px;
        }
        .page-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .student-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin-bottom: 25px;
        }
        .student-card .card-body {
            padding: 25px;
        }
        .votes-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .votes-card .card-header {
            background: linear-gradient(135deg, #4e54c8 0%, #8f94fb 100%);
            color: white;
            border-bottom: none;
            padding: 15px 25px;
            font-weight: 600;
        }
        .votes-card .card-body {
            padding: 0;
        }
        .table-custom {
            margin-bottom: 0;
        }
        .table-custom thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
            padding: 15px 20px;
            text-transform: uppercase;
            font-size: 0.9em;
            letter-spacing: 0.5px;
        }
        .table-custom tbody td {
            padding: 15px 20px;
            vertical-align: middle;
            border-color: #edf2f7;
        }
        .table-custom tbody tr:hover {
            background-color: #f8f9fa;
        }
        .category-badge {
            font-size: 0.75em;
            padding: 0.35em 0.8em;
            border-radius: 20px;
            font-weight: 500;
        }
        .category-OSAS {
            background-color: rgba(78, 84, 200, 0.1);
            color: #4e54c8;
        }
        .category-SBO {
            background-color: rgba(143, 148, 251, 0.1);
            color: #8f94fb;
        }
        .back-btn {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
            color: white;
        }
        .empty-state {
            padding: 40px 20px;
            text-align: center;
        }
        .empty-state-icon {
            font-size: 3rem;
            color: #6c757d;
            opacity: 0.5;
            margin-bottom: 15px;
        }
        .student-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .student-icon {
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        @media (max-width: 768px) {
            .table-responsive {
                border-radius: 0;
            }
            .table-custom thead th,
            .table-custom tbody td {
                padding: 12px 15px;
            }
            .student-info {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid page-container">
        <div class="row">
            <div class="col-12">
                <!-- Student Info Card -->
                <div class="card student-card">
                    <div class="card-body">
                        <div class="student-info">
                            <div class="student-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div>
                                <h3 class="mb-1">Student Vote Record</h3>
                                <p class="mb-0">
                                    <i class="fas fa-user me-1"></i>
                                    Student: <strong><?= htmlspecialchars($student['fullname']); ?></strong>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($votesResult->num_rows > 0): ?>
                    <!-- Votes Table Card -->
                    <div class="card votes-card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-vote-yea me-2"></i>Vote Details
                            </span>
                            <span class="badge bg-light text-dark">
                                <?= $votesResult->num_rows ?> Vote(s)
                            </span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-custom table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th width="35%">
                                                <i class="fas fa-briefcase me-1"></i>Position
                                            </th>
                                            <th width="40%">
                                                <i class="fas fa-user-tie me-1"></i>Candidate Voted
                                            </th>
                                            <th width="25%">
                                                <i class="fas fa-layer-group me-1"></i>Category
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $votesResult->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <i class="fas fa-briefcase text-secondary me-2"></i>
                                                    <?= htmlspecialchars($row['position_name']); ?>
                                                </td>
                                                <td>
                                                    <i class="fas fa-user-check text-success me-2"></i>
                                                    <?= htmlspecialchars($row['candidate_name']); ?>
                                                </td>
                                                <td>
                                                    <span class="badge category-badge category-<?= htmlspecialchars($row['category']); ?>">
                                                        <i class="fas fa-<?= strtolower($row['category']) == 'osas' ? 'university' : 'users' ?> me-1"></i>
                                                        <?= htmlspecialchars($row['category']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Empty State Card -->
                    <div class="card votes-card mb-4">
                        <div class="card-header">
                            <i class="fas fa-vote-yea me-2"></i>Vote Details
                        </div>
                        <div class="card-body">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <h5 class="text-muted mb-3">No Votes Recorded</h5>
                                <p class="text-muted mb-4">This student has not cast any votes yet.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Back Button -->
                <div class="text-center mt-4">
                    <a href="admin_dashboard.php" class="btn back-btn">
                        <i class="fas fa-arrow-left me-2"></i>Back to Votes
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>