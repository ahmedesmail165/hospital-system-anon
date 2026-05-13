from flask import Flask, render_template, request, redirect, url_for, send_file
import numpy as np
import tensorflow as tf
from tensorflow.keras.models import load_model
from sklearn.preprocessing import StandardScaler
from sklearn.feature_extraction import FeatureHasher
import joblib
from fpdf import FPDF
import os
import io
import matplotlib.pyplot as plt
from matplotlib.patches import ConnectionPatch

app = Flask(__name__)

# Load the trained models and preprocessing objects
# Load the trained models and preprocessing objects
scaler = joblib.load("scaler.joblib")
label_encoder = joblib.load("label_encoder.joblib")
features = joblib.load("features.joblib")
model = load_model("cbc_diagnosis_model.h5")
# Disease information dictionary with explanations and treatments in English
disease_info = {
    "Anemia": {
        "description": "A condition in which the blood doesn't have enough healthy red blood cells or hemoglobin.",
        "symptoms": "Fatigue, weakness, pale skin, shortness of breath, dizziness, cold hands and feet",
        "causes": "Iron deficiency, vitamin deficiency, chronic diseases, bone marrow problems",
        "treatments": "Iron supplements, vitamin supplements, dietary changes, treating underlying conditions",
        "recommendations": "1. Consult a hematologist\n2. Take prescribed iron supplements\n3. Eat iron-rich foods (red meat, beans, leafy greens)\n4. Get follow-up blood tests in 3 months"
    },
    "Iron Deficiency Anemia": {
        "description": "A specific type of anemia caused by insufficient iron in the body.",
        "symptoms": "Extreme fatigue, weakness, pale skin, brittle nails, unusual cravings for non-nutritive substances",
        "causes": "Blood loss, lack of iron in diet, inability to absorb iron",
        "treatments": "Iron supplements, increased dietary iron, treatment of underlying cause of bleeding",
        "recommendations": "1. Take iron supplements as prescribed\n2. Increase vitamin C intake to enhance iron absorption\n3. Avoid calcium supplements with iron\n4. Follow up with CBC in 2 months"
    },
    "Megaloblastic Anemia": {
        "description": "Anemia characterized by larger-than-normal red blood cells due to vitamin B12 or folate deficiency.",
        "symptoms": "Fatigue, pale skin, diarrhea, numbness/tingling in extremities, muscle weakness",
        "causes": "Vitamin B12 deficiency, folate deficiency, malabsorption disorders",
        "treatments": "Vitamin B12 injections, oral supplements, folate supplements, dietary changes",
        "recommendations": "1. Start vitamin B12 injections\n2. Increase folate-rich foods\n3. Monitor neurological symptoms\n4. Get regular blood tests"
    },
    "Thalassemia": {
        "description": "An inherited blood disorder characterized by less hemoglobin and fewer red blood cells.",
        "symptoms": "Fatigue, weakness, pale or yellowish skin, facial bone deformities, dark urine",
        "causes": "Genetic mutations affecting hemoglobin production",
        "treatments": "Blood transfusions, iron chelation therapy, bone marrow transplant",
        "recommendations": "1. Regular blood transfusions if severe\n2. Iron chelation therapy\n3. Genetic counseling\n4. Avoid iron supplements unless prescribed"
    },
    "Leukemia": {
        "description": "Cancer of the body's blood-forming tissues, including bone marrow and lymphatic system.",
        "symptoms": "Fever/chills, persistent fatigue, frequent infections, easy bruising/bleeding, bone pain",
        "causes": "Genetic factors, radiation exposure, certain chemicals",
        "treatments": "Chemotherapy, radiation therapy, targeted therapy, bone marrow transplant",
        "recommendations": "1. Immediate hematologist consultation\n2. Bone marrow biopsy\n3. Begin treatment plan\n4. Regular monitoring"
    }
}

