<?php
require_once 'config.php';
session_destroy();
session_start();
flash('You have been logged out.', 'info');
go('login.php');
