SELECT roomNumber FROM rooms WHERE id IN (1, 2, 3, 4, 62, 101);
PRAGMA table_info(users);
PRAGMA table_info(rooms);

UPDATE users SET roomID = 2 WHERE id = 317864;
SELECT * FROM users WHERE id = 317864; 

UPDATE users SET roomID = 2 WHERE id = 316449;
SELECT * FROM users WHERE id = 316449;

DELETE FROM users;

DROP TABLE personal_access_tokens;

DELETE FROM users WHERE id = 334859;

PRAGMA table_info(rooms);

UPDATE users SET admin = true WHERE id = 316050;

SELECT * FROM anecdotes WHERE id = 12;

UPDATE rooms SET name = 'Asso 1' WHERE id = 52; 
```Asso 1```

UPDATE rooms SET name = 'Asso 2' WHERE id = 53; 
// Asso 2

DELETE FROM rooms;
DELETE FROM users; 
