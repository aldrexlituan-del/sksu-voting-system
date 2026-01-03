<?php
include '../auth.php';
include '../db.php';

if ($_SESSION['role'] !== 'student') {
    die("Access Denied: Students only");
}

$student_id = $_SESSION['user_id'];
$positions = $conn->query("SELECT * FROM positions WHERE status='open'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-custom {
            background-color: #2c3e50;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        
        .navbar-custom .navbar-nav .nav-link {
            color: rgba(255,255,255,.85);
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .navbar-custom .navbar-nav .nav-link:hover {
            color: #fff;
            background-color: rgba(255,255,255,.1);
        }
        
        .navbar-brand {
            color: #fff !important;
            font-weight: 600;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            color: white;
            border-radius: 10px;
            border: none;
            margin-bottom: 2rem;
        }
        
        .position-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
            border: none;
            transition: transform 0.3s;
        }
        
        .position-card:hover {
            transform: translateY(-2px);
        }
        
        .position-header {
            background-color: #f1f5f9;
            border-bottom: 2px solid #e0e0e0;
            padding: 1.25rem 1.5rem;
            border-radius: 10px 10px 0 0;
        }
        
        .position-body {
            padding: 1.5rem;
        }
        
        .candidate-option {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .candidate-option:hover {
            background-color: #f8f9fa;
            border-color: #3498db;
        }
        
        .candidate-option.selected {
            background-color: rgba(52, 152, 219, 0.1);
            border-color: #3498db;
        }
        
        .candidate-radio {
            margin-right: 1rem;
        }
        
        .candidate-info {
            flex-grow: 1;
        }
        
        .candidate-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }
        
        .already-voted {
            background-color: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #c3e6cb;
            text-align: center;
        }
        
        .category-badge {
            background-color: #3498db;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
            color: white;
        }
        
        .logout-btn {
            background-color: #e74c3c;
            border-color: #e74c3c;
            color: white;
        }
        
        .logout-btn:hover {
            background-color: #c0392b;
            border-color: #c0392b;
            color: white;
        }
        
        .page-header {
            color: #2c3e50;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .no-positions {
            text-align: center;
            padding: 3rem;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .no-positions-icon {
            font-size: 3rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .position-card {
                margin-bottom: 1rem;
            }
            
            .candidate-option {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .candidate-radio {
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person-circle me-2" viewBox="0 0 16 16">
                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                    <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
                </svg>
                Student Dashboard
            </a>
            <div class="navbar-nav">
                <a class="nav-link active" href="#">Vote</a>
                <a class="nav-link" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Welcome Card -->
        <div class="card welcome-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h1 class="h4 mb-2">Student Dashboard</h1>
                        <p class="mb-0 opacity-75">Welcome, <?= htmlspecialchars($_SESSION['fullname']); ?></p>
                    </div>
                    <div class="col-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-person-check" viewBox="0 0 16 16">
                            <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
                            <path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Voting Form -->
        <form method="POST" action="vote.php">
            <?php 
            $hasOpenPositions = false;
            $positions->data_seek(0); // Reset pointer
            
            if ($positions->num_rows > 0): 
                $hasOpenPositions = true;
                while($pos = $positions->fetch_assoc()): 
            ?>
                <div class="position-card">
                    <div class="position-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="h5 mb-0"><?= htmlspecialchars($pos['position_name']); ?></h3>
                            <span class="category-badge"><?= htmlspecialchars($pos['category']); ?></span>
                        </div>
                    </div>
                    <div class="position-body">
                        <?php
                        $pid = $pos['id'];
                        $check = $conn->query("SELECT 1 FROM votes WHERE student_id=$student_id AND position_id=$pid");

                        if ($check->num_rows > 0):
                        ?>
                            <div class="already-voted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-check-circle me-2" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                    <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                                </svg>
                                <b>You have already voted for this position</b>
                            </div>
                            <?php continue; ?>
                        <?php endif; ?>

                        <?php
                        $candidates = $conn->query("SELECT * FROM candidates WHERE position_id=$pid");
                        if ($candidates->num_rows > 0):
                            while ($cand = $candidates->fetch_assoc()):
                        ?>
                            <label class="candidate-option" onclick="selectCandidate(this, <?= $pid ?>)">
                                <div class="candidate-radio">
                                    <input type="radio" name="vote[<?= $pid ?>]" value="<?= $cand['id'] ?>" required class="form-check-input">
                                </div>
                                <div class="candidate-info">
                                    <div class="candidate-name">
                                        <?= htmlspecialchars($cand['fullname']); ?>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#3498db" class="bi bi-person me-1" viewBox="0 0 16 16">
                                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm1-3.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 .5.5z"/>
                                        </svg>
                                        <small class="text-muted">Candidate</small>
                                    </div>
                                </div>
                            </label>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                            <div class="alert alert-warning">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle me-2" viewBox="0 0 16 16">
                                    <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.16.16 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.16.16 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"/>
                                    <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
                                </svg>
                                No candidates available for this position.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php 
                endwhile; 
            else: 
            ?>
                <div class="no-positions">
                    <div class="no-positions-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-clipboard-x" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M6.146 7.146a.5.5 0 0 1 .708 0L8 8.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 9l1.147 1.146a.5.5 0 0 1-.708.708L8 9.707l-1.146 1.147a.5.5 0 0 1-.708-.708L7.293 9 6.146 7.854a.5.5 0 0 1 0-.708"/>
                            <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1z"/>
                            <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0z"/>
                        </svg>
                    </div>
                    <h4 class="text-muted">No Voting Positions Available</h4>
                    <p class="text-muted mb-0">There are currently no open positions for voting.</p>
                </div>
            <?php endif; ?>

            <?php if ($hasOpenPositions): ?>
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-submit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle me-2" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                            <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                        </svg>
                        Submit Votes
                    </button>
                </div>
            <?php endif; ?>
        </form>

        <div class="text-center mt-4">
            <a href="../logout.php" class="btn logout-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-right me-1" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z"/>
                    <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/>
                </svg>
                Logout
            </a>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectCandidate(element, positionId) {
            // Remove selected class from all candidates in this position
            const positionCard = element.closest('.position-card');
            positionCard.querySelectorAll('.candidate-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selected class to clicked candidate
            element.classList.add('selected');
            
            // Trigger the radio button click
            const radio = element.querySelector('input[type="radio"]');
            radio.checked = true;
        }
        
        // Initialize selected state on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
                const candidateOption = radio.closest('.candidate-option');
                if (candidateOption) {
                    candidateOption.classList.add('selected');
                }
            });
        });
    </script>
</body>
</html>