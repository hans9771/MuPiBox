#!/bin/bash
#

sudo service spotifyd restart
sudo sh -c 'su - dietpi -s /usr/bin/pm2 restart server'
sudo sh -c 'su - dietpi -s /usr/bin/pm2 restart spotify-control'
echo "Spotify Services restarted"