from peewee import *

import datetime

class User(Model):
	admin = BooleanField(default=False)
	signup_time = DateTimeField(default=datetime.datetime.now)
	constituency = CharField(max_length=255)
		