# Function to preprocess input data
def preprocess_input(data):
    numerical_data = {}
    for key, value in data.items():
        try:
            numerical_data[key] = float(value)
        except ValueError:
            numerical_data[key] = 0.0
    
    feature_mapping = {
        'HGB': 'Hemoglobin.1',
    }
    
    feature_vector = []
    for feature in features:
        input_name = feature_mapping.get(feature, feature)
        feature_vector.append(numerical_data.get(input_name, 0.0))
    
    feature_vector = np.array(feature_vector).reshape(1, -1)
    scaled_features = scaler.transform(feature_vector)
    
    return scaled_features

def determine_blood_disease(prediction_probs):
    predicted_class_idx = np.argmax(prediction_probs)
    predicted_class = label_encoder.inverse_transform([predicted_class_idx])[0]
    confidence = prediction_probs[0][predicted_class_idx] * 100
    
    # Get disease info from our English dictionary
    diagnosis_info = disease_info.get(predicted_class, {
        "description": f"The analysis suggests {predicted_class}.",
        "recommendations": "Please consult a hematologist for specific recommendations.",
        "treatments": "Treatment should be determined by a qualified physician."
    })
    
    return {
        "type": predicted_class,
        "description": f"The analysis suggests {predicted_class} with {confidence:.1f}% confidence.",
        "recommendations": diagnosis_info["recommendations"],
        "treatments": diagnosis_info["treatments"],
        "confidence": confidence,
        "disease_details": diagnosis_info
    }

def create_relationship_diagrams(age, gender, symptoms, blood_values, diagnosis):
    diagrams = []
    
    # Age vs Disease Risk
    fig1, ax1 = plt.subplots(figsize=(6, 4))
    age_categories = ["<18", "18-40", "40-60", ">60"]
    age_risk_values = [0.2, 0.5, 0.8, 0.9]
    ax1.bar(age_categories, age_risk_values, color=['green', 'yellow', 'orange', 'red'])
    ax1.set_title("Age vs Blood Disease Risk")
    ax1.set_ylabel("Relative Risk")
    diagrams.append(fig1)
    
    # Gender Distribution
    fig2, ax2 = plt.subplots(figsize=(6, 4))
    gender_labels = ['Male', 'Female']
    gender_values = [0.6, 0.4] if gender == "Male" else [0.4, 0.6]
    ax2.pie(gender_values, labels=gender_labels, autopct='%1.1f%%', colors=['lightblue', 'pink'])
    ax2.set_title("Gender Distribution for Similar Cases")
    diagrams.append(fig2)
    
    # Symptoms Analysis
    fig3, ax3 = plt.subplots(figsize=(6, 4))
    symptom_list = [s.strip().lower() for s in symptoms.split(',')]
    common_symptoms = ['fatigue', 'weakness', 'fever', 'bruising', 'bleeding']
    symptom_counts = [1 if s in symptom_list else 0 for s in common_symptoms]
    ax3.bar(common_symptoms, symptom_counts, color=['red' if c else 'green' for c in symptom_counts])
    ax3.set_title("Reported Symptoms Analysis")
    ax3.set_ylabel("Present (1) / Absent (0)")
    diagrams.append(fig3)
    
    # Blood Values Comparison
    fig4, ax4 = plt.subplots(figsize=(10, 6))
    blood_tests = ['WBC', 'RBC', 'HGB', 'HCT', 'MCV', 'MCH', 'MCHC', 'PLT']
    normal_ranges = {
        'WBC': (4.5, 11.0),
        'RBC': (4.5, 5.9),
        'HGB': (13.5, 17.5),
        'HCT': (38.8, 50.0),
        'MCV': (80, 100),
        'MCH': (27, 33),
        'MCHC': (32, 36),
        'PLT': (150, 450)
    }
    
    patient_values = [blood_values.get(test, 0) for test in blood_tests]
    lower_bounds = [normal_ranges[test][0] for test in blood_tests]
    upper_bounds = [normal_ranges[test][1] for test in blood_tests]
    
    x = range(len(blood_tests))
    ax4.plot(x, patient_values, 'bo-', label='Patient Values')
    ax4.plot(x, lower_bounds, 'g--', label='Normal Lower')
    ax4.plot(x, upper_bounds, 'r--', label='Normal Upper')
    ax4.set_xticks(x)
    ax4.set_xticklabels(blood_tests, rotation=45)
    ax4.set_title("Blood Test Values vs Normal Ranges")
    ax4.set_ylabel("Value")
    ax4.legend()
    diagrams.append(fig4)
    
    # Disease Relationship Diagram
    fig5, ax5 = plt.subplots(figsize=(10, 8))
    components = {
        'Diagnosis': diagnosis,
        'Blood Cells': ['RBC', 'WBC', 'Platelets'],
        'Symptoms': symptom_list[:3] if len(symptom_list) > 3 else symptom_list,
        'Risk Factors': ['Age', 'Gender', 'Genetics'],
        'Treatments': disease_info.get(diagnosis, {}).get('treatments', '').split(', ')[:3]
    }
    
    pos = {
        'Diagnosis': (0.5, 0.8),
        'Blood Cells': (0.2, 0.6),
        'Symptoms': (0.8, 0.6),
        'Risk Factors': (0.2, 0.4),
        'Treatments': (0.8, 0.4)
    }
    
    for node, (x, y) in pos.items():
        ax5.text(x, y, node, ha='center', va='center', 
                bbox=dict(facecolor='lightblue', alpha=0.5, boxstyle='round,pad=0.5'))
        
        if node in components:
            for i, item in enumerate(components[node]):
                offset_x = 0 if node in ['Diagnosis'] else (-0.1 if node in ['Blood Cells', 'Risk Factors'] else 0.1)
                offset_y = -0.05 * (i+1)
                ax5.text(x + offset_x, y + offset_y, f"• {item}", 
                        ha='center' if node == 'Diagnosis' else 'left' if offset_x > 0 else 'right', 
                        va='center', fontsize=9)
    
    connections = [
        ('Diagnosis', 'Blood Cells'),
        ('Diagnosis', 'Symptoms'),
        ('Diagnosis', 'Risk Factors'),
        ('Diagnosis', 'Treatments'),
        ('Blood Cells', 'Risk Factors'),
        ('Symptoms', 'Treatments')
    ]
    
    for start, end in connections:
        start_pos = pos[start]
        end_pos = pos[end]
        con = ConnectionPatch(start_pos, end_pos, "data", "data", 
                             arrowstyle="->", shrinkA=5, shrinkB=5, 
                             mutation_scale=15, fc="gray")
        ax5.add_artist(con)
    
    ax5.set_title(f"Disease Relationship Diagram: {diagnosis}")
    ax5.axis('off')
    diagrams.append(fig5)
    
    return diagrams

