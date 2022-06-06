#!/usr/bin/env python3
import io
import picamera
import logging
import socketserver
from threading import Condition
from http import server
import datetime as dt ###

## Parse arguments from command line call
#import argparse
#parser = argparse.ArgumentParser(description='start the camera web stream')
#parser.add_argument('-s', action='store', dest='streamresolution', type=str, default='640x480', help='set the stream resolution. Default: 640x480')
#args = parser.parse_args()

## not needed anymore. could be deleted
PAGE="""\
<html>
<head>
<title>picamera MJPEG streaming demo</title>
</head>
<body>
<h1>PiCamera MJPEG Streaming Demo</h1>
<img src="stream.mjpg" width="640" height="480" />
</body>
</html>
"""

with open('/usr/local/bin/webstream.conf') as f:
        lines = f.readlines()
        for i in range(0, len(lines)):
                if(lines[i].find("Streamresolution") != -1):
                        Streamresolution = lines[i].split('Streamresolution = "')
                        Streamresolution = Streamresolution[1]
                        Streamresolution = Streamresolution.strip('\"\n')
                if(lines[i].find("Framerate") != -1):
                        Framerate = lines[i].split('Framerate = "')
                        Framerate = Framerate[1]
                        Framerate = Framerate.strip('\"\n')
                if(lines[i].find("CameraRotation") != -1):
                        CameraRotation = lines[i].split('CameraRotation = "')
                        CameraRotation = CameraRotation[1]
                        CameraRotation = CameraRotation.strip('\"\n')
                if(lines[i].find("Brigthness") != -1):
                        Brigthness = lines[i].split('Brigthness = "')
                        Brigthness = Brigthness[1]
                        Brigthness = Brigthness.strip('\"\n')
                if(lines[i].find("Contrast") != -1):
                        Contrast = lines[i].split('Contrast = "')
                        Contrast = Contrast[1]
                        Contrast = Contrast.strip('\"\n')
                if(lines[i].find("Sharpness") != -1):
                        Sharpness = lines[i].split('Sharpness = "')
                        Sharpness = Sharpness[1]
                        Sharpness = Sharpness.strip('\"\n')
                if(lines[i].find("Saturation") != -1):
                        Saturation = lines[i].split('Saturation = "')
                        Saturation = Saturation[1]
                        Saturation = Saturation.strip('\"\n')
                if(lines[i].find("ImageEffect") != -1):
                        ImageEffect = lines[i].split('ImageEffect = "')
                        ImageEffect = ImageEffect[1]
                        ImageEffect = ImageEffect.strip('\"\n')
        f.close()

class StreamingOutput(object):
    def __init__(self):
        self.frame = None
        self.buffer = io.BytesIO()
        self.condition = Condition()

    def write(self, buf):
        if buf.startswith(b'\xff\xd8'):
            # New frame, copy the existing buffer's content and notify all
            # clients it's available
            self.buffer.truncate()
            with self.condition:
                self.frame = self.buffer.getvalue()
                self.condition.notify_all()
            self.buffer.seek(0)
        return self.buffer.write(buf)

class StreamingHandler(server.BaseHTTPRequestHandler):
    def do_GET(self):
#        if self.path == '/':
#            self.send_response(301)
#            self.send_header('Location', '/index.html')
#            self.end_headers()
        if self.path == '/':
            self.send_response(301)
            self.send_header('Location', '/stream.mjpg')
            self.end_headers()
#        elif self.path == '/index.html':
#            content = PAGE.encode('utf-8')
#            self.send_response(200)
#            self.send_header('Content-Type', 'text/html')
#            self.send_header('Content-Length', len(content))
#            self.end_headers()
#            self.wfile.write(content)
        elif self.path == '/stream.mjpg':
            self.send_response(200)
            self.send_header('Age', 0)
            self.send_header('Cache-Control', 'no-cache, private')
            self.send_header('Pragma', 'no-cache')
            self.send_header('Content-Type', 'multipart/x-mixed-replace; boundary=FRAME')
            self.end_headers()
            try:
                while True:
                    with output.condition:
                        output.condition.wait()
                        frame = output.frame
                    start = dt.datetime.now() ###
                    if dt.datetime.now() > start: ###
                        camera.annotate_text = dt.datetime.now().strftime('%Y-%m-%d %H:%M:%S') ###
                    self.wfile.write(b'--FRAME\r\n')
                    self.send_header('Content-Type', 'image/jpeg')
                    self.send_header('Content-Length', len(frame))
                    self.end_headers()
                    self.wfile.write(frame)
                    self.wfile.write(b'\r\n')
            except Exception as e:
                logging.warning(
                    'Removed streaming client %s: %s',
                    self.client_address, str(e))
        else:
            self.send_error(404)
            self.end_headers()

class StreamingServer(socketserver.ThreadingMixIn, server.HTTPServer):
    allow_reuse_address = True
    daemon_threads = True

with picamera.PiCamera(resolution=Streamresolution, framerate=int(Framerate)) as camera:
    output = StreamingOutput()
    camera.rotation = int(CameraRotation)
    camera.brightness = int(Brigthness)
    camera.contrast = int(Contrast)
    camera.image_effect = ImageEffect
    camera.sharpness = int(Sharpness)
    camera.saturation = int(Saturation)
#    camera.iso = 200
#    camera.exposure_mode =
    camera.annotate_background = picamera.Color('black') ###
    camera.annotate_text_size = 20
    camera.start_recording(output, format='mjpeg')
    try:
        address = ('', 8000)
        server = StreamingServer(address, StreamingHandler)
        server.serve_forever()
    finally:
        camera.stop_recording()
