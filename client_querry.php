

<?php
// Database connection
include('config.php');

$added = false;

if (isset($_POST['submit'])) {
    // Retrieve form data
    $u_desig = $_POST['u_desig'];
    $u_f_name = $_POST['u_f_name'];
    $u_org_name = $_POST['u_org_name'];
    $u_positn = $_POST['u_positn'];
    $u_email = $_POST['u_email'];
    $u_phone = $_POST['u_phone'];
    $u_altr_mail = $_POST['u_altr_mail'];
    $u_proj_type = $_POST['u_proj_type'];
    $u_service = $_POST['u_service'];
    $u_spsfy_service = $_POST['u_spsfy_service'];
    $u_locatn = $_POST['u_locatn'];
    $u_budget = $_POST['u_budget'];
    $u_querry = $_POST['u_other_info'];
    $u_contct_methd = $_POST['u_contct_methd'];

    // PDF upload setup (Optional)
    $upload_dir = 'upload_pdfs/';
    
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            die("Failed to create upload directory.");
        }
    }

    $pdfFile = null;
    $pdfTarget = null;
    if (isset($_FILES['pdfFile']) && $_FILES['pdfFile']['error'] === UPLOAD_ERR_OK) {
        $pdfFile = $_FILES['pdfFile']['name'];
        $pdfTarget = $upload_dir . basename($pdfFile);

        if (!move_uploaded_file($_FILES['pdfFile']['tmp_name'], $pdfTarget)) {
            echo "Failed to upload the PDF.";
        }
    }

    // Insert data into the database
    $insert_data = "INSERT INTO clientquerry (u_desig, u_f_name, u_org_name, u_positn, u_email, u_phone, u_altr_mail, u_proj_type, u_service, u_spsfy_service, u_locatn, u_budget, u_querry, u_contct_methd, pdfFile, pdfuploaded) 
                    VALUES ('$u_desig','$u_f_name','$u_org_name','$u_positn','$u_email','$u_phone','$u_altr_mail','$u_proj_type','$u_service','$u_spsfy_service','$u_locatn','$u_budget','$u_querry','$u_contct_methd','$pdfFile', NOW())";

    $run_data = mysqli_query($con, $insert_data);

    if ($run_data) {
        $added = true;

        // Email configuration
        $to_user = $u_email;
        $to_admin = 'dir-ops@spplindia.org'; // Admin email address
        $subject_admin = "New client query form submitted by $u_f_name";
        $subject_user = "Confirmation client query form submission";

        // Prepare user message
        $message_user = "Dear $u_f_name,\n\nThank you for submitting your concern. We have received your details and will get back to you shortly.\n\nRegards,\nTeam SPPL India";

        // Email headers for user
        $headers_user = "From: no-reply@spplindia.org\r\n"; // Use domain-based email address
        $headers_user .= "Reply-To: dir-ops@spplindia.org\r\n";
        $headers_user .= "Return-Path: no-reply@spplindia.org\r\n"; // Set a valid Return-Path
        $headers_user .= "MIME-Version: 1.0\r\n";
        $headers_user .= "Content-Type: text/plain; charset=UTF-8\r\n";

        // Prepare admin message
        $message_admin = "New Membership Form Submitted by $u_f_name\n\n";
        $message_admin .= "Designation: $u_desig\n";
        $message_admin .= "Full Name: $u_f_name\n";
        $message_admin .= "Organisation: $u_org_name\n";
        $message_admin .= "Position: $u_positn\n";
        $message_admin .= "Email: $u_email\n";
        $message_admin .= "Phone: $u_phone\n";
        $message_admin .= "Alternate Email: $u_altr_mail\n";
        $message_admin .= "Project Type: $u_proj_type\n";
        $message_admin .= "Service Required: $u_service\n";
        $message_admin .= "Specified Service: $u_spsfy_service\n";
        $message_admin .= "Location: $u_locatn\n";
        $message_admin .= "Budget: $u_budget\n";
        $message_admin .= "Query: $u_querry\n";
        $message_admin .= "Preferred Contact Method: $u_contct_methd\n";

        // Email headers for admin
        if ($pdfFile && file_exists($pdfTarget)) {
            // Email with attachment
            $boundary = md5(time());
            $headers_admin = "From: Form-submited@spplindia.org\r\n";
            $headers_admin .= "Reply-To: dir-ops@spplindia.org\r\n";
            $headers_admin .= "MIME-Version: 1.0\r\n";
            $headers_admin .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

            // Email body for admin with PDF attachment
            $body_admin = "--$boundary\r\n";
            $body_admin .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
            $body_admin .= $message_admin . "\r\n";

            // Attach the PDF
            $pdf_content = chunk_split(base64_encode(file_get_contents($pdfTarget)));
            $body_admin .= "--$boundary\r\n";
            $body_admin .= "Content-Type: application/pdf; name=\"$pdfFile\"\r\n";
            $body_admin .= "Content-Disposition: attachment; filename=\"$pdfFile\"\r\n";
            $body_admin .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body_admin .= $pdf_content . "\r\n";
            $body_admin .= "--$boundary--";

        } else {
            // Email without attachment
            $headers_admin = "From: Form-submited@spplindia.org\r\n";
            $headers_admin .= "Reply-To: y\r\n";
            $headers_admin .= "MIME-Version: 1.0\r\n";
            $headers_admin .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $body_admin = $message_admin;
        }

        // Send the emails
        $email_to_user_sent = mail($to_user, $subject_user, $message_user, $headers_user, "-f no-reply@spplindia.org");
        $email_to_admin_sent = mail($to_admin, $subject_admin, $body_admin, $headers_admin, "-f no-reply@spplindia.org");

        // Check if both emails were sent successfully
        if ($email_to_user_sent && $email_to_admin_sent) {
            echo "Email successfully sent to the user and admin!";
        } else {
            echo "Failed to send one or both emails. Please check your configuration.";
        }
    } else {
        echo "Data not inserted into the database.";
    }
}
?>




