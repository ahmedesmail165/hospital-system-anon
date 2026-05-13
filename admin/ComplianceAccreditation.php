<?php
    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
            header("location: ../login.php");
        }
    }else{
        header("location: ../login.php");
    }
    
    include("../connection.php");
    
    // استرجاع بيانات الامتثال الشهري
    $monthly_compliance_query = "SELECT DATE_FORMAT(month, '%b') as month_name, score FROM monthly_compliance ORDER BY month ASC";
    $monthly_compliance_result = mysqli_query($database, $monthly_compliance_query);
    $monthly_labels = [];
    $monthly_scores = [];
    while($row = mysqli_fetch_assoc($monthly_compliance_result)) {
        $monthly_labels[] = $row['month_name'];
        $monthly_scores[] = $row['score'];
    }

    // استرجاع بيانات فئات الامتثال
    $categories_query = "SELECT category_name, score FROM compliance_categories";
    $categories_result = mysqli_query($database, $categories_query);
    $category_labels = [];
    $category_scores = [];
    while($row = mysqli_fetch_assoc($categories_result)) {
        $category_labels[] = $row['category_name'];
        $category_scores[] = $row['score'];
    }

    // استرجاع بيانات امتثال الأقسام
    $departments_query = "SELECT department_name, score FROM department_compliance ORDER BY score DESC";
    $departments_result = mysqli_query($database, $departments_query);
    $department_labels = [];
    $department_scores = [];
    while($row = mysqli_fetch_assoc($departments_result)) {
        $department_labels[] = $row['department_name'];
        $department_scores[] = $row['score'];
    }

    // استرجاع بيانات نتائج التدقيق
    $audit_query = "SELECT * FROM audit_results ORDER BY score DESC";
    $audit_result = mysqli_query($database, $audit_query);
    $audit_labels = [];
    $audit_scores = [];
    $audit_details = [];
    while($row = mysqli_fetch_assoc($audit_result)) {
        $audit_labels[] = $row['category'];
        $audit_scores[] = $row['score'];
        $audit_details[] = $row;
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <title>Compliance & Accreditation</title>
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
        }
    </style>
