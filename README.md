# ACL-as-a-Service
 
[![Latest Stable Version](https://poser.pugx.org/vesparny/silex-simple-rest/v/stable.png)](https://packagist.org/packages/vesparny/silex-simple-rest) [![Total Downloads](https://poser.pugx.org/vesparny/silex-simple-rest/downloads.png)](https://packagist.org/packages/vesparny/silex-simple-rest) [![Build Status](https://secure.travis-ci.org/vesparny/silex-simple-rest.png)](http://travis-ci.org/vesparny/silex-simple-rest) [![Dependency Status](https://www.versioneye.com/user/projects/52925eba632bac8d4d0000c1/badge.png)](https://www.versioneye.com/user/projects/52925eba632bac8d4d0000c1)

A simple web service that checks an IP versus a list of IPs and returns
TRUE or FALSE if the requested IP belongs to the list of IPs configured

## Setup

GET     /lists            get the lists of lists
POST    /lists/$name      create a new list and populate it with the BODY
PUT     /lists/$name      replace existing list with the body contents
DELETE  /lists/$name      remove the list

GET     /lists/$name      get the list of IPs present in $list
GET     /lists/$name/$ip  check if $ip is present in list $list
POST    /lists/$name/$ip  add $ip to list $name
DELETE  /lists/$name/$ip  remove $ip from list $name

The PUT and DELETE methods are idempotent methods. The GET method is a safe method (or nullipotent), meaning that calling it produces no side-effects.

Sometimes in the future a users entity should be added and each user should create his own list of IPs




**This project wants to be a starting point to writing scalable and maintainable REST api with Silex PHP micro-framework**

Continuous Integration is provided by [Travis-CI](http://travis-ci.org/).


####How do I run it?
From this folder run the following commands to install the php and bower dependencies, import some data, and run a local php server.

You need at least php **5.4.*** with **SQLite extension** enabled and **Composer**
    
    composer install 
    sqlite3 app.db < resources/sql/schema.sql
    php -S 0:9001 -t web/

You can install the project also as a composer project
		
		composer create-project lorello/acl-as-a-service
    
Your service is now available at http://localhost:9001/api/v1.

####Run tests
Some tests were written, and all CRUD operations are fully tested :)

From the root folder run the following command to run tests.
    
    vendor/bin/phpunit 


####What you will get
The api will respond to

	GET  ->   http://localhost:9001/api/v1/lists
	POST ->   http://localhost:9001/api/v1/lists
	POST ->   http://localhost:9001/api/v1/lists/{id}
	DELETE -> http://localhost:9001/api/v1/lists/{id}

Your request should have 'Content-Type: application/json' header.

Your api is CORS compliant out of the box, so it's capable of cross-domain communication.

Try with curl:
	
	#GET
	curl -s http://localhost:9001/api/v1/lists -H 'Content-Type: application/json' -w "\n"

	#POST (insert)
	curl -s -X POST http://localhost:9001/api/v1/lists -d '{"note":"Hello World!"}' -H 'Content-Type: application/json' -w "\n"

	#POST (update)
	curl -s -X POST http://localhost:9001/api/v1/lists/1 -d '{"note":"Uhauuuuuuu!"}' -H 'Content-Type: application/json' -w "\n"

	#DELETE
	curl -s -X DELETE http://localhost:9001/api/v1/lists/1 -H 'Content-Type: application/json' -w "\n"

####What's under the hood

Under the resources folder you can find a .htaccess file to put the api in production.

####Contributing

Fell free to contribute, fork, pull request, hack. Thanks!

####Author

+	[@lorello](https://twitter.com/lorello)

+	[http://lorello.it](http://lorello.it)

The service is a based on the work of [Alessandro Arnodo](https://twitter.com/vesparny)
from [silex-simple-rest](https://github.com/vesparny/silex-simple-rest)


## License

See LICENSE file.


