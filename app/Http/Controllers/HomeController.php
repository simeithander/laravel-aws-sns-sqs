<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aws\Sns\SnsClient;
use Aws\Sqs\SqsClient;
use Aws\Exception\AwsException;

class HomeController extends Controller
{
  private $profile = 'default';
  private $region = 'sa-east-1';
  private $queueUrl = "https://sqs.sa-east-1.amazonaws.com/675739880333/test-sqs";
  private $message;

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
  }

  private function setMessage($message)
  {
    $this->message = $message;
  }

  private function getMessage()
  {
    return $this->message;
  }

  private function getSQSClient()
  {
    return new SqsClient([
      'profile' => $this->profile,
      'region' => $this->region,
      'version' => '2012-11-05'
    ]);
  }

  private function getSNSClient()
  {
    return new SnsClient([
      'profile' => $this->profile,
      'region' => $this->region,
      'version' => '2010-03-31'
    ]);
  }

  function listSNSSubscriptions()
  {
    // Lista de emails das assinaturas
    try {
      $result = $this->getSNSClient()->listSubscriptions([]);
      // Lista de emails
      return Response()->json($result['Subscriptions']);
    } catch (AwsException $e) {
      // output error message if fails
      error_log($e->getMessage());
    }
  }

  function getSQSNotifications()
  {
    // Ler notificações SQS

    try {
      $result = $this->getSQSClient()->receiveMessage(array(
        'AttributeNames' => ['SentTimestamp'],
        'MaxNumberOfMessages' => 1,
        'MessageAttributeNames' => ['All'],
        'QueueUrl' => $this->queueUrl, // REQUIRED
        'WaitTimeSeconds' => 0,
      ));
      if (!empty($result->get('Messages'))) {
        $data = json_decode($result->get('Messages')[0]['Body'])->Message;
        $this->setMessage(json_decode($data)->notificationType);
        // Deleta a mensagem
        $result = $this->getSQSClient()->deleteMessage([
          'QueueUrl' => $this->queueUrl, // REQUIRED
          'ReceiptHandle' => $result->get('Messages')[0]['ReceiptHandle'] // REQUIRED
        ]);
      } else {
        return "No notifications";
      }
    } catch (AwsException $e) {
      // output error message if fails
      error_log($e->getMessage());
    }
  }

  /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Contracts\Support\Renderable
   */
  public function index()
  {
    $this->getSQSNotifications();
    //dd($this->getMessage());
    return view('home', ['notification' => $this->getMessage()]);
  }
}
