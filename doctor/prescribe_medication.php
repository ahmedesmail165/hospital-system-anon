<?php
session_start();
if(!isset($_SESSION["user"]) || $_SESSION["user"] == "" || $_SESSION['usertype'] != 'd') {
    header("location: ../login.php");
    exit();
}

include("../connection.php");

// Get doctor details
$useremail = $_SESSION["user"];
$userrow = $database->query("select * from doctor where docemail='$useremail'");
$userfetch = $userrow->fetch_assoc();
$userid = $userfetch["docid"];
$username = $userfetch["docname"];

// Get completed appointments
$sql = "SELECT a.appoid, a.appodate, p.pid, p.pname, p.pemail, s.title as session_title, 
        a.diagnosis, a.treatment, a.report
        FROM appointment a
        JOIN patient p ON a.pid = p.pid
        JOIN schedule s ON a.scheduleid = s.scheduleid
        WHERE s.docid = ? AND a.status = 'done'
        ORDER BY a.appodate DESC";
$stmt = $database->prepare($sql);
$stmt->bind_param("i", $userid);
$stmt->execute();
$appointments = $stmt->get_result();

// Get patient's current medications
function getPatientCurrentMeds($database, $pid) {
    $sql = "SELECT m.med_id, m.med_name, m.strength, p.start_date, p.end_date 
            FROM prescriptions p 
            JOIN meds m ON p.med_id = m.med_id 
            WHERE p.pid = ? AND p.status = 'active' 
            AND CURDATE() BETWEEN p.start_date AND p.end_date";
    $stmt = $database->prepare($sql);
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    return $stmt->get_result();
}

// Check for drug interactions
function checkDrugInteractions($database, $med_id, $pid) {
    $current_meds = getPatientCurrentMeds($database, $pid);
    $interactions = array();
    
    while($current_med = $current_meds->fetch_assoc()) {
        $sql = "SELECT i.*, m1.med_name as med1_name, m2.med_name as med2_name 
                FROM drug_interactions i 
                JOIN meds m1 ON i.med_id_1 = m1.med_id 
                JOIN meds m2 ON i.med_id_2 = m2.med_id 
                WHERE (i.med_id_1 = ? AND i.med_id_2 = ?) 
                OR (i.med_id_1 = ? AND i.med_id_2 = ?)";
        
        $stmt = $database->prepare($sql);
        $stmt->bind_param("dddd", $med_id, $current_med['med_id'], $current_med['med_id'], $med_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while($interaction = $result->fetch_assoc()) {
            $interactions[] = array(
                'med1' => $interaction['med1_name'],
                'med2' => $interaction['med2_name'],
                'severity' => $interaction['severity'],
                'description' => $interaction['description']
            );
        }
    }
    return $interactions;
}

// Handle prescription submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['prescribe'])) {
    $pid = $_POST['pid'];
    $med_id = $_POST['med_id'];
    $dosage = $_POST['dosage'];
    $frequency = $_POST['frequency'];
    $duration = $_POST['duration'];
    $instructions = $_POST['instructions'];
    $start_date = $_POST['start_date'];
    
    // Check for drug interactions
    $interactions = checkDrugInteractions($database, $med_id, $pid);
    
    if (!empty($interactions)) {
        $warning_message = "Warning: Potential drug interactions found:\n\n";
        foreach ($interactions as $interaction) {
            $warning_message .= "- " . $interaction['med1'] . " with " . $interaction['med2'] . 
                               " (" . ucfirst($interaction['severity']) . "): " . 
                               $interaction['description'] . "\n";
        }
        $warning_message .= "\nDo you want to proceed with the prescription?";
        echo "<script>
            if (confirm('" . addslashes($warning_message) . "')) {
                document.getElementById('confirmPrescription').submit();
            }
        </script>";
    } else {
        // Calculate end date based on duration
        $end_date = date('Y-m-d', strtotime($start_date . ' + ' . $duration . ' days'));
        
        $sql = "INSERT INTO prescriptions (pid, med_id, docid, dosage, frequency, duration, 
                instructions, start_date, end_date, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
        
        $stmt = $database->prepare($sql);
        $stmt->bind_param("iiissssss", $pid, $med_id, $userid, $dosage, $frequency, 
                          $duration, $instructions, $start_date, $end_date);
        
        if($stmt->execute()) {
            echo "<script>alert('Prescription added successfully!');</script>";
        } else {
            echo "<script>alert('Error adding prescription!');</script>";
        }
    }
}

