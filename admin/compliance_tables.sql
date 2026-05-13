-- جدول الامتثال الشهري
CREATE TABLE monthly_compliance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    month DATE NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول توزيع الامتثال حسب الفئات
CREATE TABLE compliance_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- جدول امتثال الأقسام
CREATE TABLE department_compliance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    department_name VARCHAR(100) NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- جدول نتائج التدقيق
DROP TABLE IF EXISTS audit_results;
CREATE TABLE audit_results (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category VARCHAR(100) NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    status ENUM('Excellent', 'Good', 'Fair', 'Poor') NOT NULL,
    findings TEXT,
    recommendations TEXT,
    last_audit_date DATE NOT NULL,
    next_audit_date DATE NOT NULL,
    auditor_name VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- إدخال بيانات تجريبية للامتثال الشهري
INSERT INTO monthly_compliance (month, score) VALUES
('2024-01-01', 85),
('2024-02-01', 88),
('2024-03-01', 90),
('2024-04-01', 92),
('2024-05-01', 95),
('2024-06-01', 98);

-- إدخال بيانات تجريبية لفئات الامتثال
INSERT INTO compliance_categories (category_name, score) VALUES
('Patient Safety', 95),
('Clinical Care', 97),
('Administrative', 92),
('Infection Control', 94);

-- إدخال بيانات تجريبية لامتثال الأقسام
INSERT INTO department_compliance (department_name, score) VALUES
('Emergency', 96),
('Surgery', 94),
('ICU', 98),
('Radiology', 93),
('Laboratory', 95);

-- إدخال بيانات تجريبية لنتائج التدقيق
INSERT INTO audit_results (category, score, status, findings, recommendations, last_audit_date, next_audit_date, auditor_name, department) VALUES
('Documentation', 90, 'Good', 'Most documentation is complete and up-to-date. Some minor gaps in patient consent forms.', 'Implement digital consent forms. Schedule staff training on documentation requirements.', '2024-03-15', '2024-06-15', 'Dr. Sarah Johnson', 'Medical Records'),
('Safety', 95, 'Excellent', 'Strong safety protocols in place. Regular safety drills conducted.', 'Maintain current safety standards. Consider additional emergency response training.', '2024-03-10', '2024-06-10', 'John Smith', 'Safety Department'),
('Infection Control', 92, 'Good', 'Good compliance with infection control protocols. Some areas need improvement in hand hygiene.', 'Increase hand hygiene monitoring. Schedule refresher training for staff.', '2024-03-20', '2024-06-20', 'Dr. Michael Brown', 'Infection Control'),
('Staff Training', 88, 'Good', 'Most staff have completed required training. Some new staff need additional training.', 'Develop online training modules. Implement training tracking system.', '2024-03-05', '2024-06-05', 'Lisa Anderson', 'Human Resources'),
('Equipment', 94, 'Excellent', 'All equipment properly maintained and calibrated. Good inventory management.', 'Continue regular maintenance schedule. Consider upgrading older equipment.', '2024-03-25', '2024-06-25', 'Robert Wilson', 'Biomedical Engineering'),
('Patient Care', 96, 'Excellent', 'High quality patient care standards maintained. Strong patient satisfaction scores.', 'Implement patient feedback system. Consider additional patient education materials.', '2024-03-30', '2024-06-30', 'Dr. Emily Davis', 'Quality Assurance'); 