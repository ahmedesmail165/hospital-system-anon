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

    // Performance metrics queries - إصلاح الاستعلامات مع معالجة الأخطاء
    try {
        $result = $database->query("SELECT COUNT(*) as count FROM patient");
        if ($result) {
            $row = $result->fetch_assoc();
            $total_patients = $row['count'];
        } else {
            $total_patients = 0;
            echo '<div style="color:red">'.$database->error.'</div>';
        }
    } catch (Exception $e) {
        $total_patients = 0;
    }
    
    try {
        $result = $database->query("SELECT COUNT(*) as count FROM doctor");
        if ($result) {
            $row = $result->fetch_assoc();
            $total_doctors = $row['count'];
        } else {
            $total_doctors = 0;
            echo '<div style="color:red">'.$database->error.'</div>';
        }
    } catch (Exception $e) {
        $total_doctors = 0;
    }
    
    $today = date('Y-m-d');
    
    try {
        $result = $database->query("SELECT COUNT(*) as count FROM appointment WHERE appodate='$today'");
        if ($result) {
            $row = $result->fetch_assoc();
            $today_appointments = $row['count'];
        } else {
            $today_appointments = 0;
            echo '<div style="color:red">'.$database->error.'</div>';
        }
    } catch (Exception $e) {
        $today_appointments = 0;
    }
    
    try {
        $result = $database->query("SELECT COUNT(*) as count FROM appointment WHERE appodate BETWEEN DATE_SUB('$today', INTERVAL 7 DAY) AND '$today'");
        if ($result) {
            $row = $result->fetch_assoc();
            $weekly_appointments = $row['count'];
        } else {
            $weekly_appointments = 0;
            echo '<div style="color:red">'.$database->error.'</div>';
        }
    } catch (Exception $e) {
        $weekly_appointments = 0;
    }
    
    try {
        $result = $database->query("SELECT COUNT(*) as count FROM appointment WHERE appodate BETWEEN DATE_SUB('$today', INTERVAL 30 DAY) AND '$today'");
        if ($result) {
            $row = $result->fetch_assoc();
            $monthly_appointments = $row['count'];
        } else {
            $monthly_appointments = 0;
            echo '<div style="color:red">'.$database->error.'</div>';
        }
    } catch (Exception $e) {
        $monthly_appointments = 0;
    }
    
    try {
        $result = $database->query("SELECT COUNT(*) as count FROM ambulance_requests");
        if ($result) {
            $row = $result->fetch_assoc();
            $ambulance_requests = $row['count'];
        } else {
            $ambulance_requests = 0;
            echo '<div style="color:red">'.$database->error.'</div>';
        }
    } catch (Exception $e) {
        $ambulance_requests = 0;
    }
    
    // إصلاح استعلام المرضى الجدد - استخدام تاريخ التسجيل بدلاً من تاريخ الميلاد
    try {
        // Temporary fix: Count total patients since created_at column doesn't exist
        // TODO: Add created_at column to patient table for proper tracking
        $result = $database->query("SELECT COUNT(*) as count FROM patient");
        if ($result) {
            $row = $result->fetch_assoc();
            $new_patients_week = $row['count']; // Show total patients for now
        } else {
            $new_patients_week = 0;
            echo '<div style="color:red">'.$database->error.'</div>';
        }
    } catch (Exception $e) {
        $new_patients_week = 0;
    }
    
    try {
        // Temporary fix: Count total patients since created_at column doesn't exist
        // TODO: Add created_at column to patient table for proper tracking
        $result = $database->query("SELECT COUNT(*) as count FROM patient");
        if ($result) {
            $row = $result->fetch_assoc();
            $new_patients_month = $row['count']; // Show total patients for now
        } else {
            $new_patients_month = 0;
            echo '<div style="color:red">'.$database->error.'</div>';
        }
    } catch (Exception $e) {
        $new_patients_month = 0;
    }
    
    // إصلاح استعلام الأقسام
    try {
        $top_departments = $database->query("
            SELECT s.sname, COUNT(d.docid) as doctors_count
            FROM specialties s
            LEFT JOIN doctor d ON s.id = d.specialties
            GROUP BY s.sname, s.id
            ORDER BY doctors_count DESC
            LIMIT 5
        ");
    } catch (Exception $e) {
        $top_departments = false;
    }
    
    // حساب الإيرادات والمصروفات
    $revenue = $monthly_appointments * 200;
    $expenses = $total_doctors * 5000;
    $profit = $revenue - $expenses;
    
    // إصلاح استعلام رضا المرضى
    try {
        $satisfaction_result = $database->query("SELECT AVG(overall_experience) as avg_satisfaction FROM results WHERE overall_experience > 0");
        $satisfaction = $satisfaction_result ? $satisfaction_result->fetch_assoc()['avg_satisfaction'] : 0;
        $satisfaction_rate = $satisfaction ? round($satisfaction/10*100) : 0;
    } catch (Exception $e) {
        $satisfaction_rate = 85; // قيمة افتراضية
    }
    
    // Data for charts
    $appointment_data = [
        'Today' => $today_appointments,
        'This Week' => $weekly_appointments,
        'This Month' => $monthly_appointments
    ];
    
    $department_data = [];
    $department_labels = [];
    if($top_departments) {
        while($dept = $top_departments->fetch_assoc()) {
            $department_labels[] = $dept['sname'];
            $department_data[] = $dept['doctors_count'];
        }
    }
    
    // إذا لم تكن هناك بيانات للأقسام، استخدم بيانات افتراضية
    if(empty($department_data)) {
        $department_labels = ['Cardiology', 'Neurology', 'Orthopedics', 'Pediatrics', 'General'];
        $department_data = [3, 2, 2, 1, 1];
    }
    
    // Data for new charts
    $satisfaction_data_for_chart = [
        'Satisfied' => $satisfaction_rate,
        'Others' => 100 - $satisfaction_rate
    ];
    
    // Representing profit and expenses as parts of revenue for the pie chart
    $financial_data_for_chart = [
        'Profit' => max(0, $profit),
        'Expenses' => $expenses
    ];
    
    // إضافة بيانات إضافية للتحقق من الأخطاء
    $debug_info = [
        'total_patients' => $total_patients,
        'total_doctors' => $total_doctors,
        'today_appointments' => $today_appointments,
        'monthly_appointments' => $monthly_appointments,
        'ambulance_requests' => $ambulance_requests,
        'satisfaction_rate' => $satisfaction_rate
    ];
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

    <title>Dashboard</title>
    <style>
        /* تحسينات المخططات الدائرية */
.pie-chart-container {
    position: relative;
    width: 100%;
    height: 100%;
    min-height: 250px;
}

.pie-chart-legend {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    margin-top: 1rem;
    gap: 0.5rem;
}

.legend-item {
    display: flex;
    align-items: center;
    margin-right: 1rem;
    font-size: 0.875rem;
}

.legend-color {
    width: 16px;
    height: 16px;
    border-radius: 3px;
    margin-right: 0.5rem;
    display: inline-block;
}

/* ألوان مخصصة للمخططات */
.chart-success {
    background-color: var(--success-color);
}

.chart-danger {
    background-color: var(--danger-color);
}

.chart-warning {
    background-color: var(--warning-color);
}

.chart-info {
    background-color: var(--info-color);
}

.chart-primary {
    background-color: var(--primary-color);
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
        
        .stat-card.mini-card {
            padding: 1.25rem;
            text-align: center;
        }
        
        .stat-card.mini-card .stat-value {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-card.mini-card .stat-label {
            font-size: 0.875rem;
            margin-bottom: 0;
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

    /* تحسين تنسيق أقسام المخططات والنصوص */
    .chart-and-text {
        display: flex;
        align-items: center;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .chart-container {
        flex: 1;
        min-width: 250px;
        max-width: 350px; /* Limit chart size */
        height: 250px; /* Fixed height for consistency */
    }

    .text-container {
        flex: 1;
        min-width: 250px;
    }

     /* تحسينات الجدول المالي */
    .financial-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1rem;
    }

    .financial-table td {
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-color);
    }

    .financial-table tr:last-child td {
        border-bottom: none;
    }

    .financial-table td:first-child {
        font-weight: 500;
        color: var(--text-secondary);
    }

     .highlight-row td {
        font-weight: 700;
        color: var(--text-primary);
    }

    /* تحسينات شريط التقدم */
     .progress-container {
        width: 100%;
        background-color: var(--border-color);
        border-radius: 0.5rem;
        overflow: hidden;
        margin-bottom: 1rem;
    }
    
    .progress-bar {
        height: 25px;
        line-height: 25px;
        color: white;
        text-align: center;
        background-color: var(--success-color);
        border-radius: 0.5rem;
        transition: width 0.5s ease-in-out;
    }

    /* تخطيط الأعمدة */
    .dual-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    .dual-column {
        min-width: 0;
    }
    
    .stat-note {
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin-top: 1rem;
        font-style: italic;
    }

    .performance-metrics {
        display: flex;
        flex-direction: column;
        gap: 2rem;
        padding: 1rem;
    }

    .metric-card {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: var(--shadow);
        transition: var(--transition);
        border: 1px solid var(--border-color);
    }

    .metric-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .metric-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .metric-title {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .metric-title i {
        font-size: 1.5rem;
        color: var(--primary-color);
        background: var(--primary-light);
        padding: 0.75rem;
        border-radius: 0.75rem;
    }

    .metric-title h3 {
        margin: 0;
        font-size: 1.25rem;
        color: var(--text-primary);
    }

    .metric-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-color);
        background: var(--primary-light);
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
    }

    .chart-wrapper {
        height: 300px;
        margin: 1rem 0;
        position: relative;
    }

    .metric-footer {
        margin-top: 1rem;
    }

    .progress-container {
        background: var(--border-color);
        border-radius: 0.5rem;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }

    .progress-bar {
        height: 25px;
        line-height: 25px;
        color: white;
        text-align: center;
        background: var(--primary-color);
        border-radius: 0.5rem;
        transition: width 0.5s ease-in-out;
    }

    .stat-note {
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin: 0.5rem 0 0 0;
        font-style: italic;
    }

    @media (max-width: 768px) {
        .metric-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .metric-value {
            align-self: flex-start;
        }

        .chart-wrapper {
            height: 250px;
        }
    }
</style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                            <button class="menu-btn menu-active">
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
    <!-- شريط التنقل -->
    <div class="nav-bar animate-slide-up">
        <div>
            <p class="header-title">Hospital Performance Dashboard</p>
        </div>
        <div class="date-container">
            <i class="far fa-calendar-alt date-icon"></i>
            <div class="date-text">
                <p class="date-label">Today's Date</p>
                <p class="current-date">
                    <?php 
                    date_default_timezone_set('Asia/Kolkata');
                    echo date('Y-m-d');
                    ?>
                </p>
            </div>
        </div>
    </div>

    <!-- المقاييس الرئيسية -->
    <div class="stats-grid">
        <div class="stat-card hover-scale">
            <div class="stat-icon">
                <i class="fas fa-procedures"></i>
            </div>
            <div class="stat-value"><?php echo $total_patients ?></div>
            <div class="stat-label">Total Patients</div>
        </div>
        
        <div class="stat-card hover-scale">
            <div class="stat-icon">
                <i class="fas fa-user-md"></i>
            </div>
            <div class="stat-value"><?php echo $total_doctors ?></div>
            <div class="stat-label">Doctors</div>
        </div>
        
        <div class="stat-card hover-scale">
            <div class="stat-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-value"><?php echo $monthly_appointments ?></div>
            <div class="stat-label">Monthly Appointments</div>
        </div>
        
        <div class="stat-card hover-scale">
            <div class="stat-icon">
                <i class="fas fa-ambulance"></i>
            </div>
            <div class="stat-value"><?php echo $ambulance_requests ?></div>
            <div class="stat-label">Emergency Calls</div>
        </div>
    </div>

    <!-- قسم المخططات -->
    <div class="dual-container animate-slide-up">
        <div class="dual-column">
            <div class="table-container">
                <p class="table-title">
                    <i class="fas fa-chart-bar"></i>
                    Appointments Overview
                </p>
                <div class="chart-container">
                    <canvas id="appointmentsChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="dual-column">
            <div class="table-container">
                <p class="table-title">
                    <i class="fas fa-pie-chart"></i>
                    Top Departments
                </p>
                <div class="chart-container">
                    <canvas id="departmentsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- مقاييس إضافية -->
    <div class="dual-container animate-slide-up">
        <div class="dual-column">
            <div class="table-container">
                <p class="table-title">
                    <i class="fas fa-smile"></i>
                    Patient Satisfaction
                </p>
                <div class="chart-and-text">
                    <div class="chart-container">
                        <canvas id="satisfactionChart"></canvas>
                    </div>
                    <div class="text-container">
                        <div class="progress-container">
                            <div class="progress-bar" style="width: <?php echo $satisfaction_rate ?>%">
                                <?php echo $satisfaction_rate ?>%
                            </div>
                        </div>
                        <p class="stat-note">
                            Based on <?php 
                            try {
                                $reviews_count = $database->query("SELECT COUNT(*) as count FROM results WHERE overall_experience > 0")->fetch_assoc()['count'];
                                echo $reviews_count ? $reviews_count : 0;
                            } catch (Exception $e) {
                                echo 0;
                            }
                            ?> patient reviews
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="dual-column">
            <div class="table-container">
                <p class="table-title">
                    <i class="fas fa-money-bill-wave"></i>
                    Financial Summary
                </p>
                <div class="chart-and-text">
                     <div class="chart-container">
                         <canvas id="financialChart"></canvas>
                     </div>
                    <div class="text-container">
                        <table class="financial-table">
                            <tr>
                                <td>Revenue:</td>
                                <td>$<?php echo number_format($revenue) ?></td>
                            </tr>
                            <tr>
                                <td>Expenses:</td>
                                <td>$<?php echo number_format($expenses) ?></td>
                            </tr>
                            <tr class="highlight-row">
                                <td><strong>Net Profit:</strong></td>
                                <td><strong>$<?php echo number_format($profit) ?></strong></td>
                            </tr>
                        </table>
                        <div class="progress-container">
                            <div class="progress-bar" style="width: <?php echo $revenue > 0 ? round($profit/$revenue*100) : 0 ?>%">
                                <?php echo $revenue > 0 ? round($profit/$revenue*100) : 0 ?>% Profit Margin
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- قسم المرضى الجدد -->
    <div class="table-container animate-slide-up">
        <p class="table-title">
            <i class="fas fa-user-plus"></i>
            New Patient Registrations
        </p>
        <div class="stats-grid">
            <div class="stat-card mini-card">
                <div class="stat-value"><?php echo $new_patients_week ?></div>
                <div class="stat-label">New patients this week</div>
            </div>
            <div class="stat-card mini-card">
                <div class="stat-value"><?php echo $new_patients_month ?></div>
                <div class="stat-label">New patients this month</div>
            </div>
        </div>
    </div>

    <!-- قسم التحقق من الأخطاء (يمكن إزالته لاحقاً) -->
    <?php if(isset($_GET['debug']) && $_GET['debug'] == '1'): ?>
    <div class="table-container animate-slide-up">
        <p class="table-title">
            <i class="fas fa-bug"></i>
            Debug Information
        </p>
        <table class="sub-table">
            <tr>
                <th class="table-headin">Metric</th>
                <th class="table-headin">Value</th>
            </tr>
            <?php foreach($debug_info as $key => $value): ?>
            <tr>
                <td><?php echo ucfirst(str_replace('_', ' ', $key)) ?></td>
                <td><?php echo $value ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- قسم مقاييس الأداء العامة -->
    <div class="performance-metrics">
        <!-- Bed Occupancy Rate -->
        <div class="metric-card animate-slide-up">
            <div class="metric-header">
                <div class="metric-title">
                    <i class="fas fa-bed"></i>
                    <h3>Bed Occupancy Rate</h3>
                </div>
                <div class="metric-value">85%</div>
            </div>
            <div class="chart-wrapper">
                <canvas id="bedOccupancyChart"></canvas>
            </div>
            <div class="metric-footer">
                <div class="progress-container">
                    <div class="progress-bar" style="width: 85%">
                        85% Occupancy
                    </div>
                </div>
                <p class="stat-note">Current bed occupancy rate compared to capacity</p>
            </div>
        </div>

        <!-- Average Length of Stay -->
        <div class="metric-card animate-slide-up">
            <div class="metric-header">
                <div class="metric-title">
                    <i class="fas fa-clock"></i>
                    <h3>Average Length of Stay</h3>
                </div>
                <div class="metric-value">4.2 Days</div>
            </div>
            <div class="chart-wrapper">
                <canvas id="lengthOfStayChart"></canvas>
            </div>
            <div class="metric-footer">
                <div class="progress-container">
                    <div class="progress-bar" style="width: 70%">
                        4.2 Days
                    </div>
                </div>
                <p class="stat-note">Average patient stay duration</p>
            </div>
        </div>

        <!-- Readmission Rate -->
        <div class="metric-card animate-slide-up">
            <div class="metric-header">
                <div class="metric-title">
                    <i class="fas fa-undo"></i>
                    <h3>Readmission Rate</h3>
                </div>
                <div class="metric-value">12%</div>
            </div>
            <div class="chart-wrapper">
                <canvas id="readmissionChart"></canvas>
            </div>
            <div class="metric-footer">
                <div class="progress-container">
                    <div class="progress-bar" style="width: 12%">
                        12% Rate
                    </div>
                </div>
                <p class="stat-note">30-day readmission rate</p>
            </div>
        </div>

        <!-- Surgery Success Rate -->
        <div class="metric-card animate-slide-up">
            <div class="metric-header">
                <div class="metric-title">
                    <i class="fas fa-procedures"></i>
                    <h3>Surgery Success Rate</h3>
                </div>
                <div class="metric-value">95%</div>
            </div>
            <div class="chart-wrapper">
                <canvas id="surgerySuccessChart"></canvas>
            </div>
            <div class="metric-footer">
                <div class="progress-container">
                    <div class="progress-bar" style="width: 95%">
                        95% Success
                    </div>
                </div>
                <p class="stat-note">Successful surgery outcomes</p>
            </div>
        </div>

        <!-- Patient Satisfaction Trend -->
        <div class="metric-card animate-slide-up">
            <div class="metric-header">
                <div class="metric-title">
                    <i class="fas fa-chart-line"></i>
                    <h3>Patient Satisfaction Trend</h3>
                </div>
                <div class="metric-value">92%</div>
            </div>
            <div class="chart-wrapper">
                <canvas id="satisfactionTrendChart"></canvas>
            </div>
            <div class="metric-footer">
                <div class="progress-container">
                    <div class="progress-bar" style="width: 92%">
                        92% Satisfaction
                    </div>
                </div>
                <p class="stat-note">Current month patient satisfaction rate</p>
            </div>
        </div>
    </div>
</div>

<script>
    // Appointments Chart
    const appointmentsCtx = document.getElementById('appointmentsChart').getContext('2d');
    const appointmentsChart = new Chart(appointmentsCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_keys($appointment_data)) ?>,
            datasets: [{
                label: 'Appointments',
                data: <?php echo json_encode(array_values($appointment_data)) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Departments Chart
    const departmentsCtx = document.getElementById('departmentsChart').getContext('2d');
    const departmentsChart = new Chart(departmentsCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($department_labels) ?>,
            datasets: [{
                data: <?php echo json_encode($department_data) ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(153, 102, 255, 0.6)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                }
            }
        }
    });

    // Patient Satisfaction Pie Chart
    const satisfactionCtx = document.getElementById('satisfactionChart').getContext('2d');
    const satisfactionChart = new Chart(satisfactionCtx, {
        type: 'pie',
        data: {
            labels: ['Satisfied', 'Others'],
            datasets: [{
                data: <?php echo json_encode(array_values($satisfaction_data_for_chart)) ?>,
                backgroundColor: [
                    'rgba(16, 185, 129, 0.6)', // Success color
                    'rgba(239, 68, 68, 0.6)' // Danger color or similar
                ],
                borderColor: [
                    'rgba(16, 185, 129, 1)',
                    'rgba(239, 68, 68, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                }
            }
        }
    });

    // Financial Summary Pie Chart
    const financialCtx = document.getElementById('financialChart').getContext('2d');
    const financialChart = new Chart(financialCtx, {
        type: 'pie',
        data: {
            labels: ['Profit', 'Expenses'],
            datasets: [{
                data: <?php echo json_encode(array_values($financial_data_for_chart)) ?>,
                backgroundColor: [
                    'rgba(16, 185, 129, 0.6)', // Profit (Success color)
                    'rgba(239, 68, 68, 0.6)'  // Expenses (Danger color)
                ],
                 borderColor: [
                    'rgba(16, 185, 129, 1)',
                    'rgba(239, 68, 68, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
             plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': $';
                            }
                            if (context.parsed !== null) {
                                label += context.parsed.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Bed Occupancy Chart
    const bedOccupancyCtx = document.getElementById('bedOccupancyChart').getContext('2d');
    const bedOccupancyChart = new Chart(bedOccupancyCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Occupancy Rate',
                data: [75, 82, 78, 85, 88, 85],
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Percentage'
                    }
                }
            }
        }
    });

    // Length of Stay Chart
    const lengthOfStayCtx = document.getElementById('lengthOfStayChart').getContext('2d');
    const lengthOfStayChart = new Chart(lengthOfStayCtx, {
        type: 'bar',
        data: {
            labels: ['Cardiology', 'Orthopedics', 'Neurology', 'General', 'Pediatrics'],
            datasets: [{
                label: 'Average Days',
                data: [4.5, 5.2, 3.8, 3.5, 2.8],
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Days'
                    }
                }
            }
        }
    });

    // Readmission Rate Chart
    const readmissionCtx = document.getElementById('readmissionChart').getContext('2d');
    const readmissionChart = new Chart(readmissionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Readmitted', 'Not Readmitted'],
            datasets: [{
                data: [12, 88],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(75, 192, 192, 0.6)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });

    // Surgery Success Chart
    const surgerySuccessCtx = document.getElementById('surgerySuccessChart').getContext('2d');
    const surgerySuccessChart = new Chart(surgerySuccessCtx, {
        type: 'bar',
        data: {
            labels: ['Cardiac', 'Orthopedic', 'Neurological', 'General', 'Pediatric'],
            datasets: [{
                label: 'Success Rate',
                data: [96, 94, 95, 93, 97],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 99, 132, 0.8)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 1,
                borderRadius: 5
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Success Rate: ${context.raw}%`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Success Rate (%)',
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    },
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                y: {
                    grid: {
                        display: false
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
            }
        }
    });

    // Patient Satisfaction Trend Chart
    const satisfactionTrendCtx = document.getElementById('satisfactionTrendChart').getContext('2d');
    const satisfactionTrendChart = new Chart(satisfactionTrendCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Current Month',
                data: [85, 87, 86, 88, 90, 92],
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Previous Month',
                data: [82, 84, 83, 85, 87, 89],
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Satisfaction Rate'
                    }
                }
            }
        }
    });

    // Update chart options for better vertical layout
    const commonChartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                align: 'center',
                labels: {
                    boxWidth: 12,
                    padding: 15
                }
            }
        }
    };

    // Update each chart with common options
    bedOccupancyChart.options = {
        ...bedOccupancyChart.options,
        ...commonChartOptions
    };

    lengthOfStayChart.options = {
        ...lengthOfStayChart.options,
        ...commonChartOptions
    };

    readmissionChart.options = {
        ...readmissionChart.options,
        ...commonChartOptions
    };

    satisfactionTrendChart.options = {
        ...satisfactionTrendChart.options,
        ...commonChartOptions
    };
</script>
</body>
</html>