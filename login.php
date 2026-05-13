<?php
// Start the session
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
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'login') {
            // Login logic
            $email = $_POST['useremail'];
            $password = $_POST['userpassword'];
            
            $result = $database->query("SELECT * FROM webuser WHERE email='$email'");
            if ($result->num_rows == 1) {
                $utype = $result->fetch_assoc()['usertype'];
                if ($utype == 'p') {
                    $checker = $database->query("SELECT * FROM patient WHERE pemail='$email' AND ppassword='$password'");
                    if ($checker->num_rows == 1) {
                        $_SESSION['user'] = $email;
                        $_SESSION['usertype'] = 'p';
                        header('location: patient/index.php');
                    } else {
                        $error = 'Wrong credentials: Invalid email or password';
                    }
                } elseif ($utype == 'a') {
                    $checker = $database->query("SELECT * FROM admin WHERE aemail='$email' AND apassword='$password'");
                    if ($checker->num_rows == 1) {
                        $_SESSION['user'] = $email;
                        $_SESSION['usertype'] = 'a';
                        header('location: admin/index.php');
                    } else {
                        $error = 'Wrong credentials: Invalid email or password';
                    }
                } elseif ($utype == 'd') {
                    $checker = $database->query("SELECT * FROM doctor WHERE docemail='$email' AND docpassword='$password'");
                    if ($checker->num_rows == 1) {
                        $_SESSION['user'] = $email;
                        $_SESSION['usertype'] = 'd';
                        header('location: doctor/index.php');
                    } else {
                        $error = 'Wrong credentials: Invalid email or password';
                    }
                }
            } else {
                $error = 'We can\'t find any account for this email.';
            }
        } elseif ($_POST['action'] == 'signup') {
            // Signup logic
            $_SESSION["personal"] = array(
                'fname' => $_POST['fname'],
                'lname' => $_POST['lname'],
                'address' => $_POST['address'],
                'nic' => $_POST['nic'],
                'dob' => $_POST['dob'],
                'email' => $_POST['email']
            );

            print_r($_SESSION["personal"]);
            header("location: create-account.php");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Opticare - Eye Care Specialists</title>
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
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1505751172876-fa1923c5c528?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            height: 100vh;
            margin: 0;
            overflow: hidden;
            color: white;
        }
        
        .container {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            position: relative;
            overflow: hidden;
            width: 768px;
            max-width: 100%;
            min-height: 480px;
        }
        
        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            transition: var(--transition);
            background-color: white;
        }
        
        .sign-in-container {
            left: 0;
            width: 50%;
            z-index: 2;
        }
        
        .sign-up-container {
            left: 0;
            width: 50%;
            opacity: 0;
            z-index: 1;
        }
        
        .overlay-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: var(--transition);
            z-index: 100;
        }
        
        .overlay {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color)) no-repeat 0 0 / cover;
            color: #fff;
            position: relative;
            left: -100%;
            height: 100%;
            width: 200%;
            transform: translateX(0);
            transition: var(--transition);
        }
        
        .overlay-panel {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 40px;
            text-align: center;
            top: 0;
            height: 100%;
            width: 50%;
            transform: translateX(0);
            transition: var(--transition);
        }
        
        .overlay-left {
            transform: translateX(-20%);
        }
        
        .overlay-right {
            right: 0;
            transform: translateX(0);
        }
        
        /* Animation */
        .container.right-panel-active .sign-in-container {
            transform: translateX(100%);
        }
        
        .container.right-panel-active .sign-up-container {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
        }
        
        .container.right-panel-active .overlay-container {
            transform: translateX(-100%);
        }
        
        .container.right-panel-active .overlay {
            transform: translateX(50%);
        }
        
        .container.right-panel-active .overlay-left {
            transform: translateX(0);
        }
        
        .container.right-panel-active .overlay-right {
            transform: translateX(20%);
        }
        
        /* Form styling */
        form {
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 50px;
            height: 100%;
            text-align: center;
        }
        
        h1 {
            font-weight: 600;
            margin: 0 0 20px;
            color: var(--dark-color);
        }
        
        #vit {
            color: var(--primary-color);
            font-size: 28px;
            margin-bottom: 30px;
        }
        
        input {
            background-color: #eee;
            border: none;
            padding: 12px 15px;
            margin: 8px 0;
            width: 100%;
            border-radius: 5px;
            font-size: 14px;
        }
        
        input:focus {
            outline: none;
            border-bottom: 2px solid var(--accent-color);
        }
        
        button {
            border-radius: 20px;
            border: 1px solid var(--primary-color);
            background-color: var(--primary-color);
            color: #fff;
            font-size: 12px;
            font-weight: bold;
            padding: 12px 45px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: transform 80ms ease-in;
            cursor: pointer;
            margin-top: 20px;
        }
        
        button:active {
            transform: scale(0.95);
        }
        
        button:focus {
            outline: none;
        }
        
        button.ghost {
            background-color: transparent;
            border-color: #fff;
        }
        
        p {
            font-size: 14px;
            font-weight: 300;
            line-height: 1.5;
            letter-spacing: 0.5px;
            margin: 15px 0;
            color: var(--dark-color);
        }
        
        .highlight-text {
            color: var(--primary-color);
            font-weight: 500;
            cursor: pointer;
        }
        
        /* Social icons */
        .social-container {
            margin: 20px 0;
        }
        
        .social-container a {
            border: 1px solid #ddd;
            border-radius: 50%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            margin: 0 5px;
            height: 40px;
            width: 40px;
            color: var(--dark-color);
            transition: var(--transition);
        }
        
        .social-container a:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-3px);
        }
        
        /* Pharmacy button */
        .pharmacy-btn {
            text-decoration: none;
            display: inline-block;
            background-color: var(--success-color);
            color: white;
            padding: 12px 25px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: transform 80ms ease-in;
            cursor: pointer;
            margin: 10px 0;
        }
        
        .pharmacy-btn:hover {
            transform: scale(0.95);
            background-color: #218838;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .container {
                width: 100%;
                height: 100%;
                border-radius: 0;
            }
            
            .overlay-container {
                display: none;
            }
            
            .sign-in-container, .sign-up-container {
                width: 100%;
            }
            
            .container.right-panel-active .sign-in-container,
            .container.right-panel-active .sign-up-container {
                transform: translateX(0);
            }
        }
        
        /* Alert messages */
        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 5px;
            color: white;
            font-weight: 500;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            animation: slideIn 0.5s, fadeOut 0.5s 2.5s;
        }
        
        .alert-error {
            background-color: var(--error-color);
        }
        
        .alert-success {
            background-color: var(--success-color);
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        /* Logo styling */
        .logo {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 24px;
            font-weight: 700;
            color: white;
            z-index: 1001;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
        }
        
        .logo span {
            color: var(--accent-color);
        }
        
        /* White text for dark background */
        .white-text {
            color: white !important;
        }
    </style>
</head>
<body>
    <div class="logo">Opticare<span>Hospital</span></div>
    
    <div class="container" id="container">
        <div class="form-container sign-up-container">
            <form action="#" method="POST">
                <input type="hidden" name="action" value="signup">
                <h1 id="vit" style="position: relative; top: 30px;">Create Account</h1>
                <div style="display: flex; gap: 10px; width: 100%;">
                    <input type="text" placeholder="First Name" name="fname" required>
                    <input type="text" placeholder="Last Name" name="lname" required>
                </div>
                <input type="text" placeholder="Address" name="address" required>
                <input type="text" placeholder="NID Number" name="nic" required>
                <input type="date" placeholder="Date of Birth" name="dob" required>
                <button type="submit">Sign Up</button>
                <p>Already have an account? <span class="highlight-text" id="signInText">Sign In</span></p>
                <div class="social-container">
                    <a href="https://www.facebook.com" class="social" id="fb"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com" class="social" id="ins"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.gmail.com" class="social" id="gm"><i class="fas fa-envelope"></i></a>
                    <a href="https://www.twitter.com" class="social" id="tw"><i class="fab fa-twitter"></i></a>
                </div>
            </form>
        </div>
        <div class="form-container sign-in-container">
            <form action="#" method="POST">
                <input type="hidden" name="action" value="login">
                <h1>Sign in to <span style="color: var(--primary-color);">Opticare</span></h1>
                <input type="email" placeholder="Email" name="useremail" required>
                <input type="password" placeholder="Password" name="userpassword" required>
                <button type="submit">Sign In</button>
                <p>Don't have an account? <span class="highlight-text" id="signUpText">Sign Up</span></p>
                <div class="social-container">
                    <a href="https://www.facebook.com" class="social" id="fb"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com" class="social" id="ins"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.gmail.com" class="social" id="gm"><i class="fas fa-envelope"></i></a>
                    <a href="https://www.twitter.com" class="social" id="tw"><i class="fab fa-twitter"></i></a>
                </div>
            </form>
        </div>
        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1 class="white-text">Welcome Back!</h1>
                    <p class="white-text">To keep connected with us please login with your personal info</p>
                    <button class="ghost" id="signIn">Sign In</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h1 class="white-text">Welcome to Opticare!</h1>
                    <p class="white-text">Enter your personal details and start your journey with us</p>
                    <button class="ghost" id="signUp">Sign Up</button>
                    <p class="white-text" style="margin-top: 30px;">Access our pharmacy system</p>
                    <a href="Opticare-Pharmacy-System/Pharmacy-Management-System-master/PHARMACY/mainpage.php" class="pharmacy-btn">
                        <i class="fas fa-prescription-bottle-alt"></i> Pharmacy Login
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    if (!empty($error)) {
        echo "<div class='alert alert-error'>$error</div>";
        echo "<script>
            setTimeout(() => {
                document.querySelector('.alert').remove();
            }, 3000);
        </script>";
    }
    if (!empty($success)) {
        echo "<div class='alert alert-success'>$success</div>";
        echo "<script>
            setTimeout(() => {
                document.querySelector('.alert').remove();
            }, 3000);
        </script>";
    }
    ?>
    
    <script>
        const signUpButton = document.getElementById('signUp');
        const signInButton = document.getElementById('signIn');
        const signUpText = document.getElementById('signUpText');
        const signInText = document.getElementById('signInText');
        const container = document.getElementById('container');

        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });
        
        signUpText.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        signInText.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });
    </script>
</body>
</html>