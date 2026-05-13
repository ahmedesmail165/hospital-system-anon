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


    <title>Settings</title>
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

        .settings-container {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.settings-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.3s ease;
}

.settings-card:hover {
    transform: translateY(-5px);
}

.card-icon {
    padding: 25px;
    text-align: center;
    font-size: 2.5rem;
    color: white;
}

.bg-blue {
    background:  #4a6fa5;
}

.bg-green {
    background: #166088;
}

.bg-red {
    background: #EA4335;
}

.card-content {
    padding: 20px;
}

.card-content h3 {
    margin-top: 0;
    color: #333;
}

.card-content p {
    color: #666;
    margin-bottom: 20px;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    text-align: center;
}

.btn-blue {
    background:  #4a6fa5;
    color: white;
}

.btn-green {
    background: #166088;
    color: white;
}

.btn-red {
    background: #EA4335;
    color: white;
}

.btn-gray {
    background: #f1f1f1;
    color: #333;
}

.btn:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

/* Popup styles */
.overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.popup {
    background: white;
    border-radius: 10px;
    padding: 30px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    position: relative;
}

.popup h2 {
    margin-top: 0;
    color: #333;
}

.close {
    position: absolute;
    top: 15px;
    right: 15px;
    font-size: 1.5rem;
    color: #666;
    text-decoration: none;
}

.popup-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 20px;
}

/* Profile popup specific styles */
.profile-popup {
    max-width: 600px;
}

.profile-view {
    text-align: center;
}

.profile-img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 15px;
}

.profile-details {
    text-align: left;
    margin-top: 20px;
}

.detail-item {
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.detail-label {
    font-weight: bold;
    color: #555;
    display: block;
    margin-bottom: 5px;
}

.detail-label i {
    margin-right: 8px;
    width: 20px;
    text-align: center;
}

.detail-value {
    color: #333;
}

/* Success popup */
.success-popup {
    text-align: center;
}

.success-icon {
    font-size: 4rem;
    color: #34A853;
    margin-bottom: 20px;
}

/* Edit form styles */
.edit-form .form-group {
    margin-bottom: 15px;
}

.edit-form label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #555;
}

.edit-form .form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 20px;
}

.text-danger {
    color: #EA4335;
}

