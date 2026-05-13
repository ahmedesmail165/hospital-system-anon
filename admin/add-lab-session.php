<?php
session_start();

// Verify user permissions
if(!isset($_SESSION["user"]) || $_SESSION['usertype'] != 'a'){
    header("location: ../login.php");
    exit();
}

include("../connection.php");
if (!$database) {
    die("Connection failed: " . mysqli_connect_error());
}

$errors = [];

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    // Validate and sanitize inputs
    $lab_type = isset($_POST['lab_type']) ? intval($_POST['lab_type']) : 0;
    $technician = isset($_POST['technician']) ? intval($_POST['technician']) : 0;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $date = isset($_POST['date']) ? $_POST['date'] : '';
    $start_time = isset($_POST['start_time']) ? $_POST['start_time'] : '';
    $duration = isset($_POST['duration']) ? intval($_POST['duration']) : 0;
    $max_appointments = isset($_POST['max_appointments']) ? intval($_POST['max_appointments']) : 0;
    $preparation_instructions = isset($_POST['preparation_instructions']) ? trim($_POST['preparation_instructions']) : '';
    
    // Validate inputs
    if(empty($lab_type)) $errors[] = "Lab type is required";
    if(empty($technician)) $errors[] = "Technician is required";
    if(empty($title)) $errors[] = "Session title is required";
    if(empty($date)) $errors[] = "Date is required";
    if(empty($start_time)) $errors[] = "Start time is required";
    if($duration < 15 || $duration > 180) $errors[] = "Duration must be between 15 and 180 minutes";
    if($max_appointments < 1 || $max_appointments > 20) $errors[] = "Maximum appointments must be between 1 and 20";
    
    if(empty($errors)) {
        // Calculate end time
        $end_time = date('H:i:s', strtotime($start_time) + ($duration * 60));
        
        // Check for scheduling conflicts
        $conflict_sql = "SELECT COUNT(*) as count FROM lab_schedule 
            WHERE technician_id = ? 
            AND available_date = ? 
            AND (
                (start_time <= ? AND end_time > ?) 
                OR (start_time < ? AND end_time >= ?)
                OR (start_time >= ? AND end_time <= ?)
            )";
        
        $conflict_check = $database->prepare($conflict_sql);
        if ($conflict_check) {
            $conflict_check->bind_param("isssssss", 
                $technician, 
                $date, 
                $end_time, $start_time, 
                $end_time, $start_time,
                $start_time, $end_time
            );
            
            if ($conflict_check->execute()) {
                $conflicts = $conflict_check->get_result()->fetch_assoc()['count'];
                
                if($conflicts > 0) {
                    $errors[] = "Scheduling conflict: Technician already has a session during this time";
                }
            }
            $conflict_check->close();
        }
        
        if(empty($errors)) {
            // Insert into database
            $sql = "INSERT INTO lab_schedule (
                lab_type_id, 
                technician_id, 
                title, 
                available_date, 
                start_time, 
                end_time, 
                max_appointments, 
                booked_appointments, 
                duration, 
                preparation_instructions, 
                is_available
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, ?, 1)";
            
            $stmt = $database->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("iisssssis", 
                    $lab_type, 
                    $technician, 
                    $title, 
                    $date, 
                    $start_time, 
                    $end_time, 
                    $max_appointments, 
                    $duration, 
                    $preparation_instructions
                );
                
                if ($stmt->execute()) {
                    header("location: lab_schedule.php?action=add-session&error=0");
                    exit();
                } else {
                    $errors[] = "Database error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $errors[] = "Database error: " . $database->error;
            }
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
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
    <title>Add Lab Session</title>
    <style>
        .form-container {
            animation: transitionIn-Y-bottom 0.5s;
        }
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- باقي كود HTML كما هو -->
        <div class="dash-body" style="margin-top: 15px">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;" >
                <tr>
                    <td width="13%">
                        <a href="lab_schedule.php"><button class="login-btn btn-primary-soft btn btn-icon-back" style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                    </td>
                    <td>
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;">Add New Lab Session</p>
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php echo date('Y-m-d'); ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button class="btn-label" style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <div class="form-container">
                            <?php
                            if(!empty($errors)) {
                                echo '<div class="error-message">';
                                foreach($errors as $error) {
                                    echo '<p>'.$error.'</p>';
                                }
                                echo '</div>';
                            }
                            ?>
                            <form action="" method="POST" class="form">
                                <div class="form-group">
                                    <label for="lab_type">Lab Type:</label>
                                    <select name="lab_type" id="lab_type" class="box" required>
                                        <option value="" disabled selected>Select Lab Type</option>
                                        <?php
                                        $types_query = $database->query("SELECT * FROM lab_types WHERE is_active = 1 ORDER BY name ASC");
                                        while($type = $types_query->fetch_assoc()){
                                            echo "<option value='".$type['lab_type_id']."'>".$type['name']."</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="technician">Technician:</label>
                                    <select name="technician" id="technician" class="box" required>
                                        <option value="" disabled selected>Select Technician</option>
                                        <?php
                                        $tech_query = $database->query("SELECT * FROM lab_technicians WHERE is_active = 1 ORDER BY full_name ASC");
                                        while($tech = $tech_query->fetch_assoc()){
                                            echo "<option value='".$tech['technician_id']."'>".$tech['full_name']." (".$tech['specialization'].")</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="title">Session Title:</label>
                                    <input type="text" name="title" id="title" class="box" required value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="date">Date:</label>
                                    <input type="date" name="date" id="date" class="box" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo isset($_POST['date']) ? $_POST['date'] : ''; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="start_time">Start Time:</label>
                                    <input type="time" name="start_time" id="start_time" class="box" required value="<?php echo isset($_POST['start_time']) ? $_POST['start_time'] : ''; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="duration">Duration (minutes):</label>
                                    <input type="number" name="duration" id="duration" class="box" required min="15" max="180" value="<?php echo isset($_POST['duration']) ? $_POST['duration'] : '60'; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="max_appointments">Maximum Appointments:</label>
                                    <input type="number" name="max_appointments" id="max_appointments" class="box" required min="1" max="20" value="<?php echo isset($_POST['max_appointments']) ? $_POST['max_appointments'] : '10'; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="preparation_instructions">Preparation Instructions:</label>
                                    <textarea name="preparation_instructions" id="preparation_instructions" class="box" required><?php echo isset($_POST['preparation_instructions']) ? htmlspecialchars($_POST['preparation_instructions']) : ''; ?></textarea>
                                </div>

                                <div class="form-group" style="margin-top: 20px;">
                                    <input type="submit" value="Add Session" class="btn-primary btn" style="width: 100%;">
                                </div>
                            </form>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>