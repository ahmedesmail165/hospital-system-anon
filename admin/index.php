<?php
session_start();
if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
        header("location: ../login.php");
    }
}else{
    header("location: ../login.php");
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
    <link rel="stylesheet" href="../css/sidebar.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <title>Dashboard</title>
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
        
        /* تحسينات التصميم الأساسية */
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
        
        .header-search {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-grow: 1;
            max-width: 600px;
            position: relative;
        }
        
        .header-searchbar {
            padding: 0.875rem 1.5rem;
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            width: 100%;
            font-size: 0.9375rem;
            transition: var(--transition);
            background: white;
            box-shadow: var(--shadow-sm);
            color: var(--text-primary);
        }
        
        .header-searchbar:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }
        
        .header-searchbar::placeholder {
            color: var(--text-secondary);
            opacity: 0.7;
        }
        
        .search-btn {
            padding: 0.875rem 1.75rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 0.75rem;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2);
        }
        
        .search-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
        }
        
        .search-btn i {
            margin-right: 0.5rem;
            font-size: 1rem;
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
        
        /* بطاقات الإحصائيات */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.75rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border-color);
            z-index: 1;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary-color);
            transition: var(--transition);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .stat-card:hover::before {
            width: 100%;
            opacity: 0.1;
        }
        
        .stat-icon {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            background: var(--primary-light);
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.75rem;
            transition: var(--transition);
        }
        
        .stat-card:hover .stat-icon {
            transform: rotate(10deg) scale(1.1);
            background: var(--primary-color);
            color: white;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0.5rem 0;
            position: relative;
            transition: var(--transition);
        }
        
        .stat-card:hover .stat-value {
            color: var(--primary-dark);
        }
        
        .stat-label {
            color: var(--text-secondary);
            font-size: 0.9375rem;
            margin-bottom: 0.5rem;
            position: relative;
        }
        
        .trend-indicator {
            display: inline-flex;
            align-items: center;
            font-size: 0.8125rem;
            font-weight: 500;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            margin-top: 0.25rem;
            position: relative;
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }
        
        .trend-up {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }
        
        .trend-down {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }
        
        .trend-indicator i {
            margin-right: 0.25rem;
            font-size: 0.75rem;
        }
        
        /* الرسوم البيانية */
        .chart-container {
            background: white;
            border-radius: 1rem;
            padding: 2.5rem 2.5rem 2.5rem 2.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            position: relative;
            height: 430px;
            transition: var(--transition);
            border: 1px solid var(--border-color);
        }
        
        .chart-container:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-3px);
        }
        
        .chart-title {
            margin-top: 0;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .chart-title i {
            margin-right: 0.75rem;
            color: var(--primary-color);
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
        
        /* زر التقرير */
        .download-report-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 1rem 1.75rem;
            border-radius: 0.75rem;
            cursor: pointer;
            font-size: 0.9375rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            transition: var(--transition);
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }
        
        .download-report-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: var(--transition-slow);
        }
        
        .download-report-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }
        
        .download-report-btn:hover::after {
            left: 100%;
        }
        
        .download-report-btn i {
            margin-right: 0.75rem;
            font-size: 1.1rem;
            transition: var(--transition);
        }
        
        .download-report-btn:hover i {
            transform: rotate(360deg);
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
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .chart-grid {
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
            
            .header-search {
                max-width: 100%;
                width: 100%;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
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
            
            .chart-container {
                height: 350px;
                padding: 1.25rem;
            }
        }
        
        /* تأثيرات إضافية */
        .hover-scale {
            transition: var(--transition);
        }
        
        .hover-scale:hover {
            transform: scale(1.03);
        }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: 1rem;
            margin-left: 0.5rem;
        }
        
        .badge-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }
        
        .badge-warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
        }
        
        .badge-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }
        
        .badge-primary {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary-color);
        }
        
        /* تأثيرات الحركة */
        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        .animate-slide-up {
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        
        .animate-delay-1 {
            animation-delay: 0.1s;
        }
        
        .animate-delay-2 {
            animation-delay: 0.2s;
        }
        
        .animate-delay-3 {
            animation-delay: 0.3s;
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
        
        /* تأثيرات خاصة للبطاقات */
        .stat-card:nth-child(1) {
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        
        .stat-card:nth-child(2) {
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.1s forwards;
        }
        
        .stat-card:nth-child(3) {
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards;
        }
        
        .stat-card:nth-child(4) {
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.3s forwards;
        }
    </style>
</head>
<body>
    <?php
    include("../connection.php");
    
    // تحديد التاريخ الحالي والفترات الزمنية
    date_default_timezone_set('Asia/Kolkata');
    $today = date("Y-m-d");
    $lastWeek = date("Y-m-d", strtotime("-1 week"));
    $lastMonth = date("Y-m-d", strtotime("-1 month"));
    $lastYear = date("Y-m-d", strtotime("-1 year"));
    
    // استعلامات البيانات الأساسية
    $totalPatients = $database->query("SELECT COUNT(*) FROM patient")->fetch_row()[0];
    $totalDoctors = $database->query("SELECT COUNT(*) FROM doctor")->fetch_row()[0];
    $todayAppointments = $database->query("SELECT COUNT(*) FROM appointment WHERE appodate='$today'")->fetch_row()[0];
    $activeDoctors = $database->query("SELECT COUNT(DISTINCT docid) FROM schedule WHERE scheduledate = '$today'")->fetch_row()[0];
    
    // بيانات المواعيد
    $totalAppointments = $database->query("SELECT COUNT(*) FROM appointment")->fetch_row()[0];
    $completedAppointments = $database->query("SELECT COUNT(*) FROM appointment WHERE status='done'")->fetch_row()[0];
    $pendingAppointments = $database->query("SELECT COUNT(*) FROM appointment WHERE status='pending'")->fetch_row()[0];
    $cancelledAppointments = $database->query("SELECT COUNT(*) FROM appointment WHERE status='cancelled'")->fetch_row()[0];
    
    // بيانات المرضى
    $malePatients = $database->query("SELECT COUNT(*) FROM patient WHERE gender='male'")->fetch_row()[0];
    $femalePatients = $database->query("SELECT COUNT(*) FROM patient WHERE gender='female'")->fetch_row()[0];
    $newPatients = $database->query("SELECT COUNT(*) FROM patient WHERE pdob >= '$lastMonth'")->fetch_row()[0];
    
    // بيانات مالية
    $totalRevenue = $database->query("SELECT SUM(amount) FROM payments WHERE status='completed'")->fetch_row()[0];
    $monthlyRevenue = $database->query("SELECT SUM(amount) FROM payments WHERE status='completed' AND payment_date >= '$lastMonth'")->fetch_row()[0];
    $weeklyRevenue = $database->query("SELECT SUM(amount) FROM payments WHERE status='completed' AND payment_date >= '$lastWeek'")->fetch_row()[0];
    
    // بيانات الفواتير
    $paidInvoices = $database->query("SELECT COUNT(*) FROM invoices WHERE status='paid'")->fetch_row()[0];
    $unpaidInvoices = $database->query("SELECT COUNT(*) FROM invoices WHERE status='unpaid'")->fetch_row()[0];
    $overdueInvoices = $database->query("SELECT COUNT(*) FROM invoices WHERE status='overdue'")->fetch_row()[0];
    
    // بيانات المختبر والأشعة
    $totalLabTests = $database->query("SELECT COUNT(*) FROM lab_appointments")->fetch_row()[0];
    $totalRadiologyTests = $database->query("SELECT COUNT(*) FROM radiology_appointment")->fetch_row()[0];
    
    // استعلامات للرسوم البيانية
    // بيانات المواعيد الشهرية
    $monthlyAppointments = $database->query("
        SELECT DATE_FORMAT(appodate, '%Y-%m') as month, COUNT(*) as count 
        FROM appointment 
        WHERE appodate >= DATE_SUB('$today', INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(appodate, '%Y-%m')
        ORDER BY month ASC
    ");
    
    $monthlyLabels = [];
    $monthlyValues = [];
    while($row = $monthlyAppointments->fetch_assoc()) {
        $monthlyLabels[] = date("M Y", strtotime($row['month'] . '-01'));
        $monthlyValues[] = $row['count'];
    }
    
    // بيانات أنواع المواعيد
    $appointmentTypes = $database->query("
        SELECT type, COUNT(*) as count 
        FROM appointment 
        GROUP BY type
    ");
    
    $typeLabels = [];
    $typeValues = [];
    while($row = $appointmentTypes->fetch_assoc()) {
        $typeLabels[] = ucfirst($row['type']);
        $typeValues[] = $row['count'];
    }
    
    // بيانات الإيرادات الشهرية
    $monthlyRevenueData = $database->query("
        SELECT DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(amount) as total 
        FROM payments 
        WHERE status='completed' AND payment_date >= DATE_SUB('$today', INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
        ORDER BY month ASC
    ");
    
    $revenueLabels = [];
    $revenueValues = [];
    while($row = $monthlyRevenueData->fetch_assoc()) {
        $revenueLabels[] = date("M Y", strtotime($row['month'] . '-01'));
        $revenueValues[] = $row['total'];
    }
    
    // المواعيد القادمة
    $upcomingAppointments = $database->query("
        SELECT appointment.appoid, schedule.scheduleid, schedule.title, doctor.docname, 
               patient.pname, schedule.scheduledate, schedule.scheduletime, 
               appointment.apponum, appointment.appodate 
        FROM schedule 
        INNER JOIN appointment ON schedule.scheduleid=appointment.scheduleid 
        INNER JOIN patient ON patient.pid=appointment.pid 
        INNER JOIN doctor ON schedule.docid=doctor.docid 
        WHERE schedule.scheduledate >= CURRENT_DATE 
        ORDER BY schedule.scheduledate ASC
    ");
    
    // الجلسات القادمة
    $upcomingSessions = $database->query("
        SELECT s.title, d.docname, s.scheduledate, s.scheduletime, s.nop
        FROM schedule s
        JOIN doctor d ON s.docid = d.docid
        WHERE s.scheduledate >= '$today'
        ORDER BY s.scheduledate ASC
        LIMIT 10
    ");
    ?>
    
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
                        <button class="menu-btn menu-active">
                            <i class="fas fa-tachometer-alt menu-icon"></i>
                            <p class="menu-text">Dashboard</p>
                        </button>
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
            <!-- شريط البحث وتاريخ اليوم -->
            <div class="nav-bar animate-slide-up">
                <form action="doctors.php" method="post" class="header-search">
                    <input type="search" name="search" class="input-text header-searchbar" placeholder="Search Doctor name or Email" list="doctors">
                    <?php
                        echo '<datalist id="doctors">';
                        $list11 = $database->query("SELECT docname,docemail FROM doctor;");
                        for ($y=0;$y<$list11->num_rows;$y++){
                            $row00=$list11->fetch_assoc();
                            $d=$row00["docname"];
                            $c=$row00["docemail"];
                            echo "<option value='$d'><br/>";
                            echo "<option value='$c'><br/>";
                        };
                        echo ' </datalist>';
                    ?>
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                        Search
                    </button>
                </form>
                
                <div class="date-container">
                    <i class="far fa-calendar-alt date-icon"></i>
                    <div class="date-text">
                        <p class="date-label">Today's Date</p>
                        <p class="current-date"><?php echo date("F j, Y"); ?></p>
                    </div>
                </div>
            </div>

            <!-- بطاقات الإحصائيات -->
            <div class="stats-grid">
                <div class="stat-card hover-scale">
                    <div class="stat-icon">
                        <i class="fas fa-user-injured"></i>
                    </div>
                    <div class="stat-value"><?php echo $totalPatients; ?></div>
                    <div class="stat-label">Total Patients</div>
                    <div class="trend-indicator trend-up">
                        <i class="fas fa-arrow-up"></i>
                        +<?php echo $newPatients; ?> this month
                    </div>
                </div>
                
                <div class="stat-card hover-scale">
                    <div class="stat-icon">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="stat-value"><?php echo $totalDoctors; ?></div>
                    <div class="stat-label">Total Doctors</div>
                    <div class="stat-label">
                        <span class="badge badge-primary"><?php echo $activeDoctors; ?> active today</span>
                    </div>
                </div>
                
                <div class="stat-card hover-scale">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-value"><?php echo $todayAppointments; ?></div>
                    <div class="stat-label">Today's Appointments</div>
                    <div class="stat-label">
                        <span class="badge badge-warning"><?php echo $pendingAppointments; ?> pending</span>
                    </div>
                </div>
                
                <div class="stat-card hover-scale">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-value">$<?php echo number_format($monthlyRevenue, 2); ?></div>
                    <div class="stat-label">Monthly Revenue</div>
                    <div class="trend-indicator trend-up">
                        <i class="fas fa-arrow-up"></i>
                        +$<?php echo number_format($weeklyRevenue, 2); ?> this week
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-value"><?php echo $completedAppointments; ?></div>
                    <div class="stat-label">Completed Appointments</div>
                </div>
            </div>

            <!-- زر تحميل التقرير -->
            <div class="report-section animate-slide-up animate-delay-1">
                <a href="generate_pdf_summary.php" class="non-style-link">
                    <button class="download-report-btn hover-scale">
                        <i class="fas fa-file-pdf"></i>
                        Download System Report (PDF)
                    </button>
                </a>
            </div>

            <!-- الرسوم البيانية -->
            <div class="chart-container hover-scale animate-slide-up animate-delay-1">
                <h3 class="chart-title">
                    <i class="fas fa-chart-line"></i>
                    Monthly Appointments Trend
                </h3>
                <canvas id="appointmentsChart"></canvas>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div class="chart-container hover-scale animate-slide-up animate-delay-2">
                    <h3 class="chart-title">
                        <i class="fas fa-pie-chart"></i>
                        Appointment Types Distribution
                    </h3>
                    <canvas id="typesChart"></canvas>
                </div>
                
                <div class="chart-container hover-scale animate-slide-up animate-delay-2">
                    <h3 class="chart-title">
                        <i class="fas fa-money-bill-wave"></i>
                        Monthly Revenue Trend
                    </h3>
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Gender Distribution Chart -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div class="chart-container hover-scale animate-slide-up animate-delay-2">
                    <h3 class="chart-title">
                        <i class="fas fa-venus-mars"></i>
                        Patient Gender Distribution
                    </h3>
                    <canvas id="genderChart"></canvas>
                </div>

                <div class="chart-container hover-scale animate-slide-up animate-delay-2">
                    <h3 class="chart-title">
                        <i class="fas fa-calendar-check"></i>
                        Appointment Status Distribution
                    </h3>
                    <canvas id="appointmentStatusChart"></canvas>
                </div>
            </div>

            <!-- المواعيد القادمة -->
            <div class="table-container hover-scale animate-slide-up animate-delay-3">
                <h3 class="table-title">
                    <i class="fas fa-calendar-day"></i>
                    Upcoming Appointments
                </h3>
                <table class="sub-table">
                    <thead>
                        <tr>
                            <th class="table-headin">Appointment #</th>
                            <th class="table-headin">Patient Name</th>
                            <th class="table-headin">Doctor</th>
                            <th class="table-headin">Session</th>
                            <th class="table-headin">Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if($upcomingAppointments->num_rows > 0) {
                            while($row = $upcomingAppointments->fetch_assoc()) {
                                echo '<tr>
                                    <td>'.$row['apponum'].'</td>
                                    <td>'.$row['pname'].'</td>
                                    <td>'.$row['docname'].'</td>
                                    <td>'.$row['title'].'</td>
                                    <td>'.$row['scheduledate'].' '.$row['scheduletime'].'</td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="5" style="text-align: center;">No upcoming appointments</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>

           
            <div class="table-container hover-scale animate-slide-up animate-delay-3" style="margin-top: 1.5rem;">
                <h3 class="table-title">
                    <i class="fas fa-calendar-day"></i>
                    Upcoming Sessions
                </h3>
                <table class="sub-table">
                    <thead>
                        <tr>
                            <th class="table-headin">Session Title</th>
                            <th class="table-headin">Doctor</th>
                            <th class="table-headin">Date</th>
                            <th class="table-headin">Time</th>
                            <th class="table-headin">Patients</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if($upcomingSessions->num_rows > 0) {
                            while($row = $upcomingSessions->fetch_assoc()) {
                                echo '<tr>
                                    <td>'.$row['title'].'</td>
                                    <td>'.$row['docname'].'</td>
                                    <td>'.$row['scheduledate'].'</td>
                                    <td>'.$row['scheduletime'].'</td>
                                    <td>'.$row['nop'].'</td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="5" style="text-align: center;">No upcoming sessions</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle sidebar menu
        const menuToggle = document.getElementById('menuToggle');
        const sidebarMenu = document.getElementById('sidebarMenu');
        
        menuToggle.addEventListener('click', function() {
            sidebarMenu.classList.toggle('active');
            this.classList.toggle('active');
        });
        
        // Add animation class to elements when they come into view
        const animateElements = document.querySelectorAll('.animate-slide-up, .animate-fade-in');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate__animated');
                    if (entry.target.classList.contains('animate-slide-up')) {
                        entry.target.classList.add('animate__fadeInUp');
                    } else if (entry.target.classList.contains('animate-fade-in')) {
                        entry.target.classList.add('animate__fadeIn');
                    }
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1
        });
        
        animateElements.forEach(element => {
            observer.observe(element);
        });
        
        // Monthly Appointments Chart
        const appointmentsCtx = document.getElementById('appointmentsChart').getContext('2d');
        new Chart(appointmentsCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($monthlyLabels); ?>,
                datasets: [{
                    label: 'Appointments',
                    data: <?php echo json_encode($monthlyValues); ?>,
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderColor: 'rgba(37, 99, 235, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: 'white',
                    pointBorderColor: 'rgba(37, 99, 235, 1)',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(30, 41, 59, 0.9)',
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(226, 232, 240, 0.5)'
                        },
                        ticks: {
                            color: 'var(--text-secondary)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: 'var(--text-secondary)'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'nearest'
                }
            }
        });

        // Appointment Types Chart
        const typesCtx = document.getElementById('typesChart').getContext('2d');
        new Chart(typesCtx, {
            type: 'doughnut',
            data: {
                labels: ['General Checkup', 'Radiology', 'Laboratory', 'Specialist', 'Emergency'],
                datasets: [{
                    data: <?php echo json_encode($typeValues); ?>,
                    backgroundColor: [
                        'rgba(37, 99, 235, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)'
                    ],
                    borderColor: [
                        'rgba(37, 99, 235, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(139, 92, 246, 1)'
                    ],
                    borderWidth: 1,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: 'var(--text-primary)',
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(30, 41, 59, 0.9)',
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                cutout: '70%',
                radius: '90%'
            }
        });

        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($revenueLabels); ?>,
                datasets: [{
                    label: 'Revenue ($)',
                    data: <?php echo json_encode($revenueValues); ?>,
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 1,
                    borderRadius: 6,
                    hoverBackgroundColor: 'rgba(16, 185, 129, 1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(30, 41, 59, 0.9)',
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return '$' + context.raw.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(226, 232, 240, 0.5)'
                        },
                        ticks: {
                            color: 'var(--text-secondary)',
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: 'var(--text-secondary)'
                        }
                    }
                }
            }
        });

        // Gender Distribution Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        const totalPatients = <?php echo $malePatients + $femalePatients; ?>;
        const malePercentage = ((<?php echo $malePatients; ?> / totalPatients) * 100).toFixed(1);
        const femalePercentage = ((<?php echo $femalePatients; ?> / totalPatients) * 100).toFixed(1);

        new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: [`Male (${malePercentage}%)`, `Female (${femalePercentage}%)`],
                datasets: [{
                    data: [<?php echo $malePatients; ?>, <?php echo $femalePatients; ?>],
                    backgroundColor: [
                        'rgba(37, 99, 235, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ],
                    borderColor: [
                        'rgba(37, 99, 235, 1)',
                        'rgba(239, 68, 68, 1)'
                    ],
                    borderWidth: 2,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: 'var(--text-primary)',
                            padding: 20,
                            font: {
                                size: 14,
                                weight: 'bold'
                            },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(30, 41, 59, 0.9)',
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const percentage = context.dataset.data[context.dataIndex] / totalPatients * 100;
                                return `${label}: ${value} patients (${percentage.toFixed(1)}%)`;
                            }
                        }
                    }
                },
                cutout: '60%',
                radius: '90%'
            }
        });

        // Appointment Status Chart
        const appointmentStatusCtx = document.getElementById('appointmentStatusChart').getContext('2d');
        const totalAppointments = <?php echo $totalAppointments; ?>;
        const completedPercentage = ((<?php echo $completedAppointments; ?> / totalAppointments) * 100).toFixed(1);
        const pendingPercentage = ((<?php echo $pendingAppointments; ?> / totalAppointments) * 100).toFixed(1);
        const cancelledPercentage = ((<?php echo $cancelledAppointments; ?> / totalAppointments) * 100).toFixed(1);

        new Chart(appointmentStatusCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    `Completed (${completedPercentage}%)`,
                    `Pending (${pendingPercentage}%)`,
                    `Cancelled (${cancelledPercentage}%)`
                ],
                datasets: [{
                    data: [
                        <?php echo $completedAppointments; ?>,
                        <?php echo $pendingAppointments; ?>,
                        <?php echo $cancelledAppointments; ?>
                    ],
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.8)',  // أخضر للمكتملة
                        'rgba(245, 158, 11, 0.8)',   // برتقالي للمعلقة
                        'rgba(239, 68, 68, 0.8)'     // أحمر للملغاة
                    ],
                    borderColor: [
                        'rgba(16, 185, 129, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(239, 68, 68, 1)'
                    ],
                    borderWidth: 2,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: 'var(--text-primary)',
                            padding: 20,
                            font: {
                                size: 14,
                                weight: 'bold'
                            },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(30, 41, 59, 0.9)',
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const percentage = context.dataset.data[context.dataIndex] / totalAppointments * 100;
                                return `${label}: ${value} appointments (${percentage.toFixed(1)}%)`;
                            }
                        }
                    }
                },
                cutout: '60%',
                radius: '90%'
            }
        });
    });
    </script>
</body>
</html>