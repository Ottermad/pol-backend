import requests
import random

from app.user.models import User

CHARS = [c for c in 'abcdefghijklmnopqrstuvwxyz0123456789./,#']

def user_exists(id):
    user_exists = False
    for user in User.select():
        user_id = "{}{}".format(user.id, user.salt) 
        if user_id == str(id):
            user_exists = True
    return user_exists


def get_constituency(longitude, latitude):
    r = requests.get("http://mapit.mysociety.org/point/4326/{},{}?type=WMC".format(longitude, latitude))
    json_data = r.json()
    return json_data


def generate_salt():
	salt = ''
	length = 20
	for i in range(1, length):
		index = random.randint(0, len(CHARS)-1)
		salt += CHARS[index]
	return salt
