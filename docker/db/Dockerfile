FROM postgres:15-alpine

ENV POSTGRES_DB=ecoflora_db
ENV POSTGRES_USER=postgres
ENV POSTGRES_PASSWORD=postgres

COPY database/schema.sql /docker-entrypoint-initdb.d/01-schema.sql

EXPOSE 5432
