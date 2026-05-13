from flask import Flask, render_template, request, send_file, redirect, url_for
import os
from werkzeug.utils import secure_filename
from PIL import Image
from tensorflow.keras.models import load_model
from tensorflow.keras.preprocessing import image
import numpy as np
from fpdf import FPDF
import matplotlib.pyplot as plt
import io
import cv2

app = Flask(__name__)
app.config['UPLOAD_FOLDER'] = 'uploads'
app.config['ALLOWED_EXTENSIONS'] = {'png', 'jpg', 'jpeg'}

# Load the trained models
type_model = load_model("brain_tumor_detection_model.h5")
size_model = load_model("brain_tumor_model.h5")
segmentation_model = load_model("tumor_detection_model.h5")

# Define mappings for tumor type categories to numeric values
type_mapping = {0: "glioma", 1: "meningioma", 2: "notumor", 3: "pituitary"}

# Define medical guidelines for tumor sizes
size_guidelines = {
    "glioma": (1.0, 3.0),
    "meningioma": (0.5, 2.5),
    "pituitary": (2.0, 4.0)
}

def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in app.config['ALLOWED_EXTENSIONS']

def preprocess_image(image_path):
    img = image.load_img(image_path, target_size=(150, 150))
    img_array = image.img_to_array(img)
    img_array = np.expand_dims(img_array, axis=0)
    img_array /= 255.0
    return img_array

def estimate_tumor_size(tumor_type):
    sizes = {"glioma": 2.3, "meningioma": 1.8, "pituitary": 3.5}
    return sizes.get(tumor_type, 0.0)

def analyze_tumor_size(tumor_type, tumor_size):
    if tumor_type in size_guidelines:
        min_size, max_size = size_guidelines[tumor_type]
        if tumor_size < min_size:
            return "Tumor size is below the expected range. Further examination needed."
        elif tumor_size > max_size:
            return "Tumor size is above the expected range. Immediate action needed."
        else:
            return "Tumor size is within the expected range."
    return "Unknown tumor type."

def generate_visualizations(tumor_type, tumor_size):
    fig, ax = plt.subplots(figsize=(6, 4))

    if tumor_type in size_guidelines:
        min_size, max_size = size_guidelines[tumor_type]
        ax.bar(tumor_type, tumor_size, color='blue', label='Tumor Size')
        ax.axhline(min_size, color='red', linestyle='--', label='Min Size Guideline')
        ax.axhline(max_size, color='green', linestyle='--', label='Max Size Guideline')
    else:
        ax.bar('Unknown', tumor_size, color='blue')

    ax.set_xlabel('Tumor Type')
    ax.set_ylabel('Tumor Size (cm)')
    ax.set_title('Tumor Size Analysis')
    ax.legend()

    buf = io.BytesIO()
    plt.savefig(buf, format='png')
    buf.seek(0)
    plt.close(fig)
    return buf

def calculate_bmi(weight, height):
    height_in_meters = float(height) / 100
    bmi = float(weight) / (height_in_meters ** 2)
    return bmi

def create_relationship_diagrams(age, gender, weight, height, blood_pressure, sugar_measurement):
    diagrams = []
    
    fig1, ax1 = plt.subplots(figsize=(6, 4))
    age_categories = ["<18", "18-65", ">65"]
    age_risk_values = [0, 0, 0]
    
    if int(age) < 18:
        age_risk_values[0] = 1
    elif 18 <= int(age) <= 65:
        age_risk_values[1] = 1
    else:
        age_risk_values[2] = 1
    
    ax1.pie(age_risk_values, labels=age_categories, autopct='%1.1f%%', colors=['lightblue', 'lightgreen', 'lightcoral'])
    ax1.set_title("Age vs Tumor Risk")
    diagrams.append(fig1)
    
    fig2, ax2 = plt.subplots(figsize=(6, 4))
    gender_labels = ['Male', 'Female']
    gender_risk_values = [1 if gender == "Male" else 0]
    gender_risk_percent = [gender_risk_values[0] * 100, (1 - gender_risk_values[0]) * 100]
    ax2.pie(gender_risk_percent, labels=gender_labels, autopct='%1.1f%%', colors=['lightblue', 'pink'])
    ax2.set_title("Gender vs Tumor Risk")
    diagrams.append(fig2)
    
    fig3, ax3 = plt.subplots(figsize=(6, 4))
    bmi = calculate_bmi(weight, height)
    bmi_categories = ['Underweight', 'Normal', 'Overweight', 'Obese']
    bmi_values = [18.5, 24.9, 29.9, 40]  
    bmi_index = next((i for i, v in enumerate(bmi_values) if bmi <= v), len(bmi_values) - 1)
    bmi_risk_values = [0] * len(bmi_categories)
    bmi_risk_values[bmi_index] = 1
    ax3.bar(bmi_categories, bmi_risk_values, color=['lightblue', 'lightgreen', 'orange', 'red'])
    ax3.set_ylim(0, 1)
    ax3.set_title('BMI Analysis')
    ax3.set_ylabel('BMI Category')
    diagrams.append(fig3)
    
    return diagrams

