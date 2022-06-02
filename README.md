# The Storage API

This is my implementation for the  Webdream  test problem 

## Setup

**Download Composer dependencies**

Make sure you have composer and symfony installed, after that install the depencies with:

```
composer install
```

**Set up the Database**

As the Storage data is saved to a DB please start the database server in docker. The database file are saved in the ./data directory.

```
docker-compose up -d
```

Then, create the tables in the database and load some test data so you can try the API in the API platfroms graphic interface!

```
symfony console doctrine:migrations:migrate
symfony console doctrine:migrations:migrate --env=test 
symfony console doctrine:fixtures:load
```

**Start the development web server**


To start the web server, open a terminal, move into the
project, and run:

```
symfony server:start -d
```


Now check out the site at `https://localhost:8000/api`

**Start messenger handler**


## Check the API-s documentation 

As the backend server is made using Api Platform, all the REST API endpoints
are documented and can be tested too accessing `https://localhost:8000/api`
where 

## Run the test 

To run the test use the command:

```
php bin/phpunit
```

