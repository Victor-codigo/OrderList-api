FROM nginx:latest

# Update dependencies
RUN apt-get update

COPY ./default.conf.template /etc/nginx/templates/default.conf.template

COPY ./includes/ /etc/nginx/includes/

# COPY ./ssl/ /etc/ssl/certs/nginx/

COPY ./backend-not-found.html /var/www/html/backend-not-found.html