<?php
session_start();

// Unset all the server side variables
$_SESSION["user"] = "";
$_SESSION["usertype"] = "";

// Set the new timezone
date_default_timezone_set('Africa/Cairo');
$date = date('Y-m-d');
$_SESSION["date"] = $date;

// Import database connection
include("connection.php");

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $fname = $_SESSION['personal']['fname'];
    $lname = $_SESSION['personal']['lname'];
    $name = $fname . " " . $lname;
    $address = $_SESSION['personal']['address'];
    $nic = $_SESSION['personal']['nic'];
    $dob = $_SESSION['personal']['dob'];
    $email = $_POST['newemail'];
    $tele = $_POST['tele'];
    $newpassword = $_POST['newpassword'];
    $cpassword = $_POST['cpassword'];
    
    if ($newpassword == $cpassword) {
        $result = $database->query("SELECT * FROM webuser WHERE email='$email'");
        
        if ($result->num_rows == 1) {
            $error = '<div class="error-message">Already have an account for this Email address.</div>';
        } else {
            // Insert into patient table
            $database->query("INSERT INTO patient(pemail, pname, ppassword, paddress, pnic, pdob, ptel) 
                             VALUES('$email', '$name', '$newpassword', '$address', '$nic', '$dob', '$tele')");
            
            // Insert into webuser table
            $database->query("INSERT INTO webuser VALUES('$email', 'p')");

            // Set session variables
            $_SESSION["user"] = $email;
            $_SESSION["usertype"] = "p";
            $_SESSION["username"] = $fname;

            // Redirect to patient dashboard
            header('Location: patient/Patient-Registration/index.php');
            exit();
        }
    } else {
        $error = '<div class="error-message">Password confirmation error! Please reconfirm your password.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Complete Registration | OpticareHospital</title>
    <style>
        :root {
            --primary-color: #4a6fa5;
            --secondary-color: #166088;
            --accent-color: #4fc3f7;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --success-color: #28a745;
            --error-color: #dc3545;
            --border-radius: 10px;
            --box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1576091160550-2173dba999ef?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            position: relative;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            width: 100%;
            max-width: 600px;
            padding: 40px;
            margin-top: 60px;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .site-logo {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 28px;
            font-weight: 700;
            z-index: 100;
        }

        .logo-part1 {
            color: var(--light-color);
            text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
        }

        .logo-part2 {
            color: var(--accent-color);
            text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
        }

        .header-text {
            font-size: 28px;
            font-weight: 600;
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 10px;
        }

        .sub-text {
            font-size: 14px;
            color: #666;
            text-align: center;
            margin-bottom: 30px;
            display: block;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-color);
            font-size: 14px;
        }

        .input-text {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 14px;
            transition: var(--transition);
            margin-bottom: 20px;
            background-color: rgba(255, 255, 255, 0.9);
        }

        .input-text:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 111, 165, 0.2);
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: var(--border-radius);
            font-weight: 500;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            width: 100%;
        }

        .btn-primary:hover {
            background-color: #3a5a80;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .btn-primary-soft {
            background-color: rgba(74, 111, 165, 0.1);
            color: var(--primary-color);
            width: 100%;
        }

        .btn-primary-soft:hover {
            background-color: rgba(74, 111, 165, 0.2);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .error-message {
            color: var(--error-color);
            font-size: 14px;
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            background-color: rgba(220, 53, 69, 0.1);
            border-radius: var(--border-radius);
        }

        .success-message {
            color: var(--success-color);
            font-size: 14px;
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            background-color: rgba(40, 167, 69, 0.1);
            border-radius: var(--border-radius);
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 30px 20px;
                margin-top: 80px;
            }
            
            .site-logo {
                position: absolute;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                width: 100%;
                text-align: center;
            }
            
            body {
                padding: 20px 10px;
            }
        }

        /* Password strength indicator */
        .password-strength {
            height: 4px;
            background-color: #eee;
            margin-top: -15px;
            margin-bottom: 15px;
            border-radius: 2px;
            overflow: hidden;
        }

        .strength-0 {
            width: 0%;
            background-color: var(--error-color);
        }

        .strength-1 {
            width: 25%;
            background-color: #ff6b6b;
        }

        .strength-2 {
            width: 50%;
            background-color: #feca57;
        }

        .strength-3 {
            width: 75%;
            background-color: #48dbfb;
        }

        .strength-4 {
            width: 100%;
            background-color: var(--success-color);
        }
    </style>
</head>
<body>
    <div class="site-logo">
        <span class="logo-part1">Opticare</span><span class="logo-part2">Hospital</span>
    </div>
    
    <div class="container">
        <h1 class="header-text">Complete Registration</h1>
        <p class="sub-text">Please fill in your details to complete your registration</p>

        <?php echo $error; ?>

        <form action="" method="POST">
            <div class="form-grid">
                <div>
                    <label for="newemail" class="form-label">Email Address</label>
                    <input type="email" name="newemail" class="input-text" placeholder="your@email.com" required>
                </div>
                <div>
                    <label for="tele" class="form-label">Mobile Number</label>
                    <input type="tel" name="tele" class="input-text" placeholder="ex: 01014106072" pattern="^(01)[0-9]{9}$" required>
                </div>
            </div>

            <div>
                <label for="newpassword" class="form-label">Create Password</label>
                <input type="password" name="newpassword" class="input-text" placeholder="New Password" required id="password">
                <div class="password-strength" id="password-strength"></div>
            </div>

            <div>
                <label for="cpassword" class="form-label">Confirm Password</label>
                <input type="password" name="cpassword" class="input-text" placeholder="Confirm Password" required>
            </div>

            <div class="form-grid" style="margin-top: 20px;">
                <input type="reset" value="Reset" class="btn btn-primary-soft">
                <input type="submit" value="Sign Up" class="btn btn-primary">
            </div>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>

    <script>
        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('password-strength');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            strengthBar.className = 'strength-' + strength;
        });
    </script>
</body>
</html>