from peewee import *

from app.models import DATABASE
from app.user.models import User

import datetime

class Post(Model):
    title = CharField()
    content = TextField()
    parent = IntegerField()
    timestamp = DateTimeField(default=datetime.datetime.now)
    creator = ForeignKeyField(rel_model=User)
    constituency = CharField(max_length=255)
    upvotes = CharField(default='')

    class Meta:
        database = DATABASE

    @classmethod
    def create_post(cls, title, content, creator, parent=0):
        cls.create(
            title=title,
            content=content,
            parent=parent,
            creator=creator,
            constituency=creator.constituency
        )

    def upvote(self, id):
        self.upvotes = self.upvotes + ',' + id
        self.save()