<?php
/**
 * Configuration file for role mappings. Will override any existing entries in 
 * the table 'ucla_rolemapping'
 */

// *SYSTEM* defaults
$role['ta']['*SYSTEM*'] = 'ta_admin'; // 02 whenever there is also an 01
$role['ta_instructor']['*SYSTEM*'] = 'ta_instructor'; 
$role['supervising_instructor']['*SYSTEM*'] = 'supervising_instructor'; 
$role['student_instructor']['*SYSTEM*'] = 'editinginstructor';
$role['editingteacher']['*SYSTEM*'] = 'editinginstructor'; // Always an 01
$role['student']['*SYSTEM*'] = 'student'; // Student enrolled in the course
$role['waitlisted']['*SYSTEM*'] = 'student'; // Student waitlisted in the course

// Subject areas using limited TA roles
$role['ta']['CHEM'] = 'ta'; 
$role['ta']['EE BIOL'] = 'ta';
$role['ta']['LIFESCI'] = 'ta';
$role['ta']['MCD BIO'] = 'ta';
$role['ta']['PHYSCI'] = 'ta';
$role['ta']['NEUROSC'] = 'ta';