<?php

class APITest extends PHPUnit_Framework_TestCase
{    
  protected $fapi = null;

  public function setUp()
  {
    $this->fapi = new Forge_API($pubKey = '12345', $privateKey = '123456', $url = '127.0.0.1:300');
  }     
  
  public function testShouldReturnAllArtifacts()
  {
    $artifacts = $this->fapi->getAllArtifacts();    
    $this->assertGreaterThan(count($artifacts), 1);
  }
}