<?php 

	include_once './config/cors.php';
	include_once './config/database.php';
	include_once './classes/banner.php';

	$database = new Database();
	$connection = $database->getConnection();

	$classBanners = new Banner($connection);
	
	$action = isset($_GET['action']) ? $_GET['action'] : NULL;

	$result = [
		'success' => true,
		'message' => ''
	];

	switch ($action) {
		case 'all':
			$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 1;
			$cidade = isset($_GET['cidade']) ? $_GET['cidade'] : 1;
			$result['banners'] = $classBanners->getBanners($tipo, $cidade);
			break;
		default:
			 $result['success'] = false;
			 $result['message'] = 'Ação não foi encontrado!';
			break;
	}	

	echo json_encode($result);
 ?>