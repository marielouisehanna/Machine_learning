from flask import Flask, request, jsonify
from flask_cors import CORS
import tensorflow as tf
from transformers import TFBertModel, BertTokenizerFast, BertConfig
import numpy as np
import re
import emoji
import contractions

app = Flask(__name__)
CORS(app)

# Define the GE_taxonomy
GE_taxonomy = ['admiration', 'amusement', 'anger', 'annoyance', 'approval', 'caring', 
               'confusion', 'curiosity', 'desire', 'disappointment', 'disapproval', 
               'disgust', 'embarrassment', 'excitement', 'fear', 'gratitude', 'grief', 
               'joy', 'love', 'nervousness', 'optimism', 'pride', 'realization', 
               'relief', 'remorse', 'sadness', 'surprise']

# Maximum text length
max_length = 48

# Preprocessing function
def preprocess_corpus(x):
    x = re.sub(r'([a-zA-Z\[\]])([,;.!?])', r'\1 \2', x)
    x = re.sub(r'([,;.!?])([a-zA-Z\[\]])', r'\1 \2', x)
    x = emoji.demojize(x)
    x = contractions.fix(x)
    x = x.lower()

    # Add acronyms/typos/abbreviations replacements
    replacements = {
        "lmao": "laughing my ass off", "amirite": "am i right", "tho": "though", "ikr": "i know right",
        "ya": "you", "u": "you", "eu": "europe", "da": "the", "dat": "that", "dats": "that is",
        "cuz": "because", "fkn": "fucking", "tbh": "to be honest", "tbf": "to be fair", "faux pas": "mistake",
        "btw": "by the way", "bs": "bullshit", "kinda": "kind of", "bruh": "bro", "w/e": "whatever",
        "w/": "with", "w/o": "without", "doj": "department of justice"
    }
    for typo, correct in replacements.items():
        x = re.sub(rf"\b{typo}\b", correct, x)

    # Handling repetition and emoji mappings
    x = re.sub(r"<3", " love ", x)
    x = re.sub(r"xd", " smiling_face_with_open_mouth_and_tightly_closed_eyes ", x)
    x = re.sub(r":\)", " smiling_face ", x)
    
    x = re.sub(r"[^A-Za-z!?_]+", " ", x)  # Remove non-alphabetic characters
    x = re.sub(r" +", " ", x)  # Remove extra spaces
    x = x.strip()
    
    return x

# From probabilities to labels
def proba_to_labels(y_pred_proba, threshold=0.5):
    y_pred_labels = np.zeros_like(y_pred_proba)
    for i in range(y_pred_proba.shape[0]):
        for j in range(y_pred_proba.shape[1]):
            if y_pred_proba[i][j] > threshold:
                y_pred_labels[i][j] = 1
    return y_pred_labels

# Custom loss function
def get_weighted_loss(weights):
    def weighted_loss(y_true, y_pred):
        from tensorflow import keras
        return keras.backend.mean((weights[:, 0] ** (1 - y_true)) * (weights[:, 1] ** (y_true)) *
                                   keras.backend.binary_crossentropy(y_true, y_pred), axis=-1)
    return weighted_loss

# Set up model, tokenizer, and other components
print("Loading BERT model and tokenizer...")
model_name = 'bert-base-uncased'
config = BertConfig.from_pretrained(model_name, output_hidden_states=False)
tokenizer = BertTokenizerFast.from_pretrained(pretrained_model_name_or_path=model_name, config=config)

# Load the model
print("Loading emotion detection model...")
try:
    model = tf.keras.models.load_model('my_model2.h5', custom_objects={
        'TFBertModel': TFBertModel,
        'weighted_loss': get_weighted_loss(np.array([[0.5, 1.5]] * len(GE_taxonomy)))
    })
    print("Model loaded successfully!")
except Exception as e:
    print(f"Error loading model: {e}")
    raise RuntimeError("Failed to load the emotion detection model.")  # Fail the app startup

@app.route('/predict', methods=['POST'])
def predict_emotion():
    data = request.get_json()
    text = data.get('text', '')
    threshold = data.get('threshold', 0.5)
    
    if not text:
        return jsonify({'error': 'No text provided'}), 400

    try:
        processed_text = preprocess_corpus(text)
        text_samples = [processed_text]
        
        samples_token = tokenizer(
            text=text_samples, add_special_tokens=True, max_length=max_length,
            truncation=True, padding='max_length', return_tensors='tf', 
            return_token_type_ids=True, return_attention_mask=True
        )
        
        samples = {
            'input_ids': samples_token['input_ids'],
            'attention_mask': samples_token['attention_mask'],
            'token_type_ids': samples_token['token_type_ids']
        }
        
        prediction_proba = model.predict(samples)
        top_indices = np.argsort(prediction_proba[0])[-2:][::-1]
        
        emotion_results = [
            {'emotion': GE_taxonomy[i], 'confidence': float(prediction_proba[0][i])} 
            for i in top_indices
        ]
        
        return jsonify({'text': text, 'emotions': emotion_results})

    except Exception as e:
        print(f"Error during prediction: {e}")
        return jsonify({'error': 'Prediction failed'}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=False)
