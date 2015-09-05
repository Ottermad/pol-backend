from flask import Blueprint, jsonify

from .models import Post
from app.user.models import User

post = Blueprint('post', __name__, url_prefix='/post')


@post.route('/new/<title>/<content>/<id>', defaults={'parent': 0})
@post.route('/new/<title>/<content>/<id>/<parent>')
def new(title, content, id, parent):
    creator = User.from_id(id)
    new_post = Post.create_post(
        title=title,
        content=content,
        creator=creator,
        parent=parent,
    )
    return jsonify({'success': True})