# Определяем аргументы
ARG APP_CODE_PATH_CONTAINER

FROM composer:latest

WORKDIR ${APP_CODE_PATH_CONTAINER}

ENTRYPOINT ["composer", "--ignore-platform-reqs"]
