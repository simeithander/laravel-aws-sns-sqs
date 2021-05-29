<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;

class NotificationsController extends Controller
{
  private $profile = 'default';
  private $region = 'us-east-1';
  private $platformApplicationArn = 'arn:aws:sns:us-east-1:675739880333:app/GCM/Firebase';

  private function getSNSClient()
  {
    return new SnsClient([
    'profile' => $this->profile,
    'region' => $this->region,
    'version' => '2010-03-31'
    ]);
  }

  function update(Request $request){

    $op = $request->op;
    $token = $request->token;
    $endpointArn = $request->arn;
    $enabled = 'true';

    //Disable Endpoint
    if(isset($op) == "disable") $enabled = 'false';
    
    
    if(isset($endpointArn)){

      try {

        $result = $this->getSNSClient()->getEndpointAttributes(array(
          // EndpointArn is required
          'EndpointArn' => $endpointArn,  
        ));

        if(isset($token)){
          // Update Token in Endpoint
          try {
            $result = $this->getSNSClient()->setEndpointAttributes(array(
              // PlatformApplicationArn is required
              'EndpointArn' => $endpointArn,
              // Token is required
              'Token' => $token,
              'Enabled' => $enabled,
              'Attributes' => array(
                  // Associative array of custom 'String' key names
                  'Token' => $token,
                  'Enabled' => $enabled,
                  // ... repeated
              ),
            ));

            return ['statusCode' => 200, 'message' => "Updated token"];

          }catch (AwsException $e) {
            // output error message if fails
            return $e->getMessage();
          }
        }else{

          return ['statusCode' => 406, 'message' => 'Token not sent'];
          
        };
      } catch (AwsException $e) {
        // output error message if fails
        return $e->getMessage();
      }
    }else{
      return ['statusCode' => 406, 'message' => 'ARN not sent'];
    }
  }

  function store(Request $request){
    
    $token = $request->token;
    
    if(isset($token)){
      // Create a new PLatform Endpoint if not exist
      try {
        $result = $this->getSNSClient()->createPlatformEndpoint(array(
          // PlatformApplicationArn is required
          'PlatformApplicationArn' => $this->platformApplicationArn,
          // Token is required
          'Token' => $token,
          'Attributes' => array(
              'Token' => $token,
          ),
        ));
        return ['status' => 201, 'EndpointArn'=> $result['EndpointArn']];

      }catch (AwsException $e) {
        // output error message if fails
        return $e->getMessage();
      }
    }else{
      return ['statusCode' => 406, 'message' => 'Token not sent'];
    }
  }

  function delete(Request $request){
    $arn = $request->arn;

    if(isset($arn)){

      try{

        $this->getSNSClient()->deleteEndpoint([
          'EndpointArn' => $arn, // REQUIRED
        ]);

        return ['statusCode' => 200, 'message' => 'endpoint successfully removed'];

      }catch (AwsException $e){
        return ['statusCode' => 404, 'message' => $e->getMessage()];
      }

    }else{
      return ['statusCode' => 406, 'message' => 'ARN not sent'];
    }
  }
}
