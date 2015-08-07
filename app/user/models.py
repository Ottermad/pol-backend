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

    def to_dict(self):
        data = {
            'salt': self.salt,
            'admin': self.admin,
            'signup_time': self.signup_time,
            'constituency': self.constituency
        }
        return data



