from flask import Flask, request, jsonify
from flask_cors import CORS  # استيراد CORS
from ctransformers import AutoModelForCausalLM
from accelerate import Accelerator

app = Flask(__name__)
CORS(app)  # تمكين CORS لجميع الطلبات
accelerator = Accelerator()

# تحميل النموذج
model = AutoModelForCausalLM.from_pretrained("TheBloke/LLaMA-2-7B-Chat-GGML", model_type="llama")
model = accelerator.prepare(model)


@app.route('/get_response', methods=['POST'])
def get_response():
    user_input = request.json.get("user_input")
    if user_input:
        response = model(user_input)
        return jsonify({"response": response})
    else:
        return jsonify({"error": "No input provided"}), 400

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)
