all: down install up

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
