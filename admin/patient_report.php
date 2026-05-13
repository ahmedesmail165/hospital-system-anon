<?php
// Start output buffering
ob_start();

session_start();
if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
        header("location: ../login.php");
        exit();
    }
}else{
    header("location: ../login.php");
    exit();
}

include("../connection.php");
require_once __DIR__ . '/../vendor/autoload.php';

// Extend TCPDF to add custom methods
class PatientReportPDF extends TCPDF {
    //Page header
    public function Header() {
        // Logo
        $image_file = '../img/logo.png';
        $this->Image($image_file, 15, 8, 25, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

        // Set font for title
        $this->SetFont('dejavusans', 'B', 14);
        // Title position and text
        $this->SetXY(45, 13);
        $this->Cell(0, 10, 'Patient Medical Report', 0, 1, 'L', 0, '', 0, false, 'T', 'M');

        // Set font for subtitle
        $this->SetFont('dejavusans', '', 9);
        // Subtitle position and text
        $this->SetXY(45, 21);
        $this->Cell(0, 10, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'L', 0, '', 0, false, 'T', 'M');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('dejavusans', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

    public function SectionTitle($title) {
        $this->AddPage();
        $this->SetFont('dejavusans', 'B', 14);
        $this->SetTextColor(59, 89, 152);
        $this->Cell(0, 12, $title, 0, 1, 'L');
        $this->SetTextColor(0, 0, 0);
        $this->Line($this->GetX(), $this->GetY(), $this->GetX() + 190, $this->GetY());
        $this->Ln(15);
    }

    public function ImprovedTable($header, $data, $colWidths) {
        // Colors, line width and bold font
        $this->SetFillColor(59, 89, 152);
        $this->SetTextColor(255);
        $this->SetDrawColor(64, 64, 64);
        $this->SetLineWidth(0.3);
        $this->SetFont('dejavusans', 'B', 10);

        // Header
        for($i = 0; $i < count($header); $i++) {
            $this->Cell($colWidths[$i], 8, $header[$i], 1, 0, 'C', true);
        }
        $this->Ln();

        // Data
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('dejavusans', '', 10);

        $fill = false;
        foreach($data as $row) {
            if($this->GetY() > 250) {
                $this->AddPage();
            }

            for($i = 0; $i < count($header); $i++) {
                $this->Cell($colWidths[$i], 7, $row[$i], 'LR', 0, 'L', $fill);
            }
            $this->Ln();
            $fill = !$fill;
        }
        $this->Cell(array_sum($colWidths), 0, '', 'T');
        $this->Ln(20);
    }
}

// Handle PDF download first, before any HTML output
if(isset($_GET['download']) && $_GET['download'] == 'pdf') {
    // Get patient ID from URL
    $patient_id = $_GET['id'] ?? 0;
    
    // Initialize variables
    $patient = [];
    $appointments = [];
    $prescriptions = [];
    $payments = [];
    $error = '';
    
    try {
        // Fetch patient data
        $patient_data = $database->query("SELECT * FROM patient WHERE pid = $patient_id");
        if($patient_data) {
            $patient = $patient_data->fetch_assoc();
        } else {
            throw new Exception("Error fetching patient data: " . $database->error);
        }
        
        // Fetch appointments
        $appointments_result = $database->query("SELECT a.*, doc.docname FROM appointment a JOIN schedule s ON a.scheduleid = s.scheduleid JOIN doctor doc ON s.docid = doc.docid WHERE a.pid = $patient_id ORDER BY a.appodate DESC");
        if($appointments_result) {
            $appointments = $appointments_result;
        } else {
            throw new Exception("Error fetching appointments: " . $database->error);
        }
        
        // Fetch prescriptions
        $prescriptions_result = $database->query("SELECT p.*, m.med_name FROM prescriptions p JOIN meds m ON p.med_id = m.med_id WHERE p.pid = $patient_id ORDER BY p.start_date DESC");
        if($prescriptions_result) {
            $prescriptions = $prescriptions_result;
        } else {
            throw new Exception("Error fetching prescriptions: " . $database->error);
        }
        
        // Fetch payments
        $payments_result = $database->query("SELECT * FROM payments WHERE patient_id = $patient_id ORDER BY payment_date DESC");
        if($payments_result) {
            $payments = $payments_result;
            
            // Calculate total payments and outstanding balance
            $total_payments = 0;
            $outstanding_balance = 0;
            
            // Reset the pointer to the beginning of the result set
            $payments->data_seek(0);
            
            while($row = $payments->fetch_assoc()) {
                $total_payments += floatval($row['amount'] ?? 0);
                if(strtolower($row['status'] ?? '') === 'pending') {
                    $outstanding_balance += floatval($row['amount'] ?? 0);
                }
            }
            
            // Reset the pointer again for later use
            $payments->data_seek(0);
        } else {
            throw new Exception("Error fetching payments: " . $database->error);
        }

        // Clear any previous output
        ob_clean();

        // Create new PDF document
        $pdf = new PatientReportPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Hospital Management System');
        $pdf->SetTitle('Patient Medical Report - ' . $patient['pname']);

        // Set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'Patient Medical Report', 'Generated on: ' . date('Y-m-d H:i:s'));

        // Set header and footer fonts
        $pdf->setHeaderFont(Array('dejavusans', '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array('dejavusans', '', PDF_FONT_SIZE_DATA));

        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // Set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // Set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // Add a page
        $pdf->AddPage();

        // Add a large logo to the first page
        if ($pdf->PageNo() == 1) {
            $image_file = '../img/logo.png';
            $pdf->Image($image_file, 60, 60, 90, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            $pdf->SetY(160);
        }

        // 1. Patient Information
        $pdf->SectionTitle('1. Patient Information');
        $patientInfo = [
            ['Patient Name', $patient['pname'] ?? 'N/A'],
            ['Patient ID', $patient['pid'] ?? 'N/A'],
            ['Date of Birth', $patient['pdob'] ?? 'N/A'],
            ['Gender', $patient['gender'] ?? 'N/A'],
            ['Blood Type', $patient['blood_type'] ?? 'N/A'],
            ['Allergies', $patient['allergies'] ?: 'None'],
            ['Chronic Conditions', $patient['chronic_conditions'] ?: 'None']
        ];

        $pdf->SetFont('dejavusans', '', 11);
        foreach($patientInfo as $info) {
            $pdf->Cell(60, 8, $info[0] . ':', 0, 0);
            $pdf->Cell(0, 8, $info[1], 0, 1);
        }
        $pdf->Ln(5);

        // 2. Medical History
        $pdf->SectionTitle('2. Medical History');
        $pdf->SetFont('dejavusans', '', 11);
        $pdf->MultiCell(0, 8, $patient['medical_history'] ?: 'No significant medical history recorded.', 0, 'L');
        $pdf->Ln(5);

        // 3. Appointments History
        $pdf->SectionTitle('3. Appointments History');
        if($appointments->num_rows > 0) {
            $header = ['Date', 'Doctor', 'Diagnosis', 'Status'];
            $data = [];
            while($row = $appointments->fetch_assoc()) {
                $data[] = [
                    $row['appodate'] ?? 'N/A',
                    $row['docname'] ?? 'N/A',
                    $row['diagnosis'] ?: 'N/A',
                    $row['status'] ?? 'N/A'
                ];
            }
            $pdf->ImprovedTable($header, $data, [40, 50, 60, 40]);
        } else {
            $pdf->Cell(0, 10, 'No appointments found.', 0, 1);
        }

        // 4. Prescriptions
        $pdf->SectionTitle('4. Prescriptions');
        if($prescriptions->num_rows > 0) {
            $header = ['Medication', 'Dosage', 'Frequency', 'Duration', 'Start Date', 'End Date'];
            $data = [];
            while($row = $prescriptions->fetch_assoc()) {
                $data[] = [
                    $row['med_name'] ?? 'N/A',
                    $row['dosage'] ?? 'N/A',
                    $row['frequency'] ?? 'N/A',
                    ($row['duration'] ?? 'N/A') . ' days',
                    $row['start_date'] ?? 'N/A',
                    $row['end_date'] ?? 'N/A'
                ];
            }
            $pdf->ImprovedTable($header, $data, [40, 30, 30, 30, 30, 30]);
        } else {
            $pdf->Cell(0, 10, 'No prescriptions found.', 0, 1);
        }

        // 5. Payments History
        $pdf->SectionTitle('5. Payments History');
        if($payments->num_rows > 0) {
            $header = ['Date', 'Amount', 'Service', 'Method', 'Status'];
            $data = [];
            while($row = $payments->fetch_assoc()) {
                $data[] = [
                    $row['payment_date'] ?? 'N/A',
                    '$' . ($row['amount'] ?? '0.00'),
                    $row['service_type'] ?? 'N/A',
                    $row['payment_method'] ?? 'N/A',
                    $row['status'] ?? 'N/A'
                ];
            }
            $pdf->ImprovedTable($header, $data, [40, 30, 40, 40, 40]);
        } else {
            $pdf->Cell(0, 10, 'No payments found.', 0, 1);
        }

        // Signature Page
        $pdf->AddPage();
        $pdf->SetY(100);
        $pdf->SetFont('dejavusans', 'I', 10);
        $pdf->Cell(0, 10, 'This report was automatically generated by the Hospital Management System', 0, 1, 'C');
        $pdf->Ln(20);

        if (file_exists('../img/signature.png')) {
            $pdf->Image('../img/signature.png', 85, $pdf->GetY(), 40);
            $pdf->Ln(30);
        } else {
            $pdf->Cell(0, 10, '__________________________', 0, 1, 'C');
            $pdf->Ln(20);
        }

        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell(0, 10, 'Authorized Signature', 0, 0, 'C');
        $pdf->Ln(15);
        $pdf->SetFont('dejavusans', 'I', 9);
        $pdf->Cell(0, 10, date('Y-m-d'), 0, 0, 'C');

        // Output the PDF
        $pdf->Output('patient_report_'.$patient_id.'.pdf', 'D');
        exit();
    } catch (Exception $e) {
        // Clear any previous output
        ob_clean();
        die("Error generating PDF: " . $e->getMessage());
    }
}

// Rest of the PHP code for HTML display...
// [Previous PHP code for HTML display remains the same]

// End output buffering
ob_end_flush();
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
    
    <title>Patient Reports</title>
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
        /* Report Container */
.report-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    padding: 30px;
    position: relative;
    overflow: hidden;
    max-width: 1200px;
    margin: 20px auto;
}

.report-stamp {
    position: absolute;
    top: 50px;
    right: 50px;
    opacity: 0.15;
    z-index: 0;
}

/* Header */
.report-header {
    text-align: center;
    margin-bottom: 30px;
    position: relative;
    z-index: 1;
}

.hospital-branding {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
}

.hospital-logo {
    height: 80px;
    margin-right: 20px;
}

.hospital-info h1 {
    color: #2c3e50;
    margin: 0;
    font-size: 28px;
}

.hospital-address, .hospital-contact {
    color: #7f8c8d;
    margin: 5px 0;
    font-size: 14px;
}

.report-meta {
    display: flex;
    justify-content: space-between;
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 5px;
    font-size: 13px;
    color: #555;
}

/* Patient Banner */
.patient-banner {
    display: flex;
    align-items: center;
    background: #e8f4f8;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    position: relative;
    z-index: 1;
}

.patient-photo-placeholder {
    width: 80px;
    height: 80px;
    background: #bdc3c7;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
    color: white;
    font-size: 40px;
}

.patient-core-info h2 {
    margin: 0;
    color: #2c3e50;
    font-size: 24px;
}

.patient-details {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 10px;
    font-size: 14px;
}

.patient-details span {
    background: rgba(255, 255, 255, 0.7);
    padding: 3px 10px;
    border-radius: 15px;
}

/* Report Sections */
.report-section {
    margin-bottom: 30px;
    position: relative;
    z-index: 1;
}

.section-header {
    margin-bottom: 20px;
}

.section-title {
    display: flex;
    align-items: center;
    color: #4a6fa5;
}

.section-title h2 {
    margin: 0 0 0 10px;
    font-size: 20px;
    color: #2c3e50;
}

.section-border {
    height: 2px;
    background: linear-gradient(to right, #3498db, #ecf0f1);
    margin-top: 5px;
}

/* Grid Layout */
.grid-layout {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.info-card {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #4a6fa5;
}

.info-card h3 {
    margin-top: 0;
    color: #4a6fa5;
    font-size: 16px;
    display: flex;
    align-items: center;
}

.info-card h3 i {
    margin-right: 8px;
}

/* Tables */
.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.data-table th {
    background: #4a6fa5;
    color: white;
    padding: 12px 15px;
    text-align: left;
}

.data-table td {
    padding: 10px 15px;
    border-bottom: 1px solid #ecf0f1;
}

.data-table tr:nth-child(even) {
    background-color: #f8f9fa;
}

.data-table tr:hover {
    background-color: #e8f4f8;
}

.no-data {
    text-align: center;
    padding: 20px;
    color: #7f8c8d;
}

/* Status Badges */
.status-badge {
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 500;
    text-transform: capitalize;
}

.status-badge.completed, 
.status-badge.paid {
    background: #2ecc71;
    color: white;
}

.status-badge.pending {
    background: #f39c12;
    color: white;
}

.status-badge.cancelled {
    background: #e74c3c;
    color: white;
}

/* Financial Summary */
.financial-summary {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.financial-card {
    flex: 1;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
}

.financial-card h3 {
    margin-top: 0;
    color: #7f8c8d;
    font-size: 16px;
}

.financial-card .amount {
    font-size: 24px;
    font-weight: bold;
    margin: 10px 0;
    color: #2c3e50;
}

.financial-card .outstanding {
    color: #e74c3c;
}

/* Footer */
.report-footer {
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #ecf0f1;
}

.disclaimer {
    font-size: 12px;
    color: #7f8c8d;
    margin-bottom: 30px;
    line-height: 1.5;
}

.signature-area {
    display: flex;
    justify-content: space-between;
    margin: 40px 0;
}

.signature-box {
    text-align: center;
    width: 45%;
}

.signature-box p {
    margin: 5px 0;
}

.report-qr {
    text-align: center;
    margin-top: 20px;
}

.report-qr p {
    font-size: 12px;
    color: #7f8c8d;
    margin-top: 5px;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 30px;
}

.btn {
    padding: 10px 20px;
    border-radius: 5px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-tertiary:hover {
    background: #bdc3c7;
}

/* Animations */
.animate-slide-up {
    animation: slideUp 0.5s ease-out;
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

/* Print Styles */
@media print {
    .action-buttons {
        display: none;
    }
    
    .report-container {
        box-shadow: none;
        padding: 0;
    }
    
    .report-stamp {
        opacity: 0.3;
    }
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
        .header-search {
                max-width: 60%;
                width: 70%;
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
    <?php
    // Get patient ID from URL
    $patient_id = $_GET['id'] ?? 0;
    
    // Initialize variables
    $patient = [];
    $appointments = [];
    $prescriptions = [];
    $payments = [];
    $error = '';
    
    try {
        // Fetch patient data
        $patient_data = $database->query("SELECT * FROM patient WHERE pid = $patient_id");
        if($patient_data) {
            $patient = $patient_data->fetch_assoc();
        } else {
            throw new Exception("Error fetching patient data: " . $database->error);
        }
        
        // Fetch appointments
        $appointments_result = $database->query("SELECT a.*, doc.docname FROM appointment a JOIN schedule s ON a.scheduleid = s.scheduleid JOIN doctor doc ON s.docid = doc.docid WHERE a.pid = $patient_id ORDER BY a.appodate DESC");

        if($appointments_result) {
            $appointments = $appointments_result;
        } else {
            throw new Exception("Error fetching appointments: " . $database->error);
        }
        
        // Fetch prescriptions
        $prescriptions_result = $database->query("SELECT p.*, m.med_name 
                                      FROM prescriptions p 
                                      JOIN meds m ON p.med_id = m.med_id 
                                      WHERE p.pid = $patient_id 
                                      ORDER BY p.start_date DESC");
        if($prescriptions_result) {
            $prescriptions = $prescriptions_result;
        } else {
            throw new Exception("Error fetching prescriptions: " . $database->error);
        }
        
        // Fetch payments
        $payments_result = $database->query("SELECT * FROM payments WHERE patient_id = $patient_id ORDER BY payment_date DESC");
        if($payments_result) {
            $payments = $payments_result;
            
            // Calculate total payments and outstanding balance
            $total_payments = 0;
            $outstanding_balance = 0;
            
            // Reset the pointer to the beginning of the result set
            $payments->data_seek(0);
            
            while($row = $payments->fetch_assoc()) {
                $total_payments += floatval($row['amount'] ?? 0);
                if(strtolower($row['status'] ?? '') === 'pending') {
                    $outstanding_balance += floatval($row['amount'] ?? 0);
                }
            }
            
            // Reset the pointer again for later use
            $payments->data_seek(0);
        } else {
            throw new Exception("Error fetching payments: " . $database->error);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
    ?>
    
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
                        <a href="patien t.php" class="non-style-link-menu">
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
    <div class="nav-bar animate-slide-up">
        <div>
            <a href="patient.php" class="non-style-link">
                <button onclick="window.history.back();" class="btn-primary btn-icon-back" style="padding: 8px 15px;">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
            </a>
        </div>
        <div>
            <p class="header-title">Patient Medical Report</p>
        </div>
        <div class="date-container">
            <i class="far fa-calendar-alt date-icon"></i>
            <div class="date-text">
                <p class="date-label">Today's Date</p>
                <p class="current-date"><?php echo date("F j, Y"); ?></p>
            </div>
        </div>
    </div>

    <div class="report-container animate-slide-up">
    <?php if($error): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error; ?>
        </div>
    <?php else: ?>
        <!-- Watermark stamp -->
        <img src="../img/hospital-stamp.png" class="report-stamp" width="150">
        
        <div class="report-header">
            <div class="hospital-branding">
                <div class="hospital-info">
                    <h1>Opticare Hospital Report</h1>
                    <p class="hospital-address">123 New Mansoura, Egypt</p>
                    <p class="hospital-contact">Email: info@opticarehospital.com</p>
                </div>
            </div>
            <div class="report-meta">
                <p class="report-id">Report ID: MR-<?php echo date('Ymd') . '-' . substr($patient['pid'], 0, 4); ?></p>
                <p class="report-date">Generated: <?php echo date('F j, Y H:i:s') ?></p>
            </div>
        </div>
        
        <div class="patient-banner">
            <div class="patient-photo-placeholder">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="patient-core-info">
                <h2><?php echo $patient['pname'] ?? 'N/A' ?></h2>
                <div class="patient-details">
                    <span><strong>ID:</strong> <?php echo $patient['pid'] ?? 'N/A' ?></span>
                    <span><strong>DOB:</strong> <?php echo $patient['pdob'] ?? 'N/A' ?></span>
                    <span><strong>Gender:</strong> <?php echo $patient['gender'] ?? 'N/A' ?></span>
                    <span><strong>Blood Type:</strong> <?php echo $patient['blood_type'] ?? 'N/A' ?></span>
                </div>
            </div>
        </div>
        
        <div class="report-body">
            <div class="report-section">
                <div class="section-header">
                    <div class="section-title">
                        <i class="fas fa-user-injured"></i>
                        <h2>Patient Information</h2>
                    </div>
                    <div class="section-border"></div>
                </div>
                <div class="section-content grid-layout">
                    <div class="info-card">
                        <h3><i class="fas fa-allergies"></i> Allergies</h3>
                        <p><?php echo $patient['allergies'] ?: 'None recorded' ?></p>
                    </div>
                    <div class="info-card">
                        <h3><i class="fas fa-heartbeat"></i> Chronic Conditions</h3>
                        <p><?php echo $patient['chronic_conditions'] ?: 'None recorded' ?></p>
                    </div>
                    <div class="info-card">
                        <h3><i class="fas fa-notes-medical"></i> Medical History</h3>
                        <p><?php echo $patient['medical_history'] ?: 'No significant medical history recorded.' ?></p>
                    </div>
                </div>
            </div>
            
            <div class="report-section">
                <div class="section-header">
                    <div class="section-title">
                        <i class="fas fa-calendar-check"></i>
                        <h2>Appointments History</h2>
                    </div>
                    <div class="section-border"></div>
                </div>
                <div class="section-content">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Doctor</th>
                                    <th>Specialty</th>
                                    <th>Diagnosis</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($appointments->num_rows == 0): ?>
                                    <tr>
                                        <td colspan="5" class="no-data">No appointments found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php while($row = $appointments->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['appodate'] ?? 'N/A' ?></td>
                                            <td><?php echo $row['docname'] ?? 'N/A' ?></td>
                                            <td><?php echo $row['specialty'] ?? 'General' ?></td>
                                            <td><?php echo $row['diagnosis'] ?: 'N/A' ?></td>
                                            <td>
                                                <span class="status-badge <?php echo strtolower($row['status'] ?? '') ?>">
                                                    <?php echo $row['status'] ?? 'N/A' ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="report-section">
                <div class="section-header">
                    <div class="section-title">
                        <i class="fas fa-prescription-bottle-alt"></i>
                        <h2>Active Prescriptions</h2>
                    </div>
                    <div class="section-border"></div>
                </div>
                <div class="section-content">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Medication</th>
                                    <th>Dosage</th>
                                    <th>Frequency</th>
                                    <th>Duration</th>
                                    <th>Prescribed On</th>
                                    <th>Prescribing Doctor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($prescriptions->num_rows == 0): ?>
                                    <tr>
                                        <td colspan="6" class="no-data">No active prescriptions found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php while($row = $prescriptions->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong><?php echo $row['med_name'] ?? 'N/A' ?></strong></td>
                                            <td><?php echo $row['dosage'] ?? 'N/A' ?></td>
                                            <td><?php echo $row['frequency'] ?? 'N/A' ?></td>
                                            <td><?php echo $row['duration'] ?? 'N/A' ?> days</td>
                                            <td><?php echo $row['start_date'] ?? 'N/A' ?></td>
                                            <td><?php echo $row['docname'] ?? 'N/A' ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="report-section">
                <div class="section-header">
                    <div class="section-title">
                        <i class="fas fa-money-bill-wave"></i>
                        <h2>Financial Summary</h2>
                    </div>
                    <div class="section-border"></div>
                </div>
                <div class="section-content">
                    <div class="financial-summary">
                        <div class="financial-card">
                            <h3>Total Payments</h3>
                            <p class="amount">$<?php echo number_format($total_payments, 2) ?></p>
                        </div>
                        <div class="financial-card">
                            <h3>Outstanding Balance</h3>
                            <p class="amount outstanding">$<?php echo number_format($outstanding_balance, 2) ?></p>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Invoice #</th>
                                    <th>Amount</th>
                                    <th>Service</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($payments->num_rows == 0): ?>
                                    <tr>
                                        <td colspan="6" class="no-data">No payment records found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php while($row = $payments->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['payment_date'] ?? 'N/A' ?></td>
                                            <td>INV-<?php echo $row['invoice_number'] ?? 'N/A' ?></td>
                                            <td>$<?php echo number_format($row['amount'] ?? 0, 2) ?></td>
                                            <td><?php echo $row['service_type'] ?? 'N/A' ?></td>
                                            <td><?php echo $row['payment_method'] ?? 'N/A' ?></td>
                                            <td>
                                                <span class="status-badge <?php echo strtolower($row['status'] ?? '') ?>">
                                                    <?php echo $row['status'] ?? 'N/A' ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="report-footer">
            <div class="disclaimer">
                <p><strong>Disclaimer:</strong> This report is generated electronically and does not require a physical signature. The information contained in this report is confidential and intended solely for the use of the individual or entity to whom it is addressed.</p>
            </div>
            
            <div class="signature-area">
                <div class="signature-box">
                    <p>_________________________</p>
                    <p>Dr. <?php echo $primary_doctor ?? 'Attending Physician' ?></p>
                    <p>License #: <?php echo $doctor_license ?? 'MD-XXXXXX' ?></p>
                </div>
                <div class="signature-box">
                    <p>_________________________</p>
                    <p>Date: <?php echo date('m/d/Y') ?></p>
                </div>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="?id=<?php echo $patient_id; ?>&download=pdf" class="non-style-link">
                <button class="btn-primary btn">
                    <i class="fas fa-file-pdf"></i> Download as PDF
                </button>
            </a>
            <a href="patient.php" class="non-style-link">
                <button class="btn-secondary btn">
                    <i class="fas fa-arrow-left"></i> Back to Patient Dashboard
                </button>
            </a>
        </div>
    <?php endif; ?>
</div>
