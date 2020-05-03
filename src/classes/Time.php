<?php namespace Time; require_once ($_SERVER['DOCUMENT_ROOT'] . '/preamble.php');

const ms_to_us = 1000;
const s_to_us = 1000 * ms_to_us;
const min_to_s = 60;
const hour_to_s = 60 * min_to_s;
const day_to_s = 24 * hour_to_s;
const week_to_s = 7 * day_to_s;

function seconds_since ($timestamp)
{
  return time() - $timestamp;
}
