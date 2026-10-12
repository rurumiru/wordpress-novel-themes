<?php
/**
 * Отправка Web Push: RFC 8291 и VAPID, без сторонних библиотек.
 *
 * Шифрование делает openssl, который в PHP есть почти всегда. Сборка
 * ключей и заголовков — здесь: тянуть composer ради одной отправки в тему,
 * которая ставится распаковкой, нельзя.
 *
 * @package XIN_V2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Send a Web Push notification to one subscription.
 *
 * @param array $subscription Keys: endpoint, p256dh, auth.
 * @param array $payload      Notification payload (title, body, url, icon, tag).
 * @param array $vapid        Keys: publicKey, privateKey, subject.
 * @return true|WP_Error
 */
function xv_push_send( $subscription, $payload, $vapid ) {
	if ( empty( $subscription['endpoint'] ) || empty( $subscription['p256dh'] ) || empty( $subscription['auth'] ) ) {
		return new WP_Error( 'xv_push_invalid_subscription', 'Invalid push subscription.' );
	}

	$audience = xv_push_get_audience( $subscription['endpoint'] );
	if ( ! $audience ) {
		return new WP_Error( 'xv_push_invalid_endpoint', 'Invalid push endpoint.' );
	}

	$encoded_payload = xv_push_encode_payload( $payload, $subscription['p256dh'], $subscription['auth'] );
	if ( is_wp_error( $encoded_payload ) ) {
		return $encoded_payload;
	}

	$jwt = xv_push_create_vapid_jwt( $audience, $vapid );
	if ( is_wp_error( $jwt ) ) {
		return $jwt;
	}

	$public_key = xv_push_base64url_decode( $vapid['publicKey'] );

	$response = wp_remote_post(
		$subscription['endpoint'],
		array(
			'timeout' => 15,
			'headers' => array(
				'Content-Type'     => 'application/octet-stream',
				'Content-Encoding' => 'aes128gcm',
				'Content-Length'   => (string) strlen( $encoded_payload ),
				'TTL'              => '86400',
				'Urgency'          => 'normal',
				'Authorization'    => 'vapid t=' . $jwt . ', k=' . xv_push_base64url_encode( $public_key ),
			),
			'body'    => $encoded_payload,
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$status = (int) wp_remote_retrieve_response_code( $response );

	if ( $status >= 200 && $status < 300 ) {
		return true;
	}

	if ( in_array( $status, array( 404, 410 ), true ) ) {
		return new WP_Error( 'xv_push_expired', 'Push subscription expired.', array( 'status' => $status ) );
	}

	return new WP_Error(
		'xv_push_failed',
		sprintf( 'Push delivery failed with status %d.', $status ),
		array( 'status' => $status, 'body' => wp_remote_retrieve_body( $response ) )
	);
}

/**
 * Extract push service audience from endpoint URL.
 *
 * @param string $endpoint Push endpoint.
 * @return string|false
 */
function xv_push_get_audience( $endpoint ) {
	$parts = wp_parse_url( $endpoint );
	if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
		return false;
	}
	return $parts['scheme'] . '://' . $parts['host'];
}

/**
 * Create a VAPID JWT for the push request.
 *
 * @param string $audience Push service origin.
 * @param array  $vapid    VAPID keys and subject.
 * @return string|WP_Error
 */
function xv_push_create_vapid_jwt( $audience, $vapid ) {
	$private_key_pem = xv_push_private_key_to_pem( $vapid['privateKey'] );
	if ( is_wp_error( $private_key_pem ) ) {
		return $private_key_pem;
	}

	$header  = xv_push_base64url_encode( wp_json_encode( array( 'typ' => 'JWT', 'alg' => 'ES256' ) ) );
	$claims  = xv_push_base64url_encode(
		wp_json_encode(
			array(
				'aud' => $audience,
				'exp' => time() + 43200,
				'sub' => $vapid['subject'],
			)
		)
	);
	$unsigned = $header . '.' . $claims;

	$signature = '';
	$ok        = openssl_sign( $unsigned, $signature, $private_key_pem, OPENSSL_ALGO_SHA256 );
	if ( ! $ok ) {
		return new WP_Error( 'xv_push_jwt_failed', 'Unable to sign VAPID JWT.' );
	}

	$der  = $signature;
	$raw  = xv_push_ecdsa_der_to_raw( $der );
	if ( is_wp_error( $raw ) ) {
		return $raw;
	}

	return $unsigned . '.' . xv_push_base64url_encode( $raw );
}

/**
 * Encrypt notification payload for aes128gcm content encoding.
 *
 * @param array  $payload Notification data.
 * @param string $p256dh  Client public key (base64url).
 * @param string $auth    Client auth secret (base64url).
 * @return string|WP_Error
 */
function xv_push_encode_payload( $payload, $p256dh, $auth ) {
	$body = wp_json_encode( $payload );
	if ( ! $body ) {
		return new WP_Error( 'xv_push_payload_failed', 'Unable to encode push payload.' );
	}

	$user_public  = xv_push_base64url_decode( $p256dh );
	$user_auth    = xv_push_base64url_decode( $auth );
	$local_keys   = xv_push_generate_local_keys();
	if ( is_wp_error( $local_keys ) ) {
		return $local_keys;
	}

	$salt          = random_bytes( 16 );
	$shared_secret = xv_push_ecdh( $local_keys['private'], $user_public );
	if ( is_wp_error( $shared_secret ) ) {
		return $shared_secret;
	}

	$ikm_info = "WebPush: info\x00" . $user_public . $local_keys['public'];
	$ikm      = xv_push_hkdf( $user_auth, $shared_secret, $ikm_info, 32 );
	$prk      = hash_hmac( 'sha256', $ikm, $salt, true );
	$cek   = xv_push_hkdf_expand( $prk, "Content-Encoding: aes128gcm\x00", 16 );
	$nonce = xv_push_hkdf_expand( $prk, "Content-Encoding: nonce\x00", 12 );

	$record_size = 4096;
	$pad         = "\x02";
	$plaintext   = $body . $pad;
	$ciphertext  = xv_push_aes_gcm_encrypt( $plaintext, $cek, $nonce );
	if ( is_wp_error( $ciphertext ) ) {
		return $ciphertext;
	}

	$header  = $salt;
	$header .= pack( 'N', $record_size );
	$header .= pack( 'C', strlen( $local_keys['public'] ) );
	$header .= $local_keys['public'];

	return $header . $ciphertext;
}

/**
 * Generate ephemeral ECDH key pair on prime256v1.
 *
 * @return array|WP_Error
 */
function xv_push_generate_local_keys() {
	$args = function_exists( 'xv_push_openssl_args' )
		? xv_push_openssl_args()
		: array(
			'curve_name'       => 'prime256v1',
			'private_key_type' => OPENSSL_KEYTYPE_EC,
		);

	$key = openssl_pkey_new( $args );

	if ( ! $key ) {
		return new WP_Error( 'xv_push_keygen_failed', 'Unable to generate ephemeral ECDH keys.' );
	}

	$details = openssl_pkey_get_details( $key );
	if ( empty( $details['ec']['x'] ) || empty( $details['ec']['y'] ) ) {
		return new WP_Error( 'xv_push_keygen_failed', 'Unable to read ephemeral public key.' );
	}

	$x = str_pad( $details['ec']['x'], 32, "\0", STR_PAD_LEFT );
	$y = str_pad( $details['ec']['y'], 32, "\0", STR_PAD_LEFT );

	$private     = '';
	$export_args = array();
	if ( function_exists( 'xv_push_openssl_config_path' ) ) {
		$cnf = xv_push_openssl_config_path();
		if ( $cnf ) {
			$export_args['config'] = $cnf;
		}
	}
	openssl_pkey_export( $key, $private, null, $export_args );

	return array(
		'private' => $private,
		'public'  => "\x04" . $x . $y,
	);
}

/**
 * Perform ECDH key agreement.
 *
 * @param string $private_pem Local private key PEM.
 * @param string $public_raw  Remote uncompressed public key.
 * @return string|WP_Error
 */
function xv_push_ecdh( $private_pem, $public_raw ) {
	$remote_pem = xv_push_public_key_to_pem( $public_raw );
	if ( is_wp_error( $remote_pem ) ) {
		return $remote_pem;
	}

	$shared = openssl_pkey_derive( $remote_pem, $private_pem );
	if ( false === $shared ) {
		return new WP_Error( 'xv_push_ecdh_failed', 'ECDH key agreement failed.' );
	}

	return str_pad( $shared, 32, "\0", STR_PAD_LEFT );
}

/**
 * AES-128-GCM encrypt helper.
 *
 * @param string $plaintext Plaintext bytes.
 * @param string $key       16-byte key.
 * @param string $nonce     12-byte nonce.
 * @return string|WP_Error
 */
function xv_push_aes_gcm_encrypt( $plaintext, $key, $nonce ) {
	$tag        = '';
	$ciphertext = openssl_encrypt( $plaintext, 'aes-128-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag, '', 16 );
	if ( false === $ciphertext ) {
		return new WP_Error( 'xv_push_encrypt_failed', 'AES-GCM encryption failed.' );
	}
	return $ciphertext . $tag;
}

/**
 * HKDF-Extract + Expand (HMAC-SHA256).
 *
 * @param string $salt Extract salt.
 * @param string $ikm  Input key material.
 * @param string $info Context string.
 * @param int    $len  Output length.
 * @return string
 */
function xv_push_hkdf( $salt, $ikm, $info, $len ) {
	$prk = hash_hmac( 'sha256', $ikm, $salt, true );
	return xv_push_hkdf_expand( $prk, $info, $len );
}

/**
 * HKDF expand step.
 *
 * @param string $prk  Pseudorandom key.
 * @param string $info Context string.
 * @param int    $len  Output length.
 * @return string
 */
function xv_push_hkdf_expand( $prk, $info, $len ) {
	$output   = '';
	$previous = '';
	$counter  = 1;

	while ( strlen( $output ) < $len ) {
		$previous = hash_hmac( 'sha256', $previous . $info . chr( $counter ), $prk, true );
		$output  .= $previous;
		++$counter;
	}

	return substr( $output, 0, $len );
}

/**
 * Convert raw VAPID private key to PEM.
 *
 * @param string $private_key_base64url Base64url-encoded private key.
 * @return string|WP_Error
 */
function xv_push_private_key_to_pem( $private_key_base64url ) {
	$private = xv_push_base64url_decode( $private_key_base64url );
	$der     = xv_push_ec_private_key_der( $private );
	if ( is_wp_error( $der ) ) {
		return $der;
	}

	$pem = "-----BEGIN EC PRIVATE KEY-----\n";
	$pem .= chunk_split( base64_encode( $der ), 64, "\n" );
	$pem .= "-----END EC PRIVATE KEY-----\n";

	return $pem;
}

/**
 * Convert uncompressed public key bytes to PEM.
 *
 * @param string $public_raw Uncompressed EC public key.
 * @return string|WP_Error
 */
function xv_push_public_key_to_pem( $public_raw ) {
	$der = xv_push_ec_public_key_der( $public_raw );
	if ( is_wp_error( $der ) ) {
		return $der;
	}

	$pem = "-----BEGIN PUBLIC KEY-----\n";
	$pem .= chunk_split( base64_encode( $der ), 64, "\n" );
	$pem .= "-----END PUBLIC KEY-----\n";

	return $pem;
}

/**
 * Build EC private key DER structure.
 *
 * @param string $private_key 32-byte private key.
 * @return string|WP_Error
 */
function xv_push_ec_private_key_der( $private_key ) {
	$oid_prime256v1 = "\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07";
	$version        = "\x02\x01\x01";
	$private_octet  = "\x04\x20" . $private_key;
	$curve          = "\xa0\x0a" . $oid_prime256v1;
	$sequence       = $version . $private_octet . $curve;

	return "\x30" . xv_push_der_length( strlen( $sequence ) ) . $sequence;
}

/**
 * Build EC public key DER structure.
 *
 * @param string $public_raw Uncompressed EC public key.
 * @return string|WP_Error
 */
function xv_push_ec_public_key_der( $public_raw ) {
	$oid_ec_public  = "\x06\x07\x2a\x86\x48\xce\x3d\x02\x01";
	$oid_prime256v1 = "\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07";
	$algorithm      = "\x30" . xv_push_der_length( strlen( $oid_ec_public . $oid_prime256v1 ) ) . $oid_ec_public . $oid_prime256v1;
	$bit_string     = "\x03" . xv_push_der_length( strlen( $public_raw ) + 1 ) . "\x00" . $public_raw;
	$sequence       = $algorithm . $bit_string;

	return "\x30" . xv_push_der_length( strlen( $sequence ) ) . $sequence;
}

/**
 * Convert ECDSA DER signature to raw R||S (64 bytes).
 *
 * @param string $der DER-encoded signature.
 * @return string|WP_Error
 */
function xv_push_ecdsa_der_to_raw( $der ) {
	$offset = 0;
	if ( ord( $der[ $offset++ ] ) !== 0x30 ) {
		return new WP_Error( 'xv_push_sig_failed', 'Invalid ECDSA signature.' );
	}

	$length = ord( $der[ $offset++ ] );
	if ( $length & 0x80 ) {
		$bytes  = $length & 0x7f;
		$length = 0;
		for ( $i = 0; $i < $bytes; $i++ ) {
			$length = ( $length << 8 ) | ord( $der[ $offset++ ] );
		}
	}

	$r = xv_push_read_asn1_integer( $der, $offset );
	if ( is_wp_error( $r ) ) {
		return $r;
	}

	$s = xv_push_read_asn1_integer( $der, $offset );
	if ( is_wp_error( $s ) ) {
		return $s;
	}

	return str_pad( $r, 32, "\x00", STR_PAD_LEFT ) . str_pad( $s, 32, "\x00", STR_PAD_LEFT );
}

/**
 * Read ASN.1 INTEGER from DER buffer.
 *
 * @param string $der    DER bytes.
 * @param int    $offset Current offset (by reference).
 * @return string|WP_Error
 */
function xv_push_read_asn1_integer( $der, &$offset ) {
	if ( ord( $der[ $offset++ ] ) !== 0x02 ) {
		return new WP_Error( 'xv_push_sig_failed', 'Invalid ASN.1 integer.' );
	}

	$length = ord( $der[ $offset++ ] );
	$value  = substr( $der, $offset, $length );
	$offset += $length;

	while ( strlen( $value ) > 0 && ord( $value[0] ) === 0x00 ) {
		$value = substr( $value, 1 );
	}

	return $value;
}

/**
 * Encode DER length.
 *
 * @param int $length Content length.
 * @return string
 */
function xv_push_der_length( $length ) {
	if ( $length < 128 ) {
		return chr( $length );
	}

	$bytes = ltrim( pack( 'N', $length ), "\x00" );
	return chr( 0x80 | strlen( $bytes ) ) . $bytes;
}

/**
 * Base64url encode.
 *
 * @param string $data Raw bytes.
 * @return string
 */
function xv_push_base64url_encode( $data ) {
	return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
}

/**
 * Base64url decode.
 *
 * @param string $data Base64url string.
 * @return string
 */
function xv_push_base64url_decode( $data ) {
	$remainder = strlen( $data ) % 4;
	if ( $remainder ) {
		$data .= str_repeat( '=', 4 - $remainder );
	}
	return base64_decode( strtr( $data, '-_', '+/' ) );
}