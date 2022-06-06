<?php
  if(isset($_POST["btnStartCamera"])) {
    exec('sudo /bin/systemctl start webstream_mjpg');
  }
  if(isset($_POST["btnStopCamera"])) {
    exec('sudo /bin/systemctl stop webstream_mjpg');
  }
  if(isset($_POST["btnStartRecording"])) {
    exec('sudo /bin/systemctl start webstream_record_mjpg');
  }
  if(isset($_POST["btnStopRecording"])) {
    exec('sudo /bin/systemctl stop webstream_record_mjpg');
    $actualConfig = getConfig();
    $Framerate = $actualConfig[2];
    $VideoFileDestination = $actualConfig[9];
    exec('/bin/ls ' . $VideoFileDestination ,$result,$code);
    $filelist =  preg_grep('/\.h264/', $result);
    sort($filelist);
    for($i=0; $i < count($filelist); $i++) {
      $filename = explode('.h264',$filelist[$i]);
      $filename = $filename[0];
      exec('sudo ffmpeg -framerate ' . $Framerate . ' -i ' . $VideoFileDestination . '/' . $filelist[$i]  .  ' -c copy ' . $VideoFileDestination . '/' . $filename . '.mp4');
      unlink($VideoFileDestination . "/" . $filelist[$i]);
    }
  }
  if(isset($_POST["btnSetDefaultConfig"])) {
    $config_file = fopen('/tmp/webstream.conf', 'w') or die("Unable to open file!");

    fwrite($config_file, '[PICAMERA]' . "\n");
    fwrite($config_file, 'Streamresolution = "640x480"' . "\n");
    fwrite($config_file, 'Recordresolution = "640x480"' . "\n");
    fwrite($config_file, 'Framerate = "24"' . "\n");
    fwrite($config_file, 'CameraRotation = "0"' . "\n");
    fwrite($config_file, 'Brigthness = "50"' . "\n");
    fwrite($config_file, 'Contrast = "0"' . "\n");
    fwrite($config_file, 'Sharpness = "0"' . "\n");
    fwrite($config_file, 'Saturation = "0"' . "\n");
    fwrite($config_file, 'ImageEffect = "none"' . "\n");
    fwrite($config_file, 'VideoFileDestination = "/home/pi"' . "\n");

    fclose($config_file);

    exec('sudo /bin/cp /tmp/webstream.conf /usr/local/bin/webstream.conf');
    unlink('/tmp/webstream.conf');
  }

  if(isset($_POST["btnSetOptions"])) {
    $config_file = fopen('/usr/local/bin/webstream.conf', 'r') or die("Unable to open file!");
    $result = array();
    while(! feof($config_file)){
      array_push($result,fgets($config_file));
    }

    $i = 0;
    while($i < count($result)){
      if(strpos($result[$i], "Streamresolution =") !== FALSE and isset($_POST["StreamResolution"])){
        $result[$i] = "Streamresolution = " . '"' . $_POST["StreamResolution"] . '"' . "\n";
      }
      if(strpos($result[$i], "Recordresolution =") !== FALSE and isset($_POST["RecordResolution"])){
        $result[$i] = "Recordresolution = " . '"' . $_POST["RecordResolution"] . '"' . "\n";
      }
      if(strpos($result[$i], "Brigthness =") !== FALSE and isset($_POST["Brigthness"])){
        $result[$i] = "Brigthness = " . '"' . $_POST["Brigthness"] . '"' . "\n";
      }
      if(strpos($result[$i], "Contrast =") !== FALSE and isset($_POST["Contrast"])){
        $result[$i] = "Contrast = " . '"' . $_POST["Contrast"] . '"' . "\n";
      }
      if(strpos($result[$i], "ImageEffect =") !== FALSE and isset($_POST["ImageEffect"])){
        $result[$i] = "ImageEffect = " . '"' . $_POST["ImageEffect"] . '"' . "\n";
      }
      if(strpos($result[$i], "Framerate =") !== FALSE and isset($_POST["Framerate"])){
        $result[$i] = "Framerate = " . '"' . $_POST["Framerate"] . '"' . "\n";
      }
      if(strpos($result[$i], "Sharpness =") !== FALSE and isset($_POST["Sharpness"])){
        $result[$i] = "Sharpness = " . '"' . $_POST["Sharpness"] . '"' . "\n";
      }
      if(strpos($result[$i], "Saturation =") !== FALSE and isset($_POST["Saturation"])){
        $result[$i] = "Saturation = " . '"' . $_POST["Saturation"] . '"' . "\n";
      }
      if(strpos($result[$i], "CameraRotation =") !== FALSE and isset($_POST["CameraRotation"])){
        $result[$i] = "CameraRotation = " . '"' . $_POST["CameraRotation"] . '"' . "\n";
      }
      if(strpos($result[$i], "VideoFileDestination =") !== FALSE and isset($_POST["VideoFileDestination"])){
        $result[$i] = "VideoFileDestination = " . '"' . $_POST["VideoFileDestination"] . '"' . "\n";
      }
      $i++;
    }

    $config_file = fopen('/tmp/webstream.conf', 'w') or die("Unable to open file!");
    $i = 0;
    while($i < count($result)){
      fwrite($config_file, "$result[$i]");
      $i++;
    }

    fclose($config_file);

    exec('sudo /bin/cp /tmp/webstream.conf /usr/local/bin/webstream.conf');
    unlink('/tmp/webstream.conf');

    // if the video file destination is set, adjust the alias config file from apache to get access to the video archive website
    if(isset($_POST["VideoFileDestination"])){
      $VideoFileDestination = $_POST["VideoFileDestination"];
      $aliasfile = fopen('/etc/apache2/mods-available/alias.conf', 'r') or die("Unable to open file!");
      $result = array();
      while(! feof($aliasfile)){
        array_push($result,fgets($aliasfile));
      }
      fclose($aliasfile);

      $i = 0;
      $existingAliasConfig = 0;
      while($i < count($result)){
        if(strpos($result[$i], "Alias /videofiles") !== FALSE ){
          $result[$i] = "\tAlias /videofiles " . '"' . $VideoFileDestination . '"' . "\n";
          if(strpos($result[$i+1], "<Directory") !== FALSE ){
            $result[$i+1] = "\t<Directory " . '"' . $VideoFileDestination . '">' . "\n";
          }
          $existingAliasConfig = 1;
        }
        $i++;
      }
		echo json_encode("status: " . $existingAliasConfig);
      if($existingAliasConfig == 1){
        $aliasfile = fopen('/tmp/alias.conf', 'w') or die("Unable to open file!");
        $i = 0;
        while($i < count($result)){
          fwrite($aliasfile, "$result[$i]");
          $i++;
        }
        fclose($aliasfile);
      }
      elseif($existingAliasConfig == 0){
        $aliasfile = fopen('/tmp/alias.conf', 'w') or die("Unable to open file!");
        $i = 0;
        while($i < count($result)){
          fwrite($aliasfile, "$result[$i]");
          if(strpos($result[$i+1], "</IfModule>") !== FALSE and $i < count($result)){
            fwrite($aliasfile, "\n");
            fwrite($aliasfile, "\tAlias /videofiles " . '"' . $VideoFileDestination . '"' . "\n");
            fwrite($aliasfile, "\t<Directory " . '"' . $VideoFileDestination . '"' . ">\n");
            fwrite($aliasfile, "\t\tRequire all granted\n");
            fwrite($aliasfile, "\t</Directory>\n");
          }
          $i++;
        }
        fclose($aliasfile);
      }
      exec('sudo /bin/cp /tmp/alias.conf /etc/apache2/mods-available/alias.conf');
      unlink('/tmp/alias.conf');
      exec('sudo /bin/systemctl restart apache2');
    }
  }

  if(isset($_POST["getVideoFiles"])){
    $actualConfig = getConfig();
    $Framerate = $actualConfig[2];
    $VideoFileDestination = $actualConfig[9];
    exec('/bin/ls ' . $VideoFileDestination ,$result,$code);
    $filelist =  preg_grep('/\.mp4/', $result);
    sort($filelist);
    echo json_encode($filelist);
  }

  if(isset($_POST["btnDeleteVideoFile"])) {
    $actualConfig = getConfig();
    $VideoFileDestination = $actualConfig[9];
    unlink($VideoFileDestination . "/" . $_POST["filename"]);
  }

  function getConfig() {
    $Recordstatus = exec('sudo /bin/systemctl is-active webstream_record_mjpg.service'); // check if an recording is in progress

    $config_file = fopen('/usr/local/bin/webstream.conf', 'r') or die("Unable to open file!");
    $result = array();
    while(! feof($config_file)){
      array_push($result,fgets($config_file));
    }

    $i = 0;
    while($i < count($result)){
      if(strpos($result[$i], "Streamresolution =") !== FALSE ){
        $Streamresolution = explode('"',$result[$i]);
        $Streamresolution = $Streamresolution[1];
      }
      if(strpos($result[$i], "Recordresolution =") !== FALSE ){
        $Recordresolution = explode('"',$result[$i]);
        $Recordresolution = $Recordresolution[1];
      }
      if(strpos($result[$i], "Framerate =") !== FALSE ){
        $Framerate = explode('"',$result[$i]);
        $Framerate = $Framerate[1];
      }
      if(strpos($result[$i], "CameraRotation =") !== FALSE ){
        $CameraRotation = explode('"',$result[$i]);
        $CameraRotation = $CameraRotation[1];
      }
      if(strpos($result[$i], "Sharpness =") !== FALSE ){
        $Sharpness = explode('"',$result[$i]);
        $Sharpness = $Sharpness[1];
      }
      if(strpos($result[$i], "Saturation =") !== FALSE ){
        $Saturation = explode('"',$result[$i]);
        $Saturation = $Saturation[1];
      }
      if(strpos($result[$i], "Brigthness =") !== FALSE ){
        $Brigthness = explode('"',$result[$i]);
        $Brigthness = $Brigthness[1];
      }
      if(strpos($result[$i], "Contrast =") !== FALSE ){
        $Contrast = explode('"',$result[$i]);
        $Contrast = $Contrast[1];
      }
      if(strpos($result[$i], "ImageEffect =") !== FALSE ){
        $ImageEffect = explode('"',$result[$i]);
        $ImageEffect = $ImageEffect[1];
      }
      if(strpos($result[$i], "VideoFileDestination =") !== FALSE ){
        $VideoFileDestination = explode('"',$result[$i]);
        $VideoFileDestination = $VideoFileDestination[1];
      }
      $i++;
    }

    if (is_dir($VideoFileDestination) && is_writable($VideoFileDestination)){
      $isWritable = 1;
    }
    else{
      $isWritable = 0;
    }

    return array($Streamresolution, $Recordresolution, $Framerate, $CameraRotation, $Sharpness, $Saturation, $Brigthness, $Contrast, $ImageEffect, $VideoFileDestination, $Recordstatus, $isWritable);
  }

$actualConfig = getConfig();
$Streamresolution = $actualConfig[0];
$Recordresolution = $actualConfig[1];
$Framerate = $actualConfig[2];
$CameraRotation = $actualConfig[3];
$Sharpness = $actualConfig[4];
$Saturation = $actualConfig[5];
$Brigthness = $actualConfig[6];
$Contrast = $actualConfig[7];
$ImageEffect = $actualConfig[8];
$VideoFileDestination = $actualConfig[9];
$Recordstatus = $actualConfig[10];
$isWritable = $actualConfig[11];

/*
echo "folder: " . $VideoFileDestination;
echo "<br>";
echo "folder write status: " . $isWritable;
echo "<br>";

//$myDir = '/var/www/html/tmp';
$myDir = '/home/ast';
if (is_dir($myDir) && is_writable($myDir)){
  echo "folder is writeable";
}
else{
  echo "folder is not writeable";
}*/
?>
