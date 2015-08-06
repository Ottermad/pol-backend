from flask import Flask

from .user.views import user

app = Flask(__name__)
app.config['DEBUG'] = True

app.register_blueprint(user)