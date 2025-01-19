<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

/**
 * Set env variables and enable error reporting in local environment
 */
require_once(__DIR__ . "/lib/env.php");
require_once(__DIR__ . "/lib/error_reporting.php");

/**
 * Start user session
 */
session_start();

/**
 * Require routing library
 */
require_once(__DIR__ . "/lib/Route.php");

/**
 * Require routes
 */
require_once(__DIR__ . "/routes/index.php");
require_once(__DIR__ . "/routes/user.php");

// run router
Route::run('/');
