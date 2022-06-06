<?php
  require_once 'camfunctions.php';
?>

<html>
  <script type="text/javascript" src="jquery.min.js"></script>
  <link rel="stylesheet" href="w3.css">
<head>
<title>picamera MJPEG Streamer</title>
<style>
.dot {
  height: 50px;
  width: 50px;
  background-color: red;
  border-radius: 50%;
  display: inline-block;
}
.pulsing-dot {
  margin:0px;
  display: none;
  width: 35px;
  height: 35px;
  border-radius: 50%;
  background: #f44336;
  cursor: pointer;
  box-shadow: 0 0 0 rgba(255,0,0, 0.4);
  animation: pulse 2s infinite;
}
@-webkit-keyframes pulse {
  0% {
    -webkit-box-shadow: 0 0 0 0 rgba(255,0,0, 0.4);
  }
  70% {
      -webkit-box-shadow: 0 0 0 10px rgba(255,0,0, 0);
  }
  100% {
      -webkit-box-shadow: 0 0 0 0 rgba(255,0,0, 0);
  }
}
@keyframes pulse {
  0% {
    -moz-box-shadow: 0 0 0 0 rgba(255,0,0, 0.4);
    box-shadow: 0 0 0 0 rgba(255,0,0, 0.4);
  }
  70% {
      -moz-box-shadow: 0 0 0 10px rgba(255,0,0, 0);
      box-shadow: 0 0 0 10px rgba(255,0,0, 0);
  }
  100% {
      -moz-box-shadow: 0 0 0 0 rgba(255,0,0, 0);
      box-shadow: 0 0 0 0 rgba(255,0,0, 0);
  }
}
</style>
</head>
<body>
  <div id="emptyShellRecording"></div>
  <h1>PiCamera MJPEG Streamer</h1>
<h3>Actual Config</h3>
<p><b>Stream Resolution:</b> <?=$Streamresolution?> <br>
  <b>Record Resolution:</b> <?=$Recordresolution?><br>
  <b>Camera Rotation:</b> <?=$CameraRotation?>°<br>
  <b>Framerate:</b> <?=$Framerate?><br>
  <b>Sharpness:</b> <?=$Sharpness?><br>
  <b>Saturation:</b> <?=$Saturation?><br>
  <b>Brigthness:</b> <?=$Brigthness?><br>
  <b>Contrast:</b> <?=$Contrast?><br>
  <b>Image Effect:</b> <?=$ImageEffect?><br>
  <b>Video File Destination:</b> <?=$VideoFileDestination?></p>

