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
    $final_name="{$id_img}.{$extension}";
    $tmp_file_path = "files/{$final_name}";
   
    // Move the file
    move_uploaded_file($tmp_name, $tmp_file_path);

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

      
       $enlace=$result["@metadata"]["effectiveUri"]."\n";
       echo "<a href='".$enlace."' target='_blank'>".$enlace."</a>";
       echo "<br>";

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


echo "count json ";
$caras=count($c);
echo $caras;
echo "<br>";

if(strlen($d)>2){
$a=$face['FaceDetails'][0]['Confidence'];
}else{
  $a=null;
}

$b=json_encode($a);

echo "<br>";

if($caras==1){
echo "Confidencialidad FaceDetection: ";
echo $b." %";
}else{
  if($caras>1){
    echo "hay mas de una cara";
  }else{
    echo "No se detectó algún rostro";
  }

}


echo "<br>";
$file='FaceDetails.json';
file_put_contents($file, $d);
echo "<br>";
echo "<a href='FaceDetails.json' target='_blank' >Detalles faciales</a>";


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
echo "<br>";
$file='Labels.json';
file_put_contents($file, $l);
echo "<br>";
echo "<a href='Labels.json' target='_blank' >Etiquetas</a>";
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
	<title>Upload</title>
</head>
<body>
     <form action="upload.php" method="post" enctype="multipart/form-data">
      <br>
      ID: <input type="text" name="nombre_img" value=""><br>
     	<input type="file" name="file">
     	<input type="submit" name="Upload">

     </form>
</body>
</html>
