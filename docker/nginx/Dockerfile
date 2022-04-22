# Dockerfile is maintained by DevOps team
FROM nginx:1.19.10

USER root
ARG UID=root

# Nginx parent image has these two files linked to  /dev/stdout and /dev/stderr by
# default, and we want a log to files!
RUN rm -f /var/log/nginx/access.log /var/log/nginx/error.log && \
    touch /var/log/nginx/access.log /var/log/nginx/error.log && \
    chown $UID:nginx /var/log/nginx/access.log /var/log/nginx/error.log && \
    chmod 664 /var/log/nginx/access.log /var/log/nginx/error.log

# nginx conf files
COPY ./nginx/etc/nginx/conf.d/ /etc/nginx/conf.d/
COPY ./nginx/etc/nginx/nginx.conf /etc/nginx/nginx.conf

WORKDIR /project
