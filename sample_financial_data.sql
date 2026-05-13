-- إنشاء جدول المدفوعات إذا لم يكن موجوداً
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    service_type VARCHAR(100) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    status VARCHAR(20) DEFAULT 'completed'
);

-- إنشاء جدول الفواتير إذا لم يكن موجوداً
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'unpaid',
    service_type VARCHAR(100) NOT NULL
);

-- حذف البيانات القديمة إذا وجدت
TRUNCATE TABLE payments;
TRUNCATE TABLE invoices;

-- إدخال بيانات تجريبية للمدفوعات
INSERT INTO payments (patient_id, amount, payment_date, service_type, payment_method) VALUES
-- مدفوعات اليوم
(1, 250.00, CURDATE(), 'Consultation', 'Credit Card'),
(2, 150.00, CURDATE(), 'Lab Test', 'Cash'),
(3, 350.00, CURDATE(), 'X-Ray', 'Debit Card'),
(4, 180.00, CURDATE(), 'Medication', 'Credit Card'),
(5, 120.00, CURDATE(), 'Check-up', 'Cash'),

-- مدفوعات الأمس
(6, 450.00, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Surgery', 'Credit Card'),
(7, 95.00, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Lab Test', 'Cash'),
(8, 280.00, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'MRI Scan', 'Debit Card'),
(9, 160.00, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Consultation', 'Credit Card'),
(10, 90.00, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Check-up', 'Cash'),

-- مدفوعات قبل يومين
(11, 380.00, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Surgery', 'Credit Card'),
(12, 120.00, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Lab Test', 'Cash'),
(13, 420.00, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'CT Scan', 'Debit Card'),
(14, 190.00, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Consultation', 'Credit Card'),
(15, 110.00, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Check-up', 'Cash'),

-- مدفوعات قبل ثلاثة أيام
(16, 520.00, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Major Surgery', 'Credit Card'),
(17, 180.00, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Lab Test', 'Cash'),
(18, 480.00, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'MRI Scan', 'Debit Card'),
(19, 220.00, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Consultation', 'Credit Card'),
(20, 130.00, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Check-up', 'Cash'),

-- مدفوعات قبل أربعة أيام
(21, 450.00, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'Surgery', 'Credit Card'),
(22, 160.00, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'Lab Test', 'Cash'),
(23, 380.00, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'X-Ray', 'Debit Card'),
(24, 200.00, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'Consultation', 'Credit Card'),
(25, 140.00, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'Check-up', 'Cash'),

-- مدفوعات قبل خمسة أيام
(26, 580.00, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Major Surgery', 'Credit Card'),
(27, 170.00, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Lab Test', 'Cash'),
(28, 420.00, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'CT Scan', 'Debit Card'),
(29, 240.00, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Consultation', 'Credit Card'),
(30, 150.00, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Check-up', 'Cash'),

-- مدفوعات قبل ستة أيام
(31, 480.00, DATE_SUB(CURDATE(), INTERVAL 6 DAY), 'Surgery', 'Credit Card'),
(32, 190.00, DATE_SUB(CURDATE(), INTERVAL 6 DAY), 'Lab Test', 'Cash'),
(33, 350.00, DATE_SUB(CURDATE(), INTERVAL 6 DAY), 'MRI Scan', 'Debit Card'),
(34, 210.00, DATE_SUB(CURDATE(), INTERVAL 6 DAY), 'Consultation', 'Credit Card'),
(35, 160.00, DATE_SUB(CURDATE(), INTERVAL 6 DAY), 'Check-up', 'Cash'),

-- مدفوعات الشهر الماضي
(36, 650.00, DATE_SUB(CURDATE(), INTERVAL 15 DAY), 'Major Surgery', 'Credit Card'),
(37, 200.00, DATE_SUB(CURDATE(), INTERVAL 16 DAY), 'Lab Test', 'Cash'),
(38, 550.00, DATE_SUB(CURDATE(), INTERVAL 17 DAY), 'CT Scan', 'Debit Card'),
(39, 280.00, DATE_SUB(CURDATE(), INTERVAL 18 DAY), 'Consultation', 'Credit Card'),
(40, 180.00, DATE_SUB(CURDATE(), INTERVAL 19 DAY), 'Check-up', 'Cash'),

-- مدفوعات السنة الماضية
(41, 1200.00, DATE_SUB(CURDATE(), INTERVAL 180 DAY), 'Major Surgery', 'Credit Card'),
(42, 350.00, DATE_SUB(CURDATE(), INTERVAL 181 DAY), 'Lab Test', 'Cash'),
(43, 800.00, DATE_SUB(CURDATE(), INTERVAL 182 DAY), 'MRI Scan', 'Debit Card'),
(44, 400.00, DATE_SUB(CURDATE(), INTERVAL 183 DAY), 'Consultation', 'Credit Card'),
(45, 250.00, DATE_SUB(CURDATE(), INTERVAL 184 DAY), 'Check-up', 'Cash');

-- إدخال بيانات تجريبية للفواتير غير المدفوعة
INSERT INTO invoices (patient_id, amount, invoice_date, due_date, status, service_type) VALUES
(46, 850.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'unpaid', 'Major Surgery'),
(47, 250.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'unpaid', 'Lab Test'),
(48, 650.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'unpaid', 'MRI Scan'),
(49, 350.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'unpaid', 'Consultation'),
(50, 200.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'unpaid', 'Check-up'),
(51, 950.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'unpaid', 'Major Surgery'),
(52, 280.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'unpaid', 'Lab Test'),
(53, 720.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'unpaid', 'CT Scan'),
(54, 380.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'unpaid', 'Consultation'),
(55, 220.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'unpaid', 'Check-up');

-- Add payments for today
INSERT INTO payments (patient_id, amount, payment_date, service_type, payment_method, status) VALUES
(1, 250, CURDATE(), 'Consultation', 'Cash', 'paid'),
(2, 400, CURDATE(), 'Lab Test', 'Credit Card', 'paid'),
(3, 600, CURDATE(), 'Surgery', 'Debit Card', 'paid'),
(4, 150, CURDATE(), 'X-Ray', 'Cash', 'paid'),
(5, 300, CURDATE(), 'Medication', 'Credit Card', 'paid');

-- Add payments for this week (excluding today)
INSERT INTO payments (patient_id, amount, payment_date, service_type, payment_method, status) VALUES
(6, 200, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Consultation', 'Cash', 'paid'),
(7, 350, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Lab Test', 'Credit Card', 'paid'),
(8, 500, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Surgery', 'Debit Card', 'paid'),
(9, 120, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'X-Ray', 'Cash', 'paid'),
(10, 280, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Medication', 'Credit Card', 'paid');

-- Add payments for earlier this month (not this week)
INSERT INTO payments (patient_id, amount, payment_date, service_type, payment_method, status) VALUES
(11, 220, DATE_SUB(CURDATE(), INTERVAL 8 DAY), 'Consultation', 'Cash', 'paid'),
(12, 370, DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'Lab Test', 'Credit Card', 'paid'),
(13, 550, DATE_SUB(CURDATE(), INTERVAL 12 DAY), 'Surgery', 'Debit Card', 'paid'),
(14, 130, DATE_SUB(CURDATE(), INTERVAL 15 DAY), 'X-Ray', 'Cash', 'paid'),
(15, 290, DATE_SUB(CURDATE(), INTERVAL 18 DAY), 'Medication', 'Credit Card', 'paid'); 