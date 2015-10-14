<?php   
   /* 
     Example howto add an user hMailserver UserAccount via COM interface in PHP5 + 
	 Make sure in your php.ini file "extension=php_com_dotnet.dll" is loaded - check it with php -m in command prompt.
	 by: Dravion
   */
   
   header('Content-Type: text/html; charset=utf-8');
   $obBaseApp = new COM("hMailServer.Application", NULL, CP_UTF8);
   $obBaseApp->Connect();
   
   $hmail_config['rooturl']			= "http://localhost/";      
   $obAccount = $obBaseApp->Authenticate("Administrator", "hMailserver Administrator password");
   
    if (!isset($obAccount)) {
		  echo "<b>Not authenticated or Administrator's password wrong</b>";
    } else {
		
	try {
	 	echo "<b>Logon COM [OK], now we can add accounts</b>";
		
		$obDomain	= $obBaseApp->Domains->ItemByDBID(1); // domain id
		$obAccount = $obDomain->Accounts->ItemByDBID(1);  // account id
		  
		  $domainname = $obDomain->Name;

		     $obAccounts = $obDomain->Accounts();		  
		 	 $obAccount = $obDomain->Accounts->Add();
			 $newalias = "powerranger";
			 $firstname = "Arnold";
			 $lastname = "Schwarzenegger";
			 $my_domainname ="@mydomain.com";
			 
			 $obAccount->PersonFirstName = $firstname;
			 $obAccount->PersonLastName = $lastname;
			 $obAccount->MaxSize = 102; // 102 MB set inbox space
		   	 $obAccount->Address = $newalias .$my_domainname; 
			 $obAccount->Password = "secret"; // provide this in Thunderbird/Outlook ect.
			 $obAccount->Active = true; // set account to active
			 $obAccount->Save(); // save, finish.
	
		 /* If we reaching this point, everything works as expected */
	     echo "<br/><h3> Account was successfully created, now you can login with".
						  "an POP3 or IMAP-Client </h3>";
	
	} 
	/* OK, if something went wrong, give us the exact error details */	
	catch(Exception $e) {
		echo "<h4>COM-ERROR: <br />".$e->getMessage()."</h4><br />";		
	}
}