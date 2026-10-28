<?php

use CodeIgniter\CLI\CLI;

/**
 * @var int $code
 * @var string $message
 */

CLI::error('ERROR: ' . $code);
CLI::write($message);
CLI::newLine();
