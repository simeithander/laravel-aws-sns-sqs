<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;

class MobilePushController extends Controller
{
  private $profile = 'default';
  private $region = 'us-east-1';

  private function getSNSClient()
  {
    return new SnsClient([
    'profile' => $this->profile,
    'region' => $this->region,
    'version' => '2010-03-31'
    ]);
  }

  function sendNotification(){    
    // Get and display the platform applications
    print("List All Platform Applications:\n");
    $Model1 = $this->getSNSClient()->listPlatformApplications();
    foreach ($Model1['PlatformApplications'] as $App)
    {
      print($App['PlatformApplicationArn'] . "\n");
    }
    print("\n");

    // Get the Arn of the first application
    $AppArn = $Model1['PlatformApplications'][0]['PlatformApplicationArn'];

    // Get the application's endpoints
    $Model2 = $this->getSNSClient()->listEndpointsByPlatformApplication(array('PlatformApplicationArn' => $AppArn));

    // Display all of the endpoints for the first application
    print("List All Endpoints for First App:\n");
    foreach ($Model2['Endpoints'] as $Endpoint)
    {
      $EndpointArn = $Endpoint['EndpointArn'];
      print($EndpointArn . "\n");
    }
    print("\n");

    // Send a message to each endpoint
    print("Send Message to all Endpoints:\n");
    foreach ($Model2['Endpoints'] as $Endpoint)
    {
      $EndpointArn = $Endpoint['EndpointArn'];
      try
      {
        $this->getSNSClient()->publish(array('Message' => 'Hello from PHP',
                            'TargetArn' => $EndpointArn));
        print($EndpointArn . " - Succeeded!\n");
      }
      catch (AwsException $e)
      {
        print($EndpointArn . " - Failed: " . $e->getMessage() . "!\n");
      }
    }
  }

  function createPlataformEndpoint(){
    try
    {
      $result = $this->getSNSClient()->createPlatformEndpoint(array(
        // PlatformApplicationArn is required
        'PlatformApplicationArn' => 'arn:aws:sns:us-east-1:675739880333:app/GCM/Firebase',
        // Token is required
        'Token' => 'fAxlcQKSSdKuzNushj2kEY:APA91bFkbciQy19wK4phmYuVEl3LvsbikD_gy5IuKTzmHbfC5cwOEnjkg5xyOSnBONEdopXZ3j7NIPJFVpocIk6_Ba_RrfxJoDbtruVt5aUZVJt31vyeCvEDQ5o3PdoLY5wfiML6Elqh',
    ));
      dd($result);
    }
    catch (AwsException $e)
    {
      print($e->getMessage() . "!\n");
    }
  
  }
}
