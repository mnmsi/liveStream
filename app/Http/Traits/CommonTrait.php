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

            $incomingBandwidth     += intval($logParts[24]);
            $outgoingBandwidth     += intval($logParts[25]);
        }

        // Close the log file
        fclose($logFile);

        return [
            'incoming_bandwidth' => round($incomingBandwidth / (1024 * 1024), 2),
            'outgoing_bandwidth' => round($outgoingBandwidth / (1024 * 1024), 2)
        ];
    }
}
