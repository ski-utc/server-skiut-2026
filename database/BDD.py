import sqlite3

# Chemin vers la base de données SQLite
DB_PATH = "./database/database.sqlite"

# Connexion à SQLite

connection = sqlite3.connect(DB_PATH)
cursor = connection.cursor()
cursor.execute("INSERT INTO rooms (id, name, roomNumber, capacity, mood, respRoom) VALUES (1, 'nomChambre', 333, 4, 'cool', 'd2c8a030-1252-11ed-9136-379a47a07441')")
cursor.execute("INSERT INTO rooms_user (id, userId, roomsId) VALUES (1, 'd2c8a030-1252-11ed-9136-379a47a07441', 1)")

connection.commit()
connection.close()