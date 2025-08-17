


<?php
// Database connection
include('config.php');

$added = false;

if (isset($_POST['submit'])) {
    // Retrieve form data
    $u_desig2 = $_POST['u_desig2'];
    $u_f_name2 = $_POST['u_f_name2'];
    $u_org_name2 = $_POST['u_org_name2'];
    $u_positn2 = $_POST['u_positn2'];
    $u_email2 = $_POST['u_email2'];
    $u_phone2 = $_POST['u_phone2'];
    $u_altr_mail2 = $_POST['u_altr_mail2'];
    $u_busns_type2 = $_POST['u_busns_type2'];
    $u_parntr_nature2 = $_POST['u_parntr_nature2'];
    $u_spsfy_service2 = $_POST['u_spsfy_service2'];
    $u_colab_area2 = $_POST['u_colab_area2'];
    $u_past_exp2 = $_POST['u_past_exp2'];
    $u_about2 = $_POST['u_about2'];
    $u_contct_methd2 = $_POST['u_contct_methd2'];

    // PDF upload setup
    $upload_dir = 'upload_pdfs/';

    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            die("Failed to create upload directory.");
        }
    }

    $pdfFile2 = null;
    if (isset($_FILES['pdfFile2']) && $_FILES['pdfFile2']['error'] === UPLOAD_ERR_OK) {
        $pdfFile2 = $_FILES['pdfFile2']['name'];
        $pdfTarget = $upload_dir . basename($pdfFile2);

        if (!move_uploaded_file($_FILES['pdfFile2']['tmp_name'], $pdfTarget)) {
            echo "Failed to upload the PDF.";
        }
    }

    // Insert data into the database
    $insert_data = "INSERT INTO bsnspartnerquerry (u_desig2, u_f_name2, u_org_name2, u_positn2, u_email2, u_phone2, u_altr_mail2, u_busns_type2, u_parntr_nature2, u_spsfy_service2, u_colab_area2, u_past_exp2, u_about2, u_contct_methd2, pdfFile2, pdfuploaded2) 
                    VALUES ('$u_desig2','$u_f_name2','$u_org_name2','$u_positn2','$u_email2','$u_phone2','$u_altr_mail2','$u_busns_type2','$u_parntr_nature2','$u_spsfy_service2','$u_colab_area2','$u_past_exp2','$u_about2','$u_contct_methd2','$pdfFile2', NOW())";

    $run_data = mysqli_query($con, $insert_data);

    if ($run_data) {
        $added = true;

        // Email configuration
        $to_user = $u_email2; // User email
        $to_admin = 'dir-ops@spplindia.org'; // Admin email address
        $subject_admin = "New partnership inquiry form submitted by $u_f_name2";
        $subject_user = "Confirmation of partnership inquiry form";

        // Prepare user message
        $message_user = "Dear $u_f_name2,\n\nThank you for submitting your concern. We have received your details and will get back to you shortly.\n\nRegards,\nTeam SPPL India\n\n\n\n\n\n\nSanrachna Prahari Pvt Ltd\n2B4/CW8, Second Floor\nResearch & Innovation Park\nIIT Delhi\nNew Delhi-110016";

        // Email headers for user
        $headers_user = "From: no-reply@sppl.org\r\n";
        $headers_user .= "Reply-To: admin@spplindia.org\r\n";
        $headers_user .= "Return-Path: no-reply@sppl.org\r\n";
        $headers_user .= "MIME-Version: 1.0\r\n";
        $headers_user .= "Content-Type: text/plain; charset=UTF-8\r\n";

        // Prepare admin message
        $message_admin = "New Partnership Inquiry Submitted by $u_f_name2\n\n";
        $message_admin .= "Full Name: $u_desig2 $u_f_name2\n";
        $message_admin .= "Organisation: $u_org_name2\n";
        $message_admin .= "Position: $u_positn2\n";
        $message_admin .= "Email: $u_email2\n";
        $message_admin .= "Phone: $u_phone2\n";
        $message_admin .= "Alternate Email: $u_altr_mail2\n";
        $message_admin .= "Business Type: $u_busns_type2\n";
        $message_admin .= "Partnership Nature: $u_parntr_nature2\n";
        $message_admin .= "Specified Service: $u_spsfy_service2\n";
        $message_admin .= "Collaboration Area: $u_colab_area2\n";
        $message_admin .= "Past Experience: $u_past_exp2\n";
        $message_admin .= "Query: $u_about2\n";
        $message_admin .= "Preferred Contact Method: $u_contct_methd2\n";

        // Email headers for admin
        $headers_admin = "From: Form-submited@sppl.org\r\n";
        $headers_admin .= "Reply-To: admin@spplindia.org\r\n";
        $headers_admin .= "Return-Path: Form-submited@sppl.org\r\n";
        $headers_admin .= "MIME-Version: 1.0\r\n";

        // Check if the PDF is available
        if ($pdfFile2) {
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
            $message_admin_body .= "Content-Type: application/pdf; name=\"$pdfFile2\"\r\n";
            $message_admin_body .= "Content-Transfer-Encoding: base64\r\n";
            $message_admin_body .= "Content-Disposition: attachment; filename=\"$pdfFile2\"\r\n\r\n";
            $message_admin_body .= $pdfEncoded . "\r\n";
            $message_admin_body .= "--PHP-mixed-$boundary--\r\n";

        } else {
            // If no PDF, send a simple text email
            $headers_admin .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $message_admin_body = $message_admin;
        }

        // Send the emails
        $email_to_user_sent = mail($to_user, $subject_user, $message_user, $headers_user, "-f form@sppl.org");
        $email_to_admin_sent = mail($to_admin, $subject_admin, $message_admin_body, $headers_admin, "-f no-reply@sppl.org");

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
    <title>ISHMS Membership Form</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
        <?php if ($added): ?>
            <div class="alert alert-success" role="alert">
                Your form has been successfully submitted to Sanrachna Prahari Pvt Ltd.
                <a href="https://spplindia.org/" target="_blank" style="font-size: 15px;">Click to get back to SPPL homepage</a>
            </div>
        <?php endif; ?>
        
        <h2 style="font-size: 26px; color: blue;"><strong>Sanrachna Prahari Pvt Ltd Partnership Inquiry Form</strong></h2>
        <h2 style="font-size: 14px;"><strong>Designed for government agencies, NGOs, and institutions seeking collaboration with SPPL for research, training, or project-based partnerships.</strong></h2>

        <form method="POST" action="" enctype="multipart/form-data">
            <!-- First Row -->
            <div class="row">
                <div class="col-xs-12 col-sm-3">
                    <div class="form-group">
                        <label for="u_desig">Title</label>
                        <select id="u_desig" name="u_desig2" class="form-control" required>
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
                        <label for="u_f_name2" style="color: #4b4b4b;">Full Name (Block Letters)</label>
                        <input type="text" class="form-control" name="u_f_name2" placeholder="Enter Name" maxlength="60" required>
                    </div>
                </div>
            </div>

            <!-- Second Row -->
            <div class="row">
                <div class="col-xs-12">
                    <div class="form-group">
                        <label for="u_org_name2" style="color: #4b4b4b;">Company / Organisation</label>
                        <input type="text" class="form-control" name="u_org_name2" placeholder="Enter Company Name" maxlength="60" required>
                    </div>
                </div>
            </div>

            <!-- Third Row -->
            <div class="row">
                <div class="col-xs-12 col-sm-6">
                    <div class="form-group">
                        <label for="u_positn2" style="color: #4b4b4b;">Position</label>
                        <input type="text" class="form-control" name="u_positn2" placeholder="Position" maxlength="30" required>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <div class="form-group">
                        <label for="u_email2" style="color: #4b4b4b;">Email ID</label>
                        <input type="email" class="form-control" name="u_email2" placeholder="Enter Email" maxlength="50" required>
                    </div>
                </div>
            </div>

            <!-- Fourth Row -->
            <div class="row">
                <div class="col-xs-12 col-sm-6">
                    <div class="form-group">
                        <label for="u_phone2" style="color: #4b4b4b;">Phone No</label>
                        <input type="text" class="form-control" name="u_phone2" placeholder="Enter Phone Number" maxlength="30" required>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <div class="form-group">
                        <label for="u_altr_mail2" style="color: #4b4b4b;">Alternate Email ID</label>
                        <input type="email" class="form-control" name="u_altr_mail2" placeholder="Enter Alternate Email" maxlength="30">
                    </div>
                </div>
            </div>

            <!-- Fifth Row -->
            <div class="row">
                <div class="col-xs-12 col-sm-6">
                    <div class="form-group">
                        <label for="u_busns_type2" style="color: #4b4b4b;">Type of Business</label>
                        <select id="u_busns_type2" name="u_busns_type2" class="form-control" required>
                            <option>Select</option>
                            <option>Technology Provider</option>
                            <option>Contractor</option>
                            <option>Consultant</option>
                            <option>Supplier</option>
                            <option>Distributor</option>
                            <option>Others</option>
                        </select>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <div class="form-group">
                        <label for="u_parntr_nature2" style="color: #4b4b4b;">Nature of Partnership</label>
                        <select id="u_parntr_nature2" name="u_parntr_nature2" class="form-control" required>
                            <option>Select</option>
                            <option>Technology Collaboration</option>
                            <option>Joint Venture</option>
                            <option>Research & Development</option>
                            <option>Subcontracting</option>
                            <option>Supply Chain Partner</option>
                            <option>Others</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Sixth Row -->
            <div class="row">
                <div class="col-xs-12">
                    <div class="form-group">
                        <label for="u_spsfy_service2" style="color: #4b4b4b;">Your Products / Services</label>
                        <input type="text" class="form-control" name="u_spsfy_service2" placeholder="Enter Products/Services" maxlength="30" required>
                    </div>
                </div>
            </div>

            <!-- Seventh Row -->
            <div class="row">
                <div class="col-xs-12 col-sm-6">
                    <div class="form-group">
                        <label for="u_colab_area2" style="color: #4b4b4b;">Proposed Area of Collaboration</label>
                        <textarea class="form-control" name="u_colab_area2" maxlength="1000" placeholder="Describe collaboration area" rows="6"></textarea>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <div class="form-group">
                        <label for="u_past_exp2" style="color: #4b4b4b;">Past Experience with Infrastructure Projects (If Any)</label>
                        <textarea class="form-control" name="u_past_exp2" maxlength="1000" placeholder="Describe past experience" rows="6"></textarea>
                    </div>
                </div>
            </div>

            <!-- Query Section -->
            <div class="row">
                <div class="col-md-6">
                    <label for="u_other_info" style="color: #4b4b4b; font-size: 16px;">Explain Your Query</label>
                    <textarea class="form-control" name="u_other_info" maxlength="1500" placeholder="Enter your query (up to 1500 words)" rows="6"></textarea>
                </div>
            </div><br>

            <!-- Eighth Row -->
            <div class="row">
                <div class="col-xs-12">
                    <div class="form-group">
                        <label for="u_contct_methd2" style="color: #4b4b4b;">Preferred Contact Method</label>
                        <select id="u_contct_methd2" name="u_contct_methd2" class="form-control" required>
                            <option>Select</option>
                            <option>Email</option>
                            <option>Phone</option>
                            <option>No Preference</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Ninth Row -->
            <div class="row">
                <div class="col-xs-12 col-sm-6">
                    <div class="form-group">
                        <label for="pdfFile2" style="color: #4b4b4b;">Upload Business Profile (optional)</label>
                        <input type="file" name="pdfFile2" class="form-control" accept=".pdf">
                    </div>
                </div>
            </div>
<br><div class="g-recaptcha" data-sitekey="6LdUHJEqAAAAADi8U6uFP7gMhHiMIbuhOGGQpifi"></div>
            <!-- Submit Button -->
            <button type="submit" name="submit" class="btn btn-info btn-submit">Submit</button>
        </form>
    </div>
</body>

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
