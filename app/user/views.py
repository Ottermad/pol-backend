from flask import Blueprint, jsonify

from .functions import user_exists, get_constituency, generate_salt
from .models import User

user = Blueprint('user', __name__, url_prefix='/user')


@user.route('/exists/<id>')
def exists(id):
    does_user_exist = user_exists(id)
    data = {
        'exists': does_user_exist
    }
    return jsonify


@user.route('/new/<long>/<lat>')
def new(long, lat):
    constituency = get_constituency(long, lat)
    for key in constituency.keys():
        const_id = key
        break
    salt = generate_salt(10)
    new_user = User.create(
        salt=salt,
        constituency=const_id
    )
    return jsonify(new_user.to_dict())

@user.route('/return/<id>')
def return_from_id(id):
    found_user = User.from_id(id)
    return jsonify(found_user.to_dict())
