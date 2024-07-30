# OrderList api
OrderList api is an API REST to manage your shopping lists.
<br>It supports following features:
- **User:** create an account, and manage your own shopping lists.
- **Groups:** You can create, modify, delete your own groups, add users to your groups or join others groups. 
	- **Users:** Add and remove users from your groups.
	- **Grants:** There are two types of users, administrators and users.
	- **Share:** If you want to share your shopping lists with others, friends, parents, etc., add them to your groups. All members of the group, can add, remove, edit, and manage shops list, products and shops.
- **Products:** Add products you buy, and then, match with shops and shopping lists or share them in your groups.
- **Shops:** Add shops where you buy those products. Match products with shops, and set its prices.

# Prerequisites
- Docker.
- Or if you prefer to create your own configuration: 
	- HTTP server, 
	- PHP 8.3, 
	- MySQL 8.0.37 
	- SMTP server

# Stack
- [Docker](https://www.docker.com/)
- [PHP 8.3](https://www.php.net/)
- [PHPUnit 9.6](https://phpunit.de/index.html)
- [Symfony 6.4](https://symfony.com/)
- [Twig 3](https://twig.symfony.com/)
- [MySQL](https://www.mysql.com/)
- SQL

# Tools
- [MySQLWorkbench](https://www.mysql.com/products/workbench/)
- [VSCode](https://code.visualstudio.com/)

# Installation
## Docker

1. [Fork](https://github.com/Victor-codigo/OrderList-api/fork) or clone the repository.
 ```
git clone git@github.com:Victor-codigo/OrderList-api.git
 ```
2. Under folder .docker is all docker configuration.
There is a make file with the following commands:

	1. `make up`               Rise up the containers
	2. `make build-no-cache`   Builds containers without cache
	3. `make build-cache`      Builds containers with cache  
	4. `make down`             Set down containers  
	5. `make start`            Starts containers
	6. `make stop`             Stops containers
	7. `make restart`          Restart containers
	8. `make ps`               List containers
	9. `make logs`             Show logs
	10. `make down-build-up`    Set down, builds and rise up containers
	11. `make build-up`         Builds and rise up containers
	12. `make bash`             Execute bash in php container
	13. `make root`             Execute bash in php container as root

Build and start containers 
```
 make up
```
The following containers will be built up:
- Nginx
- Proxy-server
- PHP
- MySQL
- Composer
- Mail catcher

2. Enter inside php container as developer user:
 ````
make bash
````
3. There is a make file with following commands:
	- `make setup-dev`               Sets the application up for development
	- `make setup-prod`  Sets the application up for production
	
<br>Execute the following command to build API for development:
````
make setup-dev
````
<br>Or build API for production:
````
make setup-prod
````
4. Follow make instructions.
5. Congratulations! You have installed the API correctly
6. You can access api though:
- http://127.0.0.1:8082 to API.
- http://127.0.0.1 to proxy server
- http://127.0.0.1:8082/api/doc API documentation
 
# Without Docker 
1. Install:
- HTTP server, 
- PHP 8.3, 
- MySQL 8.0.37 
- SMTP server

2. Remove folder .docker
3. Follow docker instructions as of point 3
