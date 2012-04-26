<?php       
if(!defined('DS')) {
  define('DS', DIRECTORY_SEPARATOR);
}

// Requires
require 'oauth.php';    
require 'REST' . DS . 'client.php';  
require 'REST' . DS . 'simpleClient.php';  
require 'source.php';  

// ------------------------------------------------------------------------

/**
 * Forge API Script.
 *    
 * @version     1.0 Beta
 * @author      Ken Erickson AKA Bookworm http://bookwormproductions.net
 * @copyright   Copyright 2009 - 2011 Design BreakDown, LLC.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2       
 */
class Forge_API
{
  /**
   * Sources/Forges to send the requests to.
   *                              
   * @var array Containing Forge_Source objects    
   **/
  var $sources = array();  
  
  /**
   * Whether or not to decode the JSON into PHP objects.
   *                              
   * @var int   
   **/
  var $decode = true; 
  
  /**
   * Configuration array.        
   *
   * @var array  
   *  $config['decode'] whether or not to decode.  
   *  $config['limit'] number of items to return. 
   **/
  var $config = array( 
    'limit' => '10',
  );

  /**
   * Constructor Function. Keeps the PHP Gods Happy.  
   *
   * @note We ultimately construct a source object and append it to $sources. 
   *  The $pubKey = null, $privateKey = null, $url = null vars are basically there for ease of use.
   * 
   * @param string $pubKey The Public key.  
   * @param string $privateKey The Public key.     
   * @param string $url The Source URL.
   * @param array  $sources Should be an array. Do not use this if you set any of the previous vars.
   *  $source['public_key'], $source['private_key'], $source['source_url']
   *
   * @param array $config Configuration array. Following options accepted; 
   *  $config['decode'] whether or not to decode.  
   *  $config['limit'] number of items to return.
   * @return void
   **/    
  public function __construct($pubKey = null, $privateKey = null, $url = null, $sources = null, $config = null)
  {      
    if(!is_null($config)) {
      $this->config = array_merge($this->config, $config);        
      if(isset($this->config['decode'])) $this->decode  = $this->config['decode'];       
    }  
    
    if(is_null($sources))
      $sources = array(array('public_key' => $pubKey, 'private_key' => $privateKey, 'source_url' => $url));
      
    $this->sources = $this->_genSources($sources);
  }
  
// ------------------------------------------------------------------------
        
  /**
   * Generates source objects.
   * 
   * @note Cool abstraction so artifacts can be pulled from multiple forgeries. 
   *   
   * @return array The array of sources
   */
  private function _genSources($sources)
  {
    $result = array();
    
    foreach($sources as $source)
    {          
      if(!is_object($source))  {
        if(!isset($source['port'])) $source['port'] = null;
        if(!isset($source['name'])) $source['name'] = null;      
        $source = (object) $source;
      }
      array_push($result, new Forge_Source($source->public_key, $source->private_key, $source->source_url, 
        $source->port, $source->name));
    }
    
    return $result;
  }
  
// ------------------------------------------------------------------------

  /**                       
   * Singleton function.
   *  
   * @note We ultimately construct a source object and append it to $sources. 
   *  The $pubKey = null, $privateKey = null, $url = null vars are basically there for ease of use.
   * 
   * @param string $pubKey The Public key.  
   * @param string $privateKey The Public key.     
   * @param string $url The Source URL.
   * @param array  $sources Should be an array. Do not use this if you set any of the previous vars.
   *  $source['public_key'], $source['private_key'], $source['source_url']
   *
   * @param array $config Configuration array. Following options accepted; 
   *  $config['decode'] whether or not to decode.  
   *  $config['limit'] number of items to return.
   * @return obj Forge_API
   */
  public function instance($pubKey = null, $privateKey = null, $url = null, $sources = null, $config = null)
  {
    static $instance;            
    
    if(!is_object($instance)) 
      $instance = new Forge_API($pubKey, $privateKey, $url, $sources, $config);   
      
    return $instance;
  } 

// ------------------------------------------------------------------------

  /**                       
   * Gets All The Artifacts.   
   *
   * @param array  $params Parameters to pass to the request.
   * @param bool   $check  Whether or not to check each artifact for missing dependencies, integrations, vulnerabilities etc.
   * @param filter $filter A filter of artifacts to NOT get. Defaults to Forge::artifacts.
   * @return array An array of all Artifacts.
   */
  public function getAllArtifacts($params = array(), $check = true, $filter = null)
  { 
    if(is_null($filter) and class_exists('Forge')) {   
      $forge  = Forge::getInstance();     
      $filter = implode(",", $forge->artifacts);
    }     
    
    $prams  = array('filter' => $filter, 'limit' => $this->config['limit']);
    $params = array_merge($params, $prams);        
    
    if($check)   
    { 
      $decodeOrg = true;      
      
      if($this->decode == false) {
        $decodeOrg    = false;
        $this->decode = true;
      } 
           
      $artifacts = $this->request('api/v1/artifacts/all', $params);  
      $artifacts->artifacts = $this->checkArtifacts($artifacts->artifacts);          

      $this->decode = $decodeOrg;  
             
      if($this->decode == false)
        return json_encode($artifacts);    
      else
        return $artifacts;
    }
    else
      return $artifacts = $this->request('api/v1/artifacts/all', $params);  
  }  
  
  
// ------------------------------------------------------------------------

