<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aws\Sns\SnsClient;
use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

class PostController extends Controller
{
  private $profile = 'default';
  private $region = 'sa-east-1';

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
      'profile' => $this->profile,
      'region' => $this->region,
      'version' => '2010-12-01'
    ]);
  }

  function sendSNSNotification($message)
  {

    // Enviando Notificações ao tópico
    $topic = 'arn:aws:sns:sa-east-1:675739880333:teste-push';

    try {
      $this->getSNSClient()->publish([
        'Message' => $message,
        'TopicArn' => $topic,
      ]);
      //return Response()->json($result);
      return true;
    } catch (AwsException $e) {
      // output error message if fails
      error_log($e->getMessage());
      return false;
    }
  }

  // Send Mail

  function sendMail(Request $request)
  {
    // Envia ao AWS SES
    $html_body = '<h1>AWS Amazon Simple Email Service Test Email</h1>' .
      '<p>This email was sent with <a href="https://aws.amazon.com/ses/">' .
      'Amazon SES</a> using the <a href="https://aws.amazon.com/sdk-for-php/">' .
      'AWS SDK for PHP</a>.</p>';

    $subject = $request->title;
    $plaintext_body = $request->message;
    $sender_email = 'simeithander@gmail.com';
    $recipient_emails = ['simei.thander@academico.ifrn.edu.br'];
    $char_set = 'UTF-8';
    $configuration_set = 'ConfigSet';

    try {
      $result = $this->getSESClient()->sendEmail([
        'Destination' => [
          'ToAddresses' => $recipient_emails,
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
        // If you aren't using a configuration set, comment or delete the
        // following line
        'ConfigurationSetName' => $configuration_set,
      ]);
      //var_dump($result);
    } catch (AwsException $e) {
      // output error message if fails
      echo $e->getMessage();
      echo "\n";
    }

    // Retorna a mensagem a view

    $message = new \stdClass;

    $message->status = true;
    $message->title = $request->title;
    $message->message = $request->message;

    return redirect()->route('home')->with(['message' => $message]);
  }
}
