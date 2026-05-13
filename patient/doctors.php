<?php

    //learn from w3schools.com

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
    

    //import database
    include("../connection.php");
    $userrow = $database->query("select * from patient where pemail='$useremail'");
    $userfetch=$userrow->fetch_assoc();
    $userid= $userfetch["pid"];
    $username=$userfetch["pname"];

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
        
    <title>Doctors</title>
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
            --info-color:  #4fc3f7;
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
        
        .menu-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(8px);
        }
        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        .animate-slide-up {
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
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
        
        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.9);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.3);
        }
        
        .logout-btn i {
            margin-right: 0.75rem;
            font-size: 1.1rem;
            transition: var(--transition);
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
        
        .date-container {
            display: flex;
            align-items: center;
            background: var(--primary-light);
            padding: 0.75rem 1.25rem;
            border-radius: 0.75rem;
            color: var(--primary-color);
            transition: var(--transition);
        }
        
        .date-icon {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }
        
        .current-date {
            font-weight: 600;
            font-size: 0.9375rem;
        }
        
        /* جدول الأطباء */
        .doctors-table-container {
            background: white;
            border-radius: 1rem;
            padding: 1.75rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            transition: var(--transition);
            border: 1px solid var(--border-color);
        }
        
        .doctors-table-title {
            margin-top: 0;
            margin-bottom: 1.25rem;
            color: var(--primary-color);
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .doctors-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 1rem;
            background: white;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
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

        
        .doctors-table th {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem 1.25rem;
            text-align: left;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.8125rem;
            letter-spacing: 0.5px;
        }
        
        .doctors-table td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.9375rem;
            transition: var(--transition);
        }
        
        .doctors-table tr:last-child td {
            border-bottom: none;
        }
        
        .doctors-table tr:hover td {
            background-color: var(--primary-light);
        }
        
        /* أزرار الإجراءات */
        .action-btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
        }
          .btn-icon-back {
            background: var(--primary-light);
            color: var(--primary-color);
        }
        
        .btn-icon-back:hover {
            background: var(--primary-color);
            color: white;
        }
        .btn-edit {
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary-color);
        }
        
        .btn-edit:hover {
            background: rgba(59, 130, 246, 0.2);
        }
        
        .btn-view {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }
        
        .btn-view:hover {
            background: rgba(16, 185, 129, 0.2);
        }
        
        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }
        
        .btn-delete:hover {
            background: rgba(239, 68, 68, 0.2);
        }
        
        /* زر إضافة جديد */
        .add-new-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.875rem 1.75rem;
            border-radius: 0.75rem;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2);
        }
        
        .add-new-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
        }
        
        .add-new-btn i {
            margin-right: 0.5rem;
        }
        
        /* نافذة البوب أب */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .popup {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            animation: fadeIn 0.3s ease-out;
        }
        
        .popup-title {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .popup-content {
            margin-bottom: 2rem;
        }
        
        .popup-actions {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .input-text {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 0.9375rem;
            transition: var(--transition);
            margin-bottom: 1rem;
        }
        
        .input-text:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
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
            .nav-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .header-search {
                width: 100%;
                max-width: 100%;
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
            
            .profile-img {
                width: 50px;
                height: 50px;
            }
            
            .dash-body {
                margin-left: 80px;
                width: calc(100% - 80px);
                padding: 1.5rem;
            }
            
            .doctors-table td, .doctors-table th {
                padding: 0.75rem;
            }
        }
        
        @media (max-width: 576px) {
            .menu {
                transform: translateX(-100%);
                position: fixed;
                width: 280px;
                z-index: 1000;
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
            }
            
            .popup {
                padding: 1.5rem;
            }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
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
                            <i class="fas fa-home menu-icon"></i>
                            <p class="menu-text">Home</p>
                        </button>
                    </a>
                </td>
            </tr>
            <tr class="menu-row">
                <td colspan="2">
                    <a href="doctors.php" class="non-style-link-menu">
                        <button class="menu-btn menu-active">
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
                        <button class="menu-btn">
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
    <!-- شريط البحث وتاريخ اليوم -->
    <div class="nav-bar animate-slide-up">
        <div>
                    <a href="schedule.php" class="non-style-link">
                        <button onclick="window.history.back();" class="btn-primary btn-icon-back" style="padding: 8px 15px;">
    <i class="fas fa-arrow-left"></i> Back
</button>
                    </a>
                </div>
        
        <form action="" method="post" class="header-search">
            <input type="search" name="search" class=" header-searchbar" placeholder="Search Doctor name or Email" list="doctors">
            <?php
                echo '<datalist id="doctors">';
                $list11 = $database->query("select docname,docemail from doctor;");
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
                <i class="fas fa-search"></i> Search
            </button>
        </form>
        
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

    <!-- عنوان الصفحة -->
    <div style="margin-bottom: 1.5rem;">
        <h1 style="color: var(--primary-color); font-size: 1.75rem;">
            <i class="fas fa-user-md"></i> All Doctors (<?php echo $list11->num_rows; ?>)
        </h1>
    </div>

    <!-- جدول الأطباء -->
    <div class="doctors-table-container">
        <div style="overflow-x: auto;">
            <table class="doctors-table">
                <thead>
                    <tr>
                        <th>Doctor Name</th>
                        <th>Email</th>
                        <th>Specialties</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($_POST){
                        $keyword=$_POST["search"];
                        $sqlmain= "select * from doctor where docemail='$keyword' or docname='$keyword' or docname like '$keyword%' or docname like '%$keyword' or docname like '%$keyword%'";
                    }else{
                        $sqlmain= "select * from doctor order by docid desc";
                    }

                    $result= $database->query($sqlmain);

                    if($result->num_rows==0){
                        echo '<tr>
                            <td colspan="4" style="text-align: center; padding: 2rem;">
                                <img src="../img/notfound.svg" alt="No doctors found" style="width: 30%; max-width: 200px; opacity: 0.7;">
                                <p style="margin-top: 1rem; color: var(--text-secondary);">We couldn\'t find anything related to your keywords!</p>
                                <a href="doctors.php" class="non-style-link">
                                    <button class="search-btn" style="margin-top: 1rem;">
                                        Show All Doctors
                                    </button>
                                </a>
                            </td>
                        </tr>';
                    } else {
                        for ($x=0; $x<$result->num_rows;$x++){
                            $row=$result->fetch_assoc();
                            $docid=$row["docid"];
                            $name=$row["docname"];
                            $email=$row["docemail"];
                            $spe=$row["specialties"];
                            $spcil_res= $database->query("select sname from specialties where id='$spe'");
                            $spcil_array= $spcil_res->fetch_assoc();
                            $spcil_name=$spcil_array["sname"];
                            
                            echo '<tr>
                                <td>'.$name.'</td>
                                <td>'.$email.'</td>
                                <td>'.$spcil_name.'</td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <a href="?action=view&id='.$docid.'" class="non-style-link">
                                            <button class="action-btn btn-view">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        </a>
                                        <a href="?action=session&id='.$docid.'&name='.$name.'" class="non-style-link">
                                            <button class="action-btn btn-edit">
                                                <i class="fas fa-calendar-check"></i> Sessions
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
    </div>
</div>

<?php 
if($_GET){
    $id=$_GET["id"];
    $action=$_GET["action"];
    if($action=='drop'){
        $nameget=$_GET["name"];
        echo '
        <div id="popup1" class="overlay">
                <div class="popup">
                <center>
                    <h2 class="popup-title">Are you sure?</h2>
                    <a class="close" href="doctors.php">&times;</a>
                    <div class="popup-content">
                        You want to delete this record<br>('.substr($nameget,0,40).').
                    </div>
                    <div class="popup-actions">
                        <a href="delete-doctor.php?id='.$id.'" class="non-style-link">
                            <button class="search-btn" style="background: var(--success-color);">
                                <i class="fas fa-check"></i> Yes
                            </button>
                        </a>
                        <a href="doctors.php" class="non-style-link">
                            <button class="search-btn" style="background: var(--danger-color);">
                                <i class="fas fa-times"></i> No
                            </button>
                        </a>
                    </div>
                </center>
                </div>
        </div>
        ';
    }elseif($action=='view'){
        $sqlmain= "select * from doctor where docid='$id'";
        $result= $database->query($sqlmain);
        $row=$result->fetch_assoc();
        $name=$row["docname"];
        $email=$row["docemail"];
        $spe=$row["specialties"];
        
        $spcil_res= $database->query("select sname from specialties where id='$spe'");
        $spcil_array= $spcil_res->fetch_assoc();
        $spcil_name=$spcil_array["sname"];
        $nic=$row['docnic'];
        $tele=$row['doctel'];
        echo '
        <div id="popup1" class="overlay">
                <div class="popup">
                <center>
                    <h2 class="popup-title">Doctor Details</h2>
                    <a class="close" href="doctors.php">&times;</a>
                    <div class="popup-content">
                        <table width="100%" class="sub-table">
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="name" class="form-label">Name: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    '.$name.'<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Email" class="form-label">Email: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                '.$email.'<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="nic" class="form-label">NIC: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                '.$nic.'<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Tele" class="form-label">Telephone: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                '.$tele.'<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="spec" class="form-label">Specialties: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                '.$spcil_name.'<br><br>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="popup-actions">
                        <a href="doctors.php" class="non-style-link">
                            <button class="search-btn">
                                <i class="fas fa-check"></i> OK
                            </button>
                        </a>
                    </div>
                </center>
                </div>
        </div>
        ';
    }elseif($action=='session'){
        $name=$_GET["name"];
        echo '
        <div id="popup1" class="overlay">
                <div class="popup">
                <center>
                    <h2 class="popup-title">Redirect to Doctors sessions?</h2>
                    <a class="close" href="doctors.php">&times;</a>
                    <div class="popup-content">
                        You want to view All sessions by <br>('.substr($name,0,40).').
                    </div>
                    <form action="schedule.php" method="post">
                        <input type="hidden" name="search" value="'.$name.'">
                        <div class="popup-actions">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-check"></i> Yes
                            </button>
                        </div>
                    </form>
                </center>
                </div>
        </div>
        ';
    }
}
?>

</body>
</html>