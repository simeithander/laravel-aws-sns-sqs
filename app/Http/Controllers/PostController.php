<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aws\Sns\SnsClient;
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

  function sendSNSNotification($message)
  {

    // Enviando Notificações ao tópico
    $topic = 'arn:aws:sns:sa-east-1:675739880333:teste-push';

    try {
      $this->getSNSClient()->publish([
        'Message' => 'Você recebeu uma mensagem: ' . $message,
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
    $message = new \stdClass;

    if ($this->sendSNSNotification($request->title)) {
      $message->status = true;
      $message->title = $request->title;
      $message->message = $request->message;
    } else $message->status = false;

    //dd(compact('message', 'notification'));
    return redirect()->route('home')->with(['message' => $message]);
  }
}
