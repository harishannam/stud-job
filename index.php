<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'vendor/autoload.php';
include 'db.php';
require '../../libs/Slim/Slim.php';
use sendwithus\API;

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

//Business
$app->get('/business/:id','getBusinessDetail');
$app->post('/:user_id/business','postBusiness');

// run the Slim app
$app->contentType('application/json');
$app->run();

function getBusinessDetail($id) {
	$sql = "SELECT * FROM a_bus_details WHERE id=:id";
	try {
		$db = getDB();
		$stmt = $db->prepare($sql); 
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$business = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo '{"business": ' . json_encode($business) . '}';
	} catch(PDOException $e) {
	    //error_log($e->getMessage(), 3, '/var/tmp/php.log');
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}



function postBusiness($user_id){
	$request = \Slim\Slim::getInstance()->request();
	$update = json_decode($request->getBody());
	$sql = "INSERT INTO a_bus_details (company_name, email, phone, currency, timezone, descrip, reg_id, vat_id, street, city, state, country, zipcode, created_at) VALUES (:company_name, :email, :phone, :currency, :timezone, :descrip, :reg_id, :vat_id, :street, :city, :state, :country, :zipcode, :created_at);";
	try {
		$db = getDB();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("company_name", $update->company_name);
		$stmt->bindParam("email", $update->email);
		$stmt->bindParam("phone", $update->phone);
		$stmt->bindParam("currency", $update->currency);
		$stmt->bindParam("timezone", $update->timezone);
		$stmt->bindParam("descrip", $update->descrip);
		$stmt->bindParam("reg_id", $update->reg_id);
		$stmt->bindParam("vat_id", $update->vat_id);
		$stmt->bindParam("street", $update->street);
		$stmt->bindParam("city", $update->city);
		$stmt->bindParam("state", $update->state);
		$stmt->bindParam("country", $update->country);
		$stmt->bindParam("zipcode", $update->zipcode);
		$time=date('Y-m-d H:i:s');
		$stmt->bindParam("created_at", $time);
		$stmt->execute();
		$bus_id = $db->lastInsertId();
		$db = null;
		$sql = "INSERT INTO a_bus_user (bus_id, user_id, created_at) VALUES (:bus_id, :user_id, :created_at);";
		try {
			$db = getDB();
			$stmt = $db->prepare($sql); 
			$stmt->bindParam("bus_id", $bus_id);
			$stmt->bindParam("user_id", $user_id);
			$time=date('Y-m-d H:i:s');
			$stmt->bindParam("created_at", $time);
			$stmt->execute();
			$db = null;
			postBusinessDefaultInvSettings($bus_id); // add default inv/est settings
			postBusinessDefaultPaySettings($bus_id); // add default payment settings
			echo '{"success": true}';
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}';
		}
	} catch(PDOException $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/php.log');
		print_r('{"error":{"text":'. $e->getMessage() .', "sql":'.$sql.'}}'); 
	}
}
?>