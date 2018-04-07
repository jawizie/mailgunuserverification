<?php

#Mailgun Libraries
require 'vendor/autoload.php';
use Mailgun\Mailgun;
# Instantiate the client.
$mgClient = new Mailgun('apikey here');
$domain = "sandoboxdomain here";

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



