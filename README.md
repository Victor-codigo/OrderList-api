# OrderList api
OrderList api is an API REST to manage your shop lists. Created with PHP,  in Symfony framework
<br>It supports following features:
- **User:** create an account, and manage your own shop lists.
- **Groups:** You can create, modify, delete your own groups, add users to your groups or join others groups.
	- **Users:** Add and remove users from your groups.
	- **Grants:** There are two types of users, administrators and users.
	- **Share:** If you want to share your shop lists with others, friends, parents, etc., add them to your groups. All members of the group, can add, remove, edit, and manage shops list, products and shops.
- **Products:** Add products you buy, and then, match with shops and shop lists or share them in your groups.
- **Shops:** Add shops where you buy those products. Match products with shops, and set its prices.

# Prerequisites
- Install docker.
- Or if you prefer to create your own configuration,  is needed a HTTP server, PHP 8.3, MySQL 8.0.37

# Installation
## Docker
Under folder .docker is all docker configuration.
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
	10.`make down-build-up`    Set down, builds and rise up containers
	11.`make build-up`         Builds and rise up containers
	12.`make bash`             Execute bash in php container
	13.`make root`             Execute bash in php container as root

1. Build and start containers
	- Nginx
	- PHP
	- MySQL
	- Composer
	- Mail catcher


```
 make up
````
2. Enter inside php container as developer user:
 ````
make bash
````

3. Clone the repository with ssh
````
git clone git@github.com:Victor-codigo/OrderList-api.git
````
4. There is a make file with following commands:
	- `make setup-dev`               Sets the application up for development
	- `make setup-prod`  Sets the application up for production
<br>Execute to build application for development:
````
make setup-dev
````
5. Follow the instructions.
6. Congratulations! You have inst