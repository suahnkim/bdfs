<?php
class Crypt {
    const secret_key = "mediablockchain";
    const secret_iv   = "#@$%^&*()_+=-";

    public static function Encrypt($str)
    {
        $key = hash('sha256', self::secret_key);
        $iv = substr(hash('sha256', self::secret_iv), 0, 16)    ;

        return str_replace("=", "", base64_encode(
                openssl_encrypt($str, "AES-256-CBC", $key, 0, $iv))
        );
    }

    public static function Decrypt($str)
    {
        $key = hash('sha256', self::secret_key);
        $iv = substr(hash('sha256', self::secret_iv), 0, 16);

        return openssl_decrypt(
            base64_decode($str), "AES-256-CBC", $key, 0, $iv
        );
    }
}
?>