  /**                       
   * Gets a specific artifact or artifacts
   *
   * @param mixed $artifacts Either: 1. The name of the artifact to get or 2. An array of artifacts.     
   * @param bool  $returnAsArray Whether or not to return a single artifact in an array. Defaults to false.
   * @return mixed Either: 1. An array of artifacts. OR 2. An artifact object.
   *  You can also choose to return as an array anyway. If you do that then use the first element via index,
   *  i.e $artifact = $artifacts[0];
   */   
  public function getArtifact($artifacts, $returnAsArray = false)
  {
    $result = array();   

    if(is_string($artifacts) AND $returnAsArray == true)
      $artifacts = array($artifacts);   
        
    if(is_array($artifacts)) {
      foreach($artifacts as $artifactName)
        $result[$artifactName] = $this->request('api/v1/artifacts/get/'.$artifactName);
    }
    else
      $result = $this->request('api/v1/artifacts/get/'.$artifactName); 

    return $result;
  }
   
// ------------------------------------------------------------------------

  /**                       
   * Alias to Forge_API::getArtifact()
   *
   * @param mixed $artifacts Either: 1. The name of the artifact to get or 2. An array of artifacts.     
   * @param bool  $returnAsArray Whether or not to return a single artifact in an array. Defaults to false.
   * @return mixed Either: 1. An array of artifacts. OR 2. An artifact object.
   *  You can also choose to return as an array anyway. If you do that then use the first element via index,
   *  i.e $artifact = $artifacts[0];
   */ 
   public function getArtifacts($artifacts, $returnAsArray = false)
   {  
     return $this->getArtifact($artifacts, $returnAsArray = false);
   }

// ------------------------------------------------------------------------

  /**                       
   * Gets artifacts that integrate with a specific artifact or artifacts. 
   *
   * @param string $artifacts A string of comma separated artifact names to find integrations for.
   * @return array An array of artifacts providing integrations.
   */   
  public function getIntegrated($artifacts)  
  {
    return $this->request('api/v1/artifacts/all', array('integrations' => $artifacts)); 
  }    
  
// ------------------------------------------------------------------------

  /**                       
   * Builds a request, sends it and handles the results. Its like some sort of request handler. :)
   *
   * @param string $path   The path to send the request to. No trailing slash or forward starting slash. 
   *  Please, thank you & have a nice request, response experience.             
   * @param array  $params Parameters to send. Takes the form of array('param' => $paramvalue);
   * @param string $method The method to use; 'get', 'post', 'put' etc. Defaults to 'get', which is recommended,
   * @return mixed A string or JSON decoded object. An error string if it fails.
   */   
  public function request($path, $params = null, $method = 'get')
  {  
    if(count($this->sources) > 1)
      $return = array('artifacts' => array(), 'count' => 0);
    
    foreach($this->sources as $source)
    {      
      
     $OAuth       = new OAuthSimple(); 
     $OAuthResult = $OAuth->sign(array('path' => 'http://'.$source->source_url.':'.$source->port.'/'.$path, 'parameters' => $params, 'signatures' => $source->signatures));  

     $rc = new REST_SimpleClient($source->source_url, $source->port);      
     $rc->request->setHeader('Authorization', 'Authorization: '.$OAuthResult['header']);          
     $result = $rc->{$method}('/'.$path, $params); 
     $rc->close(); 
     if ($result->isError()) die($result->error);
         
     
     if(count($this->sources) == 1)
     {
       if($this->decode == true)
         return json_decode($result->content)['data'];  
       else
         return $result->content; 
     }
     else
     {  
       $resultJSON = json_decode($result->content); 
       $return['artifacts'] = array_merge($return['artifacts'], $resultJSON->artifacts);  
       $return['count']     = $result['count'] + $resultJSON->count;
       unset($resultJSON);
     }

     unset($rc);
     unset($OAuth); 
     unset($OAuthResult);
    }   
    
    if($this->decode == true)
      return (object) $return;  
    else
      return json_encode($return);
  } 
  
// ------------------------------------------------------------------------

  /**                       
   * Checks the Artifacts for problems. Uses magic just kidding, it uses drugs. Don't worry only the medical kind.
   *
   * @param array $artifacts An array of Artifact objects. Should have been previously decoded from JSON.   
   * @param array $artifacts_check_against. Optional. An array of Artifact objects to check the others against. Should be the ones from the forge.
   * @return array An array of modified unicorns, nah its actually artifacts. What were your expecting unicorns?
   */
  public function checkArtifacts($artifacts, $artifacts_check_against = null)
  { 
    if(is_null($artifacts_check_against)) {
      $forge = Forge::getInstance();
      $artifacts_check_against = $forge->artifacts();
    }   
    
    foreach($artifacts as $k => $artifact)
    {   
      // Check For Integrations.
      foreach($artifact->integrations as $integration)
      {              
        if(array_key_exists($integration, $artifacts_check_against))
          @$artifacts[$k]->integrates = true;  
        else 
          @$artifacts[$k]->integrates = false;
      }     
      
      // Check For Incompatibilities.
      foreach($artifact->incompatibilities as $incompatibility)
      {              
        if(!array_key_exists($incompatibility, $artifacts_check_against))
          @$artifacts[$k]->compatible = true;  
        else 
          @$artifacts[$k]->compatible = false;
      } 
      
      // Check For unMet Dependencies
      @$artifacts[$k]->unmetDependencies = array();
      foreach($artifact->dependencies as $dependency)
      {              
        if(!array_key_exists($dependency, $artifacts_check_agains)) 
        {
           @$artifacts[$k]->dependenciesUnMet = true;    
           @$artifacts[$k]->unmetDependencies[] = $dependency;  
        }
        else 
          @$artifacts[$k]->dependenciesUnMet = false;    
      }       
    }
     
    return $artifacts;
  }
  
// ------------------------------------------------------------------------

  /**                       
   * Makes a test request to check the keys.
   *
   * @param string $pubKey     The public key to check.
   * @param string $privateKey The public key to check.
   * @return bool  
   * @todo Everything. Get on it Aslan!
   */   
  public function checkKeys($pubKey, $pirvateKey)
  {  
    return true;
  } 
}