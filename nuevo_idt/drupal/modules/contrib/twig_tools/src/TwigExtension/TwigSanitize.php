<?php

namespace Drupal\twig_tools\TwigExtension;

use Drupal\Component\Utility\Html;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Twig filters for sanitizing class arrays.
 */
class TwigSanitize extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getFilters(): array {
    return [
      new TwigFilter('clean_class_array', [$this, 'cleanClassArray']),
      new TwigFilter('array_unique', [$this, 'arrayUnique']),
      new TwigFilter('remove_empty', [$this, 'removeEmpty']),
      new TwigFilter('scrub_class_array', [$this, 'scrubClassArray']),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'twig_tools_sanitize.twig.extension';
  }

  /**
   * Sanitizes all strings in an array for use as valid class names.
   *
   * @param array $classes
   *   The array of class names to clean.
   *
   * @return array
   *   A new array with the cleaned class names.
   */
  public static function cleanClassArray(array $classes): array {

    return array_map(function ($class) {
      return Html::getClass($class);
    }, $classes);
  }

  /**
   * Filters all non-unique values from an array.
   *
   * @param array $array
   *   The array to remove non-unique values from.
   *
   * @return array
   *   A new array with only unique values.
   */
  public static function arrayUnique(array $array): array {
    return array_merge([], array_unique($array));
  }

  /**
   * Removes all falsy values from an array.
   *
   * @param array $array
   *   The array to remove empty/falsy values from.
   *
   * @return array
   *   A new array with only non-empty/non-falsy values.
   */
  public static function removeEmpty(array $array): array {
    return array_merge([], array_filter($array));
  }

  /**
   * Runs array through all of the Twig Tools sanitization filters.
   *
   * @param array $array
   *   The array to run through the sanitization filters.
   *
   * @return array
   *   The new sanitized array.
   */
  public static function scrubClassArray(array $array): array {
    $array = self::cleanClassArray($array);
    $array = self::removeEmpty($array);
    $array = self::arrayUnique($array);
    return $array;
  }

}
