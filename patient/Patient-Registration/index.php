<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Patient Registration Form | OpticareHospital</title>
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

        h1 {
            font-size: 28px;
            font-weight: 600;
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
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
        input[type="tel"],
        input[type="number"],
        input[type="email"],
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
        }

        .btn-primary:hover {
            background-color: #3a5a80;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .btn-primary-soft {
            background-color: rgba(74, 111, 165, 0.1);
            color: var(--primary-color);
            margin-right: 10px;
        }

        .btn-primary-soft:hover {
            background-color: rgba(74, 111, 165, 0.2);
        }

        .button-group {
            display: flex;
            justify-content: center;
            margin-top: 30px;
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
            
            .button-group {
                flex-direction: column;
            }
            
            .btn-primary-soft {
                margin-right: 0;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="site-logo">
        <span class="logo-part1">Opticare</span><span class="logo-part2">Hospital</span>
    </div>
    
    <div class="form-container">
        <h1>Patient Registration Form</h1>
        <form id="patient-form" action="foback.php" method="post">
            <!-- Personal Information -->
            <fieldset>
                <legend>Personal Information</legend>
                
                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender" required>
                        <option value="">Select</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
            </fieldset>

            <!-- Emergency Contact Information -->
            <fieldset>
                <legend>Emergency Contact Information</legend>
                <div class="form-group">
                    <label for="emergency-name">Emergency Contact Name:</label>
                    <input type="text" id="emergency-name" name="emergency-name" required>
                </div>
                <div class="form-group">
                    <label for="emergency-phone">Emergency Contact Phone:</label>
                    <input type="tel" id="emergency-phone" name="emergency-phone" required>
                </div>
                <div class="form-group">
                    <label for="emergency-relationship">Relationship:</label>
                    <input type="text" id="emergency-relationship" name="emergency-relationship" required>
                </div>
            </fieldset>

            <!-- Insurance Information -->
            <fieldset>
                <legend>Insurance Information</legend>
                <div class="form-group">
                    <label for="insurance-provider">Insurance Provider:</label>
                    <input type="text" id="insurance-provider" name="insurance-provider" required>
                </div>
                <div class="form-group">
                    <label for="policy-number">Policy Number:</label>
                    <input type="text" id="policy-number" name="policy-number" required>
                </div>
                <div class="form-group">
                    <label for="group-number">Group Number (if applicable):</label>
                    <input type="text" id="group-number" name="group-number">
                </div>
            </fieldset>

            <!-- Health Information -->
            <fieldset>
                <legend>Health Information</legend>
                <div class="form-group">
                    <label for="height">Height (cm):</label>
                    <input type="number" id="height" name="height" required>
                </div>
                <div class="form-group">
                    <label for="weight">Weight (kg):</label>
                    <input type="number" id="weight" name="weight" required>
                </div>
                <div class="form-group">
                    <label for="blood-type">Blood Type:</label>
                    <select id="blood-type" name="blood-type" required>
                        <option value="">Select</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="allergies">Allergies:</label>
                    <textarea id="allergies" name="allergies" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="medications">Current Medications:</label>
                    <textarea id="medications" name="medications" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="chronic-conditions">Chronic Conditions:</label>
                    <textarea id="chronic-conditions" name="chronic-conditions" rows="5"></textarea>
                </div>
                <div class="form-group">
                    <label for="medical-history">Medical History:</label>
                    <textarea id="medical-history" name="medical-history" rows="5"></textarea>
                </div>
            </fieldset>

            <!-- Family Medical History -->
            <fieldset>
                <legend>Family Medical History</legend>
                <div class="form-group">
                    <label for="family-history">Please list any significant family medical history (e.g., heart disease, diabetes, cancer):</label>
                    <textarea id="family-history" name="family-history" rows="5"></textarea>
                </div>
            </fieldset>

            <!-- Additional Questions -->
            <fieldset>
                <legend>Additional Questions</legend>
                <div class="form-group">
                    <label for="symptoms">What are your current symptoms?</label>
                    <textarea id="symptoms" name="symptoms" rows="5" required></textarea>
                </div>
                <div class="form-group">
                    <label for="smoking">Do you smoke?</label>
                    <select id="smoking" name="smoking" required>
                        <option value="">Select</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="alcohol">Do you consume alcohol?</label>
                    <select id="alcohol" name="alcohol" required>
                        <option value="">Select</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="exercise">How often do you exercise?</label>
                    <select id="exercise" name="exercise" required>
                        <option value="">Select</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="rarely">Rarely</option>
                    </select>
                </div>
            </fieldset>

            <!-- Submit and Reset Buttons -->
            <div class="button-group">
                <button type="reset" class="btn btn-primary-soft">Reset</button>
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
</body>
</html>