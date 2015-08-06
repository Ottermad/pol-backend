from flask import Flask

from .user.views import user

app = Flask()
app.config['DEBUG'] = True

app.register_blueprint(user)