// Get available medications
$meds_sql = "SELECT * FROM meds WHERE med_qty > 0 ORDER BY med_name";
$medications = $database->query($meds_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
    <title>Prescribe Medication</title>
    <style>
        .dashbord-tables,.doctor-heade{
            animation: transitionIn-Y-over 0.5s;
        }
        .filter-container{
            animation: transitionIn-Y-bottom  0.5s;
        }
        .sub-table,#anim{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .doctor-heade{
            animation: transitionIn-Y-over 0.5s;
        }
        .menu-btn {
            padding: 10px 20px;
            transition: all 0.3s;
            border-radius: 5px;
            margin: 5px;
        }
        .menu-btn:hover {
            background-color: #f0f0f0;
        }
        .menu-icon-dashbord {
            background-image: url('../img/icons/dashboard.svg');
            background-repeat: no-repeat;
            background-position: 20px center;
            background-size: 20px;
        }
        .menu-icon-appoinment {
            background-image: url('../img/icons/appointment.svg');
            background-repeat: no-repeat;
            background-position: 20px center;
            background-size: 20px;
        }
        .menu-icon-session {
            background-image: url('../img/icons/schedule.svg');
            background-repeat: no-repeat;
            background-position: 20px center;
            background-size: 20px;
        }
        .menu-icon-patient {
            background-image: url('../img/icons/patient.svg');
            background-repeat: no-repeat;
            background-position: 20px center;
            background-size: 20px;
        }
        .menu-icon-settings {
            background-image: url('../img/icons/settings.svg');
            background-repeat: no-repeat;
            background-position: 20px center;
            background-size: 20px;
        }
        .menu-active {
            background-color: #e3f2fd;
        }
        .menu-icon-dashbord-active {
            background-image: url('../img/icons/dashboard-hover.svg');
        }
        .menu-icon-appoinment-active {
            background-image: url('../img/icons/appointment-hover.svg');
        }
        .menu-icon-session-active {
            background-image: url('../img/icons/schedule-hover.svg');
        }
        .menu-icon-patient-active {
            background-image: url('../img/icons/patient-hover.svg');
        }
        .menu-icon-settings-active {
            background-image: url('../img/icons/settings-hover.svg');
        }
        .menu-text {
            padding-left: 50px;
            font-size: 14px;
            color: #2c3e50;
        }
        .non-style-link-menu {
            text-decoration: none;
            color: inherit;
        }
        .non-style-link-menu-active {
            color: #3498db;
        }
        .profile-container {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 10px;
        }
        .profile-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }
        .profile-subtitle {
            font-size: 12px;
            color: #7f8c8d;
            margin: 0;
        }
        .logout-btn {
            width: 100%;
            padding: 8px;
            margin-top: 10px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            color: #2c3e50;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .logout-btn:hover {
            background-color: #e9ecef;
        }
        .menu-row {
            transition: all 0.3s;
        }
        .menu-row:hover {
            background-color: #f8f9fa;
        }
        .menu-container {
            width: 100%;
            padding: 10px 0;
        }
        .menu {
            width: 250px;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
        }
        .container {
            display: flex;
        }
        .dash-body {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }
        .appointment-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: 1px solid #e0e0e0;
        }
        .appointment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .appointment-header h4 {
            color: #2c3e50;
            margin: 0;
            font-size: 18px;
        }
        .prescribe-btn {
            background-color: #2ecc71;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        .prescribe-btn:hover {
            background-color: #27ae60;
        }
        .appointment-details {
            color: #34495e;
            line-height: 1.6;
        }
        .appointment-details p {
            margin: 8px 0;
        }
        .appointment-details strong {
            color: #2c3e50;
            font-weight: 600;
        }
        #prescriptionModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .modal-content {
            background: white;
            width: 90%;
            max-width: 600px;
            margin: 50px auto;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .modal-content h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        .current-meds-container {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }
        .current-meds-container h4 {
            color: #2c3e50;
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        .current-meds-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .current-meds-list li {
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .current-meds-list li:last-child {
            border-bottom: none;
        }
        .current-meds-list strong {
            color: #2c3e50;
        }
        .current-meds-list small {
            color: #7f8c8d;
            display: block;
            margin-top: 3px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }
        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-group input:focus, 
        .form-group select:focus, 
        .form-group textarea:focus {
            border-color: #3498db;
            outline: none;
        }
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        .btn-container {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .btn-container button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        .section-title {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        .no-appointments {
            text-align: center;
            padding: 30px;
            background: white;
            border-radius: 10px;
            color: #7f8c8d;
            font-size: 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .interaction-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .interaction-warning h4 {
            color: #856404;
            margin: 0 0 10px 0;
        }
        .interaction-warning ul {
            margin: 0;
            padding-left: 20px;
        }
        .interaction-warning li {
            margin-bottom: 5px;
        }
        .severity-severe {
            color: #dc3545;
            font-weight: bold;
        }
        .severity-moderate {
            color: #fd7e14;
            font-weight: bold;
        }
        .severity-mild {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="menu">
            <table class="menu-container" border="0">
                <tr>
                    <td style="padding:10px" colspan="2">
                        <table border="0" class="profile-container">
                            <tr>
                                <td width="30%" style="padding-left:20px" >
                                    <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title"><?php echo substr($username,0,13)  ?>..</p>
                                    <p class="profile-subtitle"><?php echo substr($useremail,0,22)  ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="../logout.php" ><input type="button" value="Log out" class="logout-btn btn-primary-soft btn"></a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-dashbord">
                        <a href="index.php" class="non-style-link-menu"><div><p class="menu-text">Dashboard</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">My Appointments</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-session">
                        <a href="schedule.php" class="non-style-link-menu"><div><p class="menu-text">My Sessions</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-patient">
                        <a href="patient.php" class="non-style-link-menu"><div><p class="menu-text">My Patients</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-settings">
                        <a href="settings.php" class="non-style-link-menu"><div><p class="menu-text">Settings</p></a></div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="dash-body">
            <table border="0" width="100%" style="border-spacing: 0;margin:0;padding:0;">
                <tr>
                    <td colspan="1" class="nav-bar">
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;margin-left:20px;">Prescribe Medication</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <div style="padding: 20px;">
                            <h2 class="section-title">Completed Appointments</h2>
                            <?php if($appointments->num_rows > 0): ?>
                                <?php while($appointment = $appointments->fetch_assoc()): ?>
                                    <div class="appointment-card">
                                        <div class="appointment-header">
                                            <h4><?php echo htmlspecialchars($appointment['pname']); ?></h4>
                                            <button class="prescribe-btn" onclick="showPrescriptionForm(<?php echo $appointment['pid']; ?>, '<?php echo htmlspecialchars($appointment['pname']); ?>')">
                                                Prescribe Medication
                                            </button>
                                        </div>
                                        <div class="appointment-details">
                                            <p><strong>Appointment Date:</strong> <?php echo htmlspecialchars($appointment['appodate']); ?></p>
                                            <p><strong>Session:</strong> <?php echo htmlspecialchars($appointment['session_title']); ?></p>
                                            <?php if($appointment['diagnosis']): ?>
                                                <p><strong>Diagnosis:</strong> <?php echo htmlspecialchars($appointment['diagnosis']); ?></p>
                                            <?php endif; ?>
                                            <?php if($appointment['treatment']): ?>
                                                <p><strong>Treatment:</strong> <?php echo htmlspecialchars($appointment['treatment']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="no-appointments">
                                    <p>No completed appointments found.</p>
                                </div>
                            <?php endif; ?>

                            <!-- Prescription Form Modal -->
                            <div id="prescriptionModal">
                                <div class="modal-content">
                                    <h3>Prescribe Medication for <span id="patientName"></span></h3>
                                    <div class="current-meds-container">
                                        <h4>Current Medications</h4>
                                        <div id="currentMedsList" class="current-meds-list"></div>
                                    </div>
                                    <form method="POST" action="" id="prescriptionForm">
                                        <input type="hidden" name="pid" id="patientId">
                                        <div class="form-group">
                                            <label>Medication</label>
                                            <select name="med_id" required>
                                                <option value="">Select Medication</option>
                                                <?php while($med = $medications->fetch_assoc()): ?>
                                                    <option value="<?php echo $med['med_id']; ?>">
                                                        <?php echo htmlspecialchars($med['med_name'] . ' (' . $med['strength'] . ')'); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Dosage</label>
                                            <input type="text" name="dosage" required placeholder="e.g., 1 tablet">
                                        </div>
                                        <div class="form-group">
                                            <label>Frequency</label>
                                            <select name="frequency" required>
                                                <option value="once daily">Once Daily</option>
                                                <option value="twice daily">Twice Daily</option>
                                                <option value="three times daily">Three Times Daily</option>
                                                <option value="four times daily">Four Times Daily</option>
                                                <option value="as needed">As Needed</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Duration (days)</label>
                                            <input type="number" name="duration" required min="1">
                                        </div>
                                        <div class="form-group">
                                            <label>Start Date</label>
                                            <input type="date" name="start_date" required value="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Special Instructions</label>
                                            <textarea name="instructions" placeholder="e.g., Take after meals"></textarea>
                                        </div>
                                        <div class="btn-container">
                                            <button type="button" onclick="hidePrescriptionForm()" class="btn-primary-soft btn">Cancel</button>
                                            <button type="submit" name="prescribe" class="btn-primary btn">Prescribe</button>
                                        </div>
                                    </form>
                                    <form method="POST" action="" id="confirmPrescription" style="display: none;">
                                        <input type="hidden" name="pid" id="confirmPatientId">
                                        <input type="hidden" name="med_id" id="confirmMedId">
                                        <input type="hidden" name="dosage" id="confirmDosage">
                                        <input type="hidden" name="frequency" id="confirmFrequency">
                                        <input type="hidden" name="duration" id="confirmDuration">
                                        <input type="hidden" name="instructions" id="confirmInstructions">
                                        <input type="hidden" name="start_date" id="confirmStartDate">
                                        <input type="hidden" name="prescribe" value="1">
                                    </form>
                                </div>
                            </div>

                            <script>
                                function showPrescriptionForm(pid, patientName) {
                                    document.getElementById('patientId').value = pid;
                                    document.getElementById('patientName').textContent = patientName;
                                    document.getElementById('prescriptionModal').style.display = 'block';
                                    
                                    // Fetch and display current medications
                                    fetch('get_current_meds.php?pid=' + pid)
                                        .then(response => response.json())
                                        .then(data => {
                                            const medsList = document.getElementById('currentMedsList');
                                            if (data.length > 0) {
                                                let html = '';
                                                data.forEach(med => {
                                                    html += `<li>
                                                        <strong>${med.med_name}</strong> (${med.strength})<br>
                                                        <small>Started: ${med.start_date}</small>
                                                    </li>`;
                                                });
                                                medsList.innerHTML = html;
                                            } else {
                                                medsList.innerHTML = '<li>No current medications</li>';
                                            }
                                        });
                                }

                                function hidePrescriptionForm() {
                                    document.getElementById('prescriptionModal').style.display = 'none';
                                }

                                // Handle form submission
                                document.getElementById('prescriptionForm').onsubmit = function(e) {
                                    e.preventDefault();
                                    
                                    // Copy form data to confirmation form
                                    document.getElementById('confirmPatientId').value = document.getElementById('patientId').value;
                                    document.getElementById('confirmMedId').value = document.querySelector('select[name="med_id"]').value;
                                    document.getElementById('confirmDosage').value = document.querySelector('input[name="dosage"]').value;
                                    document.getElementById('confirmFrequency').value = document.querySelector('select[name="frequency"]').value;
                                    document.getElementById('confirmDuration').value = document.querySelector('input[name="duration"]').value;
                                    document.getElementById('confirmInstructions').value = document.querySelector('textarea[name="instructions"]').value;
                                    document.getElementById('confirmStartDate').value = document.querySelector('input[name="start_date"]').value;
                                    
                                    // Submit the form
                                    this.submit();
                                };

                                // Close modal when clicking outside
                                window.onclick = function(event) {
                                    var modal = document.getElementById('prescriptionModal');
                                    if (event.target == modal) {
                                        modal.style.display = "none";
                                    }
                                }
                            </script>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html> 