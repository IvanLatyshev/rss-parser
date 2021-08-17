<?php

namespace App\Logging;

use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;

class DatabaseLogger implements LoggerInterface
{
    public function emergency($message, array $context = array())
    {
        $this->insertToDB($message, $context);
    }

    public function alert($message, array $context = array())
    {
        $this->insertToDB($message, $context);
    }

    public function critical($message, array $context = array())
    {
        $this->insertToDB($message, $context);
    }

    public function error($message, array $context = array())
    {
        $this->insertToDB($message, $context);
    }

    public function warning($message, array $context = array())
    {
        $this->insertToDB($message, $context);
    }

    public function notice($message, array $context = array())
    {
        $this->insertToDB($message, $context);
    }

    public function info($message, array $context = array())
    {
        $this->insertToDB($message, $context);
    }

    public function debug($message, array $context = array())
    {
        $this->insertToDB($message, $context);
    }

    public function log($level, $message, array $context = array())
    {
        $this->insertToDB($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    private function insertToDB(string $message, array $context): void
    {
        DB::table('public.logs')->insert([
            'log_message' => $message,
            'log_context' => json_encode($context),
        ]);
    }
}
