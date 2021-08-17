all: down up install migrate

up:
	docker-compose up --build -d

down:
	docker-compose down

logs:
	docker-compose logs -f

status:
	docker-compose ps

install:
	docker run --rm --interactive --tty \
          --volume $$PWD:/app \
          composer install
migrate:
	docker exec lara sh -c "php artisan migrate"