<!--  <b>Recording Status:</b> <?=$Recordstatus?></p>-->
  <span class="pulsing-dot" id="RecordIconid"></span><br>

  <button id="btnStartCam" onclick="StartCamera()">Start Camera Stream</button>
  <button id="btnStopCam" onclick="StopCamera()">Stop Camera Stream</button><br><br>

  <button id="btnStartRecording" onclick="StartRecording()">Start Recording</button>
  <button id="btnStopRecording" onclick="StopRecording()">Stop Recording</button><br>

  <img src="http://192.168.7.43:8000/stream.mjpg" width="640" height="480" />
  <form onsubmit="event.preventDefault(); SetStreamResolution()">
    <label>Stream resolution</label>
    <select id="streamresolutionid" name="streamresolution">
      <option value="320x240">320x240</option>
      <option value="480x360">480x360</option>
      <option value="640x480">640x480</option>
      <option value="800x600">800x600</option>
      <option value="1024x768">1024x768</option>
      <option value="1280x960">1280x960</option>
      <option value="1600x1200">1600x1200</option>
    </select>
    <input type="submit" value="submit">
  </form>
  <form onsubmit="event.preventDefault(); SetRecordResolution()">
    <label>Record resolution</label>
    <select id="recordresolutionid" name="recordresolution">
      <option value="320x240">320x240</option>
      <option value="480x360">480x360</option>
      <option value="640x480">640x480</option>
      <option value="800x600">800x600</option>
      <option value="1024x768">1024x768</option>
      <option value="1280x960">1280x960</option>
      <option value="1600x1200">1600x1200</option>
    </select>
    <input type="submit" value="submit">
  </form>
  <form onsubmit="event.preventDefault(); SetCameraRotation()">
    <label>Camera Rotation</label>
    <select id="camerarotationid" name="camerarotation">
      <option value="0">0</option>
      <option value="90">90</option>
      <option value="180">180</option>
      <option value="270">270</option>
    </select>
    <input type="submit" value="submit">
  </form>
  <form onsubmit="event.preventDefault(); SetFramerate()">
    <label>Framerate</label>
    <input type="range" value="24" min="2" max="30" id="framerateid" oninput="updateSlider('framerateid', 'slideroutputframerate')">
    <div id="slideroutputframerate">24</div>
    <input type="submit" value="submit" >
  </form>
  <form onsubmit="event.preventDefault(); SetSharpness()">
    <label>Sharpness</label>
    <input type="range" value="0" min="-100" max="100" id="sharpnessid" oninput="updateSlider('sharpnessid', 'slideroutputsharpness')">
    <div id="slideroutputsharpness">0</div>
    <input type="submit" value="submit" >
  </form>
  <form onsubmit="event.preventDefault(); SetBrigthness()">
    <label>Brightness</label>
    <input type="range" value="50" min="0" max="100" id="brightnessid" oninput="updateSlider('brightnessid', 'slideroutputbrightness')">
    <div id="slideroutputbrightness">50</div>
    <input type="submit" value="submit" >
  </form>
  <form onsubmit="event.preventDefault(); SetContrast()">
    <label>Contrast</label>
    <input type="range" value="0" min="-100" max="100" id="contrastid" oninput="updateSlider('contrastid', 'slideroutputcontrast')">
    <div id="slideroutputcontrast">0</div>
    <input type="submit" value="submit" >
  </form>
  <form onsubmit="event.preventDefault(); SetSaturation()">
    <label>Saturation</label>
    <input type="range" value="0" min="-100" max="100" id="saturationid" oninput="updateSlider('saturationid', 'slideroutputsaturation')">
    <div id="slideroutputsaturation">0</div>
    <input type="submit" value="submit" >
  </form>

  <form onsubmit="event.preventDefault(); SetImageEffect()">
    <label>Image Effect</label>
    <select id="imageeffectid" name="imageeffect">
      <option value="none">none</option>
      <option value="negative">negative</option>
      <option value="solarize">solarize</option>
      <option value="sketch">sketch</option>
      <option value="denoise">denoise</option>
      <option value="emboss">emboss</option>
      <option value="oilpaint">oilpaint</option>
      <option value="hatch">hatch</option>
      <option value="gpen">gpen</option>
      <option value="pastel">pastel</option>
      <option value="watercolor">watercolor</option>
      <option value="film">film</option>
      <option value="blur">blur</option>
      <option value="saturation">saturation</option>
      <option value="colorswap">colorswap</option>
      <option value="washedout">washedout</option>
      <option value="posterise">posterise</option>
      <option value="colorpoint">colorpoint</option>
      <option value="colorbalance">colorbalance</option>
      <option value="cartoon">cartoon</option>
      <option value="deinterlace1">deinterlace1</option>
      <option value="deinterlace2">deinterlace2</option>
    </select>
    <input type="submit" value="submit">
  </form>

  <form onsubmit="event.preventDefault(); SetVideoFileDestination()">
    <label>Video File Destination</label>
    <input type="text" id="videofiledestinationid" name="videofiledestination" value=<?=$VideoFileDestination?>>
    <input type="submit" value="submit">
  </form>

  <button id="btnSetDefaultConfig" onclick="SetDefaultConfig()">Set Default Config</button>
