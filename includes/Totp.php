<?php
/**
 * includes/Totp.php
 * RFC 6238 TOTP + Base32 helpers. Zero dependencies.
 * Compatible with Google Authenticator, Authy, 1Password.
 */

class Totp {
    /** Generate a new random Base32 secret (160 bits). */
    public static function generateSecret($length = 32) {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bytes = random_bytes($length);
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $alphabet[ord($bytes[$i]) & 0x1F];
        }
        return $secret;
    }

    /** Build an otpauth:// URI for QR codes. */
    public static function provisioningUri($secret, $accountName, $issuer) {
        $label = rawurlencode($issuer . ':' . $accountName);
        $query = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => 6,
            'period' => 30,
        ]);
        return "otpauth://totp/{$label}?{$query}";
    }

    /** Verify a 6-digit code against secret, allowing ±1 step (~30s clock skew). */
    public static function verify($secret, $code, $window = 1) {
        $code = preg_replace('/\D/', '', (string)$code);
        if (strlen($code) !== 6) { return false; }
        $time = floor(time() / 30);
        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals(self::compute($secret, (int)($time + $i)), $code)) {
                return true;
            }
        }
        return false;
    }

    /** Compute the 6-digit TOTP for a given time counter. */
    private static function compute($secret, $counter) {
        $key = self::base32Decode($secret);
        $bin = pack('N*', 0) . pack('N*', $counter);
        $hash = hash_hmac('sha1', $bin, $key, true);
        $offset = ord($hash[19]) & 0xF;
        $part = substr($hash, $offset, 4);
        $value = unpack('N', $part)[1] & 0x7FFFFFFF;
        return str_pad((string)($value % 1000000), 6, '0', STR_PAD_LEFT);
    }

    /** Decode Base32 (RFC 4648, no padding tolerated). */
    private static function base32Decode($s) {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $map = array_flip(str_split($alphabet));
        $s = strtoupper(preg_replace('/[^A-Z2-7]/', '', $s));
        $bin = '';
        $buffer = 0; $bits = 0;
        for ($i = 0, $n = strlen($s); $i < $n; $i++) {
            $buffer = ($buffer << 5) | $map[$s[$i]];
            $bits += 5;
            if ($bits >= 8) {
                $bits -= 8;
                $bin .= chr(($buffer >> $bits) & 0xFF);
            }
        }
        return $bin;
    }
}
