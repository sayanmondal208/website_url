import mysql.connector
import pandas as pd
import re
import numpy as np
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.naive_bayes import MultinomialNB

# Connect to MySQL Database
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="web_scraper"
)
cursor = db.cursor()

# Fetch Website Data from Database
cursor.execute("SELECT words, full_content FROM website_data ORDER BY scraped_at DESC LIMIT 50")
data = cursor.fetchall()
cursor.close()
db.close()

# Convert to DataFrame
df = pd.DataFrame(data, columns=["words", "full_content"])

# Sample Training Data for Business Category Classification
categories = ["E-commerce", "Technology", "Healthcare", "Education", "Finance", "Real Estate"]

train_texts = [
    "buy online shopping sale fashion electronics",
    "software artificial intelligence cloud computing programming",
    "hospital doctor medical health clinic treatment",
    "school university online courses education learning",
    "banking investment finance loan insurance",
    "property house apartment real estate buy rent"
]

# Training Model for Business Category Prediction
vectorizer = TfidfVectorizer(stop_words="english")
X_train = vectorizer.fit_transform(train_texts)
y_train = categories  # Labels for training

model = MultinomialNB()
model.fit(X_train, y_train)

# Function to Predict Business Category
def predict_category(text):
    x_test = vectorizer.transform([text])
    return model.predict(x_test)[0]

# Function to Extract Products/Services using Regex
def extract_services(text):
    words = re.findall(r"\b\w+\b", text.lower())
    common_keywords = set(words) & set(np.concatenate([t.split() for t in train_texts]))
    return ", ".join(common_keywords)

# Analyze Latest Website Data
latest_text = df["words"].iloc[-1] + " " + df["full_content"].iloc[-1]
predicted_category = predict_category(latest_text)
identified_services = extract_services(latest_text)

# Display Results
print(f"📌 Business Category: {predicted_category}")
print(f"🛒 Products/Services: {identified_services}")
