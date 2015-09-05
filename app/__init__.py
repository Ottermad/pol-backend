from flask import Flask

from .user.views import user
from .post.views import post

app = Flask(__name__)
app.config['DEBUG'] = True

app.register_blueprint(user)
app.register_blueprint(post)