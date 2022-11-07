#!/bin/bash

if [ "$EUID" -ne 0 ]; then
    echo "Please run as root"
    exit 1
fi

echo "Install RaspiCam";
echo "";
echo "####################################";
echo "";

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )/RaspiCam"
if [ -d "$DIR" ]; then
  echo "Directory RaspiCam found. Proceeding with installation..."
else
  echo "Directory RaspiAP not found. Cloning from Github..."
  git clone https://github.com/vonschnabel/RaspiCam.git
fi

sudo apt install apache2 php php-mbstring libapache2-mod-php python3-picamera ffmpeg -y

#sudo sed -i 's/Priv/#Priv/g' /lib/systemd/system/apache2.service

sudo mv ./RaspiCam/070-raspicam /etc/sudoers.d/
sudo chown root:root /etc/sudoers.d/070-raspicam
sudo chmod 440 /etc/sudoers.d/070-raspicam

sudo mv ./RaspiCam/webstream_mjpg.service /etc/systemd/system/webstream_mjpg.service
sudo mv ./RaspiCam/webstream_record_mjpg.service /etc/systemd/system/webstream_record_mjpg.service

sudo mv ./RaspiCam/stream_svc.py /usr/local/bin/
sudo mv ./RaspiCam/stream_record_svc.py /usr/local/bin
sudo mv ./RaspiCam/webstream.conf /usr/local/bin
sudo chmod +x /usr/local/bin/stream_svc.py
sudo chmod +x /usr/local/bin/stream_record_svc.py

sudo mv ./RaspiCam/cam.php /var/www/html/
sudo mv ./RaspiCam/camfunctions.php /var/www/html/
sudo mv ./RaspiCam/videocaptures.php /var/www/html/
sudo mv ./RaspiCam/jquery.min.js /var/www/html/
sudo mv ./RaspiCam/w3.css /var/www/html/

rm -rf RaspiCam/
echo "";
echo "Installation complete";
