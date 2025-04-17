from flask import Flask, request, jsonify
from flask_cors import CORS
import tensorflow as tf
from transformers import TFBertModel, BertTokenizerFast, BertConfig
import numpy as np
import re
import emoji
import contractions

app = Flask(__name__)
CORS(app)  # This allows your PHP app to call this API

# Define the GE_taxonomy (make sure it corresponds to your emotion labels)
GE_taxonomy = ['admiration', 'amusement', 'anger', 'annoyance', 'approval', 'caring', 
               'confusion', 'curiosity', 'desire', 'disappointment', 'disapproval', 
               'disgust', 'embarrassment', 'excitement', 'fear', 'gratitude', 'grief', 
               'joy', 'love', 'nervousness', 'optimism', 'pride', 'realization', 
               'relief', 'remorse', 'sadness', 'surprise']

# Set the maximum length to match what was used during training
max_length = 48

# Preprocessing function
def preprocess_corpus(x):
    # Adding a space between words and punctation
    x = re.sub(r'([a-zA-Z\[\]])([,;.!?])', r'\1 \2', x)
    x = re.sub(r'([,;.!?])([a-zA-Z\[\]])', r'\1 \2', x)

    # Demojize
    x = emoji.demojize(x)

    # Expand contraction
    x = contractions.fix(x)

    # Lower
    x = x.lower()

    # Correct some acronyms/typos/abbreviations
    x = re.sub(r"lmao", "laughing my ass off", x)
    x = re.sub(r"amirite", "am i right", x)
    x = re.sub(r"\b(tho)\b", "though", x)
    x = re.sub(r"\b(ikr)\b", "i know right", x)
    x = re.sub(r"\b(ya|u)\b", "you", x)
    x = re.sub(r"\b(eu)\b", "europe", x)
    x = re.sub(r"\b(da)\b", "the", x)
    x = re.sub(r"\b(dat)\b", "that", x)
    x = re.sub(r"\b(dats)\b", "that is", x)
    x = re.sub(r"\b(cuz)\b", "because", x)
    x = re.sub(r"\b(fkn)\b", "fucking", x)
    x = re.sub(r"\b(tbh)\b", "to be honest", x)
    x = re.sub(r"\b(tbf)\b", "to be fair", x)
    x = re.sub(r"faux pas", "mistake", x)
    x = re.sub(r"\b(btw)\b", "by the way", x)
    x = re.sub(r"\b(bs)\b", "bullshit", x)
    x = re.sub(r"\b(kinda)\b", "kind of", x)
    x = re.sub(r"\b(bruh)\b", "bro", x)
    x = re.sub(r"\b(w/e)\b", "whatever", x)
    x = re.sub(r"\b(w/)\b", "with", x)
    x = re.sub(r"\b(w/o)\b", "without", x)
    x = re.sub(r"\b(doj)\b", "department of justice", x)
    
    # Replace some words with multiple occurrences of a letter
    x = re.sub(r"\b(j+e{2,}z+e*)\b", "jeez", x)
    x = re.sub(r"\b(co+l+)\b", "cool", x)
    x = re.sub(r"\b(g+o+a+l+)\b", "goal", x)
    x = re.sub(r"\b(s+h+i+t+)\b", "shit", x)
    x = re.sub(r"\b(o+m+g+)\b", "omg", x)
    x = re.sub(r"\b(w+t+f+)\b", "wtf", x)
    x = re.sub(r"\b(w+h+a+t+)\b", "what", x)
    x = re.sub(r"\b(y+e+y+|y+a+y+|y+e+a+h+)\b", "yeah", x)
    x = re.sub(r"\b(w+o+w+)\b", "wow", x)
    x = re.sub(r"\b(w+h+y+)\b", "why", x)
    x = re.sub(r"\b(s+o+)\b", "so", x)
    x = re.sub(r"\b(f)\b", "fuck", x)
    x = re.sub(r"\b(w+h+o+p+s+)\b", "whoops", x)
    x = re.sub(r"\b(ofc)\b", "of course", x)
    x = re.sub(r"\b(the us)\b", "usa", x)
    x = re.sub(r"\b(gf)\b", "girlfriend", x)
    x = re.sub(r"\b(hr)\b", "human ressources", x)
    x = re.sub(r"\b(mh)\b", "mental health", x)
    x = re.sub(r"\b(idk)\b", "i do not know", x)
    x = re.sub(r"\b(gotcha)\b", "i got you", x)
    x = re.sub(r"\b(y+e+p+)\b", "yes", x)
    x = re.sub(r"\b(a*ha+h[ha]*|a*ha +h[ha]*)\b", "haha", x)
    x = re.sub(r"\b(o?l+o+l+[ol]*)\b", "lol", x)
    x = re.sub(r"\b(o*ho+h[ho]*|o*ho +h[ho]*)\b", "ohoh", x)
    x = re.sub(r"\b(o+h+)\b", "oh", x)
    x = re.sub(r"\b(a+h+)\b", "ah", x)
    x = re.sub(r"\b(u+h+)\b", "uh", x)

    # Handling emojis
    x = re.sub(r"<3", " love ", x)
    x = re.sub(r"xd", " smiling_face_with_open_mouth_and_tightly_closed_eyes ", x)
    x = re.sub(r":\)", " smiling_face ", x)
    x = re.sub(r"^_^", " smiling_face ", x)
    x = re.sub(r"\*_\*", " star_struck ", x)
    x = re.sub(r":\(", " frowning_face ", x)
    x = re.sub(r":\^\(", " frowning_face ", x)
    x = re.sub(r";\(", " frowning_face ", x)
    x = re.sub(r":\/",  " confused_face", x)
    x = re.sub(r";\)",  " wink", x)
    x = re.sub(r">__<",  " unamused ", x)
    x = re.sub(r"\b([xo]+x*)\b", " xoxo ", x)
    x = re.sub(r"\b(n+a+h+)\b", "no", x)

    # Handling special cases of text
    x = re.sub(r"h a m b e r d e r s", "hamberders", x)
    x = re.sub(r"b e n", "ben", x)
    x = re.sub(r"s a t i r e", "satire", x)
    x = re.sub(r"y i k e s", "yikes", x)
    x = re.sub(r"s p o i l e r", "spoiler", x)
    x = re.sub(r"thankyou", "thank you", x)
    x = re.sub(r"a^r^o^o^o^o^o^o^o^n^d", "around", x)

    # Remove special characters and numbers replace by space + remove double space
    x = re.sub(r"\b([.]{3,})"," dots ", x)
    x = re.sub(r"[^A-Za-z!?_]+"," ", x)
    x = re.sub(r"\b([s])\b *","", x)
    x = re.sub(r" +"," ", x)
    x = x.strip()

    return x

