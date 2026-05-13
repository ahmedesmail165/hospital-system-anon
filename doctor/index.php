 <?php

    //learn from w3schools.com

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
    

    //import database
    include("../connection.php");
    $userrow = $database->query("select * from doctor where docemail='$useremail'");
    $userfetch=$userrow->fetch_assoc();
    $userid= $userfetch["docid"];
    $username=$userfetch["docname"];


    //echo $userid;
    //echo $username;
    
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
        .welcome-section {
    background-color: white;
    border-radius: 10px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.welcome-section h3 {
    font-size: 1.25rem;
    color: #64748b;
    margin: 0 0 0.5rem 0;
}

.welcome-section h1 {
    font-size: 2rem;
    color: #1e293b;
    margin: 0 0 1rem 0;
}

.welcome-section p {
    font-size: 1rem;
    color: #64748b;
    margin: 0 0 1.5rem 0;
    line-height: 1.5;
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
        .header-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
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
            padding: 1.75rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            position: relative;
            height: 400px;
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
                        <button class="menu-btn menu-active">
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
                        <button class="menu-btn">
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
                    <a href="http://127.0.0.1:1010/" class="non-style-link-menu">
                        <button class="menu-btn">
                            <i class="fas fa-heartbeat menu-icon"></i>
                            <p class="menu-text">Heart diseases</p>
                        </button>
                    </a>
                </td>
            </tr>
            <tr class="menu-row">
                <td colspan="2">
                    <a href="http://127.0.0.1:1060/" class="non-style-link-menu">
                        <button class="menu-btn">
                            <i class="fas fa-briefcase-medical menu-icon"></i>
                            <p class="menu-text">Breast cancer</p>
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
    <!-- شريط العنوان وتاريخ اليوم -->
    <div class="nav-bar animate-slide-up">
        <div class="header-title">
            <p class="header-title">Dashboard</p>
        </div>
        
        <div class="date-container">
            <i class="far fa-calendar-alt date-icon"></i>
            <div class="date-text">
                <p class="date-label">Today's Date</p>
                <p class="current-date">
                    <?php 
                    date_default_timezone_set('Asia/Kolkata');
                    $today = date('Y-m-d');
                    echo $today;

                    $patientrow = $database->query("select * from patient;");
                    $doctorrow = $database->query("select * from doctor;");
                    $appointmentrow = $database->query("select * from appointment where appodate>='$today';");
                    $schedulerow = $database->query("select * from schedule where scheduledate='$today';");
                    ?>
                </p>
            </div>
        </div>
    </div>

    <!-- رسالة الترحيب -->
    <div class="welcome-section animate-slide-up animate-delay-1">
        <h3>Welcome!</h3>
        <h1><?php echo $username ?>.</h1>
        <p>Thanks for joining with us. We are always trying to get you a complete service.<br>
        You can view your daily schedule, Reach Patients Appointment at home!</p>
        <a href="appointment.php" class="non-style-link">
            <button class="btn-primary btn">View My Appointments</button>
        </a>
    </div>

    <!-- بطاقات الإحصائيات -->
    <div class="stats-grid">
        <div class="stat-card hover-scale">
            <div class="stat-icon">
                <i class="fas fa-user-md"></i>
            </div>
            <div class="stat-value"><?php echo $doctorrow->num_rows ?></div>
            <div class="stat-label">All Doctors</div>
        </div>
        
        <div class="stat-card hover-scale">
            <div class="stat-icon">
                <i class="fas fa-user-injured"></i>
            </div>
            <div class="stat-value"><?php echo $patientrow->num_rows ?></div>
            <div class="stat-label">All Patients</div>
        </div>
        
        <div class="stat-card hover-scale">
            <div class="stat-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-value"><?php echo $appointmentrow->num_rows ?></div>
            <div class="stat-label">New Booking</div>
        </div>
        
        <div class="stat-card hover-scale">
            <div class="stat-icon">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="stat-value"><?php echo $schedulerow->num_rows ?></div>
            <div class="stat-label">Today Sessions</div>
        </div>
    </div>

    <!-- الجلسات القادمة -->
    <div class="table-container hover-scale animate-slide-up animate-delay-2">
        <h3 class="table-title">
            <i class="fas fa-calendar-day"></i>
            Your Up Coming Sessions until Next week
        </h3>
        <div class="scroll-container">
            <table class="sub-table">
                <thead>
                    <tr>
                        <th class="table-headin">Session Title</th>
                        <th class="table-headin">Scheduled Date</th>
                        <th class="table-headin">Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $nextweek=date("Y-m-d",strtotime("+1 week"));
                    $sqlmain= "select schedule.scheduleid,schedule.title,doctor.docname,schedule.scheduledate,schedule.scheduletime,schedule.nop from schedule inner join doctor on schedule.docid=doctor.docid where schedule.scheduledate>='$today' and schedule.scheduledate<='$nextweek' order by schedule.scheduledate desc"; 
                    $result= $database->query($sqlmain);

                    if($result->num_rows==0){
                        echo '<tr>
                        <td colspan="3">
                        <br><br><br><br>
                        <center>
                        <img src="../img/notfound.svg" width="25%">
                        
                        <br>
                        <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">We couldn\'t find anything related to your keywords!</p>
                        <a class="non-style-link" href="schedule.php"><button class="login-btn btn-primary-soft btn" style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Show all Sessions &nbsp;</button>
                        </a>
                        </center>
                        <br><br><br><br>
                        </td>
                        </tr>';
                    } else {
                        for ($x=0; $x<$result->num_rows;$x++){
                            $row=$result->fetch_assoc();
                            $scheduleid=$row["scheduleid"];
                            $title=$row["title"];
                            $docname=$row["docname"];
                            $scheduledate=$row["scheduledate"];
                            $scheduletime=$row["scheduletime"];
                            $nop=$row["nop"];
                            echo '<tr>
                                <td>'.substr($title,0,30).'</td>
                                <td>'.substr($scheduledate,0,10).'</td>
                                <td>'.substr($scheduletime,0,5).'</td>
                            </tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- إضافة Font Awesome للرموز -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- إضافة Animate.css للحركات -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add animation class to elements when they come into view
    const animateElements = document.querySelectorAll('.animate-slide-up');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });
    
    animateElements.forEach(element => {
        observer.observe(element);
    });
});
</script>