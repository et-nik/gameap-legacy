#!/usr/bin/env bash

php sprint database refresh app
vendor/bin/codecept --no-interaction run unit

php sprint database refresh app
php sprint database seed UserSeeder

vendor/bin/codecept --no-interaction run acceptance