<?php namespace File; require_once ($_SERVER['DOCUMENT_ROOT'] . '/preamble.php');

require_once ('functions.php');

function fetch_remote_file_if_exists ($remote, $local)
{
  if (checkExternalFile ($remote) !== 200)
  {
    throw \InvalidArgumentException ('file ' . $remote . ' does not exist');
  }

  if (!copy ($remote, $local))
  {
    throw \Exception ('failed to write to ' . $local);
  }
}
