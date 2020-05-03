<?php

require_once ('HTML.php');

function Growl_show_raw ($level, $message, $duration, $size, $location)
{
  echo ( '<script>$.growl.' . $level . '('
       //! \todo escape message
       . '{ message: "' . $message . '"'
       . ', duration: "' . $duration . '"'
       . ', size: "' . $size . '"'
       . ', location: "' . $location . '"'
       . '});</script>'
       );
}

function Growl_show_notice ($message, $duration = 5000, $size = "large", $location = "tc")
{
  return Growl_show_raw ("notice", $message, $duration, $size, $location);
}
function Growl_show_error ($message, $duration = 5000, $size = "large", $location = "tc")
{
  return Growl_show_raw ("error", $message, $duration, $size, $location);
}

function Growl_append_raw ($where, $level, $message, $duration, $size, $location)
{
  HTML_append_text
    ( HTML_append_element ($where, 'script')
    , '<script>$.growl.' . $level . '('
    //! \todo escape message
    . '{ message: "' . $message . '"'
    . ', duration: "' . $duration . '"'
    . ', size: "' . $size . '"'
    . ', location: "' . $location . '"'
    . '});'
    );
}

function Growl_append_notice ($where, $message, $duration = 5000, $size = "large", $location = "tc")
{
  return Growl_append_raw ($where, "notice", $message, $duration, $size, $location);
}
function Growl_append_error ($where, $message, $duration = 5000, $size = "large", $location = "tc")
{
  return Growl_append_raw ($where, "error", $message, $duration, $size, $location);
}
