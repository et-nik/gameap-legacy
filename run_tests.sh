#!/usr/bin/env bash

php sprint database refresh app
vendor/bin/codecept run unit

php sprint database refresh app
php sprint database seed UserSeeder

vendor/bin/codecept run acceptance