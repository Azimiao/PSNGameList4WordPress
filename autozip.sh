#!/bin/bash

read -p "enter version:" VERSION

if [ ! -d "./Builds/PSN_GameList_4_WordPress/" ];then
  mkdir -p ./Builds/PSN_GameList_4_WordPress/
  else
  echo "ok"
fi

cd ./Builds/PSN_GameList_4_WordPress/
echo "clean up dir"
rm -rf *
echo "copy file"
cp -r ../../assets ./assets
cp ../../PSNGameList.php ./PSNGameList.php
cp ../../README.md ./README.md
cp ../../uninstall.php ./uninstall.php
# builds
cd ../

zip -q -r "PSN_GameList_4_WordPress"${VERSION}.zip ./PSN_GameList_4_WordPress

read -p "Finish~,any key to quit" quitkey
