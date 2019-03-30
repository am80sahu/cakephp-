<?php
App::uses('Component', 'Controller');
App::import('Vendor', 'phpmailer', array('file' => 'phpmailer'.DS.'PHPMailerAutoload.php'));
/**
 *
 * @author vivekshukla
 *        
**/

 
class EmailComponent extends Component {

    var $uses = array('EmailTemplate','SiteSetting');

    /**
     * function : sendMailContent()
     * params : $receiverEmail : User full name.
     * params : $senderEmail : Sender email address.
     * params : $subject : Subject line for email.
     * params : $message : Actual contents to send to user.
     * description : This function is use to send mail to user.
     */
	
    function sendMailContent($receiverEmail,$password) {
  //require 'phpmailer/PHPMailerAutoload.php';
		App::import('Vendor', 'Vendor/phpmailer/PHPMailerAutoload.php');
		$mail = new PHPMailer;

		//$mail->SMTPDebug = 3;                               // Enable verbose debug output

		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'mail.ezeegst.online';  // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'support@ezeegst.online';                 // SMTP username
		$mail->Password = 'ezeegst123';                           // SMTP password
		//$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
		$mail->Port = 25;                                    // TCP port to connect to

		$mail->setFrom('support@ezeegst.online', 'EZEE GST');
		$mail->addAddress($receiverEmail, '');     // Add a recipient
		//$mail->addAddress('am80.sahu@gmail.com', 'Joe User');     // Add a recipient
		//$mail->addAddress('ellen@example.com');               // Name is optional
		//$mail->addReplyTo('amit.tantransh@gmail.com', 'Information');
		//$mail->addCC('rajeev.tantransh@gmail.com');
		//$mail->addBCC('amit.tantransh@gmail.com');

		//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
		//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
		$mail->isHTML(true);                                  // Set email format to HTML

		$mail->Subject = 'Registred Successfully';
		$mail->Body    = '
		<div class="section" style="max-width:550px;width:100%;float:left;border:1px solid #ccc;background-color:rgb(24, 57, 126);color:#fff">
		<div style="width:100%;float:left;height:70px;border-bottom:1px solid #254589;padding-top:10px">
		<img src="http://ezeegst.online/app/webroot/images/logo.png" style="width:150px;height:60px">
		<img src="http://tantranshsolutions.com/img/logo.png" style="width:150px;height:60px;float:right;margin-right:7px">
		</div>
		<div style="width:100%;float:left;height:430px; padding:7px 10px;">
		<h1 style="color:#2ebc48;">Welcome to ezeegst</h1>
		<p >To log in to ezeegst software just click Login and then enter your email address and password.</p>
		<p style="color:#2ebc48;">Use the following values when prompted to log in:</p>
		<p>
		Email: '.$receiverEmail.'<br>
		Password: '.$password.' <br>
		Link: http://www.ezeegst.online 
		</p>
		<p>
		When you log in to your account, you will be able to do the following:<br>
		</p>
		<ul>
		<li>Item master/ Vendor master creation</li>
		<li>Purchase/Sale</li>
		<li>Stock </li>
		<li>Reports</li>
		<li>Account/vouchers</li>
		<li>Make changes to your account information </li>
		<li>Change your password</li>
		</ul>
		
		

		</div>

		<div style="width:100%;float:left;height:70px;background-color:#2ebc48;box-shadow:0px 0px 0px #fff;">
		<p style="padding:7px 10px;">If you have any questions, please feel free to contact us at support@ezeegst.online or by phone at 8087142686. </p>
		</div>
		</div>';
		$mail->AltBody = '';

		if(!$mail->send()) {
		//return true;
		echo 'Message could not be sent.';
		echo 'Mailer Error: ' . $mail->ErrorInfo;
		} else {
		//	 return false;
		echo 'Message has been sent';
		}
		
    }
	
	/************************************************Order Cancel mail to Customer****************************/
	
	  function sendRegSuceesCustomer($emailData = array()) {
        (isset($emailData ['receiver_email'])) ? $receiverEmail = $emailData ['receiver_email'] : $receiverEmail = NULL;
		 (isset($emailData ['NAME'])) ? $cust_name = $emailData ['NAME'] : $cust_name = NULL;
		 (isset($emailData ['password'])) ? $password = $emailData ['password'] : $password = NULL;
		 

	

        if ($this->sendMailContent($receiverEmail, $password)) {
            return 1;
        } else {
            return 0;
        }
    }
	
	
	
}	

   




