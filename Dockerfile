FROM php:8.3-alpine

RUN mkdir /app
COPY . /app

RUN ["chmod", "+x", "/app/entrypoint.sh"]

ENTRYPOINT ["/app/entrypoint.sh"]