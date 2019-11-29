<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


global $authSecret;

$app->post('/api/login', function (Request $request, Response $response, array $args) {
    $userEmail = $request->getParsedBody()["email"];
    $userPassword = $request->getParsedBody()["password"];
    $authorizedUser = checkUserCredentials($userEmail, $userPassword);
    if(!empty($authorizedUser)){

        return $response->getBody()->write(json_encode(array(
            "username" => $authorizedUser->username,
            "token" => $authorizedUser->token,
            "id" => $authorizedUser->id,
        )));
    }else{
        return $response->getBody()->write(false);
    }
});

$app->post('/api/logout', function (Request $request, Response $response, array $args) {
    $userToken = !empty($request->getHeader('Bearer')) ? $request->getHeader('Bearer')[0] : '';
    $user = R::findOne("user", " token = ? ", [$userToken]);
    if($user){
        $user->token = '';
        R::store($user);
        
        return $response->getBody()->write(true);
    }
});

$app->get('/api/verifytoken', function(Request $request, Response $response) {
    $userToken = str_replace('"', "", $request->getHeader('Bearer')[0]);
    if(empty($userToken)){
        return $response->getBody()->write(json_encode(false));
    }
    $user = R::findOne("user", " token = ? ", [$userToken]);
    if($user){
        return $response->getBody()->write(json_encode(true));
    }else{
        return $response->getBody()->write(json_encode(false));
    }
});


function checkUserCredentials($email, $password){
    $user = R::findOne("user", " email = ? ", array($email));
    if(!empty($user)){
        if(password_verify($password, $user->password)){
            $newUserToken = generateRandomString(30);
            $user->token = $newUserToken;
            R::store($user);
            return $user;
        }
    }
    return false;
}
