FROM nginx:stable-alpine
COPY docker/nginx/conf.d/app.conf /etc/nginx/conf.d/default.conf
WORKDIR /var/www
COPY public /var/www/public

EXPOSE 80