</body>
<script>
  function Sleep(milliseconds) {
    return new Promise(resolve => setTimeout(resolve, milliseconds));
  }
  function updateSlider(IDelement, OutputElement){
    let slidervalue = document.getElementById(IDelement).value;
    document.getElementById(OutputElement).innerHTML = slidervalue;
  }
  function displayRecordIcon(){
    var recordicon = document.getElementById("RecordIconid");
    var recordstatus = "<?=$Recordstatus?>";
    if(recordstatus == "active"){
      recordicon.style.display = "block";
    }
    else if(recordstatus == "inactive"){
      recordicon.style.display = "none";
    }
  }

  displayRecordIcon();

  function checkFolderWriteAccess(){
    var isWritable = <?=$isWritable?>;
    if(isWritable == 0){
      var folder = "<?=$VideoFileDestination?>";
      alert("Missing write permissions for the Recording folder: " + folder + " please adjust the permissions.");
    }
  }

  checkFolderWriteAccess();

  function SetDefaultConfig(){
    $.ajax({
      type: "POST",
      url: 'cam.php',
      data: {btnSetDefaultConfig: true},
      dataType: "json",
      success: function(data){},
    });
    alert("Camera Default settings applied");
  }
  function SetStreamResolution(){
    var streamresolution = document.getElementById("streamresolutionid").value;

    $.ajax({
      type: "POST",
      url: 'cam.php',
      data: {btnSetOptions: true, StreamResolution: streamresolution},
      dataType: "json",
      success: function(data){},
    });
    alert("Stream resolution set to: " + streamresolution);
  }
  function SetRecordResolution(){
    var recordresolution = document.getElementById("recordresolutionid").value;

    $.ajax({
      type: "POST",
      url: 'cam.php',
      data: {btnSetOptions: true, RecordResolution: recordresolution},
      dataType: "json",
      success: function(data){},
    });
    alert("Record resolution set to: " + recordresolution);
  }
  async function StartCamera(){
    $.ajax({
    type: "POST",
    url: 'cam.php',
    data: {btnStartCamera: true, },
    dataType: "json",
    success: function(data){},
    });
    await Sleep(1000);
    location.reload()
  }
  async function StopCamera(){
    $.ajax({
    type: "POST",
    url: 'cam.php',
    data: {btnStopCamera: true, },
    dataType: "json",
    success: function(data){},
    });
    await Sleep(1000);
    location.reload()
  }
  async function StartRecording(){
    // Stop a camera stream no matter if its running or not
    $.ajax({
    type: "POST",
    url: 'cam.php',
    data: {btnStopCamera: true, },
    dataType: "json",
    success: function(data){},
    });

    $.ajax({
    type: "POST",
    url: 'cam.php',
    data: {btnStartRecording: true, },
    dataType: "json",
    success: function(data){},
    });
    await Sleep(1000);
    location.reload()
  }
  async function StopRecording(){
    $.ajax({
    type: "POST",
    url: 'cam.php',
    data: {btnStopRecording: true, },
    dataType: "json",
    success: function(data){},
    });
    await Sleep(1000);
    location.reload()
  }
  function SetCameraRotation(){
    let camerarotation = document.getElementById("camerarotationid").value;
    $.ajax({
      type: "POST",
      url: 'cam.php',
      data: {btnSetOptions: true, CameraRotation: camerarotation},
      dataType: "json",
      success: function(data){},
    });
    alert("Camera Rotation set to: " + camerarotation + "°");
  }
  function SetFramerate(){
    let framerate = document.getElementById("framerateid").value;
    $.ajax({
      type: "POST",
      url: 'cam.php',
      data: {btnSetOptions: true, Framerate: framerate},
      dataType: "json",
      success: function(data){},
    });
    alert("Framerate set to: " + framerate);
  }
  function SetSharpness(){
    let sharpness = document.getElementById("sharpnessid").value;
    $.ajax({
      type: "POST",
      url: 'cam.php',
      data: {btnSetOptions: true, Sharpness: sharpness},
      dataType: "json",
      success: function(data){},
    });
    alert("Sharpness set to: " + sharpness);
  }
  function SetSaturation(){
    let saturation = document.getElementById("saturationid").value;
    $.ajax({
      type: "POST",
      url: 'cam.php',
      data: {btnSetOptions: true, Saturation: saturation},
      dataType: "json",
      success: function(data){},
    });
    alert("Saturation set to: " + saturation);
  }
  function SetBrigthness(){
    let brigthness = document.getElementById("brightnessid").value;
    $.ajax({
      type: "POST",
      url: 'cam.php',
      data: {btnSetOptions: true, Brigthness: brigthness},
      dataType: "json",
      success: function(data){},
    });
    alert("Brightness set to: " + brigthness);
  }
  function SetContrast(){
    let contrast = document.getElementById("contrastid").value;
    $.ajax({
      type: "POST",
      url: 'cam.php',
      data: {btnSetOptions: true, Contrast: contrast},
      dataType: "json",
      success: function(data){},
    });
    alert("Contrast set to: " + contrast);
  }
  function SetImageEffect(){
    var imageeffect = document.getElementById("imageeffectid").value;

    $.ajax({
      type: "POST",
      url: 'cam.php',
      data: {btnSetOptions: true, ImageEffect: imageeffect},
      dataType: "json",
      success: function(data){},
    });
    alert("Image effect set to: " + imageeffect);
  }
  function SetVideoFileDestination(){
    var videofiledestination = document.getElementById("videofiledestinationid").value;

    $.ajax({
      type: "POST",
      url: 'cam.php',
      data: {btnSetOptions: true, VideoFileDestination: videofiledestination},
      dataType: "json",
      success: function(data){},
    });
    alert("Video File Destination set to: " + videofiledestination);
  }
</script>
</html>
