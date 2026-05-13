<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
    <title>Manage Medications</title>
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
        .med-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
            font-weight: 500;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        .med-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .med-table th, .med-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .med-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            font-size: 14px;
        }
        .edit-btn {
            background-color: #3498db;
            color: white;
        }
        .delete-btn {
            background-color: #e74c3c;
            color: white;
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
    
    include("../connection.php");
    $userrow = $database->query("select * from doctor where docemail='$useremail'");
    $userfetch=$userrow->fetch_assoc();
    $userid= $userfetch["docid"];
    $username=$userfetch["docname"];

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if(isset($_POST['add_med'])) {
            $med_name = $_POST['med_name'];
            $active_ingredient = $_POST['active_ingredient'];
            $dosage_form = $_POST['dosage_form'];
            $strength = $_POST['strength'];
            $med_qty = $_POST['med_qty'];
            $category = $_POST['category'];
            $med_price = $_POST['med_price'];
            $location_rack = $_POST['location_rack'];
            $description = $_POST['description'];
            $mfg_company = $_POST['mfg_company'];
            $requires_prescription = isset($_POST['requires_prescription']) ? 1 : 0;
            $side_effects = $_POST['side_effects'];
            $storage_conditions = $_POST['storage_conditions'];
            $expiry_date = $_POST['expiry_date'];

            $sql = "INSERT INTO meds (med_name, active_ingredient, dosage_form, strength, med_qty, category, 
                    med_price, location_rack, description, mfg_company, requires_prescription, 
                    side_effects, storage_conditions, expiry_date, added_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $database->prepare($sql);
            $stmt->bind_param("ssssisssssisssi", $med_name, $active_ingredient, $dosage_form, $strength, 
                            $med_qty, $category, $med_price, $location_rack, $description, $mfg_company, 
                            $requires_prescription, $side_effects, $storage_conditions, $expiry_date, $userid);
            
            if($stmt->execute()) {
                echo "<script>alert('Medication added successfully!');</script>";
            } else {
                echo "<script>alert('Error adding medication!');</script>";
            }
        }
    }
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
                            <tr>
                                <td colspan="2">
                                    <a href="../logout.php" ><input type="button" value="Log out" class="logout-btn btn-primary-soft btn"></a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-dashbord">
                        <a href="index.php" class="non-style-link-menu"><div><p class="menu-text">Dashboard</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">My Appointments</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-session">
                        <a href="schedule.php" class="non-style-link-menu"><div><p class="menu-text">My Sessions</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-patient">
                        <a href="patient.php" class="non-style-link-menu"><div><p class="menu-text">My Patients</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-settings">
                        <a href="settings.php" class="non-style-link-menu"><div><p class="menu-text">Settings</p></a></div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;" >
                <tr >
                    <td colspan="1" class="nav-bar" >
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;margin-left:20px;">Medications</p>
                        <div style="margin-left: 20px;">
                            <a href="prescribe_medication.php" class="btn-primary-soft btn">Prescribe Medication</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <div style="padding: 20px;">
                            <!-- Add Medication Form -->
                            <div class="med-form">
                                <h3>Add New Medication</h3>
                                <form method="POST" action="">
                                    <div class="form-group">
                                        <label>Medication Name</label>
                                        <input type="text" name="med_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Active Ingredient</label>
                                        <input type="text" name="active_ingredient" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Dosage Form</label>
                                        <select name="dosage_form" required>
                                            <option value="tablet">Tablet</option>
                                            <option value="capsule">Capsule</option>
                                            <option value="syrup">Syrup</option>
                                            <option value="injection">Injection</option>
                                            <option value="cream">Cream</option>
                                            <option value="ointment">Ointment</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Strength</label>
                                        <input type="text" name="strength" placeholder="e.g., 500mg" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="number" name="med_qty" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Category</label>
                                        <select name="category" required>
                                            <option value="antibiotic">Antibiotic</option>
                                            <option value="analgesic">Analgesic</option>
                                            <option value="antiviral">Antiviral</option>
                                            <option value="antifungal">Antifungal</option>
                                            <option value="antihistamine">Antihistamine</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Price</label>
                                        <input type="number" step="0.01" name="med_price" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Location/Rack</label>
                                        <input type="text" name="location_rack">
                                    </div>
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="description"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Manufacturing Company</label>
                                        <input type="text" name="mfg_company">
                                    </div>
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="requires_prescription">
                                            Requires Prescription
                                        </label>
                                    </div>
                                    <div class="form-group">
                                        <label>Side Effects</label>
                                        <textarea name="side_effects"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Storage Conditions</label>
                                        <input type="text" name="storage_conditions" placeholder="e.g., Store in a cool, dry place">
                                    </div>
                                    <div class="form-group">
                                        <label>Expiry Date</label>
                                        <input type="date" name="expiry_date" required>
                                    </div>
                                    <button type="submit" name="add_med" class="btn-primary btn">Add Medication</button>
                                </form>
                            </div>

                            <!-- Medications List -->
                            <h3>Current Medications</h3>
                            <table class="med-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Active Ingredient</th>
                                        <th>Strength</th>
                                        <th>Quantity</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Expiry Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT * FROM meds ORDER BY med_name ASC";
                                    $result = $database->query($sql);
                                    
                                    if($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>".$row['med_name']."</td>";
                                            echo "<td>".$row['active_ingredient']."</td>";
                                            echo "<td>".$row['strength']."</td>";
                                            echo "<td>".$row['med_qty']."</td>";
                                            echo "<td>".$row['category']."</td>";
                                            echo "<td>".$row['med_price']."</td>";
                                            echo "<td>".$row['expiry_date']."</td>";
                                            echo "<td>
                                                    <button class='action-btn edit-btn' onclick='editMed(".$row['med_id'].")'>Edit</button>
                                                    <button class='action-btn delete-btn' onclick='deleteMed(".$row['med_id'].")'>Delete</button>
                                                  </td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='8' style='text-align: center;'>No medications found</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <script>
        function editMed(id) {
            // Add edit functionality
            window.location.href = 'edit_medication.php?id=' + id;
        }

        function deleteMed(id) {
            if(confirm('Are you sure you want to delete this medication?')) {
                // Add delete functionality
                window.location.href = 'delete_medication.php?id=' + id;
            }
        }
    </script>
</body>
</html> 