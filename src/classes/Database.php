<?php

include_once ('data/dbconnect.php');

function Database__maybe_die ($result)
{
  global $dbcon;
  if ($result === FALSE || $result === NULL) die (mysqli_error ($dbcon));
  return $result;
}

function Database_query ($query)
{
  global $dbcon;
  return Database__maybe_die (mysqli_query ($dbcon, $query));
}

function Database_prepare ($query)
{
  global $dbcon;
  return Database__maybe_die ($dbcon->prepare ($query));
}
function Database_prepared_query ($query, $types, ...$values)
{
  $stm = Database_prepare ($query);
  Database__maybe_die ($stm->bind_param ($types, ...$values));
  Database__maybe_die ($stm->execute());
  return $stm;
}
function Database_SELECT_object ($table, $query, $types, ...$values)
{
  $stm = Database_prepared_query
    ( 'SELECT * FROM ' . $table
    . ' ' . $query . ' LIMIT 1'
    , $types
    , ...$values
    );
  $result = Database__maybe_die ($stm->get_result());
  $object = Database__maybe_die ($result->fetch_object());
  $result->free();
  return $object;
}
function Database_UPDATE ($table, $assignments, $query, $types, ...$values)
{
  $assignment_strings = [];
  foreach ($assignments as $key => $value)
  {
    if (is_string ($key))
    {
      $assignment_strings[] = $key . ' = ' . $value;
    }
    else
    {
      $assignment_strings[] = $value . ' = ?';
    }
  }
  Database_prepared_query
    ( 'UPDATE ' . $table
    . ' SET ' . implode (',', $assignment_strings)
    . ' ' . $query
    , $types
    , ...$values
    );
}
function Database_INSERT_INTO ($table, $columns, $types, ...$values)
{
  $cols = [];
  $vals = [];
  foreach ($columns as $key => $value)
  {
    if (is_string ($key))
    {
      $cols[] = $key;
      $vals[] = $value;
    }
    else
    {
      $cols[] = $value;
      $vals[] = '?';
    }
  }
  Database_prepared_query
    ( 'INSERT INTO ' . $table . ' (' . implode (',', $cols) . ')'
    . ' VALUES (' . implode (',', $vals) . ')'
    . ''
    , $types
    , ...$values
    );
}
function Database_DELETE ($table, $query, $types, ...$values)
{
  Database_prepared_query
    ( 'DELETE FROM ' . $table
    . ' ' . $query
    , $types
    , ...$values
    );
}

function Database_query_single ($query)
{
  return Database__maybe_die (mysqli_fetch_row (Database_query ($query)))[0];
}
function Database_query_maybe_single ($query)
{
  $result = Database_query ($query);
  return mysqli_num_rows ($result)
      ? Database__maybe_die (mysqli_fetch_row ($result))[0]
      : FALSE;
}

function Database_query_object ($query)
{
  return Database__maybe_die (mysqli_fetch_object (Database_query ($query)));
}
function Database_query_maybe_object ($query)
{
  $result = Database_query ($query);
  return mysqli_num_rows ($result)
      ? Database__maybe_die (mysqli_fetch_object ($result))
      : FALSE;
}

function Database_escape_string ($str)
{
  global $dbcon;
  return Database__maybe_die (mysqli_real_escape_string ($dbcon, $str));
}

function Database_insert ($table, $fields, ...$values)
{
  global $dbcon;
  //! \todo automatically escape all values
  Database_query ( 'INSERT INTO ' . $table
                 . ' (`' . implode ('`,`', $fields) . '`) '
                 . 'VALUES (\'' . implode ('\',\'', $values) . '\')'
                 );
  return mysqli_insert_id ($dbcon);
}

function Database_protocol_user_activity ($user, $priority, $activity, $comment = "")
{
  global $user_ip_adress;
  Database_insert ( 'UserProtocol'
                  , ['User', 'IP', 'Priority', 'Activity', 'Comment']
                  , $user->id, $user_ip_adress, $priority, $activity, $comment
                  );
}
function Database_protocol_user_activity_with_request ($user, $priority, $activity, $comment = "")
{
  global $user_ip_adress, $mainselector, $subselector, $strategy;
  Database_insert ( 'UserProtocol'
                  , ['User', 'IP', 'Priority', 'Activity', 'Comment', 'Main', 'Sub', 'Alternative']
                  , $user->id, $user_ip_adress, $priority, $activity, $comment, $mainselector, $subselector, $strategy
                  );
}
