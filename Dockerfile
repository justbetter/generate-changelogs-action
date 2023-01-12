# Container image that runs your code
FROM php:cli-alpine

# Copies your code file from your action repository to the filesystem path `/` of the container
COPY entrypoint.sh /entrypoint.sh
COPY changelog-generator.php /changelog-generator.php

RUN ["chmod", "+x", "/entrypoint.sh"]
RUN ["chmod", "+x", "/changelog-generator.php"]

# Code file to execute when the docker container starts up (`entrypoint.sh`)
ENTRYPOINT ["/entrypoint.sh"]