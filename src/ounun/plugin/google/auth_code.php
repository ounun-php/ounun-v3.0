<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types=1);

namespace ounun\plugin\google;

use ounun\debug;

/**
 * PHP Class for handling Google Authenticator 2-factor authentication
 *
 * @copyright 2012 Michael Kliewe
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link http://www.phpgangsta.de/
 */
class auth_code
{
    protected int $_code_length = 6;

    /**
     * Create new secret.
     * 16 characters, randomly chosen from the allowed base32 characters.
     *
     * @param int $secret_length
     * @return string
     */
    public function secret_create(int $secret_length = 16): string
    {
        $valid_chars = $this->_base32_lookup_table_get();
        unset($valid_chars[32]);

        return str_repeat($valid_chars[array_rand($valid_chars)], $secret_length);
    }

    /**
     * Calculate the code, with given secret and point in time
     *
     * @param string $secret
     * @param int|null $time_slice
     * @return string
     */
    public function code_get(string $secret, ?int $time_slice = null): string
    {
        if ($time_slice === null) {
            $time_slice = floor(time() / 30);
        }

        $secret_key = $this->_base32_decode($secret);

        // Pack time into binary string
        $time = chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $time_slice);
        // Hash it with users secret key
        $hm = hash_hmac('SHA1', $time, $secret_key, true);
        // Use last nipple of result as index/offset
        $offset = ord(substr($hm, -1)) & 0x0F;
        // grab 4 bytes of the result
        $hash_part = substr($hm, $offset, 4);

        // Unpak binary value
        $value = unpack('N', $hash_part);
        $value = $value[1];
        // Only 32 bits
        $value = $value & 0x7FFFFFFF;

        $modulo = pow(10, $this->_code_length);
        return str_pad((string)($value % $modulo), $this->_code_length, '0', STR_PAD_LEFT);
    }

    /**
     * Get QR-Code URL for image, from google charts
     *
     * @param string $name
     * @param string $secret
     * @param string|null $title
     * @param string $url_root
     * @return string
     */
    public function qrcode_google_url_get(string $name, string $secret, ?string $title = null, string $url_root = 'https://qr.7pk.cn/'): string
    {
        $urlencoded = urlencode('otpauth://totp/' . urlencode($name) . '?secret=' . $secret . '');
        if (isset($title) && $title) {
            $urlencoded .= urlencode('&issuer=' . urlencode($title));
        }
        return $url_root . 'qrout.php?c=' . $urlencoded . '';
    }

    /**
     * Check if the code is correct. This will accept codes starting from $discrepancy*30sec ago to $discrepancy*30sec from now
     *
     * @param string $secret
     * @param string $code
     * @param int $discrepancy This is the allowed time drift in 30 second units (8 means 4 minutes before or after)
     * @param int|null $current_time_slice time slice if we want use other that time()
     * @return bool
     */
    public function verify_code(string $secret, string $code, int $discrepancy = 1, ?int $current_time_slice = null): bool
    {
        if ($current_time_slice === null) {
            $current_time_slice = (int)floor(time() / 30);
        }

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculated_code = $this->code_get($secret, $current_time_slice + $i);
            // debug::header([$calculatedCode, $code, $secret, $calculatedCode == $code ? '1' : 0,$current_time_slice + $i], '', __FILE__, __LINE__);
            if ($calculated_code === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set the code length, should be >=6
     *
     * @param int $length
     * @return auth_code GoogleAuthenticator
     */
    public function code_length_set(int $length): auth_code
    {
        $this->_code_length = $length;
        return $this;
    }

    /**
     * Helper class to decode base32
     *
     * @param $secret
     * @return bool|string
     */
    protected function _base32_decode($secret)
    {
        if (empty($secret)) {
            return '';
        }

        $base32chars         = $this->_base32_lookup_table_get();
        $base32chars_flipped = array_flip($base32chars);

        $paddingCharCount = substr_count($secret, $base32chars[32]);
        $allowedValues    = array(6, 4, 3, 1, 0);
        if (!in_array($paddingCharCount, $allowedValues)) return false;
        for ($i = 0; $i < 4; $i++) {
            if ($paddingCharCount == $allowedValues[$i] &&
                substr($secret, -($allowedValues[$i])) != str_repeat($base32chars[32], $allowedValues[$i])) return false;
        }
        $secret        = str_replace('=', '', $secret);
        $secret        = str_split($secret);
        $secret_len    = count($secret);
        $binary_string = '';
        for ($i = 0; $i < $secret_len; $i = $i + 8) {
            $x = '';
            if (!in_array($secret[$i], $base32chars)) {
                return false;
            }
            for ($j = 0; $j < 8; $j++) {
                $k = $secret[$i + $j];
                $x .= str_pad(base_convert((string)$base32chars_flipped[$k], 10, 2), 5, '0', STR_PAD_LEFT);
            }
            $eight_bits     = str_split($x, 8);
            $eight_bits_len = count($eight_bits);
            for ($z = 0; $z < $eight_bits_len; $z++) {
                $binary_string .= (($y = chr((int)base_convert($eight_bits[$z], 2, 10))) || ord($y) == 48) ? $y : "";
            }
        }
        return $binary_string;
    }

    /**
     * Helper class to encode base32
     *
     * @param string $secret
     * @param bool $padding
     * @return string
     */
    protected function _base32_encode(string $secret, bool $padding = true): string
    {
        if (empty($secret)) return '';

        $base32chars = $this->_base32_lookup_table_get();

        $secret       = str_split($secret);
        $binaryString = "";
        for ($i = 0; $i < count($secret); $i++) {
            $binaryString .= str_pad(base_convert((string)ord($secret[$i]), 10, 2), 8, '0', STR_PAD_LEFT);
        }
        $fiveBitBinaryArray = str_split($binaryString, 5);
        $base32             = "";
        $i                  = 0;
        while ($i < count($fiveBitBinaryArray)) {
            $base32 .= $base32chars[base_convert(str_pad($fiveBitBinaryArray[$i], 5, '0'), 2, 10)];
            $i++;
        }
        if ($padding && ($x = strlen($binaryString) % 40) != 0) {
            if ($x == 8) $base32 .= str_repeat($base32chars[32], 6);
            elseif ($x == 16) $base32 .= str_repeat($base32chars[32], 4);
            elseif ($x == 24) $base32 .= str_repeat($base32chars[32], 3);
            elseif ($x == 32) $base32 .= $base32chars[32];
        }
        return $base32;
    }

    /**
     * Get array with all 32 characters for decoding from/encoding to base32
     *
     * @return array
     */
    protected function _base32_lookup_table_get(): array
    {
        return array(
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
            'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
            'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
            '='  // padding char
        );
    }
}
