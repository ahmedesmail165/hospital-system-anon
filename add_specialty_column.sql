
ALTER TABLE patient ADD COLUMN specialty_id INT;


ALTER TABLE patient ADD CONSTRAINT fk_patient_specialty 
FOREIGN KEY (specialty_id) REFERENCES specialties(id); 