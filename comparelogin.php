<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <title>Project Page</title>
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
          <li><a href="captura.html">Captura</a></li>
          <li class="active"><a href="reconocimiento.html">Reconocimiento</a></li>
        </ul>
        <ul id="nav-mobile" class="side-nav">
          <li><a href="captura.html">Captura</a></li>
          <li class="active"><a href="reconocimiento.html">Reconocimiento</a></li>
        </ul>
      </div>
    </nav>
    </div>
  <script type="text/javascript">
    function redireccionarPagina() {
      window.location = "reconocimiento.html";
    }
  </script>

<?php
require './app/start.php';
use Aws\S3\Exception\S3Exception;
    $id_img=  $_POST["nombre_img"];
    $pose=$_POST["pose"];
    $id_target="saved_images/{$id_img}.jpg";
    $response = $s3->doesObjectExist($config['s3']['bucket'], "uploads/{$id_target}");
    if($response)
    {
        $name = "saved_images/{$id_img}.login";
        $tmp_name = $id_img;
        $extension = "jpg";
        $key = md5(uniqid());
        $tmp_file_name = "{$key}.{$extension}";
        $encoded_data = $_POST['mydata'];
        $binary_data = base64_decode( $encoded_data );
        $path="saved_images/{$tmp_file_name}";
        $result = file_put_contents( $path, $binary_data );
        $tmp_file_path = $path;
        move_uploaded_file($tmp_name, $tmp_file_path);
        $gestor=fopen($tmp_file_path, 'rb');
        try { 
            $s3->putObject([
                'Bucket' => $config['s3']['bucket'],
                'Key' => "uploads/{$path}",
                'Body' =>  $gestor,
                'ACL' => 'public-read'
            ]);
            $result = $s3->getObject([
                'Bucket' => $config['s3']['bucket'],
                'Key' => "uploads/{$path}",
            ]);
            $enlace=$result["@metadata"]["effectiveUri"];
            $face=$rek->detectFaces([
                'Image' => [
                    'S3Object' => [ 
                        'Bucket' => $config['s3']['bucket'],
                        'Name' => "uploads/{$path}",
                    ],        
                ],
            ]);
            $cc=$face['FaceDetails'];
            $caras=count($cc);
            $dd=json_encode($cc);
            if($caras==1)
            {
                $fa=$face['FaceDetails'][0]['Confidence'];
            }else
            {
                $fa=null;
            }
            $bb=json_encode($fa);

            $posemsg="";
            $msg="";
            if($caras==1)
            {

                        $validarpose=false;
                      
                        switch ($pose) {
                          case "derecha":
                                          if($face['FaceDetails'][0]['Pose']['Yaw']>=30){
                                            $validarpose=true;
                                          }
                                          break;
                          case "izquierda":
                                          if($face['FaceDetails'][0]['Pose']['Yaw']<=-30){
                                            $validarpose=true;
                                          }
                                          break;
                          case "arriba":
                                          if($face['FaceDetails'][0]['Pose']['Pitch']>=20){
                                            $validarpose=true;
                                          }
                                          break;
                          case "abajo":
                                          if($face['FaceDetails'][0]['Pose']['Pitch']<=-15){
                                            $validarpose=true;
                                          }
                                          break;
                          }

                  

                          if($validarpose){
                                            $comparation = $rek->compareFaces([
                                            'SourceImage' => [
                                                'S3Object' => [
                                                    'Bucket' => $config['s3']['bucket'],
                                                    'Name' => "uploads/{$path}",
                                                ],
                                            ],
                                            'TargetImage' => [
                                                'S3Object' => [
                                                    'Bucket' => $config['s3']['bucket'],
                                                    'Name' => "uploads/{$id_target}",
                                                ],
                                            ],
                                        ]);
                                        $c=$comparation['FaceMatches'];
                                        $d=json_encode($c);
                                        if(strlen($d)>2)
                                            {
                                                $a=$comparation['FaceMatches'][0]['Similarity'];
                                                 $msg="";
                                            }
                                            else
                                            {
                                                $a=null;
                                                      $msg="La imagen no corresponde a la misma persona";
                                            }

                                              $b=json_encode($a);

                          }else{
                              $posemsg= "pose mal realizada";
                              $a=null;
                          }
                  
                    if(!is_null($a) && $validarpose)
                    {
                        echo "<script language='javascript'>"; 
                        echo "swal(
                            'Bienvenido ".$id_img."',
                            'Ingreso exitoso con ".$b."% de similitud',
                            'success'
                        )"; 
                        echo "</script>";


                        //echo "Confidencialidad FaceDetection: ";
                        //echo $bb." %";
                        $file='FaceDetails.json';
                        file_put_contents($file, $dd);
                        //echo "<a href='FaceDetails.json' target='_blank' >Detalles faciales imagen actual</a>";
                        $labels = $rek->detectLabels([
                            'Image' => [
                                'S3Object' => [
                                    'Bucket' => $config['s3']['bucket'],
                                    'Name' => "uploads/{$path}",
                                ],
                            ],
                        ]);
                        $l=json_encode($labels['Labels']);
                        $file='Labels.json';
                        file_put_contents($file, $l);
                        //echo "<a href='Labels.json' target='_blank' >Etiquetas</a>";
                        $file='FaceDetails_target.json';
                        file_put_contents($file, $d);
                        //echo "<a href='FaceDetails_target.json' target='_blank' >Detalles faciales imagen objetivo</a>";
                        $imageData = base64_encode(file_get_contents($enlace));
                        //echo '<img src="data:image/jpeg;base64,'.$imageData.'">';
                        $url=$s3->getObjectUrl($config['s3']['bucket'],"uploads/{$id_target}");
                        //echo $url;
                        $imageData2 = base64_encode(file_get_contents($url));
                        //echo '<img src="data:image/jpeg;base64,'.$imageData2.'">';

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
        <div class="row">
          <div class="col s12 m6 l6">
            <div class="card">
                <div class="card-image">
                    <img id="my_image" class="responsive-img materialboxed" data-caption="Se ha detectado un rostro con '.$bb." %".' de precisión" src="data:image/jpeg;base64,'.$imageData.'">
                </div>
                <div class="card-content">
                <span class="card-title">Actual</span>
                <a onClick="changeImage()" class="btn-floating btn-large halfway-fab waves-effect waves-light red right tooltipped" data-position="top" data-delay="50" data-tooltip="Marcadores"><i class="material-icons">mood</i></a>
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
                    <div class="center-align" id="test4">Confidencialidad de detección del rostro: '.$bb." %".'</div>
                        <div class="center-align" id="test5">
                            <img style="display:none" id="df-img" class="responsive-img materialboxed" data-caption="se muestran los landmarks" width="150" src="" style="margin: auto;position: relative;top:0;bottom:0;left:0;right:0;">
                            <a href="FaceDetails.json" target="_blank" >Detalles faciales</a></div>
                    <div class="center-align" id="test6"><a href="Labels.json" target="_blank" >Etiquetas</a></div>
                </div>
            </div>
              </div>

              <div class="col s12 m6 l6">
            <div class="card">
                <div class="card-image">
                    <img id="my_image2" class="responsive-img materialboxed" data-caption="Autenticación realizada con un '.$b." %".' de similitud entre las imagenes" src="data:image/jpeg;base64,'.$imageData2.'">
                </div>
                <div class="card-content">
                <span class="card-title">Original: '.$id_img.'</span>
                    <p>Autenticación exitosa.</p>
                </div>
                <div class="card-tabs">
                    <ul class="tabs tabs-fixed-width">
                        <li class="tab"><a class="active" href="#test7">Detección Facial</a></li>
                        <li class="tab"><a href="#test8">Detalles Faciales</a></li>
                    </ul>
                </div>
                <div class="card-content blue-grey lighten-4">
                    <div class="center-align" id="test7">Similitud para la Autenticación: '.$b." %".'</div>
                        <div class="center-align" id="test8">';
                            //<img id="df-img2" class="responsive-img materialboxed" data-caption="se muestran los landmarks" width="150" src="">
                           echo '<a href="FaceDetails_target.json" target="_blank" >Detalles faciales</a></div>
                        </div>
                    </div>
                </div>
            </div>
 </div>

        </div>
      </section>     
      ';

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
      ctx.arc(600*'.json_encode($face['FaceDetails'][0]['Landmarks'][0]['X']).', 460*'.json_encode($face['FaceDetails'][0]['Landmarks'][0]['Y']).', 5, 0, 2 * Math.PI);
      ctx.stroke();

//ojo2
      ctx.beginPath();
      ctx.lineWidth = "3";
      ctx.strokeStyle = "#1565c0";
      ctx.arc(600*'.json_encode($face['FaceDetails'][0]['Landmarks'][1]['X']).', 460*'.json_encode($face['FaceDetails'][0]['Landmarks'][1]['Y']).', 5, 0, 2 * Math.PI);
      ctx.stroke();

//nariz
      ctx.beginPath();
      ctx.lineWidth = "3";
      ctx.strokeStyle = "#1976d2";
      ctx.arc(600*'.json_encode($face['FaceDetails'][0]['Landmarks'][2]['X']).', 460*'.json_encode($face['FaceDetails'][0]['Landmarks'][2]['Y']).', 5, 0, 2 * Math.PI);
      ctx.stroke();

//boca1
      ctx.beginPath();
      ctx.lineWidth = "3";
      ctx.strokeStyle = "#1e88e5";
      ctx.arc(600*'.json_encode($face['FaceDetails'][0]['Landmarks'][3]['X']).', 460*'.json_encode($face['FaceDetails'][0]['Landmarks'][3]['Y']).', 5, 0, 2 * Math.PI);
      ctx.stroke();

//boca2
      ctx.beginPath();
      ctx.lineWidth = "3";
      ctx.strokeStyle = "#1e88e5";
      ctx.arc(600*'.json_encode($face['FaceDetails'][0]['Landmarks'][4]['X']).', 460*'.json_encode($face['FaceDetails'][0]['Landmarks'][4]['Y']).', 5, 0, 2 * Math.PI);
      ctx.stroke();

//caja
      ctx.beginPath();
      ctx.lineWidth = "3";
      ctx.strokeStyle = "#ef5350";
      ctx.rect(600*'.json_encode($face['FaceDetails'][0]['BoundingBox']['Left']).', 460*'.json_encode($face['FaceDetails'][0]['BoundingBox']['Top']).'-5, 600*'.json_encode($face['FaceDetails'][0]['BoundingBox']['Width']).', 460*'.json_encode($face['FaceDetails'][0]['BoundingBox']['Height']).');
      ctx.stroke();

      var img = new Image();
      img.src = c.toDataURL();
      document.getElementById("df-img").src=img.src;
    };
    </script> 
    ';
                    }
                    else
                    {
                        //echo "Las imagenes no corresponden a la misma persona";
                        $imageData = base64_encode(file_get_contents($enlace));
                        echo "<script language='javascript'>"; 
                        echo "swal({
                            title: '".$msg."',
                            text: '".$posemsg."',
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
                else
                {
                  $imageData = base64_encode(file_get_contents($enlace));
                  if($caras>1)
                  {
                     echo "<script language='javascript'>"; 
                    echo "swal({
                        title: 'Se ha reconocido más de un rostro',
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
                fclose($gestor);
                unlink($tmp_file_path);
                $s3->deleteMatchingObjects($config['s3']['bucket'],"uploads/{$path}");
                }
                catch (S3Exception $e) {
                    echo $e->getMessage() . "\n";
                }
            }
            else
            {
                echo "<script language='javascript'>"; 
                echo "swal({
                    title: 'Ha ocurrido un error',
                    text: 'El correo ingresado no corresponde a ningún usario registrado',
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