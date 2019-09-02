<?php


// Validation Class - validate_class.php
//require_once 'error_class.php';

// Class - Validation
class validator {

// Function - Reports Failures
/* function report($error) {
	$validate_error = new error(); // Create Error Class
	if (empty($error)) {
		$error = 'V_FAILURE';
	}
	$validate_error->report("$error");
} */

// Function - Kills Illegal Characters Often Used For SQL Injections
	function killChars($text) {
	$text = str_replace("''", "'", $text); // Double Single Quotes
	$badChars = array("select","drop",";","--","insert","delete","xp_");  // SQL Injection Hazards
	
	foreach($badChars as $current) {
		$text = str_replace($current, '', $text);
	}
	return $text;
}

// Function - Kills Illegal Quotes Often Used For SQL Injections
function killQuotes($text) {
	$text = str_replace("''", "'", $text); // Double Single Quotes
	return $text;
}

// Function - Kills Entities for Validating/Better Coding
function killEntities($text) {
	$text = preg_replace('# & #', ' &amp; ', $text);  // Replace & with &amp;
	return $text;
}

// Function - Kills Metas for Extra Security for Regular Expressions
function killMetas($text) {
	$replace = array( '.','+','*','?','[','^',']','(','$',')' ); // Meta Values
	$text = str_replace($replace, '', $text);
	return $text;
}

// Function - Determines If Data Is A Number
function checkNumber($text) {
	if((gettype($text)) == 'integer') { return true; }
	else { return false; }
}

// Function - Determines If Data Is Only Letters
function checkLetters($text) {
	$check = $this->stripLetters($text);
	$check = $this->stripWhitespace($check);
	if(empty($check)) { return true; }
	else { return false; }
}

// Function - Determines If Data Is a Proper URL
function checkURL($text) {
	if (!preg_match('#^http\\:\\/\\/[a-z0-9\-]+\.([a-z0-9\-]+\.)?[a-z]+#i', $text)) {
		return false;
	}
	else { return true; }
}

// Function - Determines If Data Is a Proper Email Address
function checkEmail($text) {
	if (eregi('^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$', $text)) {
		return true;
	}
	else {  return false; }
}

// Function - Checks a File's Existence
function checkFile($filename) {
	if (!file_exists($filename)) {
		$this->report('FILE_MISSING');
		return false;
	}
	if (!is_readable($filename)) {
		$this->report('UNREADABLE_FILE');
		return false;
	}
	if(!is_writeable($filename)) {
		$this->report('UNWRITABLE_FILE');
		return false;
	}
	if(is_dir($filename)) {
		$this->report('DIR_FILE');
		return false;
	}
	if(is_link($filename)) {
		$this->report('LINK_FILE');
		return false;
	}
	return true;
}

// Function - Replaces All Numbers
function stripNumber($text) {
	$text = preg_replace('/[0-9]/', '', $text);
	return $text;
}

function numberOnly($text) {
	$text = preg_replace('/[^0-9]/', '', $text);
	return $text;
}
// Function - Replaces All Letters
function stripLetters($text) {
	$text = preg_replace('/([A-Za-z])/', '', $text);
	return $text;
}

// Function - Replaces All Whitespace
function stripWhitespace($text) {
	$text = preg_replace('/[  ]/', '', $text);
	return $text;
}

// Function - Replaces A Specified String With a Specified Value
function replaceString($text, $replace, $value) {
	$text = str_replace($replace, $value, $text);
	return $text;
}

function validator() { }
}

function sqlvalue($val, $quote)
{
  if ($quote)
    $tmp = sqlstr($val);
  else
    $tmp = $val;
  if ($tmp == "")
    $tmp = "NULL";
  elseif ($quote)
    $tmp = "'".$tmp."'";
  return $tmp;
}

function sqlstr($val)
{
  return str_replace("'", "''", $val);
}

function isValidFileName($file) {
    /* don't allow .. and allow any "word" character \ / */
    return preg_match('/^(((?:\.)(?!\.))|\w)+$/', $file);
}

?>