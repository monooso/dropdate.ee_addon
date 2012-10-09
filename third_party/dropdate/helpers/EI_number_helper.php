<?php

/**
 * Helpful number functions.
 *
 * @author        Stephen Lewis (http://github.com/experience/)
 * @copyright     Experience Internet
 * @package       JAT_subscriber
 */

if ( ! function_exists('valid_float'))
{

  /**
   * Determines whether the supplied argument is, or can be evaluated to,
   * a valid floating point number.
   *
   * @param   mixed       $value      The value to check.
   * @param   mixed       $min        The minimum permissible value.
   * @param   mixed       $max        The maximum permissible value.
   * @return  bool
   */
  function valid_float($value, $min = NULL, $max = NULL)
  {
    $valid = (is_float($value)
      OR (is_numeric($value) && floatval($value) == $value));

    // If no bounds have been set, we're done.
    if ( ! $valid OR (is_null($min) && is_null($max)))
    {
      return $valid;
    }

    $min = is_null($min) ? -INF : (valid_float($min) ? floatval($min) : -INF);
    $max = is_null($max) ? INF : (valid_float($max) ? floatval($max) : INF);

    $value      = floatval($value);
    $real_min   = min($min, $max);
    $real_max   = max($min, $max);

    return $valid && (min(max($value, $real_min), $real_max) === $value);
  }

}


if ( ! function_exists('valid_int'))
{

  /**
   * Determines whether the supplied argument is, or can be evaluated to,
   * a valid integer.
   *
   * @param   mixed       $value      The value to check.
   * @param   mixed       $min        The minimum permissible value.
   * @param   mixed       $max        The maximum permissible value.
   * @return  bool
   */
  function valid_int($value, $min = NULL, $max = NULL)
  {
    $valid = (is_int($value)
      OR (is_numeric($value)
      && intval($value) == $value)
    );

    // If no bounds have been set, we're done.
    if ( ! $valid OR (is_null($min) && is_null($max)))
    {
      return $valid;
    }

    $min = is_null($min) ? -INF : (valid_int($min) ? intval($min) : -INF);
    $max = is_null($max) ? INF : (valid_int($max) ? intval($max) : INF);

    $value      = intval($value);
    $real_min   = min($min, $max);
    $real_max   = max($min, $max);

    return $valid && (min(max($value, $real_min), $real_max) === $value);
  }

}


/* End of file      : EI_number_helper.php */
/* File location    : third_party/jat_subscriber/helpers/EI_number_helper.php */
