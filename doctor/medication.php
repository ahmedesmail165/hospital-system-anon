<?php
session_start();

// التأكد من تسجيل الدخول وأن المستخدم طبيب
if (isset($_SESSION["user"])) {
    if (($_SESSION["user"]) == "" || $_SESSION['usertype'] != 'd') { // 'd' تعني طبيب
        header("location: ../login.php");
    } else {
        $useremail = $_SESSION["user"];
    }
} else {
    header("location: ../login.php");
}

// الاتصال بقاعدة البيانات
include("../connection.php");

// جلب بيانات الطبيب
$userrow = $database->query("SELECT * FROM doctor WHERE docemail='$useremail'");
$userfetch = $userrow->fetch_assoc();
$doctorid = $userfetch["did"];
$doctorname = $userfetch["dname"];

// جلب قائمة الأدوية المرتبطة بمرضى الطبيب
$medication_sql = "SELECT 
                        m.medid, 
                        p.pname AS 'Patient Name', 
                        m.medname AS 'Medicine Name', 
                        m.dosage, 
                        m.intake_time, 
                        m.start_date, 
                        m.end_date, 
                        m.daily_doses, 
                        m.status, 
                        m.notes 
                    FROM medication m
                    INNER JOIN patient p ON m.pid = p.pid
                    WHERE m.status = 'ongoing'
                    ORDER BY m.start_date DESC";
$medications = $database->query($medication_sql);
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/main.css">
    <title>لوحة التحكم - إدارة الأدوية</title>
    <style>
        .dashboard-table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
        }
        .dashboard-table th, .dashboard-table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .status-ongoing {
            color: green;
            font-weight: bold;
        }
        .status-completed {
            color: gray;
        }
        .status-paused {
            color: orange;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>لوحة التحكم للطبيب</h2>
        <h3>مرحباً، <?php echo $doctorname; ?></h3>
        <h4>قائمة الأدوية</h4>
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>اسم المريض</th>
                    <th>اسم الدواء</th>
                    <th>الجرعة</th>
                    <th>وقت أخذ الدواء</th>
                    <th>تاريخ البداية</th>
                    <th>تاريخ النهاية</th>
                    <th>الحالة</th>
                    <th>الملاحظات</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($medications->num_rows > 0) {
                    while ($row = $medications->fetch_assoc()) {
                        $statusClass = "status-" . $row['status'];
                        echo "<tr>
                                <td>{$row['Patient Name']}</td>
                                <td>{$row['Medicine Name']}</td>
                                <td>{$row['dosage']}</td>
                                <td>{$row['intake_time']}</td>
                                <td>{$row['start_date']}</td>
                                <td>{$row['end_date']}</td>
                                <td class='$statusClass'>{$row['status']}</td>
                                <td>{$row['notes']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>لا توجد أدوية حالياً.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
