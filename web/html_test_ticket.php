<?php
  ini_set('display_errors',1);
  ini_set('display_startup_erros',1);
  error_reporting(E_ALL);
?>

<ul id="countrys">
  <li><a href="?site_id=MLA"> Argentina - MLA</a></li>
  <li><a href="?site_id=MLB"> Brasil - MLB</a></li>
  <li><a href="?site_id=MLC"> Chile - MLC</a></li>
  <li><a href="?site_id=MCO"> Colombia - MCO</a></li>
  <li><a href="?site_id=MLM"> Mexico - MLM</a></li>
  <li><a href="?site_id=MPE"> Peru - MPE</a></li>
  <li><a href="?site_id=MLV"> Venezuela - MLV</a></li>
</ul>

<style>
  #countrys{
    position: fixed;
    top: 0;
    left: 10px;
    width: 160px;
    border: 2px dashed;
    padding: 10px;
  }

  #countrys li{
    list-style: none;
    border: 1px solid #cecece;
    margin: 5px 0;
    cursor: pointer;
    float: left;
    width: 100%;
    background-color: #fff;
  }

  #countrys li a{
    text-decoration: none;
    color: #000;
    width: 100%;
    float: left;
    padding: 5px;
    text-transform: uppercase;
  }

  #instant-suit-test{
    margin: 0;
    padding: 10px;
    position: fixed;
    width: 325px;
    overflow: auto;
    top: 10px;
    right: 10px;
    list-style: none;
    text-transform: uppercase;
    font-size: 9px;
    font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
    border: 2px dashed;
    height: 625px;
    background-color: #fff;
  }

  #instant-suit-test li{
    float: left;
    margin: 5px 10px !important;
    padding: 5px;
    cursor: pointer;
    background-color: #F5F5F5;
    width: 295px;
    list-style: none;
  }

  #instant-suit-test li.separate{
    cursor: auto;
    background-color: transparent;
    font-weight: bold;
    font-size: 12px;
  }

  #instant-suit-test li.ok{
    border: 1px solid green;
  }

  #instant-suit-test li.error{
    border: 1px solid red;
  }

  #instant-suit-test li p{
    margin: 4px;
  }
  #instant-suit-test li label{
    float: left;
    width: 100%;
  }

  .test-hidden-inputs{
    display: none;
  }
</style>
