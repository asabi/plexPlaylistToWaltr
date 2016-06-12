<?php
if (!isset($argv[1])) {
  echo "please provide a playlist name\n";
  exit;
}

if (!file_exists(__DIR__.'/script.ini')) {
  echo "Please copy script.ini.template to script.ini";
  exit;
}

$arrSettings = parse_ini_file(__DIR__.'/script.ini');

$playList = $argv[1];

define(SERVERLOCATION,$arrSettings['SERVERLOCATION']);
define(SAVEPATH, $arrSettings['SAVEPATH']);
define(PLAYLIST, $playList);

$playLists = getEndPointContentAsObject('/playlists');
$playListProcessed = false;

/*********************************************
Figure out if the requested play list exists, if it is process it,
and complete the loop. We show the names of all of the playlists, in
case the user misspelled the playlist (easier to see for the user)
*********************************************/
foreach ($playLists as $playListObj) {
  if (strtolower($playListObj->attributes()['title']) == strtolower(PLAYLIST)) {
    echo "FOUND !! - Processing {$playListObj->attributes()['title']}\n";
    processPlayList($playListObj->attributes()['key']);
    $playListProcessed = true;
    break;
  }

  echo $playListObj->attributes()['title']."\n";
 }

if (!$playListProcessed) {
  echo "Sorry, could not find $playList on server";
}


/*********************************************
Creates a folder for each movie with the
thumbnail from plex.
*********************************************/
function processPlayList($playListEndPoint) {
  $playListInfo = getEndPointContentAsObject($playListEndPoint);

  foreach ($playListInfo as $movie) {
    $title = $movie->attributes()['title'];
    $thumb = $movie->attributes()['thumb'];
    $file = $movie->Media->Part->attributes()['file'];
    $ext = pathinfo($file, PATHINFO_EXTENSION);

    // Create a new folder for the play list
    if (!file_exists(SAVEPATH.'/'.PLAYLIST)) {
      mkdir(SAVEPATH.'/'.PLAYLIST);
    }

    $movieFolder = SAVEPATH.'/'.PLAYLIST.'/'.$title;

    // Create a new folder for the movie
    if (!file_exists(SAVEPATH.'/'.PLAYLIST.'/'.$title)) {
      mkdir(SAVEPATH.'/'.PLAYLIST.'/'.$title);
    }

    // Make a copy of the movie
    if (!file_exists(SAVEPATH.'/'.PLAYLIST.'/'.$title.'/'.$title.'.'.$ext)) {
      copy ($file, SAVEPATH.'/'.PLAYLIST.'/'.$title.'/'.$title.'.'.$ext);
    } else {
      echo 'FILE ALREADY EXISTS: '.SAVEPATH.'/'.PLAYLIST.'/'.$title.'/'.$title.'.'.$ext."] --- SKIPPING\n";
    }
    // Save the thumbnail
    $stream = fopen(SERVERLOCATION.$thumb, "rb");
    $thumbnail = stream_get_contents($stream);
    file_put_contents(SAVEPATH.'/'.PLAYLIST.'/'.$title.'/picture.jpg',$thumbnail);

    echo "done $title\n";

  }

}

/*********************************************
Returns an array of objects that are contained
in the plex end point requested. Plex returns
things as XML, this returns the information as
arrays of simplexml
*********************************************/
function getEndPointContentAsObject($endPoint) {
  //echo SERVERLOCATION.$endPoint."\n";
  $ch = curl_init(SERVERLOCATION.$endPoint);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
  $output = curl_exec($ch);

  // Converts the XML into an array of objects
  try {
  $xmlObj = new SimpleXMLElement($output);
  } catch (Exception $e) {
   echo $e->getMessage()."\n";
   echo "End Point:".SERVERLOCATION.$endPoint."\n";
   echo "The String:".$xml."\n\n";
   exit;
  }
  return $xmlObj;
}
