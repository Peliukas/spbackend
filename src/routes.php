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
        case "tournament":
        $tournament = R::dispense('tournament');
        $tournament->name = $request->getParsedBody()['name'];
        $tournament->startdate = $request->getParsedBody()['startdate'];
        $tournament->enddate = $request->getParsedBody()['enddate'];
        $tournament->city = $request->getParsedBody()['city'];
        $tournament->address = $request->getParsedBody()['address'];
        $tournament->description = $request->getParsedBody()['description'];
        $tournamentid = R::store($tournament);
        $tournamentFights = $request->getParsedBody()['tournament_fights'];
        if($tournamentFights){
            foreach($tournamentFights as $fight){
                $tournamentFight = R::dispense('tournamentfight');
                $tournamentFight->tournamentid = $tournamentid;
                $tournamentFight->winnerfighterid = 0;
                $tournamentfightid = R::store($tournamentFight);
                
                $fightContestant = R::dispense('fightcontestant');
                $fightContestant->fighterid = $fight["red"]["id"];
                $fightContestant->tournamentfightid = $tournamentfightid;
                $fightContestant->score = 0;
                R::store($fightContestant);
                
                $fightContestant = R::dispense('fightcontestant');
                $fightContestant->fighterid = $fight["blue"]["id"];
                $fightContestant->tournamentfightid = $tournamentfightid;
                $fightContestant->score = 0;
                R::store($fightContestant);
            }
        }
        return $response->getBody()->write($tournamentid);
        case "pageconfiguration":
        $page_name = $request->getParsedBody()["page_name"];
        $param_name = $request->getParsedBody()["param_name"];
        $param_value = $request->getParsedBody()["param_value"];
        $pageConfiguration = R::dispense('pageconfiguration');
        $pageConfiguration->page = $page_name;
        $pageConfiguration->paramname = $param_name;
        $pageConfiguration->paramvalue = $param_value;
        $pageConfigurationId = R::store($pageConfiguration);
        return $response->getBody()->write($pageConfigurationId);
    }
});

$app->post('/api/update/pageconfig/{page_name}/', function (Request $request, Response $response, array $args) {
    $page_name = $args["page_name"];
    $page_configs = $request->getParsedBody()["page_configs"];
    foreach($page_configs as $configname => $configvalue){
        $pageConfiguration = R::find('PageConfiguration', ' pagename = ? AND paramname = ? ', array($page_name, $configname) );
        if(!empty($pageConfiguration)){
            $pageConfiguration->param_value = $configvalue;
            R::store($pageConfiguration);
        }else{
            $pageConfiguration = R::dispense('PageConfiguration');
            $pageConfiguration->paramname = $configname;
            $pageConfiguration->paramvalue = $configvalue;
            R::store($pageConfiguration);
        }
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
        case "tournament":
        $object->name = $request->getParsedBody()['name'];
        $object->startdate = $request->getParsedBody()['startdate'];
        $object->enddate = $request->getParsedBody()['enddate'];
        $object->city = $request->getParsedBody()['city'];
        $object->address = $request->getParsedBody()['address'];
        $object->description = $request->getParsedBody()['description'];
        $tournamentid = R::store($object);
        $tournamentFights = $request->getParsedBody()['tournament_fights'];
        if($tournamentFights){
            foreach($tournamentFights as $fight){
                if(empty($fight["id"])){
                    $tournamentFight = R::dispense('tournamentfight');
                    $tournamentFight->tournamentid = $tournamentid;
                    $tournamentFight->winnerfighterid = !empty($fight['winnerfighterid']) ? $fight['winnerfighterid'] : "" ;
                    $tournamentFight->videourl = $fight['videourl'];
                    $tournamentfightid = R::store($tournamentFight);
                    
                    $fightContestant = R::dispense('fightcontestant');
                    $fightContestant->fighterid = $fight["red"]["id"];
                    $fightContestant->tournamentfightid = $tournamentfightid;
                    $fightContestant->score = 0;
                    R::store($fightContestant);
                    
                    $fightContestant = R::dispense('fightcontestant');
                    $fightContestant->fighterid = $fight["blue"]["id"];
                    $fightContestant->tournamentfightid = $tournamentfightid;
                    $fightContestant->score = 0;
                    R::store($fightContestant);
                }else{
                    $tournamentFight = R::load('tournamentfight', $fight["id"]);
                    if(empty($fight["deleted"])){
                        $tournamentFight->winnerfighterid = !empty($fight['winnerfighterid']) ? $fight['winnerfighterid'] : "" ;
                        $tournamentFight->videourl = $fight['videourl'];
                        $tournamentfightid = R::store($tournamentFight);
                        
                        $fightContestant = R::load('fightcontestant', ' fighterid = ' . $fight["red"]["id"] . ' AND tournamentfightid = ' . $fight["id"]);
                        $fightContestant->fighterid = $fight["red"]["id"];
                        $fightContestant->score = 0;
                        R::store($fightContestant);
                        
                        $fightContestant = R::load('fightcontestant', ' fighterid = ' . $fight["blue"]["id"] . ' AND tournamentfightid = ' . $fight["id"]);
                        $fightContestant->fighterid = $fight["blue"]["id"];
                        $fightContestant->score = 0;
                        R::store($fightContestant);
                    }else{
                        $tournamentFightContestants = R::findAll('fightcontestant', ' tournamentfightid = ' . $fight["id"]);
                        foreach($tournamentFightContestants as $fightContestant){
                            R::trash('fightcontestant', R::load("fightcontestant", $fightContestant["id"]));
                        }
                        R::trash('tournamentfight', $tournamentFight);
                    }
                }
            }
        }
        return $response->getBody()->write($tournamentid);
    }
    return false;
});

