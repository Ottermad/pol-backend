from flask import Blueprint

from .functions import user_exists

user = Blueprint('user', __name__, url_prefix='/user')


@user.route("/exists/<id>")
def exists(id):
    user_exists(id)