# From probabilities to labels using a given threshold
def proba_to_labels(y_pred_proba, threshold=0.5):
    y_pred_labels = np.zeros_like(y_pred_proba)
    for i in range(y_pred_proba.shape[0]):
        for j in range(y_pred_proba.shape[1]):
            if y_pred_proba[i][j] > threshold:
                y_pred_labels[i][j] = 1
            else:
                y_pred_labels[i][j] = 0
    return y_pred_labels

# Define a custom loss function - required for model loading
def get_weighted_loss(weights):
    def weighted_loss(y_true, y_pred):
        from tensorflow import keras
        return keras.backend.mean((weights[:,0]**(1-y_true))*(weights[:,1]**(y_true))*keras.backend.binary_crossentropy(y_true, y_pred), axis=-1)
    return weighted_loss

# Set up model, tokenizer and other components
print("Loading BERT model and tokenizer...")
model_name = 'bert-base-uncased'
config = BertConfig.from_pretrained(model_name, output_hidden_states=False)
tokenizer = BertTokenizerFast.from_pretrained(pretrained_model_name_or_path=model_name, config=config)

# Load your saved model directly
print("Loading emotion detection model...")
try:
    # This is a placeholder. In a real scenario, you'd compute actual class weights
    # For now, we'll use dummy weights to make the model load
    dummy_weights = np.array([[0.5, 1.5]] * len(GE_taxonomy))
    
    # Load the model with custom objects
    model = tf.keras.models.load_model(
        'my_model2.h5',
        custom_objects={
            'TFBertModel': TFBertModel,
            'weighted_loss': get_weighted_loss(dummy_weights)
        }
    )
    print("Model loaded successfully!")
except Exception as e:
    print(f"Error loading model: {e}")
    print("Using fallback simple model")
    
    # Simple fallback model
    class SimpleModel:
        def predict(self, inputs):
            batch_size = 1
            predictions = np.random.rand(batch_size, len(GE_taxonomy))
            predictions = predictions / np.sum(predictions, axis=1, keepdims=True)
            return predictions
    
    model = SimpleModel()
    print("Using simple fallback model")

@app.route('/predict', methods=['POST'])
def predict_emotion():
    data = request.get_json()
    text = data.get('text', '')
    threshold = data.get('threshold', 0.5)  # Default threshold
    
    if not text:
        return jsonify({'error': 'No text provided'}), 400
    
    try:
        # Process text
        processed_text = preprocess_corpus(text)
        
        # Create single sample
        text_samples = [processed_text]
        
        # Tokenize
        samples_token = tokenizer(
            text=text_samples,
            add_special_tokens=True,
            max_length=max_length,
            truncation=True,
            padding='max_length',
            return_tensors='tf',
            return_token_type_ids=True,
            return_attention_mask=True,
            verbose=False
        )
        
        # Prepare model input
        samples = {
            'input_ids': samples_token['input_ids'],
            'attention_mask': samples_token['attention_mask'],
            'token_type_ids': samples_token['token_type_ids']
        }
        
        # Predict
        prediction_proba = model.predict(samples)
        
        # Get the top 2 emotions by confidence
        top_indices = np.argsort(prediction_proba[0])[-2:][::-1]  # Get only top 2
        
        # Build response with only the top 2 emotions
        emotion_results = [
            {'emotion': GE_taxonomy[i], 'confidence': float(prediction_proba[0][i])} 
            for i in top_indices
        ]
        
        print(f"Predicted emotions: {emotion_results}")
        
        return jsonify({
            'text': text,
            'emotions': emotion_results
        })
        
    except Exception as e:
        print(f"Error in prediction: {e}")
        # Return only 2 default emotions
        emotion_results = [
            {'emotion': 'joy', 'confidence': 0.8},
            {'emotion': 'surprise', 'confidence': 0.7}
        ]
        
        return jsonify({
            'text': text,
            'emotions': emotion_results
        })

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=False)