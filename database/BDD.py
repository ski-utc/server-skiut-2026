import sqlite3
import random
import json
from datetime import datetime

# Connexion à la base de données SQLite
db_path = "./database/database.sqlite"
conn = sqlite3.connect(db_path)
cursor = conn.cursor()

cursor.execute("insert into users(id, cas, firstName, lastName, email, roomID, admin, location, alumniOrExte) values(1,'mdelmaer','Mathis', 'Delmaere','mathis.delmaere@etu.utc.fr',1,true, 'hein', false)")

cursor.execute("insert into skinder_likes (id, room_likeur, room_liked) values (1, 2, 1),(2,4,1),(3,6,1),(4,8,1),(5,10,1)")

cursor.execute("update rooms set photoPath='storage/roomImages/imageDefault.jpg' where id!=1")

# Sauvegarder les changements et fermer la connexion
conn.commit()

# Images aléatoires
images = [
    "https://s.yimg.com/ny/api/res/1.2/a1h8slWY1TVJ4VO8O6IAzw--/YXBwaWQ9aGlnaGxhbmRlcjt3PTY0MDtoPTQ4MA--/https://media.zenfs.com/fr/prisma_media_ca_m_interesse_783/a9bebfb414733b8dede7a6eb52b88de9",
    "https://www.pokepedia.fr/images/thumb/e/e5/Simiabraz_de_Sacha.png/800px-Simiabraz_de_Sacha.png",
    "https://www.gamosaurus.com/wp-content/uploads/Pokemon/Images/Artworks/395.png",
]

# Passions possibles
possible_passions = ["azertyuiopqsdfgj", "azertyuiopqsdfgj", "azertyuiopqsdfgj", "Traveling", "Music", "Sports", "Art", "Coding"]

# Génération de données aléatoires pour les chambres
def generate_random_room_data(room_number):
    capacity = random.choice([4, 6])
    name = f"Room_{room_number}"
    mood = random.choice(["Happy", "Calm", "Energetic", "Cozy"])
    photo_path = random.choice(images)
    description = f"This is the description for {name}.This is the description for This is the descr"
    passions = random.sample(possible_passions, random.randint(0, 6))
    total_points = random.randint(0, 100)
    created_at = updated_at = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    
    return (
        room_number,
        capacity,
        name,
        mood,
        photo_path,
        description,
        json.dumps(passions),
        total_points,
        created_at,
        updated_at,
    )

# Insérer des données dans la table rooms
def insert_random_rooms(num_rooms):
    for room_number in range(1, num_rooms + 1):
        room_data = generate_random_room_data(room_number)
        try:
            cursor.execute("""
                INSERT INTO rooms (
                    roomNumber, capacity, name, mood, photoPath, description, passions, totalPoints, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            """, room_data)
        except sqlite3.IntegrityError as e:
            print(f"Erreur d'insertion pour Room {room_number}: {e}")

# Nombre de chambres à générer
num_rooms_to_generate = 20

# Remplir la base de données
insert_random_rooms(num_rooms_to_generate)

# Sauvegarder les changements et fermer la connexion
conn.commit()
conn.close()

print(f"{num_rooms_to_generate} chambres ont été insérées dans la base de données.")
