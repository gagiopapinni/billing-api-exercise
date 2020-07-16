<?php

require '../vendor/autoload.php';

require '../src/config.php';
require '../src/utils.php';


$app = new \Slim\App();

$container = $app->getContainer();

$container['view'] = function ($container) {
    return new \Slim\Views\PhpRenderer('../template/');
};



$app->get('/payments/card/transactions', function ($request, $response) {
    $params = $request->getQueryParams();
    if(!($params['from'] ?? FALSE) || !($params['to'] ?? FALSE))
        return $response->withHeader("Content-Type","application/json")
                        ->write("{error:{text:'incomplete arguments'}}");
    
    $sql = "SELECT * FROM transactions WHERE timestamp >= ".$params['from']." AND timestamp <= ".$params['to'];

    try{

        $db = dbConnection();
        $stmt = $db->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        if(!$result) $result = array();

        return $response->withHeader("Content-Type","application/json")
                        ->write( json_encode($result) );

    } catch(PDOException $e){
        return $response->withHeader("Content-Type","application/json")
                        ->write( "{error:{text:unknown}}");
    }

});


$app->get('/', function ($request, $response) {
    return $this->view->render($response, 'landing.html',  [ 'action' => "/register" ]);

});

$app->get('/payments/card/transaction/success', function ($request, $response) {
    return $this->view->render($response, 'success.html');

});

$app->get('/payments/card/transaction/failure', function ($request, $response) {
    return $this->view->render($response, 'failure.html');

});

$app->post('/payments/card/transaction', function ($request, $response) {
    $params = $request->getParams();
    $status = "failure";
    $sql = "SELECT timestamp FROM transactions WHERE sessionID = '".$params["sessionID"]."'";
    try{

        $db = dbConnection();
        $stmt = $db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_OBJ);

        $now = (new DateTime())->getTimestamp();
        $timeDiffMins = (int)(($now - $result->timestamp)/60);

        if($params["cardNumber"] && luhnAlgorithm($params["cardNumber"]) && $timeDiffMins < 30){

              $status = "success";
              $sql = "UPDATE transactions SET completed=1 WHERE sessionID='".$params["sessionID"]."'";
              $stmt= $db->prepare($sql);
              $stmt->execute();
         }
        
    } catch(PDOException $e){
        
        return $response->withHeader("Content-Type","application/json")
                        ->write( "{error:{text:unknown}}");
    }
    

    return $response->withRedirect( "/payments/card/transaction/".$status, 303);
   
})->setName('card_transaction');

$app->get('/payments/card/form', function ($request, $response) {
    $params = $request->getQueryParams();
    $sql = "SELECT * FROM transactions WHERE sessionID = '".$params["sessionID"]."'";

    try{

        $db = dbConnection();
        $stmt = $db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_OBJ);

        if($result){
            if($result->completed) 
                return $response->withRedirect( "/payments/card/transaction/success", 303);

            return $this->view->render($response, 'transaction.html', [ 'target' => $result->target,
                                                                        'amount' => $result->amount, 
                                                                        'sessionID' => $params["sessionID"],
                                                                        'action' => $this->router->pathFor('card_transaction') ]);
        }else{
            return $response->withHeader("Content-Type","application/json")
                            ->write( "{error:{text:'unknown transaction. bad session id'}}");

        }
    } catch(PDOException $e){

        return $response->withHeader("Content-Type","application/json")
                        ->write( "{error:{text:unknown}}");
    }

    
   
})->setName('card_form');




$app->post('/register', function ($request, $response) {
    $params = $request->getParams();
    //todo: check param types
    $sessionID = uniqid("a",TRUE);
    $date = new DateTime('NOW');

    $sql = "INSERT INTO transactions (target,amount,sessionID,timestamp) VALUES(:t,:a,:s,:ts)";

    try{

        $db = dbConnection();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':t', $params["target"]);
        $stmt->bindParam(':a', $params["amount"]);
        $stmt->bindParam(':s', $sessionID);
        $stmt->bindParam(':ts', $date->getTimestamp() );
        $stmt->execute();
        
        return $response->withRedirect( $this->router->pathFor('card_form') . "?sessionID=" . $sessionID, 303);



    } catch(PDOException $e){

        return $response->withHeader("Content-Type","application/json")
                        ->write( "{error:{text:unknown}}");
    }

   
});


$app->run();











