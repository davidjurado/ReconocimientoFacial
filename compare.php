<?php

require './app/start.php';
use Aws\S3\Exception\S3Exception;

if(isset($_FILES['file'])){

   $id_img=  $_POST["nombre_img"];

    $file = $_FILES['file'];
 
    // File details
    $name = $file['name'];
    $tmp_name = $file['tmp_name'];
 
    $extension = explode('.', $name);
    $extension = strtolower(end($extension));
 
    // Temp details
    $key = md5(uniqid());
    $tmp_file_name = "{$key}.{$extension}";
    $id_target="{$id_img}.{$extension}";
    $tmp_file_path = "files/{$tmp_file_name}";
   
    // Move the file
    move_uploaded_file($tmp_name, $tmp_file_path);

     $gestor=fopen($tmp_file_path, 'rb');
     try {
 
        $s3->putObject([
            'Bucket' => $config['s3']['bucket'],
            'Key' => "uploads/{$name}",
            'Body' =>  $gestor,
            'ACL' => 'public-read'
        ]);
 
        // Remove the file
       
 
        // Print the URL to the object.

      $result = $s3->getObject([
    'Bucket' => $config['s3']['bucket'],
    'Key' => "uploads/{$name}",

]);

      
       $enlace=$result["@metadata"]["effectiveUri"];
       echo "<a href='".$enlace."' target='_blank'>".$enlace."</a>";
       echo "<br>";

$face=$rek->detectFaces([
    'Image' => [
        'S3Object' => [ 
            'Bucket' => $config['s3']['bucket'],
            'Name' => "uploads/{$name}",
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

if(!is_null($a)){
echo "Confidencialidad FaceDetection: ";
echo $b." %";
}else{
echo "No se detectó algún rostro";
}


echo "<br>";
$file='FaceDetails.json';
file_put_contents($file, $d);
echo "<br>";
echo "<a href='FaceDetails.json' target='_blank' >Detalles faciales imagen actual</a>";


$labels = $rek->detectLabels([
    'Image' => [
        'S3Object' => [
            'Bucket' => $config['s3']['bucket'],
            'Name' => "uploads/{$name}",
        ],
    ],
    //'MaxLabels' => 123,
    //'MinConfidence' => 70,
]);



$l=json_encode($labels['Labels']);
echo "<br>";
$file='Labels.json';
file_put_contents($file, $l);
echo "<br>";
echo "<a href='Labels.json' target='_blank' >Etiquetas</a>";
echo "<br>";


$comparation = $rek->compareFaces([
    'SourceImage' => [
        'S3Object' => [
             'Bucket' => $config['s3']['bucket'],
            'Name' => "uploads/{$name}",
        ],
    ],
    'TargetImage' => [
        'S3Object' => [
             'Bucket' => $config['s3']['bucket'],
            'Name' => "uploads/{$id_target}",
        ],
    ],
]);

//echo $comparation;
//$match=$comparation['FaceMatches'][0]['Similarity'];
//$match2=$comparation['FaceMatches'];
//$match_json=json_encode($match2);
//echo $match_json;


$c=$comparation['FaceMatches'];
$d=json_encode($c);
if(strlen($d)>2){
$a=$comparation['FaceMatches'][0]['Similarity'];
}else{
  $a=null;
}

$b=json_encode($a);

echo "<br>";

if(!is_null($a)){
echo "Porcentaje de similitud: ";
echo $b." %";
}else{
echo "Las imagenes no corresponden a la misma persona";
}


echo "<br>";
$file='FaceDetails_target.json';
file_put_contents($file, $d);
echo "<br>";
echo "<a href='FaceDetails_target.json' target='_blank' >Detalles faciales imagen objetivo</a>";
echo "<br>";
echo "<br>";



$imageData = base64_encode(file_get_contents($enlace));
echo '<img src="data:image/jpeg;base64,'.$imageData.'">';
echo "<br>";
echo "<br>";


$url=$s3->getObjectUrl($config['s3']['bucket'],"uploads/{$id_target}");
echo $url;
echo "<br>";



$imageData = base64_encode(file_get_contents($url));
echo '<img src="data:image/jpeg;base64,'.$imageData.'">';
echo "<br>";
echo "<br>";

fclose($gestor);
unlink($tmp_file_path);

    } catch (S3Exception $e) {
    //die("There was an error uploading that file.");
    echo $e->getMessage() . "\n";
    }
}
 
?>

<!DOCTYPE html>
<html>
<head>
	<title>comparar caras</title>
</head>
<body>
     <form action="compare.php" method="post" enctype="multipart/form-data">
      <br>
      ID (sin extensión): <input type="text" name="nombre_img" value=""><br>
     	<input type="file" name="file">
     	<input type="submit" name="Upload">

     </form>
</body>
</html>
