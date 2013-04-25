#!/bin/bash
echo "Clearing all caches"

rm -f ClassMap_*.php

rm -rf database/*
rm -rf templates/cache/*
rm -rf templates/compile/*

find proxies/ -name '*.php' -print0 | xargs -0 rm -f
find doctrine/filecache/ -name '*.php' -print0 | xargs -0 rm -f
find doctrine/proxies/ -name '*.php' -print0 | xargs -0 rm -f
find doctrine/attributes/ -name '*.php' -print0 | xargs -0 rm -f
