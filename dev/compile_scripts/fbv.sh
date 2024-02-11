#!/bin/bash

cd ~
sudo apt-get install libc6-dev libjpeg-dev libpng-dev make gcc -y
git clone https://github.com/hans9771/fbv.git
cd fbv
sudo ./configure
sudo make
sudo make install