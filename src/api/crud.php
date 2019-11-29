<?php 
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\UploadedFile;

$app->post('/api/addmodel/{model_name}', function (Request $request, Response $response, array $args) {
    $model_name = $args['model_name'];
    if(!empty($request->getParsedBody()['id'])){ //update model
        $id = $request->getParsedBody()['id'];
        $object = R::load($model_name, $id);
        switch($model_name){
            case "fighter":
            $object->firstname = !empty($request->getParsedBody()['firstname']) ? $request->getParsedBody()['firstname'] : '';
            $object->lastname =  !empty($request->getParsedBody()['lastname']) ? $request->getParsedBody()['lastname']  : '';
            $object->nickname =  !empty($request->getParsedBody()['nickname']) ? $request->getParsedBody()['nickname']  : '';
            $object->bio =  !empty($request->getParsedBody()['bio']) ? $request->getParsedBody()['bio']  : '';
            $object->weight =  !empty($request->getParsedBody()['weight']) ? $request->getParsedBody()['weight']  : '';
            $object->height =  !empty($request->getParsedBody()['height']) ? $request->getParsedBody()['height']  : '';
            $object->gender =  !empty($request->getParsedBody()['gender']) ? $request->getParsedBody()['gender']  : '';
            $object->birthdate =  !empty($request->getParsedBody()['birthdate']) ? $request->getParsedBody()['birthdate']  : '';
            $object->country =  !empty($request->getParsedBody()['country']) ? $request->getParsedBody()['country']  : '';
            $object->image =  !empty($request->getParsedBody()['image']) ? $request->getParsedBody()['image']  : '';
            $object->facebookurl =  !empty($request->getParsedBody()['facebookurl']) ? $request->getParsedBody()['facebookurl']  : '';
            $object->twitterurl =  !empty($request->getParsedBody()['facebookurl']) ? $request->getParsedBody()['facebookurl']  : '';
            $object->instagramurl =  !empty($request->getParsedBody()['instagramurl']) ? $request->getParsedBody()['instagramurl']  : '';
            $object->fightclubid = "";
            $fighterid = R::store($object);
            return $response->getBody()->write($fighterid);
            case "fightclub":
            $object->name = !empty($request->getParsedBody()['name']) ? $request->getParsedBody()['name'] : '';
            $object->city = !empty($request->getParsedBody()['city']) ? $request->getParsedBody()['city'] : '';
            $object->address = !empty($request->getParsedBody()['address']) ? $request->getParsedBody()['address'] : '';
            $object->website = !empty($request->getParsedBody()['website']) ? $request->getParsedBody()['website'] : '';
            $object->email = !empty($request->getParsedBody()['email']) ? $request->getParsedBody()['email'] : '';
            $object->phone = !empty($request->getParsedBody()['phone']) ? $request->getParsedBody()['phone'] : '';
            $object->country = !empty($request->getParsedBody()['country']) ? $request->getParsedBody()['country'] : '';
            $object->image = !empty($request->getParsedBody()['image']) ? $request->getParsedBody()['image'] : '';
            $fightclubid = R::store($object);
            return $response->getBody()->write($fightclubid);
            case "tournament":
            $object->name = !empty($request->getParsedBody()['name']) ? $request->getParsedBody()['name'] : '';
            $object->startdate = !empty($request->getParsedBody()['startdate']) ? $request->getParsedBody()['startdate'] : '';
            $object->starttime = !empty($request->getParsedBody()['starttime']) ? $request->getParsedBody()['starttime'] : '';
            $object->enddate = !empty($request->getParsedBody()['enddate']) ? $request->getParsedBody()['enddate'] : '';
            $object->endtime = !empty($request->getParsedBody()['endtime']) ? $request->getParsedBody()['endtime'] : '';
            $object->city = !empty($request->getParsedBody()['city']) ? $request->getParsedBody()['city'] : '';
            $object->address = !empty($request->getParsedBody()['address']) ? $request->getParsedBody()['address'] : '';
            $object->description = !empty($request->getParsedBody()['description']) ? $request->getParsedBody()['description'] : '';
            $tournamentid = R::store($object);
            if(!empty($request->getParsedBody()['tournament_fights'])){
                $tournamentFights = $request->getParsedBody()['tournament_fights'];
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
            $object->name = !empty($request->getParsedBody()["name"]) ? $request->getParsedBody()["name"] : '';
            $object->url = !empty($request->getParsedBody()["url"]) ? $request->getParsedBody()["url"] : '';
            $object->logo = !empty( $request->getParsedBody()["logo"]) ? $request->getParsedBody()["logo"] : '';
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
            case "weightclass":
            $object->name = !empty($request->getParsedBody()["name"]) ? $request->getParsedBody()["name"] : '';
            $weightclassId = R::store($object);
            return $response->getBody()->write($weightclassId);
            

            default: return $response->getBody()->write(false);

        }
    }else{
        switch($model_name){
            case "fighter":
            $fighter = R::dispense('fighter');
            $fighter->firstname = !empty($request->getParsedBody()['firstname']) ? $request->getParsedBody()['firstname'] : '';
            $fighter->lastname =  !empty($request->getParsedBody()['lastname']) ? $request->getParsedBody()['lastname']  : '';
            $fighter->nickname =  !empty($request->getParsedBody()['nickname']) ? $request->getParsedBody()['nickname']  : '';
            $fighter->bio =  !empty($request->getParsedBody()['bio']) ? $request->getParsedBody()['bio']  : '';
            $fighter->weight =  !empty($request->getParsedBody()['weight']) ? $request->getParsedBody()['weight']  : '';
            $fighter->height =  !empty($request->getParsedBody()['height']) ? $request->getParsedBody()['height']  : '';
            $fighter->gender =  !empty($request->getParsedBody()['gender']) ? $request->getParsedBody()['gender']  : '';
            $fighter->birthdate =  !empty($request->getParsedBody()['birthdate']) ? $request->getParsedBody()['birthdate']  : '';
            $fighter->country =  !empty($request->getParsedBody()['country']) ? $request->getParsedBody()['country']  : '';
            $fighter->image =  !empty($request->getParsedBody()['image']) ? $request->getParsedBody()['image']  : '';
            $fighter->facebookurl =  !empty($request->getParsedBody()['facebookurl']) ? $request->getParsedBody()['facebookurl']  : '';
            $fighter->twitterurl =  !empty($request->getParsedBody()['twitterurl']) ? $request->getParsedBody()['twitterurl']  : '';
            $fighter->instagramurl =  !empty($request->getParsedBody()['instagramurl']) ? $request->getParsedBody()['instagramurl']  : '';
            $fighter->fightclubid = "";
            
            $fighterid = R::store($fighter);
            return $response->getBody()->write($fighterid);
            case "fightclub":
            $fightclub = R::dispense('fightclub');
            $fightclub->name = !empty($request->getParsedBody()['name']) ? $request->getParsedBody()['name'] : '';
            $fightclub->city = !empty($request->getParsedBody()['city']) ? $request->getParsedBody()['city'] : '';
            $fightclub->address = !empty($request->getParsedBody()['address']) ? $request->getParsedBody()['address'] : '';
            $fightclub->website = !empty($request->getParsedBody()['website']) ? $request->getParsedBody()['website'] : '';
            $fightclub->email = !empty($request->getParsedBody()['email']) ? $request->getParsedBody()['email'] : '';
            $fightclub->phone = !empty($request->getParsedBody()['phone']) ? $request->getParsedBody()['phone'] : '';
            $fightclub->country = !empty($request->getParsedBody()['country']) ? $request->getParsedBody()['country'] : '';
            $fightclub->image = !empty($request->getParsedBody()['image']) ? $request->getParsedBody()['image'] : '';
            $fightclubid = R::store($fightclub);
            return $response->getBody()->write($fightclubid);
            case "tournament":
            $tournament = R::dispense('tournament');
            $tournament->name = !empty($request->getParsedBody()['name']) ? $request->getParsedBody()['name'] : '';
            $tournament->startdate = !empty($request->getParsedBody()['startdate']) ? $request->getParsedBody()['startdate'] : '';
            $tournament->starttime = !empty($request->getParsedBody()['starttime']) ? $request->getParsedBody()['starttime'] : '';
            $tournament->enddate = !empty($request->getParsedBody()['enddate']) ? $request->getParsedBody()['enddate'] : '';
            $tournament->endtime = !empty($request->getParsedBody()['endtime']) ? $request->getParsedBody()['endtime'] : '';
            $tournament->city = !empty($request->getParsedBody()['city']) ? $request->getParsedBody()['city'] : '';
            $tournament->address = !empty($request->getParsedBody()['address']) ? $request->getParsedBody()['address'] : '';
            $tournament->description = !empty($request->getParsedBody()['description']) ? $request->getParsedBody()['description'] : '';
            $tournament->image = !empty($request->getParsedBody()['image']) ? $request->getParsedBody()['image'] : '';
            $tournamentid = R::store($tournament);
            if(!empty($request->getParsedBody()['tournament_fights'])){
                $tournamentFights = $request->getParsedBody()['tournament_fights'];
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
            $sponsor->name = !empty($request->getParsedBody()["name"]) ? $request->getParsedBody()["name"] : '';
            $sponsor->url = !empty($request->getParsedBody()["url"]) ? $request->getParsedBody()["url"] : '';
            $sponsor->logo = !empty($request->getParsedBody()["logo"]) ? $request->getParsedBody()["logo"] : '';
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
            case "weightclass":
            $weightclass = R::dispense('weightclass');
            $weightclass->name = !empty($request->getParsedBody()["name"]) ? $request->getParsedBody()["name"] : '';
            $weightclassId = R::store($weightclass);
            return $response->getBody()->write($weightclassId);
            

        } 
    }
});

// get single model by param
$app->get('/api/search/{model_name}/{param_name}/{param_value}', function (Request $request, Response $response, array $args) {
    $model_name = $args['model_name'];
    $param_name = $args['param_name'];
    $param_value = $args['param_value'];
    $result = R::find($model_name, $param_name . ' LIKE ? ', array('%'.$param_value.'%'));
    return $response->getBody()->write(json_encode($result));
});

//get fighter profile data
$app->get('/api/fighterprofile/{fighterid}', function (Request $request, Response $response, array $args) {
    $fighter_id = $args['fighterid'];
    $fighter = R::find('fighter', ' id = ? ', array($fighter_id));
    $fighter = reset($fighter);
    $fightClub = R::find('fightclub', ' id = ? ', array($fighter->fightclubid));
    if(!empty($fightClub)){
        $fightClub = reset($fightClub);
        $fighter->fightclub = $fightClub->export();
    }
    $fightContentants = R::findAll('fightcontestant', ' fighterid = ? ', array($fighter_id));
    $relatedFights = array();
    foreach($fightContentants as $fightContestant){
        $relatedFight = R::find('tournamentfight', ' id = ? ', array($fightContestant->tournamentfightid));
        $relatedFight = reset($relatedFight);
        if(!empty($relatedFight)){
            $relatedVideo = R::find('channelvideo', ' videoid = ? ', array($relatedFight->videourl));
            $relatedVideo = reset($relatedVideo);
            $relatedFight->relatedvideo = $relatedVideo->export();
            $relatedFights[] = $relatedFight->export();
        }
    }
    $fighter->relatedfights = $relatedFights;
    return $response->getBody()->write(json_encode($fighter->export()));
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
        case "channelvideo":
            foreach($result as $channelVideo){
                $relatedFight = R::find('tournamentfight', ' videourl = ? ', array($channelVideo->videoid));
                if(!empty($relatedFight)){
                    $channelVideo->tournamentfight = reset($relatedFight);
                    $tournament = R::find('tournament', ' id = ? ', array(reset($relatedFight)->tournamentid));
                    $channelVideo->tournament = reset($tournament)->export();
                    $fightContestants = R::findAll('fightcontestant', ' tournamentfightid = ? ', array(reset($relatedFight)->id));
                    $channelVideo->relatedfighters = array();
                    foreach($fightContestants as $fightContestant){
                        $relatedFighter = R::find('fighter', ' id = ? ', array($fightContestant->fighterid));
                        $relatedFighter = reset($relatedFighter);
                        $fightclub = R::find('fightclub', ' id = ? ', array($relatedFighter->fightclubid));
                        $relatedFighter->fightclub = reset($fightclub);
                        $channelVideo->relatedfighters[] = $relatedFighter->export();
                    }
                }
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