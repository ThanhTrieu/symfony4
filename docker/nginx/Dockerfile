FROM nginx:stable

RUN apt-get update \
    && apt-get install -y nano unzip curl net-tools htop

#COPY nginx.conf /etc/nginx/conf.d/cms.nginx.conf
#ARG php_fpm_link
#ARG domain
#RUN sed -i -- "s/php_fpm_link/$php_fpm_link/g" /etc/nginx/conf.d/cms.nginx.conf
#RUN sed -i -- "s/website_domain_name/$domain/g" /etc/nginx/conf.d/cms.nginx.conf
#EXPOSE 80

#RUN mkdir -p /opt/www/dvg-web/public_html
#RUN mkdir -p /opt/www/dvg-web/logs

#EXPOSE 80
#CMD /etc/init.d/nginx restart