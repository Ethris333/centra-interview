# Hi

This is solved assignment for Centra recrutation

## Configuration

In order to run this project you need to setup an environment running PHP and Composer.
I am using nginx + php-fpm to serve application. 

Nginx configuration. Replace $root_path with path to public directory (`/src/public/`) in provided archive, and fastcg_pass to your php-fpm location.

    server {
    	listen *:80;
    	server_name localhost;
    
    	index index.php;
    	set $root_path "/home/centra/src/public";
    	root $root_path;
    
    	location ~ \.php {
    		fastcgi_index /index.php;
    		fastcgi_pass unix:/run/php/php7.2-fpm.sock;
    		fastcgi_intercept_errors on;
    		include fastcgi_params;
    		fastcgi_split_path_info ^(.+\.php)(/.*)$;
    		fastcgi_param PATH_INFO $fastcgi_path_info;
    		fastcgi_param PATH_TRANSLATED $document_root$fastcgi_path_info;
    		fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    		fastcgi_param DOCUMENT_ROOT $realpath_root;
    		#fastcgi_param SCRIPT_FILENAME $realpath_root/index.php;
    		fastcgi_param APPLICATION_ENV local;
    	}
    	
    }

Next, in `/src/classes/config/` there should be a `github.ini.example` that should be filled with data from Github. Account username, repositories, client_id and client_secret can be generated in Github > Settings > Developer Settings > Oauth Apps

Then in the root directory use command `composer install` that downloads all the dependencies needed to run this project.

After completing this process you should be able to access your `localhost` and view the application running. By creating issues and milestones on github repository you can check whether the project is working.