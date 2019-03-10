<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Isbank_Request {
	private $url;

	public function __construct( $url ) {
		$this->url = $url;
	}

	private function create_xml( $nodes ) {
		$dom  = new DOMDocument( '1.0', 'UTF-8' );
		$root = $dom->createElement( 'CC5Request' );

		foreach ( $nodes as $key => $value ) {
			$element = $dom->createElement( $key, $value );
			$root->appendChild( $element );
		}

		$dom->appendChild( $root );
		$xml = $dom->saveXML();

		return $xml;
	}

	public function send( $nodes ) {
		$request = $this->create_xml( $nodes );

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $this->url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 1 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 90 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $request );
		$result = curl_exec( $ch );

		return simplexml_load_string( $result );
	}
}
