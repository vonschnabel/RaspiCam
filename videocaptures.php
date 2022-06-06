<?php
  require_once 'camfunctions.php';
?>
<html>
  <script type="text/javascript" src="jquery.min.js"></script>
  <link rel="stylesheet" href="w3.css">
<head>
<title>picamera MJPEG Streamer</title>
<style>
.w3-panel w3-card-4{
  display: inline-block;
}

.flex {
  display:flex;
  justify-content:flex-start;
  flex-wrap:wrap;
}

.flex > DIV {
  margin:2px;
  padding:25px;
//  background:#ccc;
  border:1px solid #bbb;
  font-size:14pt;
  color:#666;
}

.trashbin {
  background-color: #FFFFFF;
  border: none;
}
</style>
</head>
<body>
<h1>Captured Videos</h1>
<!--<div class="flex">
  <div class="container">
    <ul class="w3-ul">
      <li>
        <video src="/videofiles/2022-06-02__10-19-09.mp4" width=320  height=240 controls></video>
        <button class="trashbin" onclick="deleteVideoFile()" type="button"><img src="/img/trashbin.png" height ="40" width="40"/></button>
      </li>
    </ul>
  </div>
  <div class="container">
    <ul class="w3-ul">
      <li>
        <video src="/videofiles/2022-06-02__10-19-09.mp4" width=320  height=240 controls></video>
        <button class="trashbin" onclick="deleteVideoFile()" type="button"><img src="/img/trashbin.png" height ="40" width="40"/></button>
      </li>
    </ul>
  </div>
</div>-->

<div id="emptyShellVideoFiles"></div>

<!--<img class="trashbin" src="/img/trashbin.png" height ="40" width="40"/>-->
<!--<button class="trashbin" onclick="deleteVideoFile()">Start Camera Stream</button>-->
</body>
<script>
  function Sleep(milliseconds) {
    return new Promise(resolve => setTimeout(resolve, milliseconds));
  }
  async function getVideoFiles(){
    $.ajax({
      type: "POST",
      url: 'camfunctions.php',
      data: {getVideoFiles: true},
      dataType: "json",
      success: function(data){
        videofiles = data;
      },
    });
    await Sleep(500);

    var elements = document.getElementById("emptyShellVideoFiles");
    while (elements.hasChildNodes()) {
      elements.removeChild(elements.firstChild);
    }

    if(videofiles.length == 0){
       elements.innerHTML = "no Videos available";
    }
    else{
      var DIV_Flex = document.createElement("DIV");
      DIV_Flex.setAttribute("class", "flex");
      DIV_Flex.setAttribute("id", "flexelement");

      for(var i = 0; i < videofiles.length; i++){
        var DIV_Container = document.createElement("DIV");
        DIV_Container.setAttribute("class", "container");
        DIV_Container.setAttribute("id", "id-" + videofiles[i]);
        var UL_w3_ul = document.createElement("UL");
        UL_w3_ul.setAttribute("class", "w3-ul");
        var LI = document.createElement("LI");
        var VIDEO = document.createElement("VIDEO");
        VIDEO.setAttribute("src", "/videofiles/" + videofiles[i]);
        VIDEO.setAttribute("width", "320");
        VIDEO.setAttribute("height", "240");
        VIDEO.setAttribute("controls", "");
        var PARAGRAPH = document.createElement("p");
        var BOLD = document.createElement("b")
        var TEXT = document.createTextNode(videofiles[i]);
        var BUTTON = document.createElement("BUTTON");
        BUTTON.setAttribute("class", "trashbin");
        BUTTON.setAttribute("onclick", "deleteVideoFile(this.id)");
        BUTTON.setAttribute("type", "button");
        BUTTON.setAttribute("ID", videofiles[i]);
        var IMG = document.createElement("IMG");
        IMG.setAttribute("src", "/img/trashbin.png");
        IMG.setAttribute("height", "40");
        IMG.setAttribute("width", "40");

        BOLD.appendChild(TEXT);
        PARAGRAPH.appendChild(BOLD);
        BUTTON.appendChild(IMG);
        LI.appendChild(VIDEO);
        LI.appendChild(BUTTON);
        LI.appendChild(PARAGRAPH);
        UL_w3_ul.appendChild(LI);
        DIV_Container.appendChild(UL_w3_ul);
        DIV_Flex.appendChild(DIV_Container);
      }
      elements.appendChild(DIV_Flex);
    }
  }
  getVideoFiles();

  function deleteVideoFile(buttonid){
    if (confirm("do you really want to delete this video?") == true) {
      var elements = document.getElementById("flexelement");
      var delete_element = document.getElementById("id-" + buttonid);
      elements.removeChild(delete_element);
      $.ajax({
        type: "POST",
        url: 'camfunctions.php',
        data: {btnDeleteVideoFile: true, filename: buttonid},
        dataType: "json",
        success: function(data){
          videofiles = data;
        },
      });
    }
  }
</script>
</html>
