/* A newsletter batch script, written in PHP5 running from command prompt * 
 * Description: It looks in a sql table for newsletter jobs and execute pending jobs.
 * by: Dravion - 08/24/2014
 */ 
#!/usr/bin/php
<?php
    namespace corenews;

    use PDO;
    use PDOException;

	
	/* This is the global connection point to the sql db */ 
    class PDOConnectionFactory{

	public $con 	= null;
    public $dbType  = "mysql";
	public $host    = "localhost";
    public $user    = "root";
	public $pass    = "wizzard";
    public $db      = "newsdb";
	public $persistent = true;

        public function PDOConnectionFactory( $persistent=false ) {
	       if( $persistent != false){ $this->persistent = true; }
        }

	/* Get a new connection from the pool */	
	public function getConnection() {
    	    
	    try {

        	$this->con = new PDO($this->dbType.":host=".$this->host.";dbname=".$this->db,
            $this->user, $this->pass, array( PDO::ATTR_PERSISTENT => $this->persistent ));    
	        $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	        return $this->con;

            } catch ( 
				PDOException $ex ) {
	            echo "Error: ".$ex->getMessage();
    		    return false;
            }
		}

        public function Close()	{
			if( $this->con != null )
				$this->con = null;
        }
    }

	/* in class news are the needed functions for newsjobhandling */
    class news {

		private $nid = null;
		private $headline = null;  
		private $storydate = null;
		private $story;

	/*  call if news was successfully send */
	public function jobisdone($nid) {

        	$db = new PDOConnectionFactory(TRUE);
	        $pdo = $db->getConnection();
    		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

				try {

            	    $stmt = $pdo->prepare 
						(" 
						UPDATE
							news                    
						SET
							sended = 1
						WHERE
							nid = :nid;
						");

					$stmt->bindParam(':nid' , $nid, PDO::PARAM_INT);
            	    $stmt->execute();

				    $pdo = null;
				}  catch(PDOException $e) {
                    echo $e->getMessage();
				}
				return true;
    	}		    
			
	/* process pending newsjobs */			
	public function sendNews() { 
	    
	    try	{
        	$db = new PDOConnectionFactory(TRUE);
                $pdo = $db->getConnection();

        	$stmt = $pdo->prepare("
                SELECT
					firstname,				
                    email
                FROM
                    customers
				WHERE 
					newsletter = 1;
                ");
            
 	           $stmt->execute();

	           while ($rs = $stmt->fetch(PDO::FETCH_OBJ)) {

					$nid 	 = $this->nid;
					$subject = $this->headline;
					$message = $this->story;
					$headers = 'From: '."Infomailer" . "\r\n" .
							   'Reply-To: '."Infomailer" . "\r\n" .
							   'X-Mailer: PHP/' . phpversion();

					/* params ok, send email now */
					mail($rs->email, $this->headline, $message, $headers);
						 $this->jobisdone($this->nid);
	       	   }	

			   } catch(PDOException $e) {
					echo "Error : ".$e->getMessage();
					return null;
    	    }
		}    

	    /* load news where the sendflag is not set to 1  */
        public function loadNews() { 
	    
			$nState = false;

			try	{
				$db = new PDOConnectionFactory(TRUE);
				$pdo = $db->getConnection();

				$stmt = $pdo->prepare("
               
			    SELECT
					nid,
                    headline,
					storydate,
					story
                FROM
                    news
				WHERE 
					sended = 0;
                 ");
            
	     	    $stmt->execute();
 			
	        	while ($rs = $stmt->fetch(PDO::FETCH_OBJ)) {
    		    	   $this->nid = $rs->nid;
					   $this->headline = $rs->headline;
					   $this->storydate = $rs->storydate;
					   $this->story = $rs->story; 			    
			           $nState = true;
	        	}	
		   

              } catch(PDOException $e) {
    	           echo "Error: ".$e->getMessage();
    	           return  null;
    	      }
			return $nState;
	    }     

        public function getJobCount() { 
	        
	    $nState = 0;
	    
			try	{
				$db = new PDOConnectionFactory(TRUE);
				$pdo = $db->getConnection();

				$stmt = $pdo->prepare("
                    SELECT
					   count(nid) AS x 
                    FROM 
                       news 
					WHERE 
					   sended = 0
                    ");
            
	     	    $stmt->execute();
 			
	        	while ($rs = $stmt->fetch(PDO::FETCH_OBJ)) {
		    	    $nState = $rs->x;			    
	        	}			   

              } catch(PDOException $e){
    	           echo "Error: ".$e->getMessage();
    	           return  null;
    	      }
		return $nState;
	    }      	 
	}

	/* Main program */
	print "\nNewsscript 1.0\n\n";
        $cinfo = new news();
	    
	print "New Jobs(".$cinfo->getJobCount().")\r";
	while (true) {
         if ($cinfo->loadNews() == true) {    
	     print "New Jobs(".$cinfo->getJobCount().") found, working... Exit with CTRL+C\r";
	     $cinfo->sendNews();
	     print "\r";
	 } else {
	     $date = date('Y/m/d H:i:s');
 	     print "No newsjobs pending, waiting for jobs... ".$date ." Exit with CTRL+C\r";	     
	 }
		 sleep(3); /* sleep 3 secs until we check the mysql news table again for new newsjobs */
	}    
