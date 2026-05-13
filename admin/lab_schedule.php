<?php
// Start output buffering to prevent any accidental output
ob_start();

session_start();

// Verify user permissions
if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" || $_SESSION['usertype']!='a'){
        header("location: ../login.php");
        exit();
    }
}else{
    header("location: ../login.php");
    exit();
}

include("../connection.php");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$view_data = null;
$error_message = null;

// Handle view/delete requests
if(isset($_GET['action']) && isset($_GET['id'])){
    $action = $_GET['action'];
    $id = intval($_GET['id']);
    
    if($action == 'view'){
        // View details code - simplified query
        $sqlmain = "SELECT 
            ls.*, 
            lt.name as lab_type_name,
            ltech.full_name as technician_name,
            ltech.specialization
        FROM lab_schedule ls
        LEFT JOIN lab_types lt ON ls.lab_type_id = lt.lab_type_id
        LEFT JOIN lab_technicians ltech ON ls.technician_id = ltech.technician_id
        WHERE ls.schedule_id = " . intval($id);
        
        $result = $database->query($sqlmain);
        if($result === false) {
            $error_message = "Database error occurred";
            error_log("Database error in lab_schedule.php view: " . $database->error);
            header("location: lab_schedule.php?error=" . urlencode($error_message));
            exit();
        } else if($result->num_rows == 0) {
            $error_message = "Session not found";
            header("location: lab_schedule.php?error=" . urlencode($error_message));
            exit();
        } else {
            $row = $result->fetch_assoc();
            $lab_type_name = $row["lab_type_name"] ?? 'N/A';
            $technician_name = $row["technician_name"] ?? 'N/A';
            $schedule_id = $row["schedule_id"] ?? '';
            $available_date = $row["available_date"] ?? '';
            $start_time = $row["start_time"] ?? '';
            $end_time = $row["end_time"] ?? '';
            $max_appointments = $row["max_appointments"] ?? 0;

            // Get booked appointments
            $sqlmain12 = "SELECT 
                la.*, 
                p.pname as patient_name,
                p.ptel as patient_phone
            FROM lab_appointments la
            LEFT JOIN patient p ON la.pid = p.pid
            WHERE la.schedule_id = " . intval($id) . "
            ORDER BY la.apponum ASC";
            
            $result12 = $database->query($sqlmain12);
            if($result12 === false) {
                $error_message = "Error fetching appointments: " . $database->error;
                error_log("Database error in lab_schedule.php appointments: " . $database->error);
                header("location: lab_schedule.php?error=" . urlencode($error_message));
                exit();
            }
            
            // Store appointments count
            $appointments_count = $result12->num_rows;
            
            // Display the view popup
            ?>
            <div id="popup1" class="overlay">
                <div class="popup" style="width: 70%; position: relative;">
                    <center>
                        <button class="close" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; color: #666; cursor: pointer; z-index: 1000;" onclick="window.location.href='lab_schedule.php'">&times;</button>
                        <div class="content"></div>
                        <div class="abc scroll" style="display: flex;justify-content: center;">
                            <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                                <tr>
                                    <td>
                                        <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">View Session Details</p><br><br>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="name" class="form-label">Lab Type: </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <?php echo htmlspecialchars($lab_type_name); ?><br><br>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="Email" class="form-label">Lab Technician: </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <?php echo htmlspecialchars($technician_name); ?><br><br>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="nic" class="form-label">Session Date: </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <?php echo htmlspecialchars($available_date); ?><br><br>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="Tele" class="form-label">Session Time: </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <?php echo substr($start_time, 0, 5) . ' - ' . substr($end_time, 0, 5); ?><br><br>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="spec" class="form-label"><b>Booked Appointments:</b> (<?php echo $appointments_count; ?>/<?php echo $max_appointments; ?>)</label>
                                        <br><br>
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="4">
                                        <center>
                                            <div class="abc scroll">
                                                <table width="100%" class="sub-table scrolldown" border="0">
                                                    <thead>
                                                        <tr>   
                                                            <th class="table-headin">Appointment #</th>
                                                            <th class="table-headin">Patient Name</th>
                                                            <th class="table-headin">Phone Number</th>
                                                            <th class="table-headin">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php
                                                    if($appointments_count == 0) {
                                                        ?>
                                                        <tr>
                                                            <td colspan="4">
                                                                <br><br><br><br>
                                                                <center>
                                                                    <img src="../img/notfound.svg" width="25%">
                                                                    <br>
                                                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">No appointments booked yet!</p>
                                                                </center>
                                                                <br><br><br><br>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    } else {
                                                        while($appointment = $result12->fetch_assoc()) {
                                                            $status_class = '';
                                                            switch($appointment['status'] ?? 'pending') {
                                                                case 'completed':
                                                                    $status_class = 'success';
                                                                    break;
                                                                case 'cancelled':
                                                                    $status_class = 'danger';
                                                                    break;
                                                                default:
                                                                    $status_class = 'primary';
                                                            }
                                                            ?>
                                                            <tr style="text-align:center;">
                                                                <td style="text-align:center;font-size:23px;font-weight:500; color: var(--btnnicetext);">
                                                                    <?php echo htmlspecialchars($appointment['apponum'] ?? 'N/A'); ?>
                                                                </td>
                                                                <td style="font-weight:600;padding:25px">
                                                                    <?php echo htmlspecialchars($appointment['patient_name'] ?? 'N/A'); ?>
                                                                </td>
                                                                <td>
                                                                    <?php echo htmlspecialchars($appointment['patient_phone'] ?? 'N/A'); ?>
                                                                </td>
                                                                <td>
                                                                    <span class="badge badge-<?php echo $status_class; ?>">
                                                                        <?php echo ucfirst(htmlspecialchars($appointment['status'] ?? 'Pending')); ?>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </center>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </center>
                </div>
            </div>
            <?php
        }
    } elseif($action == 'drop') {
        // Handle delete request
        // First check if there are any appointments booked
        $check_appointments = $database->query("SELECT COUNT(*) as count FROM lab_appointments WHERE schedule_id = " . intval($id));
        if($check_appointments === false) {
            $error_message = "Error checking appointments";
            error_log("Database error checking appointments: " . $database->error);
            header("location: lab_schedule.php?error=" . urlencode($error_message));
            exit();
        }
        
        $appointment_count = $check_appointments->fetch_assoc()['count'];
        
        if($appointment_count > 0) {
            $error_message = "Cannot delete session - there are booked appointments";
            header("location: lab_schedule.php?error=" . urlencode($error_message));
            exit();
        } else {
            // Proceed with deletion
            $delete_query = $database->query("DELETE FROM lab_schedule WHERE schedule_id = " . intval($id));
            
            if($delete_query === false) {
                $error_message = "Failed to delete session: " . $database->error;
                error_log("Database error deleting lab session: " . $database->error);
                header("location: lab_schedule.php?error=" . urlencode($error_message));
                exit();
            } else {
                header("location: lab_schedule.php?success=Session deleted successfully");
                exit();
            }
        }
    }
}

// Handle filters
$filter_conditions = [];
$sql_where = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(!empty($_POST["scheduledate"])){
        $scheduledate = $database->real_escape_string($_POST["scheduledate"]);
        $filter_conditions[] = "ls.available_date='$scheduledate'";
    }

    if(!empty($_POST["lab_type"])){
        $lab_type = $database->real_escape_string($_POST["lab_type"]);
        $filter_conditions[] = "ls.lab_type_id='$lab_type'";
    }

    if(!empty($filter_conditions)){
        $sql_where = " WHERE " . implode(" AND ", $filter_conditions);
    }
}

// Main query to retrieve data
$sqlmain = "SELECT 
                ls.*, 
                lt.name as lab_type_name,
                lt.description as lab_description,
                lt.preparation_instructions,
                lt.estimated_duration,
                lt.price,
                ltech.full_name as technician_name,
                ltech.specialization
            FROM lab_schedule ls
            LEFT JOIN lab_types lt ON ls.lab_type_id = lt.lab_type_id
            LEFT JOIN lab_technicians ltech ON ls.technician_id = ltech.technician_id
            $sql_where
            ORDER BY ls.available_date DESC, ls.start_time ASC";

$result = $database->query($sqlmain);

// Check for query errors
if ($result === false) {
    die("Database query error: " . $database->error);
}

// Get total session count
$count_query = $database->query("SELECT COUNT(*) as total FROM lab_schedule");
if ($count_query === false) {
    die("Database query error: " . $database->error);
}
$total_sessions = $count_query->fetch_assoc()['total'];

// If there was an error, redirect with error message
if($error_message !== null) {
    header("location: lab_schedule.php?error=" . urlencode($error_message));
    exit();
}

// Handle form submission for adding new lab session
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_session'])) {
    $lab_type = $database->real_escape_string($_POST['lab_type']);
    $technician = $database->real_escape_string($_POST['technician']);
    $title = $database->real_escape_string($_POST['title']);
    $available_date = $database->real_escape_string($_POST['available_date']);
    $start_time = $database->real_escape_string($_POST['start_time']);
    $end_time = $database->real_escape_string($_POST['end_time']);
    $max_appointments = (int)$_POST['max_appointments'];
    $duration = (int)$_POST['duration'];
    $preparation_instructions = $database->real_escape_string($_POST['preparation_instructions']);
    
    // Validate inputs
    $errors = [];
    if(empty($lab_type)) $errors[] = "Lab type is required";
    if(empty($technician)) $errors[] = "Technician is required";
    if(empty($available_date)) $errors[] = "Date is required";
    if(empty($start_time)) $errors[] = "Start time is required";
    if(empty($end_time)) $errors[] = "End time is required";
    if($max_appointments <= 0) $errors[] = "Maximum appointments must be greater than 0";
    if($duration <= 0) $errors[] = "Duration must be greater than 0";
    
    // Check if date is in the future
    if(strtotime($available_date) < strtotime(date('Y-m-d'))) {
        $errors[] = "Date must be in the future";
    }
    
    // Check if end time is after start time
    if(strtotime($end_time) <= strtotime($start_time)) {
        $errors[] = "End time must be after start time";
    }
    
    if(empty($errors)) {
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
                $available_date, 
                $start_time, 
                $end_time, 
                $max_appointments, 
                $duration, 
                $preparation_instructions
            );
            
            if ($stmt->execute()) {
                header("location: lab_schedule.php?success=Lab session added successfully");
                exit();
            } else {
                $error_message = "Failed to add lab session: " . $stmt->error;
                error_log("Database error adding lab session: " . $stmt->error);
            }
            $stmt->close();
        } else {
            $error_message = "Failed to prepare statement: " . $database->error;
            error_log("Database error preparing statement: " . $database->error);
        }
    }
}