<style>
    body {
        background-image: url('images/Picture4.jpg');
        background-size: cover;
        background-position: center;
        color: #fff;
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
    }

    .container {
        max-width: 100%;
        margin: 0 auto;
        padding: 20px;
    }

    .modal-content {
        background-color: rgba(0, 0, 0, 0.7);
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }

    /* Styling for form inputs */
    .form-control {
        width: 100%;
        padding: 12px;
        margin-bottom: 10px;
        border-radius: 4px;
        background-color: #f1f1f1;
        color: #333;
    }

    /* Button Styling */
    .btn {
        padding: 12px;
        width: 100%;
        font-size: 16px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn:hover {
        background-color: #45a049;
    }

    /* Media query for mobile devices */
    @media (max-width: 768px) {
        .container {
            width: 90%;
            padding: 15px;
        }

        .modal-content {
            padding: 20px;
        }

        .row {
            margin: 0;
        }

        .col-md-1,
        .col-md-4,
        .col-md-5,
        .col-md-6,
        .col-md-8,
        .col-md-3 {
            width: 100%;
            margin-bottom: 15px;
        }

        .btn {
            width: 100%;
        }

        label {
            font-size: 14px;
        }

        .form-control {
            font-size: 14px;
        }
    }
</style>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SPPL client querry</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="img/logo3.png" rel="icon">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <!-- Custom CSS -->
    <style>
        body {
            background-image: url('images/Picture4.jpg');
            background-size: cover;
            background-position: center;
            color: #fff;
            margin: 0;
            padding: 0;
        }

        .form-container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px 30px;
            background-color: rgba(255, 255, 255, 0.9);
            color: #333;
            border-radius: 8px;
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .form-group label {
            color: #4b4b4b;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            font-size: 18px;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 15px 20px;
            }

            .btn-submit {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>

<div class="container form-container">
    <?php if($added): ?>
        <div class="alert alert-success" role="alert">
            Your form has been successfully submitted to Sanrachna Prahari Pvt Ltd.
            <a href="https://spplindia.org/" target="_blank" style="font-size: 15px;">Click to get back to SPPL homepage</a>
        </div>
    <?php endif; ?>

    <h2 style= "font-size: 30px; color: blue;"><strong>Sanrachna Prahari Pvt Ltd Client Querry Form</strong></h2>
        <h2 style= "font-size: 14px"><strong>Designed for existing or prospective clients to inquire about SPPL’s <br>products, services, and consulting.</strong></h2>


    <form method="POST" action="" enctype="multipart/form-data">
        <!-- First Row -->
        <div class="row">
            <div class="col-xs-12 col-sm-3">
                <div class="form-group">
                    <label for="u_desig">Title <span style="color: red;">*</span></label>
                    <select id="u_desig" name="u_desig" class="form-control" required>
                        <option>Select</option>
                        <option>Prof.</option>
                        <option>Dr.</option>
                        <option>Er.</option>
                        <option>Mr.</option>
                        <option>Ms.</option>
                        <option>Mrs.</option>
                    </select>
                </div>
            </div>
            <div class="col-xs-12 col-sm-9">
                <div class="form-group">
                    <label for="u_f_name">Full Name (Block Letters)<span style="color: red;">*</span></label>
                    <input type="text" class="form-control" name="u_f_name" placeholder="Enter Name" maxlength="60" required>
                </div>
            </div>
        </div>

        <!-- Second Row -->
        <div class="row">
            <div class="col-xs-12">
                <div class="form-group">
                    <label for="u_org_name">Company / Organisation<span style="color: red;">*</span></label>
                    <input type="text" class="form-control" name="u_org_name" maxlength="60" required>
                </div>
            </div>
        </div>

        <!-- Third Row -->
        <div class="row">
            <div class="col-xs-12 col-sm-6">
                <div class="form-group">
                    <label for="u_positn">Position<span style="color: red;">*</span></label>
                    <input type="text" class="form-control" name="u_positn" maxlength="30" required>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="form-group">
                    <label for="u_email">Email ID<span style="color: red;">*</span></label>
                    <input type="email" class="form-control" name="u_email" maxlength="50" required>
                </div>
            </div>
        </div>

        <!-- Fourth Row -->
        <div class="row">
            <div class="col-xs-12 col-sm-6">
                <div class="form-group">
                    <label for="u_phone">Phone No<span style="color: red;">*</span></label>
                    <input type="tel" class="form-control" name="u_phone" maxlength="30" required>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="form-group">
                    <label for="u_altr_mail">Alternate Email ID</label>
                    <input type="email" class="form-control" name="u_altr_mail" maxlength="50">
                </div>
            </div>
        </div>

        <!-- Fifth Row -->
        <div class="row">
            <div class="col-xs-12 col-sm-6">
                <div class="form-group">
                    <label for="u_proj_type">Type of Project<span style="color: red;">*</span></label>
                    <input type="text" class="form-control" name="u_proj_type" maxlength="50" required>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="form-group">
                    <label for="u_service">Service Inquiry<span style="color: red;">*</span></label>
                    <select id="u_service" name="u_service" class="form-control" required>
                        <option>Select</option>
                        <option>Structural Health Monitoring</option>
                        <option>Research & Development</option>
                        <option>Training Programs</option>
                        <option>Consultation Services</option>
                        <option>Retrofitting & Maintenance</option>
                        <option>Others</option>
                    </select>
                </div>
            </div>
        </div>


        <!-- Seventh Row -->
        <div class="row">
            <div class="col-xs-12 col-sm-6">
                <div class="form-group">
                    <label for="u_locatn">Project Location<span style="color: red;">*</span></label>
                    <input type="text" class="form-control" name="u_locatn" maxlength="100" required>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="form-group">
                    <label for="u_budget">Budget Range (Optional)</label>
                    <select id="u_budget" name="u_budget" class="form-control">
                        <option>Select</option>
                        <option>Less than 1 Cr</option>
                        <option>1 Cr</option>
                        <option>2 - 5 Cr</option>
                        <option>5 - 10 Cr</option>
                        <option>> 10 Cr</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Eighth Row -->
        <div class="row">
            <div class="col-xs-12">
                <div class="form-group">
                    <label for="u_other_info">Explain Your Query<span style="color: red;">*</span></label>
                    <textarea class="form-control" name="u_other_info" maxlength="1500" placeholder="Enter your note (up to 1500 words)" rows="6" required></textarea>
                </div>
            </div>
        </div>

        <!-- Ninth Row -->
        <div class="row">
            <div class="col-xs-12 col-sm-6">
                <div class="form-group">
                    <label for="u_contct_methd">Preferred Contact Method</label>
                    <select id="u_contct_methd" name="u_contct_methd" class="form-control" required>
                        <option>Select</option>
                        <option>Email</option>
                        <option>Phone</option>
                        <option>No Preference</option>
                    </select>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="form-group">
                    <label for="pdfFile">Upload Document (optional)</label>
                    <input type="file" name="pdfFile" class="form-control" accept=".pdf">
                </div>
            </div>
        </div>
<br>
<div class="g-recaptcha" data-sitekey="6LdUHJEqAAAAADi8U6uFP7gMhHiMIbuhOGGQpifi"></div>
        <!-- Submit Button -->
        <button type="submit" name="submit" class="btn btn-info btn-submit">Submit Your Query</button>
    </form>
</div>

<!-- JS Scripts -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

</body>
</html>


<!-- JS Scripts -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</body>
</html>
