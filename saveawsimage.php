 <!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Reconocimiento Facial</title>

  <!-- CSS  -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="css/materialize.css" type="text/css" rel="stylesheet" media="screen,projection"/>
  <link href="css/style.css" type="text/css" rel="stylesheet" media="screen,projection"/>
    <style type="text/css">
        #results { float:right; margin:20px; padding:20px; border:1px solid;}
    </style>
</head>
<body>
  <nav class="navbar-fixed blue lighten-5" role="navigation">
    <div class="nav-wrapper container ">



      <a id="logo-container" href="." class="brand-logo">Captura</a>
      <ul class="right hide-on-med-and-down">
        <li><a href="#">Reconocimiento</a></li>
      </ul>

      <ul id="nav-mobile" class="side-nav">
        <li><a href="#">Navbar Link</a></li>
      </ul>
     
    </div>
  </nav>







<?php
require './app/start.php';
use Aws\S3\Exception\S3Exception;
//$filename =  time() . '.jpg';
//$filepath = 'saved_images/';

$encoded_data = $_POST['mydata'];
	$binary_data = base64_decode( $encoded_data );
	
	$id_img=  $_POST["nombre_img"];
	$path="{$id_img}.jpg";
	$result = file_put_contents( $path, $binary_data );
	if (!$result) die("Could not save image!  Check file permissions.");


$tmp_file_path=$path;
 $final_name=$tmp_file_path;

$gestor=fopen($tmp_file_path, 'rb');
     try {
 
        $s3->putObject([
            'Bucket' => $config['s3']['bucket'],
            'Key' => "uploads/{$final_name}",
            'Body' =>  $gestor,
            'ACL' => 'public-read'
        ]);
 
        // Remove the file
       
 
        // Print the URL to the object.

      $result = $s3->getObject([
    'Bucket' => $config['s3']['bucket'],
    'Key' => "uploads/{$final_name}",

]);

      
       $enlace=$result["@metadata"]["effectiveUri"];
      // echo "<a href='".$enlace."' target='_blank'>".$enlace."</a>";
      // echo "<br>";
       $imageData = base64_encode(file_get_contents($enlace));

$face=$rek->detectFaces([
    'Image' => [
        'S3Object' => [ 
            'Bucket' => $config['s3']['bucket'],
            'Name' => "uploads/{$final_name}",
        ],

    ],
]);



$c=$face['FaceDetails'];
$d=json_encode($c);
if(strlen($d)>2){
$a=$face['FaceDetails'][0]['Confidence'];
}else{
  $a=null;
}

$b=json_encode($a);




echo "<br>";
$file='FaceDetails.json';
file_put_contents($file, $d);



$labels = $rek->detectLabels([
    'Image' => [
        'S3Object' => [
            'Bucket' => $config['s3']['bucket'],
            'Name' => "uploads/{$final_name}",
        ],
    ],
    //'MaxLabels' => 123,
    //'MinConfidence' => 70,
]);



$l=json_encode($labels['Labels']);

$file='Labels.json';
file_put_contents($file, $l);




fclose($gestor);
unlink($tmp_file_path);






if(!is_null($a)){


echo '

 <div class="row">
<div class="col s8 offset-s1">



<div class="row">
        <div class="col s12 m7">
          <div class="card">
            <div class="card-image">
              <img class="materialboxed" data-caption="'.$b." %".'" src="data:image/jpeg;base64,'.$imageData.'">
              <span class="card-title">'.$id_img.'</span>
            </div>
            <div class="card-content">
              <p>Rostro detectado exitosamente.</p>
            </div>
            <div class="card-tabs">
      <ul class="tabs tabs-fixed-width">
        <li class="tab"><a class="active" href="#test4">Detección Facial</a></li>
        <li class="tab"><a href="#test5">Detalles Faciales</a></li>
        <li class="tab"><a href="#test6">Etiquetas</a></li>
      </ul>
    </div>
    <div class="card-content grey lighten-4">
      <div id="test4">Confidencialidad de detección: '.$b." %".'</div>
      <div id="test5"><a href="FaceDetails.json" target="_blank" >Detalles faciales</a></div>
      <div id="test6"><a href="Labels.json" target="_blank" >Etiquetas</a></div>
    </div>
          </div>
        </div>
      </div>
            
            
      </div>
       </div>
';







}else{


echo '

 <div class="row">
<div class="col s8 offset-s1">



<div class="row">
        <div class="col s12 m7">
          <div class="card">
            <div class="card-image">
             <img class="materialboxed" data-caption="No se recoce ninguna cara" src="data:image/jpeg;base64,'.$imageData.'">
              <span class="card-title">'.$id_img.'</span>
            </div>
            <div class="card-content">
              <p>No se ha detectado ningún rostro.</p>
            </div>
            <div class="card-tabs">
      <ul class="tabs tabs-fixed-width">
        <li class="tab"><a class="active" href="#test4">Detección Facial</a></li>
        <li class="tab"><a href="#test6">Etiquetas</a></li>
      </ul>
    </div>
    <div class="card-content grey lighten-4">
      <div id="test4">Confidencialidad de detección: 0% </div>
      <div id="test6"><a href="Labels.json" target="_blank" >Etiquetas</a>
      </div>
    </div>
          </div>
        </div>
      </div>
            
            
      </div>
       </div>
';

}



    } catch (S3Exception $e) {
    //die("There was an error uploading that file.");
    echo $e->getMessage() . "\n";
    }
 




?>






  <!--  Scripts-->
  <script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
  <script src="js/materialize.js"></script>
  <script src="js/init.js"></script>

</body>
</html>