<?php
/**
 * Normalized runtime lab test result.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Test_Result {
	public const STATUS_PASSED       = 'Passed';
	public const STATUS_WARNING      = 'Warning';
	public const STATUS_FAILED       = 'Failed';
	public const STATUS_SKIPPED      = 'Skipped';
	public const STATUS_NOT_AVAILABLE = 'Not Available';

	public const SEVERITY_INFO     = 'Info';
	public const SEVERITY_LOW      = 'Low';
	public const SEVERITY_MEDIUM   = 'Medium';
	public const SEVERITY_HIGH     = 'High';
	public const SEVERITY_CRITICAL = 'Critical';

	public static function normalize_status( string $status ): string {
		$map = array(
			'passed'        => self::STATUS_PASSED,
			'warning'       => self::STATUS_WARNING,
			'failed'        => self::STATUS_FAILED,
			'skipped'       => self::STATUS_SKIPPED,
			'not available' => self::STATUS_NOT_AVAILABLE,
			'not_available' => self::STATUS_NOT_AVAILABLE,
		);
		$key = strtolower( trim( $status ) );
		return $map[ $key ] ?? self::STATUS_NOT_AVAILABLE;
	}

	public static function normalize_severity( string $severity ): string {
		$map = array(
			'info'     => self::SEVERITY_INFO,
			'low'      => self::SEVERITY_LOW,
			'medium'   => self::SEVERITY_MEDIUM,
			'high'     => self::SEVERITY_HIGH,
			'critical' => self::SEVERITY_CRITICAL,
		);
		$key = strtolower( trim( $severity ) );
		return $map[ $key ] ?? self::SEVERITY_INFO;
	}

	public static function create(
		string $test_id,
		string $category,
		string $title,
		string $status,
		string $severity,
		string $message,
		string $detail = '',
		string $fix = ''
	): array {
		return array(
			'testId'          => sanitize_key( $test_id ),
			'category'        => sanitize_text_field( $category ),
			'title'           => sanitize_text_field( $title ),
			'status'          => self::normalize_status( $status ),
			'severity'        => self::normalize_severity( $severity ),
			'shortMessage'    => self::limit( $message, 180 ),
			'technicalDetail' => self::limit( $detail, 500 ),
			'suggestedFix'    => self::limit( $fix, 280 ),
			'timestamp'       => gmdate( 'c' ),
		);
	}

	public static function pass( string $test_id, string $category, string $title, string $message, string $detail = '', string $fix = '' ): array {
		return self::create( $test_id, $category, $title, self::STATUS_PASSED, self::SEVERITY_INFO, $message, $detail, $fix );
	}

	public static function warn( string $test_id, string $category, string $title, string $message, string $detail = '', string $fix = '' ): array {
		return self::create( $test_id, $category, $title, self::STATUS_WARNING, self::SEVERITY_MEDIUM, $message, $detail, $fix );
	}

	public static function fail( string $test_id, string $category, string $title, string $message, string $detail = '', string $fix = '' ): array {
		return self::create( $test_id, $category, $title, self::STATUS_FAILED, self::SEVERITY_HIGH, $message, $detail, $fix );
	}

	public static function skipped( string $test_id, string $category, string $title, string $message, string $detail = '', string $fix = '' ): array {
		return self::create( $test_id, $category, $title, self::STATUS_SKIPPED, self::SEVERITY_LOW, $message, $detail, $fix );
	}

	public static function unavailable( string $test_id, string $category, string $title, string $message, string $detail = '', string $fix = '' ): array {
		return self::create( $test_id, $category, $title, self::STATUS_NOT_AVAILABLE, self::SEVERITY_LOW, $message, $detail, $fix );
	}

	private static function limit( string $value, int $max ): string {
		$value = sanitize_text_field( $value );
		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $value, 0, $max );
		}
		return substr( $value, 0, $max );
	}
}
