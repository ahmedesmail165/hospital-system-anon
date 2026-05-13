<?php
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

// Handle view/delete requests before any output
if(isset($_GET['action']) && isset($_GET['id'])){
    $action = $_GET['action'];
    $id = intval($_GET['id']);
    
    if($action == 'view'){
        // Get session details
        $sqlmain = "SELECT 
            rs.*, 
            rt.techname as technician_name
            FROM radiology_schedule rs
            INNER JOIN radiology_technicians rt ON rs.techid = rt.techid
            WHERE rs.scheduleid = " . $id;
            
        $result = $database->query($sqlmain);
        
        if($result === false) {
            header("location: radiology_schedule.php?error=db_error");
            exit();
        }
        
        if($result->num_rows == 0) {
            header("location: radiology_schedule.php?error=not_found");
            exit();
        }
    } elseif($action == 'drop'){
        // Check if session has any appointments
        $check_query = $database->query("SELECT COUNT(*) as count FROM radiology_appointment WHERE scheduleid = " . $id);
        $check_result = $check_query->fetch_assoc();
        
        if($check_result['count'] > 0) {
            header("location: radiology_schedule.php?error=has_appointments");
            exit();
        }
    }
}

// Handle filters
$filter_conditions = [];
$sql_where = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(!empty($_POST["scheduledate"])){
        $scheduledate = $database->real_escape_string($_POST["scheduledate"]);
        $filter_conditions[] = "rs.scheduledate='$scheduledate'";
    }

    if(!empty($_POST["radiology_type"])){
        $radiology_type = $database->real_escape_string($_POST["radiology_type"]);
        $filter_conditions[] = "rs.session_type='$radiology_type'";
    }

    if(!empty($filter_conditions)){
        $sql_where = " WHERE " . implode(" AND ", $filter_conditions);
    }
}

// Main query to retrieve data
$sqlmain = "SELECT 
                rs.*, 
                rt.techname as technician_name 
            FROM radiology_schedule rs
            INNER JOIN radiology_technicians rt ON rs.techid = rt.techid
            $sql_where
            ORDER BY rs.scheduledate DESC";

$result = $database->query($sqlmain);

// Check for query errors
if (!$result) {
    die("Database query error: " . $database->error);
}

// Get total session count
$total_sessions = $database->query("SELECT COUNT(*) as total FROM radiology_schedule")->fetch_assoc()['total'];
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
    <title>Radiology Scheduling System</title>
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
                            <button class="menu-btn">
                                <i class="fas fa-flask menu-icon"></i>
                                <p class="menu-text">Lab Schedule</p>
                            </button>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td colspan="2">
                        <a href="radiology_schedule.php" class="non-style-link-menu">
                            <button class="menu-btn menu-active">
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
                                <p class="menu-text">Appointment</p>
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

        
        <div class="dash-body">
                
                <tr>
    <td colspan="4" style="padding:0;">
        <div class="nav-bar animate-slide-up">
            <div>
                <a href="radiology_schedule.php" class="non-style-link">
                    <button class="btn-primary btn-icon-back" style="padding: 8px 15px;">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                </a>
            </div>
            <div>
                <p class="header-title">Radiology Schedule Management</p>
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
        <i class="fas fa-calendar-plus"></i>
        Manage Radiology Sessions
    </div>
    <button class="btn-primary" onclick="openAddSessionModal()">
        <i class="fas fa-plus"></i> Add Session
    </button>
</div>

