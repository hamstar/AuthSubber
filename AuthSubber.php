<?php

require_once 'Zend/Loader.php';
Zend_Loader::loadClass('Zend_Gdata_AuthSub');

class AuthSubber {

	private $hostedDomain = false;
	private $appName = 'me-myCoolApp-v1.0';
	private $ready = false;
	$s = new StdClass;
	
	private $scopeUrl = false;
	private $scopeClass = false;
	private $scope = false;
	
	$nextUrl = 'http://www.example.com/tokenReceiver.php';
	$session = 1;
	$secure = 0;  // set $secure=1 to request secure AuthSub tokens
	
	private $sessionToken = false;
	private $singleUseToken = false;
	$tokenInfo = false;

	/*////////////////////////////////////////////////////////

						MAGIC/UTILITY METHODS
						
	////////////////////////////////////////////////////////*/
	
	function __construct() {
		$this->setScope( 'Calendar' );
		$this->setAppName( 'telescum-TrademeWatcher-v1.0' );
	}
	
	/**
	* Returns true if className is declared
	*/
	function isDeclared( $className ) {
		return !( array_search( $className, get_declared_classes() ) );
	}
	
	/**
	* Quick function to send the user to google
	*/
	function send( $scope, $nextUrl ) {
		$this->setScope( $scope );
		$nextUrl .= "&scope=$scope";
		return $this->getRedirectUrl( $nextUrl );
	}
	
	/**
	* Quick function to get the token from the user
	* and return an object
	*/
	function receive( $__GET ) {
		
		$this->setScope( $__GET['scope'] );
		$this->setSessionToken( $__GET['Token'] );
		
		return $this->getService();
		
	}
	
	/*////////////////////////////////////////////////////////

						SETTER METHODS
						
	////////////////////////////////////////////////////////*/
	
	/**
	* Sets the scope of the object to create, the scope url,
	* and the name of the scope class to load, then loads
	* the scope class
	*
	* Returns true if the scope was applied successfully
	*/
	function setScope( $scope ) {
		
		// Set some class variables
		$this->scope = $scope;
		$this->scopeUrl = $this->getScopeUrl($scope);
		$this->scopeClass = $this->getScopeClass($scope);
		
		// If the class is not already declared then try to call it
		if ( !$this->isDeclared( $this->scopeClass ) ) {
			@Zend_Loader::loadClass($this->scopeClass);
		}
		
		if ( !$this->isDeclared( $this->scopeClass ) ) {
			$this->ready = false;
			throw new Exception("Couldn't load class {$this->scopeClass} in AuthSubber::setScope()");
		} else {
			$this->ready = true;
		}
		
		// Apply boolean to $this->ready and return it
		return $this;
		
	}
	
	/**
	* Set the single use token.
	*
	* Call $this->getSessionToken() with no arguments after this function
	* to update $this->sessionToken
	*/
	function setSingleUseToken( $token ) {
		$this->singleUseToken = $token;
		return $this;
	}
	
	/**
	* Sets a session token on a Service Object
	*/
	function setSessionToken( $serviceObj, $sessionToken=false ) {
	
		$sessionToken = ( $sessionToken ) ? $sessionToken: $this->sessionToken;
		$serviceObj->setAuthToken( $sessionToken );
		
		// Return the service object
		return $serviceObj;
	}
	
	/**
	* Set the application name
	*/
	function setAppName( $name ) {
		$this->appName = $name;
		return $this;
	}
	
	/**
	* Set hosted domain
	*/
	function setHostedDomain( $hd=false ) {
		$this->hostedDomain = $hd;
		return $this;
	}

	/**
	* Set a session using the single use token
	*
	* If singleUseToken passed via arg then $this->sessionToken will not be changed
	*/
	function setSessionToken( $singleUseToken=false ) {
		
		$singleUseToken = ( $singleUseToken ) ? $singleUseToken: $this->singleUseToken;
		$sessionToken = Zend_Gdata_AuthSub::getAuthSubSessionToken($singleUseToken);
		
		if ( $singleUseToken == $this->singleUseToken ) {
			$this->sessionToken = $sessionToken;
		}
		
		return $sessionToken;
		
	}
	
	/*////////////////////////////////////////////////////////

						GETTER METHODS
						
	////////////////////////////////////////////////////////*/
	
