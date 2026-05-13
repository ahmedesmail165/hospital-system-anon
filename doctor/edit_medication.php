<?php
session_start();
if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='d'){
        header("location: ../login.php");
    }else{
        $useremail=$_SESSION["user"];
    }
}else{
    header("location: ../login.php");
}

include("../connection.php");
$userrow = $database->query("select * from doctor where docemail='$useremail'");
$userfetch=$userrow->fetch_assoc();
$userid= $userfetch["docid"];
$username=$userfetch["docname"];

// Get completed appointments
$sql = "SELECT a.appoid, a.appodate, p.pid, p.pname, p.pemail, s.title as session_title, 
        a.diagnosis, a.treatment, a.report
        FROM appointment a
        JOIN patient p ON a.pid = p.pid
        JOIN schedule s ON a.scheduleid = s.scheduleid
        WHERE s.docid = ? AND a.status = 'done'
        ORDER BY a.appodate DESC";
$stmt = $database->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . $database->error);
}
$stmt->bind_param("s", $userid);
$stmt->execute();
$appointments = $stmt->get_result();

// Get available medications
$meds_sql = "SELECT * FROM meds WHERE med_qty > 0 ORDER BY med_name";
$medications = $database->query($meds_sql);

// Handle prescription submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['prescribe'])) {
    $pid = $_POST['pid'];
    $med_id = $_POST['med_id'];
    $dosage = $_POST['dosage'];
    $frequency = $_POST['frequency'];
    $duration = $_POST['duration'];
    $instructions = $_POST['instructions'];
    $start_date = $_POST['start_date'];
    
    // Calculate end date based on duration
    $end_date = date('Y-m-d', strtotime($start_date . ' + ' . $duration . ' days'));
    
    // First check if prescriptions table exists
    $check_table = $database->query("SHOW TABLES LIKE 'prescriptions'");
    if ($check_table->num_rows == 0) {
        // Create prescriptions table if it doesn't exist
        $create_table = "CREATE TABLE IF NOT EXISTS `prescriptions` (
            `prescription_id` INT PRIMARY KEY AUTO_INCREMENT,
            `pid` INT NOT NULL,
            `med_id` DECIMAL(6,0) NOT NULL,
            `docid` INT NOT NULL,
            `dosage` VARCHAR(50) NOT NULL,
            `frequency` VARCHAR(50) NOT NULL,
            `duration` INT NOT NULL,
            `instructions` TEXT,
            `start_date` DATE NOT NULL,
            `end_date` DATE NOT NULL,
            `status` ENUM('active', 'completed', 'cancelled') NOT NULL DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci";
        
        if (!$database->query($create_table)) {
            die("Error creating prescriptions table: " . $database->error);
        }
    }
    
    $sql = "INSERT INTO prescriptions (pid, med_id, docid, dosage, frequency, duration, 
            instructions, start_date, end_date, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
    
    $stmt = $database->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $database->error);
    }
    $stmt->bind_param("idsisssss", $pid, $med_id, $userid, $dosage, $frequency, 
                      $duration, $instructions, $start_date, $end_date);
    
    if($stmt->execute()) {
        $_SESSION['success'] = "Prescription added successfully!";
        header("Location: edit_medication.php");
        exit();
    } else {
        $_SESSION['error'] = "Error adding prescription: " . $stmt->error;
        header("Location: edit_medication.php");
        exit();
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
    <link rel="stylesheet" href="../css/admin-sidebar.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <title>Edit Medication</title>
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
        
    /* تنسيقات عنوان القسم */
.section-header {
    margin: 25px 0 15px;
    padding: 0 25px;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-title h2 {
    font-size: 18px;
    font-weight: 600;
    color: var(--primary-color);
    margin: 0;
}

.section-icon {
    color: var(--primary-color);
    font-size: 20px;
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
        }
        
        /* Modal Styles */
        #prescriptionModal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 1rem;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
            animation: slideUp 0.3s ease-out;
            position: relative;
        }
        
        .modal-content h3 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-size: 1.25rem;
            border-bottom: 2px solid var(--primary-light);
            padding-bottom: 0.75rem;
        }
        
        .modal-content .sub-table {
            margin-top: 1rem;
        }
        
        .modal-content .label-td {
            padding: 0.75rem 0;
            vertical-align: top;
        }
        
        .modal-content .label-td:first-child {
            width: 30%;
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .modal-content .input-text {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 0.9375rem;
            transition: var(--transition);
        }
        
        .modal-content .input-text:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }
        
        .modal-content textarea.input-text {
            min-height: 80px;
            resize: vertical;
        }
        
        .modal-content .btn-primary,
        .modal-content .btn-primary-soft {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: var(--transition);
            border: none;
            cursor: pointer;
        }
        
        .modal-content .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .modal-content .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .modal-content .btn-primary-soft {
            background: var(--primary-light);
            color: var(--primary-color);
        }
        
        .modal-content .btn-primary-soft:hover {
            background: var(--primary-color);
            color: white;
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
        
        /* Alert Messages */
        .alert {
            transition: opacity 0.3s ease;
        }
        
        .alert-success {
            background: var(--success-color) !important;
            color: white !important;
            border-left: 4px solid #059669;
        }
        
        .alert-danger {
            background: var(--danger-color) !important;
            color: white !important;
            border-left: 4px solid #dc2626;
        }
    </style>
</head>
<body>
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
                                <i class="fas fa-tachometer-alt menu-icon"></i>
                                <p class="menu-text">Dashboard</p>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="appointment.php" class="non-style-link-menu">
                            <button class="menu-btn">
                                <i class="fas fa-calendar-check menu-icon"></i>
                                <p class="menu-text">My Appointments</p>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="schedule.php" class="non-style-link-menu">
                            <button class="menu-btn">
                                <i class="fas fa-calendar-alt menu-icon"></i>
                                <p class="menu-text">My Sessions</p>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="patient.php" class="non-style-link-menu">
                            <button class="menu-btn">
                                <i class="fas fa-user-injured menu-icon"></i>
                                <p class="menu-text">My Patients</p>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="repo.php" class="non-style-link-menu">
                            <button class="menu-btn">
                                <i class="fas fa-heartbeat menu-icon"></i>
                                <p class="menu-text">Treated Patients</p>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="edit_medication.php" class="non-style-link-menu">
                            <button class="menu-btn menu-active">
                                <i class="fas fa-pills menu-icon"></i>
                                <p class="menu-text">Edit Medication</p>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="patient_report.php" class="non-style-link-menu">
                            <button class="menu-btn">
                                <i class="fas fa-file-medical menu-icon"></i>
                                <p class="menu-text">Patient Reports</p>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="http://127.0.0.1:8000/" class="non-style-link-menu">
                            <button class="menu-btn">
                                <i class="fas fa-lungs menu-icon"></i>
                                <p class="menu-text">Pneumonia</p>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="http://127.0.0.1:1000/" class="non-style-link-menu">
                            <button class="menu-btn">
                                <i class="fas fa-brain menu-icon"></i>
                                <p class="menu-text">Brain tumor</p>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="http://127.0.0.1:2000/" class="non-style-link-menu">
                            <button class="menu-btn">
                                <i class="fas fa-tint menu-icon"></i>
                                <p class="menu-text">Blood diseases</p>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="http://127.0.0.1:3000/" class="non-style-link-menu">
                            <button class="menu-btn">
                                <i class="fas fa-dna menu-icon"></i>
                                <p class="menu-text">Leukemia</p>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="http://127.0.0.1:4000/" class="non-style-link-menu">
                            <button class="menu-btn">
                                <i class="fas fa-microscope menu-icon"></i>
                                <p class="menu-text">Liver tumor</p>
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
    <?php
    // عرض رسائل النجاح والخطأ - نسخة محدثة لتتناسب مع التصميم الجديد
    if(isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">
                <i class="fas fa-check-circle"></i> '.$_SESSION['success'].'
                <button onclick="this.parentElement.style.display=\'none\'" class="alert-close-btn">&times;</button>
              </div>';
        unset($_SESSION['success']);
    }
    if(isset($_SESSION['error'])) {
        echo '<div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> '.$_SESSION['error'].'
                <button onclick="this.parentElement.style.display=\'none\'" class="alert-close-btn">&times;</button>
              </div>';
        unset($_SESSION['error']);
    }
    ?>
    
    <!-- شريط التنقل العلوي المحدث -->
    <div class="nav-bar">
        <div>
            <a href="index.php" class="non-style-link">
                <button class="btn-primary btn-icon-back">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
            </a>
        </div>
        <div>
            <p class="header-title">Edit Medication</p>
        </div>
        <div class="date-container">
            <i class="far fa-calendar-alt date-icon"></i>
            <div class="date-text">
                <p class="date-label">Today's Date</p>
                <p class="current-date"><?php echo date('Y-m-d'); ?></p>
            </div>
        </div>
    </div>
                
    <div class="section-header">
    <div class="section-title">
        <i class="fas fa-calendar-check section-icon"></i>
        <h2>Completed Appointments (<?php echo $appointments->num_rows; ?>)</h2>
    </div>
</div>
                
                <tr>
                    <td colspan="4">
                        <center>
                            <div class="abc scroll">
                                <table width="93%" class="sub-table scrolldown" border="0">
                                    <thead>
                                        <tr>
                                            <th class="table-headin">Patient Name</th>
                                            <th class="table-headin">Appointment Date</th>
                                            <th class="table-headin">Session</th>
                                            <th class="table-headin">Diagnosis</th>
                                            <th class="table-headin">Treatment</th>
                                            <th class="table-headin">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if($appointments->num_rows == 0) {
                                            echo '<tr><td colspan="6">
                                                <br><br><br><br>
                                                <center>
                                                <img src="../img/notfound.svg" width="25%">
                                                <br>
                                                <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">No completed appointments found!</p>
                                                </center>
                                                <br><br><br><br>
                                            </td></tr>';
                                        } else {
                                            while($appointment = $appointments->fetch_assoc()) {
                                                echo '<tr>
                                                    <td>'.htmlspecialchars($appointment['pname']).'</td>
                                                    <td>'.htmlspecialchars($appointment['appodate']).'</td>
                                                    <td>'.htmlspecialchars($appointment['session_title']).'</td>
                                                    <td>'.($appointment['diagnosis'] ? htmlspecialchars($appointment['diagnosis']) : '-').'</td>
                                                    <td>'.($appointment['treatment'] ? htmlspecialchars($appointment['treatment']) : '-').'</td>
                                                    <td>
                                                        <div style="display:flex;justify-content: center;">
                                                            <a href="#" onclick="showPrescriptionForm('.$appointment['pid'].', \''.htmlspecialchars($appointment['pname']).'\')" class="non-style-link">
                                                                <button class="btn-primary-soft btn button-icon btn-view" style="padding-center: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;">
                                                                    <font class="tn-in-text">Prescribe Medication</font>
                                                                </button>
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
                        </center>
                    </td>
                </tr>
            </table>

            <!-- Prescription Form Modal -->
            <div id="prescriptionModal">
                <div class="modal-content">
                    <span class="close" onclick="hidePrescriptionForm()" style="position: absolute; top: 1rem; right: 1rem; font-size: 1.5rem; cursor: pointer; color: var(--text-secondary);">&times;</span>
                    <h3>Prescribe Medication for <span id="patientName"></span></h3>
                    <form method="POST" action="">
                        <input type="hidden" name="pid" id="patientId">
                        <table class="sub-table" width="100%">
                            <tr>
                                <td class="label-td">
                                    <label for="med_id">Medication</label>
                                </td>
                                <td class="label-td">
                                    <select name="med_id" id="med_id" class="input-text" required>
                                        <option value="">Select Medication</option>
                                        <?php 
                                        $medications->data_seek(0);
                                        while($med = $medications->fetch_assoc()): 
                                        ?>
                                            <option value="<?php echo $med['med_id']; ?>">
                                                <?php echo htmlspecialchars($med['med_name'] . ' (' . $med['strength'] . ')'); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td">
                                    <label for="dosage">Dosage</label>
                                </td>
                                <td class="label-td">
                                    <input type="text" name="dosage" id="dosage" class="input-text" required placeholder="e.g., 1 tablet">
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td">
                                    <label for="frequency">Frequency</label>
                                </td>
                                <td class="label-td">
                                    <select name="frequency" id="frequency" class="input-text" required>
                                        <option value="once daily">Once Daily</option>
                                        <option value="twice daily">Twice Daily</option>
                                        <option value="three times daily">Three Times Daily</option>
                                        <option value="four times daily">Four Times Daily</option>
                                        <option value="as needed">As Needed</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td">
                                    <label for="duration">Duration (days)</label>
                                </td>
                                <td class="label-td">
                                    <input type="number" name="duration" id="duration" class="input-text" required min="1">
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td">
                                    <label for="start_date">Start Date</label>
                                </td>
                                <td class="label-td">
                                    <input type="date" name="start_date" id="start_date" class="input-text" required value="<?php echo date('Y-m-d'); ?>">
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td">
                                    <label for="instructions">Special Instructions</label>
                                </td>
                                <td class="label-td">
                                    <textarea name="instructions" id="instructions" class="input-text" placeholder="e.g., Take after meals"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div style="display:flex;justify-content: flex-end;gap:10px;margin-top:20px;">
                                        <button type="button" onclick="hidePrescriptionForm()" class="btn-primary-soft btn">Cancel</button>
                                        <button type="submit" name="prescribe" class="btn-primary btn">Prescribe</button>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>

            <script>
                function showPrescriptionForm(pid, patientName) {
                    console.log('Opening modal for patient:', patientName, 'ID:', pid);
                    document.getElementById('patientId').value = pid;
                    document.getElementById('patientName').textContent = patientName;
                    document.getElementById('prescriptionModal').style.display = 'block';
                    document.body.style.overflow = 'hidden'; // Prevent background scrolling
                }

                function hidePrescriptionForm() {
                    console.log('Closing modal');
                    document.getElementById('prescriptionModal').style.display = 'none';
                    document.body.style.overflow = 'auto'; // Restore scrolling
                }

                // Close modal when clicking outside
                window.onclick = function(event) {
                    var modal = document.getElementById('prescriptionModal');
                    if (event.target == modal) {
                        hidePrescriptionForm();
                    }
                }

                // Close modal with Escape key
                document.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape') {
                        hidePrescriptionForm();
                    }
                });

                // Auto-hide success and error messages
                document.addEventListener('DOMContentLoaded', function() {
                    const successAlert = document.querySelector('.alert-success');
                    const errorAlert = document.querySelector('.alert-danger');
                    
                    if (successAlert) {
                        setTimeout(function() {
                            successAlert.style.opacity = '0';
                            setTimeout(function() {
                                successAlert.style.display = 'none';
                            }, 300);
                        }, 3000);
                    }
                    
                    if (errorAlert) {
                        setTimeout(function() {
                            errorAlert.style.opacity = '0';
                            setTimeout(function() {
                                errorAlert.style.display = 'none';
                            }, 300);
                        }, 5000);
                    }
                });
            </script>
        </div>
    </div>
</body>
</html> 