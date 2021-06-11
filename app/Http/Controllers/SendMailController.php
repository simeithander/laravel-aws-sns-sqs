<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aws\Ses\SesClient;
use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;
use Aws\Ses\Exception\SesException;
use PHPMailer\PHPMailer\PHPMailer;


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

    $request->validate([
      'file' => 'required|mimes:pdf,xlx,csv,png,jpg,jpeg|max:2048',
    ]);

    $fileName = time().'.'.$request->file->extension();  

    $request->file->move(public_path('uploads'), $fileName);

    $sender = $request->from;
    $sendername = 'Simei Thander';

    // Replace recipient@example.com with a "To" address. If your account
    // is still in the sandbox, this address must be verified.
    $recipient = $request->to;

    // Specify a configuration set.
    //$configset = 'ConfigSet';

    $subject = $message->title;

    $htmlbody = <<<EOD
    <html>
    <head></head>
    <body>
    $message->message
    </body>
    </html>
    EOD;

    $textbody = <<<EOD
    Hello,
    Please see the attached file for a list of customers to contact.
    EOD;

    // The full path to the file that will be attached to the email.
    $att = public_path('uploads').'/'.$fileName;

    // Create a new PHPMailer object.
    $mail = new PHPMailer;

    // Add components to the email.
    $mail->setFrom($sender, $sendername);
    $mail->addAddress($recipient);
    $mail->Subject = $subject;
    $mail->Body = $htmlbody;
    $mail->AltBody = $textbody;
    $mail->CharSet = 'UTF-8';
    $mail->addAttachment($att);
    //$mail->addCustomHeader('X-SES-CONFIGURATION-SET', $configset);

    // Attempt to assemble the above components into a MIME message.
    if (!$mail->preSend()) {
        echo $mail->ErrorInfo;
    } else {
        // Create a new variable that contains the MIME message.
        $message = $mail->getSentMIMEMessage();
    }

    // Try to send the message.
    try {
        $result = $this->getSESClient()->sendRawEmail([
            'RawMessage' => [
                'Data' => $message
            ]
        ]);
        // If the message was sent, show the message ID.
        $messageId = $result->get('MessageId');
        echo("Email sent! Message ID: $messageId" . "\n");
    } catch (SesException $error) {
        // If the message was not sent, show a message explaining what went wrong.
        echo("The email was not sent. Error message: "
            . $error->getAwsErrorMessage() . "\n");
    } 

    // SES

    /* $toList = explode(", ", $request->to);

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
    } */

    //dd($att);
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
        $this->getSNSClient()->publish(array('Message' => $message,'TargetArn' => $EndpointArn));
      }
      catch (AwsException $e)
      {
        print($EndpointArn . " - Failed: " . $e->getMessage() . "!\n");
      }
    }
  }
}
