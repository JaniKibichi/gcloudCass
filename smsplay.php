<?php

//get DB
require_once('smsDB.php');


//Receive POST from API
 $from = $_POST['from'];
 $to = $_POST['to'];
 $text = $_POST['text'];
 $date = $_POST['date'];
 $id = $_POST['id'];
 $linkId = $_POST['linkId'];
 $code = '20880';
 
 

//Check if a POST happened and if $text is empty 
if (isset($_POST['from'])){
// Be sure to include the file you've just downloaded
require_once('AfricasTalkingGateway.php');
require_once('config.php');

//check if phoneNumber is user from Users Table
                  $isQuery =$session->execute(new Cassandra\SimpleStatement(
		 "SELECT * FROM `".$usrTable."` WHERE `phoneNumber` LIKE '%".$from."%' LIMIT 1"));
             
                              
//Check is users details are in
//if not then register them - users first attempt
if(empty($isQuery) && empty($text)){
    // Create in USERS Table
          $secondUsrQuery = $session->execute(new Cassandra\SimpleStatement(
	"INSERT INTO `".$usrTable."` (`name`,`phoneNumber`,`gender`,`language`,`age`,`City`) 
	VALUES (NULL,'".$from."',NULL,NULL,NULL,NULL )" ));
         
                    
        //send SMS to get Name
            $recipients = $from;
            $message    = "What is your name? Jina lako? ";
            $gateway    = new AfricasTalkingGateway($username, $apikey);
            try { 
              $results = $gateway->sendMessage($recipients, $message, $code);
            }
            catch ( AfricasTalkingGatewayException $e ) {
              echo "Encountered an error while sending: ".$e->getMessage();
            }
        //check if all user fields are completed                                      
            
//if query is not empty and other user fields are empty
//new user who has not given us their details
}elseif($isQuery && isset($text) ){
    //update state of user
     if($isQuery['name']==NULL){                  
           //at least user is in the database
           //Update name and send SMS for age
            $thirdUdpateNameSql =$session->execute(new Cassandra\SimpleStatement(
		 "UPDATE `".$usrTable."` SET `name`='".$text."' WHERE `phoneNumber`=".$from." " ));
         
                    
           //send SMS to get Name
            $recipients = $from;
            $message    = "What is your age? Miaka yako? ";
            $gateway    = new AfricasTalkingGateway($username, $apikey);
            try { 
              $results = $gateway->sendMessage($recipients, $message, $code);
            }
            catch ( AfricasTalkingGatewayException $e ) {
              echo "Encountered an error while sending: ".$e->getMessage();
            }
        //check if all user fields are completed                  
        
        }elseif($isQuery['age']==NULL){            
           //at least user is in the database
           //Update age and send SMS for City
            $fourthAgeSql =$session->execute(new Cassandra\SimpleStatement(
		 "UPDATE `".$usrTable."` SET `age`='".$text."' WHERE `phoneNumber`=".$from." " ));
          
                    
           //send SMS to get Name
            $recipients = $from;
            $message    = "Hi".$isQuery['name']." What is your City? Mji yako? ";
            $gateway    = new AfricasTalkingGateway($username, $apikey);
            try { 
              $results = $gateway->sendMessage($recipients, $message, $code);
            }
            catch ( AfricasTalkingGatewayException $e ) {
              echo "Encountered an error while sending: ".$e->getMessage();
            }
        //check if all user fields are completed    
        }elseif($isQuery['City']==NULL){                  
           //at least user is in the database
           //Update City and send SMS for gender
            $fifthAgeSql =$session->execute(new Cassandra\SimpleStatement(
		 "UPDATE `".$usrTable."` SET `City`='".$text."' WHERE `phoneNumber`=".$from." " )); 
            
                    
           //send SMS to get Name
            $recipients = $from;
            $message    = "Male or Female? Jinsia yako? ";
            $gateway    = new AfricasTalkingGateway($username, $apikey);
            try { 
              $results = $gateway->sendMessage($recipients, $message, $code);
            }
            catch ( AfricasTalkingGatewayException $e ) {
              echo "Encountered an error while sending: ".$e->getMessage();
            }
        //check if all user fields are completed                  
        
        }elseif($isQuery['gender']=NULL){                  
           //at least user is in the database
           //Update gender and send SMS for age
            $sixthGenderSql = $session->execute(new Cassandra\SimpleStatement(
		"UPDATE `".$usrTable."` SET `gender`='".$text."' WHERE `phoneNumber`=".$from." " ));
            
                    
           //send SMS to get Name
            $recipients = $from;
            $message    = "Swahili(jibu 1) ama/or english(reply 2) ? ";
            $gateway    = new AfricasTalkingGateway($username, $apikey);
            try { 
              $results = $gateway->sendMessage($recipients, $message, $code);
            }
            catch ( AfricasTalkingGatewayException $e ) {
              echo "Encountered an error while sending: ".$e->getMessage();
            }
        //check if all user fields are completed             
        
        }elseif($isQuery['language']=NULL){                  
           //at least user is in the database
           //Update language and send welcome SMS
            $seventhLangSql = $session->execute(new Cassandra\SimpleStatement
	"UPDATE `".$usrTable."` SET `language`='".$text."' WHERE `phoneNumber`=".$from." "));
            
                    
           //send SMS to get Name
            $recipients = $from;
            $message    = "Thanks for registering on Mjuaji.com. Do you know? SMS 1 to 20880 to find out! ";
            $gateway    = new AfricasTalkingGateway($username, $apikey);
            try { 
              $results = $gateway->sendMessage($recipients, $message, $code);
            }
            catch ( AfricasTalkingGatewayException $e ) {
              echo "Encountered an error while sending: ".$e->getMessage();
            }
        //check if all user fields are completed                  
        }
//if query is not empty and all user fields are filled and text is empty send questions and update waiting to true
//User is returning to receive a question
}elseif($isQuery && $isQuery['language']!=NULL && empty($text)){ //I.E all personal details are up to date...
  //{correct}     //Query for random qn from Questions table where answer is null and phoneNumber!=$phoneNumber
                  $eighthAnsQuery = $session->execute(new Cassandra\SimpleStatement(
			"SELECT * FROM `".$ansTable."` WHERE `phoneNumber` NOT LIKE '%".$from."%' 
			ORDER BY RAND() LIMIT 1"));
                      
    //If successful, send Question, create phoneNumber and make waiting true
    if($eighthAnsQuery){
            //Send this question
            $messageAns = $eighthAnsQuery['qn'];
            //send SMS to get Age
            $recipients = $from;
            $message    = $messageAns;
            $waiting = true;
            $gateway    = new AfricasTalkingGateway($username, $apikey);
            try { $results = $gateway->sendMessage($recipients, $message, $code); }
            catch ( AfricasTalkingGatewayException $e ) {  echo "Encountered an error while sending: ".$e->getMessage(); }
            
            //Create a new record with the phoneNumber and waiting is true
            $ninthAddQuery = $session->execute(new Cassandra\SimpleStatement(
		"INSERT INTO `".$ansTable."` (`qn`,`phoneNumber`,`ans`,`waiting`) 
		VALUES (NULL,'".$messageAns."','.$from.','.$waiting.')"));
            

    }
//if query is not empty and all user fields are filled and text is not empty 
//Update ans with $text and waiting to false and send questions
//User is answering last question
}elseif($isQuery && $isQuery['language']!=NULL && isset($text)){
            $waiting = true;
            $notwaiting = false;
    //Update last question with $text where phoneNumber = $phoneNumber and waiting = 'TRUE'
    $tenthWaitSql = $session->execute(new Cassandra\SimpleStatement(
	"UPDATE `".$ansTable."` SET `waiting`='".$notwaiting."', `ans`='".$text."' 
	WHERE `phoneNumber`='".$from."' AND `waiting`='".$waiting."' "));

            //Next
            //Query for random qn from Questions table where phoneNumber!=$phoneNumber
                  $eleventhAnsQuery =$session->execute(new Cassandra\SimpleStatement(
		 "SELECT * FROM `".$ansTable."` WHERE `phoneNumber` NOT LIKE '%".$from."%' ORDER BY RAND() LIMIT 1"));
    
            //If successful, send Question, create phoneNumber and make waiting true
            if($eleventhAnsQuery){
            //Send this question
            $messageAns = $eleventhAnsQuery['qn'];
            //send SMS to get Age
            $recipients = $from;
            $message    = $messageAns;
            $waiting = true;
            $gateway    = new AfricasTalkingGateway($username, $apikey);
            try { $results = $gateway->sendMessage($recipients, $message, $code); }
            catch ( AfricasTalkingGatewayException $e ) {  echo "Encountered an error while sending: ".$e->getMessage(); }
            }
//Create a new record with the phoneNumber and waiting is true
$twelfthAddQuery = $selector->execute(new Cassandra\SimpleStatement(
" INSERT INTO `'.$ansTable.'` (`qn`,`phoneNumber`,`ans`,`waiting`) 
VALUES (NULL,'".$messageAns."','".$from."','".$waiting."')"
));

}

}

?>
