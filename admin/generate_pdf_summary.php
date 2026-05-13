<?php
include("../connection.php");
require_once __DIR__ . '/../vendor/autoload.php';

// التحقق من اتصال قاعدة البيانات
if ($database->connect_error) {
    die("Connection failed: " . $database->connect_error);
}

// Extend TCPDF to add custom methods
class MYPDF extends TCPDF {
    //Page header
    public function Header() {
        // Logo
        $image_file = '../img/logo.png'; // Corrected path to img directory
        $this->Image($image_file, 15, 8, 25, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

        // Set font for title
        $this->SetFont('dejavusans', 'B', 14);
        // Title position and text
        $this->SetXY(45, 13);
        $this->Cell(0, 10, 'Opticare system Report', 0, 1, 'L', 0, '', 0, false, 'T', 'M');

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

    public function BarChart($data, $width = 190, $height = 60) {
        if($this->GetY() > 200) {
            $this->AddPage();
        }

        $maxValue = max($data);
        $barWidth = $width / count($data);

        $this->SetFont('dejavusans', '', 9);

        $x = $this->GetX();
        $y = $this->GetY();

        foreach($data as $label => $value) {
            $barHeight = ($value / $maxValue) * $height;
            $this->SetFillColor(59, 89, 152);
            $this->Rect($x, $y + $height - $barHeight, $barWidth - 2, $barHeight, 'F');

            $this->SetXY($x, $y + $height + 2);
            $this->Cell($barWidth, 5, $label, 0, 0, 'C');

            $this->SetXY($x, $y + $height - $barHeight - 8);
            $this->Cell($barWidth, 5, $value, 0, 0, 'C');

            $x += $barWidth;
        }

        $this->SetXY($this->GetX(), $y + $height + 25);
    }
}

// Create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('eDoc System');
$pdf->SetTitle('System Summary Report');

// Set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'Opticare system Report', 'Generated on: ' . date('Y-m-d H:i:s'));

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

// Set font
$pdf->SetFont('dejavusans', '', 12);

// Add a page
$pdf->AddPage();

// Add a large logo to the first page
if ($pdf->PageNo() == 1) {
    $image_file = '../img/logo.png'; // Path to your logo
    $pdf->Image($image_file, 60, 60, 90, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    $pdf->SetY(160); // Set Y position below the large logo
}

// 1. System Overview
$pdf->SectionTitle('1. System Overview');
$counts = [
    'Doctors' => $database->query("SELECT COUNT(*) FROM doctor")->fetch_row()[0],
    'Patients' => $database->query("SELECT COUNT(*) FROM patient")->fetch_row()[0],
    'Doctor Appointments' => $database->query("SELECT COUNT(*) FROM appointment")->fetch_row()[0],
    'Lab Appointments' => $database->query("SELECT COUNT(*) FROM lab_appointments")->fetch_row()[0],
    'Radiology Appointments' => $database->query("SELECT COUNT(*) FROM radiology_appointment")->fetch_row()[0],
    'Schedules' => $database->query("SELECT COUNT(*) FROM schedule")->fetch_row()[0],
    'Invoices' => $database->query("SELECT COUNT(*) FROM invoices")->fetch_row()[0],
    'Payments' => $database->query("SELECT COUNT(*) FROM payments")->fetch_row()[0],
    'Medications' => $database->query("SELECT COUNT(*) FROM meds")->fetch_row()[0],
    'Ambulance Requests' => $database->query("SELECT COUNT(*) FROM ambulance_requests")->fetch_row()[0]
];

$pdf->SetFont('dejavusans', '', 12);
foreach ($counts as $label => $count) {
    $pdf->Cell(95, 10, $label . ':', 0, 0);
    $pdf->Cell(0, 10, number_format($count), 0, 1);
}
$pdf->Ln(25);

// 2. Recent Appointments
$pdf->SectionTitle('2. Recent Doctor Appointments (Last 7 Days)');
$seven_days_ago = date('Y-m-d', strtotime('-7 days'));
$recent_appointments = $database->query("
    SELECT a.apponum, p.pname, d.docname, a.appodate, a.status 
    FROM appointment a 
    INNER JOIN patient p ON a.pid = p.pid 
    INNER JOIN schedule s ON a.scheduleid = s.scheduleid
    INNER JOIN doctor d ON s.docid = d.docid
    WHERE a.appodate >= '$seven_days_ago' 
    ORDER BY a.appodate DESC 
    LIMIT 15
");

if ($recent_appointments->num_rows > 0) {
    $header = ['Appt No.', 'Patient Name', 'Doctor Name', 'Date', 'Status'];
    $data = [];
    while($row = $recent_appointments->fetch_assoc()) {
        $data[] = [
            $row['apponum'],
            $row['pname'],
            $row['docname'],
            $row['appodate'],
            ucfirst($row['status'])
        ];
    }
    $pdf->ImprovedTable($header, $data, [25, 50, 50, 30, 35]);
} else {
    $pdf->Cell(0, 10, 'No recent doctor appointments in the last 7 days.', 0, 1);
}

// 3. Recent Payments
$pdf->SectionTitle('3. Recent Payments (Last 30 Days)');
$thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
$recent_payments = $database->query("
    SELECT p.payment_id, pat.pname, p.amount, p.payment_date, p.payment_method, p.status 
    FROM payments p 
    INNER JOIN patient pat ON p.patient_id = pat.pid 
    WHERE p.payment_date >= '$thirty_days_ago' 
    ORDER BY p.payment_date DESC 
    LIMIT 15
");

if ($recent_payments->num_rows > 0) {
    $header = ['ID', 'Patient', 'Amount', 'Date', 'Method', 'Status'];
    $data = [];
    while($row = $recent_payments->fetch_assoc()) {
        $data[] = [
            $row['payment_id'],
            $row['pname'],
            '$' . number_format($row['amount'], 2),
            $row['payment_date'],
            $row['payment_method'],
            ucfirst($row['status'])
        ];
    }
    $pdf->ImprovedTable($header, $data, [15, 50, 30, 30, 30, 35]);
} else {
    $pdf->Cell(0, 10, 'No recent payments in the last 30 days.', 0, 1);
}

// 4. Appointment Distribution
$pdf->SectionTitle('4. Appointment Type Distribution');
$appt_types = [
    'Doctor' => $database->query("SELECT COUNT(*) FROM appointment")->fetch_row()[0],
    'Lab' => $database->query("SELECT COUNT(*) FROM lab_appointments")->fetch_row()[0],
    'Radiology' => $database->query("SELECT COUNT(*) FROM radiology_appointment")->fetch_row()[0]
];

$pdf->BarChart($appt_types);
$pdf->Ln(10);

$header = ['Type', 'Count', 'Percentage'];
$data = [];
$total = array_sum($appt_types);
foreach ($appt_types as $type => $count) {
    $percentage = ($total > 0) ? round(($count / $total) * 100, 2) . '%' : '0%';
    $data[] = [$type, $count, $percentage];
}
$pdf->ImprovedTable($header, $data, [60, 40, 40]);

// 5. Patient Demographics
$pdf->SectionTitle('5. Patient Gender Distribution');
$gender_dist = [
    'Male' => $database->query("SELECT COUNT(*) FROM patient WHERE gender = 'male'")->fetch_row()[0],
    'Female' => $database->query("SELECT COUNT(*) FROM patient WHERE gender = 'female'")->fetch_row()[0],
    'Unknown' => $database->query("SELECT COUNT(*) FROM patient WHERE gender IS NULL OR gender = ''")->fetch_row()[0]
];

$pdf->BarChart($gender_dist);
$pdf->Ln(10);

$header = ['Gender', 'Count', 'Percentage'];
$data = [];
$total = array_sum($gender_dist);
foreach ($gender_dist as $gender => $count) {
    $percentage = ($total > 0) ? round(($count / $total) * 100, 2) . '%' : '0%';
    $data[] = [$gender, $count, $percentage];
}
$pdf->ImprovedTable($header, $data, [60, 40, 40]);

// 6. Medications
$pdf->SectionTitle('6. Top Medications in Inventory');
$medications = $database->query("
    SELECT med_name, med_qty, med_price, expiry_date 
    FROM meds 
    ORDER BY med_qty DESC 
    LIMIT 10
");

if ($medications->num_rows > 0) {
    $header = ['Medication', 'Quantity', 'Unit Price', 'Expiry Date'];
    $data = [];
    while($row = $medications->fetch_assoc()) {
        $data[] = [
            $row['med_name'],
            $row['med_qty'],
            '$' . number_format($row['med_price'], 2),
            $row['expiry_date']
        ];
    }
    $pdf->ImprovedTable($header, $data, [70, 30, 30, 30]);
} else {
    $pdf->Cell(0, 10, 'No medication data available.', 0, 1);
}

// 7. Ambulance Requests
$pdf->SectionTitle('7. Recent Ambulance Requests (Last 30 Days)');
$ambulance_requests = $database->query("
    SELECT patient_name, phone, address, emergency_type, request_time 
    FROM ambulance_requests 
    WHERE request_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ORDER BY request_time DESC 
    LIMIT 10
");

if ($ambulance_requests->num_rows > 0) {
    $header = ['Patient', 'Phone', 'Address', 'Emergency Type', 'Request Time'];
    $data = [];
    while($row = $ambulance_requests->fetch_assoc()) {
        $data[] = [
            $row['patient_name'],
            $row['phone'],
            substr($row['address'], 0, 20) . (strlen($row['address']) > 20 ? '...' : ''),
            substr($row['emergency_type'], 0, 20) . (strlen($row['emergency_type']) > 20 ? '...' : ''),
            $row['request_time']
        ];
    }
    $pdf->ImprovedTable($header, $data, [40, 30, 40, 40, 40]);
} else {
    $pdf->Cell(0, 10, 'No recent ambulance requests in the last 30 days.', 0, 1);
}

// 8. Financial Summary
$pdf->SectionTitle('8. Financial Summary');
$financial_data = [
    'Total Payments' => $database->query("SELECT SUM(amount) FROM payments WHERE status = 'completed'")->fetch_row()[0],
    'Total Invoices' => $database->query("SELECT SUM(amount) FROM invoices")->fetch_row()[0],
    'Unpaid Invoices' => $database->query("SELECT SUM(amount) FROM invoices WHERE status = 'unpaid'")->fetch_row()[0],
    'Overdue Invoices' => $database->query("SELECT SUM(amount) FROM invoices WHERE status = 'overdue'")->fetch_row()[0],
    'Pending Payments' => $database->query("SELECT SUM(amount) FROM payments WHERE status = 'pending'")->fetch_row()[0]
];

$pdf->SetFont('dejavusans', '', 12);
foreach ($financial_data as $label => $amount) {
    $pdf->Cell(95, 10, $label . ':', 0, 0);
    $pdf->Cell(0, 10, '$' . number_format($amount ?: 0, 2), 0, 1);
}
$pdf->Ln(25);

// 9. Payment Methods
$pdf->SectionTitle('9. Payment Methods Distribution');
$payment_methods = [];
$result = $database->query("SELECT payment_method, COUNT(*) as count FROM payments GROUP BY payment_method");
while ($row = $result->fetch_assoc()) {
    $payment_methods[$row['payment_method']] = $row['count'];
}

$pdf->BarChart($payment_methods);
$pdf->Ln(10);

// Signature Page
$pdf->AddPage();
$pdf->SetY(100);
$pdf->SetFont('dejavusans', 'I', 10);
$pdf->Cell(0, 10, 'This report was automatically generated by the Opticare System', 0, 1, 'C');
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
$pdf->Output('eDoc_System_Report_' . date('Y-m-d') . '.pdf', 'D');

$database->close();
?>