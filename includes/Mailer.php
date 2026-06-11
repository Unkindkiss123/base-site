<?php
/**
 * includes/Mailer.php
 * Lightweight mail sender. Supports:
 *   - Native PHP mail()  (default — works on most hosts with sendmail)
 *   - Plain SMTP over fsockopen, with optional STARTTLS and AUTH LOGIN
 *
 * Configure via .env:
 *   MAIL_DRIVER     = "mail" | "smtp"   (default: "mail")
 *   MAIL_HOST       = smtp.example.com
 *   MAIL_PORT       = 587
 *   MAIL_ENCRYPTION = "tls" | "ssl" | ""
 *   MAIL_USER       = user@example.com
 *   MAIL_PASSWORD   = secret
 *   MAIL_FROM       = noreply@example.com
 *   MAIL_FROM_NAME  = "Base Site"
 *
 * No external dependencies.
 */

class Mailer {
    public static function send($to, $subject, $htmlBody, $textBody = null) {
        $driver = getenv('MAIL_DRIVER') ?: 'mail';
        $from = getenv('MAIL_FROM') ?: 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $fromName = getenv('MAIL_FROM_NAME') ?: (defined('APP_NAME') ? APP_NAME : 'Base Site');

        $textBody = $textBody ?? trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody)));
        $boundary = 'b_' . bin2hex(random_bytes(8));

        $headers = [];
        $headers[] = 'From: ' . sprintf('%s <%s>', self::encodeHeader($fromName), $from);
        $headers[] = 'Reply-To: ' . $from;
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';

        $body  = "--$boundary\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $body .= $textBody . "\r\n\r\n";
        $body .= "--$boundary\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $body .= $htmlBody . "\r\n\r\n";
        $body .= "--$boundary--\r\n";

        if ($driver === 'smtp') {
            return self::sendSmtp($from, $to, $subject, $headers, $body);
        }

        // Default: native mail()
        $encodedSubject = self::encodeHeader($subject);
        return @mail($to, $encodedSubject, $body, implode("\r\n", $headers));
    }

    private static function encodeHeader($s) {
        if (preg_match('/[^\x20-\x7E]/', $s)) {
            return '=?UTF-8?B?' . base64_encode($s) . '?=';
        }
        return $s;
    }

    private static function sendSmtp($from, $to, $subject, array $headers, $body) {
        $host = getenv('MAIL_HOST');
        $port = (int)(getenv('MAIL_PORT') ?: 587);
        $enc  = strtolower(getenv('MAIL_ENCRYPTION') ?: 'tls');
        $user = getenv('MAIL_USER') ?: '';
        $pass = getenv('MAIL_PASSWORD') ?: '';

        if (!$host) { return false; }

        $remote = ($enc === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
        $errno = 0; $errstr = '';
        $fp = @stream_socket_client($remote, $errno, $errstr, 15);
        if (!$fp) { return false; }
        stream_set_timeout($fp, 15);

        $read = function () use ($fp) {
            $data = '';
            while (!feof($fp)) {
                $line = fgets($fp, 1024);
                if ($line === false) break;
                $data .= $line;
                if (isset($line[3]) && $line[3] === ' ') break;
            }
            return $data;
        };
        $write = function ($cmd) use ($fp, $read) {
            fwrite($fp, $cmd . "\r\n");
            return $read();
        };

        $read(); // banner
        $write('EHLO ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));

        if ($enc === 'tls') {
            $write('STARTTLS');
            stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $write('EHLO ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        }

        if ($user !== '') {
            $write('AUTH LOGIN');
            $write(base64_encode($user));
            $resp = $write(base64_encode($pass));
            if (strpos($resp, '235') !== 0) { fclose($fp); return false; }
        }

        $write('MAIL FROM:<' . $from . '>');
        $write('RCPT TO:<' . $to . '>');
        $write('DATA');

        $subjectHeader = 'Subject: ' . self::encodeHeader($subject);
        $toHeader = 'To: ' . $to;
        $payload = $subjectHeader . "\r\n" . $toHeader . "\r\n" . implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
        $write($payload);
        $write('QUIT');
        fclose($fp);
        return true;
    }
}