// Get lab types and technicians for dropdowns
$lab_types_query = "SELECT * FROM lab_types WHERE is_active = 1 ORDER BY name";
$lab_types_result = $database->query($lab_types_query);

$technicians_query = "SELECT * FROM lab_technicians WHERE is_active = 1 ORDER BY full_name";
$technicians_result = $database->query($technicians_query);
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
    <link rel="stylesheet" href="../css/admin-sidebar.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <title>Lab Schedule</title>
    <style>
          .menu-btn.menu-active {
            background: rgba(255, 255, 255, 0.2) !important;
            color: white !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15) !important;
            border-left: 4px solid #3498db !important;
            font-weight: bold !important;
        }
        :root {
            --primary-color: #4a6fa5;
            --primary-dark: #166088;
            --primary-light: #dbeafe;
            --secondary-color: #f8fafc;
            --accent-color: #f59e0b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #4fc3f7;
            --dark-color: #1e293b;
            --light-color: #f8fafc;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: var(--light-color);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        .container {
            display: flex;
            width: 100%;
            min-height: 100vh;
            position: relative;
        }
        
        /* القائمة الجانبية المحسنة */
        .menu {
            width: 280px;
            background: linear-gradient(180deg, var(--primary-color), var(--primary-dark));
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            left: 0;
            top: 0;
            box-shadow: var(--shadow-lg);
            z-index: 100;
            transition: var(--transition-slow);
            transform: translateZ(0);
            will-change: transform;
        }
        
        .menu::-webkit-scrollbar {
            width: 6px;
        }
        
        .menu::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }
        
        .dash-body {
            margin-left: 280px;
            padding: 2rem;
            width: calc(100% - 280px);
            background: var(--light-color);
            min-height: 100vh;
            transition: var(--transition-slow);
        }
        
        /* تحسينات القائمة الجانبية */
        .menu-container {
            width: 100%;
            padding: 0;
            margin: 0;
        }
        
        .profile-container {
            padding: 2rem 1.5rem;
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .profile-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(30deg);
            z-index: 0;
        }
        
        .profile-img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 1rem;
            transition: var(--transition);
            position: relative;
            z-index: 1;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .profile-img:hover {
            transform: scale(1.1) rotate(5deg);
            border-color: rgba(255, 255, 255, 0.6);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }
        
        .profile-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
            padding: 0;
            color: white;
            position: relative;
            z-index: 1;
        }
        
        .profile-subtitle {
            font-size: 0.875rem;
            opacity: 0.9;
            margin: 0.5rem 0 0 0;
            padding: 0;
            color: rgba(255, 255, 255, 0.8);
            position: relative;
            z-index: 1;
        }
        
        .menu-row {
            margin: 0;
            padding: 0.5rem 1.5rem;
            position: relative;
        }
        
        .menu-row::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 1.5rem;
            right: 1.5rem;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .menu-row:last-child::after {
            display: none;
        }
        
        .menu-btn {
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            width: 100%;
            text-align: left;
            border: none;
            background: none;
            cursor: pointer;
            border-radius: 0.75rem;
            color: rgba(255, 255, 255, 0.8);
            transition: var(--transition);
            margin: 0.25rem 0;
            position: relative;
            overflow: hidden;
        }
        
        .menu-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: var(--transition-slow);
        }
        
        .menu-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(8px);
        }
        
        .menu-btn:hover::before {
            left: 100%;
        }
        
        .menu-btn.menu-active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }
        
        .menu-icon {
            margin-right: 1rem;
            font-size: 1.25rem;
            width: 24px;
            text-align: center;
            transition: var(--transition);
        }
        
        .menu-btn:hover .menu-icon {
            transform: scale(1.2);
        }
        
        .menu-text {
            font-size: 0.9375rem;
            font-weight: 500;
            margin: 0;
            padding: 0;
            transition: var(--transition);
        }
        
        .logout-btn {
            width: calc(100% - 3rem);
            padding: 1rem;
            margin: 1.5rem;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-weight: 500;
            border-radius: 0.75rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .logout-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(239, 68, 68, 0.3), rgba(220, 38, 38, 0.5));
            opacity: 0;
            transition: var(--transition);
        }
        
        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.9);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.3);
        }
        
        .logout-btn:hover::after {
            opacity: 1;
        }
        
        .logout-btn i {
            margin-right: 0.75rem;
            font-size: 1.1rem;
            transition: var(--transition);
        }
        
        .logout-btn:hover i {
            transform: rotate(180deg);
        }
        
        /* شريط البحث وتاريخ اليوم */
        .nav-bar {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: var(--transition);
            border: 1px solid var(--border-color);
        }
        
        .nav-bar:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }
        
        .header-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .date-container {
            display: flex;
            align-items: center;
            background: var(--primary-light);
            padding: 0.75rem 1.25rem;
            border-radius: 0.75rem;
            color: var(--primary-color);
            transition: var(--transition);
        }
        
        .date-container:hover {
            background: var(--primary-color);
            color: white;
        }
        
        .date-icon {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }
        
        .date-text {
            text-align: right;
        }
        
        .date-label {
            font-size: 0.8125rem;
            color: inherit;
            opacity: 0.8;
        }
        
        .current-date {
            font-weight: 600;
            font-size: 0.9375rem;
        }
        
        /* أزرار التحكم */
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow);
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .btn-primary i {
            margin-right: 0.5rem;
        }
        
        .btn-icon-back {
            background: var(--primary-light);
            color: var(--primary-color);
        }
        
        .btn-icon-back:hover {
            background: var(--primary-color);
            color: white;
        }
        
        /* الجداول */
        .table-container {
            background: white;
            border-radius: 1rem;
            padding: 1.75rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            transition: var(--transition);
            border: 1px solid var(--border-color);
        }
        
        .table-container:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-3px);
        }
        
        .table-title {
            margin-top: 0;
            margin-bottom: 1.25rem;
            color: var(--primary-color);
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .table-title i {
            margin-right: 0.75rem;
            color: var(--primary-color);
        }
        
        .filter-container {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }
        
        .filter-container table {
            width: 100%;
        }
        
        .filter-container td {
            padding: 0.5rem;
            vertical-align: middle;
        }
        
        .filter-container td[style*="text-align: center"] {
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .filter-container-items {
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 0.9375rem;
            transition: var(--transition);
            background-color: white;
            cursor: pointer;
            width: 100%;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%234a6fa5' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
            padding-right: 2.5rem;
        }
        
        .filter-container-items:hover {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 111, 165, 0.1);
        }
        
        .filter-container-items:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 111, 165, 0.2);
        }
        
        .filter-container-items option {
            padding: 0.75rem;
            font-size: 0.9375rem;
            background-color: white;
            color: var(--text-primary);
        }
        
        .filter-container-items option:checked {
            background-color: var(--primary-light);
            color: var(--primary-color);
            font-weight: 500;
        }
        
        .btn-filter {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .btn-filter:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .sub-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 1rem;
            background: white;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }
        
        .table-headin {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem 1.25rem;
            text-align: left;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.8125rem;
            letter-spacing: 0.5px;
        }
        
        .sub-table th:first-child {
            border-top-left-radius: 0.75rem;
        }
        
        .sub-table th:last-child {
            border-top-right-radius: 0.75rem;
        }
        
        .sub-table td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.9375rem;
            transition: var(--transition);
        }
        
        .sub-table tr:last-child td {
            border-bottom: none;
        }
        
        .sub-table tr:hover td {
            background-color: var(--primary-light);
        }
        
        .btn-view {
            background: var(--info-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .btn-view:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }
        
        .btn-delete {
            background: var(--danger-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .btn-delete:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }
        
        /* النوافذ المنبثقة */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
        }
        
        .popup {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            animation: transitionIn-Y-bottom 0.5s;
            position: relative;
        }
        
        .popup h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            font-weight: 600;
            padding-right: 2rem;
        }
        
        .close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary-color);
            border: none;
            font-size: 1.25rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            text-decoration: none;
            box-shadow: var(--shadow-sm);
        }
        
        .close:hover {
            background: var(--danger-color);
            color: white;
            transform: rotate(90deg);
            box-shadow: var(--shadow);
        }
        
        .close:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
        }
        
        .add-doc-form-container {
            width: 100%;
        }
        
        .label-td {
            padding: 0.5rem 0;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .input-text {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 0.9375rem;
            transition: var(--transition);
        }
        
        .input-text:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }
        
        .box {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 0.9375rem;
            transition: var(--transition);
            background: white;
        }
        
        .box:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }
        
        .scroll {
            overflow-x: auto;
        }
        
        .abc {
            width: 100%;
        }
        
        /* تأثيرات الحركة */
        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        .animate-slide-up {
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* تخطيط متجاوب */
        @media (max-width: 1200px) {
            .menu {
                width: 250px;
            }
            
            .dash-body {
                margin-left: 250px;
                width: calc(100% - 250px);
            }
        }
        
        @media (max-width: 992px) {
            .filter-container {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .menu {
                width: 80px;
                overflow: hidden;
            }
            
            .menu-text, .profile-title, .profile-subtitle, .logout-btn span {
                display: none;
            }
            
            .profile-container {
                padding: 1rem 0.5rem;
            }
            
            .profile-img {
                width: 50px;
                height: 50px;
                margin-bottom: 0;
            }
            
            .menu-btn {
                justify-content: center;
                padding: 1rem 0.5rem;
            }
            
            .menu-icon {
                margin-right: 0;
                font-size: 1.25rem;
            }
            
            .dash-body {
                margin-left: 80px;
                width: calc(100% - 80px);
                padding: 1.5rem;
            }
            
            .nav-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                padding: 1.25rem;
            }
        }
        
        @media (max-width: 576px) {
            .menu {
                transform: translateX(-100%);
                position: fixed;
                width: 280px;
                z-index: 1000;
                height: 100vh;
                top: 0;
                left: 0;
                transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .menu.active {
                transform: translateX(0);
            }
            
            .dash-body {
                margin-left: 0;
                width: 100%;
            }
            
            .menu-toggle {
                display: flex;
                position: fixed;
                top: 1.5rem;
                left: 1.5rem;
                z-index: 1001;
                background: var(--primary-color);
                color: white;
                border: none;
                width: 48px;
                height: 48px;
                border-radius: 50%;
                align-items: center;
                justify-content: center;
                font-size: 1.25rem;
                cursor: pointer;
                box-shadow: var(--shadow-lg);
                transition: var(--transition);
            }
            
            .menu-toggle:hover {
                transform: scale(1.1);
                background: var(--primary-dark);
            }
            
            .popup {
                width: 95%;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
    
    <div class="container">
        <!-- القائمة الجانبية -->
        <div class="menu" id="sidebarMenu">
            <table class="menu-container" border="0">
                <tr>
                    <td colspan="2">
                        <div class="profile-container">
                            <img src="../img/user.png" alt="Profile Image" class="profile-img">
                            <p class="profile-title">Administrator</p>
                            <p class="profile-subtitle">admin@edoc.com</p>
                        </div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="../logout.php" class="non-style-link-menu">
                            <button class="logout-btn">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Log out</span>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="index.php" class="non-style-link-menu">
                            <button class="menu-btn">
                                <i class="fas fa-tachometer-alt menu-icon"></i>
                                <p class="menu-text">Dashboard</p>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="doctors.php" class="non-style-link-menu">
                            <button class="menu-btn">
                                <i class="fas fa-user-md menu-icon"></i>
                                <p class="menu-text">Doctors</p>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="schedule.php" class="non-style-link-menu">
                            <button class="menu-btn">
                                <i class="fas fa-calendar-alt menu-icon"></i>
                                <p class="menu-text">Schedule</p>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="lab_schedule.php" class="non-style-link-menu">
                            <button class="menu-btn menu-active">
                                <i class="fas fa-flask menu-icon"></i>
                                <p class="menu-text">Lab Schedule</p>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="radiology_schedule.php" class="non-style-link-menu">
                            <button class="menu-btn">
                                <i class="fas fa-x-ray menu-icon"></i>
                                <p class="menu-text">Radiology Schedule</p>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="appointment.php" class="non-style-link-menu">
                            <button class="menu-btn">
                                <i class="fas fa-calendar-check menu-icon"></i>
                                <p class="menu-text">Appointments</p>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="patient.php" class="non-style-link-menu">
                            <button class="menu-btn">
                                <i class="fas fa-user-injured menu-icon"></i>
                                <p class="menu-text">Patients</p>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="staff performance.php" class="non-style-link-menu">
                            <button class="menu-btn">
                                <i class="fas fa-chart-line menu-icon"></i>
                                <p class="menu-text">Staff Performance</p>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="Financialdash.php" class="non-style-link-menu">
                            <button class="menu-btn">
                                <i class="fas fa-wallet menu-icon"></i>
                                <p class="menu-text">Financial Dashboard</p>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="GeneralHospita Performance.php" class="non-style-link-menu">
                            <button class="menu-btn">
                                <i class="fas fa-hospital menu-icon"></i>
                                <p class="menu-text">Hospital Performance</p>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="ComplianceAccreditation.php" class="non-style-link-menu">
                            <button class="menu-btn">
                                <i class="fas fa-clipboard-check menu-icon"></i>
                                <p class="menu-text">Compliance & Accreditation</p>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="symetrix.php" class="non-style-link-menu">
                            <button class="menu-btn">
                                <i class="fas fa-chart-pie menu-icon"></i>
                                <p class="menu-text">System Metrics</p>
                            </button>
                        </a>
                    </td>
                </tr>
            </table>
        </div>

        <!-- المحتوى الرئيسي -->
        <div class="dash-body">
    <tr>
        <td colspan="4" style="padding:0;">
            <div class="nav-bar animate-slide-up">
                <div>
                    <a href="lab_schedule.php" class="non-style-link">
                        <button class="btn-primary btn-icon-back" style="padding: 8px 15px;">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                    </a>
                </div>
                <div>
                    <p class="header-title">Lab Schedule Management</p>
                </div>
                <div class="date-container">
                    <i class="far fa-calendar-alt date-icon"></i>
                    <div class="date-text">
                        <p class="date-label">Today's Date</p>
                        <p class="current-date"><?php echo date('Y-m-d'); ?></p>
                    </div>
                </div>
            </div>
        </td>
    </tr>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div class="table-title">
            <i class="fas fa-flask"></i>
            Manage Lab Sessions
        </div>
        <a href="?action=add-session&id=none&error=0" class="non-style-link">
            <button class="btn-primary" style="display:flex;align-items:center;">
                <i class="fas fa-plus" style="margin-right:0.5rem;"></i> Add Session
            </button>
        </a>
    </div>

    <div class="table-container animate-slide-up">
        <p class="table-title">
            <i class="fas fa-list"></i>
            All Lab Sessions (<?php echo $total_sessions; ?>)
        </p>
        
        <div class="filter-container animate-slide-up">
            <form action="" method="POST">
                <table border="0" width="100%">
                    <tr>
                        <td width="10%" style="text-align: center;">Date:</td>
                        <td width="20%">
                            <input type="date" name="scheduledate" id="date" class="filter-container-items">
                        </td>
                        <td width="10%" style="text-align: center;">Lab Type:</td>
                        <td width="30%">
                            <select name="lab_type" class="filter-container-items">
                                <option value="" disabled selected hidden>Select Lab Type</option>
                                <?php 
                                $types_query = $database->query("SELECT * FROM lab_types WHERE is_active = 1 ORDER BY name ASC");
                                while($type = $types_query->fetch_assoc()){
                                    echo "<option value='".$type['lab_type_id']."'>".$type['name']."</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td width="12%">
                            <input type="submit" name="filter" value="Filter" class="btn-filter">
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>

    <div class="table-container animate-slide-up">
        <div class="scroll">
            <table width="100%" class="sub-table scrolldown" border="0">
                <thead>
                    <tr>
                        <th class="table-headin">Lab Type</th>
                        <th class="table-headin">Technician</th>
                        <th class="table-headin">Date & Time</th>
                        <th class="table-headin">Max Appts</th>
                        <th class="table-headin">Booked</th>
                        <th class="table-headin">Status</th>
                        <th class="table-headin">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($result->num_rows == 0){
                        echo '<tr>
                            <td colspan="7">
                            <br><br><br><br>
                            <center>
                            <img src="../img/notfound.svg" width="25%">
                            <br>
                            <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">No sessions available</p>
                            <a class="non-style-link" href="lab_schedule.php?action=add-session">
                                <button class="login-btn btn-primary-soft btn">Add New Session</button>
                            </a>
                            </center>
                            <br><br><br><br>
                            </td>
                            </tr>';
                    } else {
                        while($row = $result->fetch_assoc()){
                            // Count bookings
                            $booked_query = $database->query("SELECT COUNT(*) as booked FROM lab_appointments WHERE schedule_id=".$row['schedule_id']);
                            $booked = $booked_query->fetch_assoc()['booked'];
                            
                            echo '<tr>
                                <td>'.htmlspecialchars($row['lab_type_name']).'</td>
                                <td>'.htmlspecialchars($row['technician_name']).'</td>
                                <td style="text-align:center;">
                                    '.htmlspecialchars($row['available_date']).' 
                                    '.substr($row['start_time'], 0, 5).' - 
                                    '.substr($row['end_time'], 0, 5).'
                                </td>
                                <td style="text-align:center;">'.htmlspecialchars($row['max_appointments']).'</td>
                                <td style="text-align:center;">'.$booked.'</td>
                                <td style="text-align:center;">'.($booked < $row['max_appointments'] ? '<span class="badge badge-success">Available</span>' : '<span class="badge badge-danger">Full</span>').'</td>
                                <td>
                                    <div style="display:flex;justify-content: center;">
                                        <a href="?action=view&id='.$row['schedule_id'].'" class="non-style-link">
                                            <button class="btn-primary-soft btn button-icon btn-view">View</button>
                                        </a>
                                        &nbsp;&nbsp;&nbsp;
                                        <a href="?action=drop&id='.$row['schedule_id'].'&name='.urlencode($row['lab_type_name']).'" class="non-style-link">
                                            <button class="btn-primary-soft btn button-icon btn-delete">Delete</button>
                                        </a>
                                    </div>
                                </td>
                            </tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if($error_message !== null): ?>
<div class="error-message animate-slide-up" style="position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: #fee2e2; color: #dc2626; padding: 15px 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); z-index: 1000; text-align: center; min-width: 300px;">
    <?php echo htmlspecialchars($error_message); ?>
</div>
<?php endif; ?>

<?php if(isset($errors) && !empty($errors)): ?>
    <div class="error-message" style="position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: #fee2e2; color: #dc2626; padding: 15px 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); z-index: 1000; text-align: center; min-width: 300px;">
        <ul style="list-style: none; padding: 0; margin: 0;">
            <?php foreach($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if(isset($_GET['success'])): ?>
    <div class="success-message" style="position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: #dcfce7; color: #16a34a; padding: 15px 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); z-index: 1000; text-align: center; min-width: 300px;">
        <?php echo htmlspecialchars($_GET['success']); ?>
    </div>
<?php endif; ?>

<?php if(isset($_GET['action']) && $_GET['action'] == 'add-session'): ?>
    <div id="popup1" class="overlay">
        <div class="popup">
            <center>
                <a class="close" href="lab_schedule.php">&times;</a>
                <div style="display: flex;justify-content: center;">
                    <div class="abc">
                        <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                            <tr>
                                <td>
                                    <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">Add New Lab Session</p><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <form action="" method="POST" class="add-new-form">
                                        <label for="lab_type" class="form-label">Lab Type: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <select name="lab_type" id="lab_type" class="box" required>
                                        <option value="" disabled selected hidden>Select Lab Type</option>
                                        <?php 
                                        $types_query = $database->query("SELECT * FROM lab_types WHERE is_active = 1 ORDER BY name ASC");
                                        while($type = $types_query->fetch_assoc()){
                                            echo "<option value='".$type['lab_type_id']."'>".$type['name']."</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="technician" class="form-label">Lab Technician: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <select name="technician" id="technician" class="box" required>
                                        <option value="" disabled selected hidden>Select Technician</option>
                                        <?php 
                                        $tech_query = $database->query("SELECT * FROM lab_technicians WHERE is_active = 1 ORDER BY full_name ASC");
                                        while($tech = $tech_query->fetch_assoc()){
                                            echo "<option value='".$tech['technician_id']."'>".$tech['full_name']."</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="title" class="form-label">Session Title: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <input type="text" name="title" class="input-text" placeholder="Enter session title" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="available_date" class="form-label">Session Date: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <input type="date" name="available_date" class="input-text" min="<?php echo date('Y-m-d'); ?>" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="start_time" class="form-label">Start Time: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <input type="time" name="start_time" class="input-text" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="end_time" class="form-label">End Time: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <input type="time" name="end_time" class="input-text" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="max_appointments" class="form-label">Maximum Appointments: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <input type="number" name="max_appointments" class="input-text" min="1" value="10" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="duration" class="form-label">Duration (minutes): </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <input type="number" name="duration" class="input-text" min="1" value="30" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="preparation_instructions" class="form-label">Preparation Instructions: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <textarea name="preparation_instructions" class="input-text" rows="3" placeholder="Enter preparation instructions"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <input type="reset" value="Reset" class="login-btn btn-primary btn">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <input type="submit" value="Add Session" class="login-btn btn-primary btn" name="add_session">
                                </td>
                            </tr>
                        </form>
                    </table>
                </div>
            </div>
        </center>
    </div>
</div>
<?php endif; ?>

<script>
    // Function to open add session modal
    function openAddSessionModal() {
        window.location.href = '?action=add-session&id=none&error=0';
    }
    
    // Auto-hide success/error messages after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const messages = document.querySelectorAll('.error-message, .success-message');
        messages.forEach(function(message) {
            setTimeout(function() {
                message.style.opacity = '0';
                setTimeout(function() {
                    message.style.display = 'none';
                }, 500);
            }, 5000);
        });
    });
</script>