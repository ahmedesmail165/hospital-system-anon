from flask import Flask, render_template, request, jsonify
import google.generativeai as genai
import os
import logging

# Set up logging
logging.basicConfig(level=logging.DEBUG)
logger = logging.getLogger(__name__)

app = Flask(__name__)

# Configure the API key using environment variable
GOOGLE_API_KEY = os.environ.get("GOOGLE_API_KEY", "YOUR_API_KEY_HERE")
genai.configure(api_key=GOOGLE_API_KEY)

# Initialize the generative model
try:
    model = genai.GenerativeModel("gemini-pro")
    chat = model.start_chat()
    logger.info("Successfully initialized Gemini model")
except Exception as e:
    logger.error(f"Error initializing Gemini model: {str(e)}")
    raise

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/send_message', methods=['POST'])
def send_message():
    try:
        user_input = request.json.get('message', '')
        logger.debug(f"Received message: {user_input}")
        
        if not user_input.strip():
            logger.warning("Received empty message")
            return jsonify({'response': '', 'error': 'Empty message'})
            
        try:
            response = chat.send_message(user_input)
            logger.debug(f"Bot response: {response.text}")
            return jsonify({'response': response.text})
        except Exception as e:
            logger.error(f"Error getting response from Gemini: {str(e)}")
            return jsonify({'response': 'Sorry, I encountered an error. Please try again.', 'error': str(e)})
            
    except Exception as e:
        logger.error(f"Error processing request: {str(e)}")
        return jsonify({'response': 'Sorry, something went wrong. Please try again.', 'error': str(e)})

if __name__ == '__main__':
    app.run(host="127.0.0.1", port=9000, debug=True)
