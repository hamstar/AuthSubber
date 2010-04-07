# AuthSubber

## What is it?

AuthSubber provides a means to authenticate to google data API's using 
Google AuthSub system.  It then returns a google service object that you can use.

## Does it work?

I'm not sure, I haven't even tested this yet, just wanted to get this 
idea out there.

## How will it work when it does work?

This is what I set out to achieve:

First a sender script to create the auth link for the user to click on:
	<?php
	
	// sender.php
	
	include 'AuthSubber.php';
	$a = new AuthSubber;
	
	$url = $a->send( 'Calendar', 'http://example.com/receiver.php' 
);
	
	echo $url;
	
	?>

Then a receiver script to get the single use token, convert it into a session token and return a usable Google Service Object.

	<?php
	
	// receiver.php
	
	include 'AuthSubber.php';
        $a = new AuthSubber;
	
	$calender = $a->receive( $_GET );
	
	$newEvent = $calender->newEventEntry();
	
	// ... et al
	
	?>

Yeah, that would be nice.