def predict_and_generate_report(image_path, name, national_id, nationality, age, mobile_number, gender, chronic_diseases, blood_speed, blood_oxygen, sugar_measurement, blood_pressure, weight, height):
    img_array = preprocess_image(image_path)
    
    type_prediction = type_model.predict(img_array)
    type_label = type_mapping[np.argmax(type_prediction)]  # نوع الورم
    
    size_prediction = size_model.predict(img_array)
    tumor_size = size_prediction[0][0]  # حجم الورم
    
    size_analysis = analyze_tumor_size(type_label, tumor_size)
    visualization_buf = generate_visualizations(type_label, tumor_size)
    
    report = (f"Name: {name}\nNational ID: {national_id}\nNationality: {nationality}\nAge: {age}\n"
              f"Mobile Number: {mobile_number}\nGender: {gender}\nChronic Diseases: {chronic_diseases}\n"
              f"Blood Speed: {blood_speed}\nBlood Oxygen: {blood_oxygen}\nSugar Measurement: {sugar_measurement}\n"
              f"Blood Pressure: {blood_pressure}\nWeight: {weight}\nHeight: {height}\n\n")
    
    report += f"Tumor Type: {type_label}\n"
    report += f"Tumor Size (cm): {tumor_size:.2f}\n\n"
    report += f"Size Analysis: {size_analysis}\n"
    
    if type_label == "notumor":
        report += f"{name} does not have a brain tumor and does not need any tumor surgery. Treatment from a doctor and discharge from the hospital."
    else:
        report += f"{name} suffers from a brain tumor and needs to see a specialist doctor, and must be admitted to the hospital to undergo some other tests."
    
    report += "\nRelationships between Input Data and Diagnosis:\n"
    
    if int(age) > 65:
        report += "- Age: Patients over 65 years old are at higher risk of brain tumors.\n"
    else:
        report += "- Age: Younger patients have a lower risk of brain tumors.\n"
    
    if gender == "Male":
        report += "- Gender: Males may have a slightly higher risk of brain tumors compared to females.\n"
    else:
        report += "- Gender: Females may have a slightly lower risk of brain tumors compared to males.\n"
    
    if float(weight) > 90 and float(blood_pressure.split('/')[0]) > 140:
        report += "- Weight & Blood Pressure: High weight and high blood pressure may increase the risk of cardiovascular diseases.\n"
    
    bmi = calculate_bmi(weight, height)
    report += f"- BMI: {bmi:.2f} ({'Underweight' if bmi < 18.5 else 'Normal' if bmi < 25 else 'Overweight' if bmi < 30 else 'Obese'})\n"
    
    return report, visualization_buf, type_label, tumor_size  # إرجاع نوع الورم وحجم الورم

def preprocess_segmentation_image(image_path, target_size=(256, 256)):
    image = cv2.imread(image_path)
    if image is None:
        raise ValueError("Image not found. Please check the path.")
    image = cv2.resize(image, target_size)
    image = image / 255.0
    image = np.expand_dims(image, axis=0)
    return image

def postprocess_and_display(original_path, prediction):
    original_image = cv2.imread(original_path, cv2.IMREAD_COLOR)
    prediction = np.squeeze(prediction)
    prediction = (prediction > 0.5).astype(np.uint8)
    prediction = cv2.resize(prediction, (original_image.shape[1], original_image.shape[0]))
    
    highlighted_image = original_image.copy()
    highlighted_image[prediction == 1] = [0, 0, 255]

    plt.figure(figsize=(12, 8))
    plt.subplot(1, 3, 1)
    plt.title("Original Image")
    plt.imshow(cv2.cvtColor(original_image, cv2.COLOR_BGR2RGB))
    plt.axis("off")

    plt.subplot(1, 3, 2)
    plt.title("Predicted Mask")
    plt.imshow(prediction, cmap="gray")
    plt.axis("off")

    plt.subplot(1, 3, 3)
    plt.title("Highlighted Tumor")
    plt.imshow(cv2.cvtColor(highlighted_image, cv2.COLOR_BGR2RGB))
    plt.axis("off")

    figure_path = "temp_segmentation_figure.png"
    plt.savefig(figure_path)
    plt.close()

    postprocess_and_display.figure_path = figure_path

    plt.show()

