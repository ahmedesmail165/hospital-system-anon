<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Results</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .table-container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 20px;
        }
        .table thead th {
            background-color: #007bff;
            color: #fff;
        }
        .table tbody tr:hover {
            background-color: #f1f1f1;
        }
        .table tbody tr td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Results Data</h1>
        <div class="table-container">
            <?php
            // بيانات الاتصال بقاعدة البيانات
            $host = "127.0.0.1";
            $username = "root";
            $password = "";
            $database = "sql_database_edoc";

            // إنشاء الاتصال
            $conn = new mysqli($host, $username, $password, $database);

            // التحقق من الاتصال
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // استعلام SQL لاسترداد البيانات
            $sql = "SELECT * FROM results";
            $result = $conn->query($sql);

            // التحقق من وجود بيانات
            if ($result->num_rows > 0) {
                // عرض البيانات في جدول
                echo '<table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Patient ID</th>
                                <th>Service Rating</th>
                                <th>Need Support</th>
                                <th>Overall Experience</th>
                                <th>Wait Time</th>
                                <th>Staff Courtesy</th>
                                <th>Facilities Rating</th>
                                <th>Would Recommend</th>
                                <th>Feedback</th>
                                <th>Prediction</th>
                                <th>Confidence</th>
                                <th>Doctor</th>
                            </tr>
                        </thead>
                        <tbody>';

                // عرض كل صف من البيانات
                while($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . $row["id"] . "</td>
                            <td>" . $row["patient_id"] . "</td>
                            <td>" . $row["service_rating"] . "</td>
                            <td>" . $row["need_support"] . "</td>
                            <td>" . $row["overall_experience"] . "</td>
                            <td>" . $row["wait_time"] . "</td>
                            <td>" . $row["staff_courtesy"] . "</td>
                            <td>" . $row["facilities_rating"] . "</td>
                            <td>" . $row["would_recommend"] . "</td>
                            <td>" . $row["feedback"] . "</td>
                            <td>" . $row["prediction"] . "</td>
                            <td>" . $row["confidence"] . "</td>
                            <td>" . $row["doctor"] . "</td>
                          </tr>";
                }
                echo '</tbody></table>';
            } else {
                echo '<div class="alert alert-warning" role="alert">No results found.</div>';
            }

            // إغلاق الاتصال
            $conn->close();
            ?>
        </div>
    </div>

    <!-- Bootstrap JS (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>