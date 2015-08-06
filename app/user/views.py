from flask import Blueprint, jsonify

from .functions import user_exists, get_constituency

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