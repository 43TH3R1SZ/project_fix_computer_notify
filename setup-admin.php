<?php
declare(strict_types=1);
require __DIR__ . '/app.php';
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
if($argc<6){fwrite(STDERR,"Usage: php setup-admin.php STUDENT_ID PASSWORD FIRST_NAME LAST_NAME DEPARTMENT\nPassword must be at least 12 characters.\n");exit(1);}
[$script,$student,$password,$name,$lastname,$department]=$argv;
if(strlen($password)<12){fwrite(STDERR,"Password must be at least 12 characters.\n");exit(1);}
$hash=password_hash($password,PASSWORD_DEFAULT);$stmt=db()->prepare("INSERT INTO users(student_id,name,lastname,department,password,role) VALUES(?,?,?,?,?,'admin') ON DUPLICATE KEY UPDATE name=VALUES(name),lastname=VALUES(lastname),department=VALUES(department),password=VALUES(password),role='admin'");$stmt->bind_param('sssss',$student,$name,$lastname,$department,$hash);$stmt->execute();$stmt->close();fwrite(STDOUT,"Admin account ready: {$student}\n");
