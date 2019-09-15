<?php 
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\UploadedFile;

$app->post('/api/addmodel/{model_name}', function (Request $request, Response $response, array $args) {
    $model_name = $args['model_name'];
    if(!empty($request->getParsedBody()['id'])){
        $id = $request->getParsedBody()['id'];
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
                            $tournamentFight->date = date('Y-m-d h:i:s');
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
                return $response->getBody()->write($tournamentid);
                
            }
            case "sponsor":
            $object->name = $request->getParsedBody()["name"];
            $object->url = $request->getParsedBody()["url"];
            $object->logo = $request->getParsedBody()["logo"];
            $sponsorId = R::store($object);
            return $response->getBody()->write($sponsorId);
            case "user":
            $object->username = $request->getParsedBody()["username"];
            $object->password = password_hash($request->getParsedBody()["password"], PASSWORD_BCRYPT);
            $object->email = $request->getParsedBody()["email"];
            $object->level = $request->getParsedBody()["level"];
            $object->avatar = $request->getParsedBody()["avatar"];
            $userId = R::store($object);
            return $response->getBody()->write($userId);
            

            default: return $response->getBody()->write(false);

        }
    }else{
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
                    $tournamentFight->videourl = $fight['videourl'];
                    $tournamentFight->date = date('Y-m-d h:i:s');
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
            case "sponsor":
            $sponsor = R::dispense('sponsor');
            $sponsor->name = $request->getParsedBody()["name"];
            $sponsor->url = $request->getParsedBody()["url"];
            $sponsor->logo = $request->getParsedBody()["logo"];
            $sponsorId = R::store($sponsor);
            return $response->getBody()->write($sponsorId);
            case "user":
            $user = R::dispense('user');
            $user->email = $request->getParsedBody()["username"];
            $user->password = password_hash($request->getParsedBody()["password"], PASSWORD_BCRYPT);
            $user->token = generateRandomString(30);
            $user->email = $request->getParsedBody()["email"];
            $user->level = $request->getParsedBody()["level"];
            $user->avatar = $request->getParsedBody()["avatar"];
            $userId = R::store($user);
            return $response->getBody()->write($userId);
            

        } 
    }
});

// get all models
$app->get('/api/all/{model_name}', function (Request $request, Response $response, array $args) {
    $model_name = $args['model_name'];
    $result = R::findAll($model_name);
    switch($model_name){
        case "fightclub":
        foreach($result as $fightclub){
            $fightclub->membercount = R::count('fighter', ' fightclubid = ' . $fightclub->id);
        }
        break;
    }
    return $response->getBody()->write(json_encode($result));
});


$app->delete('/api/deletemodel/{model_name}/{id}', function (Request $request, Response $response, array $args) {
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
            case "sponsor":
            $result = R::trash($object);
            return $response->getBody()->write(true);
        }
    }
    return $response->getBody()->write(false);
});


function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}