<?php
/**
 * Classes/Logger.php
 * 
 * Author: Timon
 * Date: 08/02/2025
 */
namespace App\Classes;

class Logger{
    private $logFile;
    private $email;
    private $maxFileSize;
    private $rotatedDir;
    private $rateLimitFile;
    private $rateLimitInterval;
    private $config;
    private $logFileName;

    public function __construct($logFile=null){
        $this->config = require __DIR__ . '/../Config/logger.php';

        if($logFile){
            $this->logFileName = $logFile;
            $this->logFile = __DIR__ . '/../logs/'.$logFile.'_log.json';
            $this->rateLimitFile = __DIR__ . '/../logs/'.$logFile.'_rate_limit.json';
        }else {
            $this->logFile = $this->config['logFile']; 
            $this->logFileName = $this->config['logFileName'];
            $this->rateLimitFile = $this->config['rateLimitFile'];
        }

        $this->email = $this->config['email'];
        $this->maxFileSize = $this->config['maxFileSize'];
        $this->rotatedDir = $this->config['rotatedDir'];
        $this->rateLimitInterval = $this->config['rateLimitInterval'];

        if (!is_dir(dirname($this->logFile))) mkdir(dirname($this->logFile), 0775, true);
        if (!file_exists($this->logFile)) touch($this->logFile);
        if (!file_exists($this->rateLimitFile)) file_put_contents($this->rateLimitFile, json_encode(['last_sent' => 0]));
    }

    // public function test(){
        // $this->rotateLogs();
        // echo var_dump($this->rotatedDir);  


    // }

    private function rotateLogs() {
        if (file_exists($this->logFile) && filesize($this->logFile) > $this->maxFileSize) {
            if (!is_dir($this->rotatedDir)) mkdir($this->rotatedDir, 0775, true);
            $rotatedFile = $this->rotatedDir . $this->logFileName.'_log_' . date('Y-m-d_H_i_a') . '.json';
            rename($this->logFile, $rotatedFile);
            touch($this->logFile);
        }
    }

    public function log($level, $message, $context = []) {
        $this->rotateLogs();

        $logEntry = [
            'timestamp' => date('Y-m-d\TH:i:sP'),
            'level'     => $level,
            'message'   => $message,
            'context'   => $context,
        ];

        file_put_contents($this->logFile, json_encode($logEntry, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND);

        if ($level === 'CRITICAL') {
            // $this->rateLimitedEmail($logEntry);
        }
    }

    private function rateLimitedEmail($logEntry) {
        $rateLimitData = json_decode(file_get_contents($this->rateLimitFile), true);
        $currentTime = time();

        if ($currentTime - $rateLimitData['last_sent'] >= $this->rateLimitInterval) {
            $subject = "Critical Error on Your Website";
            $message = "<html><body>";
            $message .= "<h2 style='color: red;'>Critical Error Occurred</h2>";
            $message .= "<p><strong>Timestamp:</strong> {$logEntry['timestamp']}</p>";
            $message .= "<p><strong>Message:</strong> {$logEntry['message']}</p>";
            $message .= "<p><strong>Context:</strong> " . json_encode($logEntry['context'], JSON_PRETTY_PRINT) . "</p>";
            $message .= "</body></html>";

            mail($this->email, $subject, $message, [
                'Content-Type: text/html; charset=UTF-8',
            ]);

            $rateLimitData['last_sent'] = $currentTime;
            file_put_contents($this->rateLimitFile, json_encode($rateLimitData));
        }
    }

    public function userAction($message, $userId, $context = []) {
        $this->log('INFO', $message, array_merge(['user_id' => $userId], $context));
    }

    public function systemError($message, $context = []) {
        $this->log('ERROR', $message, $context);
    }

    public function error($message, $context = []) { $this->log('ERROR', $message, $context); }
    public function warning($message, $context = []) { $this->log('WARNING', $message, $context); }
    public function info($message, $context = []) { $this->log('INFO', $message, $context); }
    public function critical($message, $context = []) { $this->log('CRITICAL', $message, $context); }
    public function debug($message, $context = []) { $this->log('DEBUG', $message, $context); }
}