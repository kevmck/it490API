#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

//Function below sorts requests to the API, and builds the links on the fly based on the information provided by the user.
function carData($request)
{
    //Try-catch block to catch any errors and send them to the logging server.
    try
    {
        $reqCount = count($request['param']);
        if ($reqCount == 0)
        {
            echo("Year request");
            return(file_get_contents("http://www.nhtsa.gov/webapi/api/Recalls/vehicle?format=json"));
        }
        else if ($reqCount == 1)
        {
            echo("Make request, given year");
            return(file_get_contents("http://www.nhtsa.gov/webapi/api/Recalls/vehicle/modelyear/".($request['param']['year'])."?format=json"));
        }
        else if ($reqCount == 2)
        {
            echo("Model request, given year and make");
            return(file_get_contents("http://www.nhtsa.gov/webapi/api/Recalls/vehicle/modelyear/".($request['param']['year'])."/make/".($request['param']['make'])."?format=json"));
        }
        else if ($reqCount == 3)
        {
            echo("Recall information, given year, make, model");
            return(file_get_contents("http://www.nhtsa.gov/webapi/api/Recalls/vehicle/modelyear/".($request['param']['year'])."/make/".($request['param']['make'])."/model/".($request['param']['model'])."?format=json"));
        }

        return ($reqCount);
    }
    
    catch (Exception $e)
        {
            /*$connection = new AMQPStreamConnection('192.168.2.10', 5672, 'test', 'test');
            $channel = $connection->channel();
            $channel->queue_declare('queueLog', false, true, false, false);
            $msg = new AMQPMessage($e);
            $channel->basic_publish($msg);
            $channel->close();
            $connection->close();*/
            
            $client = new rabbitMQClient("logRabbitMQ.ini", "testServer");
            
            $request = array();
            $request["type"] = "log";
            $request["message"] = $e->getMessage();
            $client->publish($request);
            
            echo ("\n\nException: ". $e->getMessage(). "\n");
        }
}


function requestProcessor($request)
{
    echo "\r\n\r\nreceived request".PHP_EOL;
    if(!isset($request['type']))
    {
        return "ERROR: unsupported message type";
    }
        switch ($request['type'])
    {
        case "apiRequest":
        return carData($request);
    }
    
    return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");
$server->process_requests('requestProcessor');
exit();
?>