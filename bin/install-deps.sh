#!/bin/bash -ex

# install bower and bower deps
npm install -g bower
bower install

# install grunt and grunt deps
npm install -g grunt-cli
npm install