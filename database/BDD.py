import uuid
import random
import sqlite3
from faker import Faker

# Initialiser Faker
faker = Faker()

# Chemin vers la base de données SQLite
DB_PATH = "./database/database.sqlite"

# Connexion à SQLite
try:
    connection = sqlite3.connect(DB_PATH)
    cursor = connection.cursor()
    print("Connexion réussie à la base SQLite.")
except Exception as e:
    print(f"Erreur de connexion : {e}")
    exit()

# Seeder pour users
def seed_users(nb_users=10):
    print(f"Ajout de {nb_users} utilisateurs...")
    users = []
    for _ in range(nb_users):
        user_id = str(uuid.uuid4())
        cas = faker.unique.user_name()
        first_name = faker.first_name()
        last_name = faker.last_name()
        email = faker.unique.email()
        password = faker.password() if random.choice([True, False]) else None
        location = faker.city() if random.choice([True, False]) else None
        admin = random.choice([True, False])
        users.append((user_id, cas, first_name, last_name, email, password, location, admin))
    cursor.executemany("""
        INSERT INTO users (id, cas, firstName, lastName, email, password, location, admin, roomId)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)
    """, users)
    connection.commit()
    print(f"{nb_users} utilisateurs ajoutés avec succès.")
    return [user[0] for user in users]  # Retourne les IDs des utilisateurs

# Seeder pour rooms
def seed_rooms(nb_rooms=10, user_ids=None):
    print(f"Ajout de {nb_rooms} salles...")
    if not user_ids:
        print("Erreur : Aucun utilisateur pour associer les rooms.")
        return []
    rooms = []
    room_numbers = set()
    for _ in range(nb_rooms):
        room_id = str(uuid.uuid4())
        name = faker.unique.company()
        room_number = random.randint(1, 100)
        while room_number in room_numbers:
            room_number = random.randint(1, 100)
        room_numbers.add(room_number)
        capacity = random.choice([4, 6])
        mood = faker.sentence(nb_words=3)
        total_points = random.randint(0, 100)
        resp_room = random.choice(user_ids)
        rooms.append((room_id, name, room_number, capacity, mood, total_points, resp_room))
    cursor.executemany("""
        INSERT INTO rooms (id, name, roomNumber, capacity, mood, totalPoints, respRoom)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    """, rooms)
    connection.commit()
    print(f"{nb_rooms} salles ajoutées avec succès.")
    return [room[0] for room in rooms]  # Retourne les IDs des rooms

# Seeder pour anecdotes
def seed_anecdotes(nb_anecdotes=20, user_ids=None):
    print(f"Ajout de {nb_anecdotes} anecdotes...")
    if not user_ids:
        print("Erreur : Aucun utilisateur pour associer les anecdotes.")
        return []
    anecdotes = []
    for _ in range(nb_anecdotes):
        anecdote_id = str(uuid.uuid4())
        text = faker.paragraph(nb_sentences=3)
        rooom = random.randint(0, 100)
        user_id = random.choice(user_ids)
        anecdotes.append((anecdote_id, text, rooom, user_id))
    cursor.executemany("""
        INSERT INTO anecdotes (id, text, room, userId)
        VALUES (?, ?, ?, ?)
    """, anecdotes)
    connection.commit()
    print(f"{nb_anecdotes} anecdotes ajoutées avec succès.")
    return [anecdote[0] for anecdote in anecdotes]  # Retourne les IDs des anecdotes

# Seeder pour anecdotesLikes
def seed_anecdotes_likes(nb_likes=30, user_ids=None, anecdote_ids=None):
    print(f"Ajout de {nb_likes} likes sur des anecdotes...")
    if not user_ids or not anecdote_ids:
        print("Erreur : Utilisateurs ou anecdotes manquants pour associer les likes.")
        return
    likes = []
    for _ in range(nb_likes):
        user_id = random.choice(user_ids)
        anecdote_id = random.choice(anecdote_ids)
        # Vérifier l'unicité des likes
        cursor.execute(
            "SELECT 1 FROM anecdotes_likes WHERE user_id = ? AND anecdote_id = ?",
            (user_id, anecdote_id)
        )
        if not cursor.fetchone():
            likes.append((user_id, anecdote_id))
    cursor.executemany("""
        INSERT INTO anecdotes_likes (user_id, anecdote_id)
        VALUES (?, ?)
    """, likes)
    connection.commit()
    print(f"{len(likes)} likes ajoutés avec succès.")

# Exécution globale
try:
    # Peupler les utilisateurs
    user_ids = seed_users(10)

    # Peupler les rooms
    room_ids = seed_rooms(5, user_ids)

    # Peupler les anecdotes
    anecdote_ids = seed_anecdotes(20, user_ids)

    # Peupler les likes d'anecdotes
    seed_anecdotes_likes(30, user_ids, anecdote_ids)

except Exception as e:
    print(f"Erreur lors du seed : {e}")
finally:
    cursor.close()
    connection.close()
    print("Connexion fermée.")
