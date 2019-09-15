<?php 
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


//endpoint for storing page configuration list.
// page_name as query param
// body as json of paramname: paramvalue pairs 
$app->post('/api/pageconfig/{page_name}', function (Request $request, Response $response, array $args) {
    $page_name = $args['page_name'];
    $page_configs = $request->getParsedBody();
    foreach($page_configs as $configname => $configvalue){
        $pageConfiguration = R::findOne('pageconfiguration', ' page = ? AND paramname = ? ', [$page_name, $configname] );
        if(!empty($pageConfiguration)){
            $pageConfiguration->paramvalue = $configvalue;
            R::store($pageConfiguration);
        }else{
            $pageConfiguration = R::dispense('pageconfiguration');
            $pageConfiguration->paramname = $configname;
            $pageConfiguration->paramvalue = $configvalue;
            $pageConfiguration->page = $page_name;
            R::store($pageConfiguration);
        }
    }
    return $response->getBody()->write(true);
});  

//get page configuration list by page name
$app->get('/api/pageconfig/{page_name}', function(Request $request, Response $response, array $args){
    $page_name = $args['page_name'];
    $result = R::findAll('pageconfiguration', ' page = ? ', [$page_name]);
    $formattedConfigList = array();
    foreach($result as $r){
        $formattedConfigList[$r->paramname] = $r;
    }
    return $response->getBody()->write(json_encode($formattedConfigList));
});