	/**
	* Returns the url for a scope
	*
	* xScopeName is a https URL
	*/
	function getScopeUrl( $scope ) {
		$urls = array(
			'Google Analytics' => 'https://www.google.com/analytics/feeds/',
			'Google Base' => 'http://www.google.com/base/feeds/',
			'Google Sites' => 'http://sites.google.com/feeds/',
			'Blogger' => 'http://www.blogger.com/feeds/',
			'Book Search' => 'http://www.google.com/books/feeds/',
			'Calendar' => 'http://www.google.com/calendar/feeds/',
			'Contacts' => 'http://www.google.com/m8/feeds/',
			'Documents List' => 'http://docs.google.com/feeds/',
			'Finance' => 'http://finance.google.com/finance/feeds/',
			'Gmail Atom' => 'https://mail.google.com/mail/feed/atom/',
			'Health' => 'https://www.google.com/health/feeds/',
			'H9 Sandbox' => 'https://www.google.com/h9/feeds/',
			'Maps Data' => 'http://maps.google.com/maps/feeds/',
			'Picasa Web Albums' => 'http://picasaweb.google.com/data/',
			'Sidewiki' => 'http://www.google.com/sidewiki/feeds/',
			'Spreadsheets' => 'http://spreadsheets.google.com/feeds/',
			'Webmaster Tools' => 'http://www.google.com/webmasters/tools/feeds/',
			'YouTube' => 'http://gdata.youtube.com',
			'xSpreadsheets' => 'https://spreadsheets.google.com/feeds/',
			'xCalendar' => 'https://www.google.com/calendar/feeds/',
			'xContacts' => 'https://www.google.com/m8/feeds/',
			'xDocuments List' => 'https://docs.google.com/feeds/',
			'xGoogle Sites' => 'https://sites.google.com/feeds/'
		);
		
		return ( isset( $urls[$scope] ) ) ? $urls[$scope]: false;
	}
	
	/**
	* Return the class name for a scope
	*
	* xScopeName is a https URL
	*/
	function getScopeClass( $scope ) {
		$classes = array(
			'Google Analytics' => 'Zend_Gdata_',
			'Google Base' => 'Zend_Gdata_',
			'Google Sites' => 'Zend_Gdata_',
			'Blogger' => 'Zend_Gdata_',
			'Book Search' => 'Zend_Gdata_',
			'Calendar' => 'Zend_Gdata_Calendar',
			'Contacts' => 'Zend_Gdata_',
			'Documents List' => 'Zend_Gdata_',
			'Finance' => 'Zend_Gdata_',
			'Gmail Atom' => 'Zend_Gdata_',
			'Health' => 'Zend_Gdata_',
			'H9 Sandbox' => 'Zend_Gdata_',
			'Maps Data' => 'Zend_Gdata_',
			'Picasa Web Albums' => 'Zend_Gdata_',
			'Sidewiki' => 'Zend_Gdata_',
			'Spreadsheets' => 'Zend_Gdata_',
			'Webmaster Tools' => 'Zend_Gdata_',
			'YouTube' => 'Zend_Gdata_',
			'xSpreadsheets' => 'Zend_Gdata_',
			'xCalendar' => 'Zend_Gdata_',
			'xContacts' => 'Zend_Gdata_',
			'xDocuments List' => 'Zend_Gdata_',
			'xGoogle Sites' => 'Zend_Gdata_'
		);
		
		return ( isset( $classes[$scope] ) ) ? $classes[$scope]: false;
	}
	
	/**
	* Gives the URL to redirect your users to enter their account details
	*/
	function getRedirectUrl( $next=null ) {
	
		if ( !$this->ready ) { 
			throw new Exception('Calling AuthSubber::getRedirectUrl() without calling applying a proper scope');
		}
	
		$nextUrl = ( $next ) ? $next: $this->nextUrl;
		$authSubUrl = Zend_Gdata_AuthSub::getAuthSubTokenUri($nextUrl, $this->scopeUrl, $this->secure, $this->session);
		$authSubUrl .= ( $this->hostedDomain ) ? '&hd=' . $this->hostedDomain : '';
		
		return $authSubUrl;
	}
	
	/**
	* Gets info for the session token given
	*/
	function getTokenInfo( $sessionToken=false ) {
		
		$sessionToken = ( $sessionToken ) ? $sessionToken: $this->sessionToken;
		$this->tokenInfo = Zend_Gdata_AuthSub::getAuthSubTokenInfo($sessionToken);
		
		// return the tokenInfo
		return $this->tokenInfo;
		
	}
	
	/**
	* Returns and object of the service objects
	*/
	function getServiceObjects() {
		return $s;
	}
	
	/**
	* Get the services object using the session token
	*
	* The service objects are created in $this->s->SERVICE_NAME
	*/
	function getService() {
		
		if ( !isset( $this->sessionToken ) ) {
			throw new Exception('No sessionToken set when trying to call AuthSubber::createServiceObject()');
		}
		
		// Create a service object and set the session token for subsequent requests
		$this->s->$this->scope = new $this->scopeClass(null, $this->appName);
		$this->s->$this->scope->setAuthSubToken($this->sessionToken);
		
		// Return the service object
		return $this->s->$this->scope;
		
	}
	
}

?>