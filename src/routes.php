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
$container['upload_directory'] = '../public/images';

require 'api/crud.php';
require 'api/pageconfig.php';


// endpoints for model filter component
$app->get('/api/clubmembers/{club_id}/{param_name}/{param_value}', function (Request $request, Response $response, array $args) {
    $club_id = $args['club_id'];
    $param_name = $args['param_name'];
    $param_value = $args['param_value'];
    $result = R::find("fighter", $param_name . ' LIKE ? AND fightclubid = ?', array('%'.$param_value.'%', $club_id));
    return $response->getBody()->write(json_encode($result));
});

$app->get('/api/search/{model_name}/{param_name}/{param_value}', function (Request $request, Response $response, array $args) {
    $model_name = $args['model_name'];
    $param_name = $args['param_name'];
    $param_value = $args['param_value'];
    $result = R::find($model_name, $param_name . ' LIKE ? ', array('%'.$param_value.'%'));
    return $response->getBody()->write(json_encode($result));
});


// club member endpoints
$app->get('/api/clubmembers/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];
    $result = R::findAll('fighter', ' fightclubid = ?', array($id));
    return $response->getBody()->write(json_encode($result));
});

$app->get('/api/fighters/unassigned', function (Request $request, Response $response, array $args) {
    $result = R::findAll('fighter', ' fightclubid = 0');
    return $response->getBody()->write(json_encode($result));
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



$app->get('/api/tournamentprogram/{tournament_id}', function (Request $request, Response $response, array $args) {
    $tournament_id = $args['tournament_id'];
    $tournamentFights = R::findAll('tournamentfight', ' tournamentid = ' . $tournament_id);
    $results = array();
    foreach($tournamentFights as $fight){
        $contestants = R::findAll('fightcontestant', 'tournamentfightid = ' . $fight['id']);
        $fightContestants = [];
        foreach($contestants as $contestant){
            $contestant['fighter'] = R::load('fighter', $contestant->fighterid);
            $fightContestants[] = $contestant;
        }
        $fight['fight_contestants'] = $fightContestants;
        $results[] = $fight;
    }
    return $response->getBody()->write(json_encode($results));
});



$app->get('/api/fighters/unassigned/{param_name}/{param_value}', function (Request $request, Response $response, array $args) {
    $param_name = $args['param_name'];
    $param_value = $args['param_value'];
    $result = R::findAll('fighter', ' fightclubid = 0 AND ' . $param_name . ' = ?', array($param_value));
    return $response->getBody()->write(json_encode($result));
});


// image upload
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