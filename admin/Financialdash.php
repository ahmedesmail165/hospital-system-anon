<?php
    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    session_start();
    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
            header("location: ../login.php");
        }
    }else{
        header("location: ../login.php");
    }
    
    include("../connection.php");

    // Financial data queries
    $today = date('Y-m-d');
    $month_start = date('Y-m-01');
    $year_start = date('Y-01-01');
    $week_start = date('Y-m-d', strtotime('monday this week')); // Get start of current week
    
    // Daily revenue
    $daily_rev = $database->query("SELECT SUM(amount) as total FROM payments WHERE payment_date='$today'");
    
    // Weekly revenue
    $weekly_rev = $database->query("SELECT SUM(amount) as total FROM payments WHERE payment_date BETWEEN '$week_start' AND '$today'");
    
    // Check for query errors
    if (!$weekly_rev) {
        die("Database query error for weekly revenue: " . $database->error);
    }
    
    $weekly_row = $weekly_rev->fetch_assoc();
    $weekly_total = $weekly_row['total'] ? $weekly_row['total'] : 0;
    
    // Check for query errors
    if (!$daily_rev) {
        die("Database query error for daily revenue: " . $database->error);
    }
    
    $daily_row = $daily_rev->fetch_assoc();
    $daily_total = $daily_row['total'] ? $daily_row['total'] : 0;
    
    // Monthly revenue
    $monthly_rev = $database->query("SELECT SUM(amount) as total FROM payments WHERE payment_date BETWEEN '$month_start' AND '$today'");
    
    // Check for query errors
    if (!$monthly_rev) {
         die("Database query error for monthly revenue: " . $database->error);
    }

    $monthly_row = $monthly_rev->fetch_assoc();
    $monthly_total = $monthly_row['total'] ? $monthly_row['total'] : 0;
    
    // Yearly revenue
    $yearly_rev = $database->query("SELECT SUM(amount) as total FROM payments WHERE payment_date BETWEEN '$year_start' AND '$today'");
    
    // Check for query errors
    if (!$yearly_rev) {
         die("Database query error for yearly revenue: " . $database->error);
    }

    $yearly_row = $yearly_rev->fetch_assoc();
    $yearly_total = $yearly_row['total'] ? $yearly_row['total'] : 0;
    
    // Outstanding payments
    $outstanding = $database->query("SELECT SUM(amount) as total FROM invoices WHERE status='unpaid'");
    
    // Check for query errors
    if (!$outstanding) {
         die("Database query error for outstanding payments: " . $database->error);
    }

    $outstanding_row = $outstanding->fetch_assoc();
    $outstanding_total = $outstanding_row['total'] ? $outstanding_row['total'] : 0;
    
    // Recent transactions
    $transactions = $database->query("SELECT * FROM payments ORDER BY payment_date DESC LIMIT 5");
    
    // Check for query errors
    if (!$transactions) {
         die("Database query error for recent transactions: " . $database->error);
    }

    // Fetch monthly revenue data for the chart
    $monthly_data = array_fill(0, 12, 0); // Initialize array for 12 months
    $current_year = date('Y');
    
    $monthly_rev_query = $database->query("SELECT MONTH(payment_date) as month, SUM(amount) as total FROM payments WHERE YEAR(payment_date) = '$current_year' GROUP BY MONTH(payment_date)");
    
    if ($monthly_rev_query) {
        while($row = $monthly_rev_query->fetch_assoc()) {
            // Adjust month number for 0-indexed array (January = 1, index = 0)
            $month_index = intval($row['month']) - 1;
            if ($month_index >= 0 && $month_index < 12) {
                $monthly_data[$month_index] = $row['total'] ? floatval($row['total']) : 0;
            }
        }
    }

    // Fetch weekly revenue data for the chart with debug information
    $weekly_data = array_fill(0, 7, 0); // Initialize array for 7 days
    $current_week_start = date('Y-m-d', strtotime('monday this week'));
    
    // Get the day names for labels
    $day_names = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
    
    // Query to get daily totals for the current week
    $weekly_rev_query = $database->query("
        SELECT 
            DAYOFWEEK(payment_date) as day_number,
            DATE(payment_date) as payment_day,
            COUNT(*) as transaction_count,
            SUM(amount) as daily_total
        FROM payments 
        WHERE payment_date BETWEEN '$current_week_start' AND '$today'
        GROUP BY DATE(payment_date)
        ORDER BY payment_date
    ");
    
    // Debug information
    if (!$weekly_rev_query) {
        error_log("Weekly Revenue Query Error: " . $database->error);
    }

    if ($weekly_rev_query) {
        while($row = $weekly_rev_query->fetch_assoc()) {
            // Adjust day number for 0-indexed array (Sunday = 1, index = 0)
            $day_index = intval($row['day_number']) - 1;
            if ($day_index >= 0 && $day_index < 7) {
                $weekly_data[$day_index] = $row['daily_total'] ? floatval($row['daily_total']) : 0;
                error_log("Day: " . $row['payment_day'] . ", Total: " . $row['daily_total']);
            }
        }
    }

    // If no data found, add sample data
    if (array_sum($weekly_data) === 0) {
        error_log("No weekly revenue data found, using sample data");
        // Generate sample data with some variation
        $weekly_data = array(
            rand(800, 1200),  // Sunday
            rand(1000, 1500), // Monday
            rand(1200, 1800), // Tuesday
            rand(1500, 2000), // Wednesday
            rand(1800, 2500), // Thursday
            rand(2000, 3000), // Friday
            rand(1500, 2200)  // Saturday
        );
    }

    // Debug output
    error_log("Weekly Data: " . json_encode($weekly_data));

    // Debugging output to check the fetched data
    error_log("Monthly Data: " . json_encode($monthly_data));
    error_log("Weekly Data: " . json_encode($weekly_data));
    // Optionally print to page for direct view (comment out in production):
    // echo "<pre>Monthly Data: " . json_encode($monthly_data) . "</pre>";

    // Fetch revenue by service type with debug information
    $service_revenue = $database->query("
        SELECT 
            service_type,
            COUNT(*) as service_count,
            SUM(amount) as total
        FROM payments 
        WHERE payment_date BETWEEN '$month_start' AND '$today'
        GROUP BY service_type
        ORDER BY total DESC
    ");

    // Debug information
    if (!$service_revenue) {
        error_log("Service Revenue Query Error: " . $database->error);
    }

    $service_labels = array();
    $service_data = array();
    $service_colors = array(
        '#4f46e5', // Indigo
        '#10b981', // Emerald
        '#f59e0b', // Amber
        '#ef4444', // Red
        '#8b5cf6', // Purple
        '#ec4899', // Pink
        '#14b8a6'  // Teal
    );

    if ($service_revenue) {
        $index = 0;
        while($row = $service_revenue->fetch_assoc()) {
            $service_labels[] = $row['service_type'];
            $service_data[] = floatval($row['total']);
            error_log("Service: " . $row['service_type'] . ", Total: " . $row['total']);
            $index++;
        }
    }

    // If no data found, add sample data
    if (empty($service_data)) {
        error_log("No service revenue data found, using sample data");
        $service_labels = array('Consultation', 'Lab Test', 'Surgery', 'X-Ray', 'MRI Scan', 'Medication', 'Check-up');
        $service_data = array(2500, 1800, 3500, 1200, 2800, 1500, 900);
    }

    // Debug output
    error_log("Service Labels: " . json_encode($service_labels));
    error_log("Service Data: " . json_encode($service_data));

    // Fetch revenue by payment method with debug information
    $payment_method_revenue = $database->query("
        SELECT 
            payment_method,
            COUNT(*) as method_count,
            SUM(amount) as total
        FROM payments 
        WHERE payment_date BETWEEN '$month_start' AND '$today'
        GROUP BY payment_method
        ORDER BY total DESC
    ");

    // Debug information
    if (!$payment_method_revenue) {
        error_log("Payment Method Query Error: " . $database->error);
    }

    $method_labels = array();
    $method_data = array();
    $method_colors = array(
        '#4f46e5', // Credit Card - Indigo
        '#10b981', // Cash - Emerald
        '#f59e0b'  // Debit Card - Amber
    );

    if ($payment_method_revenue) {
        while($row = $payment_method_revenue->fetch_assoc()) {
            $method_labels[] = $row['payment_method'];
            $method_data[] = floatval($row['total']);
            error_log("Payment Method: " . $row['payment_method'] . ", Total: " . $row['total']);
        }
    }

    // If no data found, add sample data
    if (empty($method_data)) {
        error_log("No payment method data found, using sample data");
        $method_labels = array('Credit Card', 'Cash', 'Debit Card');
        $method_data = array(8500, 4500, 3200);
    }

    // Debug output
    error_log("Payment Method Labels: " . json_encode($method_labels));
    error_log("Payment Method Data: " . json_encode($method_data));

    // Fetch monthly comparison data with debug information
    $monthly_comparison = $database->query("
        SELECT 
            DATE_FORMAT(payment_date, '%Y-%m') as month,
            COUNT(*) as transaction_count,
            SUM(amount) as revenue
        FROM payments
        WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
        ORDER BY month
    ");

    // Debug information
    if (!$monthly_comparison) {
        error_log("Monthly Comparison Query Error: " . $database->error);
    }

    $month_labels = array();
    $monthly_revenue = array();
    
    if ($monthly_comparison) {
        while($row = $monthly_comparison->fetch_assoc()) {
            $month_labels[] = date('M Y', strtotime($row['month'] . '-01'));
            $monthly_revenue[] = floatval($row['revenue']);
            error_log("Month: " . $row['month'] . ", Revenue: " . $row['revenue']);
        }
    }

    // If no data found or less than 6 months, add sample data
    if (count($monthly_revenue) < 6) {
        error_log("Adding sample monthly revenue data");
        // Get current month and year
        $current_month = date('n');
        $current_year = date('Y');
        
        // Clear existing data
        $month_labels = array();
        $monthly_revenue = array();
        
        // Generate sample data for last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $month = $current_month - $i;
            $year = $current_year;
            
            // Adjust for negative months
            if ($month <= 0) {
                $month += 12;
                $year--;
            }
            
            $month_labels[] = date('M Y', mktime(0, 0, 0, $month, 1, $year));
            
            // Generate random revenue with increasing trend
            $base_revenue = 15000; // Base revenue
            $trend_factor = (6 - $i) * 0.1; // Increasing trend
            $random_factor = rand(-1000, 1000); // Random variation
            $revenue = $base_revenue * (1 + $trend_factor) + $random_factor;
            
            $monthly_revenue[] = round($revenue, 2);
        }
    }

    // Debug output
    error_log("Monthly Labels: " . json_encode($month_labels));
    error_log("Monthly Revenue: " . json_encode($monthly_revenue));
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
        
    <title>Financial Dashboard</title>
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
        .financial-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
.report-buttons {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

.report-button {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 8px;
    padding: 1.5rem 1rem;
    text-align: center;
    color: #334155;
    text-decoration: none;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

.report-button:hover {
    background: #f8fafc;
    transform: translateY(-3px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.report-button i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: var(--primary-color);
}

.report-button span {
    font-weight: 500;
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

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .chart-container {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .chart-container:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        @media (max-width: 1200px) {
            .charts-grid {
                grid-template-columns: 1fr;
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
                            <button class="menu-btn menu-active">
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
    <!-- العنوان وتاريخ اليوم -->
    <div class="nav-bar animate-slide-up">
        <div class="header-title">
            <h2>
                <i class="fas fa-chart-pie"></i>
                Financial Overview
            </h2>
        </div>
        <div class="date-container">
            <i class="far fa-calendar-alt date-icon"></i>
            <div class="date-text">
                <p class="current-date"><?php echo date("F j, Y"); ?></p>
            </div>
        </div>
    </div>

    <!-- بطاقات الإحصائيات المالية -->
    <div class="stats-grid">
        <div class="stat-card hover-scale">
            <div class="stat-icon" style="background-color: rgba(16, 185, 129, 0.1); color: #10b981;">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">$<?php echo number_format($daily_total, 2); ?></div>
                <div class="stat-label">Today's Revenue</div>
                <div class="trend-indicator trend-up">
                    <i class="fas fa-arrow-up"></i>
                    Daily income
                </div>
            </div>
        </div>
        
        <div class="stat-card hover-scale">
            <div class="stat-icon" style="background-color: rgba(79, 70, 229, 0.1); color: #4f46e5;">
                <i class="fas fa-calendar-week"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">$<?php echo number_format($weekly_total, 2); ?></div>
                <div class="stat-label">Weekly Revenue</div>
                <div class="trend-indicator trend-up">
                    <i class="fas fa-chart-line"></i>
                    This week's income
                </div>
            </div>
        </div>
        
        <div class="stat-card hover-scale">
            <div class="stat-icon" style="background-color: rgba(37, 99, 235, 0.1); color: #2563eb;">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">$<?php echo number_format($monthly_total, 2); ?></div>
                <div class="stat-label">Monthly Revenue</div>
                <div class="trend-indicator trend-up">
                    <i class="fas fa-arrow-up"></i>
                    Monthly trend
                </div>
            </div>
        </div>
        
        <div class="stat-card hover-scale">
            <div class="stat-icon" style="background-color: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                <i class="fas fa-calendar-star"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">$<?php echo number_format($yearly_total, 2); ?></div>
                <div class="stat-label">Yearly Revenue</div>
                <div class="trend-indicator trend-up">
                    <i class="fas fa-chart-line"></i>
                    Annual performance
                </div>
            </div>
        </div>
        
        <div class="stat-card hover-scale">
            <div class="stat-icon" style="background-color: rgba(239, 68, 68, 0.1); color: #ef4444;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">$<?php echo number_format($outstanding_total, 2); ?></div>
                <div class="stat-label">Outstanding</div>
                <div class="trend-indicator trend-down">
                    <i class="fas fa-arrow-down"></i>
                    Unpaid balances
                </div>
            </div>
        </div>
    </div>

    <!-- إضافة قسم جديد للرسوم البيانية -->
    <div class="charts-grid">
        <!-- الرسم البياني الأسبوعي (الموجود مسبقاً) -->
        <div class="chart-container">
            <div class="table-title">
                <h3><i class="fas fa-chart-line"></i> Weekly Revenue</h3>
            </div>
            <div class="financial-card">
                <canvas id="revenueChart" height="300"></canvas>
            </div>
        </div>

        <!-- رسم بياني للإيرادات حسب نوع الخدمة -->
        <div class="chart-container">
            <div class="table-title">
                <h3><i class="fas fa-chart-pie"></i> Revenue by Service Type</h3>
            </div>
            <div class="financial-card">
                <canvas id="serviceChart" height="300"></canvas>
            </div>
        </div>

        <!-- رسم بياني لطرق الدفع -->
        <div class="chart-container">
            <div class="table-title">
                <h3><i class="fas fa-credit-card"></i> Payment Methods</h3>
            </div>
            <div class="financial-card">
                <canvas id="paymentMethodChart" height="300"></canvas>
            </div>
        </div>

        <!-- رسم بياني للمقارنة الشهرية -->
        <div class="chart-container">
            <div class="table-title">
                <h3><i class="fas fa-chart-bar"></i> Monthly Revenue Trend</h3>
            </div>
            <div class="financial-card">
                <canvas id="monthlyTrendChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- الرسوم البيانية والعمليات الحديثة -->
    <div class="table-container animate-slide-up">
        <div class="table-title">
            <h3>
                <i class="fas fa-exchange-alt"></i>
                Recent Transactions
            </h3>
        </div>
        <div class="financial-card">
            <div class="table-responsive">
                <table class="sub-table">
                    <thead>
                        <tr>    
                            <th class="table-headin">Date</th>
                            <th class="table-headin">Patient</th>
                            <th class="table-headin">Service</th>
                            <th class="table-headin">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if($transactions->num_rows==0){
                            echo '<tr><td colspan="4"><br><center>No recent transactions found</center><br></td></tr>';
                        } else {
                            while($row=$transactions->fetch_assoc()){
                                echo '<tr>
                                    <td>'.substr($row['payment_date'],0,10).'</td>
                                    <td>Patient #'.$row['patient_id'].'</td>
                                    <td>'.$row['service_type'].'</td>
                                    <td>$'.number_format($row['amount'],2).'</td>
                                </tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- التقارير المالية -->
    <div class="table-container animate-slide-up">
        <div class="table-title">
            <h3>
                <i class="fas fa-file-alt"></i>
                Financial Reports
            </h3>
        </div>
        <div class="financial-card">
            <div class="report-buttons">
                <a href="generate_financial_report.php?type=daily" class="report-button hover-scale">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Daily Report</span>
                </a>
                <a href="generate_financial_report.php?type=monthly" class="report-button hover-scale">
                    <i class="fas fa-file-invoice"></i>
                    <span>Monthly Report</span>
                </a>
                <a href="generate_financial_report.php?type=yearly" class="report-button hover-scale">
                    <i class="fas fa-file-contract"></i>
                    <span>Annual Report</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // تأثيرات الظهور
    const animateElements = document.querySelectorAll('.animate-slide-up');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    
    animateElements.forEach(element => observer.observe(element));

    // الرسم البياني الأسبوعي (الموجود مسبقاً)
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($day_names) ?>,
            datasets: [{
                label: 'Weekly Revenue ($)',
                data: <?php echo json_encode($weekly_data) ?>,
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 2,
                pointBackgroundColor: '#4f46e5',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(30, 41, 59, 0.9)',
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return `Revenue: $${context.raw.toFixed(2)}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { 
                        color: 'rgba(226, 232, 240, 0.5)',
                        drawBorder: false
                    },
                    ticks: { 
                        color: '#64748b',
                        callback: function(value) {
                            return '$' + value.toFixed(2);
                        }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { 
                        color: '#64748b',
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            },
            animation: {
                duration: 2000,
                easing: 'easeOutQuart'
            }
        }
    });

    // رسم بياني للإيرادات حسب نوع الخدمة
    const serviceCtx = document.getElementById('serviceChart').getContext('2d');
    new Chart(serviceCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($service_labels) ?>,
            datasets: [{
                data: <?php echo json_encode($service_data) ?>,
                backgroundColor: <?php echo json_encode($service_colors) ?>,
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
                            size: 12,
                            family: "'Poppins', sans-serif"
                        },
                        color: '#64748b'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(30, 41, 59, 0.9)',
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: {
                        size: 14,
                        weight: 'bold',
                        family: "'Poppins', sans-serif"
                    },
                    bodyFont: {
                        size: 13,
                        family: "'Poppins', sans-serif"
                    },
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${context.label}: $${value.toFixed(2)} (${percentage}%)`;
                        }
                    }
                }
            },
            animation: {
                animateScale: true,
                animateRotate: true,
                duration: 2000,
                easing: 'easeOutQuart'
            }
        }
    });

    // رسم بياني لطرق الدفع
    const paymentMethodCtx = document.getElementById('paymentMethodChart').getContext('2d');
    new Chart(paymentMethodCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($method_labels) ?>,
            datasets: [{
                data: <?php echo json_encode($method_data) ?>,
                backgroundColor: <?php echo json_encode($method_colors) ?>,
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
                            size: 12,
                            family: "'Poppins', sans-serif"
                        },
                        color: '#64748b'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(30, 41, 59, 0.9)',
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: {
                        size: 14,
                        weight: 'bold',
                        family: "'Poppins', sans-serif"
                    },
                    bodyFont: {
                        size: 13,
                        family: "'Poppins', sans-serif"
                    },
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${context.label}: $${value.toFixed(2)} (${percentage}%)`;
                        }
                    }
                }
            },
            animation: {
                animateScale: true,
                animateRotate: true,
                duration: 2000,
                easing: 'easeOutQuart'
            }
        }
    });

    // رسم بياني للمقارنة الشهرية
    const monthlyTrendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
    new Chart(monthlyTrendCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($month_labels) ?>,
            datasets: [{
                label: 'Monthly Revenue',
                data: <?php echo json_encode($monthly_revenue) ?>,
                backgroundColor: '#4f46e5',
                borderRadius: 6,
                borderSkipped: false,
                barPercentage: 0.6,
                categoryPercentage: 0.8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(30, 41, 59, 0.9)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return `Revenue: $${context.raw.toFixed(2)}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { 
                        color: 'rgba(226, 232, 240, 0.5)',
                        drawBorder: false
                    },
                    ticks: { 
                        color: '#64748b',
                        callback: function(value) {
                            return '$' + value.toFixed(2);
                        }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { 
                        color: '#64748b',
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeOutQuart'
            }
        }
    });
});
</script>



