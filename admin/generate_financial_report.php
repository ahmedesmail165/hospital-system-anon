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
require_once('fpdf.php');

class FinancialReport extends FPDF {
    function Header() {
        // Logo
        $this->Image('../img/logo.png', 10, 6, 30);
        // Arial bold 15
        $this->SetFont('Arial', 'B', 15);
        // Move to the right
        $this->Cell(80);
        // Title
        $this->Cell(30, 10, 'Hospital Financial Report', 0, 0, 'C');
        // Line break
        $this->Ln(20);
    }

    function Footer() {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function ChapterTitle($title) {
        // Arial 12
        $this->SetFont('Arial', 'B', 12);
        // Background color
        $this->SetFillColor(200, 220, 255);
        // Title
        $this->Cell(0, 6, $title, 0, 1, 'L', true);
        // Line break
        $this->Ln(4);
    }

    function ChapterBody($content) {
        // Times 12
        $this->SetFont('Times', '', 12);
        // Output justified text
        $this->MultiCell(0, 5, $content);
        // Line break
        $this->Ln();
    }
}

// Get report type from URL parameter
$report_type = isset($_GET['type']) ? $_GET['type'] : 'daily';

// Initialize PDF
$pdf = new FinancialReport();
$pdf->AliasNbPages();
$pdf->AddPage();

// Set font
$pdf->SetFont('Arial', 'B', 16);

// Get current date
$today = date('Y-m-d');
$month_start = date('Y-01-01');
$year_start = date('Y-01-01');

// Report title based on type
switch($report_type) {
    case 'daily':
        $title = 'Daily Financial Report - ' . date('F j, Y');
        $period = 'Today (' . date('F j, Y') . ')';
        $start_date = $today;
        $end_date = $today;
        break;
    case 'weekly':
        $title = 'Weekly Financial Report - Week of ' . date('F j, Y', strtotime('monday this week'));
        $period = 'This Week';
        $start_date = date('Y-m-d', strtotime('monday this week'));
        $end_date = $today;
        break;
    case 'monthly':
        $title = 'Monthly Financial Report - ' . date('F Y');
        $period = 'This Month';
        $start_date = date('Y-m-01');
        $end_date = $today;
        break;
    case 'yearly':
        $title = 'Annual Financial Report - ' . date('Y');
        $period = 'This Year';
        $start_date = $year_start;
        $end_date = $today;
        break;
    default:
        $title = 'Financial Report - ' . date('F j, Y');
        $period = 'All Time';
        $start_date = '2020-01-01';
        $end_date = $today;
}

// Add title
$pdf->Cell(0, 10, $title, 0, 1, 'C');
$pdf->Ln(10);

// Revenue Summary
$pdf->ChapterTitle('Revenue Summary');

// Get revenue data
$revenue_query = "SELECT SUM(amount) as total FROM payments WHERE payment_date BETWEEN '$start_date' AND '$end_date'";
$revenue_result = $database->query($revenue_query);
$revenue_row = $revenue_result->fetch_assoc();
$total_revenue = $revenue_row['total'] ? $revenue_row['total'] : 0;

// Get outstanding payments
$outstanding_query = "SELECT SUM(amount) as total FROM invoices WHERE status='unpaid'";
$outstanding_result = $database->query($outstanding_query);
$outstanding_row = $outstanding_result->fetch_assoc();
$outstanding_total = $outstanding_row['total'] ? $outstanding_row['total'] : 0;

// Revenue details
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Period: ' . $period, 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 6, 'Total Revenue: $' . number_format($total_revenue, 2), 0, 1);
$pdf->Cell(0, 6, 'Outstanding Payments: $' . number_format($outstanding_total, 2), 0, 1);
$pdf->Cell(0, 6, 'Net Revenue: $' . number_format($total_revenue - $outstanding_total, 2), 0, 1);
$pdf->Ln(10);

// Payment Methods Breakdown
$pdf->ChapterTitle('Payment Methods Breakdown');

$payment_methods_query = "SELECT payment_method, SUM(amount) as total, COUNT(*) as count 
                         FROM payments 
                         WHERE payment_date BETWEEN '$start_date' AND '$end_date' 
                         GROUP BY payment_method";
$payment_methods_result = $database->query($payment_methods_query);

if($payment_methods_result && $payment_methods_result->num_rows > 0) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 8, 'Payment Method', 1);
    $pdf->Cell(40, 8, 'Amount', 1);
    $pdf->Cell(30, 8, 'Count', 1);
    $pdf->Cell(40, 8, 'Percentage', 1);
    $pdf->Ln();
    
    $pdf->SetFont('Arial', '', 10);
    while($row = $payment_methods_result->fetch_assoc()) {
        $percentage = $total_revenue > 0 ? ($row['total'] / $total_revenue) * 100 : 0;
        $pdf->Cell(60, 6, $row['payment_method'], 1);
        $pdf->Cell(40, 6, '$' . number_format($row['total'], 2), 1);
        $pdf->Cell(30, 6, $row['count'], 1);
        $pdf->Cell(40, 6, number_format($percentage, 1) . '%', 1);
        $pdf->Ln();
    }
} else {
    $pdf->Cell(0, 6, 'No payment data available for this period.', 0, 1);
}
$pdf->Ln(10);

// Service Types Breakdown
$pdf->ChapterTitle('Service Types Breakdown');

$service_types_query = "SELECT service_type, SUM(amount) as total, COUNT(*) as count 
                       FROM payments 
                       WHERE payment_date BETWEEN '$start_date' AND '$end_date' 
                       GROUP BY service_type";
$service_types_result = $database->query($service_types_query);

