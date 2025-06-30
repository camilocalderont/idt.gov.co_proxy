<?php

namespace Drupal\twig_tools\TwigExtension;

use Twig\Error\RuntimeError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Twig filters for converting values.
 */
class TwigConvert extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getFilters(): array {
    return [
      new TwigFilter('boolean', [$this, 'booleanValue']),
      new TwigFilter('integer', [$this, 'integerValue']),
      new TwigFilter('float', [$this, 'floatValue']),
      new TwigFilter('string', [$this, 'stringValue']),
      new TwigFilter('md5', [$this, 'md5Value']),
      new TwigFilter('json_decode', [$this, 'jsonDecode']),
      new TwigFilter('date_from_format', [$this, 'dateFromFormat']),
      new TwigFilter('base64_encode', [$this, 'base64Encode']),
      new TwigFilter('base64_decode', [$this, 'base64Decode']),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'twig_tools_convert.twig.extension';
  }

  /**
   * Returns the boolean value of a passed variable.
   *
   * @param mixed $value
   *   The variable to get the boolean equivalent value of.
   *
   * @return bool
   *   The boolean value equivalent of the variable.
   */
  public static function booleanValue($value): bool {
    return boolval($value);
  }

  /**
   * Returns the integer value of a passed variable.
   *
   * @param mixed $value
   *   The variable to get the integer equivalent value of.
   *
   * @return int
   *   The integer value equivalent of the variable.
   */
  public static function integerValue($value): int {
    return intval($value);
  }

  /**
   * Returns the float value of a passed variable.
   *
   * @param mixed $value
   *   The variable to get the float equivalent value of.
   *
   * @return float
   *   The float value equivalent of the variable.
   */
  public static function floatValue($value): float {
    return floatval($value);
  }

  /**
   * Returns the string value of a passed variable.
   *
   * @param mixed $value
   *   The variable to get the string equivalent value of.
   *
   * @return string
   *   The string value equivalent of the variable.
   */
  public static function stringValue($value): string {
    return strval($value);
  }

  /**
   * Returns the md5 hash value of a passed variable.
   *
   * @param mixed $value
   *   The variable to get the md5 hash equivalent value of.
   *
   * @return string
   *   The md5 string hash value of the variable.
   */
  public static function md5Value($value): string {
    return md5(strval($value));
  }

  /**
   * Decodes a JSON string into an object or array.
   *
   * @param string $value
   *   The JSON string to decode.
   * @param bool $assoc
   *   If TRUE, will convert JSON to an associative array instead of an object.
   *
   * @return array|object
   *   The object or array equivalent of the JSON string.
   */
  public static function jsonDecode(string $value, bool $assoc = FALSE) {
    return json_decode($value, $assoc);
  }

  /**
   * Converts a datetime string between different date formats.
   *
   * @param string $value
   *   A datetime string that matches the $from_format date format.
   * @param string $from_format
   *   A PHP datetime format string.
   * @param string $to_format
   *   A PHP datetime format string.
   * @param string|null $from_timezone
   *   The timezone identifier the datetime should be converted from.
   * @param string|null $to_timezone
   *   The timezone identifier the datetime should be converted to.
   *
   * @return string
   *   The datetime formatted according to the specific data format.
   */
  public static function dateFromFormat(string $value, string $from_format, string $to_format, ?string $from_timezone = NULL, ?string $to_timezone = NULL): string {
    // Since a Unix timestamp can be 0 or '0', we need additional
    // empty/falsy checks.
    if (empty($value) && $value !== '0' && $value !== 0) {
      return '';
    }

    // Create a datetime object from the specified format.
    $converted_date = $from_timezone
    ? \DateTime::createFromFormat($from_format, $value, new \DateTimeZone($from_timezone))
    : \DateTime::createFromFormat($from_format, $value);

    // Convert datetime to other timezone if specified.
    if (isset($to_timezone)) {
      $converted_date = $converted_date->setTimezone(new \DateTimeZone($to_timezone));
    }

    // Return the datetime formatted in the specified format.
    return $converted_date->format($to_format);
  }

  /**
   * Encode string value to Base64 string.
   *
   * @param string $value
   *   The string value to encode.
   *
   * @return string
   *   The encoded string.
   *
   * @throws Twig\Error\RuntimeError
   */
  public static function base64Encode(string $value): string {
    if (empty($value)) {
      return '';
    }

    if (!is_string($value)) {
      throw new RuntimeError(sprintf('The "base64_encode" filter expects a string as value, got "%s".',
        \gettype($value)));
    }

    return base64_encode($value);
  }

  /**
   * Decode string from Base64.
   *
   * @param string $value
   *   The encoded string value.
   * @param bool $strict
   *   If set to TRUE, enforce base64 alphabet.
   *
   * @return string|false
   *   Returns decoded string or FALSE on failure.
   *
   * @throws Twig\Error\RuntimeError
   */
  public static function base64Decode(string $value, bool $strict = FALSE) {
    if (empty($value)) {
      return '';
    }

    if (!is_string($value)) {
      throw new RuntimeError(sprintf('The "base64_decode" filter expects a string as value, got "%s".',
        \gettype($value)));
    }

    return base64_decode($value, $strict);
  }

}