<div class="table-container animate-slide-up">
    <p class="table-title">
        <i class="fas fa-calendar-check"></i>
        All Radiology Sessions (<?php echo $total_sessions; ?>)
    </p>
    
    <div class="filter-container animate-slide-up">
        <form action="" method="POST">
            <table border="0" width="100%">
                <tr>
                    <td width="10%" style="text-align: center;">Date:</td>
                    <td width="20%">
                        <input type="date" name="scheduledate" id="date" class="filter-container-items">
                    </td>
                    <td width="10%" style="text-align: center;">Radiology Type:</td>
                    <td width="30%">
                        <select name="radiology_type" class="filter-container-items">
                            <option value="" disabled selected>Select Radiology Type</option>
                            <option value="xray">X-Ray</option>
                            <option value="ct">CT Scan</option>
                            <option value="mri">MRI</option>
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
    <table width="100%" class="sub-table" border="0">
        <thead>
            <tr>
                <th class="table-headin">Radiology Type</th>
                <th class="table-headin">Technician</th>
                <th class="table-headin">Date & Time</th>
                <th class="table-headin">Duration</th>
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
                                                <td colspan="8">
                                                <br><br><br><br>
                                                <center>
                                                <img src="../img/notfound.svg" width="25%">
                                                <br>
                                                <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">No sessions available</p>
                                                <a class="non-style-link" href="radiology_schedule.php?action=add-session">
                                                    <button class="login-btn btn-primary-soft btn">Add New Session</button>
                                                </a>
                                                </center>
                                                <br><br><br><br>
                                                </td>
                                                </tr>';
                                        } else {
                                            while($row = $result->fetch_assoc()){
                                                // Count bookings
                                                $booked_query = $database->query("SELECT COUNT(*) as booked FROM radiology_appointment WHERE scheduleid=".$row['scheduleid']);
                                                $booked = $booked_query->fetch_assoc()['booked'];
                                                
                                                // Calculate end time
                                                $end_time = date('H:i', strtotime($row['scheduletime']) + ($row['duration'] * 60));
                                                
                                                echo '<tr>
                                                    <td>'.ucfirst(htmlspecialchars($row['session_type'])).'</td>
                                                    <td>'.htmlspecialchars($row['technician_name']).'</td>
                                                    <td style="text-align:center;">
                                                        '.htmlspecialchars($row['scheduledate']).' 
                                                        '.substr($row['scheduletime'], 0, 5).' - 
                                                        '.$end_time.'
                                                    </td>
                                                    <td style="text-align:center;">'.htmlspecialchars($row['duration']).' minutes</td>
                                                    <td style="text-align:center;">'.htmlspecialchars($row['nop']).'</td>
                                                    <td style="text-align:center;">'.$booked.'</td>
                                                    <td style="text-align:center;">'.($booked < $row['nop'] ? 'Available' : 'Full').'</td>
                                                    <td>
                                                        <div style="display:flex;justify-content: center;">
                                                            <a href="?action=view&id='.$row['scheduleid'].'" class="non-style-link">
                                                                <button class="btn-primary-soft btn button-icon btn-view">View</button>
                                                            </a>
                                                            &nbsp;&nbsp;&nbsp;
                                                            <a href="?action=drop&id='.$row['scheduleid'].'" class="non-style-link">
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
                        </center>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Add Session Modal -->
    <div id="add-session-modal" class="overlay" style="display: none;">
        <div class="popup">
            <a href="radiology_schedule.php" class="close" title="Close" onclick="closeModal()">&times;</a>
            <center>
                <h2>Add New Radiology Session</h2>
                <div class="add-doc-form-container">
                    <form action="add-radiology-session.php" method="POST" class="add-doc-form" onsubmit="return validateForm()">
                        <table border="0" width="100%" class="schedule-form-table">
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="title" class="form-label">Session Title: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <input type="text" name="title" id="title" class="input-text" placeholder="Session Title" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="techid" class="form-label">Radiology Technician: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <select name="techid" id="techid" class="input-text" required>
                                        <option value="" disabled selected>Select Technician</option>
                                        <?php 
                                        $technicians_query = $database->query("SELECT * FROM radiology_technicians ORDER BY techname ASC");
                                        while($tech = $technicians_query->fetch_assoc()){
                                            echo "<option value='".$tech['techid']."'>".$tech['techname']."</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="session_type" class="form-label">Session Type: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <select name="session_type" id="session_type" class="input-text" required>
                                        <option value="" disabled selected>Select Session Type</option>
                                        <option value="xray">X-Ray</option>
                                        <option value="ct">CT Scan</option>
                                        <option value="mri">MRI</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="scheduledate" class="form-label">Scheduled Date: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <input type="date" name="scheduledate" id="scheduledate" class="input-text" min="<?php echo date('Y-m-d'); ?>" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="scheduletime" class="form-label">Scheduled Time: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <input type="time" name="scheduletime" id="scheduletime" class="input-text" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="nop" class="form-label">Number of Patients (Max Appointments): </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <input type="number" name="nop" id="nop" class="input-text" placeholder="e.g. 10" min="1" max="50" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="duration" class="form-label">Duration (minutes): </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <input type="number" name="duration" id="duration" class="input-text" placeholder="e.g. 30" min="15" max="180" required>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <input type="submit" value="Add Session" class="login-btn btn-primary btn">
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </center>
        </div>
    </div>

    <?php
    // Handle view/delete requests
    if(isset($_GET['action']) && isset($_GET['id'])){
        $action = $_GET['action'];
        $id = intval($_GET['id']);
        
        if($action == 'view'){
            // Get session details
            $sqlmain = "SELECT 
                rs.*, 
                rt.techname as technician_name
                FROM radiology_schedule rs
                INNER JOIN radiology_technicians rt ON rs.techid = rt.techid
                WHERE rs.scheduleid = " . $id;
                
            $result = $database->query($sqlmain);
            $schedule = $result->fetch_assoc();
            
            // Get booked appointments
            $sqlapp = "SELECT 
                ra.*, 
                p.pname as patient_name,
                p.ptel as patient_phone
                FROM radiology_appointment ra
                LEFT JOIN patient p ON ra.pid = p.pid
                WHERE ra.scheduleid = " . $id . "
                ORDER BY ra.apponum ASC";
                
            $resultapp = $database->query($sqlapp);
            $appointments = [];
            
            if($resultapp !== false) {
                while($row = $resultapp->fetch_assoc()) {
                    $appointments[] = $row;
                }
            }
            
            // Calculate end time
            $end_time = date('H:i', strtotime($schedule['scheduletime']) + ($schedule['duration'] * 60));
            
            // Show view popup
            echo '
            <div id="popup1" class="overlay">
                <div class="popup">
                    <a href="radiology_schedule.php" class="close" title="Close">&times;</a>
                    <center>
                        <h2>Session Details</h2>
                        <div class="content">
                            <div style="margin: 20px 0;">
                                <table class="sub-table" border="0">
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <h2>Session Information</h2>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td">Radiology Type:</td>
                                        <td>'.ucfirst(htmlspecialchars($schedule['session_type'])).'</td>
                                    </tr>
                                    <tr>
                                        <td class="label-td">Technician:</td>
                                        <td>'.htmlspecialchars($schedule['technician_name']).'</td>
                                    </tr>
                                    <tr>
                                        <td class="label-td">Date:</td>
                                        <td>'.htmlspecialchars($schedule['scheduledate']).'</td>
                                    </tr>
                                    <tr>
                                        <td class="label-td">Time:</td>
                                        <td>'.htmlspecialchars($schedule['scheduletime']).' - '.$end_time.'</td>
                                    </tr>
                                    <tr>
                                        <td class="label-td">Duration:</td>
                                        <td>'.htmlspecialchars($schedule['duration']).' minutes</td>
                                    </tr>
                                    <tr>
                                        <td class="label-td">Max Appointments:</td>
                                        <td>'.htmlspecialchars($schedule['nop']).'</td>
                                    </tr>
                                </table>
                                
                                <table class="sub-table" border="0" style="margin-top: 20px;">
                                    <tr>
                                        <td class="label-td" colspan="4">
                                            <h2>Booked Appointments ('.count($appointments).')</h2>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-headin">Appointment #</th>
                                        <th class="table-headin">Patient Name</th>
                                        <th class="table-headin">Phone</th>
                                        <th class="table-headin">Status</th>
                                    </tr>';
                                    
                                    if(count($appointments) == 0) {
                                        echo '<tr>
                                            <td colspan="4" style="text-align: center;">
                                                No appointments booked for this session
                                            </td>
                                        </tr>';
                                    } else {
                                        foreach($appointments as $appointment) {
                                            $status_class = '';
                                            switch($appointment['status']) {
                                                case 'Completed':
                                                    $status_class = 'success';
                                                    break;
                                                case 'Cancelled':
                                                    $status_class = 'danger';
                                                    break;
                                                default:
                                                    $status_class = 'info';
                                            }
                                            
                                            echo '<tr>
                                                <td style="text-align:center;font-size:23px;font-weight:500; color: var(--btnnicetext);">
                                                    '.htmlspecialchars($appointment['apponum'] ?? '').'
                                                </td>
                                                <td style="font-weight:600;padding:25px">
                                                    '.htmlspecialchars($appointment['patient_name'] ?? '').'
                                                </td>
                                                <td>
                                                    '.htmlspecialchars($appointment['patient_phone'] ?? '').'
                                                </td>
                                                <td>
                                                    <span class="badge badge-'.$status_class.'">
                                                        '.ucfirst(htmlspecialchars($appointment['status'] ?? '')).'
                                                    </span>
                                                </td>
                                            </tr>';
                                        }
                                    }
                                    
                                    echo '
                                </table>
                            </div>
                        </div>
                    </center>
                </div>
            </div>';
            
        } elseif($action == 'drop'){
            // Get session details for confirmation
            $session_query = $database->query("SELECT 
                rs.session_type,
                rs.scheduledate,
                rs.scheduletime
                FROM radiology_schedule rs
                WHERE rs.scheduleid = " . $id);
                
            $session = $session_query->fetch_assoc();
            
            echo '
            <div id="popup1" class="overlay">
                <div class="popup">
                    <a href="radiology_schedule.php" class="close" title="Close">&times;</a>
                    <center>
                        <h2>Are you sure?</h2>
                        <div class="content">
                            You are about to delete this radiology session:<br>
                            <b>'.ucfirst(htmlspecialchars($session['session_type'] ?? '')).' - '.htmlspecialchars($session['scheduledate'] ?? '').' '.htmlspecialchars($session['scheduletime'] ?? '').'</b><br><br>
                            This action cannot be undone.
                        </div>
                        <div style="display: flex;justify-content: center;">
                            <a href="delete-radiology-session.php?id='.$id.'" class="non-style-link">
                                <button class="btn-primary btn" style="margin:10px;padding:10px;">Yes, Delete</button>
                            </a>
                            <a href="radiology_schedule.php" class="non-style-link">
                                <button class="btn-primary btn" style="margin:10px;padding:10px;">Cancel</button>
                            </a>
                        </div>
                    </center>
                </div>
            </div>';
        }
    }
    
    // Show error messages if any
    if(isset($_GET['error'])){
        $error_msg = "";
        switch($_GET['error']){
            case 'db_error': 
                $error_msg = "Database error occurred. Please try again."; 
                break;
            case 'not_found': 
                $error_msg = "Session not found."; 
                break;
            case 'has_appointments': 
                $error_msg = "Cannot delete session with existing appointments."; 
                break;
            case 'empty_fields': 
                $error_msg = "Please fill in all required fields."; 
                break;
            default: 
                $error_msg = "An unknown error occurred."; 
        }
        
        echo '<div class="error-message animate-slide-up" style="background: #fee2e2; color: #dc2626; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; text-align: center;">
            <i class="fas fa-exclamation-circle"></i> '.$error_msg.'
        </div>';
    }
    
    // Show success messages if any
    if(isset($_GET['success'])){
        $success_msg = "";
        switch($_GET['success']){
            case 'deleted': 
                $success_msg = "Session deleted successfully."; 
                break;
            case 'added': 
                $success_msg = "Session added successfully."; 
                break;
            default: 
                $success_msg = "Operation completed successfully."; 
        }
        
        echo '<div class="success-message animate-slide-up" style="background: #dcfce7; color: #16a34a; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; text-align: center;">
            <i class="fas fa-check-circle"></i> '.$success_msg.'
        </div>';
    }
    ?>
    <script>
        // Function to open add session modal
        function openAddSessionModal() {
            document.getElementById('add-session-modal').style.display = 'flex';
        }
        
        // Function to close modal
        function closeModal() {
            document.getElementById('add-session-modal').style.display = 'none';
        }
        
        // Form validation function
        function validateForm() {
            const title = document.getElementById('title').value.trim();
            const techid = document.getElementById('techid').value;
            const session_type = document.getElementById('session_type').value;
            const scheduledate = document.getElementById('scheduledate').value;
            const scheduletime = document.getElementById('scheduletime').value;
            const nop = document.getElementById('nop').value;
            const duration = document.getElementById('duration').value;
            
            if (!title || !techid || !session_type || !scheduledate || !scheduletime || !nop || !duration) {
                alert('Please fill in all required fields.');
                return false;
            }
            
            if (nop < 1 || nop > 50) {
                alert('Number of patients must be between 1 and 50.');
                return false;
            }
            
            if (duration < 15 || duration > 180) {
                alert('Duration must be between 15 and 180 minutes.');
                return false;
            }
            
            // Check if date is not in the past
            const selectedDate = new Date(scheduledate);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                alert('Scheduled date cannot be in the past.');
                return false;
            }
            
            return true;
        }
        
        // Close modal when clicking outside
        document.getElementById('add-session-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Close modal with escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
        
        // Auto-open modal if there's an error
        <?php if(isset($_GET['error']) && $_GET['error'] == 'empty_fields'): ?>
        document.addEventListener('DOMContentLoaded', function() {
            openAddSessionModal();
        });
        <?php endif; ?>
        
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
        
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const sidebarMenu = document.getElementById('sidebarMenu');
            const mainContent = document.getElementById('mainContent');
            
            menuToggle.addEventListener('click', function() {
                sidebarMenu.classList.toggle('active');
                this.classList.toggle('active');
                mainContent.classList.toggle('sidebar-active');
            });
        });
    </script>

    <!-- Delete Confirmation Modal -->
    <div id="delete-confirmation-modal" class="overlay" style="display: none;">
        <div class="popup">
            <a href="javascript:void(0)" class="close" title="Close" onclick="closeDeleteModal()">&times;</a>
            <center>
                <h2>Confirm Deletion</h2>
                <div class="content">
                    <p>Are you sure you want to delete this radiology session?</p>
                    <div id="delete-session-details" style="margin: 20px 0; padding: 15px; background: #f8fafc; border-radius: 8px;">
                        <p><strong>Radiology Type:</strong> <span id="delete-session-type"></span></p>
                        <p><strong>Date:</strong> <span id="delete-session-date"></span></p>
                        <p><strong>Time:</strong> <span id="delete-session-time"></span></p>
                    </div>
                    <p style="color: #dc2626; font-weight: 500;">This action cannot be undone.</p>
                </div>
                <div style="display: flex; justify-content: center; gap: 20px; margin-top: 20px;">
                    <button onclick="proceedWithDelete()" class="btn-primary btn" style="background: #dc2626;">Yes, Delete</button>
                    <button onclick="closeDeleteModal()" class="btn-primary-soft btn">Cancel</button>
                </div>
            </center>
        </div>
    </div>

    <script>
        let sessionToDelete = null;

        function confirmDelete(scheduleId, sessionType, sessionDate, sessionTime) {
            sessionToDelete = scheduleId;
            document.getElementById('delete-session-type').textContent = sessionType;
            document.getElementById('delete-session-date').textContent = sessionDate;
            document.getElementById('delete-session-time').textContent = sessionTime;
            document.getElementById('delete-confirmation-modal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('delete-confirmation-modal').style.display = 'none';
            sessionToDelete = null;
        }

        function proceedWithDelete() {
            if (sessionToDelete) {
                window.location.href = '?action=drop&id=' + sessionToDelete;
            }
        }

        // Close modal when clicking outside
        document.getElementById('delete-confirmation-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // Close modal with escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
            }
        });
    </script>
</body>
</html>