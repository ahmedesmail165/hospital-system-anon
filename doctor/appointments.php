<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Appointments</title>
    <style>
        .dashbord-tables,.doctor-heade{
            animation: transitionIn-Y-over 0.5s;
        }
        .filter-container{
            animation: transitionIn-Y-bottom  0.5s;
        }
        .sub-table,#anim{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .doctor-heade{
            animation: transitionIn-Y-over 0.5s;
        }
        .menu-btn {
            padding: 10px 20px;
            transition: all 0.3s;
            border-radius: 5px;
            margin: 5px;
        }
        .menu-btn:hover {
            background-color: #f0f0f0;
        }
        .menu-icon-dashbord {
            background-image: url('../img/icons/dashboard.svg');
            background-repeat: no-repeat;
            background-position: 20px center;
            background-size: 20px;
        }
        .menu-icon-appoinment {
            background-image: url('../img/icons/appointment.svg');
            background-repeat: no-repeat;
            background-position: 20px center;
            background-size: 20px;
        }
        .menu-icon-session {
            background-image: url('../img/icons/schedule.svg');
            background-repeat: no-repeat;
            background-position: 20px center;
            background-size: 20px;
        }
        .menu-icon-patient {
            background-image: url('../img/icons/patient.svg');
            background-repeat: no-repeat;
            background-position: 20px center;
            background-size: 20px;
        }
        .menu-icon-settings {
            background-image: url('../img/icons/settings.svg');
            background-repeat: no-repeat;
            background-position: 20px center;
            background-size: 20px;
        }
        .menu-active {
            background-color: #e3f2fd;
        }
        .menu-icon-dashbord-active {
            background-image: url('../img/icons/dashboard-hover.svg');
        }
        .menu-icon-appoinment-active {
            background-image: url('../img/icons/appointment-hover.svg');
        }
        .menu-icon-session-active {
            background-image: url('../img/icons/schedule-hover.svg');
        }
        .menu-icon-patient-active {
            background-image: url('../img/icons/patient-hover.svg');
        }
        .menu-icon-settings-active {
            background-image: url('../img/icons/settings-hover.svg');
        }
        .menu-text {
            padding-left: 50px;
            font-size: 14px;
            color: #2c3e50;
        }
        .non-style-link-menu {
            text-decoration: none;
            color: inherit;
        }
        .non-style-link-menu-active {
            color: #3498db;
        }
        .profile-container {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 10px;
        }
        .profile-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }
        .profile-subtitle {
            font-size: 12px;
            color: #7f8c8d;
            margin: 0;
        }
        .logout-btn {
            width: 100%;
            padding: 8px;
            margin-top: 10px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            color: #2c3e50;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .logout-btn:hover {
            background-color: #e9ecef;
        }
        .menu-row {
            transition: all 0.3s;
        }
        .menu-row:hover {
            background-color: #f8f9fa;
        }
        .menu-container {
            width: 100%;
            padding: 10px 0;
        }
        .menu {
            width: 250px;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
        }
        .container {
            display: flex;
        }
        .dash-body {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }
    </style>
</head>
<body>
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
    
    //import database
    include("../connection.php");
    $userrow = $database->query("select * from doctor where docemail='$useremail'");
    $userfetch=$userrow->fetch_assoc();
    $userid= $userfetch["docid"];
    $username=$userfetch["docname"];
    ?>
    <div class="container">
        <div class="menu">
            <table class="menu-container" border="0">
                <tr>
                    <td style="padding:10px" colspan="2">
                        <table border="0" class="profile-container">
                            <tr>
                                <td width="30%" style="padding-left:20px" >
                                    <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title"><?php echo substr($username,0,13)  ?>..</p>
                                    <p class="profile-subtitle"><?php echo substr($useremail,0,22)  ?></p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-dashbord">
                        <a href="index.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">Dashboard</p>
                            </div>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row menu-active">
                    <td class="menu-btn menu-icon-appoinment menu-active">
                        <a href="appointments.php" class="non-style-link-menu non-style-link-menu-active">
                            <div>
                                <p class="menu-text">Appointments</p>
                            </div>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-session">
                        <a href="schedule.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">My Schedule</p>
                            </div>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-patient">
                        <a href="patient.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">My Patients</p>
                            </div>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-settings">
                        <a href="settings.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">Settings</p>
                            </div>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-settings">
                        <a href="../logout.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">Logout</p>
                            </div>
                        </a>
                    </td>
                </tr>
            </table>
        </div>
        <div class="dash-body">
            <!-- Rest of the file remains unchanged -->
        </div>
    </div>
</body>
</html> 