def generate_report(input_data, diagnosis_info):
    report = (f"Name: {input_data['name']}\n"
              f"National ID: {input_data['national_id']}\n"
              f"Nationality: {input_data['nationality']}\n"
              f"Age: {input_data['age']}\n"
              f"Mobile Number: {input_data['mobile_number']}\n"
              f"Gender: {input_data['gender']}\n"
              f"Symptoms: {input_data['symptoms']}\n\n"
              f"Blood Test Results:\n"
              f"- White Blood Cells (WBC): {input_data['WBC']} x10³/uL\n"
              f"- Red Blood Cells (RBC): {input_data['RBC']} x10⁶/uL\n"
              f"- Hemoglobin (HGB): {input_data['HGB']} g/dL\n"
              f"- Hematocrit (HCT): {input_data['HCT']} %\n"
              f"- Mean Corpuscular Volume (MCV): {input_data['MCV']} fL\n"
              f"- Mean Corpuscular Hemoglobin (MCH): {input_data['MCH']} pg\n"
              f"- Mean Corpuscular Hemoglobin Concentration (MCHC): {input_data['MCHC']} g/dL\n"
              f"- Platelets (PLT): {input_data['PLT']} x10³/uL\n\n")
    
    report += f"Diagnosis: {diagnosis_info['type']}\n"
    report += f"Confidence: {diagnosis_info['confidence']:.1f}%\n"
    report += f"Description: {diagnosis_info['description']}\n\n"
    
    disease_details = diagnosis_info.get('disease_details', {})
    if disease_details:
        report += "Disease Information:\n"
        report += f"- Description: {disease_details.get('description', 'N/A')}\n"
        report += f"- Common Symptoms: {disease_details.get('symptoms', 'N/A')}\n"
        report += f"- Possible Causes: {disease_details.get('causes', 'N/A')}\n\n"
    
    report += "Recommended Actions:\n"
    report += f"{diagnosis_info['recommendations']}\n\n"
    
    report += "Treatment Options:\n"
    report += f"{diagnosis_info['treatments']}\n\n"
    
    report += "Additional Notes:\n"
    report += "- These results should be interpreted by a qualified hematologist.\n"
    report += "- Abnormal values may require follow-up testing.\n"
    report += "- Some conditions may need bone marrow biopsy for confirmation.\n"
    
    return report

