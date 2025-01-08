from flask import Flask, request, render_template, session
import google.generativeai as genai
import os
from dotenv import load_dotenv
from PIL import Image
from io import BytesIO
import base64
import re

# Load environment variables
load_dotenv()
genai.configure(api_key=os.getenv("GOOGLE_API_KEY"))

# Initialize the Flask app
app = Flask(__name__)
app.secret_key = os.urandom(24)  # For session handling

# Function to clean up the model's response text by removing unwanted characters
def clean_response_text(text):
    # Remove '**' and '*' characters, and extra newlines
    cleaned_text = re.sub(r'(\*+|\*\*+)', '', text) # Remove stars
    # cleaned_text = re.sub(r'\n+', '\n', cleaned_text)  # Normalize multiple newlines
    return cleaned_text

# Function to get a response from the Gemini model
def get_gemini_response(input_prompt, image=None):
    try:
        if not input_prompt:
            raise ValueError("The input prompt cannot be empty.")
        
        model = genai.GenerativeModel('gemini-1.5-flash-8b')

        # Prepare input data with a fallback for image
        request_data = [input_prompt]
        if image:
            request_data.append(image[0])  # Add image if available

        print("Requesting Gemini API with data:", request_data)  # Log the request

        response = model.generate_content(request_data)
        print("Gemini Response:", response)  # Log the response

        # Clean the response before returning
        return clean_response_text(response.text)

    except Exception as e:
        print(f"Error in Gemini API call: {e}")  # Print the full error for debugging
        raise RuntimeError(f"Gemini API call failed: {e}")

# Function to prepare the image for the model
def input_image_setup(uploaded_file):
    if uploaded_file:
        bytes_data = uploaded_file.read()
        image_parts = [
            {
                "mime_type": uploaded_file.content_type,
                "data": bytes_data
            }
        ]
        return image_parts
    else:
        print("No image file uploaded.")
        return None

# Function to encode image to base64 for inline display in HTML
def encode_image_to_base64(uploaded_file):
    img = Image.open(uploaded_file)
    buffered = BytesIO()
    img.save(buffered, format="PNG")  # You can change the format if needed
    img_str = base64.b64encode(buffered.getvalue()).decode("utf-8")
    return img_str

@app.route("/", methods=["GET", "POST"])
def chatbot():
    # Initialize session chat history if not present
    if "chat_history" not in session:
        session["chat_history"] = []

    image_data = None
    if request.method == "POST":
        uploaded_image = request.files.get("image")
        if uploaded_image and uploaded_image.filename:
            try:
                # Prepare image for the model
                image_parts = input_image_setup(uploaded_image)

                # Convert image to base64 for inline display
                image_data = encode_image_to_base64(uploaded_image)

                # Ask the model to describe the image
                input_prompt = """
                You are a healthcare expert. Provide detailed preventive measures that the user can take while dealing with a particular bruise.
                Also, provide the details about the disease or bruise the user is dealing with, along with preventive measures (DO's and DON'Ts).
                Also provide in bullet points.
                """
                prompt = "Tell me about this"

                # Get response from Gemini with an image description
                image_description = get_gemini_response(input_prompt, image_parts)

                # Update chat history with the description
                session["chat_history"].append({"user": prompt, "model": image_description})
                session["image_description"] = image_description  # Store image description in session
                session.modified = True  # Ensure session is saved

            except Exception as e:
                return f"Error processing request: {e}", 500

    return render_template(
        "chatbot.html",
        chat_history=session.get("chat_history", []),
        image_data=image_data,
        image_description=session.get("image_description")
    )

@app.route("/ask", methods=["POST"])
def ask():
    # Initialize session chat history if not present
    if "chat_history" not in session:
        session["chat_history"] = []

    user_input = request.form.get("user_input")
    uploaded_file = request.files.get("image")

    if user_input:
        try:
            # If image is uploaded, process it
            image_parts = input_image_setup(uploaded_file) if uploaded_file else None

            # Use image description for context when asking follow-up questions
            image_description = session.get("image_description", "")

            if image_parts:
                # Ask the model to describe the image if it's new
                model_response = get_gemini_response(user_input + "\n" + image_description, image_parts)
            else:
                # If no image is uploaded, use the image description for context
                model_response = get_gemini_response(user_input + "\n" + image_description)

            # Update chat history with the question and the response
            session["chat_history"].append({"user": user_input, "model": model_response})
            session.modified = True  # Ensure session is saved

        except Exception as e:
            return f"Error processing request: {e}", 500

    return render_template(
        "chatbot.html",
        chat_history=session["chat_history"],
        image_data=None,
        response_text=None,
        image_description=image_description
    )

if __name__ == "__main__":
    app.run(debug=True)