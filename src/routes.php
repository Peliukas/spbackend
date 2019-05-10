<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\UploadedFile;

require 'rb-mysql.php';
require '../vendor/autoload.php';

$config['displayErrorDetails'] = true;
// $config['addContentLengthHeader'] = false;
// $config['determineRouteBeforeAppMiddleware'] = true;

$config['db']['host']   = 'localhost';
$config['db']['user']   = 'root';
$config['db']['pass']   = '';
$config['db']['dbname'] = 'sportsplanetdb';

R::setup( 'mysql:host=localhost;dbname=sportsplanetdb', 'root', '');

$app = new \Slim\App(['settings' => $config]);


header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

$container = $app->getContainer();
// $container['upload_directory'] = __DIR__ . '/uploads';
$container['upload_directory'] = '../public/images';


$app->post('/api/{model_name}', function (Request $request, Response $response, array $args) {
    $model_name = $args['model_name'];
    switch($model_name){
        case "fighter":
        $fighter = R::dispense('fighter');
        $fighter->firstname = $request->getParsedBody()['firstname'];
        $fighter->lastname = $request->getParsedBody()['lastname'];
        $fighter->weight = $request->getParsedBody()['weight'];
        $fighter->height = $request->getParsedBody()['height'];
        $fighter->gender = $request->getParsedBody()['gender'];
        $fighter->birthdate = $request->getParsedBody()['birthdate'];
        $fighter->fightclubid = "";
        
        $fighterid = R::store($fighter);
        return $response->getBody()->write($fighterid);
        case "fightclub":
        $fightclub = R::dispense('fightclub');
        $fightclub->name = $request->getParsedBody()['name'];
        $fightclub->city = $request->getParsedBody()['city'];
        $fightclub->address = $request->getParsedBody()['address'];
        $fightclub->website = $request->getParsedBody()['website'];
        $fightclub->email = $request->getParsedBody()['email'];
        $fightclub->phone = $request->getParsedBody()['phone'];
        $fightclubid = R::store($fightclub);
        return $response->getBody()->write($fightclubid);
    }
});

$app->post('/api/update/{model_name}/{id}', function (Request $request, Response $response, array $args) {
    $model_name = $args['model_name'];
    $id = $args['id'];
    $object = R::load($model_name, $id);
    switch($model_name){
        case "fighter":
        $object->firstname = $request->getParsedBody()['firstname'];
        $object->lastname = $request->getParsedBody()['lastname'];
        $object->weight = $request->getParsedBody()['weight'];
        $object->height = $request->getParsedBody()['height'];
        $object->gender = $request->getParsedBody()['gender'];
        $object->birthdate = $request->getParsedBody()['birthdate'];
        $object->fightclubid = "";
        $fighterid = R::store($object);
        return $response->getBody()->write($fighterid);
        case "fightclub":
        $object->name = $request->getParsedBody()['name'];
        $object->city = $request->getParsedBody()['city'];
        $object->address = $request->getParsedBody()['address'];
        $object->website = $request->getParsedBody()['website'];
        $object->email = $request->getParsedBody()['email'];
        $object->phone = $request->getParsedBody()['phone'];
        $fightclubid = R::store($object);
        return $response->getBody()->write($fightclubid);
    }
    return false;
});

$app->post('/api/assign/fighters/{club_id}', function (Request $request, Response $response, array $args) {
    $club_id = $args['club_id'];
    $fighter_list = $request->getParsedBody()['fighterlist'];
    foreach($fighter_list as $fighter_id ){
        $fighter = R::load('fighter', $fighter_id);
        $fighter->fightclubid = $club_id;
        R::store($fighter);
    }
    return $response->getBody()->write(true);
});

$app->get('/api/search/{model_name}/{param_name}/{param_value}', function (Request $request, Response $response, array $args) {
    $model_name = $args['model_name'];
    $param_name = $args['param_name'];
    $param_value = $args['param_value'];
    $result = R::find($model_name, $param_name . ' LIKE ? ', array('%'.$param_value.'%'));
    return $response->getBody()->write(json_encode($result));
});


// endpoints for model filter component
$app->get('/api/clubmembers/{club_id}/{param_name}/{param_value}', function (Request $request, Response $response, array $args) {
    $club_id = $args['club_id'];
    $param_name = $args['param_name'];
    $param_value = $args['param_value'];
    $result = R::find("fighter", $param_name . ' LIKE ? AND fightclubid = ?', array('%'.$param_value.'%', $club_id));
    return $response->getBody()->write(json_encode($result));
});

$app->get('/api/{model_name}', function (Request $request, Response $response, array $args) {
    $model_name = $args['model_name'];
    $result = R::findAll($model_name);
    return $response->getBody()->write(json_encode($result));
});

$app->get('/api/clubmembers/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];
    $result = R::findAll('fighter', ' fightclubid = ?', array($id));
    return $response->getBody()->write(json_encode($result));
});

$app->get('/api/fighters/unassigned', function (Request $request, Response $response, array $args) {
    $result = R::findAll('fighter', ' fightclubid = 0');
    return $response->getBody()->write(json_encode($result));
});

$app->get('/api/fighters/unassigned/{param_name}/{param_value}', function (Request $request, Response $response, array $args) {
    $param_name = $args['param_name'];
    $param_value = $args['param_value'];
    $result = R::findAll('fighter', ' fightclubid = 0 AND ' . $param_name . ' = ?', array($param_value));
    return $response->getBody()->write(json_encode($result));
});



$app->delete('/api/{model_name}/{id}', function (Request $request, Response $response, array $args) {
    $model_name = $args['model_name'];
    $id = $args['id'];
    $object = R::load($model_name, $id);
    if(!empty($object)){
        $result = R::trash($object);
        return $response->getBody()->write(true);
    }
    return $response->getBody()->write(false);
});


$app->post('/api/{model_name}/image/{id}', function(Request $request, Response $response, array $args) {
    $model_name = $args['model_name'];
    $id = $args['id'];
    $object = R::load($model_name, $id);
    $directory = $this->get('upload_directory');
    $uploadedFiles = $request->getUploadedFiles();
    $uploadedFile = $uploadedFiles['image'];
    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
        $filename = moveUploadedFile($directory, $uploadedFile);
        $object->image = $filename;
        R::store($object);
        return $response->write(true);
    }
        
});



function moveUploadedFile($directory, UploadedFile $uploadedFile)
{
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $basename = bin2hex(random_bytes(8));
    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}




$app->run();