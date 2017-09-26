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






<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.9.1/sweetalert2.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.9.1/sweetalert2.min.css">

<!-- Include a polyfill for ES6 Promises (optional) for IE11 and Android browser -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js"></script>


    <style type="text/css">
        #results { float:right; margin:20px; padding:20px; border:1px solid;}
    </style>
</head>
<body>
  <nav class="navbar-fixed blue lighten-5" role="navigation">
    <div class="nav-wrapper container ">



      <a id="logo-container" href="." class="brand-logo">Captura</a>
      <ul class="right hide-on-med-and-down">
        <li><a href="compare.html">Reconocimiento</a></li>
      </ul>

      <ul id="nav-mobile" class="side-nav">
        <li><a href="#">Navbar Link</a></li>
      </ul>
     <script type="text/javascript">
       function redireccionarPagina() {
  window.location = "index.html";
}
     </script>
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
	$path="saved_images/{$id_img}.jpg";
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
//unlink($tmp_file_path);






if(!is_null($a)){

echo "<script language='javascript'>"; 
echo "swal(
  'Rostro detectado',
  'A continiación se muestran los resultados',
  'success'
)"; 
echo "</script>";


echo '

 <div class="row">
<div class="col s3">
      </div>
      <div class="col s6">
          <div class="card">
            <div class="card-image">
              <img id="my_image" class="responsive-img materialboxed" data-caption="'.$b." %".'" src="data:image/jpeg;base64,'.$imageData.'">
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
      <div class="center-align" id="test4">Confidencialidad de detección: '.$b." %".'</div>
      <div class="center-align" id="test5">
         <img id="df-img" class="responsive-img materialboxed" data-caption="se muestran los landmarks" width="150" src="">
      <a href="FaceDetails.json" target="_blank" >Detalles faciales</a></div>
      <div class="center-align" id="test6"><a href="Labels.json" target="_blank" >Etiquetas</a></div>
    </div>
          </div>
        </div>
      </div>
            
            
      </div>
      </div>
      
';


}else{
unlink($tmp_file_path);
echo "<script language='javascript'>"; 
echo "swal({
  title: 'No se reconoció algun rostro',
  imageUrl: 'data:image/jpeg;base64,".$imageData."',
  type: 'error',
  confirmButtonColor: '#47A6AC',";


$s3->deleteMatchingObjects($config['s3']['bucket'],"uploads/{$final_name}");

  echo "
  confirmButtonText: 'intentar de nuevo!'
}).then(function () {
  redireccionarPagina();
})"; 
echo "</script>";

}

    } catch (S3Exception $e) {
    //die("There was an error uploading that file.");
    echo $e->getMessage() . "\n";
    }
 
if(strlen($d)>2){

echo '

<canvas id="myCanvas" width="600" height="460" style="display:none">
</canvas>
<script>

window.onload = function() {
    var c=document.getElementById("myCanvas");
    var ctx=c.getContext("2d");
    var img=document.getElementById("my_image");
    ctx.drawImage(img,0,0);



// Red rectangle
ctx.beginPath();
ctx.lineWidth = "3";
ctx.strokeStyle = "blue";
ctx.rect(600*'.json_encode($face['FaceDetails'][0]['Landmarks'][0]['X']).'-5, 460*'.json_encode($face['FaceDetails'][0]['Landmarks'][0]['Y']).'-5, 15, 10);
ctx.stroke();

// Red rectangle
ctx.beginPath();
ctx.lineWidth = "3";
ctx.strokeStyle = "blue";
ctx.rect(600*'.json_encode($face['FaceDetails'][0]['Landmarks'][1]['X']).'-5, 460*'.json_encode($face['FaceDetails'][0]['Landmarks'][1]['Y']).'-5, 15, 10);
ctx.stroke();

// Red rectangle
ctx.beginPath();
ctx.lineWidth = "3";
ctx.strokeStyle = "blue";
ctx.rect(600*'.json_encode($face['FaceDetails'][0]['Landmarks'][2]['X']).'-5, 460*'.json_encode($face['FaceDetails'][0]['Landmarks'][2]['Y']).'-5, 15, 10);
ctx.stroke();

// Red rectangle
ctx.beginPath();
ctx.lineWidth = "3";
ctx.strokeStyle = "blue";
ctx.rect(600*'.json_encode($face['FaceDetails'][0]['Landmarks'][3]['X']).'-5, 460*'.json_encode($face['FaceDetails'][0]['Landmarks'][3]['Y']).'-5, 15, 10);
ctx.stroke();

// Red rectangle
ctx.beginPath();
ctx.lineWidth = "3";
ctx.strokeStyle = "blue";
ctx.rect(600*'.json_encode($face['FaceDetails'][0]['Landmarks'][4]['X']).'-5, 460*'.json_encode($face['FaceDetails'][0]['Landmarks'][4]['Y']).'-5, 15, 10);
ctx.stroke();

var img = new Image();
img.src = c.toDataURL();
document.getElementById("df-img").src=img.src;

};
</script> 
 ';

}
?>

  <!--  Scripts-->
  <script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
  <script src="js/materialize.js"></script>
  <script src="js/init.js"></script>

</body>
</html>