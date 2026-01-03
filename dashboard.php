<?php
include '../auth.php';
include '../db.php';

// ADMIN ONLY
if ($_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

// Get all votes with student info
$result = $conn->query("
    SELECT 
        u.fullname AS student,
        p.position_name,
        c.fullname AS candidate,
        p.category
    FROM votes v
    JOIN users u ON v.student_id = u.id
    JOIN positions p ON v.position_id = p.id
    JOIN candidates c ON v.candidate_id = c.id
    ORDER BY u.fullname, p.position_name
");

// Get vote statistics
$stats_result = $conn->query("
    SELECT 
        COUNT(DISTINCT student_id) as total_voters,
        COUNT(*) as total_votes,
        COUNT(DISTINCT position_id) as positions_voted
    FROM votes
");
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Votes - Admin Panel</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 10px;
            border: none;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .table-container {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        .table thead th {
            background-color: #4e54c8;
            color: white;
            border-bottom: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9em;
            letter-spacing: 0.5px;
        }
        .category-badge {
            font-size: 0.75em;
            padding: 0.25em 0.8em;
        }
        .category-OSAS {
            background-color: #4e54c8;
            color: white;
        }
        .category-SBO {
            background-color: #f093fb;
            color: #333;
        }
        .back-btn {
            position: relative;
            padding-left: 2.5rem;
            transition: all 0.3s ease;
        }
        .back-btn:hover {
            transform: translateX(-5px);
            padding-left: 3rem;
        }
        .vote-row:hover {
            background-color: rgba(102, 126, 234, 0.05);
            cursor: pointer;
        }
        .empty-state {
            padding: 3rem 1rem;
            background: white;
            border-radius: 10px;
        }
        .logout-btn {
            background: linear-gradient(135deg, #ff6b6b 0%, #ff8e53 100%);
            border: none;
            color: white;
        }
        .logout-btn:hover {
            background: linear-gradient(135deg, #ff5252 0%, #ff7a45 100%);
            color: white;
            transform: translateX(-5px);
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 text-dark mb-1">
                            <i class="fas fa-vote-yea text-primary me-2"></i>Student Votes Dashboard
                        </h1>
                        <p class="text-muted mb-0">View all recorded votes in the system</p>
                    </div>
                    <!-- Back to Login Button -->
                    <a href="../logout.php" class="btn logout-btn back-btn" id="backButton">
                        <i class="fas fa-sign-out-alt me-2"></i> Back to Login
                    </a>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card stat-card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Voters
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= $stats['total_voters'] ?? 0 ?>
                                        </div>
                                        <div class="text-muted small">Students who voted</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-primary opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card stat-card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Total Votes
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= $stats['total_votes'] ?? 0 ?>
                                        </div>
                                        <div class="text-muted small">Votes cast</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-success opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card stat-card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Positions Voted
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= $stats['positions_voted'] ?? 0 ?>
                                        </div>
                                        <div class="text-muted small">Different positions</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-briefcase fa-2x text-warning opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Votes Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>All Student Votes
                        </h5>
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-database me-1"></i>
                            <?= $result->num_rows ?> Vote(s)
                        </span>
                    </div>
                    <div class="card-body">
                        <?php if ($result->num_rows > 0): ?>
                            <div class="table-container">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th width="25%">
                                                    <i class="fas fa-user-graduate me-1"></i>Student Name
                                                </th>
                                                <th width="25%">
                                                    <i class="fas fa-briefcase me-1"></i>Position
                                                </th>
                                                <th width="25%">
                                                    <i class="fas fa-user-tie me-1"></i>Candidate Voted
                                                </th>
                                                <th width="15%">
                                                    <i class="fas fa-layer-group me-1"></i>Category
                                                </th>
                                                <th width="10%" class="text-center">
                                                    <i class="fas fa-info-circle me-1"></i>Status
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $prev_student = '';
                                            $row_count = 0;
                                            $result->data_seek(0); // Reset pointer
                                            while ($row = $result->fetch_assoc()): 
                                                $row_count++;
                                                $is_new_student = $prev_student != $row['student'];
                                                $prev_student = $row['student'];
                                            ?>
                                                <tr class="vote-row <?= $is_new_student ? 'border-top-light' : '' ?>">
                                                    <td>
                                                        <?php if($is_new_student): ?>
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-circle-sm bg-primary text-white me-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 36px; height: 36px;">
                                                                    <?= strtoupper(substr($row['student'], 0, 1)) ?>
                                                                </div>
                                                                <div>
                                                                    <strong><?= htmlspecialchars($row['student']); ?></strong>
                                                                </div>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="ms-5 ps-2 text-muted">
                                                                <small>Continued...</small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <i class="fas fa-briefcase text-secondary me-2"></i>
                                                        <?= htmlspecialchars($row['position_name']); ?>
                                                    </td>
                                                    <td>
                                                        <i class="fas fa-user-check text-success me-2"></i>
                                                        <span class="text-dark"><?= htmlspecialchars($row['candidate']); ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge category-badge category-<?= htmlspecialchars($row['category']); ?>">
                                                            <i class="fas fa-<?= strtolower($row['category']) == 'osas' ? 'university' : 'users' ?> me-1"></i>
                                                            <?= htmlspecialchars($row['category']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-success rounded-pill">
                                                            <i class="fas fa-check me-1"></i> Voted
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Summary -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-info d-flex align-items-center">
                                        <i class="fas fa-chart-pie fa-2x me-3 opacity-75"></i>
                                        <div>
                                            <h6 class="alert-heading mb-1">Voting Summary</h6>
                                            Total of <strong><?= $row_count ?></strong> votes recorded from 
                                            <strong><?= $stats['total_voters'] ?? 0 ?></strong> unique voters across 
                                            <strong><?= $stats['positions_voted'] ?? 0 ?></strong> positions.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="empty-state text-center py-5">
                                <div class="mb-4">
                                    <i class="fas fa-vote-yea fa-4x text-muted opacity-25 mb-3"></i>
                                    <h4 class="text-muted">No Votes Recorded Yet</h4>
                                    <p class="text-muted mb-4">No students have cast their votes yet. Votes will appear here once the voting begins.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i> Admin View Only
                        </small>
                        <!-- Footer Back to Login Button -->
                        <a href="../logout.php" class="btn logout-btn back-btn">
                            <i class="fas fa-sign-out-alt me-2"></i> Back to Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Enhanced back button with instant action
        document.addEventListener('DOMContentLoaded', function() {
            // Add click handler to all back buttons
            document.querySelectorAll('.back-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Add loading state
                    const originalHTML = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Redirecting...';
                    this.classList.add('disabled');
                    
                    // Clear session and navigate to login
                    setTimeout(() => {
                        window.location.href = '../logout.php';
                    }, 500);
                    
                    // Restore button after 3 seconds in case navigation fails
                    setTimeout(() => {
                        this.innerHTML = originalHTML;
                        this.classList.remove('disabled');
                    }, 3000);
                });
            });
            
            // Auto-focus back button for keyboard navigation
            document.getElementById('backButton').focus();
            
            // Add keyboard shortcut (Alt + L for logout/login)
            document.addEventListener('keydown', function(e) {
                if (e.altKey && (e.key === 'l' || e.key === 'L')) {
                    e.preventDefault();
                    window.location.href = '../logout.php';
                }
            });
        });
    </script>
</body>
</html>