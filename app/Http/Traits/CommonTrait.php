<?php

namespace App\Http\Traits;

trait CommonTrait
{
    public function getBandwidth($logDir): array
    {
        // Check if the log file exists
        if (!file_exists($logDir)) {
            return [
                'incoming_bandwidth' => 0,
                'outgoing_bandwidth' => 0
            ];
        }

        // Open the log file for reading
        $logFile = fopen($logDir, 'r');

        // Initialize variables to store incoming and outgoing bandwidth
        $incomingBandwidth = 0;
        $outgoingBandwidth = 0;

        // Read the log file line by line
        while ($line = fgets($logFile)) {
            // Parse the line based on the log format
            $logParts = explode(' ', $line);

            $incomingBandwidth     += !empty($logParts[24]) ? intval($logParts[24]) : 0;
            $outgoingBandwidth     += !empty($logParts[25]) ? intval($logParts[25]) : 0;
        }

        // Close the log file
        fclose($logFile);

        return [
            'incoming_bandwidth' => round($incomingBandwidth / (1024 * 1024), 2),
            'outgoing_bandwidth' => round($outgoingBandwidth / (1024 * 1024), 2)
        ];
    }

    public function removeTrailingSlash($text)
    {
        // Check if the string ends with '/'
        if (str_ends_with($text, '/')) {
            // Remove the trailing '/'
            return rtrim($text, '/');
        } else {
            return $text;
        }
    }
}
