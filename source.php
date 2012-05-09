<?php

// ------------------------------------------------------------------------

/**
 * Forge Source. 
 *    
 * @version     1.0 Beta
 * @author      Ken Erickson AKA Bookworm http://bookwormproductions.net
 * @copyright   Copyright 2009 - 2011 Design BreakDown, LLC.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2       
 */
class Forge_Source
{  
  /**
   * Public API Key.
   *                              
   * @var string     
   **/
  var $public_key; 

  /**
   * Private API Key.
   *                              
   * @var string     
   **/
  var $private_key;
              
  /**
   * Port to use.
   *                              
   * @var int   
   **/
  var $port = 3000;
  
  /**
   * The Source URL. 
   *                              
   * @var string
   **/
  var $url;
  
  /**
   * The name of the forge
   *                              
   * @var string
   **/
  var $name;    
  
  /**
   * Holds the public key, private key and token for passing to the oAuth script.      
   * 
   * @var array
   */
  var $signatures = array();
  
  /**
   * Constructor Function. Keeps the PHP Gods Happy.  
   *
   * @param string $public_key The Public key.  
   * @param string $private_key The Public key.     
   * @param string $url The Source URL.   
   * @param int   $port The Source port. 
   * @param name  $name The Source name. 
   * @return self
   */
  public function __construct($public_key, $private_key, $url = 'localhost', $port=3000, $name='Forge Source')
  {       
    $this->public_key  = $public_key;  
    $this->private_key = $private_key;      
    
    $this->signatures = array('consumer_key' => $this->public_key, 'shared_secret' => $this->private_key);     
    
    $this->url         = $url;     
    $this->port        = $port;
    $this->name        = $name;
  }
}