<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sql_database_edoc";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Patient ID for schedule
session_start();

if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='p'){
        header("location: ../login.php");
    }else{
        $useremail=$_SESSION["user"];
    }

}else{
    header("location: ../login.php");
}
include("../connection.php");

$userrow = $database->query("select * from patient where pemail='$useremail'");
    $userfetch=$userrow->fetch_assoc();
    $userid= $userfetch["pid"];
    $username=$userfetch["pname"];
$pid = $userid; // Update this as needed
$today = date('Y-m-d');
$startOfWeek = date('Y-m-d', strtotime('last Sunday', strtotime($today)));
$endOfWeek = date('Y-m-d', strtotime('next Saturday', strtotime($today)));

// Query for medication schedule using prescriptions table
$sql = "SELECT 
            m.med_name AS 'Medicine Name', 
            p.dosage AS 'Dosage', 
            p.frequency AS 'Frequency', 
            p.start_date AS 'Start Date', 
            p.end_date AS 'End Date', 
            p.duration AS 'Duration', 
            p.status AS 'Status', 
            p.instructions AS 'Instructions',
            d.docname AS 'Doctor Name'
        FROM prescriptions p
        JOIN meds m ON p.med_id = m.med_id
        LEFT JOIN doctor d ON p.docid = d.docid
        WHERE p.pid = $pid
          AND DATE(p.start_date) <= '$endOfWeek' 
          AND DATE(p.end_date) >= '$startOfWeek'
          AND p.status = 'active'
        ORDER BY p.start_date, p.frequency";

$result = $conn->query($sql);

// Generate dates for the week
$dates = [];
for ($i = 0; $i < 7; $i++) {
    $dates[] = date('Y-m-d', strtotime("+$i day", strtotime($startOfWeek)));
}

// Handle adding sample data
if (isset($_POST['add_sample_data'])) {
    $sample_medications = [
        "INSERT INTO medication (pid, medname, dosage, intake_time, start_date, end_date, daily_doses, notes, status) 
         VALUES ($pid, 'باراسيتامول', '500 مجم', '08:00:00', '$today', DATE_ADD('$today', INTERVAL 7 DAY), 3, 'تناول الدواء بعد الوجبات', 'ongoing')",
        
        "INSERT INTO medication (pid, medname, dosage, intake_time, start_date, end_date, daily_doses, notes, status) 
         VALUES ($pid, 'فيتامين سي', '1000 مجم', '12:00:00', '$today', DATE_ADD('$today', INTERVAL 30 DAY), 1, 'تناول مع الطعام', 'ongoing')",
        
        "INSERT INTO medication (pid, medname, dosage, intake_time, start_date, end_date, daily_doses, notes, status) 
         VALUES ($pid, 'أوميغا 3', '1000 مجم', '20:00:00', '$today', DATE_ADD('$today', INTERVAL 60 DAY), 1, 'تناول مع العشاء', 'ongoing')"
    ];
    
    foreach ($sample_medications as $insert_sql) {
        $conn->query($insert_sql);
    }
    
    // Redirect to refresh the page
    header("Location: " . $_SERVER['PHP_SELF'] . "?period=" . (isset($_GET['period']) ? $_GET['period'] : 'week'));
    exit();
}

// Handle filter periods
$period = isset($_GET['period']) ? $_GET['period'] : 'week';

switch($period) {
    case 'today':
        $startOfPeriod = $today;
        $endOfPeriod = $today;
        $dates = [$today];
        break;
    case 'month':
        $startOfPeriod = date('Y-m-01', strtotime($today));
        $endOfPeriod = date('Y-m-t', strtotime($today));
        $dates = [];
        $current = strtotime($startOfPeriod);
        $end = strtotime($endOfPeriod);
        while ($current <= $end) {
            $dates[] = date('Y-m-d', $current);
            $current = strtotime('+1 day', $current);
        }
        break;
    case 'all':
        // For 'all' period, we'll show all medications without date filtering
        $startOfPeriod = '1900-01-01';
        $endOfPeriod = '2100-12-31';
        $dates = [];
        break;
    default: // week
        $startOfPeriod = date('Y-m-d', strtotime('last Sunday', strtotime($today)));
        $endOfPeriod = date('Y-m-d', strtotime('next Saturday', strtotime($today)));
        $dates = [];
        for ($i = 0; $i < 7; $i++) {
            $dates[] = date('Y-m-d', strtotime("+$i day", strtotime($startOfPeriod)));
        }
        break;
}

