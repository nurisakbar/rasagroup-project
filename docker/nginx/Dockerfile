FROM node:22 AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM nginx:stable-alpine
WORKDIR /var/www
COPY public /var/www/public
COPY --from=frontend /app/public/build /var/www/public/build

# Copy custom Nginx configuration
RUN rm -f /etc/nginx/conf.d/default.conf
COPY docker/nginx/conf.d /etc/nginx/conf.d/

EXPOSE 18080
