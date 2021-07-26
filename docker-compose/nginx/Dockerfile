ARG NGINX_VERSION=1.20.1
FROM nginx:${NGINX_VERSION} as nginx_dev

ADD docker-compose/nginx/nginx.dev.conf /etc/nginx/nginx.conf
ADD docker-compose/nginx/symfony.dev.conf /etc/nginx/templates/default.conf.template

FROM nginx:${NGINX_VERSION} as nginx_prod

ADD docker-compose/nginx/nginx.conf /etc/nginx/nginx.conf
ADD docker-compose/nginx/symfony.conf /etc/nginx/templates/default.conf.template
