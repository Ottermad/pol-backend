import requests

def user_exists(id):
	pass

def get_constituency(longitude, latitude):
	r = requests.get("http://mapit.mysociety.org/point/4326/{},{}?type=WMC".format(longitude, latitude))
	return r.json()