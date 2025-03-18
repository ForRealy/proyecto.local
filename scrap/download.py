import pandas as pd
import requests
import os

# Load the CSV file
csv_file = 'pokemon_images.csv'  # Replace with your CSV file path
df = pd.read_csv(csv_file)

# Create a directory to save the images
output_dir = 'pokemon_images'
if not os.path.exists(output_dir):
    os.makedirs(output_dir)

# Function to download an image from a URL
def download_image(url, filename):
    try:
        response = requests.get(url)
        response.raise_for_status()  # Check if the request was successful
        with open(filename, 'wb') as file:
            file.write(response.content)
        print(f"Downloaded {filename}")
    except requests.exceptions.RequestException as e:
        print(f"Failed to download {filename}: {e}")

# Iterate over each row in the CSV and download the image
for index, row in df.iterrows():
    image_url = row['Image URL']
    image_name = f"{row['Number']}_{row['Name']}.jpg"  # Customize the filename as needed
    image_path = os.path.join(output_dir, image_name)
    
    download_image(image_url, image_path)