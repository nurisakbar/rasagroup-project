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

EXPOSE 18080
