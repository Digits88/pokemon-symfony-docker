FROM nginx:alpine
COPY nginx_dev.conf  /etc/nginx/
COPY nginx_prod.conf  /etc/nginx/
COPY templates /etc/nginx/templates/
WORKDIR "/srv/app"
# RUN echo "upstream php-upstream { server php-fpm:9000; }" > /etc/nginx/conf.d/upstream.conf

EXPOSE 80
EXPOSE 443
