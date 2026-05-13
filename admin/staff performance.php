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

    // Create staff_attendance table if it doesn't exist
    $database->query("
        CREATE TABLE IF NOT EXISTS staff_attendance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            docid INT NOT NULL,
            date DATE NOT NULL,
            check_in TIME,
            check_out TIME,
            status ENUM('present', 'absent', 'late', 'leave') NOT NULL,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
    ");

    // Insert sample data if table is empty
    $check_data = $database->query("SELECT COUNT(*) as count FROM staff_attendance")->fetch_assoc();
    if ($check_data['count'] == 0) {
        $today = date('Y-m-d');
        $doctors = $database->query("SELECT docid FROM doctor LIMIT 5");
        
        while ($doctor = $doctors->fetch_assoc()) {
            $status = ['present', 'absent', 'late', 'leave'][rand(0, 3)];
            $check_in = $status == 'present' || $status == 'late' ? date('H:i:s', strtotime('08:00:00 + ' . rand(0, 30) . ' minutes')) : null;
            $check_out = $status == 'present' ? date('H:i:s', strtotime('16:00:00 + ' . rand(-30, 30) . ' minutes')) : null;
            
            $database->query("
                INSERT INTO staff_attendance (docid, date, check_in, check_out, status, notes)
                VALUES (
                    {$doctor['docid']},
                    '$today',
                    " . ($check_in ? "'$check_in'" : "NULL") . ",
                    " . ($check_out ? "'$check_out'" : "NULL") . ",
                    '$status',
                    'Sample attendance record'
                )
            ");
        }
    }

    // Staff Performance Metrics
    $total_staff = $database->query("SELECT COUNT(*) FROM doctor")->fetch_row()[0];
    $active_staff = $database->query("SELECT COUNT(DISTINCT docid) FROM schedule WHERE scheduledate >= CURDATE()")->fetch_row()[0];
    
    // Top Performing Doctors
    $top_doctors = $database->query("
        SELECT d.docname, s.sname, COUNT(a.appoid) as appointment_count, 
               AVG(r.overall_experience) as avg_rating
        FROM doctor d
        LEFT JOIN specialties s ON d.specialties = s.id
        LEFT JOIN schedule sc ON d.docid = sc.docid
        LEFT JOIN appointment a ON sc.scheduleid = a.scheduleid
        LEFT JOIN results r ON a.appoid = r.id
        GROUP BY d.docid
        ORDER BY appointment_count DESC, avg_rating DESC
        LIMIT 5
    ");
    
    // Department Performance
    $department_performance = $database->query("
        SELECT s.sname, COUNT(DISTINCT d.docid) as doctor_count, 
               COUNT(a.appoid) as appointment_count,
               AVG(r.overall_experience) as avg_rating
        FROM specialties s
        LEFT JOIN doctor d ON s.id = d.specialties
        LEFT JOIN schedule sc ON d.docid = sc.docid
        LEFT JOIN appointment a ON sc.scheduleid = a.scheduleid
        LEFT JOIN results r ON a.appoid = r.id
        GROUP BY s.id
        ORDER BY appointment_count DESC
    ");
    
    // Staff Attendance
    $today = date('Y-m-d');
    $staff_attendance = $database->query("
        SELECT 
            d.docid,
            d.docname,
            d.specialties,
            s.sname as specialty_name,
            COUNT(sc.scheduleid) as scheduled_sessions,
            SUM(CASE WHEN a.appoid IS NOT NULL THEN 1 ELSE 0 END) as attended_sessions,
            sa.check_in,
            sa.check_out,
            sa.status,
            sa.notes
        FROM doctor d
        LEFT JOIN specialties s ON d.specialties = s.id
        LEFT JOIN schedule sc ON d.docid = sc.docid AND sc.scheduledate = '$today'
        LEFT JOIN appointment a ON sc.scheduleid = a.scheduleid
        LEFT JOIN staff_attendance sa ON d.docid = sa.docid AND sa.date = '$today'
        GROUP BY d.docid
        ORDER BY d.docname ASC
    ");
    
    // Satisfaction Ratings
    $satisfaction = $database->query("SELECT AVG(overall_experience) FROM results WHERE overall_experience > 0")->fetch_row()[0];
    $satisfaction_rate = $satisfaction ? round($satisfaction/10*100) : 0;

    // Prepare department data for charts
    $dept_labels = [];
    $dept_appointments = [];
    $dept_ratings = [];
    if($department_performance->num_rows > 0) {
        while($dept = $department_performance->fetch_assoc()) {
            $dept_labels[] = $dept['sname'];
            $dept_appointments[] = $dept['appointment_count'];
            $dept_ratings[] = round($dept['avg_rating'], 1);
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <title>Staff Performance Dashboard</title>
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

        /* تحسينات المخططات */
        .chart-and-text {
            display: flex;
            align-items: center;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
            margin: 1rem 0;
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
            padding: 1rem;
        }

        .chart-container canvas {
            width: 100% !important;
            height: 100% !important;
        }

        .text-container {
            flex: 1;
            min-width: 250px;
        }

        .progress-container {
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            margin: 1rem 0;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #10B981, #3B82F6);
            transition: width 0.3s ease;
        }

        .chart-note {
            font-size: 0.9rem;
            color: var(--text-secondary);
            text-align: center;
            margin-top: 0.5rem;
        }

        .dual-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin: 1.5rem 0;
        }

        .dual-column {
            min-width: 0;
        }

        .rate-text {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
            display: block;
            text-align: center;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-present {
            background-color: #10B981;
            color: white;
        }

        .status-absent {
            background-color: #EF4444;
            color: white;
        }

        .status-late {
            background-color: #F59E0B;
            color: white;
        }

        .status-leave {
            background-color: #3B82F6;
            color: white;
        }

        .status-unknown {
            background-color: #94A3B8;
            color: white;
        }

        .progress-container {
            width: 100%;
            height: 6px;
            background-color: #E2E8F0;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 0.25rem;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #10B981, #3B82F6);
            border-radius: 3px;
            transition: width 0.3s ease;
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
                            <button class="menu-btn menu-active">
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
    
    <div class="nav-bar animate-slide-up">
        <div>
            <p class="header-title">Staff Performance Dashboard</p>
        </div>
        <div class="date-container">
            <i class="far fa-calendar-alt date-icon"></i>
            <div class="date-text">
                <p class="date-label">Today's Date</p>
                <p class="current-date">
                    <?php 
                    date_default_timezone_set('Asia/Riyadh');
                    echo date('Y-m-d');
                    ?>
                </p>
            </div>
        </div>
    </div>

   <!-- Key Staff Metrics -->
<div class="stats-grid">
    <div class="stat-card hover-scale">
        <div class="stat-icon">
            <i class="fas fa-user-md"></i>
        </div>
        <div class="stat-value"><?php echo $total_staff ?></div>
        <div class="stat-label">Total Staff</div>
    </div>
    
    <div class="stat-card hover-scale">
        <div class="stat-icon">
            <i class="fas fa-user-clock"></i>
        </div>
        <div class="stat-value"><?php echo $active_staff ?></div>
        <div class="stat-label">Active Today</div>
    </div>
    
    <div class="stat-card hover-scale">
        <div class="stat-icon">
            <i class="fas fa-star"></i>
        </div>
        <div class="stat-value"><?php echo $satisfaction_rate ?>%</div>
        <div class="stat-label">Satisfaction Rate</div>
    </div>
</div>

<!-- Staff Performance Overview -->
<div class="dual-container animate-slide-up">
    <div class="dual-column">
        <div class="table-container">
            <p class="table-title">
                <i class="fas fa-chart-pie"></i>
                Staff Performance Overview
            </p>
            <div style="position: relative; height: 300px; width: 100%; margin-bottom: 1rem;">
                <canvas id="staffPerformanceChart"></canvas>
            </div>
            <div style="margin-top: 1rem; text-align: center;">
                <div style="height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                    <div style="width: <?php echo $satisfaction_rate; ?>%; height: 100%; background: linear-gradient(90deg, #10B981, #3B82F6);"></div>
                </div>
                <p style="margin-top: 0.5rem; color: #64748b;">Overall Satisfaction Rate: <?php echo $satisfaction_rate; ?>%</p>
            </div>
        </div>
    </div>
    
    <div class="dual-column">
        <div class="table-container">
            <p class="table-title">
                <i class="fas fa-chart-bar"></i>
                Department Performance
            </p>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="departmentChart"></canvas>
            </div>
        </div>
    </div>
</div>

    <!-- Top Performing Doctors -->
    <div class="table-container animate-slide-up">
        <p class="table-title">
            <i class="fas fa-award"></i>
            Top Performing Doctors
        </p>
        
        <div class="scroll">
            <table class="sub-table" border="0">
                <thead>
                    <tr>
                        <th class="table-headin">Doctor</th>
                        <th class="table-headin">Specialty</th>
                        <th class="table-headin">Appointments</th>
                        <th class="table-headin">Avg Rating</th>
                        <th class="table-headin">Performance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($top_doctors->num_rows > 0) {
                        while($doctor = $top_doctors->fetch_assoc()) {
                            $rating = $doctor['avg_rating'] ? round($doctor['avg_rating'], 1) : 'N/A';
                            $appointments = $doctor['appointment_count'];
                            
                            // Determine performance level
                            if($rating === 'N/A') {
                                $perf_class = 'performance-average';
                                $perf_text = 'No Data';
                            } elseif($rating >= 8) {
                                $perf_class = 'performance-excellent';
                                $perf_text = 'Excellent';
                            } elseif($rating >= 6) {
                                $perf_class = 'performance-good';
                                $perf_text = 'Good';
                            } elseif($rating >= 4) {
                                $perf_class = 'performance-average';
                                $perf_text = 'Average';
                            } else {
                                $perf_class = 'performance-poor';
                                $perf_text = 'Needs Improvement';
                            }
                            
                            echo "<tr>
                                <td>{$doctor['docname']}</td>
                                <td>{$doctor['sname']}</td>
                                <td>{$appointments}</td>
                                <td>{$rating}</td>
                                <td><span class='performance-badge {$perf_class}'>{$perf_text}</span></td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center;'>No doctor performance data available</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Department Performance and Staff Attendance -->
    <div class="dual-container animate-slide-up">
        <div class="dual-column">
            <div class="table-container">
                <p class="table-title">
                    <i class="fas fa-user-check"></i>
                    Today's Staff Attendance
                </p>
                <div class="scroll">
                    <table class="sub-table" border="0">
                        <thead>
                            <tr>
                                <th class="table-headin">Doctor</th>
                                <th class="table-headin">Specialty</th>
                                <th class="table-headin">Status</th>
                                <th class="table-headin">Check In</th>
                                <th class="table-headin">Check Out</th>
                                <th class="table-headin">Scheduled</th>
                                <th class="table-headin">Attended</th>
                                <th class="table-headin">Attendance Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if($staff_attendance->num_rows > 0) {
                                while($attendance = $staff_attendance->fetch_assoc()) {
                                    $rate = $attendance['scheduled_sessions'] > 0 
                                        ? round(($attendance['attended_sessions']/$attendance['scheduled_sessions'])*100)
                                        : 0;
                                    
                                    // Determine status color
                                    $status_class = '';
                                    switch($attendance['status']) {
                                        case 'present':
                                            $status_class = 'status-present';
                                            break;
                                        case 'absent':
                                            $status_class = 'status-absent';
                                            break;
                                        case 'late':
                                            $status_class = 'status-late';
                                            break;
                                        case 'leave':
                                            $status_class = 'status-leave';
                                            break;
                                        default:
                                            $status_class = 'status-unknown';
                                    }
                                    
                                    echo "<tr>
                                        <td>{$attendance['docname']}</td>
                                        <td>{$attendance['specialty_name']}</td>
                                        <td><span class='status-badge {$status_class}'>{$attendance['status']}</span></td>
                                        <td>{$attendance['check_in']}</td>
                                        <td>{$attendance['check_out']}</td>
                                        <td>{$attendance['scheduled_sessions']}</td>
                                        <td>{$attendance['attended_sessions']}</td>
                                        <td>
                                            <div class='progress-container'>
                                                <div class='progress-bar' style='width: {$rate}%'></div>
                                            </div>
                                            <span class='rate-text'>{$rate}%</span>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8' style='text-align:center;'>No attendance data for today</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// تأكد من تحميل الصفحة بالكامل قبل تنفيذ الكود
window.addEventListener('load', function() {
    console.log('Page loaded, initializing charts...');
    
    // التحقق من وجود مكتبة Chart.js
    if (typeof Chart === 'undefined') {
        console.error('Chart.js library is not loaded!');
        return;
    }
    
    // Staff Performance Chart
    var staffCtx = document.getElementById('staffPerformanceChart');
    console.log('Staff chart context:', staffCtx);
    
    if(staffCtx) {
        try {
            // التحقق من البيانات
            var staffData = [
                <?php 
                $excellent = $database->query("SELECT COUNT(*) FROM results WHERE overall_experience >= 8")->fetch_row()[0];
                $good = $database->query("SELECT COUNT(*) FROM results WHERE overall_experience >= 6 AND overall_experience < 8")->fetch_row()[0];
                $average = $database->query("SELECT COUNT(*) FROM results WHERE overall_experience >= 4 AND overall_experience < 6")->fetch_row()[0];
                $poor = $database->query("SELECT COUNT(*) FROM results WHERE overall_experience < 4")->fetch_row()[0];
                echo "$excellent, $good, $average, $poor";
                ?>
            ];
            console.log('Staff performance data:', staffData);
            
            var staffChart = new Chart(staffCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Excellent', 'Good', 'Average', 'Needs Improvement'],
                    datasets: [{
                        data: staffData,
                        backgroundColor: [
                            '#10B981',  // Success color
                            '#3B82F6',  // Blue
                            '#F59E0B',  // Warning color
                            '#EF4444'   // Danger color
                        ],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                padding: 20,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var label = context.label || '';
                                    var value = context.raw || 0;
                                    var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    var percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '70%',
                    animation: {
                        animateScale: true,
                        animateRotate: true
                    }
                }
            });
            console.log('Staff chart created successfully');
        } catch (error) {
            console.error('Error creating staff chart:', error);
        }
    } else {
        console.error('Staff performance chart canvas not found!');
    }

    // Department Performance Chart
    var deptCtx = document.getElementById('departmentChart');
    console.log('Department chart context:', deptCtx);
    
    if(deptCtx) {
        try {
            // التحقق من البيانات
            var deptLabels = <?php echo json_encode($dept_labels); ?>;
            var deptData = <?php echo json_encode($dept_appointments); ?>;
            var deptRatings = <?php echo json_encode($dept_ratings); ?>;
            console.log('Department data:', {
                labels: deptLabels,
                appointments: deptData,
                ratings: deptRatings
            });
            
            var deptChart = new Chart(deptCtx, {
                type: 'bar',
                data: {
                    labels: deptLabels,
                    datasets: [{
                        label: 'Appointments',
                        data: deptData,
                        backgroundColor: '#3B82F6',
                        borderColor: '#3B82F6',
                        borderWidth: 1,
                        borderRadius: 4,
                        hoverBackgroundColor: '#2563EB'
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
                            callbacks: {
                                label: function(context) {
                                    var label = context.dataset.label || '';
                                    var value = context.raw || 0;
                                    var rating = deptRatings[context.dataIndex];
                                    return [
                                        `${label}: ${value}`,
                                        `Avg Rating: ${rating}`
                                    ];
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false
                            }
                        },
                        x: {
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
            console.log('Department chart created successfully');
        } catch (error) {
            console.error('Error creating department chart:', error);
        }
    } else {
        console.error('Department performance chart canvas not found!');
    }
});
</script>
</body>
</html>