$app->get('/api/pageconfiguration/{page_name}', function(Request $request, Response $response, array $args){
    $page_name = $args['page_name'];
    $result = R::find('pageconfiguration', ' page = ? ', array($page_name));
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

$app->get('/api/search/{model_name}/{param_name}/{param_value}', function (Request $request, Response $response, array $args) {
    $model_name = $args['model_name'];
    $param_name = $args['param_name'];
    $param_value = $args['param_value'];
    $result = R::find($model_name, $param_name . ' LIKE ? ', array('%'.$param_value.'%'));
    return $response->getBody()->write(json_encode($result));
});

// get all models
$app->get('/api/{model_name}', function (Request $request, Response $response, array $args) {
    $model_name = $args['model_name'];
    $result = R::findAll($model_name);
    switch($model_name){
        case "fightclub":
        $result = R::findAll($model_name);
        foreach($result as $fightclub){
            $fightclub->membercount = R::count('fighter', ' fightclubid = ' . $fightclub->id);
        }
        break;
        case "tournament":
        $result = R::findAll($model_name);
        break;
        default:
        break;
    }
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



$app->delete('/api/{model_name}/{id}', function (Request $request, Response $response, array $args) {
    $model_name = $args['model_name'];
    $id = $args['id'];
    $object = R::load($model_name, $id);
    if(!empty($object)){
        switch($model_name){
            case "fighter":
            $result = R::trash($object);
            return $response->getBody()->write(true);
            case "fightclub":
            $fightclubMembers = R::findAll("fighter", " fightclubid = " . $object->id);
            foreach($fightclubMembers as $member){
                $fightclubMember = R::load("fighter", $member->id);
                $fightclubMember->fightclubid = 0;
                R::store($fightclubMember);
            }
            $result = R::trash($object);
            return $response->getBody()->write($result);
            case "tournament":
                $tournamentFights = R::findAll("tournamentfight", " tournamentid = " . $object->id);
                foreach($tournamentFights as $fight){
                    $fightContestants = R::findAll("fightcontestant", " tournamentfightid = " . $fight->id);
                    foreach($fightContestants as $contestant){
                        R::trash("fightcontestant", $contestant->id); 
                    }
                    R::trash("tournamentfight", $fight->id);
                }
                R::trash($model_name, $id);
            return $response->getBody()->write(true);
        }
    }
    return $response->getBody()->write(false);
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