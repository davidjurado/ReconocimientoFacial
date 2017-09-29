<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <title>Resultados</title>
  <!--Let browser know website is optimized for mobile-->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <!-- Compiled and minified CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.7/css/materialize.min.css">
  <!-- Compiled and minified JavaScript -->
  <link href="https://fonts.googleapis.com/css?family=Raleway|Roboto" rel="stylesheet">
  <!--  Materialize Scripts-->
  <!--  SweetAleert2-->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.9.1/sweetalert2.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.9.1/sweetalert2.min.css">
  <!-- Include a polyfill for ES6 Promises (optional) for IE11 and Android browser -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js"></script>
  <!-- First, include the Webcam.js JavaScript Library -->
  <script type="text/javascript" src="webcam.js"></script>
  <link href="css/style.css" type="text/css" rel="stylesheet" media="screen,projection"/>
  <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.7/js/materialize.min.js"></script>
  <script src="js/init.js"></script>
</head>
<body>
 <div class="navbar-fixed">
    <nav class="white" role="navigation">
      <div class="nav-wrapper container">
        <ul id="slide-out" class="side-nav">
          <li><a href=".">Home</a></li>
          <li><a href="captura.html">Captura</a></li>
          <li><a href="reconocimiento.html">Reconocimiento Facial</a></li>
        </ul>
        <a href="#" data-activates="slide-out" class="button-collapse show-on-large"><i class="material-icons">menu</i></a>
        <a id="logo-container" href="." class="brand-logo">RF</a>
        <ul class="right hide-on-med-and-down">
          <li class="active"><a href="captura.html">Captura</a></li>
          <li><a href="reconocimiento.html">Reconocimiento</a></li>
        </ul>
        <ul id="nav-mobile" class="side-nav">
          <li class="active"><a href="captura.html">Captura</a></li>
          <li><a href="reconocimiento.html">Reconocimiento</a></li>
        </ul>
      </div>
    </nav>
    </div>
  <script type="text/javascript">
    function redireccionarPagina() {
      window.location = "captura.html";
    }
  </script>

  <?php
  require './app/start.php';
  use Aws\S3\Exception\S3Exception;
  $id_img=  $_POST["nombre_img"];
  $path="saved_images/{$id_img}.jpg";

  $response = $s3->doesObjectExist($config['s3']['bucket'], "uploads/{$path}");

  if(!$response){

      $encoded_data = $_POST['mydata'];
  $binary_data = base64_decode( $encoded_data );
  $result = file_put_contents( $path, $binary_data );
  if (!$result) die("Could not save image!  Check file permissions.");
  $final_name=$path;
  $gestor=fopen($path, 'rb');
  try {
    $s3->putObject([
      'Bucket' => $config['s3']['bucket'],
      'Key' => "uploads/{$final_name}",
      'Body' =>  $gestor,
      'ACL' => 'public-read'
    ]);
    $result = $s3->getObject([
      'Bucket' => $config['s3']['bucket'],
      'Key' => "uploads/{$final_name}",
    ]);
    $enlace=$result["@metadata"]["effectiveUri"];
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
    $caras=count($c);
    $d=json_encode($c);
    if($caras==1)
    {
      $a=$face['FaceDetails'][0]['Confidence'];
    }
    else
    {
      $a=null;
    }
    $b=json_encode($a);
    $file='FaceDetails.json';
    file_put_contents($file, $d);
    $labels = $rek->detectLabels([
      'Image' => [
        'S3Object' => [
          'Bucket' => $config['s3']['bucket'],
          'Name' => "uploads/{$final_name}",
        ],
      ],
    ]);
    $l=json_encode($labels['Labels']);
    $file='Labels.json';
    file_put_contents($file, $l);
    fclose($gestor);
    if($caras==1)
    {
      $validarpose=false;
       $valMin=-5;
       $valMax=5;
       if($face['FaceDetails'][0]['Pose']['Yaw']>=$valMin && $face['FaceDetails'][0]['Pose']['Pitch']>=$valMin &&  $face['FaceDetails'][0]['Pose']['Roll']>=$valMin && $face['FaceDetails'][0]['Pose']['Yaw']<=$valMax && $face['FaceDetails'][0]['Pose']['Pitch']<=$valMax &&  $face['FaceDetails'][0]['Pose']['Roll']<=$valMax){
            $validarpose=true;

              echo "<script language='javascript'>"; 
            echo "swal(
        'Rostro detectado',
        'A continiación se muestran los resultados',
        'success'
      )"; 
      echo "</script>";

  echo'
        <script type="text/javascript">
    
var newsrc = "0";

function changeImage() {
  if ( newsrc == "0" ) {
    document.getElementById("my_image").src = document.getElementById("df-img").src;
    
    newsrc  = "1";
  }
  else {
   document.getElementById("my_image").src= "data:image/jpeg;base64,'.$imageData.'";
   
    newsrc  = "0";
  }
}
  </script>';


      echo '
      <section class="aboutContent">
        <div class="container row">
          <div class="col s12 m2 l2">
          </div>
           <div class="col s12 m6 l8">
          <div class="card">
            <div class="card-image">
              <img id="my_image" class="responsive-img materialboxed" data-caption="'.$b." %".' de precisión" src="data:image/jpeg;base64,'.$imageData.'">
            </div>
            <div class="card-content">
            <span class="card-title activator grey-text text-darken-4">'.$id_img.'</span>
<a onClick="changeImage()" class="btn-floating btn-large halfway-fab waves-effect waves-light red right tooltipped" data-position="top" data-delay="50" data-tooltip="Landmarks"><i class="material-icons">mood</i></a>
              <p>Rostro detectado exitosamente.</p>
            </div>
            <div class="card-tabs">
              <ul class="tabs tabs-fixed-width">
                <li class="tab"><a class="active" href="#test4">Detección Facial</a></li>
                <li class="tab"><a href="#test5">Detalles Faciales</a></li>
                <li class="tab"><a href="#test6">Etiquetas</a></li>
              </ul>
            </div>
            <div class="card-content blue-grey lighten-4">
              <div class="center-align" id="test4">Confidencialidad de detección: '.$b." %".'</div>
                <div class="center-align" id="test5">
                  <img style="display:none" id="df-img" class="responsive-img materialboxed" data-caption="se muestran los landmarks" width="150" src="" style="margin: auto;position: relative;top:0;bottom:0;left:0;right:0;">
                  <a href="FaceDetails.json" target="_blank" >Detalles faciales</a></div>
                <div class="center-align" id="test6"><a href="Labels.json" target="_blank" >Etiquetas</a></div>
              </div>
            </div>
          </div>
        </div> 
         </div>    
      </section>     
      ';

       }
       else{
        unlink($path);
        echo "<script language='javascript'>"; 
        echo "swal({
        title: 'El rostro no está centrado',
        imageUrl: 'data:image/jpeg;base64,".$imageData."',
        type: 'error',
        confirmButtonColor: '#47A6AC',";
        $s3->deleteMatchingObjects($config['s3']['bucket'],"uploads/{$final_name}");
        echo "
        confirmButtonText: 'intentar de nuevo!',
        allowOutsideClick: false
      }).then(function () {
        redireccionarPagina();
      })"; 
      echo "</script>";

       }


    }
    else
    {
      unlink($path);
      $s3->deleteMatchingObjects($config['s3']['bucket'],"uploads/{$final_name}");

      if($caras>1)
      {
        echo "<script language='javascript'>"; 
      echo "swal({
        title: 'Hay más de un rostro',
        imageUrl: 'data:image/jpeg;base64,".$imageData."',
        type: 'error',
        confirmButtonColor: '#47A6AC',";
    
        echo "
        confirmButtonText: 'intentar de nuevo!',
        allowOutsideClick: false
      }).then(function () {
        redireccionarPagina();
      })"; 
      echo "</script>";
      }
      else
      {
       echo "<script language='javascript'>"; 
      echo "swal({
        title: 'No se reconoció algun rostro',
        imageUrl: 'data:image/jpeg;base64,".$imageData."',
        type: 'error',
        confirmButtonColor: '#47A6AC',";
        echo "
        confirmButtonText: 'intentar de nuevo!',
        allowOutsideClick: false
      }).then(function () {
        redireccionarPagina();
      })"; 
      echo "</script>";
      }
    }
  }
  catch (S3Exception $e) {
    echo $e->getMessage() . "\n";
  }
  if($caras==1 && $validarpose)
  {
    echo '
    <canvas id="myCanvas" width="600" height="460" style="display:none">
    </canvas>
    <script>
    window.onload = function() {
      var c=document.getElementById("myCanvas");
      var ctx=c.getContext("2d");
      var img=document.getElementById("my_image");
      ctx.drawImage(img,0,0);
//ojo1
      ctx.beginPath();
      ctx.lineWidth = "3";
      ctx.strokeStyle = "#1565c0";
      ctx.rect(600*'.json_encode($face['FaceDetails'][0]['Landmarks'][0]['X']).'-5, 460*'.json_encode($face['FaceDetails'][0]['Landmarks'][0]['Y']).'-5, 15, 10);
      ctx.stroke();

//ojo2
      ctx.beginPath();
      ctx.lineWidth = "3";
      ctx.strokeStyle = "#1565c0";
      ctx.rect(600*'.json_encode($face['FaceDetails'][0]['Landmarks'][1]['X']).'-5, 460*'.json_encode($face['FaceDetails'][0]['Landmarks'][1]['Y']).'-5, 15, 10);
      ctx.stroke();

//nariz
      ctx.beginPath();
      ctx.lineWidth = "3";
      ctx.strokeStyle = "#1976d2";
      ctx.rect(600*'.json_encode($face['FaceDetails'][0]['Landmarks'][2]['X']).'-5, 460*'.json_encode($face['FaceDetails'][0]['Landmarks'][2]['Y']).'-5, 15, 10);
      ctx.stroke();

//boca1
      ctx.beginPath();
      ctx.lineWidth = "3";
      ctx.strokeStyle = "#1e88e5";
      ctx.rect(600*'.json_encode($face['FaceDetails'][0]['Landmarks'][3]['X']).'-5, 460*'.json_encode($face['FaceDetails'][0]['Landmarks'][3]['Y']).'-5, 15, 10);
      ctx.stroke();

//boca2
      ctx.beginPath();
      ctx.lineWidth = "3";
      ctx.strokeStyle = "#1e88e5";
      ctx.rect(600*'.json_encode($face['FaceDetails'][0]['Landmarks'][4]['X']).'-5, 460*'.json_encode($face['FaceDetails'][0]['Landmarks'][4]['Y']).'-5, 15, 10);
      ctx.stroke();

      var img = new Image();
      img.src = c.toDataURL();
      document.getElementById("df-img").src=img.src;
    };
    </script> 
    ';
    }
  
  }
  else{

     echo "<script language='javascript'>"; 
                echo "swal({
                    title: 'Ha ocurrido un error',
                    text: 'El correo ingresado ya se encuentra registrado',
                    type: 'error',
                    confirmButtonColor: '#47A6AC',";
                    echo "
                    confirmButtonText: 'intentar de nuevo!',
                    allowOutsideClick: false
                }).then(function () {
                    redireccionarPagina();
                })"; 
                echo "</script>";
  }
  ?>
    <footer class="page-footer teal">
    <div class="footer-copyright">
      <div class="container">
      &copy;2017 Universidad Industrial de Santander
      </div>
    </div>
  </footer>
</body>
</html>