@app.route('/', methods=['GET', 'POST'])
def index():
    if request.method == 'POST':
        input_data = {
            'name': request.form['name'],
            'national_id': request.form['national_id'],
            'nationality': request.form['nationality'],
            'age': request.form['age'],
            'mobile_number': request.form['mobile_number'],
            'gender': request.form['gender'],
            'symptoms': request.form['symptoms'],
            'WBC': request.form['WBC'],
            'RBC': request.form['RBC'],
            'HGB': request.form['HGB'],
            'HCT': request.form['HCT'],
            'MCV': request.form['MCV'],
            'MCH': request.form['MCH'],
            'MCHC': request.form['MCHC'],
            'PLT': request.form['PLT']
        }
        
        processed_data = preprocess_input(input_data)
        prediction_probs = model.predict(processed_data)
        diagnosis_info = determine_blood_disease(prediction_probs)
        
        report = generate_report(input_data, diagnosis_info)
        diagrams = create_relationship_diagrams(
            input_data['age'],
            input_data['gender'],
            input_data['symptoms'],
            {k: float(input_data[k]) for k in ['WBC', 'RBC', 'HGB', 'HCT', 'MCV', 'MCH', 'MCHC', 'PLT']},
            diagnosis_info['type']
        )
        
        diagram_paths = []
        for i, diagram in enumerate(diagrams):
            buf = io.BytesIO()
            diagram.savefig(buf, format='png')
            buf.seek(0)
            path = os.path.join('static', f'diagram_{i}.png')
            with open(path, 'wb') as f:
                f.write(buf.getbuffer())
            diagram_paths.append(path)
            plt.close(diagram)
        
        return render_template('result.html',
                            report=report,
                            diagnosis_info=diagnosis_info,
                            input_data=input_data,
                            diagrams=diagram_paths)
    
    return render_template('index.html')

@app.route('/download_pdf', methods=['POST'])
def download_pdf():
    try:
        report = request.form['report']
        input_data = {
            'name': request.form['name'],
            'national_id': request.form['national_id'],
            'age': request.form['age'],
            'gender': request.form['gender']
        }
        
        pdf = FPDF()
        pdf.add_page()
        
        try:
            pdf.add_font('Arial', '', 'C:/Windows/Fonts/arial.ttf', uni=True)
            pdf.set_font('Arial', '', 12)
        except:
            pdf.add_font('Arial', '', 'arial.ttf', uni=True)
            pdf.set_font('Arial', '', 12)
        
        report = report.replace('μ', 'u').replace('³', '3').replace('²', '2')
        
        # Add report text
        pdf.multi_cell(0, 10, report)
        
        # Add diagrams
        for i in range(5):
            diagram_key = f'diagram_{i}'
            if diagram_key in request.form:
                pdf.add_page()
                pdf.image(request.form[diagram_key], x=10, y=10, w=180)
        
        pdf_path = os.path.join('static', 'blood_report.pdf')
        pdf.output(pdf_path)
        
        return send_file(pdf_path, as_attachment=True)
    
    except Exception as e:
        print(f"Error generating PDF: {str(e)}")
        return f"An error occurred while generating the PDF: {str(e)}", 500

if __name__ == '__main__':
    if not os.path.exists('static'):
        os.makedirs('static')
    app.run(host='0.0.0.0', port=2000)