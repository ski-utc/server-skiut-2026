import sqlite3
import random
import json
from datetime import datetime

# Connexion à la base de données SQLite
db_path = "./database/database.sqlite"
conn = sqlite3.connect(db_path)
cursor = conn.cursor()

#cursor.execute("insert into users(id, cas, firstName, lastName, email, roomID, admin, location, alumniOrExte) values(1,'mdelmaer','Mathis', 'Delmaere','mathis.delmaere@etu.utc.fr',1,true, 'hein', false)")

cursor.execute("delete from skinder_likes where 1=1")

cursor.execute("insert into skinder_likes (id, room_likeur, room_liked) values (1, 2, 1),(2,4,1),(3,6,1),(4,8,1),(5,10,1)")

cursor.execute("update rooms set photoPath='storage/roomImages/imageDefault.jpg' where id!=1")



# Sauvegarder les changements et fermer la connexion
conn.commit()
conn.close()

print(f"{num_rooms_to_generate} chambres ont été insérées dans la base de données.")