@media (max-width: 768px) {
    .settings-grid {
        grid-template-columns: 1fr;
    }
    
    .popup {
        width: 95%;
        padding: 20px;
    }
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
                        <button class="menu-btn menu-active">
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
            <a href="javascript:history.back()" class="non-style-link">
                <button class="btn-primary btn-icon-back">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
            </a>
        </div>
        <div>
            <p class="header-title">Account Settings</p>
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

    <div class="settings-container">
        <div class="settings-grid">
            <!-- Edit Profile Card -->
            <div class="settings-card">
                <div class="card-icon bg-blue">
                    <i class="fas fa-user-edit"></i>
                </div>
                <div class="card-content">
                    <h3>Edit Profile</h3>
                    <p>Update your personal information and account details</p>
                    <a href="?action=edit&id=<?php echo $userid ?>&error=0" class="btn btn-blue">
                        <i class="fas fa-pencil-alt"></i> Edit Profile
                    </a>
                </div>
            </div>

            <!-- View Profile Card -->
            <div class="settings-card">
                <div class="card-icon bg-green">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="card-content">
                    <h3>View Profile</h3>
                    <p>Review your current account information</p>
                    <a href="?action=view&id=<?php echo $userid ?>" class="btn btn-green">
                        <i class="fas fa-user"></i> View Profile
                    </a>
                </div>
            </div>

            <!-- Delete Account Card -->
            <div class="settings-card">
                <div class="card-icon bg-red">
                    <i class="fas fa-user-times"></i>
                </div>
                <div class="card-content">
                    <h3>Delete Account</h3>
                    <p>Permanently remove your account from the system</p>
                    <a href="?action=drop&id=<?php echo $userid.'&name='.$username ?>" class="btn btn-red">
                        <i class="fas fa-trash-alt"></i> Delete Account
                    </a>
                </div>
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
                        <h2>Confirm Account Deletion</h2>
                        <a class="close" href="settings.php">&times;</a>
                        <div class="content">
                            Are you sure you want to permanently delete your account?<br><br>
                            Account: <b>'.htmlspecialchars(substr($nameget,0,40)).'</b><br><br>
                            <span class="text-danger"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone!</span>
                        </div>
                        <div class="popup-actions">
                            <a href="delete-account.php?id='.$id.'" class="non-style-link">
                                <button class="btn btn-red">Confirm Delete</button>
                            </a>
                            <a href="settings.php" class="non-style-link">
                                <button class="btn btn-blue">Cancel</button>
                            </a>
                        </div>
                    </center>
                </div>
            </div>
            ';
        }elseif($action=='view'){
            $sqlmain= "select * from patient where pid='$id'";
            $result= $database->query($sqlmain);
            $row=$result->fetch_assoc();
            $name=$row["pname"];
            $email=$row["pemail"];
            $address=$row["paddress"];
            $dob=$row["pdob"];
            $nic=$row['pnic'];
            $tele=$row['ptel'];
            
            echo '
            <div id="popup1" class="overlay">
                <div class="popup profile-popup">
                    <center>
                        <h2>Account Details</h2>
                        <a class="close" href="settings.php">&times;</a>
                        <div class="content">
                            <div class="profile-view">
                                <div class="profile-header">
                                    <img src="../img/user.png" alt="Profile" class="profile-img">
                                    <h3>'.htmlspecialchars($name).'</h3>
                                </div>
                                
                                <div class="profile-details">
                                    <div class="detail-item">
                                        <span class="detail-label"><i class="fas fa-envelope"></i> Email:</span>
                                        <span class="detail-value">'.htmlspecialchars($email).'</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label"><i class="fas fa-id-card"></i> NIC:</span>
                                        <span class="detail-value">'.htmlspecialchars($nic).'</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label"><i class="fas fa-phone"></i> Phone:</span>
                                        <span class="detail-value">'.htmlspecialchars($tele).'</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label"><i class="fas fa-map-marker-alt"></i> Address:</span>
                                        <span class="detail-value">'.htmlspecialchars($address).'</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label"><i class="fas fa-birthday-cake"></i> Date of Birth:</span>
                                        <span class="detail-value">'.htmlspecialchars($dob).'</span>
                                    </div>
                                </div>
                                
                                <div class="profile-footer">
                                    <a href="settings.php" class="btn btn-blue">
                                        Close
                                    </a>
                                </div>
                            </div>
                        </div>
                    </center>
                </div>
            </div>
            ';
        }elseif($action=='edit'){
            $sqlmain= "select * from patient where pid='$id'";
            $result= $database->query($sqlmain);
            $row=$result->fetch_assoc();
            $name=$row["pname"];
            $email=$row["pemail"];
            $address=$row["paddress"];
            $nic=$row['pnic'];
            $tele=$row['ptel'];

            $error_1=$_GET["error"];
            $errorlist= array(
                '1'=>'<div class="alert alert-danger">Already have an account for this Email address.</div>',
                '2'=>'<div class="alert alert-danger">Password Conformation Error! Reconform Password</div>',
                '3'=>'<div class="alert alert-danger"></div>',
                '4'=>"",
                '0'=>'',
            );

            if($error_1!='4'){
                echo '
                <div id="popup1" class="overlay">
                    <div class="popup edit-popup">
                        <center>
                            <h2>Edit Account Details</h2>
                            <a class="close" href="settings.php">&times;</a>
                            <div class="content">
                                '.$errorlist[$error_1].'
                                <form action="edit-user.php" method="POST" class="edit-form">
                                    <input type="hidden" value="'.$id.'" name="id00">
                                    <input type="hidden" name="oldemail" value="'.$email.'">
                                    
                                    <div class="form-group">
                                        <label for="email">Email Address</label>
                                        <input type="email" name="email" class="form-control" value="'.htmlspecialchars($email).'" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="name">Full Name</label>
                                        <input type="text" name="name" class="form-control" value="'.htmlspecialchars($name).'" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="nic">NIC Number</label>
                                        <input type="text" name="nic" class="form-control" value="'.htmlspecialchars($nic).'" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="Tele">Phone Number</label>
                                        <input type="tel" name="Tele" class="form-control" value="'.htmlspecialchars($tele).'" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="address">Address</label>
                                        <input type="text" name="address" class="form-control" value="'.htmlspecialchars($address).'" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="password">New Password</label>
                                        <input type="password" name="password" class="form-control" placeholder="Enter new password">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="cpassword">Confirm Password</label>
                                        <input type="password" name="cpassword" class="form-control" placeholder="Confirm new password">
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="reset" class="btn btn-gray">Reset</button>
                                        <button type="submit" class="btn btn-blue">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </center>
                    </div>
                </div>
                ';
            }else{
                echo '
                <div id="popup1" class="overlay">
                    <div class="popup success-popup">
                        <center>
                            <div class="success-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h2>Update Successful!</h2>
                            <a class="close" href="settings.php">&times;</a>
                            <div class="content">
                                <p>Your account details have been updated successfully.</p>
                                <p>If you changed your email, please logout and login again with your new email.</p>
                            </div>
                            <div class="popup-actions">
                                <a href="settings.php" class="btn btn-blue">
                                    Continue
                                </a>
                                <a href="../logout.php" class="btn btn-gray">
                                    Logout Now
                                </a>
                            </div>
                        </center>
                    </div>
                </div>
                ';
            }; 
        }
    }
    ?>
</div>