</head>
<body>
    
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
                            <button class="menu-btn menu-active">
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
            <!-- شريط التنقل -->
            <div class="nav-bar animate-slide-up">
                <div>
                    <p class="header-title">Compliance & Accreditation</p>
                </div>
                <div class="date-container">
                    <i class="far fa-calendar-alt date-icon"></i>
                    <div class="date-text">
                        <p class="date-label">Today's Date</p>
                        <p class="current-date">
                            <?php 
                            date_default_timezone_set('Asia/Kolkata');
                            echo date("Y-m-d");
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- حالة الاعتماد -->
            <div class="table-container animate-slide-up">
                <p class="table-title">
                    <i class="fas fa-clipboard-check"></i>
                    Accreditation Status
                </p>
                <div class="stats-grid">
                    <div class="stat-card hover-scale">
                        <div class="stat-icon" style="color: #28a745;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-value" style="color: #28a745;">Active</div>
                        <div class="stat-label">Current Status</div>
                    </div>
                    
                    <div class="stat-card hover-scale">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="stat-value">2025-12-31</div>
                        <div class="stat-label">Expiry Date</div>
                    </div>
                    
                    <div class="stat-card hover-scale">
                        <div class="stat-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <div class="stat-value">JCI</div>
                        <div class="stat-label">Accreditation Body</div>
                    </div>
                    
                    <div class="stat-card hover-scale">
                        <div class="stat-icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="stat-value">98%</div>
                        <div class="stat-label">Compliance Score</div>
                    </div>
                </div>
            </div>

            <!-- قسم الرسوم البيانية -->
            <div class="charts-container animate-slide-up">
                <div class="chart-row">
                    <div class="chart-card">
                        <p class="chart-title">
                            <i class="fas fa-chart-line"></i>
                            Compliance Trends
                        </p>
                        <canvas id="complianceTrendChart"></canvas>
                    </div>
                    
                    <div class="chart-card">
                        <p class="chart-title">
                            <i class="fas fa-chart-pie"></i>
                            Compliance Distribution
                        </p>
                        <canvas id="complianceDistributionChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-row">
                    <div class="chart-card">
                        <p class="chart-title">
                            <i class="fas fa-chart-bar"></i>
                            Department Compliance
                        </p>
                        <canvas id="departmentComplianceChart"></canvas>
                    </div>
                    
                    <div class="chart-card">
                        <p class="chart-title">
                            <i class="fas fa-chart-area"></i>
                            Audit Results
                        </p>
                        <canvas id="auditResultsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- المهام والنتائج -->
            <div class="dual-container animate-slide-up">
                <div class="dual-column">
                    <div class="table-container">
                        <p class="table-title">
                            <i class="fas fa-tasks"></i>
                            Upcoming Compliance Tasks
                        </p>
                        <p class="table-subtitle">
                            Pending tasks that require your attention to maintain compliance
                        </p>
                        <div class="scroll">
                            <table class="sub-table" border="0">
                                <thead>
                                    <tr>    
                                        <th class="table-headin">Task Name</th>
                                        <th class="table-headin">Due Date</th>
                                        <th class="table-headin">Priority</th>
                                        <th class="table-headin">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Annual Staff Training</td>
                                        <td>2025-06-15</td>
                                        <td><span class="priority-high">High</span></td>
                                        <td><span class="status-pending">Pending</span></td>
                                    </tr>
                                    <tr>
                                        <td>Policy Review - Infection Control</td>
                                        <td>2025-05-30</td>
                                        <td><span class="priority-medium">Medium</span></td>
                                        <td><span class="status-in-progress">In Progress</span></td>
                                    </tr>
                                    <tr>
                                        <td>Equipment Calibration</td>
                                        <td>2025-05-25</td>
                                        <td><span class="priority-high">High</span></td>
                                        <td><span class="status-pending">Pending</span></td>
                                    </tr>
                                    <tr>
                                        <td>Documentation Audit</td>
                                        <td>2025-06-01</td>
                                        <td><span class="priority-medium">Medium</span></td>
                                        <td><span class="status-not-started">Not Started</span></td>
                                    </tr>
                                    <tr>
                                        <td>Emergency Preparedness Drill</td>
                                        <td>2025-06-10</td>
                                        <td><span class="priority-high">High</span></td>
                                        <td><span class="status-scheduled">Scheduled</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <a href="compliance-tasks.php" class="non-style-link">
                            <button class="btn-primary btn" style="width:100%; margin-top:15px;">
                                View All Tasks
                            </button>
                        </a>
                    </div>
                </div>
                
                <div class="dual-column">
                    <div class="table-container">
                        <p class="table-title">
                            <i class="fas fa-search"></i>
                            Recent Audit Findings
                        </p>
                        <p class="table-subtitle">
                            Summary of recent audit observations and corrective actions
                        </p>
                        <div class="scroll">
                            <table class="sub-table" border="0">
                                <thead>
                                    <tr>
                                        <th class="table-headin">Finding</th>
                                        <th class="table-headin">Category</th>
                                        <th class="table-headin">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Incomplete patient consent forms</td>
                                        <td>Documentation</td>
                                        <td><span class="status-corrected">Corrected</span></td>
                                    </tr>
                                    <tr>
                                        <td>Expired medication in storage</td>
                                        <td>Pharmacy</td>
                                        <td><span class="status-in-progress">In Progress</span></td>
                                    </tr>
                                    <tr>
                                        <td>Missing hand hygiene signage</td>
                                        <td>Infection Control</td>
                                        <td><span class="status-pending">Pending</span></td>
                                    </tr>
                                    <tr>
                                        <td>Inadequate fire extinguisher inspection</td>
                                        <td>Safety</td>
                                        <td><span class="status-corrected">Corrected</span></td>
                                    </tr>
                                    <tr>
                                        <td>Outdated policy manual</td>
                                        <td>Administrative</td>
                                        <td><span class="status-in-progress">In Progress</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- قسم تفاصيل نتائج التدقيق -->
            <div class="table-container animate-slide-up">
                <p class="table-title">
                    <i class="fas fa-clipboard-list"></i>
                    Detailed Audit Results
                </p>
                <div class="scroll">
                    <table class="sub-table" border="0">
                        <thead>
                            <tr>
                                <th class="table-headin">Category</th>
                                <th class="table-headin">Score</th>
                                <th class="table-headin">Status</th>
                                <th class="table-headin">Findings</th>
                                <th class="table-headin">Recommendations</th>
                                <th class="table-headin">Last Audit</th>
                                <th class="table-headin">Next Audit</th>
                                <th class="table-headin">Auditor</th>
                                <th class="table-headin">Department</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($audit_details as $audit): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($audit['category']); ?></td>
                                <td>
                                    <div class="score-badge" style="background-color: <?php 
                                        echo $audit['score'] >= 95 ? '#28a745' : 
                                            ($audit['score'] >= 90 ? '#17a2b8' : 
                                            ($audit['score'] >= 85 ? '#ffc107' : '#dc3545')); 
                                    ?>">
                                        <?php echo $audit['score']; ?>%
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($audit['status']); ?>">
                                        <?php echo $audit['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($audit['findings']); ?></td>
                                <td><?php echo htmlspecialchars($audit['recommendations']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($audit['last_audit_date'])); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($audit['next_audit_date'])); ?></td>
                                <td><?php echo htmlspecialchars($audit['auditor_name']); ?></td>
                                <td><?php echo htmlspecialchars($audit['department']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <style>
                .score-badge {
                    display: inline-block;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.25rem;
                    color: white;
                    font-weight: 600;
                    text-align: center;
                    min-width: 60px;
                }

                .status-badge {
                    display: inline-block;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.25rem;
                    color: white;
                    font-weight: 600;
                    text-align: center;
                }

                .status-excellent {
                    background-color: #28a745;
                }

                .status-good {
                    background-color: #17a2b8;
                }

                .status-fair {
                    background-color: #ffc107;
                    color: #000;
                }

                .status-poor {
                    background-color: #dc3545;
                }

                .sub-table td {
                    vertical-align: middle;
                    max-width: 200px;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }

                .sub-table td:hover {
                    white-space: normal;
                    overflow: visible;
                }
            </style>
        </div>
    </div>

    <!-- إضافة مكتبة Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .charts-container {
            padding: 1.5rem;
            margin-top: 2rem;
        }
        
        .chart-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .chart-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid var(--border-color);
            height: 400px;
            display: flex;
            flex-direction: column;
        }
        
        .chart-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .chart-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }
        
        .chart-title i {
            margin-right: 0.75rem;
            font-size: 1.5rem;
        }
        
        .chart-card canvas {
            flex: 1;
            width: 100% !important;
            height: 300px !important;
        }
        
        @media (max-width: 1200px) {
            .chart-row {
                grid-template-columns: 1fr;
            }
            
            .chart-card {
                height: 350px;
            }
        }
        
        @media (max-width: 768px) {
            .charts-container {
                padding: 1rem;
            }
            
            .chart-card {
                padding: 1.5rem;
                height: 300px;
            }
            
            .chart-title {
                font-size: 1.1rem;
                margin-bottom: 1rem;
            }
        }
    </style>

    <script>
   
        const complianceTrendCtx = document.getElementById('complianceTrendChart').getContext('2d');
        new Chart(complianceTrendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($monthly_labels); ?>,
                datasets: [{
                    label: 'Compliance Score',
                    data: <?php echo json_encode($monthly_scores); ?>,
                    borderColor: '#4a6fa5',
                    backgroundColor: 'rgba(74, 111, 165, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3,
                    pointRadius: 5,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Monthly Compliance Trend',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: {
                            top: 10,
                            bottom: 20
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 80,
                        max: 100,
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    }
                }
            }
        });

     
        const complianceDistributionCtx = document.getElementById('complianceDistributionChart').getContext('2d');
        new Chart(complianceDistributionCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($category_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($category_scores); ?>,
                    backgroundColor: [
                        '#4e73df',
                        '#1cc88a',
                        '#36b9cc',
                        '#f6c23e'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: {
                                size: 14,
                                weight: 'bold'
                            },
                            padding: 20
                        }
                    },
                    title: {
                        display: true,
                        text: 'Compliance Distribution by Category',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: {
                            top: 10,
                            bottom: 20
                        }
                    }
                }
            }
        });


        const departmentComplianceCtx = document.getElementById('departmentComplianceChart').getContext('2d');
        new Chart(departmentComplianceCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($department_labels); ?>,
                datasets: [{
                    label: 'Compliance Score',
                    data: <?php echo json_encode($department_scores); ?>,
                    backgroundColor: '#4a6fa5',
                    borderWidth: 2,
                    borderRadius: 5
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
                        display: true,
                        text: 'Department Compliance Scores',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: {
                            top: 10,
                            bottom: 20
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 80,
                        max: 100,
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    }
                }
            }
        });

 
        const auditResultsCtx = document.getElementById('auditResultsChart').getContext('2d');
        new Chart(auditResultsCtx, {
            type: 'bar',
            indexAxis: 'y',
            data: {
                labels: <?php echo json_encode($audit_labels); ?>,
                datasets: [{
                    label: 'Current Score',
                    data: <?php echo json_encode($audit_scores); ?>,
                    backgroundColor: [
                        'rgba(74, 111, 165, 0.8)',
                        'rgba(74, 111, 165, 0.8)',
                        'rgba(74, 111, 165, 0.8)',
                        'rgba(74, 111, 165, 0.8)',
                        'rgba(74, 111, 165, 0.8)',
                        'rgba(74, 111, 165, 0.8)'
                    ],
                    borderColor: '#4a6fa5',
                    borderWidth: 2,
                    borderRadius: 5,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
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
                        display: true,
                        text: 'Audit Results Overview',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: {
                            top: 10,
                            bottom: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Score: ${context.raw}%`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: false,
                        min: 80,
                        max: 100,
                        ticks: {
                            font: {
                                size: 12
                            },
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        grid: {
                            display: true,
                            drawBorder: true,
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    y: {
                        ticks: {
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>