// If no results, check if there are any medications for this patient at all
if ($result->num_rows == 0) {
    $check_sql = "SELECT COUNT(*) as count FROM medication WHERE pid = $pid";
    $check_result = $conn->query($check_sql);
    $check_row = $check_result->fetch_assoc();
    
    if ($check_row['count'] == 0) {
        // No medications found for this patient, add some sample data
        $sample_medications = [
            "INSERT INTO medication (pid, medname, dosage, intake_time, start_date, end_date, daily_doses, notes, status) 
             VALUES ($pid, 'باراسيتامول', '500 مجم', '08:00:00', '$today', DATE_ADD('$today', INTERVAL 7 DAY), 3, 'تناول الدواء بعد الوجبات', 'ongoing')",
            
            "INSERT INTO medication (pid, medname, dosage, intake_time, start_date, end_date, daily_doses, notes, status) 
             VALUES ($pid, 'فيتامين سي', '1000 مجم', '12:00:00', '$today', DATE_ADD('$today', INTERVAL 30 DAY), 1, 'تناول مع الطعام', 'ongoing')",
            
            "INSERT INTO medication (pid, medname, dosage, intake_time, start_date, end_date, daily_doses, notes, status) 
             VALUES ($pid, 'أوميغا 3', '1000 مجم', '20:00:00', '$today', DATE_ADD('$today', INTERVAL 60 DAY), 1, 'تناول مع العشاء', 'ongoing')"
        ];
        
        foreach ($sample_medications as $insert_sql) {
            $conn->query($insert_sql);
        }
        
        // Re-run the query after adding sample data
        $result = $conn->query($sql);
    } else {
        // There are medications but they're not in the current period, show all medications for this patient
        $sql = "SELECT 
                    medname AS 'Medicine Name', 
                    dosage AS 'Dosage', 
                    intake_time AS 'Intake Time', 
                    start_date AS 'Start Date', 
                    end_date AS 'End Date', 
                    daily_doses AS 'Daily Doses', 
                    status AS 'Status', 
                    notes AS 'Notes'
                FROM medication 
                WHERE pid = $pid
                ORDER BY start_date DESC, intake_time";
        
        $result = $conn->query($sql);
    }
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
   <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin-sidebar.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <title>Medication Schedule</title>
    <style>
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
            width: 100%;
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
        }
        
        .filter-container-items {
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 0.9375rem;
            transition: var(--transition);
        }
        
        .filter-container-items:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
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
        }
        
        .close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            color: var(--text-secondary);
            cursor: pointer;
            transition: var(--transition);
        }
        
        .close:hover {
            color: var(--danger-color);
            transform: rotate(90deg);
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
        
        .sub-table tr:hover td {
            background-color: var(--primary-light);
        }
        
        .status-ongoing {
            background: var(--success-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.8125rem;
            font-weight: 500;
        }
        
        .status-completed {
            background: var(--info-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.8125rem;
            font-weight: 500;
        }
        
        .status-paused {
            background: var(--warning-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.8125rem;
            font-weight: 500;
        }
        
        .medication-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .medication-name {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .medication-dosage {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        .time-slot {
            background: var(--primary-light);
            color: var(--primary-color);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .no-medication {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--text-secondary);
        }
        
        .no-medication i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .no-medication p {
            font-size: 1.125rem;
            margin-bottom: 0.5rem;
        }
        
        .no-medication small {
            opacity: 0.7;
        }

    </style>
    
</head>
<body>
    <?php

    if (isset($_SESSION["user"])) {
        if (($_SESSION["user"]) == "" || $_SESSION['usertype'] != 'p') {
            header("location: ../login.php");
        } else {
            $useremail = $_SESSION["user"];
        }
    } else {
        header("location: ../login.php");
    }

    include("../connection.php");
    $userrow = $database->query("SELECT * FROM patient WHERE pemail='$useremail'");
    $userfetch = $userrow->fetch_assoc();
    $userid = $userfetch["pid"];
    $username = $userfetch["pname"];
    ?>
     <div class="container">
    <div class="menu" id="sidebarMenu">
        <table class="menu-container" border="0">
            <tr>
                <td colspan="2">
                    <div class="profile-container">
                        <img src="../img/user.png" alt="Profile Image" class="profile-img" width="100%" style="border-radius:50%">
                        <p class="profile-title"><?php echo substr($username,0,13)  ?>..</p>
                        <p class="profile-subtitle"><?php echo substr($useremail,0,22)  ?></p>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <a href="../logout.php" class="non-style-link-menu">
                        <button class="logout-btn btn-primary-soft btn">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Log out</span>
                        </button>
                    </a>
                </td>
            </tr>
            <tr class="menu-row">
                <td colspan="2">
                    <a href="index.php" class="non-style-link-menu non-style-link-menu-active">
                        <button class="menu-btn">
                            <i class="fas fa-home menu-icon"></i>
                            <p class="menu-text">Home</p>
                        </button>
                    </a>
                </td>
            </tr>
            <tr class="menu-row">
                <td colspan="2">
                    <a href="doctors.php" class="non-style-link-menu">
                        <button class="menu-btn">
                            <i class="fas fa-user-md menu-icon"></i>
                            <p class="menu-text">All Doctors</p>
                        </button>
                    </a>
                </td>
            </tr>
            <tr class="menu-row">
                <td colspan="2">
                    <a href="schedule.php" class="non-style-link-menu">
                        <button class="menu-btn">
                            <i class="fas fa-calendar-alt menu-icon"></i>
                            <p class="menu-text">Scheduled Sessions</p>
                        </button>
                    </a>
                </td>
            </tr>
            <tr class="menu-row">
                <td colspan="2">
                    <a href="radio.php" class="non-style-link-menu">
                        <button class="menu-btn">
                            <i class="fas fa-x-ray menu-icon"></i>
                            <p class="menu-text">Radiology Sessions</p>
                        </button>
                    </a>
                </td>
            </tr>
            <tr class="menu-row">
                <td colspan="2">
                    <a href="lab_types.php" class="non-style-link-menu">
                        <button class="menu-btn">
                            <i class="fas fa-flask menu-icon"></i>
                            <p class="menu-text">Medical Labs</p>
                        </button>
                    </a>
                </td>
            </tr>
            <tr class="menu-row">
                <td colspan="2">
                    <a href="appointment.php" class="non-style-link-menu">
                        <button class="menu-btn">
                            <i class="fas fa-calendar-check menu-icon"></i>
                            <p class="menu-text">My Bookings</p>
                        </button>
                    </a>
                </td>
            </tr>
            <tr class="menu-row">
                <td colspan="2">
                    <a href="appointment_report.php" class="non-style-link-menu">
                        <button class="menu-btn">
                            <i class="fas fa-file-medical menu-icon"></i>
                            <p class="menu-text">Booking Reports</p>
                        </button>
                    </a>
                </td>
            </tr>
            <tr class="menu-row">
                <td colspan="2">
                    <a href="form.php" class="non-style-link-menu">
                        <button class="menu-btn">
                            <i class="fas fa-file-alt menu-icon"></i>
                            <p class="menu-text">Booking form</p>
                        </button>
                    </a>
                </td>
            </tr>
            <tr class="menu-row">
                <td colspan="2">
                    <a href="medication_schedule.php" class="non-style-link-menu">
                        <button class="menu-btn menu-active">
                            <i class="fas fa-pills menu-icon"></i>
                            <p class="menu-text">My schedule</p>
                        </button>
                    </a>
                </td>
            </tr>
            <tr class="menu-row">
                <td colspan="2">
                    <a href="http://127.0.0.1:9000/" class="non-style-link-menu">
                        <button class="menu-btn">
                            <i class="fas fa-robot menu-icon"></i>
                            <p class="menu-text">Med AI</p>
                        </button>
                    </a>
                </td>
            </tr>
            <tr class="menu-row">
                <td colspan="2">
                    <a href="Ambulance Booking.php" class="non-style-link-menu">
                        <button class="menu-btn">
                            <i class="fas fa-ambulance menu-icon"></i>
                            <p class="menu-text">Ambulance</p>
                        </button>
                    </a>
                </td>
            </tr>
            <tr class="menu-row">
                <td colspan="2">
                    <a href="settings.php" class="non-style-link-menu">
                        <button class="menu-btn">
                            <i class="fas fa-cog menu-icon"></i>
                            <p class="menu-text">Settings</p>
                        </button>
                    </a>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="dash-body">
    <div class="nav-bar animate-slide-up">
        <div>
            <a href="index.php" class="non-style-link">
                <button onclick="window.history.back();" class="btn-primary btn-icon-back" style="padding: 8px 15px;">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
            </a>
        </div>
        <div>
            <p class="header-title">Medication Schedule</p>
        </div>
        <div class="date-container">
            <i class="far fa-calendar-alt date-icon"></i>
            <div class="date-text">
                <p class="date-label">Today's Date</p>
                <p class="current-date"><?php echo date('Y-m-d'); ?></p>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-container animate-slide-up">
        <form method="GET" action="" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <label for="period" style="font-weight: 500; color: var(--text-primary);">View Schedule:</label>
                <select name="period" id="period" class="filter-container-items" onchange="this.form.submit()">
                    <option value="week" <?php echo (!isset($_GET['period']) || $_GET['period'] == 'week') ? 'selected' : ''; ?>>This Week</option>
                    <option value="today" <?php echo (isset($_GET['period']) && $_GET['period'] == 'today') ? 'selected' : ''; ?>>Today Only</option>
                    <option value="month" <?php echo (isset($_GET['period']) && $_GET['period'] == 'month') ? 'selected' : ''; ?>>This Month</option>
                    <option value="all" <?php echo (isset($_GET['period']) && $_GET['period'] == 'all') ? 'selected' : ''; ?>>All Medications</option>
                </select>
            </div>
            <button type="submit" class="btn-filter">
                <i class="fas fa-filter"></i> Filter
            </button>
        </form>
    </div>

    <div class="table-container animate-slide-up">
        <p class="table-title">
            <i class="fas fa-pills"></i>
            Your Medication Schedule
        </p>

        <center>
            <div class="abc scroll">
                <table width="93%" class="sub-table scrolldown" border="0">
                    <thead>
                        <tr>
                            <th class="table-headin">Day</th>
                            <th class="table-headin">Date</th>
                            <th class="table-headin">Medicine Name</th>
                            <th class="table-headin">Dosage</th>
                            <th class="table-headin">Frequency</th>
                            <th class="table-headin">Duration</th>
                            <th class="table-headin">Status</th>
                            <th class="table-headin">Doctor</th>
                            <th class="table-headin">Instructions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                foreach ($dates as $currentDate) {
                                    if (strtotime($currentDate) >= strtotime($row['Start Date']) && strtotime($currentDate) <= strtotime($row['End Date'])) {
                                        $dayName = date('l', strtotime($currentDate));
                                        echo "<tr>
                                            <td>$dayName</td>
                                            <td>$currentDate</td>
                                            <td>" . ($row['Medicine Name'] ?? '-') . "</td>
                                            <td>" . ($row['Dosage'] ?? '-') . "</td>
                                            <td>" . ($row['Frequency'] ?? '-') . "</td>
                                            <td>" . (isset($row['Duration']) ? $row['Duration'] . ' days' : '-') . "</td>
                                            <td>" . ($row['Status'] ?? '-') . "</td>
                                            <td>" . ($row['Doctor Name'] ?? '-') . "</td>
                                            <td>" . (isset($row['Instructions']) && !empty($row['Instructions']) ? htmlspecialchars($row['Instructions']) : '-') . "</td>
                                        </tr>";
                                    }
                                }
                            }
                        } else {
                            echo "<tr><td colspan='9' style='text-align: center; padding: 2rem;'><p>No medication scheduled for this period.</p></td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </center>
    </div>
</div>
</div>

<script>
// Auto-refresh the page every 5 minutes to keep medication schedule updated
setInterval(function() {
    // Only refresh if user is not actively interacting
    if (!document.hidden) {
        location.reload();
    }
}, 300000); // 5 minutes

// Add smooth scrolling for better UX
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll to top when filter changes
    const periodSelect = document.getElementById('period');
    if (periodSelect) {
        periodSelect.addEventListener('change', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Add loading animation for filter changes
    const filterForm = document.querySelector('.filter-container form');
    if (filterForm) {
        filterForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('.btn-filter');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                submitBtn.disabled = true;
            }
        });
    }
    
    // Highlight today's date in the schedule
    const today = new Date().toISOString().split('T')[0];
    const tableRows = document.querySelectorAll('.sub-table tbody tr');
    tableRows.forEach(row => {
        const dateCell = row.querySelector('td:nth-child(2)');
        if (dateCell && dateCell.textContent.trim() === today) {
            row.style.backgroundColor = 'var(--primary-light)';
            row.style.borderLeft = '4px solid var(--primary-color)';
        }
    });
});

// Add keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + R to refresh
    if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
        e.preventDefault();
        location.reload();
    }
    
    // Escape key to go back
    if (e.key === 'Escape') {
        window.history.back();
    }
});
</script>

</body>
</html>