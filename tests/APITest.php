<?php

class APITest extends PHPUnit_Framework_TestCase
{    
  protected $fapi = null;

  public function setUp()
  {          
    $source = array('public_key' => '12345', 'private_key' => '123456', 'url' => '127.0.0.1', 'port' => 3000);
    $sources[] = $source;
    $this->fapi = new Forge_API(null, null, null, $sources);
  }     
  
  public function testShouldReturnAllArtifacts()
  {
    $artifacts = $this->fapi->getAllArtifacts();     
    $this->assertGreaterThan(1, count($artifacts));
  }    
  
  public function testShouldReturnArtifact()
  {
    $artifact = $this->fapi->getArtifact('bob');    
    $this->assertEquals($artifact->ext_name, 'bob');
  }    
  
  public function testShouldReturnArtifacts()
  {
    $artifacts = $this->fapi->getArtifacts(array('bob', 'george', 'joe', 'sam'));   
    $this->assertEquals($artifacts[0]->ext_name, 'bob');   
    $this->assertEquals($artifacts[1]->ext_name, 'george');
    $this->assertEquals($artifacts[2]->ext_name, 'joe');        
    $this->assertEquals($artifacts[3]->ext_name, 'sam');
  } 
  
  public function testShouldReturnSpecificArtifactsOnly()
  {
    $artifacts = $this->fapi->getAllArtifacts(array('only' => 'bob,george'));       
    $this->assertGreaterThan(1, count($artifacts));  
    $this->assertEquals($artifacts[0]->ext_name, 'bob');   
    $this->assertEquals($artifacts[1]->ext_name, 'george');
  }     
  
  public function testShouldGetIntegrated()
  {
    $artifacts = $this->fapi->getIntegrated('bob'); 
    $this->assertGreaterThan(0, count($artifacts)); 
  }
}