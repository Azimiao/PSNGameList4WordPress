#!/bin/bash

read -p "enter version:" VERSION

if [ ! -d "./Builds/PSN_GameList_4_WordPress/" ];then
  mkdir -p ./Builds/PSN_GameList_4_WordPress/
  else
  echo "文件夹已经存在"
fi

cd ./Builds/PSN_GameList_4_WordPress/

rm -rf *

cp -r ../../assets ./assets
cp ../../PSNGameList.php ./PSNGameList.php
cp ../../README.md ./README.md
cp ../../uninstall.php ./uninstall.php
# builds
cd ../

zip -q -r "PSN_GameList_4_WordPress"${VERSION}.zip ./PSN_GameList_4_WordPress

read -p "any key to quit" quitkey
