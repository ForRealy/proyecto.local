import csv
import mysql.connector
from urllib.parse import urlparse
import os

# Database connection
try:
    db = mysql.connector.connect(
        host="localhost",
        user="usuario",
        password="password",
        database="PokemonDB"
    )
    cursor = db.cursor()
except mysql.connector.Error as err:
    print(f"Database connection error: {err}")
    exit(1)

# CSV file path
csv_file = "pokemon_images.csv"

def insert_pokemon_data():
    try:
        with open(csv_file, mode="r", encoding='utf-8') as file:
            reader = csv.DictReader(file)
            print("Columns:", reader.fieldnames)
            for row in reader:
                # Extract data from CSV
                try:
                    number = int(row["Number"])
                    name = row["Name"].strip()
                    types = [t.strip() for t in row["Type"].split(",")]
                    image_url = row["Image URL"].strip()
                except KeyError as e:
                    print(f"Missing column in CSV: {e}")
                    continue

                # Check if Pokémon already exists
                cursor.execute("SELECT Number FROM Pokemon WHERE Number = %s", (number,))
                if cursor.fetchone():
                    print(f"Pokemon {number} already exists, skipping.")
                    continue

                # Format image path as "Number_Name.jpg" (e.g. "1_Bulbasaur.jpg")
                image_path = f"{number}_{name}.jpg" if image_url else None

                # Insert Pokémon into the Pokemon table
                try:
                    cursor.execute(
                        "INSERT INTO Pokemon (Number, Name, ImagePath) VALUES (%s, %s, %s)",
                        (number, name, image_path)
                    )
                except mysql.connector.IntegrityError as e:
                    print(f"Skipping duplicate Pokémon {number}: {e}")
                    continue

                # Insert types into Types table
                for type_name in types:
                    cursor.execute("INSERT IGNORE INTO Types (TypeName) VALUES (%s)", (type_name,))

                # Link Pokémon to types in PokemonTypes
                for type_name in types:
                    cursor.execute("SELECT TypeID FROM Types WHERE TypeName = %s", (type_name,))
                    type_id = cursor.fetchone()
                    if type_id:
                        cursor.execute(
                            "INSERT IGNORE INTO PokemonTypes (PokemonNumber, TypeID) VALUES (%s, %s)",
                            (number, type_id[0])
                )

        # Update all existing rows to use the "Number_Name.jpg" format
        cursor.execute("SELECT Number, Name FROM Pokemon")
        for (number, name) in cursor.fetchall():
             new_path = f"{number}_{name}.jpg"
             cursor.execute("UPDATE Pokemon SET ImagePath = %s WHERE Number = %s", (new_path, number))
        db.commit()
        print("Data inserted and updated (ImagePath) successfully!")
    except Exception as e:
        print(f"Error: {e}")
        db.rollback()

if __name__ == "__main__":
    insert_pokemon_data()
    cursor.close()
    db.close()