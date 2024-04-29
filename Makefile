REGISTRY = henrotaym 
PROJECT = laravel-trustup-io-authentification
TAG = ${REGISTRY}/${PROJECT}:local

build:
	docker build \
		--tag ${TAG} \
		--build-arg UID=$(id -u) \
		--build-arg GID=$(id -g) \
		.

run:
	docker run \
		--volume ${PWD}:/opt/apps/app \
		--user $(id -u):$(id -g) \
		--rm \ 
		--interactive \
		--tty \
		${TAG}