if($service_types_result && $service_types_result->num_rows > 0) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 8, 'Service Type', 1);
    $pdf->Cell(40, 8, 'Amount', 1);
    $pdf->Cell(30, 8, 'Count', 1);
    $pdf->Cell(40, 8, 'Percentage', 1);
    $pdf->Ln();
    
    $pdf->SetFont('Arial', '', 10);
    while($row = $service_types_result->fetch_assoc()) {
        $percentage = $total_revenue > 0 ? ($row['total'] / $total_revenue) * 100 : 0;
        $pdf->Cell(60, 6, $row['service_type'], 1);
        $pdf->Cell(40, 6, '$' . number_format($row['total'], 2), 1);
        $pdf->Cell(30, 6, $row['count'], 1);
        $pdf->Cell(40, 6, number_format($percentage, 1) . '%', 1);
        $pdf->Ln();
    }
} else {
    $pdf->Cell(0, 6, 'No service data available for this period.', 0, 1);
}
$pdf->Ln(10);

// Recent Transactions
$pdf->ChapterTitle('Recent Transactions');

$recent_transactions_query = "SELECT p.payment_date, p.amount, p.payment_method, p.service_type, 
                             CONCAT(pat.pname, ' (ID: ', p.patient_id, ')') as patient_name
                             FROM payments p 
                             LEFT JOIN patient pat ON p.patient_id = pat.pid
                             WHERE p.payment_date BETWEEN '$start_date' AND '$end_date'
                             ORDER BY p.payment_date DESC 
                             LIMIT 15";
$recent_transactions_result = $database->query($recent_transactions_query);

if($recent_transactions_result && $recent_transactions_result->num_rows > 0) {
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(25, 8, 'Date', 1);
    $pdf->Cell(40, 8, 'Patient', 1);
    $pdf->Cell(30, 8, 'Service', 1);
    $pdf->Cell(25, 8, 'Method', 1);
    $pdf->Cell(30, 8, 'Amount', 1);
    $pdf->Ln();
    
    $pdf->SetFont('Arial', '', 8);
    while($row = $recent_transactions_result->fetch_assoc()) {
        $pdf->Cell(25, 6, date('m/d/Y', strtotime($row['payment_date'])), 1);
        $pdf->Cell(40, 6, substr($row['patient_name'], 0, 20), 1);
        $pdf->Cell(30, 6, substr($row['service_type'], 0, 15), 1);
        $pdf->Cell(25, 6, substr($row['payment_method'], 0, 10), 1);
        $pdf->Cell(30, 6, '$' . number_format($row['amount'], 2), 1);
        $pdf->Ln();
    }
} else {
    $pdf->Cell(0, 6, 'No transactions found for this period.', 0, 1);
}
$pdf->Ln(10);

// Monthly Comparison (for monthly and yearly reports)
if($report_type == 'monthly' || $report_type == 'yearly') {
    $pdf->ChapterTitle('Monthly Revenue Comparison');
    
    $monthly_comparison_query = "SELECT MONTH(payment_date) as month, 
                                SUM(amount) as total,
                                COUNT(*) as transactions
                                FROM payments 
                                WHERE YEAR(payment_date) = YEAR('$start_date')
                                GROUP BY MONTH(payment_date)
                                ORDER BY month";
    $monthly_comparison_result = $database->query($monthly_comparison_query);
    
    if($monthly_comparison_result && $monthly_comparison_result->num_rows > 0) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(40, 8, 'Month', 1);
        $pdf->Cell(40, 8, 'Revenue', 1);
        $pdf->Cell(30, 8, 'Transactions', 1);
        $pdf->Cell(40, 8, 'Avg per Transaction', 1);
        $pdf->Ln();
        
        $pdf->SetFont('Arial', '', 10);
        while($row = $monthly_comparison_result->fetch_assoc()) {
            $month_name = date('F', mktime(0, 0, 0, $row['month'], 1));
            $avg_per_transaction = $row['transactions'] > 0 ? $row['total'] / $row['transactions'] : 0;
            
            $pdf->Cell(40, 6, $month_name, 1);
            $pdf->Cell(40, 6, '$' . number_format($row['total'], 2), 1);
            $pdf->Cell(30, 6, $row['transactions'], 1);
            $pdf->Cell(40, 6, '$' . number_format($avg_per_transaction, 2), 1);
            $pdf->Ln();
        }
    } else {
        $pdf->Cell(0, 6, 'No monthly comparison data available.', 0, 1);
    }
    $pdf->Ln(10);
}

// Summary and Recommendations
$pdf->ChapterTitle('Summary and Recommendations');

$summary_content = "This financial report covers the period from " . date('F j, Y', strtotime($start_date)) . " to " . date('F j, Y', strtotime($end_date)) . ".\n\n";

if($total_revenue > 0) {
    $summary_content .= "• Total revenue generated: $" . number_format($total_revenue, 2) . "\n";
    $summary_content .= "• Outstanding payments: $" . number_format($outstanding_total, 2) . "\n";
    $summary_content .= "• Net revenue: $" . number_format($total_revenue - $outstanding_total, 2) . "\n\n";
    
    if($outstanding_total > 0) {
        $summary_content .= "Recommendations:\n";
        $summary_content .= "• Focus on collecting outstanding payments to improve cash flow\n";
        $summary_content .= "• Review payment collection procedures\n";
        $summary_content .= "• Consider implementing payment reminders\n";
    } else {
        $summary_content .= "All payments have been collected successfully.\n";
    }
} else {
    $summary_content .= "No revenue was generated during this period. Consider reviewing marketing strategies and service offerings.";
}

$pdf->ChapterBody($summary_content);

// Add timestamp
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 6, 'Report generated on: ' . date('F j, Y \a\t g:i A'), 0, 1);

// Output PDF
$filename = 'financial_report_' . $report_type . '_' . date('Y-m-d') . '.pdf';
$pdf->Output('D', $filename);
?> 