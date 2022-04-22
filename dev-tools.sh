#!/bin/bash
set -e

# targets
#=========================================
allowed_targets=(
  'build'
  'bash'
  'build_dev'
  'provision'
  'up'
  'stop'
  'down'
  'clear_cache'
  'test'
  'help'
  'logs'
)

script_name="$(cd $(dirname "$0"); pwd -P)/$(basename "$0")"
script_dir="$(dirname "$script_name")"

# @internal, this must be called before we setup the base variables
parse_env() {
  if [ ! -f "${script_dir}/.env" ]; then
    echo -e "\n\nFile ${script_dir}/.env not found, the file: ${script_dir}/.env.local will be copied for you\n\n"
    cp ${script_dir}/.env.local ${script_dir}/.env
  fi
  set -a
  . "${script_dir}/.env"
  set +a

  if [ -z "$UID" ]; then
    echo >&2
    echo "IMPORTANT: the env var UID is not set, it is important to set it inside ${script_dir}/.env" >&2
    echo >&2
  fi
}

parse_env

# base variables
#=========================================

docker_compose_base_file=$script_dir/docker/docker-compose.yml
docker_compose_cmd="docker-compose -f $docker_compose_base_file"

mongo_container_name=${MONGODB_CONTAINER_NAME:-mongodb}
php_fpm_container_name=${PHP_FPM_CONTAINER_NAME:-php-fpm}


build() {
  sh -c "UID=$UID $docker_compose_cmd build"
}

bash() {
  if [ -z "$2" ]; then
  set -x
  $docker_compose_cmd exec "$php_fpm_container_name" bash
  set +x
  else
    set +e
    container="$2"
    shift
    shift
    set -x
    $docker_compose_cmd exec "$container" bash "$@"
    rv=$?
    set +x
    if [ $rv -ne 0 ]; then
      echo "available options: nginx, php-fpm" >&2
    fi
  fi
}

build_dev() {
  set -x
  sh -c "UID=$UID $docker_compose_cmd up -d $php_fpm_container_name"
  $docker_compose_cmd exec -T "$php_fpm_container_name" composer install
  set +x
  wait
}

provision() {
  build_dev
  up
  wait
}

up() {
  if [ ! -d "$script_dir/vendor" ]; then
    echo "You need to run the 'provision' target first" >&2
    exit 1
  fi
  sh -c "UID=$UID $docker_compose_cmd up -d"

  clear_cache
  if [ "$($docker_compose_cmd exec -T ${mongo_container_name} mongo app_db --quiet --eval 'db.sins.count()' | sed 's/[\r\n ]*$//g')" = "0" ]; then
    set -x
    $docker_compose_cmd exec -T "$php_fpm_container_name" vendor/bin/laminas app:fill-sins -c9000
    set +x
    echo "Local development server is up and running: http://localhost:8013/"
 fi
}

stop() {
  echo -e "just stopping the containers\n"
  $docker_compose_cmd stop
}

down() {
  echo -e "stopping the containers and removing them along with the network\n"
  $docker_compose_cmd down
}


clear_cache() {
  $docker_compose_cmd exec -T "$php_fpm_container_name" bash -c "rm -f /project/data/cache/*"
}


test() {
  $docker_compose_cmd exec -T "$php_fpm_container_name" php -d "xdebug.mode=coverage" ./vendor/bin/phpunit  --coverage-html tests/coverage-html --coverage-text
}

logs() {
  $docker_compose_cmd logs -f
}

#=========================================
#environment specific functions
#=========================================
os_init() {
  Linux() { :; }
  MINGW() {
    docker() {
      winpty docker "$@"
    }
    docker-compose() {
      winpty docker-compose "$@"
    }
  }

  unameOut="$(uname -s)"
  case "${unameOut}" in
  Linux*)
    machine=Linux
    Linux
    ;;
  Darwin*) machine=Mac ;;
  CYGWIN*) machine=Cygwin ;;
  MINGW*)
    machine=MinGw
    MINGW
    ;;
  *) machine="UNKNOWN:${unameOut}" ;;
  esac
}

help() {
  base64 -d <<<"H4sIAAAAAAAAA1OQjjawNrQ2NrG2NMl9tLD90aIdQJFcBRThxf2PFnZiCi9sB4uhCfY9WjARi9q+
R4s2woWNQSLbHy3ahyKCsEYBLrZo56MFjaj6YCZxodqwYP6jBbsxLV6w/NGCJdiEYeYhyYK5ix8t
mIIhMgdVpP3Rgh2oInBboE43z4UpQfBh5nABAMeKRB91AQAA" | gunzip
  base64 -d <<<"H4sIAAAAAAAAA5VS2w2EIBD8p4X7oYPlookxlHIkVGAHW7zyWphjNZEPzcy+ZmCttZ+f81+/rH5f
j5jORRwTm0lkE1jKv+QLsFZwO8BvktdgTzI4hTQ1VLsTjAYhAWLEI+JuBptsFYgsQpkMUSjkuUsV
7fzuFKbaaH6T5EcHr8GfphuBCTwhnoTPzHBpkqRcQcDVymTIjBnW5d4OReWpJBbR7Z0nUaO+yGxW
qeTZLikLiltbGNPlXkkhf+vidxlU2eYXBUXoqkziSKV9KTb23TEnLMnZTxMEAAA=" | gunzip
  echo "============================="
  echo "allowed targets: "
  for i in ${allowed_targets[@]}; do
    echo $i
  done
}

_main() {
  os_init "$@"

  #=========================================
  # dynamically calling exposed targets
  #=========================================
  target="$1"
  if [[ ! -z "$target" ]] && [[ "${allowed_targets[@]}" =~ "$target" ]]; then
    $target "$@"
  else
    help "$@"
  fi
}

_main "$@"
