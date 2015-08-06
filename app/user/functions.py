import requests

from app.user.models import User


def user_exists(id):
    user_exists = False
    for user in User.select():
        user_id = "{}{}".format(user.id, user.salt) 
        if user_id == str(id):
            user_exists = True
    return user_exists


def get_constituency(longitude, latitude):
    r = requests.get("http://mapit.mysociety.org/point/4326/{},{}?type=WMC".format(longitude, latitude))
    return r.json()