@app.route('/', methods=['GET', 'POST'])
def index():
    if request.method == 'POST':
        if 'file' not in request.files:
            return redirect(request.url)
        
        file = request.files['file']
        if file.filename == '':
            return redirect(request.url)
        
        if file and allowed_file(file.filename):
            filename = secure_filename(file.filename)
            file_path = os.path.join(app.config['UPLOAD_FOLDER'], filename)
            file.save(file_path)
            
            name = request.form['name']
            national_id = request.form['national_id']
            nationality = request.form['nationality']
            age = request.form['age']
            mobile_number = request.form['mobile_number']
            gender = request.form['gender']
            chronic_diseases = request.form['chronic_diseases']
            blood_speed = request.form['blood_speed']
            blood_oxygen = request.form['blood_oxygen']
            sugar_measurement = request.form['sugar_measurement']
            blood_pressure = request.form['blood_pressure']
            weight = request.form['weight']
            height = request.form['height']
            
            if all([name, national_id, nationality, age, mobile_number, gender, chronic_diseases, blood_speed, blood_oxygen, sugar_measurement, blood_pressure, weight, height]):
                if national_id.isdigit() and age.isdigit() and mobile_number.isdigit() and weight.replace('.', '', 1).isdigit() and height.replace('.', '', 1).isdigit():
                    report, visualization_buf, tumor_type, tumor_size = predict_and_generate_report(
                        file_path, name, national_id, nationality, age, mobile_number, gender,
                        chronic_diseases, blood_speed, blood_oxygen, sugar_measurement, blood_pressure,
                        weight, height)
                    
                    visualization_image_path = "temp_visualization.png"
                    Image.open(visualization_buf).save(visualization_image_path)
                    
                    preprocessed_image = preprocess_segmentation_image(file_path)
                    prediction = segmentation_model.predict(preprocessed_image)
                    postprocess_and_display(file_path, prediction)
                    
                    return render_template('result.html', report=report, visualization_image=visualization_image_path, segmentation_image=postprocess_and_display.figure_path, tumor_type=tumor_type, tumor_size=tumor_size)
                else:
                    return "Please enter valid numbers for National ID, Age, Mobile Number, Weight, and Height."
            else:
                return "Please complete all information."
    
    return render_template('index.html')

@app.route('/save_pdf', methods=['POST'])
def save_pdf():
    report_text = request.form['report']
    visualization_image_path = request.form['visualization_image']
    segmentation_image_path = request.form['segmentation_image']
    
    pdf = FPDF()
    pdf.set_auto_page_break(auto=True, margin=15)
    
    pdf.add_page()
    pdf.set_font("Arial", style='B', size=16)
    pdf.cell(200, 10, "Brain Tumor Detection Report", ln=True, align='C')
    pdf.ln(10)
    
    # البيانات الأساسية
    data = [
        ["Name:", request.form['name']],
        ["National ID:", request.form['national_id']],
        ["Nationality:", request.form['nationality']],
        ["Age:", request.form['age']],
        ["Mobile Number:", request.form['mobile_number']],
        ["Gender:", request.form['gender']],
        ["Chronic Diseases:", request.form['chronic_diseases']],
        ["Blood Speed:", request.form['blood_speed']],
        ["Blood Oxygen:", request.form['blood_oxygen']],
        ["Sugar Measurement:", request.form['sugar_measurement']],
        ["Blood Pressure:", request.form['blood_pressure']],
        ["Weight:", request.form['weight']],
        ["Height:", request.form['height']],
        ["Tumor Type:", request.form['tumor_type']],  # إضافة نوع الورم
        ["Tumor Size (cm):", request.form['tumor_size']]  # إضافة حجم الورم
    ]
    
    col_width = pdf.w / 2.5
    row_height = 10
    for row in data:
        pdf.cell(col_width, row_height, row[0], border=1)
        pdf.cell(col_width, row_height, row[1], border=1)
        pdf.ln(row_height)
    
    if visualization_image_path:
        pdf.add_page()
        pdf.set_font("Arial", style='B', size=16)
        pdf.cell(200, 10, "Visualization", ln=True, align='C')
        pdf.ln(10)
        pdf.image(visualization_image_path, x=10, y=None, w=180)
    
    if segmentation_image_path:
        pdf.add_page()
        pdf.set_font("Arial", style='B', size=16)
        pdf.cell(200, 10, "Tumor Segmentation", ln=True, align='C')
        pdf.ln(10)
        pdf.image(segmentation_image_path, x=10, y=None, w=180)
    
    age = request.form['age']
    gender = request.form['gender']
    weight = request.form['weight']
    height = request.form['height']
    blood_pressure = request.form['blood_pressure']
    sugar_measurement = request.form['sugar_measurement']
    
    diagrams = create_relationship_diagrams(age, gender, weight, height, blood_pressure, sugar_measurement)
    
    for i, diagram in enumerate(diagrams):
        pdf.add_page()
        pdf.set_font("Arial", style='B', size=16)
        pdf.cell(200, 10, f"Diagram {i+1}", ln=True, align='C')
        pdf.ln(10)
        diagram_path = f"temp_diagram_{i}.png"
        diagram.savefig(diagram_path)
        pdf.image(diagram_path, x=10, y=None, w=180)
    
    pdf_output_path = "report.pdf"
    pdf.output(pdf_output_path)
    
    return send_file(pdf_output_path, as_attachment=True)


if __name__ == '__main__':
    if not os.path.exists(app.config['UPLOAD_FOLDER']):
        os.makedirs(app.config['UPLOAD_FOLDER'])
    app.run(host='0.0.0.0', port=1000)