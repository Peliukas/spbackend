<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\UploadedFile;

require 'rb-mysql.php';
require 'vendor/autoload.php';
require 'config.php';


// global $youtubeApiKey;
// global $youtubeChannelId; 
R::setup( 'mysql:host='.$config['db']['host'].';dbname='.$config['db']['dbname'], $config['db']['user'], $config['db']['pass']);

$app = new \Slim\App(['settings' => $config]);


header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Bearer');

$container = $app->getContainer();
$container['upload_directory'] = '../public/images';

require 'api/crud.php';
require 'api/pageconfig.php';
require 'api/authentication.php';


// club member endpoints
$app->get('/api/clubmembers/{club_id}/{param_name}/{param_value}', function (Request $request, Response $response, array $args) {
    $club_id = $args['club_id'];
    $param_name = $args['param_name'];
    $param_value = $args['param_value'];
    $result = R::find("fighter", $param_name . ' LIKE ? AND fightclubid = ?', array('%'.$param_value.'%', $club_id));
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

$app->get('/api/cachevideos', function(Request $request, Response $response) {
        require 'config.php';
        $ch = curl_init();
        $channelVideos = array();
        $requestUrl = 'https://www.googleapis.com/youtube/v3/search?key=' . $youtubeApiKey .  '&channelId=' . $youtubeCannelId . '&order=date&part=snippet&type=video,id&maxResults=50';
        $pageResult = json_decode(file_get_contents($requestUrl));
        if(empty($pageResult->items)){
            return $response->getBody()->write(json_encode($pageResult));
        }
        foreach($pageResult->items as $pi){
            $channelVideos[] = array(
                'videoid' => $pi->id->videoId,
                'title' => $pi->snippet->title,
                'thumbnail' => $pi->snippet->thumbnails->default->url
            );
        }
        if(!empty($pageResult->nextPageToken)){
            do{
                $requestUrl = 'https://www.googleapis.com/youtube/v3/search?key=' . $youtubeApiKey .  '&channelId=' . $youtubeCannelId . '&order=date&part=snippet&type=video,id&maxResults=50&pageToken=' . $pageResult->nextPageToken;
                $pageResult = json_decode(file_get_contents($requestUrl));
                if(!empty($pageResult->items)){
                    foreach($pageResult->items as $pi){
                        $channelVideos[] = array(
                            'videoid' => $pi->id->videoId,
                            'title' => $pi->snippet->title,
                            'thumbnail' => $pi->snippet->thumbnails->default->url
                        );
                    }   
                }
            }while(!empty($pageResult->nextPageToken));

        }
        $cachedcount = 0;
        $addedids = array();
        foreach($channelVideos as $channelVideo){
            $existingvideo = R::find('channelvideo', " videoid = ? ", array($channelVideo['videoid']));
            if(empty($existingvideo)){
                $video = R::dispense('channelvideo');
                $video->videoid = $channelVideo['videoid'];
                $video->title = $channelVideo['title'];
                $video->thumbnail = $channelVideo['thumbnail'];
                $videoid = R::store($video);
                if(!empty($videoid)){
                    $addedids[] = $videoid;
                    $cachedcount++;
                }
            }
        }
        return $response->getBody()->write(json_encode(array("cachedcount" => $cachedcount, "addedids" => $addedids)));
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