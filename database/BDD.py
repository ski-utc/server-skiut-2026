import sqlite3

conn = sqlite3.connect('./database/database.sqlite')
cursor = conn.cursor()

cursor.execute('''INSERT INTO users (id, name, email) VALUES (1, "Mathis", "mail")''')

conn.commit()

conn.close()