<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Survey | OpticareHospital</title>
    <!-- Add Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Add Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
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
            color: #333;
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

        .form-container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            width: 100%;
            max-width: 800px;
            padding: 40px;
            margin-top: 60px;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h1, h2, h3 {
            color: var(--primary-color);
            text-align: center;
        }

        h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        h2 {
            font-size: 22px;
            font-weight: 500;
            margin-bottom: 30px;
            color: var(--secondary-color);
        }

        h3 {
            font-size: 20px;
            font-weight: 500;
            margin-bottom: 20px;
            text-align: left;
        }

        fieldset {
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 25px;
            background-color: rgba(255, 255, 255, 0.8);
        }

        legend {
            font-weight: 500;
            color: var(--primary-color);
            padding: 0 10px;
            font-size: 18px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-color);
            font-size: 14px;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 14px;
            transition: var(--transition);
            background-color: rgba(255, 255, 255, 0.9);
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 111, 165, 0.2);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
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
            max-width: 300px;
            margin: 0 auto;
            display: block;
        }

        .btn-primary:hover {
            background-color: #3a5a80;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .radio-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }

        .radio-group input[type="radio"] {
            display: none;
        }

        .radio-group input[type="radio"] + label {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            background-color: rgba(74, 111, 165, 0.1);
            cursor: pointer;
            transition: var(--transition);
            font-size: 14px;
            color: var(--primary-color);
        }

        .radio-group input[type="radio"]:hover + label,
        .radio-group input[type="radio"]:focus + label {
            background-color: rgba(74, 111, 165, 0.2);
        }

        .radio-group input[type="radio"]:checked + label {
            background-color: var(--primary-color);
            color: white;
        }

        @media (max-width: 768px) {
            .form-container {
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
            
            h1 {
                font-size: 24px;
            }
            
            h2 {
                font-size: 18px;
            }
            
            .radio-group {
                gap: 8px;
            }
            
            .radio-group input[type="radio"] + label {
                padding: 6px 12px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="site-logo">
        <span class="logo-part1">Opticare</span><span class="logo-part2">Hospital</span>
    </div>
    
    <div class="form-container">
        <h1>Patient Survey</h1>
        <h2>How are you feeling today?</h2>
        
        <?php
        if(isset($_SESSION["user"])){
            if(($_SESSION["user"])=="" or $_SESSION['usertype']!='p'){
                header("location: ../login.php");
            }else{
                $useremail=$_SESSION["user"];
            }
        }else{
        }
        
        include("../connection.php");
        $doctor=$_GET['doctor']; 
        ?>
        
        <!-- Form Start -->
        <form method="POST" action="http://127.0.0.1:5000/">
            <fieldset>
                <legend>Patient Information</legend>
                <input type="hidden" name="patient_id" value="<?php echo $userid; ?>">
                <input type="hidden" name="doctor" value="<?php echo $doctor; ?>">
                
                <div class="form-group">
                    <label for="patient_name">Patient Name</label>
                    <input type="text" name="patient_name" required>
                </div>
                
                <div class="form-group">
                    <label for="Patient_ID">Patient ID</label>
                    <input type="text" name="Patient_ID" required>
                </div>
                
                <div class="form-group">
                    <label for="visit_date">Date of Visit</label>
                    <input type="date" name="visit_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </fieldset>
            
            <fieldset>
                <legend>Service Evaluation</legend>
                <div class="form-group">
                    <label>How would you rate the overall quality of care you received?</label>
                    <div class="radio-group">
                        <input type="radio" name="service_rating" id="service_rating_poor" value="Poor" required>
                        <label for="service_rating_poor">Poor</label>
                        <input type="radio" name="service_rating" id="service_rating_average" value="Average">
                        <label for="service_rating_average">Average</label>
                        <input type="radio" name="service_rating" id="service_rating_good" value="Good">
                        <label for="service_rating_good">Good</label>
                        <input type="radio" name="service_rating" id="service_rating_excellent" value="Excellent">
                        <label for="service_rating_excellent">Excellent</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Overall experience rating?</label>
                    <div class="radio-group">
                        <?php for($i=1; $i<=10; $i++): ?>
                            <input type="radio" name="overall_experience" id="overall_experience_<?php echo $i; ?>" value="<?php echo $i; ?>" <?php echo $i==5 ? 'required' : ''; ?>>
                            <label for="overall_experience_<?php echo $i; ?>"><?php echo $i; ?></label>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>How courteous were the staff?</label>
                    <div class="radio-group">
                        <?php for($i=1; $i<=10; $i++): ?>
                            <input type="radio" name="wait_time" id="wait_time_<?php echo $i; ?>" value="<?php echo $i; ?>" <?php echo $i==5 ? 'required' : ''; ?>>
                            <label for="wait_time_<?php echo $i; ?>"><?php echo $i; ?></label>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>How would you rate the hospital facilities?</label>
                    <div class="radio-group">
                        <?php for($i=1; $i<=10; $i++): ?>
                            <input type="radio" name="facilities_rating" id="would_recommend_<?php echo $i; ?>" value="<?php echo $i; ?>" <?php echo $i==5 ? 'required' : ''; ?>>
                            <label for="would_recommend_<?php echo $i; ?>"><?php echo $i; ?></label>
                        <?php endfor; ?>
                    </div>
                </div>
            </fieldset>
            
            <fieldset>
                <legend>Additional Feedback</legend>
                <div class="form-group">
                    <label>Would you recommend this hospital to others?</label>
                    <div class="radio-group">
                        <input type="radio" name="would_recommend" id="would_recommend_no" value="No">
                        <label for="would_recommend_no">No</label>
                        <input type="radio" name="would_recommend" id="would_recommend_yes" value="Yes" required>
                        <label for="would_recommend_yes">Yes</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Do you feel you need additional support?</label>
                    <div class="radio-group">
                        <input type="radio" name="need_support" id="need_support_no" value="No">
                        <label for="need_support_no">No</label>
                        <input type="radio" name="need_support" id="need_support_yes" value="Yes" required>
                        <label for="need_support_yes">Yes</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="patient_feedback">Please add any additional comments or suggestions here</label>
                    <textarea name="patient_feedback" rows="4" required></textarea>
                </div>
            </fieldset>
            
            <button type="submit" class="btn btn-primary">Submit Survey</button>
        </form>
        <!-- Form End -->
    </div>

    <!-- Add Bootstrap JS (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>