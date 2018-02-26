<?php 

require_once 'vendor/autoload.php';
include_once './connection.php';
require_once './piramide-uploader/PiramideUploader.php';

// Necessary headers 
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}

$app = new Slim\Slim();

/**
 * URL for get all products
 */
$app->get('/products',function() use ($db_connection,$app) {
    $query = "SELECT * FROM product";
    $result = $db_connection->query($query,PDO::FETCH_OBJ);
    echo json_encode($result->fetchAll());
});

/**
 * URL for get a product by id
 */
$app->get('/products/:id_product',function($id_product) use ($db_connection,$app) {
    $query = "SELECT * FROM product WHERE id=:id_product";
    $read_task = $db_connection->prepare($query);
    $read_task->bindValue(':id_product',$id_product,PDO::PARAM_INT);
    $read_task->execute();
    echo json_encode($read_task->fetchObject());
});

/**
 * URL for add products
 */
$app->post("/products",function() use ($db_connection,$app) {
	$data_request = $app->request->post('data_request');
    $array_data = json_decode($data_request,true);

    $query = "INSERT INTO product VALUES (DEFAULT ,:name,:description,:price,:url_image);";
    $insert_task = $db_connection->prepare($query);

    try {
        $insert_task->bindValue(':name',       $array_data['name'],       PDO::PARAM_STR);
        $insert_task->bindValue(':description',$array_data['description'],PDO::PARAM_STR);
        $insert_task->bindValue(':price',      $array_data['price'],      PDO::PARAM_STR);
        $insert_task->bindValue(':url_image',  $array_data['url_image'],  PDO::PARAM_STR);
        $insert_task->execute();
        $response_from_db = ($insert_task->rowCount() > 0);

        $response_params = array(
            'success_code'    => 201,
            'success_message' => 'Product added successfully',
            'error_code'      => 400,
            'error_message'   => 'Product not added. Please check required data and retry'
        );

        $result = evaluateOperation($response_from_db,$response_params);
    } catch (PDOException $exception) {
        $result = array(
            "Status code" => $exception->getCode(),
            "Message"     => $exception->getMessage()
        );
    }
    echo json_encode($result);
});

/**
 * URL for update products
 */
$app->put("/products/:id_product",function($id_product) use ($db_connection, $app) {
    $data_request = $app->request->post("data_request"); 
    $array_data = json_decode($data_request,true);

    $query = "UPDATE product SET name=:name, description=:description, price=:price, url_image=:url_image WHERE id=:id;";
    $update_task = $db_connection->prepare($query);
    try {
        $update_task->bindValue(':name',       $array_data['name'],       PDO::PARAM_STR);
        $update_task->bindValue(':description',$array_data['description'],PDO::PARAM_STR);
        $update_task->bindValue(':price',      $array_data['price'],      PDO::PARAM_STR);
        $update_task->bindValue(':url_image',  $array_data['url_image'],  PDO::PARAM_STR);
        $update_task->bindValue(':id',         $id_product,               PDO::PARAM_STR);
        $update_task->execute();
        $response_from_db = ($update_task->rowCount() > 0);

        $response_params = array(
            'success_code'    => 201,
            'success_message' => 'Product updated successfully',
            'error_code'      => 400,
            'error_message'   => 'Product not updated. Please check required data and retry'
        );

        $result = evaluateOperation($response_from_db,$response_params);
    } catch (PDOException $exception) {
        $result = array(
            "Status code" => $exception->getCode(),
            "Message"     => $exception->getMessage()
        );
    }
    echo json_encode($result);
});

/**
 *  URL for delete products
 */
$app->delete('/products/:id_product',function($id_product) use ($db_connection,$app){
    $query = "DELETE FROM product WHERE id=:id_product";
    $delete_task = $db_connection->prepare($query);

    try {
        $delete_task->bindValue(':id_product',$id_product,PDO::PARAM_INT);
        $delete_task->execute();
        $response_from_db = ($delete_task->rowCount() > 0);

        $response_params = array(
            'success_code'    => 200,
            'success_message' => 'Product deleted successfully',
            'error_code'      => '400',
            'error_message'   => 'Product not deleted. Please check required data and retry'
        );

        $result = evaluateOperation($response_from_db,$response_params);
    } catch (PDOException $exception) {
        $result = array(
            "Status code" => $exception->getCode(),
            "Message"     => $exception->getMessage()
        );
    }
    echo json_encode($result);
});

/**
 * URL from upload an image to a product
 */
$app->post('/upload_image',function() use ($db_connection,$app){
    $response_params = array(
        'success_code'    => 201,
        'success_message' => 'File uploaded successfully',
        'error_code'      => '400',
        'error_message'   => 'File not uploaded. Please check required data and retry'
    );
    $piramideUploader = new piramideUploader();
    $upload = $piramideUploader->upload('image',"upload","upload",array('image/jpeg','image/png','image/gif'));
    $file = $piramideUploader->getInfoFile();
    $file_name = $file['complete_name'];

    $upload_status = evaluateOperation((isset($upload) && $upload['uploaded']),$response_params);

    echo json_encode($result);
});

/**
 * @param string $type_operation CRUD operation type
 * @param bool $operationStatus Response received from DB
 * @return array Result of db operation to show in JSON format
 */
function evaluateOperation(bool $operationStatus, array $response_params):array {
    if ($operationStatus) {
        return array(
            "Status code" => $response_params['success_code'],
            "Message"     => $response_params['success_message']
        );
    } else {
        return  array(
            "Status code" => $response_params['error_code'],
            "Message"     => $response_params['error_message']
        );
    }
}

$app->run();