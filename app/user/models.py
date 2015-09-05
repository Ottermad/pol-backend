from peewee import *

from app.models import DATABASE

import datetime


class User(Model):
    salt = CharField(max_length=20)
    admin = BooleanField(default=False)
    signup_time = DateTimeField(default=datetime.datetime.now)
    constituency = CharField(max_length=255)

    class Meta:
        database = DATABASE


    @classmethod
    def from_id(cls, id):
        for user in User.select():
            user_id = "{}{}".format(user.id, user.salt)
            if user_id == str(id):
                return user

    def to_dict(self):
        data = {
            'id': "{}{}".format(self.id, self.salt),
            'admin': self.admin,
            'signup_time': self.signup_time,
            'constituency': self.constituency
        }
        return data






