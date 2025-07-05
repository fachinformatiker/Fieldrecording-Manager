FROM eriksoderblom/alpine-apache-php

RUN apk -U upgrade
RUN apk add sqlite sqlite-dev

# Set working directory
WORKDIR htdocs

# Copy project files into the container
COPY data/ .

RUN chmod 777 /htdocs

# Expose port 80
EXPOSE 80
