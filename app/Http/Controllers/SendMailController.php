<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aws\Ses\SesClient;
use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;
use PhpParser\Node\Expr\Cast\String_;

class SendMailController extends Controller
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

  private function getSESClient()
  {
    return new SesClient([
      'profile' => 'default',
      'version' => '2010-12-01',
      'region' => 'us-east-1'
    ]);
  }

  function listMails()
  {
    try {
      $result = $this->getSESClient()->listIdentities([
        'IdentityType' => 'EmailAddress',
      ]);
    } catch (AwsException $e) {
      // output error message if fails
      echo $e->getMessage();
      echo "\n";
    }
    //dd($result['Identities']);
    return $result['Identities'];
  }

  function addEmails(Request $request)
  {
    $addEmailConfirm = new \stdClass;
    $addEmailConfirm->emailConfirm = false;

    $emails = explode(",", $request->list_mail);

    try {
      for ($i = 0; $i < count($emails); $i++) {
        $this->getSESClient()->verifyEmailIdentity([
          'EmailAddress' => $emails[$i],
        ]);
      }
      $addEmailConfirm->emailConfirm = "Emails registred";
    } catch (AwsException $e) {
      // output error message if fails
      echo $e->getMessage();
      echo "\n";
    }

    return redirect()->route('home')->with(['addEmailConfirm' => $addEmailConfirm]);
  }

  function postMailSES(Request $request)
  {
    $message = new \stdClass;

    $message->status = true;
    $message->title = $request->title;
    $message->message = $request->message;

    // SES

    $toList = explode(", ", $request->to);

    $html_body = $message->message;
    $subject = $message->title;
    $plaintext_body = $message->message;
    $sender_email = $request->from;
    $char_set = 'UTF-8';

    try {
      $this->getSESClient()->sendEmail([
        'Destination' => [
          'ToAddresses' => $toList,
        ],
        'ReplyToAddresses' => [$sender_email],
        'Source' => $sender_email,
        'Message' => [

          'Body' => [
            'Html' => [
              'Charset' => $char_set,
              'Data' => $html_body,
            ],
            'Text' => [
              'Charset' => $char_set,
              'Data' => $plaintext_body,
            ],
          ],
          'Subject' => [
            'Charset' => $char_set,
            'Data' => $subject,
          ],
        ],
      ]);
      
      $notify = "Você recebeu um email em: " . strval($request->to);
      $this->sendNotification($notify);
    } catch (AwsException $e) {
      // output error message if fails
      dd($e->getMessage());
      echo "\n";
    }

    //dd($mails);
    return redirect()->route('home')->with(['message' => $message]);
  }

  function sendNotification($message){    
    // Get and display the platform applications
    $Model1 = $this->getSNSClient()->listPlatformApplications();

    // Get the Arn of the first application
    $AppArn = $Model1['PlatformApplications'][0]['PlatformApplicationArn'];

    // Get the application's endpoints
    $Model2 = $this->getSNSClient()->listEndpointsByPlatformApplication(array('PlatformApplicationArn' => $AppArn));

    // Send a message to each endpoint
    foreach ($Model2['Endpoints'] as $Endpoint)
    {
      $EndpointArn = $Endpoint['EndpointArn'];
      try
      {
        $this->getSNSClient()->publish(array('Message' => $message,
                            'TargetArn' => $EndpointArn));
      }
      catch (AwsException $e)
      {
        print($EndpointArn . " - Failed: " . $e->getMessage() . "!\n");
      }
    }
  }
}
