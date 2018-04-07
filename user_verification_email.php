<?php
session_start();
error_reporting(0);
include 'config.php';

#Mailgun Libraries
require '../../../../vendor/autoload.php';
use Mailgun\Mailgun;
# Instantiate the client.
$mgClient = new Mailgun('key-53a546688bce3bdf0f7ad7d147039f23');
$domain = "letsgo.co.zw";

# email address post from form
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$email = strtolower($request->email);
$user = $_SESSION['current_user'];
# hash for user verification
$hash = md5( rand(0,1000));
$status = 'FALSE';

if($email != ''){
    $user_id = $_SESSION['logged_in'];
    global  $db;
    //update email address and status of email verification
    $sql ="UPDATE users SET email=?,hash=?,email_verified=? WHERE user_id =?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ssss",  $email,$hash,$status,$user_id);

    if ($stmt->execute()) {
        //email message with activation hash
        $message = '
            ------------------------
            Hi '.$user['name'].' !
            ------------------------
        
            You are a step closer to verifying your account.        
            Please click this link to activate your account: http://www.letsgo.co.zw/user/email-verification.php?verification_token='.$user_id.'&actif='.$hash.'
        ';
        //send email with mailgun
        # Make the call to the client.
        $result = $mgClient->sendMessage("$domain",
            array('from'    => 'Lets Go ZW <no-reply@letsgo.co.zw>',
                'to'      => $email,
                'subject' => 'Email Verification',
                'text'    => $message));
       /* $queryString = array(
            'begin'        => 'Fri, 3 May 2013 09:00:00 -0000',
            'ascending'    => 'yes',
            'limit'        =>  25,
            'pretty'       => 'yes',
            'subject'      => 'test'
        );

        # Make the call to the client.
        $result = $mgClient->get("$domain/events", $queryString);*/

        # You can see a record of this email in your logs: https://mailgun.com/app/logs .
        echo 'Message sent';
        # You can send up to 300 emails/day from this sandbox server.
        # Next, you should add your own domain so you can send 10,000 emails/month for free.

    } else {
        //update failed
        echo "Transaction cannot be processed at the moment !";
    }


}else{
    echo "Your Input is invalid ....";
}



