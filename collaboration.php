


<?php
// Database connection
include('config.php');

$added = false;

if (isset($_POST['submit'])) {
    // Retrieve form data
    $u_desig3 = $_POST['u_desig3'];
    $u_f_name3 = $_POST['u_f_name3'];
    $u_org_name3 = $_POST['u_org_name3'];
    $u_positn3 = $_POST['u_positn3'];
    $u_email3 = $_POST['u_email3'];
    $u_phone3 = $_POST['u_phone3'];
    $u_altr_mail3 = $_POST['u_altr_mail3'];
    $u_org_type3 = $_POST['u_org_type3'];
    $u_quer_purps3 = $_POST['u_quer_purps3'];
    $u_interst3 = $_POST['u_interst3'];
    $u_proposl3 = $_POST['u_proposl3'];
    $u_org_exp3 = $_POST['u_org_exp3'];
    $u_contct_methd3 = $_POST['u_contct_methd3'];

    // PDF upload setup
    $upload_dir = 'upload_pdfs/';

    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            die("Failed to create upload directory.");
        }
    }

    $pdfFile3 = null;
    if (isset($_FILES['pdfFile3']) && $_FILES['pdfFile3']['error'] === UPLOAD_ERR_OK) {
        $pdfFile3 = $_FILES['pdfFile3']['name'];
        $pdfTarget = $upload_dir . basename($pdfFile3);

        if (!move_uploaded_file($_FILES['pdfFile3']['tmp_name'], $pdfTarget)) {
            echo "Failed to upload the PDF.";
        }
    }

    // Insert data into the database
    $insert_data = "INSERT INTO orgquerry (u_desig3, u_f_name3, u_org_name3, u_positn3, u_email3, u_phone3, u_altr_mail3, u_org_type3, u_quer_purps3, u_interst3, u_proposl3, u_org_exp3, u_contct_methd3, pdfFile3, pdfuploaded3) 
                    VALUES ('$u_desig3','$u_f_name3','$u_org_name3','$u_positn3','$u_email3','$u_phone3','$u_altr_mail3','$u_org_type3','$u_quer_purps3','$u_interst3','$u_proposl3','$u_org_exp3','$u_contct_methd3','$pdfFile3', NOW())";

    $run_data = mysqli_query($con, $insert_data);

    if ($run_data) {
        $added = true;

        // Email configuration
        $to_user = $u_email3; // User email
        $to_admin = 'dir-ops@spplindia.org'; // Admin email address
        $subject_admin = "New organizational collaboration form submitted by $u_f_name3";
        $subject_user = "Confirmation of organisational collaboration form submission";

        // Prepare user message
        $message_user = "Dear $u_f_name3,\n\nThank you for submitting your concern. We have received your details and will get back to you shortly.\n\nRegards,\nTeam SPPL India\n\n\n\n\n\n\n
        Sanrachna Prahari Pvt Ltd\n2B4/CW8, Second Floor\nResearch & Innovation Park\nIIT Delhi\nNew Delhi-110016";

        // Email headers for user
        $headers_user = "From: no-reply@spplindia.org\r\n";
        $headers_user .= "Reply-To: dir-ops@spplindia.org\r\n";
        $headers_user .= "Return-Path: no-reply@spplindia.org\r\n";
        $headers_user .= "MIME-Version: 1.0\r\n";
        $headers_user .= "Content-Type: text/plain; charset=UTF-8\r\n";

        // Prepare admin message
        $message_admin = "New Membership Form Submitted by $u_f_name3\n\n";
        $message_admin .= "Designation: $u_desig3\n";
        $message_admin .= "Full Name: $u_f_name3\n";
        $message_admin .= "Organisation: $u_org_name3\n";
        $message_admin .= "Position: $u_positn3\n";
        $message_admin .= "Email: $u_email3\n";
        $message_admin .= "Phone: $u_phone3\n";
        $message_admin .= "Alternate Email: $u_altr_mail3\n";
        $message_admin .= "Organisation Type: $u_org_type3\n";
        $message_admin .= "Query Purpose: $u_quer_purps3\n";
        $message_admin .= "Interest: $u_interst3\n";
        $message_admin .= "Proposal: $u_proposl3\n";
        $message_admin .= "Organisation Experience: $u_org_exp3\n";
        $message_admin .= "Preferred Contact Method: $u_contct_methd3\n";

        // Email headers for admin
        $headers_admin = "From: Form-submited@spplindia.org\r\n";
        $headers_admin .= "Reply-To: dir-ops@spplindia.org\r\n";
        $headers_admin .= "Return-Path: Form-submited@spplindia.org\r\n";
        $headers_admin .= "MIME-Version: 1.0\r\n";

        // Check if the PDF is available
        if ($pdfFile3) {
            $boundary = md5(time());
            $headers_admin .= "Content-Type: multipart/mixed; boundary=\"PHP-mixed-".$boundary."\"\r\n";

            // Email body with attachment
            $message_admin_body = "--PHP-mixed-$boundary\r\n";
            $message_admin_body .= "Content-Type: text/plain; charset=\"utf-8\"\r\n";
            $message_admin_body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $message_admin_body .= $message_admin . "\r\n";

            // Add PDF attachment
            $pdfData = file_get_contents($pdfTarget);
            $pdfEncoded = chunk_split(base64_encode($pdfData));
            $message_admin_body .= "--PHP-mixed-$boundary\r\n";
            $message_admin_body .= "Content-Type: application/pdf; name=\"$pdfFile3\"\r\n";
            $message_admin_body .= "Content-Transfer-Encoding: base64\r\n";
            $message_admin_body .= "Content-Disposition: attachment; filename=\"$pdfFile3\"\r\n\r\n";
            $message_admin_body .= $pdfEncoded . "\r\n";
            $message_admin_body .= "--PHP-mixed-$boundary--\r\n";

        } else {
            // If no PDF, send a simple text email
            $headers_admin .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $message_admin_body = $message_admin;
        }

        // Send the emails
        $email_to_user_sent = mail($to_user, $subject_user, $message_user, $headers_user, "-f no-reply@spplindia.org");
        $email_to_admin_sent = mail($to_admin, $subject_admin, $message_admin_body, $headers_admin, "-f no-reply@spplindia.org");

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
    <title>Colaboration form</title>
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
        Your form has been successfully submitted to Sanrachna Prahari Pvt Ltd.        </div>
        <a href="https://spplindia.org/" target="_blank" style="font-size: 15px;">Click to get back to SPPL homepage</a>
        <?php endif; ?>

        <h2 style= "font-size: 26px; color: blue;"><strong>Sanrachna Prahari Pvt Ltd Organizational Collaboration Form</strong></h2>
        <h2 style= "font-size: 14px"><strong>Designed for government agencies, NGOs, and institutions seeking collaboration with SPPL <br>for research, training, or project-based partnerships.</strong></h2>


    <form method="POST" action="" enctype="multipart/form-data">
        <!-- First Row -->
        <div class="row">
            <div class="col-xs-12 col-sm-3">
                <div class="form-group">
                    <label for="u_desig3">Title<span style="color: red;">*</span></label>
                    <select id="u_desig3" name="u_desig3" class="form-control" required>
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
                    <label for="u_f_name3">Full Name (Block Letters)<span style="color: red;">*</span></label>
                    <input type="text" class="form-control" name="u_f_name3" placeholder="Enter Name" maxlength="60" required>
                </div>
            </div>
        </div>

        <!-- Second Row -->
        <div class="row">
            <div class="col-xs-12">
                <div class="form-group">
                    <label for="u_org_name3">Company / Organisation<span style="color: red;">*</span></label>
                    <input type="text" class="form-control" name="u_org_name3" maxlength="60" required>
                </div>
            </div>
        </div>

        <!-- Third Row -->
        <div class="row">
            <div class="col-xs-12 col-sm-6">
                <div class="form-group">
                    <label for="u_positn3">Position<span style="color: red;">*</span></label>
                    <input type="text" class="form-control" name="u_positn3" maxlength="30" required>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="form-group">
                    <label for="u_email3">Email ID<span style="color: red;">*</span></label>
                    <input type="email" class="form-control" name="u_email3" maxlength="50" required>
                </div>
            </div>
        </div>

        <!-- Fourth Row -->
        <div class="row">
            <div class="col-xs-12 col-sm-6">
                <div class="form-group">
                    <label for="u_phone3">Phone No<span style="color: red;">*</span></label>
                    <input type="tel" class="form-control" name="u_phone3" maxlength="30" required>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="form-group">
                    <label for="u_altr_mail3">Alternate Email ID</label>
                    <input type="email" class="form-control" name="u_altr_mail3" maxlength="50">
                </div>
            </div>
        </div>

        <!-- Fifth Row -->
        <div class="row">
            <div class="col-xs-12 col-sm-6">
                <div class="form-group">
                    <label for="u_org_type3">Type of Organization<span style="color: red;">*</span></label>
                    <select id="u_quer_purps3" name="u_org_type3" class="form-control" required>
                        <option>Select</option>
                        <option>Government Agency</option>
                        <option>Academic Institution</option>
                        <option>Non-Profit</option>
                        <option>Research Body</option>
                        <option>Others</option>
                    </select> 
                  </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="form-group">
                    <label for="u_quer_purps3">Purpose of Inquiry <span style="color: red;">*</span></label>
                    <select id="u_quer_purps3" name="u_quer_purps3" class="form-control" required>
                        <option>Select</option>
                        <option>Research Collaboration</option>
                        <option>Training & Education</option>
                        <option>Infrastructure Project</option>
                        <option>Technical Consultation</option>
                        <option>SHM Solutions</option>
                        <option>Others</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Sixth Row -->
        <div class="row">
            <div class="col-xs-12 col-sm-6">
                <div class="form-group">
                    <label for="u_interst3">Your Experience (optional)</label>
                     <select id="u_quer_purps3" name="u_org_exp3" class="form-control">
                        <option>Less then 5 years</option>
                        <option>5 to 10 years</option>
                        <option>10 to 15 years</option>
                        <option>15 to 20 years</option>
                        <option>more then 20 years</option>
                    </select>

                </div>
            </div>
             <div class="col-xs-12 col-sm-6">
                <div class="form-group">
                    <label for="u_interst3">Areas of Interest (optional)</label>
                                        <input type="email" class="form-control" name="u_interst3" maxlength="50">
                </div>
            </div>
        </div>

        <!-- Seventh Row -->
        <div class="row">
             <div class="col-xs-12">
                <div class="form-group">
                    <label for="u_proposl3">Project/ Research Proposal (if Any)</label>
                    <textarea class="form-control" name="u_proposl3" maxlength="1500" placeholder="Enter your note (up to 1500 words)" rows="6"></textarea>

                </div>
            </div>
           
        </div>

        <!-- Eighth Row -->
        <div class="row">
            <div class="col-xs-12">
                <div class="form-group">
                    <label for="u_org_exp3">About your organization<span style="color: red;">*</span></label>
                    <textarea class="form-control" name="u_org_exp3" maxlength="500" placeholder="Enter your note (up to 1500 words)" rows="6"></textarea>
                </div>
            </div>
        </div>

        <!-- Ninth Row -->
        <div class="row">
            <div class="col-xs-12 col-sm-6">
                <div class="form-group">
                    <label for="u_contct_methd3">Preferred Contact Method (optional)</label>
                    <select id="u_contct_methd3" name="u_contct_methd3" class="form-control" required>
                        <option>Select</option>
                        <option>Email</option>
                        <option>Phone</option>
                        <option>No Preference</option>
                    </select>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="form-group">
                    <label for="pdfFile3">Upload Document (optional)</label>
                    <input type="file" name="pdfFile3" class="form-control" accept=".pdf">
                </div>
            </div>
        </div>
<br> <div class="g-recaptcha" data-sitekey="6LdUHJEqAAAAADi8U6uFP7gMhHiMIbuhOGGQpifi"></div>
        <!-- Submit Button -->
        <button type="submit" name="submit" class="btn btn-info btn-submit">Submit</button>
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
