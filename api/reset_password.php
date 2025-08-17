<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight request immediately
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require 'db_connection.php'; // Ensure this path is correct

// Handle POST request (API endpoint)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validate input
        if (!$data || !isset($data['token']) || !isset($data['password']) || !isset($data['password_confirm'])) {
            http_response_code(400);
            echo json_encode(['error' => 'All fields are required']);
            exit;
        }

        if ($data['password'] !== $data['password_confirm']) {
            http_response_code(400);
            echo json_encode(['error' => 'Passwords do not match']);
            exit;
        }

        $token = $data['token'];
        $password = password_hash($data['password'], PASSWORD_DEFAULT);

        // Start transaction
        $pdo->beginTransaction();

        // Validate token
        $stmt = $pdo->prepare("SELECT email FROM password_resets 
                              WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $resetRequest = $stmt->fetch();

        if (!$resetRequest) {
            http_response_code(410);
            echo json_encode(['error' => 'Invalid or expired token']);
            $pdo->rollBack();
            exit;
        }

        // Update password
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$password, $resetRequest['email']]);

        // Delete token
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);

        $pdo->commit();

        echo json_encode(['message' => 'Password updated successfully']);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle GET request (Show HTML form)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header("Content-Type: text/html"); // Switch to HTML for form display
    $token = $_GET['token'] ?? null;

    try {
        if (!$token) {
            die("Invalid password reset link");
        }

        // Validate token
        $stmt = $pdo->prepare("SELECT * FROM password_resets 
                             WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $resetRequest = $stmt->fetch();

        if (!$resetRequest) {
            die("Invalid or expired token. Please request a new reset link.");
        }

        // Show HTML form
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Reset Password</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { background: #f8f9fa; }
                .reset-card { max-width: 400px; margin: 5rem auto; padding: 2rem; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="reset-card bg-white rounded">
                    <h2 class="mb-4 text-center">Reset Password</h2>
                    <form id="resetForm">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirm" class="form-control" required minlength="8">
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                    </form>
                    <div id="message" class="mt-3"></div>
                </div>
            </div>

            <script>
                document.getElementById('resetForm').addEventListener('submit', async (e) => {
                    e.preventDefault();
                    
                    const formData = {
                        token: document.querySelector('[name="token"]').value,
                        password: document.querySelector('[name="password"]').value,
                        password_confirm: document.querySelector('[name="password_confirm"]').value
                    };

                    try {
                        const response = await fetch('reset_password.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(formData)
                        });

                        const result = await response.json();
                        
                        const messageDiv = document.getElementById('message');
                        messageDiv.innerHTML = `
                            <div class="alert alert-${response.ok ? 'success' : 'danger'}">
                                ${result.message || result.error}
                            </div>
                        `;

                        if (response.ok) {
                            document.getElementById('resetForm').reset();
                            setTimeout(() => {
                                window.location.href = 'https://dashboard.spplindia.org/login'; // Redirect to login
                            }, 2000);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        document.getElementById('message').innerHTML = `
                            <div class="alert alert-danger">
                                An error occurred. Please try again.
                            </div>
                        `;
                    }
                });
            </script>
        </body>
        </html>
        <?php
    } catch (PDOException $e) {
        die("<div class='alert alert-danger'>Database error: " . htmlspecialchars($e->getMessage()) . "</div>");
    }
    exit;
}