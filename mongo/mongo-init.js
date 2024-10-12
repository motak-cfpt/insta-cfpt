db.createUser({
  user: 'user',
  pwd: 'user',
  roles: [
    {
      role: 'readWrite',
      db: 'instaCfpt',
    },
  ],
})

db.User.insert({"_id": "65aa4b7233e6098bd0650e84", "name": "Alice"})
db.User.insert({"_id": "65aa4b7233e6098bd0650e85", "name": "Bob"})
db.User.insert({"_id": "65aa4b7233e6098bd0650e86", "name": "Charly"})

db.Publication.insert({"title": "Incroyable artiste!", "author": "65aa4b7233e6098bd0650e84", "image": "/frog.png"})
db.Publication.insert({"title": "Gouuugouuu", "author": "65aa4b7233e6098bd0650e84", "image": "/fish.png"})
db.Publication.insert({"title": "Descends que je t'attrape !", "author": "65aa4b7233e6098bd0650e85", "image": "/kangaroo.png"})
db.Publication.insert({"title": "Ninja !", "author": "65aa4b7233e6098bd0650e86", "image": "/racoon.png"})
